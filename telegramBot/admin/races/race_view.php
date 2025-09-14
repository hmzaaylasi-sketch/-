<?php
session_start();
include '../init/ini.php';

// التحقق من وجود معرف السباق
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: " . bess_url('races/race_list', 'url'));
    exit();
}

$race_id = intval($_GET['id']);

// جلب بيانات السباق
try {
    $race_stmt = $conn->prepare("
        SELECT r.*, COUNT(re.entry_id) as horses_count,
               COUNT(hb.bet_id) as active_bets,
               COALESCE(SUM(hb.total_stake), 0) as total_bets_amount
        FROM races r 
        LEFT JOIN race_entries re ON r.race_id = re.race_id 
        LEFT JOIN horse_bets hb ON r.race_id = hb.race_id AND hb.status = 'pending'
        WHERE r.race_id = :race_id 
        GROUP BY r.race_id
    ");
    $race_stmt->bindParam(":race_id", $race_id);
    $race_stmt->execute();
    $race = $race_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$race) {
        $_SESSION['toast'] = [
            'type' => 'error',
            'message' => '❌ السباق غير موجود'
        ];
        header("Location: " . bess_url('races/race_list', 'url'));
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['toast'] = [
        'type' => 'error',
        'message' => '❌ خطأ في جلب بيانات السباق: ' . $e->getMessage()
    ];
    header("Location: " . bess_url('races/race_list', 'url'));
    exit();
}

// جلب الأحصنة المشاركة في السباق
try {
    $horses_stmt = $conn->prepare("
        SELECT re.*, h.horse_id, h.age, h.color, h.gender
        FROM race_entries re 
        LEFT JOIN horses h ON re.horse_name = h.horse_name 
        WHERE re.race_id = :race_id 
        ORDER BY re.horse_number
    ");
    $horses_stmt->bindParam(":race_id", $race_id);
    $horses_stmt->execute();
    $horses = $horses_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $horses = [];
}

// جلب جميع الأحصنة المتاحة للإضافة
try {
    $all_horses_stmt = $conn->prepare("
        SELECT horse_id, horse_name, age, jockey, trainer, owner 
        FROM horses 
        WHERE horse_id NOT IN (
            SELECT h.horse_id 
            FROM horses h 
            INNER JOIN race_entries re ON h.horse_name = re.horse_name 
            WHERE re.race_id = :race_id
        )
        ORDER BY horse_name
    ");
    $all_horses_stmt->bindParam(":race_id", $race_id);
    $all_horses_stmt->execute();
    $all_horses = $all_horses_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $all_horses = [];
}

// معالجة إضافة الحصان
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_horse'])) {
    try {
        $horse_id = intval($_POST['horse_id']);
        
        if ($horse_id > 0) {
            // جلب بيانات الحصان
            $horse_stmt = $conn->prepare("
                SELECT horse_name, jockey, trainer, owner 
                FROM horses WHERE horse_id = :horse_id
            ");
            $horse_stmt->bindParam(":horse_id", $horse_id);
            $horse_stmt->execute();
            $horse = $horse_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($horse) {
                // تحديد رقم الحصان التالي
                $number_stmt = $conn->prepare("
                    SELECT MAX(horse_number) as max_number 
                    FROM race_entries 
                    WHERE race_id = :race_id
                ");
                $number_stmt->bindParam(":race_id", $race_id);
                $number_stmt->execute();
                $max_number = $number_stmt->fetch(PDO::FETCH_ASSOC)['max_number'] ?? 0;
                $next_number = $max_number + 1;
                
                // إضافة الحصان للسباق
                $add_stmt = $conn->prepare("
                    INSERT INTO race_entries 
                    (race_id, horse_number, horse_name, jockey, trainer, owner, status) 
                    VALUES (:race_id, :horse_number, :horse_name, :jockey, :trainer, :owner, 'scheduled')
                ");
                
                $add_stmt->bindParam(":race_id", $race_id);
                $add_stmt->bindParam(":horse_number", $next_number);
                $add_stmt->bindParam(":horse_name", $horse['horse_name']);
                $add_stmt->bindParam(":jockey", $horse['jockey']);
                $add_stmt->bindParam(":trainer", $horse['trainer']);
                $add_stmt->bindParam(":owner", $horse['owner']);
                
                if ($add_stmt->execute()) {
                    $_SESSION['toast'] = [
                        'type' => 'success',
                        'message' => '✅ تم إضافة الحصان بنجاح'
                    ];
                    header("Location: ?id=" . $race_id);
                    exit();
                }
            }
        }
    } catch (PDOException $e) {
        $_SESSION['toast'] = [
            'type' => 'error',
            'message' => '❌ خطأ في إضافة الحصان: ' . $e->getMessage()
        ];
    }
}

include bess_url('header', 'file');
include bess_url('navbar', 'file');
include bess_url('sidebar', 'file');
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>🏇 تفاصيل السباق: <?= htmlspecialchars($race['meeting_code'] . $race['race_number']) ?></h1>
                </div>
                <div class="col-sm-6 text-left">
                    <a href="<?= bess_url('races/race_list', 'url') ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> رجوع للقائمة
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- معلومات السباق -->
                <div class="col-md-4">
                    <div class="card card-info">
                        <div class="card-header">
                            <!-- سيكون هكدة -- -- -- -- -- قبل/ لاحصنة لتي سبقت بي ترتيب السباق -->
                            <h3 class="card-title">5 - 8 - 15 - 7</h3>
                        </div>
                    </div>

                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title">📋 معلومات السباق</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <tr>
                                    <th>السباق</th>
                                    <td><?= htmlspecialchars($race['meeting_code'] . $race['race_number']) ?></td>
                                </tr>
                                <tr>
                                    <th>اسم السباق</th>
                                    <td><?= htmlspecialchars($race['race_name'] ?? 'غير محدد') ?></td>
                                </tr>
                                <tr>
                                    <th>📍 المكان</th>
                                    <td><?= htmlspecialchars($race['location']) ?></td>
                                </tr>
                                <tr>
                                    <th>⏰ التوقيت</th>
                                    <td><?= date('Y-m-d H:i', strtotime($race['start_time'])) ?></td>
                                </tr>
                                <tr>
                                    <th>📊 الحالة</th>
                                    <td>
                                        <?php
                                        $statuses = [
                                            'upcoming' => '🟢 قادم',
                                            'running' => '🟠 جاري',
                                            'finished' => '🔵 منتهي',
                                            'cancelled' => '🔴 ملغى'
                                        ];
                                        echo $statuses[$race['status']] ?? $race['status'];
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>📏 المسافة</th>
                                    <td><?= $race['distance'] ? $race['distance'] . ' متر' : 'غير محدد' ?></td>
                                </tr>
                                <tr>
                                    <th>💰 الجائزة</th>
                                    <td><?= number_format($race['prize_pool'] ?? 0, 2) ?> درهم</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- إحصائيات -->
                    <div class="card card-success mt-3">
                        <div class="card-header">
                            <h3 class="card-title">📊 إحصائيات</h3>
                        </div>
                        <div class="card-body">
                            <div class="text-center">
                                <div class="mb-3">
                                    <h4><?= $race['horses_count'] ?></h4>
                                    <small>عدد الأحصنة</small>
                                </div>
                                <div class="mb-3">
                                    <h4><?= $race['active_bets'] ?></h4>
                                    <small>الرهانات النشطة</small>
                                </div>
                                <div>
                                    <h4><?= number_format($race['total_bets_amount'], 2) ?> ريال</h4>
                                    <small>إجمالي المبلغ</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- الأحصنة المشاركة -->
                <div class="col-md-8">
                    <div class="card card-warning">
                        <div class="card-header">
                            <h3 class="card-title">🐎 الأحصنة المشاركة</h3>
                            <div class="card-tools">
                                <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addHorseModal">
                                    <i class="fas fa-plus"></i> إضافة حصان
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if ($horses && count($horses) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>اسم الحصان</th>
                                            <th>الفارس</th>
                                            <th>المدرب</th>
                                            <th>المالك</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($horses as $horse): ?>
                                        <tr>
                                            <td><?= $horse['horse_number'] ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($horse['horse_name']) ?></strong>
                                                <?php if ($horse['age']): ?>
                                                <br><small>العمر: <?= $horse['age'] ?> سنة</small>
                                                <?php endif; ?>
                                                <?php if ($horse['gender']): ?>
                                                <br><small>الجنس: <?= $horse['gender'] ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($horse['jockey']) ?></td>
                                            <td><?= htmlspecialchars($horse['trainer']) ?></td>
                                            <td><?= htmlspecialchars($horse['owner']) ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-danger remove-horse"
                                                    data-id="<?= $horse['entry_id'] ?>">
                                                    <i class="fas fa-times"></i> إزالة
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle"></i>
                                لا توجد أحصنة مضافة لهذا السباق
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- إدارة السباق -->
                    <div class="card card-primary mt-3">
                        <div class="card-header">
                            <h3 class="card-title">⚙️ إدارة السباق</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php if ($race['status'] === 'upcoming'): ?>
                                <div class="col-md-4">
                                    <button class="btn btn-success btn-block" id="startRace">
                                        <i class="fas fa-play"></i> بدء السباق
                                    </button>
                                </div>
                                <?php endif; ?>

                                <?php if ($race['status'] === 'running'): ?>
                                <div class="col-md-4">
                                    <button class="btn btn-warning btn-block" id="finishRace" data-toggle="modal"
                                        data-target="#finishRaceModal">
                                        <i class="fas fa-flag-checkered"></i> إنهاء السباق
                                    </button>
                                </div>
                                <?php endif; ?>

                                <?php if ($race['status'] === 'upcoming' || $race['status'] === 'running'): ?>
                                <div class="col-md-4">
                                    <button class="btn btn-danger btn-block" id="cancelRace">
                                        <i class="fas fa-times"></i> إلغاء السباق
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- مودال إضافة حصان -->
<div class="modal fade" id="addHorseModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">➕ إضافة حصان للسباق</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label>اختر الحصان</label>
                        <select class="form-control" name="horse_id" required>
                            <option value="">اختر الحصان</option>
                            <?php foreach ($all_horses as $horse): ?>
                            <option value="<?= $horse['horse_id'] ?>">
                                <?= htmlspecialchars($horse['horse_name']) ?>
                                (الفارس: <?= htmlspecialchars($horse['jockey']) ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                    <button type="submit" name="add_horse" class="btn btn-primary">إضافة</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- مودال إنهاء السباق -->
<div class="modal fade" id="finishRaceModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">🏁 إنهاء السباق</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="finishRaceForm">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        قم بتحديد المراكز النهائية للأحصنة المشاركة في السباق
                    </div>

                    <div class="form-group">
                        <label>ترتيب الفائزين (من الأول إلى الأخير)</label>
                        <small class="text-muted d-block">اسحب وأفلت الأحصنة لتحديد الترتيب</small>

                        <ul id="horsesSortable" class="list-group">
                            <?php if ($horses && count($horses) > 0): ?>
                            <?php foreach ($horses as $horse): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center"
                                data-entry-id="<?= $horse['entry_id'] ?>">
                                <div>
                                    <strong>#<?= $horse['horse_number'] ?> -
                                        <?= htmlspecialchars($horse['horse_name']) ?></strong>
                                    <br>
                                    <small class="text-muted">الفارس: <?= htmlspecialchars($horse['jockey']) ?></small>
                                </div>
                                <span class="badge badge-primary badge-pill">
                                    <i class="fas fa-bars"></i>
                                </span>
                            </li>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <li class="list-group-item text-center text-muted">
                                لا توجد أحصنة في هذا السباق
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <div class="form-group">
                        <label>وقت السباق (ثواني)</label>
                        <input type="number" class="form-control" name="race_time" step="0.01"
                            placeholder="أدخل وقت السباق بالثواني" required>
                    </div>

                    <div class="form-group">
                        <label>ملاحظات</label>
                        <textarea class="form-control" name="race_notes" placeholder="ملاحظات إضافية عن السباق"
                            rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-flag-checkered"></i> تأكيد إنهاء السباق
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>



<?php include bess_url('footer', 'file'); ?>



<!-- في نهاية race_view.php -->
<script>
// تهيئة أحداث السباق عندما تكون الصفحة جاهزة
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const raceId = urlParams.get('id');

    if (raceId && typeof initializeRaceEvents === 'function') {
        initializeRaceEvents(parseInt(raceId));
    }
});
</script>