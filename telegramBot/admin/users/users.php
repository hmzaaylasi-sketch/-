<?php
include 'init/ini.php';

// جلب المستخدمين
$stmt = $conn->query("SELECT * FROM users ORDER BY id DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

include bess_url('header', 'file');
include bess_url('navbar', 'file');
include bess_url('sidebar', 'file');
?>

<div class="content-wrapper p-4">
    <div class="container-fluid">
        <h1 class="mb-4">👥 إدارة المستخدمين</h1>

        <a href="user_add.php" class="btn btn-primary mb-3">➕ إضافة مستخدم</a>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>الاسم</th>
                    <th>البريد</th>
                    <th>تاريخ الإنشاء</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo $user['created_at']; ?></td>
                    <td>
                        <a href="user_edit.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-warning">✏️ تعديل</a>
                        <a href="user_delete.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من الحذف؟');">🗑️ حذف</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include bess_url('footer', 'file'); ?>
