<?php
// backend/db_patch_v1.php
require_once __DIR__ . '/../config/database.php';

echo "Starting Database Patch v1...\n";

try {
    // 1. Update Students Table
    echo "Updating 'students' table...\n";
    
    // Add user_id if not exists
    $pdo->exec("ALTER TABLE students ADD COLUMN IF NOT EXISTS user_id INT DEFAULT NULL AFTER id");
    $pdo->exec("ALTER TABLE students ADD UNIQUE INDEX IF NOT EXISTS (user_id)");
    
    // Add class_id if not exists
    $pdo->exec("ALTER TABLE students ADD COLUMN IF NOT EXISTS class_id INT DEFAULT NULL AFTER user_id");

    // 2. Update Teachers Table
    echo "Updating 'teachers' table...\n";
    
    // Add user_id if not exists
    $pdo->exec("ALTER TABLE teachers ADD COLUMN IF NOT EXISTS user_id INT DEFAULT NULL AFTER id");
    $pdo->exec("ALTER TABLE teachers ADD UNIQUE INDEX IF NOT EXISTS (user_id)");

    echo "Database patch completed successfully!\n";
} catch (PDOException $e) {
    echo "Error during patch: " . $e->getMessage() . "\n";
}
?>
