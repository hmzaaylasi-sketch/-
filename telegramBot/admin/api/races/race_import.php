<?php
include '../../init/ini.php';
header('Content-Type: application/json; charset=utf-8');

// تحقق من الملف
if (!isset($_FILES['pdf_file']) || $_FILES['pdf_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(["status"=>"error","message"=>"⚠️ لم يتم رفع أي ملف"]);
    exit;
}

$pdfPath = __DIR__ . "/../../uploads/" . basename($_FILES['pdf_file']['name']);
move_uploaded_file($_FILES['pdf_file']['tmp_name'], $pdfPath);

// شغل سكربت Python لتحليل PDF
$cmd = escapeshellcmd("python3 ../../bot/pdf_import.py " . escapeshellarg($pdfPath));
$output = shell_exec($cmd);

if (!$output) {
    echo json_encode(["status"=>"error","message"=>"❌ فشل تشغيل سكربت التحليل"]);
    exit;
}

echo $output;
