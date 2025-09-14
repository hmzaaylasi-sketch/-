<?php
session_start();
require_once "init/config.php"; 

$error = "";
$message = "";
$show_form = true;

// إعدادات الحماية من Brute Force
$max_attempts = 5;
$lockout_time = 300; // 5 دقائق

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt_time'] = 0;
}

// التحقق من القفل
if ($_SESSION['login_attempts'] >= $max_attempts && (time() - $_SESSION['last_attempt_time']) < $lockout_time) {
    $remaining_time = $lockout_time - (time() - $_SESSION['last_attempt_time']);
    $error = "❌ تم تجاوز عدد المحاولات المسموح بها. الرجاء الانتظار " . ceil($remaining_time/60) . " دقائق";
    $show_form = false;
}

// إنشاء CSRF Token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $show_form) {
    // التحقق من CSRF Token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "❌ طلب غير صالح";
        exit();
    }

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // استعلام آمن
    $stmt = $conn->prepare("SELECT admin_id, username, password_hash FROM admins WHERE username = :username LIMIT 1");
    $stmt->bindParam(":username", $username);
    $stmt->execute();

    if ($stmt->rowCount() === 1) {
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (password_verify($password, $admin['password_hash'])) {
            // نجاح التسجيل
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['login_attempts'] = 0;
            $_SESSION['last_attempt_time'] = 0;
            
            header("Location: dashboard.php");
            exit();
        }
    }
    
    // فشل التسجيل
    $_SESSION['login_attempts']++;
    $_SESSION['last_attempt_time'] = time();
    
    // تأخير متزايد
    if ($_SESSION['login_attempts'] > 2) {
        sleep($_SESSION['login_attempts']);
    }
    
    $error = "❌ اسم المستخدم أو كلمة المرور غير صحيحة!";
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>تسجيل الدخول - لوحة التحكم</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f8f9fa; }
    .login-box {
      max-width: 380px;
      margin: 80px auto;
      padding: 30px;
      border-radius: 15px;
      background: #fff;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .login-box h2 { text-align: center; margin-bottom: 20px; }
    .btn-custom { background: #0d6efd; color: #fff; }
    .btn-custom:hover { background: #0b5ed7; }
  </style>
</head>
<body>
  <div class="login-box">
    <h2>🔐 تسجيل الدخول</h2>
    
    <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($show_form): ?>
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
      
      <div class="mb-3">
        <label class="form-label">اسم المستخدم</label>
        <input type="text" name="username" class="form-control" required>
      </div>
      
      <div class="mb-3">
        <label class="form-label">كلمة المرور</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      
      <button type="submit" class="btn btn-custom w-100">تسجيل الدخول</button>
    </form>
    <?php endif; ?>
  </div>
</body>
</html>