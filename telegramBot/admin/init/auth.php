<?php
// auth.php - تأكد من تضمين هذا الملف في كل صفحة تحتاج حماية

// بدء الجلسة إذا لم تبدأ بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// التحقق من أن المشرف مسجل دخوله
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // تخزين الصفحة الحالية للعودة إليها بعد التسجيل
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    
    header("Location: login.php");
    exit();
}

// (اختياري) التحقق الإضافي لأمان الجلسة - لمنع Session Hijacking
if (isset($_SESSION['admin_ip'])) {
    // التحقق من أن IP المشرف لم يتغير
    if ($_SESSION['admin_ip'] !== $_SERVER['REMOTE_ADDR']) {
        // إذا تغير IP، ننهي الجلسة لأسباب أمنية
        session_unset();
        session_destroy();
        header("Location: login.php?error=session_security");
        exit();
    }
} else {
    // تخزين IP المشرف لأول مرة
    $_SESSION['admin_ip'] = $_SERVER['REMOTE_ADDR'];
}

// (اختياري) التحقق من آخر نشاط لمنع الجلسات الطويلة
$inactive = 3600; // 1 ساعة من الثواني
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $inactive)) {
    session_unset();
    session_destroy();
    header("Location: login.php?error=session_expired");
    exit();
}
$_SESSION['last_activity'] = time(); // تحديث وقت النشاط الأخير

// (اختياري) تجديد معرف الجلسة periodically لمنع fixation
$session_regenerate = 1800; // 30 دقيقة
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} else if (time() - $_SESSION['created'] > $session_regenerate) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}