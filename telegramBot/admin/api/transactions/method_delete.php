<?php
header("Content-Type: application/json");
require_once "../../init/ini.php";

try {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['id'] ?? 0;

    if (!$id) {
        echo json_encode(["status" => "error", "message" => "❌ معرف غير صالح"]);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM payment_methods WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode(["status" => "success", "message" => "✅ تم حذف طريقة الدفع بنجاح"]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "❌ خطأ في قاعدة البيانات: " . $e->getMessage()]);
}
