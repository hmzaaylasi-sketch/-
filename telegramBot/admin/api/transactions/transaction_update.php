<?php
include '../../init/ini.php';

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$transaction_id = $data['id'] ?? 0;
$action = $data['action'] ?? '';

if (!$transaction_id || !in_array($action, ['approve','reject'])) {
    echo json_encode(["status"=>"error","message"=>"⚠️ بيانات غير صحيحة"]);
    exit;
}

try {
    // جلب المعاملة
    $stmt = $conn->prepare("SELECT * FROM transactions WHERE transaction_id=? AND status='pending'");
    $stmt->execute([$transaction_id]);
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$transaction) {
        echo json_encode(["status"=>"error","message"=>"⚠️ المعاملة غير موجودة أو تمت معالجتها"]);
        exit;
    }

    $new_status = $action === 'approve' ? 'approved' : 'rejected';

    $conn->beginTransaction();

    // تحديث الحالة
    $stmt = $conn->prepare("UPDATE transactions SET status=? WHERE transaction_id=?");
    $stmt->execute([$new_status, $transaction_id]);

    // إذا تمت الموافقة على الإيداع → إضافة رصيد
    if ($action === 'approve' && $transaction['type'] === 'deposit') {
        $stmt = $conn->prepare("UPDATE users SET currency = currency + ? WHERE user_id=?");
        $stmt->execute([$transaction['amount'], $transaction['user_id']]);
    }

    // إذا تمت الموافقة على السحب → خصم رصيد
    if ($action === 'approve' && $transaction['type'] === 'withdraw') {
        $stmt = $conn->prepare("UPDATE users SET currency = currency - ? WHERE user_id=?");
        $stmt->execute([$transaction['amount'], $transaction['user_id']]);
    }

    $conn->commit();

    echo json_encode(["status"=>"success","message"=>"✅ تم تحديث المعاملة ($new_status)"]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(["status"=>"error","message"=>"❌ خطأ: ".$e->getMessage()]);
}
