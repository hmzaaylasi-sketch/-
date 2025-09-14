<?php
// api/update_positions.php
session_start();
include '../init/ini.php';

$race_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($race_id <= 0) {
    die("⚠️ سباق غير صالح");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("⚠️ طلب غير صحيح");
}

$positions = $_POST['positions'] ?? [];
if (!is_array($positions) || empty($positions)) {
    // حتى لو فاضيين نرجّع للعرض بدون تحديث
    header("Location: ../races/race_view.php?id=".$race_id);
    exit;
}

try {
    $conn->beginTransaction();

    // تحديث كل حصان على حدة باستخدام (race_id, horse_id)
    $stmt = $conn->prepare("
        UPDATE race_horses 
        SET final_position = :pos, result_status = CASE WHEN :pos IS NULL OR :pos = '' THEN result_status ELSE 'finished' END
        WHERE race_id = :race_id AND horse_id = :horse_id
    ");

    foreach ($positions as $horse_id => $pos) {
        $horse_id = (int)$horse_id;
        // السماح بتفريغ الخانة لو ترجعها فاضية
        $pos = (strlen(trim((string)$pos)) === 0) ? null : (int)$pos;

        $stmt->execute([
            ':pos' => $pos,
            ':race_id' => $race_id,
            ':horse_id' => $horse_id
        ]);
    }

    $conn->commit();
    $_SESSION['success'] = "✅ تم حفظ ترتيب الخيول بنجاح.";
} catch (PDOException $e) {
    $conn->rollBack();
    $_SESSION['error'] = "❌ فشل حفظ الترتيب: " . $e->getMessage();
}

header("Location: ../races/race_view.php?id=".$race_id);
exit;
