<?php
session_start();
include '../init/ini.php';

// ✅ جلب جميع الأحصنة
try {
    $stmt = $conn->query("SELECT * FROM horses ORDER BY horse_id DESC");
    $horses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("❌ خطأ في جلب الأحصنة: " . $e->getMessage());
}
?>

<?php include bess_url('header'); ?>
<?php include bess_url('navbar'); ?>
<?php include bess_url('sidebar'); ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h2>🐎 قائمة الأحصنة</h2>
            <a href="horse_add.php" class="btn btn-success">➕ إضافة حصان جديد</a>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped text-center">
                        <thead>
                            <tr>
                                <th>🆔 ID</th>
                                <th>🐴 اسم الحصان</th>
                                <th>🎂 العمر</th>
                                <th>👤 المالك</th>
                                <th>🏋️ المدرب</th>
                                <th>👨‍🦱 الجوكي</th>
                                <th>⚙️ الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($horses) > 0): ?>
                            <?php foreach ($horses as $h): ?>
                            <tr>
                                <td><?= $h['horse_id'] ?></td>
                                <td><?= htmlspecialchars($h['horse_name']) ?></td>
                                <td><?= $h['age'] ?></td>
                                <td><?= htmlspecialchars($h['owner']) ?></td>
                                <td><?= htmlspecialchars($h['trainer']) ?></td>
                                <td><?= htmlspecialchars($h['jockey']) ?></td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-secondary btn-sm dropdown-toggle" type="button"
                                            data-bs-toggle="dropdown">
                                            الإجراءات
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item"
                                                    href="horse_view.php?id=<?= $h['horse_id'] ?>">👁️ عرض</a></li>
                                            <li><a class="dropdown-item"
                                                    href="horse_edit.php?id=<?= $h['horse_id'] ?>">✏️ تعديل</a></li>
                                            <li>
                                                <button class="btn btn-danger btn-sm delete-horse"
                                                    data-id="<?= $row['horse_id'] ?>">
                                                    🗑 حذف
                                                </button>

                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="7">⚠️ لا توجد أحصنة مسجلة بعد</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include bess_url('footer'); ?>