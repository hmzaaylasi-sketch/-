<?php
session_start();
include '../init/ini.php';

$user_id = $_GET['id'] ?? 0;
if (!$user_id) {
    die("⚠️ المستخدم غير موجود.");
}

try {
    // ✅ بيانات المستخدم
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = :id");
    $stmt->execute([':id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("⚠️ المستخدم غير موجود.");
    }

    $referral_link = "http://localhost/telegram/register.php?ref=";

    // ✅ إحصائيات الرهانات
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS total_bets, 
               COALESCE(SUM(stake),0) AS total_stake, 
               COALESCE(SUM(potential_payout),0) AS total_payout 
        FROM horse_bets WHERE user_id = :id
    ");
    $stmt->execute([':id' => $user_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // ✅ آخر 10 رهانات
    $stmt = $conn->prepare("
        SELECT b.bet_id, b.stake, b.potential_payout, b.bet_date, b.status, 
               r.meeting_code, r.race_number, r.location, r.start_time
        FROM horse_bets b
        JOIN races r ON b.race_id = r.race_id
        WHERE b.user_id = :id
        ORDER BY b.bet_date DESC
        LIMIT 10
    ");
    $stmt->execute([':id' => $user_id]);
    $bets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ✅ بيانات الإحالات
    $stmt = $conn->prepare("
        SELECT u.user_id, u.username, r.referral_date, r.bonus
        FROM referrals r
        JOIN users u ON r.referred_id = u.user_id
        WHERE r.referrer_id = :id
        ORDER BY r.referral_date DESC
    ");
    $stmt->execute([':id' => $user_id]);
    $referrals = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("❌ خطأ في قاعدة البيانات: " . $e->getMessage());
}

include bess_url('header','file');
include bess_url('navbar','file');
include bess_url('sidebar','file');
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <h1>👤 معلومات المستخدم</h1>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">

            <div class="row g-4">
                <!-- معلومات -->
                <div class="col-md-4">
                    <div class="card card-primary">
                        <div class="card-body">
                            <h5 class="card-title">🆔 معلومات</h5>
                            <p onclick="copyuserID()" style="cursor: pointer;">ID: <?= $user['user_id'] ?></p>
                            <input type="text" id="userID" value="<?= $user['user_id'] ?>" style="display:none;">
                            <p><b>الرصيد:</b> <?= number_format($user['currency'], 2) ?> درهم</p>
                            <p><b>تاريخ التسجيل:</b> <?= $user['registration_date'] ?></p>
                            <div class="input-group">
                                <input type="text" id="refLink" class="form-control" value="<?= $referral_link ?>" readonly>
                                <button class="btn btn-outline-light bg-dark" onclick="copyReferral()">📋 نسخ</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- إحصائيات -->
                <div class="col-md-8">
                    <div class="card card-success">
                        <div class="card-body">
                            <h5 class="card-title">📊 إحصائيات الرهانات</h5>
                            <p><b>عدد الرهانات:</b> <?= $stats['total_bets'] ?></p>
                            <p><b>مجموع المبالغ:</b> <?= number_format($stats['total_stake'], 2) ?> درهم</p>
                            <p><b>العائد الكلي:</b> <?= number_format($stats['total_payout'], 2) ?> درهم</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- الرهانات -->
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title">📝 آخر 10 رهانات</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered text-center">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>🏁 السباق</th>
                                    <th>💵 المبلغ</th>
                                    <th>💰 العائد المحتمل</th>
                                    <th>الحالة</th>
                                    <th>⏰ تاريخ</th>
                                    <th>⚙️</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($bets): foreach ($bets as $bet): ?>
                                <tr>
                                    <td><?= $bet['bet_id'] ?></td>
                                    <td>
                                        <?= $bet['meeting_code'] ?><?= $bet['race_number'] ?><br>
                                    </td>
                                    <td><?= number_format($bet['stake'], 2) ?> درهم</td>
                                    <td><?= number_format($bet['potential_payout'], 2) ?> درهم</td>
                                    <td>
                                        <?php 
                                            $statusColors = [
                                                'pending' => 'badge bg-warning',
                                                'won' => 'badge bg-success',
                                                'lost' => 'badge bg-danger'
                                            ];
                                        ?>
                                        <span class="<?= $statusColors[$bet['status']] ?? 'badge bg-secondary' ?>">
                                            <?= $bet['status'] ?>
                                        </span>
                                    </td>
                                    <td><?= $bet['bet_date'] ?></td>
                                    <td>
                                        <button class="btn btn-info btn-sm view-bet" data-id="<?= $bet['bet_id'] ?>">👁  <?= $bet['meeting_code'] ?><?= $bet['race_number'] ?></button>
                                    </td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="7">لا يوجد رهانات</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- الإحالات -->
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title">👥 قائمة الإحالات</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered text-center">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>👤 المستخدم</th>
                                    <th>⏰ تاريخ</th>
                                    <th>💎 المكافأة</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($referrals): foreach ($referrals as $ref): ?>
                                <tr>
                                    <td><?= $ref['user_id'] ?></td>
                                    <td><?= htmlspecialchars($ref['username']) ?></td>
                                    <td><?= $ref['referral_date'] ?></td>
                                    <td><?= number_format($ref['bonus'], 2) ?> درهم</td>
                                </tr>
                                <?php endforeach; else: ?>
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
    </section>
</div>

<!-- ✅ مودال واحد -->
<div class="modal fade" id="betDetailsModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">🎯 تفاصيل الرهان</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <div id="betDetailsContent">⏳ اختر رهانًا لعرض التفاصيل...</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">إغلاق</button>
      </div>
    </div>
  </div>
</div>

<?php include bess_url('footer','file'); ?>

<script>
document.querySelectorAll(".view-bet").forEach(btn => {
  btn.addEventListener("click", function() {
    let betId = this.dataset.id;
    let modal = $("#betDetailsModal");
    let content = document.getElementById("betDetailsContent");

    content.innerHTML = "⏳ جاري تحميل البيانات...";
    modal.modal("show");

    fetch("<?= bess_url_v2('api/bets/bet_details','php') ?>?id=" + betId)
      .then(res => res.json())
      .then(data => {
        if (data.status === "success") {
          let bet = data.bet;
          let race = data.race;
          let horses = data.horses;

          content.innerHTML = `
            <h6>🎲 تفاصيل الرهان</h6>
            <p><b>ID:</b> ${bet.bet_id}</p>
            <p><b>النوع:</b> ${bet.bet_type}</p>
            <p><b>الأرقام:</b> ${bet.bet_numbers}</p>
            <p><b>المبلغ:</b> ${bet.stake} درهم</p>
            <p><b>العائد المحتمل:</b> ${bet.potential_payout} درهم</p>
            <hr>
            <h6>🏁 تفاصيل السباق</h6>
            <p><b>الكود:</b> ${race.meeting_code}</p>
            <p><b>السباق:</b> ${race.race_number}</p>
            <p><b>الأرقام:</b> لارقام نتيجة نطورها لاحقا</p>
            <p><b>المكان:</b> ${race.location}</p>
            <p><b>التوقيت:</b> ${race.start_time}</p>
            <p><b>الحالة:</b> ${race.status}</p>
            <hr>
            <h6>🐎 الأحصنة المشاركة</h6>
            ${horses.length ? `
              <table class="table table-sm table-bordered text-center">
                <thead class="thead-light">
                  <tr><th>#</th><th>الاسم</th><th>الفارس</th><th>المدرب</th></tr>
                </thead>
                <tbody>
                  ${horses.map(h => `
                    <tr>
                      <td>${h.horse_number}</td>
                      <td>${h.horse_name}</td>
                      <td>${h.jockey}</td>
                      <td>${h.trainer}</td>
                    </tr>`).join("")}
                </tbody>
              </table>
            ` : `<p class="text-muted">❌ لا توجد بيانات أحصنة</p>`}
          `;
        } else {
          showErrorModal("❌ خطأ", {
            message: data.message || "فشل جلب التفاصيل",
            responseJson: data,
            requestUrl: "api/bets/bet_details.php?id=" + betId
          });
        }
      })
      .catch(err => {
        showErrorModal("⚠️ خطأ في الاتصال", {
          message: err.message || String(err),
          error: err,
          requestUrl: "api/bets/bet_details.php?id=" + betId
        });
      });
  });
});
</script>
