<?php
session_start();
include '../init/ini.php';

// جلب الأحداث من قاعدة البيانات
try {
    $stmt = $conn->query("
        SELECT e.event_id, e.event_name, e.start_time, e.end_time, et.type_name
        FROM events e
        JOIN event_types et ON e.type_id = et.type_id
        ORDER BY e.start_time DESC
    ");
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("خطأ في جلب الأحداث: " . $e->getMessage());
}
?>

<?php include bess_url('header'); ?>
<?php include bess_url('navbar'); ?>
<?php include bess_url('sidebar'); ?>

<div class="container-fluid px-4 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>🎲 إدارة الأحداث</h2>
        <a href="event_add.php" class="btn btn-primary">
            ➕ إضافة حدث جديد
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">📋 قائمة الأحداث</h5>
            <div class="overflow-visible table-responsive">
                <table class="table table-bordered table-striped text-center align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>اسم الحدث</th>
                            <th>نوع الحدث</th>
                            <th>تاريخ البداية</th>
                            <th>تاريخ النهاية</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($events) > 0): ?>
                        <?php foreach ($events as $event): ?>
                        <tr>
                            <td><?php echo $event['event_id']; ?></td>
                            <td>
                                <a href="event_view.php?id=<?php echo $event['event_id']; ?>"
                                    class="text-decoration-none fw-bold">
                                    <?php echo htmlspecialchars($event['event_name']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($event['type_name']); ?></td>
                            <td><?php echo $event['start_time']; ?></td>
                            <td><?php echo $event['end_time']; ?></td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button"
                                        data-bs-toggle="dropdown">
                                        ⚙️ خيارات
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item"
                                                href="event_edit.php?id=<?php echo $event['event_id']; ?>">✏️ تعديل</a>
                                        </li>
                                        <li><a class="dropdown-item text-danger"
                                                href="event_delete.php?id=<?php echo $event['event_id']; ?>"
                                                onclick="return confirm('هل أنت متأكد من حذف هذا الحدث؟');">🗑️ حذف</a>
                                        </li>
                                        <li><a class="dropdown-item"
                                                href="event_view.php?id=<?php echo $event['event_id']; ?>">👁️ عرض
                                                التفاصيل</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="6">⚠️ لا توجد أحداث مسجلة حالياً</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include bess_url('footer'); ?>