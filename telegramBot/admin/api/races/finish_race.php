<?php
header('Content-Type: application/json');
require_once '../../init/config.php';
require_once '../../init/auth.php';

// هذا الملف سيكون مسؤولاً عن إنهاء السباق وتحديد المراكز
// سيتم تطويره لاحقاً عندما يكون جاهزاً

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'الطريقة غير مسموحة'
    ]);
    exit();
}

echo json_encode([
    'status' => 'error',
    'message' => 'هذه الميزة قيد التطوير'
]);