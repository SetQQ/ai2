<?php
require_once 'config/database.php';

// เตรียมตาราง students หากยังไม่มี
$sql = "
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_code VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    gender ENUM('Male', 'Female', 'Other') DEFAULT NULL,
    class_id INT DEFAULT NULL,
    class_level VARCHAR(50) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    parent_phone VARCHAR(20) DEFAULT NULL,
    dob DATE DEFAULT NULL,
    address TEXT DEFAULT NULL,
    status ENUM('Active', 'Graduated', 'Resigned') DEFAULT 'Active',
    profile_image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";
$pdo->exec($sql);

// Alter table if exists but missing new columns
try { $pdo->exec("ALTER TABLE students ADD COLUMN gender ENUM('Male', 'Female', 'Other') DEFAULT NULL AFTER last_name"); } catch(PDOException $e) {}
try { $pdo->exec("ALTER TABLE students ADD COLUMN class_id INT DEFAULT NULL AFTER gender"); } catch(PDOException $e) {}
try { $pdo->exec("ALTER TABLE students ADD COLUMN address TEXT DEFAULT NULL AFTER dob"); } catch(PDOException $e) {}
try { $pdo->exec("ALTER TABLE students ADD COLUMN status ENUM('Active', 'Graduated', 'Resigned') DEFAULT 'Active' AFTER address"); } catch(PDOException $e) {}
try { $pdo->exec("ALTER TABLE students ADD COLUMN dob DATE DEFAULT NULL AFTER last_name"); } catch(PDOException $e) {}
?>
