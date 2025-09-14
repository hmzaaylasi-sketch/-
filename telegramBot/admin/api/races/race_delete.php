<?php
header("Content-Type: application/json");
include '../../init/ini.php';

$data = json_decode(file_get_contents("php://input"), true);
$race_id = $data['id'] ?? null;

if (!$race_id) {
    echo json_encode(["status" => "error", "message" => "⚠️ معرف السباق غير صالح"]);
    exit;
}

try {
    $stmt = $conn->prepare("DELETE FROM horse_races WHERE race_id = ?");
    $stmt->execute([$race_id]);

    echo json_encode(["status" => "success", "message" => "✅ تم حذف السباق بنجاح"]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "❌ خطأ: " . $e->getMessage()]);
}
