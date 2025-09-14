<?php
include '../init/ini.php';

// 🕒 التاريخ الافتراضي
$dateFilter = isset($_POST['date']) && $_POST['date'] 
    ? $_POST['date'] 
    : (isset($_GET['date']) && $_GET['date'] ? $_GET['date'] : date('Y-m-d'));

// ⏰ الساعات
$hourStart = isset($_POST['hourStart']) ? (int)$_POST['hourStart'] : 0;
$hourEnd   = isset($_POST['hourEnd'])   ? (int)$_POST['hourEnd']   : 24;

// ✅ جلب السباقات مع الأحصنة
$stmt = $conn->prepare("
    SELECT r.race_id, r.meeting_code, r.race_number, r.location, r.start_time, r.status,
           re.horse_number, re.final_position,
           h.horse_id, h.horse_name, h.jockey, h.trainer
    FROM races r
    LEFT JOIN race_entries re ON r.race_id = re.race_id
    LEFT JOIN horses h ON re.horse_id = h.horse_id
    WHERE DATE(r.start_time) = :dateFilter
      AND HOUR(r.start_time) BETWEEN :hourStart AND :hourEnd
    ORDER BY r.meeting_code, r.start_time ASC, re.horse_number ASC
");
$stmt->execute([
    ':dateFilter' => $dateFilter,
    ':hourStart'  => $hourStart,
    ':hourEnd'    => $hourEnd
]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ ترتيب السباقات
$races = [];
foreach ($rows as $row) {
    $race_id = $row['race_id'];
    if (!isset($races[$race_id])) {
        $races[$race_id] = [
            'race_id' => $row['race_id'],
            'meeting_code' => $row['meeting_code'],
            'race_number' => $row['race_number'],
            'location' => $row['location'],
            'start_time' => $row['start_time'],
            'status' => $row['status'],
            'horses' => []
        ];
    }
    if ($row['horse_id']) {
        $races[$race_id]['horses'][] = [
            'id' => $row['horse_id'],
            'number' => $row['horse_number'],
            'name' => $row['horse_name'],
            'jockey' => $row['jockey'],
            'trainer' => $row['trainer'],
            'position' => $row['final_position']
        ];
    }
}

// ✅ ترتيب حسب الاجتماع
$meetings = [];
foreach ($races as $race) {
    $meetings[$race['meeting_code']][] = $race;
}

// ✅ إنشاء مصفوفة الساعات للعرض
$hours = range($hourStart, $hourEnd);

include bess_url('header', 'file');
include bess_url('navbar', 'file');
include bess_url('sidebar', 'file');
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>🏁 جدول السباقات - <?= htmlspecialchars($dateFilter) ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="index.php">الرئيسية</a></li>
                        <li class="breadcrumb-item active">جدول السباقات</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- ✅ نموذج البحث -->
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">🔍 خيارات البحث</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="" class="form-inline">
                        <div class="form-group mr-2">
                            <label for="date" class="mr-2">التاريخ:</label>
                            <input type="date" name="date" class="form-control" 
                                value="<?= htmlspecialchars($dateFilter) ?>">
                        </div>
                        
                        <div class="form-group mr-2">
                            <label for="hourStart" class="mr-2">من الساعة:</label>
                            <input type="number" name="hourStart" class="form-control" 
                                min="0" max="24" style="width: 80px;" value="<?= $hourStart ?>">
                        </div>
                        
                        <div class="form-group mr-2">
                            <label for="hourEnd" class="mr-2">إلى الساعة:</label>
                            <input type="number" name="hourEnd" class="form-control" 
                                min="0" max="24" style="width: 80px;" value="<?= $hourEnd ?>">
                        </div>
                        
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fas fa-search"></i> بحث
                        </button>
                        
                        <div class="btn-group">
                            <a href="?date=<?= date('Y-m-d') ?>" class="btn btn-info">
                                <i class="fas fa-calendar-day"></i> اليوم
                            </a>
                            <a href="?date=<?= date('Y-m-d', strtotime('-1 day')) ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> أمس
                            </a>
                            <a href="?date=<?= date('Y-m-d', strtotime('+1 day')) ?>" class="btn btn-secondary">
                                غداً <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ✅ جدول السباقات -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">📅 جدول السباقات</h3>
                    <div class="card-tools">
                        <a href="race_add.php" class="btn btn-success btn-sm">
                            <i class="fas fa-plus"></i> إضافة سباق جديد
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($meetings)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover timeline-table">
                            <thead class="thead-dark">
                                <tr>
                                    <th style="width: 150px; background: #6c757d;">الاجتماع / المكان</th>
                                    <?php foreach ($hours as $hour): ?>
                                    <th class="text-center"><?= sprintf('%02d:00', $hour) ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($meetings as $meeting_code => $meeting_races): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($meeting_code) ?></strong><br>
                                        <small class="text-muted">
                                            <?= htmlspecialchars($meeting_races[0]['location']) ?>
                                        </small>
                                    </td>
                                    
                                    <?php foreach ($hours as $hour): ?>
                                    <td class="text-center">
                                        <?php foreach ($meeting_races as $race): ?>
                                        <?php if ((int)date('H', strtotime($race['start_time'])) == $hour): ?>
                                        <?php
                                            $status_class = [
                                                'upcoming' => 'race-upcoming',
                                                'running' => 'race-running',
                                                'finished' => 'race-finished',
                                                'cancelled' => 'race-cancelled'
                                            ][$race['status']] ?? 'race-upcoming';
                                        ?>
                                        <div class="race-box <?= $status_class ?>" data-toggle="modal"
                                            data-target="#raceModal<?= $race['race_id'] ?>"
                                            style="cursor: pointer; padding: 5px; margin: 2px; border-radius: 4px;">
                                            <strong><?= $race['race_number'] ?></strong><br>
                                            <small><?= date('H:i', strtotime($race['start_time'])) ?></small>
                                        </div>
                                        <?php endif; ?>
                                        <?php endforeach; ?>
                                    </td>
                                    <?php endforeach; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle"></i> لا توجد سباقات في هذا التاريخ
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- ✅ Modals للسباقات -->
<?php foreach ($races as $race): ?>
<div class="modal fade" id="raceModal<?= $race['race_id'] ?>" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    🏁 السباق <?= $race['race_number'] ?> - <?= $race['meeting_code'] ?>
                </h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>📋 معلومات السباق:</h6>
                        <table class="table table-sm">
                            <tr>
                                <th>📍 المكان:</th>
                                <td><?= htmlspecialchars($race['location']) ?></td>
                            </tr>
                            <tr>
                                <th>⏰ التوقيت:</th>
                                <td><?= date('Y-m-d H:i', strtotime($race['start_time'])) ?></td>
                            </tr>
                            <tr>
                                <th>📊 الحالة:</th>
                                <td>
                                    <span class="badge badge-<?= [
                                        'upcoming' => 'info',
                                        'running' => 'warning',
                                        'finished' => 'success',
                                        'cancelled' => 'danger'
                                    ][$race['status']] ?>">
                                        <?= [
                                            'upcoming' => 'قادم',
                                            'running' => 'جاري',
                                            'finished' => 'منتهي',
                                            'cancelled' => 'ملغي'
                                        ][$race['status']] ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>📊 إحصائيات:</h6>
                        <table class="table table-sm">
                            <tr>
                                <th>عدد الأحصنة:</th>
                                <td><?= count($race['horses']) ?></td>
                            </tr>
                            <tr>
                                <th>الرهانات النشطة:</th>
                                <td>25 رهان</td>
                            </tr>
                            <tr>
                                <th>إجمالي المبلغ:</th>
                                <td>5,250 ريال</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <h6 class="mt-3">🐎 الأحصنة المشاركة:</h6>
                <?php if (!empty($race['horses'])): ?>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>اسم الحصان</th>
                                <th>الفارس</th>
                                <th>المدرب</th>
                                <th>المركز</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($race['horses'] as $horse): ?>
                            <tr>
                                <td><?= $horse['number'] ?></td>
                                <td><?= htmlspecialchars($horse['name']) ?></td>
                                <td><?= htmlspecialchars($horse['jockey']) ?></td>
                                <td><?= htmlspecialchars($horse['trainer']) ?></td>
                                <td>
                                    <?php if ($horse['position']): ?>
                                    <span class="badge badge-success"><?= $horse['position'] ?></span>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> لا توجد أحصنة مضافة لهذا السباق
                </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <a href="race_view.php?id=<?= $race['race_id'] ?>" class="btn btn-info">
                    <i class="fas fa-eye"></i> عرض التفاصيل
                </a>
                <a href="race_edit.php?id=<?= $race['race_id'] ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> تعديل
                </a>
                <a href="race_add_horses.php?id=<?= $race['race_id'] ?>" class="btn btn-success">
                    <i class="fas fa-horse"></i> إدارة الأحصنة
                </a>
                
                <?php if ($race['status'] !== 'finished'): ?>
                <button class="btn btn-danger" data-toggle="modal" 
                    data-target="#finishRaceModal<?= $race['race_id'] ?>"
                    data-dismiss="modal">
                    <i class="fas fa-flag-checkered"></i> إنهاء السباق
                </button>
                <?php endif; ?>
                
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> إغلاق
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ✅ Modal إنهاء السباق -->
<div class="modal fade" id="finishRaceModal<?= $race['race_id'] ?>" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">🏁 إنهاء السباق <?= $race['race_number'] ?></h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" action="race_finish.php">
                <input type="hidden" name="race_id" value="<?= $race['race_id'] ?>">
                
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> قم بإدخال المراكز النهائية للأحصنة
                    </div>
                    
                    <table class="table table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>اسم الحصان</th>
                                <th>الفارس</th>
                                <th>المركز</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($race['horses'] as $horse): ?>
                            <tr>
                                <td><?= $horse['number'] ?></td>
                                <td><?= htmlspecialchars($horse['name']) ?></td>
                                <td><?= htmlspecialchars($horse['jockey']) ?></td>
                                <td>
                                    <input type="number" name="positions[<?= $horse['number'] ?>]" 
                                        class="form-control form-control-sm" min="1" 
                                        max="<?= count($race['horses']) ?>" 
                                        placeholder="المركز" required>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-save"></i> حفظ النتائج
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> إلغاء
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>


<?php include bess_url('footer', 'file'); ?>
