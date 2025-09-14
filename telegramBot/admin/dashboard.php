<?php
session_start();
include 'init/ini.php';

// ✅ تحقق من تسجيل الدخول
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// 🔢 الإحصائيات العامة
try {
    $total_users   = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $total_races   = $conn->query("SELECT COUNT(*) FROM races")->fetchColumn();   // ✅ تعديل
    $total_horses  = $conn->query("SELECT COUNT(*) FROM horses")->fetchColumn();
    $total_bets    = $conn->query("SELECT COUNT(*) FROM horse_bets")->fetchColumn();
    $total_balance = $conn->query("SELECT COALESCE(SUM(currency),0) FROM users")->fetchColumn();

    // آخر 5 مستخدمين
    $last_users = $conn->query("
        SELECT user_id, username, currency, registration_date 
        FROM users 
        ORDER BY registration_date DESC 
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);

    // آخر 5 رهانات
    $last_bets = $conn->query("
        SELECT hb.bet_id, u.username, hb.total_stake, hb.potential_return, hb.status, hb.created_at
        FROM horse_bets hb
        JOIN users u ON hb.user_id = u.user_id
        ORDER BY hb.created_at DESC
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("❌ خطأ في جلب البيانات: " . $e->getMessage());
}

include bess_url('header','file');
include bess_url('navbar','file');
include bess_url('sidebar','file');
?>

<!-- المحتوى -->
<div class="content-wrapper">
    <!-- عنوان -->
    <section class="content-header">
        <div class="container-fluid">
            <h1>📊 لوحة التحكم</h1>
        </div>
    </section>

    <!-- البطاقات -->
    <section class="content">
        <div class="container-fluid">
            <div class="row text-center">
                <div class="col-lg-2 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?= $total_users ?></h3>
                            <p>👥 المستخدمين</p>
                        </div>
                        <div class="icon"><i class="fas fa-users"></i></div>
                        <a href="users/user_list.php" class="small-box-footer">عرض <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?= $total_races ?></h3>
                            <p>🏇 السباقات</p>
                        </div>
                        <div class="icon"><i class="fas fa-flag-checkered"></i></div>
                        <a href="races/race_list.php" class="small-box-footer">عرض <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?= $total_horses ?></h3>
                            <p>🐴 الأحصنة</p>
                        </div>
                        <div class="icon"><i class="fas fa-horse"></i></div>
                        <a href="horses/horse_list.php" class="small-box-footer">عرض <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?= $total_bets ?></h3>
                            <p>🎲 الرهانات</p>
                        </div>
                        <div class="icon"><i class="fas fa-coins"></i></div>
                        <a href="bets/bet_list.php" class="small-box-footer">عرض <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 col-12">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3><?= number_format($total_balance,2) ?> درهم</h3>
                            <p>💰 إجمالي أرصدة المستخدمين</p>
                        </div>
                        <div class="icon"><i class="fas fa-wallet"></i></div>
                    </div>
                </div>
            </div>

            <!-- الجداول -->
            <div class="row">
                <!-- آخر المستخدمين -->
                <div class="col-md-6">
                    <div class="card card-info">
                        <div class="card-header"><h3 class="card-title">🧑‍🤝‍🧑 آخر 5 مستخدمين</h3></div>
                        <div class="card-body table-responsive">
                            <table class="table table-bordered table-hover text-center align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>المستخدم</th>
                                        <th>الرصيد</th>
                                        <th>تاريخ التسجيل</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($last_users): foreach ($last_users as $u): ?>
                                    <tr>
                                        <td><?= $u['user_id'] ?></td>
                                        <td><?= htmlspecialchars($u['username']) ?></td>
                                        <td><?= number_format($u['currency'],2) ?> درهم</td>
                                        <td><?= $u['registration_date'] ?></td>
                                    </tr>
                                    <?php endforeach; else: ?>
                                    <tr><td colspan="4">لا يوجد مستخدمين</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- آخر الرهانات -->
                <div class="col-md-6">
                    <div class="card card-warning">
                        <div class="card-header"><h3 class="card-title">🎲 آخر 5 رهانات</h3></div>
                        <div class="card-body table-responsive">
                            <table class="table table-bordered table-hover text-center align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>المستخدم</th>
                                        <th>المبلغ</th>
                                        <th>العائد المحتمل</th>
                                        <th>الحالة</th>
                                        <th>التاريخ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($last_bets): foreach ($last_bets as $b): ?>
                                    <tr>
                                        <td><?= $b['bet_id'] ?></td>
                                        <td><?= htmlspecialchars($b['username']) ?></td>
                                        <td><?= number_format($b['total_stake'],2) ?> درهم</td>
                                        <td><?= number_format($b['potential_return'],2) ?> درهم</td>
                                        <td>
                                            <?php
                                            $statuses = [
                                                'pending'=>'⏳ انتظار',
                                                'won'=>'✅ فوز',
                                                'lost'=>'❌ خسارة',
                                                'cancelled'=>'🚫 ملغاة'
                                            ];
                                            echo $statuses[$b['status']] ?? $b['status'];
                                            ?>
                                        </td>
                                        <td><?= $b['created_at'] ?></td>
                                    </tr>
                                    <?php endforeach; else: ?>
                                    <tr><td colspan="6">لا يوجد رهانات</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- الرسوم البيانية -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-primary">
                        <div class="card-header"><h3 class="card-title">📈 نمو المستخدمين</h3></div>
                        <div class="card-body"><canvas id="usersChart"></canvas></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-success">
                        <div class="card-header"><h3 class="card-title">💵 توزيع الأرصدة</h3></div>
                        <div class="card-body"><canvas id="balanceChart"></canvas></div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<?php include bess_url('footer','file'); ?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
    // 📊 نمو المستخدمين
    new Chart(document.getElementById("usersChart"), {
        type: "line",
        data: {
            labels: ["يناير","فبراير","مارس","أبريل","مايو","يونيو","يوليو"],
            datasets: [{
                label: "عدد المستخدمين",
                data: [5, 10, 15, 20, 30, 40, <?= $total_users ?>],
                borderColor: "rgba(54, 162, 235, 1)",
                fill: false
            }]
        }
    });

    // 📊 توزيع الأرصدة
    new Chart(document.getElementById("balanceChart"), {
        type: "doughnut",
        data: {
            labels: ["رصيد المستخدمين", "النظام"],
            datasets: [{
                data: [<?= $total_balance ?>, 1000000 - <?= $total_balance ?>],
                backgroundColor: ["#28a745", "#dc3545"]
            }]
        }
    });
});
</script>
