<?php
session_start();
include '../init/ini.php';

$event_id = $_GET['id'] ?? 0;
if (!$event_id) {
    die("⚠️ حدث غير موجود");
}

try {
    // جلب بيانات الحدث
    $stmt = $conn->prepare("SELECT e.*, et.type_name 
                            FROM events e 
                            LEFT JOIN event_types et ON e.type_id = et.type_id 
                            WHERE e.event_id = :id");
    $stmt->execute([':id' => $event_id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        die("⚠️ هذا الحدث غير موجود");
    }

    // جلب الخيارات (Outcomes)
    $stmt = $conn->prepare("SELECT * FROM outcomes WHERE event_id = :id");
    $stmt->execute([':id' => $event_id]);
    $outcomes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("خطأ: " . $e->getMessage());
}
?>

<?php include bess_url('header'); ?>
<?php include bess_url('navbar'); ?>
<?php include bess_url('sidebar'); ?>

<div class="container-fluid px-4 mt-4">
    <h2 class="mb-4">📌 تفاصيل الحدث</h2>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title"><?php echo htmlspecialchars($event['event_name']); ?></h5>
            <p><strong>⚡ النوع:</strong> <?php echo htmlspecialchars($event['type_name']); ?></p>
            <p><strong>⏰ البداية:</strong> <?php echo $event['start_time']; ?></p>
            <p><strong>⏳ النهاية:</strong> <?php echo $event['end_time']; ?></p>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>🎲 الخيارات المتاحة</h4>
        <a href="outcome_add.php?event_id=<?php echo $event_id; ?>" class="btn btn-success btn-sm">➕ إضافة خيار</a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered text-center align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>اسم الخيار</th>
                            <th>الاحتمال (Odds)</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($outcomes) > 0): ?>
                        <?php foreach ($outcomes as $outcome): ?>
                        <tr>
                            <td><?php echo $outcome['outcome_id']; ?></td>
                            <td><?php echo htmlspecialchars($outcome['outcome_name']); ?></td>
                            <td><?php echo $outcome['odds']; ?></td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button"
                                        data-bs-toggle="dropdown">
                                        الإجراءات
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item"
                                                href="outcome_edit.php?id=<?php echo $outcome['outcome_id']; ?>">✏️
                                                تعديل</a></li>
                                        <li><a class="dropdown-item text-danger"
                                                href="outcome_delete.php?id=<?php echo $outcome['outcome_id']; ?>"
                                                onclick="return confirm('هل أنت متأكد من الحذف؟');">🗑️ حذف</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="4">⚠️ لا توجد خيارات مضافة بعد</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include bess_url('footer'); ?>