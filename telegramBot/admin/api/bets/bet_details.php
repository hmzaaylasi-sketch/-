<?php
include '../../init/ini.php';
header("Content-Type: application/json; charset=utf-8");

$betId = $_GET['id'] ?? 0;
if (!$betId) {
    echo json_encode(["status"=>"error","message"=>"⚠️ لم يتم تحديد الرهان"]);
    exit;
}

try {
    // ✅ جلب الرهان
    $stmt = $conn->prepare("
        SELECT b.*, r.meeting_code, r.race_number, r.location, r.start_time, r.status
        FROM horse_bets b
        JOIN races r ON b.race_id = r.race_id
        WHERE b.bet_id = ?
    ");
    $stmt->execute([$betId]);
    $bet = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$bet) {
        echo json_encode(["status"=>"error","message"=>"⚠️ الرهان غير موجود"]);
        exit;
    }

    // ✅ جلب الأحصنة
    $stmt = $conn->prepare("
        SELECT rh.horse_number, h.horse_name, h.jockey, h.trainer
        FROM race_horses rh
        JOIN horses h ON rh.horse_id = h.horse_id
        WHERE rh.race_id = ?
        ORDER BY rh.horse_number ASC
    ");
    $stmt->execute([$bet['race_id']]);
    $horses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status"=>"success",
        "bet"=>$bet,
        "race"=>[
            "meeting_code"=>$bet['meeting_code'],
            "race_number"=>$bet['race_number'],
            "location"=>$bet['location'],
            "start_time"=>$bet['start_time'],
            "status"=>$bet['status']
        ],
        "horses"=>$horses
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status"=>"error",
        "message"=>"❌ خطأ داخلي",
        "error"=>$e->getMessage()
    ]);
}
