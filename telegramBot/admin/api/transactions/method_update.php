<?php
include '../../init/ini.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id'], $data['status'])) {
    echo json_encode(["status" => "error", "message" => "بيانات ناقصة"]);
    exit;
}

try {
    $stmt = $conn->prepare("UPDATE payment_methods SET status=? WHERE method_id=?");
    $stmt->execute([$data['status'], $data['id']]);
    echo json_encode(["status" => "success", "message" => "تم التحديث"]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
