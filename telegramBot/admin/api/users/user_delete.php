<?php
header("Content-Type: application/json");
include '../../init/ini.php';

$data = json_decode(file_get_contents("php://input"), true);
$user_id = $data['id'] ?? null;

if (!$user_id) {
    echo json_encode(["status" => "error", "message" => "⚠️ معرف المستخدم غير صالح"]);
    exit;
}

try {
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);

    echo json_encode(["status" => "success", "message" => "✅ تم حذف المستخدم بنجاح"]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "❌ خطأ: " . $e->getMessage()]);
}
