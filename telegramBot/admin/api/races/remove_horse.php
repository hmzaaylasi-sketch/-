<?php
header('Content-Type: application/json');
require_once '../../init/config.php';
require_once '../../init/auth.php';

// التحقق من أن الطلب POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'الطريقة غير مسموحة'
    ]);
    exit();
}

// قراءة البيانات المرسلة
$input = json_decode(file_get_contents('php://input'), true);

// التحقق من البيانات
if (!isset($input['entry_id'])) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'معرف الحصان مطلوب'
    ]);
    exit();
}

$entry_id = intval($input['entry_id']);

try {
    // التحقق من وجود الحصان في السباق
    $check_stmt = $conn->prepare("
        SELECT re.*, r.status as race_status 
        FROM race_entries re 
        JOIN races r ON re.race_id = r.race_id 
        WHERE re.entry_id = :entry_id
    ");
    $check_stmt->bindParam(":entry_id", $entry_id);
    $check_stmt->execute();
    
    $entry = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$entry) {
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'الحصان غير موجود في السباق'
        ]);
        exit();
    }
    
    // التحقق من أن السباق لم يبدأ بعد
    if ($entry['race_status'] !== 'upcoming') {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'لا يمكن إزالة الحصان بعد بدء السباق'
        ]);
        exit();
    }
    
    // حذف الحصان من السباق
    $delete_stmt = $conn->prepare("DELETE FROM race_entries WHERE entry_id = :entry_id");
    $delete_stmt->bindParam(":entry_id", $entry_id);
    
    if ($delete_stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'تم إزالة الحصان بنجاح'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'فشل في إزالة الحصان'
        ]);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'خطأ في قاعدة البيانات: ' . $e->getMessage()
    ]);
}