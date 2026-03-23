<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

try {
    $actualTeacherCount = $pdo->query("SELECT COUNT(*) FROM teachers")->fetchColumn();
    $actualStudentCount = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
    $actualSubjectCount = $pdo->query("SELECT COUNT(*) FROM subjects")->fetchColumn();
    $actualClassroomCount = $pdo->query("SELECT COUNT(*) FROM classrooms")->fetchColumn();
} catch (PDOException $e) {
    $actualTeacherCount = $actualStudentCount = $actualSubjectCount = $actualClassroomCount = 0;
}

$data = [
    'teacher_count' => $actualTeacherCount,
    'student_count' => $actualStudentCount,
    'subject_count' => $actualSubjectCount,
    'classroom_count' => $actualClassroomCount
];

echo json_encode(['status' => 'success', 'data' => $data]);
?>
