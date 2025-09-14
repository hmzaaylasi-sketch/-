<?php
include '../../init/ini.php';

$apiKey = "YOUR_BINANCE_API_KEY";
$apiSecret = "YOUR_BINANCE_API_SECRET";
$merchantId = "YOUR_BINANCE_MERCHANT_ID";

// بيانات العملية
$orderId = uniqid("order_");
$amount = 10; // مثلا 10 USDT

$data = [
    "merchantId" => $merchantId,
    "subMerchantId" => "",
    "merchantTradeNo" => $orderId,
    "totalFee" => $amount * 100, // بالمليمتر (cents)
    "currency" => "USDT",
    "productType" => "Payment",
    "productName" => "Deposit",
    "productDetail" => "User deposit",
    "returnUrl" => "https://example.com/return", // صفحة بعد الدفع
    "cancelUrl" => "https://example.com/cancel"
];

// توقيع البيانات
$payload = json_encode($data, JSON_UNESCAPED_SLASHES);
$nonce = uniqid();
$timestamp = round(microtime(true) * 1000);
$message = $timestamp . "\n" . $nonce . "\n" . $payload . "\n";
$signature = strtoupper(hash_hmac('SHA512', $message, $apiSecret));

$ch = curl_init("https://bpay.binanceapi.com/binancepay/openapi/v2/order");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "BinancePay-Timestamp: $timestamp",
    "BinancePay-Nonce: $nonce",
    "BinancePay-Certificate-SN: $apiKey",
    "BinancePay-Signature: $signature"
]);

$response = curl_exec($ch);
curl_close($ch);

echo $response;
