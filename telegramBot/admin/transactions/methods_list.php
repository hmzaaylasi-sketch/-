<?php
session_start();
include '../init/ini.php';

// جلب جميع طرق الدفع
try {
    $stmt = $conn->prepare("SELECT * FROM payment_methods ORDER BY method_id DESC");
    $stmt->execute();
    $methods = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['toast'] = [
        'type' => 'error',
        'message' => '❌ خطأ في جلب البيانات: ' . $e->getMessage()
    ];
}

 include bess_url('header','file'); 
 include bess_url('navbar','file'); 
 include bess_url('sidebar','file');
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>💳 طرق الدفع</h1>
                </div>
                <div class="col-sm-6 text-left">
                    <a href="methods_add.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> إضافة طريقة دفع
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">قائمة طرق الدفع</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>الاسم</th>
                                    <th>النوع</th>
                                    <th>العملة</th>
                                    <th>الحالة</th>
                                    <th>تاريخ الإضافة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($methods && count($methods) > 0): ?>
                                    <?php foreach ($methods as $method): ?>
                                    <tr>
                                        <td><?= $method['method_id'] ?></td>
                                        <td><?= htmlspecialchars($method['method_name']) ?></td>
                                        <td>
                                            <?php
                                            $types = [
                                                'bank' => '🏦 بنكي',
                                                'crypto' => '₿ عملات رقمية',
                                                'ewallet' => '📱 محفظة إلكترونية'
                                            ];
                                            echo $types[$method['method_type']] ?? $method['method_type'];
                                            ?>
                                        </td>
                                        <td><?= $method['currency'] ?></td>
                                        <td>
                                            <select class="form-control form-control-sm update-method-status" 
                                                    data-id="<?= $method['method_id'] ?>">
                                                <option value="active" <?= $method['status'] == 'active' ? 'selected' : '' ?>>🟢 نشط</option>
                                                <option value="inactive" <?= $method['status'] == 'inactive' ? 'selected' : '' ?>>🔴 غير نشط</option>
                                            </select>
                                        </td>
                                        <td><?= $method['created_at'] ?? 'غير محدد' ?></td>
                                        <td>
                                            <a href="methods_edit.php?id=<?= $method['method_id'] ?>" 
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-sm btn-danger delete-method" 
                                                    data-id="<?= $method['method_id'] ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">لا توجد طرق دفع</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php  include bess_url('footer','file'); ?>