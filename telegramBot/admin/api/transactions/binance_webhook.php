<?php
include '../../init/ini.php';

$payload = file_get_contents("php://input");
$data = json_decode($payload, true);

if ($data['bizStatus'] == "PAY_SUCCESS") {
    $orderId = $data['bizId']; // رقم الطلب
    $amount = $data['totalFee'] / 100;
    $currency = $data['currency'];

    // تحديث العملية في قاعدة البيانات
    $stmt = $conn->prepare("UPDATE transactions SET status='approved' WHERE tx_hash=?");
    $stmt->execute([$orderId]);

    // إضافة الرصيد للمستخدم
    $stmt = $conn->prepare("UPDATE users SET currency = currency + ? WHERE user_id=?");
    $stmt->execute([$amount * 10, $data['merchantTradeNo']]); // تحويل USDT → MAD
}
