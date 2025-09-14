<?php
session_start();
include '../init/ini.php';

// ✅ جلب المعرف
$method_id = $_GET['id'] ?? 0;
if (!$method_id) {
    die("⚠️ طريقة الدفع غير موجودة.");
}

try {
    // ✅ جلب البيانات الحالية
    $stmt = $conn->prepare("SELECT * FROM payment_methods WHERE method_id = ?");
    $stmt->execute([$method_id]);
    $method = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$method) {
        die("⚠️ طريقة الدفع غير موجودة.");
    }

    // ✅ تحديث البيانات
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $method_name = trim($_POST['method_name']);
        $details = trim($_POST['details']);
        $status = $_POST['status'] ?? 'inactive';

        if (!empty($method_name) && !empty($details)) {
            $stmt = $conn->prepare("UPDATE payment_methods SET method_name=?, details=?, status=? WHERE method_id=?");
            $stmt->execute([$method_name, $details, $status, $method_id]);

            $_SESSION['success'] = "✅ تم تحديث طريقة الدفع بنجاح";
            header("Location: methods_list.php");
            exit;
        } else {
            $error = "⚠️ يرجى ملء جميع الحقول";
        }
    }
} catch (PDOException $e) {
    $error = "❌ خطأ في قاعدة البيانات: " . $e->getMessage();
}
?>

<?php include bess_url('header','file'); ?>
<?php include bess_url('navbar','file'); ?>
<?php include bess_url('sidebar','file'); ?>

<div class="content-wrapper">
    <!-- عنوان الصفحة -->
    <section class="content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h2>✏️ تعديل طريقة الدفع</h2>
            <a href="methods_list.php" class="btn btn-secondary">⬅️ رجوع</a>
        </div>
    </section>

    <!-- النموذج -->
    <section class="content">
        <div class="container-fluid">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">معلومات طريقة الدفع</h3>
                </div>
                <form method="POST">
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label>اسم الطريقة</label>
                            <input type="text" name="method_name" class="form-control"
                                value="<?= htmlspecialchars($method['method_name']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label>تفاصيل</label>
                            <textarea name="details" class="form-control" rows="3"
                                required><?= htmlspecialchars($method['details']) ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>الحالة</label>
                            <select name="status" class="form-control">
                                <option value="active" <?= $method['status']=="active"?"selected":"" ?>>✅ فعال</option>
                                <option value="inactive" <?= $method['status']=="inactive"?"selected":"" ?>>⏸️ غير فعال
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-success">💾 تحديث</button>
                        <a href="methods_list.php" class="btn btn-secondary">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<?php include bess_url('footer','file'); ?>