<?php
session_start();
include '../init/ini.php';

// ✅ تحقق من تسجيل الأدمن
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// ✅ جلب المستخدمين
try {
    $stmt = $conn->query("SELECT user_id, username FROM users ORDER BY user_id DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("❌ خطأ في جلب المستخدمين: " . $e->getMessage());
}

// ✅ جلب طرق الدفع
try {
    $stmt = $conn->query("SELECT method_id, method_name FROM payment_methods ORDER BY method_id DESC");
    $methods = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("❌ خطأ في جلب طرق الدفع: " . $e->getMessage());
}

// ✅ إضافة معاملة جديدة
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? 0;
    $method_id = $_POST['method_id'] ?? 0;
    $amount = $_POST['amount'] ?? 0;
    $status = $_POST['status'] ?? 'pending';

    try {
        $stmt = $conn->prepare("
            INSERT INTO transactions (user_id, method_id, amount, converted_amount, status, created_at) 
            VALUES (:user_id, :method_id, :amount, :converted_amount, :status, NOW())
        ");
        $stmt->execute([
            ':user_id' => $user_id,
            ':method_id' => $method_id,
            ':amount' => $amount,
            ':converted_amount' => $amount, // مؤقتاً نفس المبلغ
            ':status' => $status
        ]);

        $_SESSION['success'] = "✅ تمت إضافة المعاملة بنجاح";
        header("Location: transactions_list.php");
        exit();

    } catch (PDOException $e) {
        die("❌ خطأ في قاعدة البيانات: " . $e->getMessage());
    }
}

include bess_url('header','file');
include bess_url('navbar','file');
include bess_url('sidebar','file');
?>

<!-- محتوى الصفحة -->
<div class="content-wrapper">
    <!-- العنوان -->
    <section class="content-header">
        <div class="container-fluid">
            <h2>➕ إضافة معاملة جديدة</h2>
        </div>
    </section>

    <!-- النموذج -->
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h3 class="card-title">📋 تفاصيل المعاملة</h3>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <!-- اختيار المستخدم -->
                        <div class="mb-3">
                            <label class="form-label">👤 المستخدم</label>
                            <select name="user_id" class="form-control" required>
                                <option value="">-- اختر المستخدم --</option>
                                <?php foreach ($users as $u): ?>
                                <option value="<?= $u['user_id'] ?>">
                                    <?= $u['username'] ?> (ID: <?= $u['user_id'] ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- اختيار طريقة الدفع -->
                        <div class="mb-3">
                            <label class="form-label">💳 طريقة الدفع</label>
                            <select name="method_id" class="form-control" required>
                                <option value="">-- اختر الطريقة --</option>
                                <?php foreach ($methods as $m): ?>
                                <option value="<?= $m['method_id'] ?>"><?= $m['method_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- المبلغ -->
                        <div class="mb-3">
                            <label class="form-label">💰 المبلغ</label>
                            <input type="number" step="0.01" name="amount" class="form-control" required>
                        </div>

                        <!-- الحالة -->
                        <div class="mb-3">
                            <label class="form-label">📊 الحالة</label>
                            <select name="status" class="form-control">
                                <option value="pending">⏳ قيد المراجعة</option>
                                <option value="approved">✅ مقبول</option>
                                <option value="rejected">❌ مرفوض</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-success">💾 حفظ</button>
                        <a href="transactions_list.php" class="btn btn-secondary">↩️ رجوع</a>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include bess_url('footer','file'); ?>