<?php
include '../../init/ini.php';
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$methodId = $data['method_id'];
$userId = $data['user_id'];
$amount = $data['amount']; // بالدرهم المغربي

// جلب بيانات Binance من قاعدة البيانات
$stmt = $conn->prepare("SELECT * FROM payment_methods WHERE method_id=?");
$stmt->execute([$methodId]);
$method = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$method) {
    echo json_encode(["status"=>"error","message"=>"❌ طريقة الدفع غير موجودة"]);
    exit;
}

// تحويل العملة من MAD → USDT (مثلا ثابت 1 USDT = 10 MAD)
$usdtAmount = round($amount / 10, 2);

$orderId = uniqid("order_");

// بيانات الطلب
$data = [
    "merchantId" => $method['binance_merchant_id'],
    "merchantTradeNo" => $orderId,
    "totalFee" => $usdtAmount * 100, // Binance يحسب بالـ "cents"
    "currency" => "USDT",
    "productType" => "Payment",
    "productName" => "Deposit",
    "productDetail" => "User deposit $userId",
    "returnUrl" => "http://localhost/telegram/payment_success.php",
    "cancelUrl" => "http://localhost/telegram/payment_cancel.php"
];

$payload = json_encode($data, JSON_UNESCAPED_SLASHES);
$nonce = uniqid();
$timestamp = round(microtime(true) * 1000);

$message = $timestamp . "\n" . $nonce . "\n" . $payload . "\n";
$signature = strtoupper(hash_hmac('SHA512', $message, $method['binance_api_secret']));

$ch = curl_init("https://bpay.binanceapi.com/binancepay/openapi/v2/order");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "BinancePay-Timestamp: $timestamp",
    "BinancePay-Nonce: $nonce",
    "BinancePay-Certificate-SN: ".$method['binance_api_key'],
    "BinancePay-Signature: $signature"
]);

$response = curl_exec($ch);
curl_close($ch);

echo $response;
