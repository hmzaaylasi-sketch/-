<?php
session_start();
include '../init/ini.php';


// معالجة إضافة الحصان
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $horse_name = trim($_POST['horse_name']);
        $age = intval($_POST['age']);
        $owner = trim($_POST['owner']);
        $trainer = trim($_POST['trainer']);
        $jockey = trim($_POST['jockey']);
        $gender = $_POST['gender'];
        $color = trim($_POST['color']);

        // التحقق من البيانات
        if (empty($horse_name) || empty($owner) || empty($trainer) || empty($jockey)) {
            $_SESSION['toast'] = [
                'type' => 'error',
                'message' => '❌ جميع الحقول الإلزامية مطلوبة!'
            ];
        } elseif ($age <= 0 || $age > 30) {
            $_SESSION['toast'] = [
                'type' => 'error',
                'message' => '❌ العمر يجب أن يكون بين 1 و 30 سنة!'
            ];
        } else {
            // التحقق من عدم وجود حصان بنفس الاسم
            $check_stmt = $conn->prepare("SELECT horse_id FROM horses WHERE horse_name = :horse_name");
            $check_stmt->bindParam(":horse_name", $horse_name);
            $check_stmt->execute();

            if ($check_stmt->rowCount() > 0) {
                $_SESSION['toast'] = [
                    'type' => 'error',
                    'message' => '❌ يوجد حصان بنفس الاسم بالفعل!'
                ];
            } else {
                // إضافة الحصان إلى قاعدة البيانات
                $stmt = $conn->prepare("
                    INSERT INTO horses (horse_name, age, owner, trainer, jockey, gender, color) 
                    VALUES (:horse_name, :age, :owner, :trainer, :jockey, :gender, :color)
                ");

                $stmt->bindParam(":horse_name", $horse_name);
                $stmt->bindParam(":age", $age);
                $stmt->bindParam(":owner", $owner);
                $stmt->bindParam(":trainer", $trainer);
                $stmt->bindParam(":jockey", $jockey);
                $stmt->bindParam(":gender", $gender);
                $stmt->bindParam(":color", $color);

                if ($stmt->execute()) {
                    $_SESSION['toast'] = [
                        'type' => 'success',
                        'message' => '✅ تم إضافة الحصان بنجاح!'
                    ];
                    // تفريغ الحقول بعد الإضافة الناجحة
                    header("Location: horse_add.php");
                    exit();
                } else {
                    $_SESSION['toast'] = [
                        'type' => 'error',
                        'message' => '❌ حدث خطأ أثناء إضافة الحصان!'
                    ];
                }
            }
        }
    } catch (PDOException $e) {
        $_SESSION['toast'] = [
            'type' => 'error',
            'message' => '❌ خطأ في قاعدة البيانات: ' . $e->getMessage()
        ];
    }
}

 include bess_url('header'); 
 include bess_url('navbar'); 
 include bess_url('sidebar'); ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <h1>🐎 إضافة حصان جديد</h1>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8 mx-auto">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">معلومات الحصان</h3>
                        </div>

                        <form method="POST" id="addHorseForm">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>اسم الحصان *</label>
                                            <input type="text" class="form-control" name="horse_name" 
                                                   value="<?= htmlspecialchars($_POST['horse_name'] ?? '') ?>" 
                                                   required maxlength="100">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>العمر (سنوات) *</label>
                                            <input type="number" class="form-control" name="age" 
                                                   value="<?= htmlspecialchars($_POST['age'] ?? '') ?>" 
                                                   min="1" max="30" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>المالك *</label>
                                            <input type="text" class="form-control" name="owner" 
                                                   value="<?= htmlspecialchars($_POST['owner'] ?? '') ?>" 
                                                   required maxlength="100">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>المدرب *</label>
                                            <input type="text" class="form-control" name="trainer" 
                                                   value="<?= htmlspecialchars($_POST['trainer'] ?? '') ?>" 
                                                   required maxlength="100">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>الفارس *</label>
                                            <input type="text" class="form-control" name="jockey" 
                                                   value="<?= htmlspecialchars($_POST['jockey'] ?? '') ?>" 
                                                   required maxlength="100">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>الجنس *</label>
                                            <select class="form-control" name="gender" required>
                                                <option value="ذكر" <?= ($_POST['gender'] ?? '') === 'ذكر' ? 'selected' : '' ?>>ذكر</option>
                                                <option value="أنثى" <?= ($_POST['gender'] ?? '') === 'أنثى' ? 'selected' : '' ?>>أنثى</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>اللون</label>
                                    <input type="text" class="form-control" name="color" 
                                           value="<?= htmlspecialchars($_POST['color'] ?? '') ?>" 
                                           maxlength="50" placeholder="أدخل لون الحصان (اختياري)">
                                </div>
                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> إضافة الحصان
                                </button>
                                <a href="horse_list.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-right"></i> رجوع إلى القائمة
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include bess_url('footer'); ?>

<script>
// التحقق من النموذج قبل الإرسال
document.getElementById('addHorseForm').addEventListener('submit', function(e) {
    const horseName = document.querySelector('input[name="horse_name"]').value.trim();
    const age = document.querySelector('input[name="age"]').value;
    const owner = document.querySelector('input[name="owner"]').value.trim();
    const trainer = document.querySelector('input[name="trainer"]').value.trim();
    const jockey = document.querySelector('input[name="jockey"]').value.trim();
    
    if (!horseName || !age || !owner || !trainer || !jockey) {
        e.preventDefault();
        showToast('❌ جميع الحقول الإلزامية مطلوبة!', 'error');
        return false;
    }
    
    if (age < 1 || age > 30) {
        e.preventDefault();
        showToast('❌ العمر يجب أن يكون بين 1 و 30 سنة!', 'error');
        return false;
    }
    
    return true;
});

// عرض التوست إذا كان هناك رسالة في الجلسة
<?php if (isset($_SESSION['toast'])): ?>
$(document).ready(function() {
    showToast('<?= $_SESSION['toast']['message'] ?>', '<?= $_SESSION['toast']['type'] ?>');
    <?php unset($_SESSION['toast']); ?>
});
<?php endif; ?>
</script>