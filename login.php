<?php
session_name('SCHOOL_SECURE_SESSION'); // เปลี่ยนชื่อ Session Cookie เพื่อป้องกันการเดา
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '', // 
    'secure' => isset($_SERVER['HTTPS']), // ให้เป็น true ถ้าใช้ HTTPS
    'httponly' => true, // ป้องกัน XSS ในการอ่าน Session Cookie ผ่าน JavaScript
    'samesite' => 'Lax' // ป้องกัน CSRF ระดับหนึ่ง
]);
session_start();

require_once 'config/database.php';

// หากล็อกอินอยู่แล้วให้ Redirect ไปหน้า Dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// สร้าง CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    // ตรวจสอบ CSRF Token
    if (!hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        $error = "การเชื่อมต่อไม่ปลอดภัย กรุณาลองใหม่อีกครั้ง (CSRF Token Mismatch)";
    } elseif (empty($username) || empty($password)) {
        $error = "กรุณากรอกชื่อผู้ใช้และรหัสผ่าน";
    } else {
        // ใช้ Prepared Statement เพื่อป้องกัน SQL Injection
        $stmt = $pdo->prepare("SELECT id, username, password_hash, role, first_name, last_name, is_active FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            if (!$user['is_active']) {
                $error = "บัญชีนี้ถูกระงับการใช้งาน กรุณาติดต่อผู้ดูแลระบบ";
            } else {
                // ตรวจสอบรหัสผ่านที่ถูกเข้ารหัสไว้แล้ว (Password Hashing)
                if (password_verify($password, $user['password_hash'])) {
                    
                    // ป้องกัน Session Fixation Attack โดยการสร้าง Session ID ใหม่
                    session_regenerate_id(true);

                    // เก็บข้อมูลผู้ใช้ลง Session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['first_name'] = $user['first_name'];
                    $_SESSION['last_name'] = $user['last_name'];
                    $_SESSION['last_login'] = time();

                    header('Location: index.php');
                    exit;
                } else {
                    $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
                }
            }
        } else {
            // เราใช้ข้อความ error เดียวกันเพื่อไม่ให้ Hacker รู้ว่า Username นี้มีในระบบหรือไม่ (User Enumeration Prevention)
            $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - โรงเรียนสาธิตวิทยา</title>
    <!-- Favicon -->
    <link rel="icon" href="images/favicon.svg" type="image/svg+xml">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body.login-page {
            background-color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .login-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
            width: 100%;
            max-width: 450px;
        }
        .login-header {
            background-color: var(--secondary-color);
            padding: 30px 20px;
            text-align: center;
            color: #fff;
        }
    </style>
</head>
<body class="login-page">

    <div class="login-card">
        <div class="login-header">
            <h3 class="mb-0 fw-bold">โรงเรียนสาธิตวิทยา</h3>
            <p class="mb-0 text-white-50">เข้าสู่ระบบเพื่อจัดการข้อมูล</p>
        </div>
        <div class="p-4 p-md-5">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                
                <div class="mb-3">
                    <label for="username" class="form-label fw-semibold">ชื่อผู้ใช้ (Username)</label>
                    <input type="text" class="form-control" id="username" name="username" required autofocus placeholder="กรอกชื่อผู้ใช้ของคุณ">
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label fw-semibold">รหัสผ่าน (Password)</label>
                    <input type="password" class="form-control" id="password" name="password" required placeholder="กรอกรหัสผ่านของคุณ">
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary-custom fw-bold py-2">เข้าสู่ระบบ</button>
                </div>
            </form>
            <div class="text-center mt-4">
                <small class="text-muted">หากพบปัญหาการเข้าสู่ระบบ โปรดติดต่อฝ่ายไอที</small>
            </div>
        </div>
    </div>

</body>
</html>
