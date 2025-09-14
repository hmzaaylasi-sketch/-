<?php
session_start();
include '../init/ini.php';

$event_id = $_GET['event_id'] ?? 0;
if (!$event_id) {
    die("⚠️ حدث غير محدد");
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $outcome_name = trim($_POST['outcome_name']);
    $odds = floatval($_POST['odds']);

    if ($outcome_name && $odds > 0) {
        try {
            $stmt = $conn->prepare("INSERT INTO outcomes (event_id, outcome_name, odds) 
                                    VALUES (:event_id, :outcome_name, :odds)");
            $stmt->execute([
                ':event_id' => $event_id,
                ':outcome_name' => $outcome_name,
                ':odds' => $odds
            ]);

            $message = "✅ تم إضافة الخيار بنجاح!";
        } catch (PDOException $e) {
            $message = "❌ خطأ: " . $e->getMessage();
        }
    } else {
        $message = "⚠️ يرجى إدخال جميع الحقول بشكل صحيح.";
    }
}
?>

<?php include bess_url('header'); ?>
<?php include bess_url('navbar'); ?>
<?php include bess_url('sidebar'); ?>

<div class="container-fluid px-4 mt-4">
    <h2 class="mb-4">➕ إضافة خيار جديد للحدث</h2>

    <?php if (!empty($message)): ?>
    <div class="alert alert-info"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">اسم الخيار</label>
                    <input type="text" name="outcome_name" class="form-control" required
                        placeholder="مثال: الفريق الأول">
                </div>

                <div class="mb-3">
                    <label class="form-label">الاحتمال (Odds)</label>
                    <input type="number" step="0.01" name="odds" class="form-control" required placeholder="مثال: 2.5">
                </div>

                <button type="submit" class="btn btn-success">✅ إضافة</button>
                <a href="event_view.php?id=<?php echo $event_id; ?>" class="btn btn-secondary">⬅️ رجوع</a>
            </form>
        </div>
    </div>
</div>

<?php include bess_url('footer'); ?>