<?php
require_once 'config/database.php';

try {
    // 1. Add user_id to teachers
    try {
        $pdo->exec("ALTER TABLE teachers ADD COLUMN user_id INT DEFAULT NULL UNIQUE AFTER id");
        echo "Added user_id to teachers.\n";
    } catch(PDOException $e) { // If column exists, ignore
        echo "user_id might already exist in teachers. Error: " . $e->getMessage() . "\n";
    }

    // 2. Add user_id to students
    try {
        $pdo->exec("ALTER TABLE students ADD COLUMN user_id INT DEFAULT NULL UNIQUE AFTER id");
        echo "Added user_id to students.\n";
    } catch(PDOException $e) {
        echo "user_id might already exist in students. Error: " . $e->getMessage() . "\n";
    }

    // 3. Add teacher_id to subjects
    try {
        $pdo->exec("ALTER TABLE subjects ADD COLUMN teacher_id INT DEFAULT NULL AFTER subject_name");
        echo "Added teacher_id to subjects.\n";
    } catch(PDOException $e) {
        echo "teacher_id might already exist in subjects. Error: " . $e->getMessage() . "\n";
    }

    echo "Database relation setup complete!\n";
} catch (PDOException $e) {
    echo "Error updating relations: " . $e->getMessage() . "\n";
}
?>
