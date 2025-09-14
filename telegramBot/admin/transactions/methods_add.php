<?php
session_start();
include '../init/ini.php';

// ✅ معالجة الإضافة
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $method_name = trim($_POST['method_name']);
    $details = trim($_POST['details']);
    $status = $_POST['status'] ?? 'inactive';

    if (!empty($method_name) && !empty($details)) {
        try {
            $stmt = $conn->prepare("INSERT INTO payment_methods (method_name, details, status) VALUES (?, ?, ?)");
            $stmt->execute([$method_name, $details, $status]);

            $_SESSION['success'] = "✅ تم إضافة طريقة الدفع بنجاح";
            header("Location: methods_list.php");
            exit;
        } catch (PDOException $e) {
            $error = "❌ خطأ: " . $e->getMessage();
        }
    } else {
        $error = "⚠️ يرجى ملء جميع الحقول";
    }
}
?>

<?php include bess_url('header','file'); ?>
<?php include bess_url('navbar','file'); ?>
<?php include bess_url('sidebar','file'); ?>

<div class="content-wrapper">
    <!-- عنوان الصفحة -->
    <section class="content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h2>➕ إضافة طريقة دفع</h2>
            <a href="methods_list.php" class="btn btn-secondary">⬅️ رجوع</a>
        </div>
    </section>

    <!-- النموذج -->
    <section class="content">
        <div class="container-fluid">
            <div class="card card-primary">
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
                            <input type="text" name="method_name" class="form-control" placeholder="مثل: بايبال / تحويل بنكي" required>
                        </div>

                        <div class="form-group">
                            <label>تفاصيل</label>
                            <textarea name="details" class="form-control" rows="3" placeholder="أدخل تفاصيل الحساب أو البيانات" required></textarea>
                        </div>

                        <div class="form-group">
                            <label>الحالة</label>
                            <select name="status" class="form-control">
                                <option value="active">✅ فعال</option>
                                <option value="inactive" selected>⏸️ غير فعال</option>
                            </select>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-success">💾 حفظ</button>
                        <a href="methods_list.php" class="btn btn-secondary">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<?php include bess_url('footer','file'); ?>
