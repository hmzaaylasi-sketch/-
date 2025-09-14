<?php
session_start();
include '../init/ini.php';

$user_id = $_GET['id'] ?? 0;
if (!$user_id) {
    die("مستخدم غير موجود");
}

try {
    // جلب بيانات المستخدم
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = :id");
    $stmt->execute([':id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("⚠️ المستخدم غير موجود.");
    }

    // عدد الرهانات + المجموع
    $stmt = $conn->prepare("SELECT COUNT(*) AS total_bets, SUM(stake) AS total_stake, SUM(potential_payout) AS total_payout 
                            FROM bets WHERE user_id = :id");
    $stmt->execute([':id' => $user_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // آخر 5 رهانات
    $stmt = $conn->prepare("SELECT b.bet_id, b.stake, b.potential_payout, b.bet_date, o.outcome_name 
                            FROM bets b 
                            JOIN outcomes o ON b.outcome_id = o.outcome_id 
                            WHERE b.user_id = :id 
                            ORDER BY b.bet_date DESC LIMIT 5");
    $stmt->execute([':id' => $user_id]);
    $bets = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("خطأ: " . $e->getMessage());
}

// 🔹 جلب الإحالات الخاصة بالمستخدم
$stmt = $conn->prepare("SELECT u.user_id, u.username, r.referral_date, r.bonus
                        FROM referrals r
                        JOIN users u ON r.referred_id = u.user_id
                        WHERE r.referrer_id = :id
                        ORDER BY r.referral_date DESC");
$stmt->execute([':id' => $user_id]);
$referrals = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<?php include bess_url('header'); ?>
<?php include bess_url('navbar'); ?>
<?php include bess_url('sidebar'); ?>

<div class="container-fluid px-4 mt-4">
    <h2 class="mb-4">بروفايل المستخدم: <?php echo htmlspecialchars($user['username']); ?></h2>

    <div class="row g-4">
        <!-- معلومات أساسية -->
        <div class="col-md-4">
            <div class="card border-primary">
                <div class="card-body">
                    <h5 class="card-title">🆔 معلومات المستخدم</h5>
                    <p><strong>ID:</strong> <?php echo $user['user_id']; ?></p>
                    <p><strong>الاسم:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                    <p><strong>الرصيد:</strong> <?php echo number_format($user['currency'], 2); ?> درهم</p>
                    <p><strong>تاريخ التسجيل:</strong> <?php echo $user['registration_date']; ?></p>
                </div>
            </div>
        </div>

        <!-- إحصائيات -->
        <div class="col-md-8">
            <div class="card border-success">
                <div class="card-body">
                    <h5 class="card-title">📊 إحصائيات الرهانات</h5>
                    <p><strong>عدد الرهانات:</strong> <?php echo $stats['total_bets'] ?? 0; ?></p>
                    <p><strong>مجموع المبالغ:</strong> <?php echo number_format($stats['total_stake'] ?? 0, 2); ?> درهم
                    </p>
                    <p><strong>العائد الكلي:</strong> <?php echo number_format($stats['total_payout'] ?? 0, 2); ?> درهم
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- آخر الرهانات -->
    <div class="card mt-4">
        <div class="card-body">
            <h5 class="card-title">📝 آخر 5 رهانات</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-striped text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>ID الرهان</th>
                            <th>الخيار</th>
                            <th>المبلغ</th>
                            <th>العائد المحتمل</th>
                            <th>تاريخ الرهان</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($bets) > 0): ?>
                        <?php foreach ($bets as $bet): ?>
                        <tr>
                            <td><?php echo $bet['bet_id']; ?></td>
                            <td><?php echo htmlspecialchars($bet['outcome_name']); ?></td>
                            <td><?php echo number_format($bet['stake'], 2); ?> درهم</td>
                            <td><?php echo number_format($bet['potential_payout'], 2); ?> درهم</td>
                            <td><?php echo $bet['bet_date']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="5">لا يوجد رهانات</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- جدول الإحالات -->
    <div class="card mt-4">
        <div class="card-body">
            <h5 class="card-title">🤝 الإحالات</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-striped text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>اسم المستخدم</th>
                            <th>تاريخ التسجيل عبر الإحالة</th>
                            <th>المكافأة</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($referrals) > 0): ?>
                        <?php foreach ($referrals as $ref): ?>
                        <tr>
                            <td><?php echo $ref['user_id']; ?></td>
                            <td><?php echo htmlspecialchars($ref['username']); ?></td>
                            <td><?php echo $ref['referral_date']; ?></td>
                            <td><?php echo number_format($ref['bonus'], 2); ?> درهم</td>
                        </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="4">لا يوجد إحالات</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php include bess_url('footer'); ?>