<?php
// includes/auth_check.php
session_name('SCHOOL_SECURE_SESSION');
session_start();

// 1. ตรวจสอบว่ามี session user_id หรือไม่ (ล็อกอินหรือยัง)
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// 2. ป้องกัน Session Hijacking โดยกำหนดอายุของ Session ถ้านานเกินไปให้ล็อกเอาท์ (ตัวอย่างตั้งไว้ 2 ชั่วโมง)
$timeout_duration = 7200; // 2 hours
if (isset($_SESSION['last_login']) && (time() - $_SESSION['last_login']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: login.php?msg=SessionExpired");
    exit;
}

// อัปเดตเวลาใช้งานล่าสุด
$_SESSION['last_login'] = time();

// Function สำหรับตรวจสอบ Role สิทธิ์การเข้าถึงหน้าเพจต่างๆ
function checkRole($allowed_roles) {
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
        // ถ้าไม่มีสิทธิ์ ให้แสดงหน้า Error หรือเด้งกลับหน้า Index ของ Role นั้น
        die("<h2>Access Denied: คุณไม่มีสิทธิ์เข้าถึงหน้านี้</h2><a href='index.php'>กลับสู่หน้าหลัก</a>");
    }
}
?>
