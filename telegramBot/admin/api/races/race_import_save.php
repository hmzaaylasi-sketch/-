<?php
// admin/api/races/race_import_save.php
include '../../init/ini.php';
header("Content-Type: application/json; charset=utf-8");

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

if (!$data || empty($data['races'])) {
    echo json_encode(["status"=>"error","message"=>"❌ لا توجد بيانات لحفظها"]);
    exit;
}

try {
    $conn->beginTransaction();

    foreach ($data['races'] as $race) {
        // إدخال السباق
        $stmt = $conn->prepare("INSERT INTO races (meeting_code,race_number,location,start_time,distance,prize,status) 
                                VALUES (:mc,:rn,:loc,:st,:dist,:prize,'upcoming')");
        $stmt->execute([
            ':mc'=>$data['meeting'],
            ':rn'=>$race['race_number'],
            ':loc'=>$data['location'],
            ':st'=>$data['date']." ".$race['start_time'],
            ':dist'=>$race['distance'],
            ':prize'=>$race['prize']
        ]);
        $race_id = $conn->lastInsertId();

        // إدخال الأحصنة
        $stmtHorse = $conn->prepare("INSERT IGNORE INTO horses (horse_name) VALUES (:name)");
        $stmtLink = $conn->prepare("INSERT INTO race_horses (race_id,horse_id,horse_number) VALUES (:race,:horse,:num)");
        $num=1;
        foreach ($race['horses'] as $hName) {
            $stmtHorse->execute([":name"=>$hName]);
            $horse_id = $conn->lastInsertId();
            if (!$horse_id) {
                // إذا كان موجود نسترجعه
                $stmtFind = $conn->prepare("SELECT horse_id FROM horses WHERE horse_name=:n");
                $stmtFind->execute([":n"=>$hName]);
                $row = $stmtFind->fetch();
                $horse_id = $row ? $row['horse_id'] : null;
            }
            if ($horse_id) {
                $stmtLink->execute([":race"=>$race_id,":horse"=>$horse_id,":num"=>$num++]);
            }
        }
    }

    $conn->commit();
    echo json_encode(["status"=>"success","message"=>"✅ تم حفظ السباقات والأحصنة"]);
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(["status"=>"error","message"=>$e->getMessage()]);
}
