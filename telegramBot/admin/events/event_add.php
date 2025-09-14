<?php
session_start();
include '../init/ini.php';

// جلب أنواع الأحداث من قاعدة البيانات
try {
    $stmt = $conn->query("SELECT type_id, type_name FROM event_types ORDER BY type_name ASC");
    $event_types = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("خطأ في جلب أنواع الأحداث: " . $e->getMessage());
}

// إضافة حدث جديد
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_name = trim($_POST['event_name']);
    $type_id = $_POST['type_id'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    if ($event_name && $type_id && $start_time && $end_time) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO events (event_name, type_id, start_time, end_time) 
                VALUES (:event_name, :type_id, :start_time, :end_time)
            ");
            $stmt->execute([
                ':event_name' => $event_name,
                ':type_id' => $type_id,
                ':start_time' => $start_time,
                ':end_time' => $end_time
            ]);

            $_SESSION['success'] = "✅ تم إضافة الحدث بنجاح!";
            header("Location: event_list.php");
            exit;
        } catch (PDOException $e) {
            $error = "خطأ في إضافة الحدث: " . $e->getMessage();
        }
    } else {
        $error = "⚠️ يرجى ملء جميع الحقول.";
    }
}
?>

<?php include bess_url('header'); ?>
<?php include bess_url('navbar'); ?>
<?php include bess_url('sidebar'); ?>

<div class="container-fluid px-4 mt-4">
    <h2 class="mb-4">➕ إضافة حدث جديد</h2>

    <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">اسم الحدث</label>
                    <input type="text" name="event_name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">نوع الحدث</label>
                    <select name="type_id" class="form-select" required>
                        <option value="">-- اختر النوع --</option>
                        <?php foreach ($event_types as $type): ?>
                        <option value="<?php echo $type['type_id']; ?>">
                            <?php echo htmlspecialchars($type['type_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">تاريخ البداية</label>
                    <input type="datetime-local" name="start_time" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">تاريخ النهاية</label>
                    <input type="datetime-local" name="end_time" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-success">✅ حفظ</button>
                <a href="event_list.php" class="btn btn-secondary">⬅️ رجوع</a>
            </form>
        </div>
    </div>
</div>

<?php include bess_url('footer'); ?>