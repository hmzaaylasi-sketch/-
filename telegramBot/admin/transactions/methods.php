<?php
session_start();
include '../init/ini.php';

// جلب الطرق
$stmt = $conn->query("SELECT * FROM payment_methods ORDER BY status DESC");
$methods = $stmt->fetchAll(PDO::FETCH_ASSOC);

include bess_url('header','file');
include bess_url('navbar','file');
include bess_url('sidebar','file');
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h2>🏦 طرق الدفع</h2>
            <a href="method_add.php" class="btn btn-success">➕ إضافة طريقة جديدة</a>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">📋 قائمة طرق الدفع</h3>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped text-center">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>الطريقة</th>
                                <th>التفاصيل</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($methods): foreach ($methods as $m): ?>
                            <tr>
                                <td><?= $m['method_id'] ?></td>
                                <td><?= htmlspecialchars($m['method_name']) ?></td>
                                <td><?= nl2br(htmlspecialchars($m['details'])) ?></td>
                                <td>
                                    <span class="badge bg-<?= $m['status']=='active'?'success':'secondary' ?>">
                                        <?= $m['status'] ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="method_edit.php?id=<?= $m['method_id'] ?>"
                                        class="btn btn-warning btn-sm">✏️ تعديل</a>
                                    <button class="btn btn-danger btn-sm delete-method"
                                        data-id="<?= $m['method_id'] ?>">❌ حذف</button>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr>
                                <td colspan="5" class="text-muted">⚠️ لا توجد طرق دفع حالياً</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include bess_url('footer','file'); ?>