<?php
require_once 'config/database.php';

// เตรียมตาราง teachers หากยังไม่มี
$sql = "
CREATE TABLE IF NOT EXISTS teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_code VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    line_id VARCHAR(50) DEFAULT NULL,
    department VARCHAR(100) DEFAULT NULL,
    profile_image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";
$pdo->exec($sql);

// Alter table if exists but missing new columns (For backward compatibility with existing table)
try {
    $pdo->exec("ALTER TABLE teachers ADD COLUMN line_id VARCHAR(50) DEFAULT NULL AFTER phone");
} catch(PDOException $e) {}

try {
    $pdo->exec("ALTER TABLE teachers ADD COLUMN profile_image VARCHAR(255) DEFAULT NULL AFTER department");
} catch(PDOException $e) {}
?>
