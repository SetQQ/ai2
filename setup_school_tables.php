<?php
// setup_school_tables.php
require_once 'config/database.php';

try {
    // 1. สร้างตาราง subjects (รายวิชา)
    $sql_subjects = "
    CREATE TABLE IF NOT EXISTS subjects (
        id INT AUTO_INCREMENT PRIMARY KEY,
        subject_code VARCHAR(20) NOT NULL UNIQUE,
        subject_name VARCHAR(150) NOT NULL,
        credit DECIMAL(3,1) DEFAULT 0.0,
        type ENUM('core', 'elective') DEFAULT 'core',
        description TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $pdo->exec($sql_subjects);
    echo "ตาราง subjects สร้างสำเร็จหรือมีอยู่แล้ว<br>";

    // 2. สร้างตาราง classes (ระดับชั้นเรียน)
    $sql_classes = "
    CREATE TABLE IF NOT EXISTS classes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        class_code VARCHAR(20) UNIQUE DEFAULT NULL,
        class_name VARCHAR(50) NOT NULL UNIQUE,
        level VARCHAR(50) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $pdo->exec($sql_classes);
    
    // Alter table if exists but missing new columns
    try {
        $pdo->exec("ALTER TABLE classes ADD COLUMN class_code VARCHAR(20) DEFAULT NULL UNIQUE AFTER id");
    } catch(PDOException $e) {}
    
    echo "ตาราง classes สร้างสำเร็จหรือมีอยู่แล้ว<br>";

    // 3. สร้างตาราง classrooms (ห้องเรียน)
    $sql_classrooms = "
    CREATE TABLE IF NOT EXISTS classrooms (
        id INT AUTO_INCREMENT PRIMARY KEY,
        room_code VARCHAR(20) UNIQUE DEFAULT NULL,
        class_id INT NOT NULL,
        room_name VARCHAR(50) NOT NULL,
        teacher_id INT DEFAULT NULL,
        capacity INT DEFAULT 40,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    // NOTE: In a strict DB we would add: FOREIGN KEY (class_id) REFERENCES classes(id) and FOREIGN KEY (teacher_id) REFERENCES teachers(id),
    // but we omit it here for simplicity and to prevent foreign key constraint issues during basic CRUD operations if tables are dropped/truncated.
    
    $pdo->exec($sql_classrooms);
    
    // Alter table if exists but missing new columns
    try {
        $pdo->exec("ALTER TABLE classrooms ADD COLUMN room_code VARCHAR(20) DEFAULT NULL UNIQUE AFTER id");
    } catch(PDOException $e) {}
    
    echo "ตาราง classrooms สร้างสำเร็จหรือมีอยู่แล้ว<br>";

    echo "<h3>การเตรียมฐานข้อมูลเสร็จสมบูรณ์</h3>";

} catch (PDOException $e) {
    die("Error creating tables: " . $e->getMessage());
}
?>
