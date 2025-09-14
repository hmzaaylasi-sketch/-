<?php
include 'init/ini.php';

// جلب الأحداث من قاعدة البيانات
$stmt = $conn->query("SELECT e.*, t.type_name FROM events e 
                      LEFT JOIN event_types t ON e.type_id = t.type_id 
                      ORDER BY e.start_time DESC");
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

include bess_url('header', 'file');
include bess_url('navbar', 'file');
include bess_url('sidebar', 'file');
?>

<div class="content-wrapper p-4">
    <div class="container-fluid">
        <h1 class="mb-4">📅 قائمة الأحداث</h1>
        
        <a href="event_add.php" class="btn btn-primary mb-3">➕ إضافة حدث</a>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>الحدث</th>
                    <th>النوع</th>
                    <th>من</th>
                    <th>إلى</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($events as $event): ?>
                <tr>
                    <td><?php echo htmlspecialchars($event['event_name']); ?></td>
                    <td><?php echo htmlspecialchars($event['type_name']); ?></td>
                    <td><?php echo $event['start_time']; ?></td>
                    <td><?php echo $event['end_time']; ?></td>
                    <td>
                        <a href="event_edit.php?id=<?php echo $event['event_id']; ?>" class="btn btn-sm btn-warning">✏️ تعديل</a>
                        <a href="event_delete.php?id=<?php echo $event['event_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من الحذف؟');">🗑️ حذف</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include bess_url('footer', 'file'); ?>
