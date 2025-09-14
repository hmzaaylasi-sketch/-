<?php
session_start();
include '../init/ini.php';

// تأكد أن الأدمن مسجل دخول
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// جلب المستخدمين
$stmt = $conn->query("SELECT user_id, username, phone, currency, registration_date, status 
                      FROM users ORDER BY user_id DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include bess_url('header','file'); ?>
<?php include bess_url('navbar','file'); ?>
<?php include bess_url('sidebar','file'); ?>

<!-- محتوى الصفحة -->
<div class="content-wrapper">
    <!-- عنوان الصفحة -->
    <section class="content-header">
        <div class="container-fluid">
            <h1>👥 إدارة المستخدمين</h1>
        </div>
    </section>

    <!-- المحتوى -->
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">قائمة جميع المستخدمين</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped text-center">
                        <thead class="table-dark">
                            <tr>
                                <th>ID (Telegram)</th>
                                <th>👤 اسم المستخدم</th>
                                <th>📱 رقم الهاتف</th>
                                <th>💰 الرصيد</th>
                                <th>📅 تاريخ التسجيل</th>
                                <th>⚙️ الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($users) > 0): ?>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <p onclick="copyuserID()" style="cursor: pointer;"><?= $user['user_id'] ?></p>
                                    <input type="text" id="userID" value="<?= $user['user_id'] ?>"
                                        style="display: none;">
                                </td>
                                <td>
                                    <a href="user_view.php?id=<?= $user['user_id'] ?>">
                                        <?= htmlspecialchars($user['username']) ?>
                                    </a>
                                </td>
                                <td><?= $user['phone'] ?? '-' ?></td>
                                <td><?= number_format($user['currency'], 2) ?> درهم</td>
                                <td><?= $user['registration_date'] ?></td>
                                <td>
                                    <div class="dropdown d-inline">
                                        <button class="btn btn-secondary btn-sm dropdown-toggle" type="button"
                                            data-toggle="dropdown" aria-expanded="false">
                                            الإجراءات
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right shadow">
                                            <a class="dropdown-item" href="user_view.php?id=<?= $user['user_id'] ?>">
                                                <i class="fas fa-eye text-info"></i> عرض
                                            </a>
                                            <a class="dropdown-item" href="user_edit.php?id=<?= $user['user_id'] ?>">
                                                <i class="fas fa-edit text-warning"></i> تعديل
                                            </a>
                                            <button class="dropdown-item text-danger delete-user"
                                                data-id="<?= $user['user_id'] ?>">
                                                <i class="fas fa-trash-alt"></i> حذف
                                            </button>
                                            <button
                                                class="dropdown-item toggle-user <?= $user['status']=='active'?'text-success':'text-warning' ?>"
                                                data-id="<?= $user['user_id'] ?>" data-status="<?= $user['status'] ?>">
                                                <i class="fas fa-toggle-on"></i>
                                                <?= $user['status']=='active'?'تعطيل':'تفعيل' ?>
                                            </button>
                                            <a class="dropdown-item" href="user_message.php?id=<?= $user['user_id'] ?>">
                                                <i class="fas fa-envelope text-primary"></i> مراسلة
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">⚠️ لا يوجد مستخدمين حالياً</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </section>
</div>

<?php include bess_url('footer','file'); ?>