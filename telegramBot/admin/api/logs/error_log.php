<?php
// admin/api/logs/error_log.php
// يستقبل JSON { title, message, request, responseJson, responseText, trace, debug }
header('Content-Type: application/json; charset=utf-8');

// ضع المسار المناسب للـ ini أو التهيئة لو تحتاج
// include '../../init/ini.php'; // لو تريد تسجيل اسم المستخدم أو DB info

$input = json_decode(file_get_contents("php://input"), true);
if (!$input) {
    echo json_encode(['status'=>'error','message'=>'لا توجد بيانات']);
    exit;
}

$logDir = __DIR__ . '/../../logs'; // => admin/logs
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}

$file = $logDir . '/error_' . date('Ymd') . '.log';
$entry = "=== " . date('c') . " ===\n";
$entry .= "Title: " . ($input['title'] ?? '') . "\n";
$entry .= "Message: " . ($input['message'] ?? '') . "\n";
$entry .= "Request: " . print_r($input['request'] ?? null, true) . "\n";
$entry .= "Response JSON: " . print_r($input['responseJson'] ?? null, true) . "\n";
$entry .= "Response Raw: " . ($input['responseText'] ?? '') . "\n";
$entry .= "Trace: " . ($input['trace'] ?? '') . "\n";
$entry .= "Debug: " . print_r($input['debug'] ?? null, true) . "\n";
$entry .= "\n";

if (file_put_contents($file, $entry, FILE_APPEND | LOCK_EX) !== false) {
    echo json_encode(['status'=>'success','message'=>'logged','file'=>$file]);
} else {
    echo json_encode(['status'=>'error','message'=>'فشل في كتابة الملف']);
}
