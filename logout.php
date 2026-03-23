<?php
session_name('SCHOOL_SECURE_SESSION');
session_start();

// ล้างตัวแปร session ทั้งหมด
$_SESSION = array();

// ทำลาย session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// ทำลาย session สุดท้าย
session_destroy();

// Redirect ไปที่หน้า login
header("Location: login.php");
exit;
?>
