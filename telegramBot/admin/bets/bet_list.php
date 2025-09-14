<?php
session_start();
include '../init/ini.php';

// ✅ تحقق من تسجيل الأدمن
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// ✅ جلب قائمة الرهانات مع المستخدمين والسباقات
$stmt = $conn->prepare("
    SELECT b.bet_id, b.user_id, b.race_id, b.bet_type, b.bet_numbers, b.stake, b.potential_payout, b.bet_date,
           u.username, u.first_name, u.last_name,
           r.race_number, r.meeting_code, r.location, r.start_time
    FROM horse_bets b
    JOIN users u ON b.user_id = u.user_id
    JOIN races r ON b.race_id = r.race_id
    ORDER BY b.bet_date DESC
");
$stmt->execute();
$bets = $stmt->fetchAll(PDO::FETCH_ASSOC);

include bess_url('header','file');
include bess_url('navbar','file');
include bess_url('sidebar','file');
?>

<div class="content-wrapper">
  <!-- العنوان -->
  <section class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
      <h2>📋 قائمة الرهانات</h2>
    </div>
  </section>

  <!-- المحتوى -->
  <section class="content">
    <div class="container-fluid">
      <div class="card shadow">
        <div class="card-header bg-primary text-white">
          <h3 class="card-title">📊 جميع الرهانات</h3>
        </div>
        <div class="card-body table-responsive">
          <table class="table table-bordered table-striped text-center align-middle">
            <thead class="thead-dark">
              <tr>
                <th>#</th>
                <th>👤 المستخدم</th>
                <th>🏁 السباق</th>
                <th>🎯 نوع الرهان</th>
                <th>🐎 الأرقام</th>
                <th>💵 المبلغ</th>
                <th>💰 العائد المحتمل</th>
                <th>⏰ التاريخ</th>
                <th>⚙️ الإجراءات</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($bets): ?>
                <?php foreach ($bets as $i => $bet): ?>
                  <tr>
                    <td><?= $i+1 ?></td>
                    <td>
                      <?= htmlspecialchars($bet['username']) ?><br>
                      <small><?= htmlspecialchars($bet['first_name'] . " " . $bet['last_name']) ?></small>
                    </td>
                    <td>
                      اجتماع: <?= htmlspecialchars($bet['meeting_code']) ?><br>
                      سباق #<?= htmlspecialchars($bet['race_number']) ?><br>
                      <small><?= date("Y-m-d H:i", strtotime($bet['start_time'])) ?> | <?= htmlspecialchars($bet['location']) ?></small>
                    </td>
                    <td><?= strtoupper($bet['bet_type']) ?></td>
                    <td><?= htmlspecialchars($bet['bet_numbers']) ?></td>
                    <td><?= number_format($bet['stake'], 2) ?> درهم</td>
                    <td><?= number_format($bet['potential_payout'], 2) ?> درهم</td>
                    <td><?= $bet['bet_date'] ?></td>
                    <td>
                      <a href="bet_view.php?id=<?= $bet['bet_id'] ?>" class="btn btn-info btn-sm">👁 عرض</a>
                      <button class="btn btn-danger btn-sm delete-bet" data-id="<?= $bet['bet_id'] ?>">🗑 حذف</button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="9" class="text-muted">⚠️ لا توجد رهانات مسجلة بعد</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</div>

<?php include bess_url('footer','file'); ?>

