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
if (!isset($input['race_id']) || !isset($input['final_order'])) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'بيانات ناقصة'
    ]);
    exit();
}

$race_id = intval($input['race_id']);
$final_order = $input['final_order'];
$race_time = floatval($input['race_time'] ?? 0);
$race_notes = trim($input['race_notes'] ?? '');

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

    // بدء transaction
    $conn->beginTransaction();
    
    // 1. تحديث حالة السباق إلى finished
    $update_race = $conn->prepare("UPDATE races SET status = 'finished' WHERE race_id = :race_id");
    $update_race->bindParam(":race_id", $race_id);
    $update_race->execute();
    
    // 2. تحديث مراكز الأحصنة
    foreach ($final_order as $result) {
        $position = intval($result['position']);
        $entry_id = intval($result['entry_id']);
        
        $update_horse = $conn->prepare("
            UPDATE race_entries 
            SET final_position = :position, status = 'finished' 
            WHERE entry_id = :entry_id AND race_id = :race_id
        ");
        $update_horse->bindParam(":position", $position);
        $update_horse->bindParam(":entry_id", $entry_id);
        $update_horse->bindParam(":race_id", $race_id);
        $update_horse->execute();
    }
    
    // 3. حفظ نتائج السباق (باستخدام prepared statements بشكل صحيح)
    $final_order_json = json_encode($final_order, JSON_UNESCAPED_UNICODE);
    
    $save_results = $conn->prepare("
        INSERT INTO race_results (race_id, final_order, race_time, notes) 
        VALUES (:race_id, :final_order, :race_time, :notes)
    ");
    $save_results->bindParam(":race_id", $race_id);
    $save_results->bindParam(":final_order", $final_order_json);
    $save_results->bindParam(":race_time", $race_time);
    $save_results->bindParam(":notes", $race_notes);
    $save_results->execute();
    
    $conn->commit();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'تم إنهاء السباق بنجاح وتحديد المراكز'
    ]);
    
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'خطأ في قاعدة البيانات: ' . $e->getMessage()
    ]);
    
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'خطأ غير متوقع: ' . $e->getMessage()
    ]);
}
?>