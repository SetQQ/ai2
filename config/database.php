<?php
// config/database.php
$host = 'localhost';
$dbname = 'school_db';
$username = 'root'; // เปลี่ยนเป็น username ของฐานข้อมูลหากจำเป็น
$password = '';     // เปลี่ยนเป็น password ของฐานข้อมูลหากจำเป็น

try {
    // เชื่อมต่อ MySQL เพื่อสร้างฐานข้อมูลก่อน ป้องกัน Database Not Found
    $init_dsn = "mysql:host=$host;charset=utf8mb4";
    $init_pdo = new PDO($init_dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    // สร้างฐานข้อมูลหากยังไม่มี
    $init_pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    // หลังจากแน่ใจว่าได้สร้างฐานข้อมูลแล้ว ให้เชื่อมต่อเข้าฐานข้อมูลนั้น
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, $username, $password, $options);

    // สร้างตาราง users หากยังไม่มี
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

    // ตรวจสอบว่ามีข้อมูล Admin อยู่หรือยัง (Seed Data)
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    if ($stmt->fetchColumn() == 0) {
        $insertStmt = $pdo->prepare("INSERT INTO users (username, password_hash, role, first_name, last_name) VALUES (?, ?, ?, ?, ?)");
        $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
        $insertStmt->execute(['admin01', $hashedPassword, 'admin', 'ผู้ดูแลระบบ', 'สาธิตวิทยา']);
        $insertStmt->execute(['teacher01', $hashedPassword, 'teacher', 'สมชาย', 'ใจดี']);
        $insertStmt->execute(['student01', $hashedPassword, 'student', 'สมหญิง', 'เรียนดี']);
    }

} catch (PDOException $e) {
    // ใน production ไม่ควรแสดง error message ให้กับผู้ใช้ทั่วไปเห็นโดยตรง
    die("Database connection failed: " . $e->getMessage());
}
?>
