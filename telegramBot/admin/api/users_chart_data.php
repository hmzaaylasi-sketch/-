<?php
session_start();
require_once '../init/config.php';
require_once '../init/auth.php';

header('Content-Type: application/json');

try {
    // جلب بيانات المستخدمين للشهر السابق
    $chart_data = $conn->query("
        SELECT 
            DATE_FORMAT(registration_date, '%Y-%m') as month,
            COUNT(*) as user_count
        FROM users 
        WHERE registration_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(registration_date, '%Y-%m')
        ORDER BY month
    ")->fetchAll(PDO::FETCH_ASSOC);

    $labels = [];
    $values = [];
    
    foreach ($chart_data as $row) {
        $labels[] = $row['month'];
        $values[] = (int)$row['user_count'];
    }

    echo json_encode([
        'labels' => $labels,
        'values' => $values
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Failed to load chart data']);
}