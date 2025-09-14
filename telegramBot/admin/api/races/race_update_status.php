<?php
header("Content-Type: application/json");
include '../../init/ini.php';

$data = json_decode(file_get_contents("php://input"), true);
$race_id = $data['id'] ?? null;
$status  = $data['status'] ?? null;

if (!$race_id || !$status) {
    echo json_encode(["status" => "error", "message" => "⚠️ بيانات غير مكتملة"]);
    exit;
}

try {
    $stmt = $conn->prepare("UPDATE horse_races SET status = ? WHERE race_id = ?");
    $stmt->execute([$status, $race_id]);

    echo json_encode(["status" => "success", "message" => "✅ تم تحديث حالة السباق"]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "❌ خطأ: " . $e->getMessage()]);
}
