<?php
// admin/api/races/race_import_preview.php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

// تحويل أية تحذيرات/notice إلى Exception لنرجع JSON بدل HTML
set_error_handler(function($severity, $message, $file, $line) {
    throw new \ErrorException($message, 0, $severity, $file, $line);
});

try {
    session_start();

    // (اختياري) تحقق أن الأدمن مسجل
    if (!isset($_SESSION['admin_id'])) {
        echo json_encode(['status'=>'error','message'=>'غير مصرح: يجب تسجيل الدخول.']);
        exit;
    }

    // تأكد أن هناك ملف مرفوع
    if (empty($_FILES['pdf_file']) || $_FILES['pdf_file']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['status'=>'error','message'=>'لم يتم رفع الملف']);
        exit;
    }

    // مسارات ومجلدات
    $projectRoot = dirname(__DIR__, 3); // من admin/api/races => نعود للروت
    $uploadsDir = $projectRoot . DIRECTORY_SEPARATOR . 'uploads';
    if (!is_dir($uploadsDir)) {
        @mkdir($uploadsDir, 0755, true);
    }

    $file = $_FILES['pdf_file'];
    $origName = basename($file['name']);
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    if ($ext !== 'pdf') {
        echo json_encode(['status'=>'error','message'=>'الملف ليس PDF']);
        exit;
    }

    $target = $uploadsDir . DIRECTORY_SEPARATOR . uniqid('race_') . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $target)) {
        echo json_encode(['status'=>'error','message'=>'فشل حفظ الملف المؤقت']);
        exit;
    }

    // محاولة قراءة النص من PDF:
    $text = '';
    $methods = [];
    // 1) تجربة مكتبة composer (Smalot\PdfParser)
    $autoloadCandidates = [
        $projectRoot . '/vendor/autoload.php',
        $projectRoot . '/admin/vendor/autoload.php',
        dirname(__DIR__, 2) . '/vendor/autoload.php',
        __DIR__ . '/../../../vendor/autoload.php'
    ];
    $autoloadFound = null;
    foreach ($autoloadCandidates as $c) {
        if (file_exists($c)) { $autoloadFound = $c; break; }
    }
    if ($autoloadFound) {
        require_once $autoloadFound;
        if (class_exists('Smalot\PdfParser\Parser')) {
            try {
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($target);
                $text = (string)$pdf->getText();
                $methods[] = 'smalot/pdfparser';
            } catch (Exception $e) {
                // نكمل لطرق أخرى
                $methods[] = 'smalot_installed_but_parse_failed:' . $e->getMessage();
            }
        }
    } else {
        $methods[] = 'no_autoload_found';
    }

    // 2) تجربة pdftotext (poppler/xpdf) إذا لم نحصل على نص مع smalot
    if (trim($text) === '') {
        // تحقق إذا كان pdftotext متوفر
        $pdftotextPath = null;
        // محاولة اكتشاف الأمر
        $which = (stripos(PHP_OS, 'WIN') === 0) ? 'where' : 'command -v';
        @exec("$which pdftotext 2>&1", $out, $rc);
        if ($rc === 0 && !empty($out)) {
            $pdftotextPath = trim($out[0]);
        } else {
            // ممكن يكون في PATH لكن command -v فشل، سنجرب مباشرة
            $pdftotextPath = 'pdftotext';
        }

        // نفذ الأمر pdftotext مع إخراج إلى STDOUT
        $cmd = escapeshellcmd($pdftotextPath) . ' -layout ' . escapeshellarg($target) . ' -';
        @exec($cmd . ' 2>&1', $outputLines, $rc2);
        if ($rc2 === 0 || !empty($outputLines)) {
            $text = implode("\n", $outputLines);
            $methods[] = 'pdftotext';
        } else {
            $methods[] = 'pdftotext_not_available_or_failed';
        }
    }

    // لو لا يوجد نص بعد كل المحاولات -> أبلغ المستخدم
    if (trim($text) === '') {
        echo json_encode([
            'status'=>'error',
            'message'=>'لا توجد طريقة لاستخراج نص من PDF على الخادم. ثبت مكتبة composer (smalot/pdfparser) أو أداة pdftotext.',
            'debug'=>[
                'uploaded_file'=>$target,
                'methods_checked'=>$methods,
                'autoload_candidates'=>$autoloadCandidates
            ]
        ]);
        exit;
    }

    // تنظيف النص وتحويله إلى أسطر
    $text = preg_replace("/\r\n?/", "\n", $text);
    $lines = preg_split('/\n+/', $text);

    // ======== محاولات استخراج مبدئية (best-effort) =========
    $meeting_code = null;
    $meeting_date = null;
    $location = null;
    // meeting code: بحث عن "Réunion N" أو "Meeting Rn" أو "R<number>"
    foreach ($lines as $ln) {
        if (!$meeting_code && preg_match('/\b(R(?:éunion|eunion)?\s*[:\-]?\s*(\d+))\b/i', $ln, $m)) {
            $meeting_code = 'R' . $m[2];
        }
        if (!$meeting_code && preg_match('/\bR(\d{1,2})\b/', $ln, $m2)) {
            $meeting_code = 'R' . $m2[1];
        }
        if (!$meeting_date && preg_match('/\b(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4})\b/', $ln, $md)) {
            $meeting_date = $md[1];
        }
        // مكان (غالبا اسم المدينة يظهر في السطر الأول)
        if (!$location && preg_match('/(Settat|Rabat|Casablanca|Marrakech|Khemisset|Chartres|Quarté|Settat)/i', $ln, $loc)) {
            $location = $loc[1];
        }
        if ($meeting_code && $meeting_date && $location) break;
    }

    // استخراج سباقات: نبحث عن أسطر تحتوي على C<number>
    $races = [];
    $countLines = count($lines);
    for ($i=0; $i<$countLines; $i++) {
        $ln = $lines[$i];
        if (preg_match('/\bC(\d{1,3})\b/i', $ln, $rm)) {
            $race_number = 'C' . $rm[1];
            // عنوان السباق - نحاول إزالة C# من السطر
            $title = trim(preg_replace('/\bC\d+\b/i','',$ln));
            // بحث بسيط عن وقت/مسافة/جائزة في نفس السطر أو التالي
            $time = null; $distance = null; $prize = null;
            if (preg_match('/(\d{1,2}:\d{2})/', $ln, $t1)) $time = $t1[1];
            if (!$time && $i+1 < $countLines && preg_match('/(\d{1,2}:\d{2})/',$lines[$i+1],$t2)) $time = $t2[1];
            if (preg_match('/(\d+)\s*m\b/i', $ln, $d1)) $distance = $d1[1] . 'm';
            if (preg_match('/(\d+[,\.\d]*)\s*DH/i', $ln, $p1)) $prize = $p1[1] . ' DH';

            // جمع الاحصنة المتوقعة: ابحث الأسطر التالية حتى تلاقي سطر سباق جديد أو فراغ كبير
            $horses = [];
            for ($j=$i+1; $j<$countLines; $j++) {
                $next = trim($lines[$j]);
                if ($next === '') break;
                if (preg_match('/\bC\d{1,3}\b/i', $next)) break;
                // نمط رقم ثم اسم: "1 AMAZONE MU" أو "1. AMAZONE MU"
                if (preg_match('/^\s*(\d{1,2})[.\-\)]?\s+(.{2,120})$/u', $next, $hm)) {
                    $horses[] = ['number'=>intval($hm[1]), 'name'=>trim($hm[2])];
                } else {
                    // سطر قد يحتوي أسماء مختلطة؛ نجرب التقاط أسطر طويلة كاسم حصان إذا تحتوي أحرف
                    if (preg_match('/[A-Za-z\u0600-\u06FF0-9]{3,}/u', $next)) {
                        // محاولة لاستخراج اسم اذا ليس رقم بداية
                        if (preg_match('/^\s*([A-Z\u0600-\u06FF0-9][\w\-\.\' ]{3,})$/u', $next, $hm2)) {
                            // تقديري
                            $horses[] = ['number'=>null, 'name'=>trim($hm2[1])];
                        }
                    }
                }
            }

            $races[] = [
                'race_number'=>$race_number,
                'title'=>trim($title),
                'time'=>$time,
                'distance'=>$distance,
                'prize'=>$prize,
                'horses'=>$horses
            ];
        }
    }

    // نتيجة الـ preview (نعيد النص كاملاً أيضاً)
    echo json_encode([
        'status'=>'success',
        'message'=>'تم استخراج بيانات (preview).',
        'data'=>[
            'uploaded_file'=>basename($target),
            'extraction_methods'=>$methods,
            'meeting_code'=>$meeting_code,
            'meeting_date'=>$meeting_date,
            'location'=>$location,
            'races'=>$races,
            'raw_text_preview'=>mb_substr($text, 0, 2000) // نعيد معاينة من النص (حتى 2000 حرف)
        ]
    ], JSON_UNESCAPED_UNICODE);

    exit;
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status'=>'error',
        'message'=>'❌ خطأ داخلي أثناء المعالجة',
        'error'=>$e->getMessage(),
        'trace'=>$e->getTraceAsString()
    ], JSON_UNESCAPED_UNICODE);
    exit;
} finally {
    restore_error_handler();
}
