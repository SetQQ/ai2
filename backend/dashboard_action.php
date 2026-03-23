<?php
session_start();
require_once '../config/database.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$action = $_REQUEST['action'] ?? '';

// Ensure table exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS dashboard_settings (
        setting_key VARCHAR(50) PRIMARY KEY,
        setting_value VARCHAR(100)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
} catch(PDOException $e) {}

if ($action == 'update_stats') {
    $teacher = $_POST['teacher_count'] ?? '';
    $student = $_POST['student_count'] ?? '';
    $subject = $_POST['subject_count'] ?? '';
    $classroom = $_POST['classroom_count'] ?? '';
    
    $updates = [
        'teacher_count' => $teacher,
        'student_count' => $student,
        'subject_count' => $subject,
        'classroom_count' => $classroom
    ];

    try {
        $stmt = $pdo->prepare("INSERT INTO dashboard_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        foreach($updates as $key => $val) {
            $stmt->execute([$key, $val, $val]);
        }
        echo json_encode(['status' => 'success', 'message' => 'บันทึกข้อมูลสถิติเรียบร้อยแล้ว']);
    } catch(PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการบันทึก: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
?>
