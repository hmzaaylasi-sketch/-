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
if (!isset($input['race_id']) || !isset($input['status'])) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'بيانات ناقصة'
    ]);
    exit();
}

$race_id = intval($input['race_id']);
$status = trim($input['status']);

// التحقق من القيم المسموحة
$allowed_statuses = ['upcoming', 'running', 'finished', 'cancelled'];
if (!in_array($status, $allowed_statuses)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'حالة غير صالحة'
    ]);
    exit();
}

try {
    // التحقق من وجود السباق
    $check_stmt = $conn->prepare("SELECT race_id FROM races WHERE race_id = :race_id");
    $check_stmt->bindParam(":race_id", $race_id);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'السباق غير موجود'
        ]);
        exit();
    }

    // تحديث حالة السباق
    $update_stmt = $conn->prepare("UPDATE races SET status = :status WHERE race_id = :race_id");
    $update_stmt->bindParam(":status", $status);
    $update_stmt->bindParam(":race_id", $race_id);
    
    if ($update_stmt->execute()) {
        // إذا تم إنهاء السباق، تحديث الرهانات
        if ($status === 'finished') {
            // هنا سيتم إضافة كود تحديث الرهانات الفائزة
            // سيتم تطويره لاحقاً
        }
        
        echo json_encode([
            'status' => 'success',
            'message' => 'تم تحديث حالة السباق بنجاح'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'فشل في تحديث الحالة'
        ]);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'خطأ في قاعدة البيانات: ' . $e->getMessage()
    ]);
}