<?php
session_start();
include '../init/ini.php';

// ✅ تحقق من تسجيل الأدمن
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// ✅ جلب ID الرهان
$bet_id = $_GET['id'] ?? 0;

// ✅ جلب تفاصيل الرهان
try {
    $stmt = $conn->prepare("
        SELECT b.bet_id, b.amount, b.odds, b.payout, b.status, b.created_at,
               u.user_id, u.username, u.email,
               r.race_id, r.race_number, r.meeting_code, r.location, r.start_time,
               h.horse_id, h.horse_name, rh.horse_number
        FROM bets b
        JOIN users u ON b.user_id = u.user_id
        JOIN race_horses rh ON b.race_horse_id = rh.race_horse_id
        JOIN races r ON rh.race_id = r.race_id
        JOIN horses h ON rh.horse_id = h.horse_id
        WHERE b.bet_id = :id
    ");
    $stmt->execute([':id' => $bet_id]);
    $bet = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$bet) {
        die("❌ الرهان غير موجود");
    }
} catch (PDOException $e) {
    die("⚠️ خطأ في جلب البيانات: " . $e->getMessage());
}

include bess_url('header','file');
include bess_url('navbar','file');
include bess_url('sidebar','file');
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h2>👁️ تفاصيل الرهان</h2>
            <a href="bet_list.php" class="btn btn-secondary">⬅️ رجوع</a>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">🎲 تفاصيل الرهان #<?= $bet['bet_id'] ?></h3>
                </div>
                <div class="card-body">

                    <h5>👤 معلومات المستخدم</h5>
                    <ul class="list-group mb-3">
                        <li class="list-group-item"><strong>اسم المستخدم:</strong>
                            <?= htmlspecialchars($bet['username']) ?></li>
                        <li class="list-group-item"><strong>البريد:</strong> <?= htmlspecialchars($bet['email']) ?></li>
                        <li class="list-group-item">
                            <a href="../users/user_view.php?id=<?= $bet['user_id'] ?>" class="btn btn-info btn-sm">👁️
                                عرض المستخدم</a>
                        </li>
                    </ul>

                    <h5>🏁 تفاصيل السباق</h5>
                    <ul class="list-group mb-3">
                        <li class="list-group-item"><strong>الاجتماع:</strong> <?= $bet['meeting_code'] ?></li>
                        <li class="list-group-item"><strong>رقم السباق:</strong> <?= $bet['race_number'] ?></li>
                        <li class="list-group-item"><strong>المكان:</strong> <?= htmlspecialchars($bet['location']) ?>
                        </li>
                        <li class="list-group-item"><strong>التوقيت:</strong>
                            <?= date('Y-m-d H:i', strtotime($bet['start_time'])) ?></li>
                        <li class="list-group-item">
                            <a href="../races/race_view.php?id=<?= $bet['race_id'] ?>" class="btn btn-info btn-sm">👁️
                                عرض السباق</a>
                        </li>
                    </ul>

                    <h5>🐎 الحصان</h5>
                    <ul class="list-group mb-3">
                        <li class="list-group-item"><strong>رقم الحصان:</strong> <?= $bet['horse_number'] ?></li>
                        <li class="list-group-item"><strong>اسم الحصان:</strong>
                            <?= htmlspecialchars($bet['horse_name']) ?></li>
                        <li class="list-group-item">
                            <a href="../horses/horse_view.php?id=<?= $bet['horse_id'] ?>"
                                class="btn btn-info btn-sm">👁️ عرض الحصان</a>
                        </li>
                    </ul>

                    <h5>💵 تفاصيل الرهان</h5>
                    <ul class="list-group mb-3">
                        <li class="list-group-item"><strong>المبلغ:</strong> <?= number_format($bet['amount'],2) ?> درهم
                        </li>
                        <li class="list-group-item"><strong>الاحتمال:</strong> <?= $bet['odds'] ?></li>
                        <li class="list-group-item"><strong>العائد المتوقع:</strong>
                            <?= number_format($bet['payout'],2) ?> درهم</li>
                        <li class="list-group-item"><strong>الحالة:</strong>
                            <?php if ($bet['status'] == 'pending'): ?>
                            ⏳ قيد الانتظار
                            <?php elseif ($bet['status'] == 'won'): ?>
                            ✅ فاز
                            <?php else: ?>
                            ❌ خسر
                            <?php endif; ?>
                        </li>
                        <li class="list-group-item"><strong>📅 تاريخ الإنشاء:</strong> <?= $bet['created_at'] ?></li>
                    </ul>

                </div>
                <div class="card-footer text-right">
                    <a href="bet_edit.php?id=<?= $bet['bet_id'] ?>" class="btn btn-warning">✏️ تعديل</a>
                    <a href="bet_list.php" class="btn btn-secondary">⬅️ رجوع</a>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include bess_url('footer','file'); ?>