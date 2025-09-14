<?php
session_start();

include '../init/ini.php';

$error = "";
$success = "";

// جلب الأحصنة للقائمة المنسدلة
try {
    $horses_stmt = $conn->prepare("SELECT horse_id, horse_name FROM horses ORDER BY horse_name");
    $horses_stmt->execute();
    $horses = $horses_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "❌ خطأ في جلب بيانات الأحصنة: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $meeting_code = trim($_POST['meeting_code']);
        $race_number = trim($_POST['race_number']);
        $race_name = trim($_POST['race_name']);
        $location = trim($_POST['location']);
        $start_time = $_POST['start_time'];
        $distance = intval($_POST['distance']);
        $prize_pool = floatval($_POST['prize_pool']);
        
        // التحقق من البيانات
        if (empty($meeting_code) || empty($race_number) || empty($location) || empty($start_time)) {
            $error = "❌ جميع الحقول الإلزامية مطلوبة!";
        } else {
            // إضافة السباق إلى قاعدة البيانات
            $stmt = $conn->prepare("
                INSERT INTO races 
                (meeting_code, race_number, race_name, location, start_time, distance, prize_pool, status) 
                VALUES (:meeting_code, :race_number, :race_name, :location, :start_time, :distance, :prize_pool, 'upcoming')
            ");

            $stmt->bindParam(":meeting_code", $meeting_code);
            $stmt->bindParam(":race_number", $race_number);
            $stmt->bindParam(":race_name", $race_name);
            $stmt->bindParam(":location", $location);
            $stmt->bindParam(":start_time", $start_time);
            $stmt->bindParam(":distance", $distance);
            $stmt->bindParam(":prize_pool", $prize_pool);

            if ($stmt->execute()) {
                $race_id = $conn->lastInsertId();
                
                // إضافة الأحصنة المشاركة في السباق
                if (isset($_POST['horses']) && is_array($_POST['horses'])) {
                    foreach ($_POST['horses'] as $horse_id) {
                        $horse_id = intval($horse_id);
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
                                $entry_stmt = $conn->prepare("
                                    INSERT INTO race_entries 
                                    (race_id, horse_number, horse_name, jockey, trainer, owner, status) 
                                    VALUES (:race_id, :horse_number, :horse_name, :jockey, :trainer, :owner, 'scheduled')
                                ");
                                
                                $horse_number = count($_POST['horses']) + 1; // رقم افتراضي
                                $entry_stmt->bindParam(":race_id", $race_id);
                                $entry_stmt->bindParam(":horse_number", $horse_number);
                                $entry_stmt->bindParam(":horse_name", $horse['horse_name']);
                                $entry_stmt->bindParam(":jockey", $horse['jockey']);
                                $entry_stmt->bindParam(":trainer", $horse['trainer']);
                                $entry_stmt->bindParam(":owner", $horse['owner']);
                                $entry_stmt->execute();
                            }
                        }
                    }
                }
                
                $_SESSION['toast'] = [
                    'type' => 'success',
                    'message' => '✅ تم إضافة السباق بنجاح!'
                ];
                header("Location: " . bess_url('races/race_list', 'url'));
                exit();
            } else {
                $error = "❌ حدث خطأ أثناء إضافة السباق!";
            }
        }
    } catch (PDOException $e) {
        $error = "❌ خطأ في قاعدة البيانات: " . $e->getMessage();
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
                    <h1>🏇 إضافة سباق جديد</h1>
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
                <div class="col-md-8 mx-auto">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">معلومات السباق</h3>
                        </div>

                        <form method="POST" id="addRaceForm">
                            <div class="card-body">
                                <?php if (!empty($error)): ?>
                                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                                <?php endif; ?>
                                
                                <?php if (!empty($success)): ?>
                                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                                <?php endif; ?>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>كود الاجتماع *</label>
                                            <input type="text" class="form-control" name="meeting_code" 
                                                   value="<?= htmlspecialchars($_POST['meeting_code'] ?? '') ?>" 
                                                   required placeholder="مثال: R1">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>رقم السباق *</label>
                                            <input type="text" class="form-control" name="race_number" 
                                                   value="<?= htmlspecialchars($_POST['race_number'] ?? '') ?>" 
                                                   required placeholder="مثال: C6">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>اسم السباق</label>
                                    <input type="text" class="form-control" name="race_name" 
                                           value="<?= htmlspecialchars($_POST['race_name'] ?? '') ?>" 
                                           placeholder="مثال: Société des courses: الرباط">
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>المكان *</label>
                                            <input type="text" class="form-control" name="location" 
                                                   value="<?= htmlspecialchars($_POST['location'] ?? '') ?>" 
                                                   required placeholder="مثال: KHEMISSET">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>وقت البدء *</label>
                                            <input type="datetime-local" class="form-control" name="start_time" 
                                                   value="<?= htmlspecialchars($_POST['start_time'] ?? '') ?>" 
                                                   required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>المسافة (متر)</label>
                                            <input type="number" class="form-control" name="distance" 
                                                   value="<?= htmlspecialchars($_POST['distance'] ?? '') ?>" 
                                                   placeholder="المسافة بالأمتار">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>الجائزة (درهم)</label>
                                            <input type="number" step="0.01" class="form-control" name="prize_pool" 
                                                   value="<?= htmlspecialchars($_POST['prize_pool'] ?? '') ?>" 
                                                   placeholder="مبلغ الجائزة">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>الأحصنة المشاركة *</label>
                                    <select class="form-control select2" name="horses[]" multiple="multiple" 
                                            data-placeholder="اختر الأحصنة" required style="width: 100%;">
                                        <?php if ($horses && count($horses) > 0): ?>
                                            <?php foreach ($horses as $horse): ?>
                                            <option value="<?= $horse['horse_id'] ?>">
                                                <?= htmlspecialchars($horse['horse_name']) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <option value="">لا توجد أحصنة متاحة</option>
                                        <?php endif; ?>
                                    </select>
                                    <small class="text-muted">اضغط Ctrl لاختيار أكثر من حصان</small>
                                </div>
                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> إضافة السباق
                                </button>
                                <button type="reset" class="btn btn-secondary">
                                    <i class="fas fa-redo"></i> إعادة تعيين
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include bess_url('footer', 'file'); ?>

