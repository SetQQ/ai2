<?php
// db_install.php - สำหรับรันเพื่อสร้างฐานข้อมูลและตารางครั้งแรก
$host = 'localhost';
$username = 'root'; // MySQL username พื้นฐานของ XAMPP
$password = '';     // MySQL password พื้นฐานของ XAMPP

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // 1. สร้างฐานข้อมูล
    $pdo->exec("CREATE DATABASE IF NOT EXISTS school_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE school_db");

    // 2. สร้างตาราง Users
    $sql = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('admin', 'teacher', 'student') NOT NULL,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        is_active TINYINT(1) DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $pdo->exec($sql);

    // 3. ตรวจสอบว่ามีข้อมูล Admin อยู่หรือยัง
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    if ($stmt->fetchColumn() == 0) {
        // เพิ่มข้อมูลตั้งต้น (Seed Data) แบบปลอดภัยด้วย Prepared Statement และ password_hash()
        $insertStmt = $pdo->prepare("INSERT INTO users (username, password_hash, role, first_name, last_name) VALUES (?, ?, ?, ?, ?)");
        
        $defaultPassword = 'password123';
        $hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);

        // นำเข้าบัญชี 3 ระดับ
        $insertStmt->execute(['admin01', $hashedPassword, 'admin', 'ผู้ดูแลระบบ', 'สาธิตวิทยา']);
        $insertStmt->execute(['teacher01', $hashedPassword, 'teacher', 'สมชาย', 'ใจดี']);
        $insertStmt->execute(['student01', $hashedPassword, 'student', 'สมหญิง', 'เรียนดี']);

        echo "<h2>ติดตั้งฐานข้อมูล school_db และสร้างบัญชีสำเร็จ!</h2>";
        echo "<p>คุณสามารถล็อกอินด้วย <br>
              <strong>Admin:</strong> admin01 / password123 <br>
              <strong>Teacher:</strong> teacher01 / password123 <br>
              <strong>Student:</strong> student01 / password123</p>";
        echo "<a href='login.php'>ไปที่หน้าเข้าสู่ระบบ</a>";
    } else {
        echo "<h2>ฐานข้อมูล school_db มีข้อมูลผู้ใช้อยู่แล้ว</h2>";
        echo "<a href='login.php'>ไปที่หน้าเข้าสู่ระบบ</a>";
    }

} catch (PDOException $e) {
    die("Installation failed: " . $e->getMessage());
}
?>
