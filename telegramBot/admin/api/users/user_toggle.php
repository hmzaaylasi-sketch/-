<?php
header("Content-Type: application/json");
include '../../init/ini.php';

$data = json_decode(file_get_contents("php://input"), true);
$user_id = $data['id'] ?? null;
$status  = $data['status'] ?? null;

if (!$user_id || !$status) {
    echo json_encode(["status" => "error", "message" => "⚠️ بيانات غير مكتملة"]);
    exit;
}

try {
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE user_id = ?");
    $stmt->execute([$status, $user_id]);

    echo json_encode([
        "status" => "success",
        "message" => $status === "active" ? "✅ تم تفعيل المستخدم" : "⛔ تم تعطيل المستخدم"
    ]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "❌ خطأ: " . $e->getMessage()]);
}
