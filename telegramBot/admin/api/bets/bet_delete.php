<?php
header("Content-Type: application/json; charset=UTF-8");
session_start();
include '../../init/ini.php';

// ✅ تحقق من تسجيل الأدمن
if (!isset($_SESSION['admin_id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "❌ غير مصرح لك (يجب تسجيل الدخول كأدمن)"
    ]);
    exit;
}

// ✅ استقبال البيانات (JSON أو POST)
$input = json_decode(file_get_contents("php://input"), true);
if (!$input) {
    $input = $_POST;
}

if (!isset($input['id']) || empty($input['id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "⚠️ معرف الرهان مفقود"
    ]);
    exit;
}

$bet_id = intval($input['id']);

try {
    // ✅ حذف الرهان
    $stmt = $conn->prepare("DELETE FROM horse_bets WHERE bet_id = ?");
    $stmt->execute([$bet_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            "status" => "success",
            "message" => "✅ تم حذف الرهان بنجاح"
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "⚠️ الرهان غير موجود"
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "❌ خطأ داخلي",
        "error"   => $e->getMessage()
    ]);
}
