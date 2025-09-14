<?php
session_start();
include '../init/ini.php';

// ✅ تحقق من تسجيل الأدمن
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// ✅ جلب المعاملات مع المستخدم وطريقة الدفع
try {
    $stmt = $conn->query("
        SELECT t.transaction_id, t.user_id, t.amount, t.status, t.created_at,
               u.username, pm.method_name
        FROM transactions t
        JOIN users u ON t.user_id = u.user_id
        JOIN payment_methods pm ON t.method_id = pm.method_id
        ORDER BY t.transaction_id DESC
    ");
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("❌ خطأ في جلب البيانات: " . $e->getMessage());
}

include bess_url('header', 'file');
include bess_url('navbar', 'file');
include bess_url('sidebar', 'file');
?>

<div class="content-wrapper">
    <!-- العنوان -->
    <section class="content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h2>💰 قائمة المعاملات</h2>
        </div>
    </section>

    <!-- المحتوى -->
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">📋 جميع المعاملات</h3>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped text-center">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>المستخدم</th>
                                <th>المبلغ</th>
                                <th>طريقة الدفع</th>
                                <th>الحالة</th>
                                <th>التاريخ</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($transactions): ?>
                            <?php foreach ($transactions as $t): ?>
                            <tr>
                                <td><?= $t['transaction_id'] ?></td>
                                <td><?= htmlspecialchars($t['username']) ?> (<?= $t['user_id'] ?>)</td>
                                <td><?= number_format($t['amount'], 2) ?> درهم</td>
                                <td><?= htmlspecialchars($t['method_name']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $t['status']=='approved'?'success':($t['status']=='rejected'?'danger':'warning') ?>">
                                        <?= $t['status']=='approved'?'✅ مقبول':($t['status']=='rejected'?'❌ مرفوض':'⏳ معلق') ?>
                                    </span>
                                </td>
                                <td><?= $t['created_at'] ?></td>
                                <td>
                                    <button class="btn btn-success btn-sm approve-transaction"
                                        data-id="<?= $t['transaction_id'] ?>">✅ قبول</button>
                                    <button class="btn btn-danger btn-sm reject-transaction"
                                        data-id="<?= $t['transaction_id'] ?>">❌ رفض</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-muted">⚠️ لا توجد معاملات بعد</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include bess_url('footer', 'file'); ?>
