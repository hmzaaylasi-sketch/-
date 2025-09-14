<?php
session_start();
include '../../init/ini.php';

// ✅ تحقق من تسجيل الدخول
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(["status" => "error", "message" => "غير مصرح بالدخول"]);
    exit();
}

header("Content-Type: application/json; charset=UTF-8");

// ✅ التحقق من وجود horse_id
$data = json_decode(file_get_contents("php://input"), true);
$horse_id = $data['id'] ?? 0;

if (!$horse_id) {
    echo json_encode(["status" => "error", "message" => "معرف الحصان غير صالح"]);
    exit();
}

try {
    // تحقق من وجود الحصان
    $stmt = $conn->prepare("SELECT horse_id FROM horses WHERE horse_id = ?");
    $stmt->execute([$horse_id]);
    if (!$stmt->fetch()) {
        echo json_encode(["status" => "error", "message" => "الحصان غير موجود"]);
        exit();
    }

    // حذف الحصان
    $stmt = $conn->prepare("DELETE FROM horses WHERE horse_id = ?");
    $stmt->execute([$horse_id]);

    echo json_encode(["status" => "success", "message" => "✅ تم حذف الحصان بنجاح"]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "⚠️ خطأ: " . $e->getMessage()]);
}
