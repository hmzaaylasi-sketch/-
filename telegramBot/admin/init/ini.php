<?php

function bess_url_v2($path = '', $type = '') {
    $project = 'telegram';

    $base_url = "http://localhost/telegram/admin/"; // عدل حسب بيئتك

    return $base_url.$path.".".$type;
}

function bess_url($path = '', $type = 'file') {
    $base_path = __DIR__ . "/";
    $base_url = "http://localhost/telegram/admin/";
    
    // إضافة التحقق من المسارات الآمنة
    $path = str_replace(['../', './'], '', $path); // منع Directory Traversal
    
    if ($type === 'file') {
        if (!preg_match('/\.php$/i', $path)) {
            $path .= ".php";
        } 
        return $base_path . $path;
    }
    
    if ($type === 'url') {
        if (preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|ico|webp)$/i', $path)) {
            return $base_url . $path;
        } else {
            return $base_url . $path . ".php";
        }
    }
    
    return null;
}
// نبدأ السيشن
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// استدعاء الملفات الأساسية
require_once __DIR__ . "/config.php";  // الاتصال بقاعدة البيانات
require_once __DIR__ . "/auth.php";    // التحقق من تسجيل الدخول