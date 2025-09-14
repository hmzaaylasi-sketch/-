<?php
include '../../init/ini.php';
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

try {
    $stmt = $conn->prepare("INSERT INTO payment_methods 
        (method_name, method_type, details, status, binance_api_key, binance_api_secret, binance_merchant_id) 
        VALUES (:name, :type, :details, :status, :api_key, :api_secret, :merchant_id)");
    
    $stmt->execute([
        ':name' => $data['method_name'],
        ':type' => $data['method_type'],
        ':details' => $data['details'],
        ':status' => $data['status'],
        ':api_key' => $data['binance_api_key'] ?? null,
        ':api_secret' => $data['binance_api_secret'] ?? null,
        ':merchant_id' => $data['binance_merchant_id'] ?? null
    ]);

    echo json_encode(["status" => "success", "message" => "✅ تم إضافة طريقة الدفع بنجاح"]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "❌ خطأ: " . $e->getMessage()]);
}
