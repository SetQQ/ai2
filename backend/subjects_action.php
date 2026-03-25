<?php
session_name('SCHOOL_SECURE_SESSION');
session_start();
require_once '../config/database.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Forbidden']);
    exit;
}


$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'create':
        $subject_code = $_POST['subject_code'] ?? '';
        $subject_name = $_POST['subject_name'] ?? '';
        $credit = $_POST['credit'] ?? 0;
        $type = $_POST['type'] ?? 'core';
        $description = $_POST['description'] ?? '';
        $teacher_id = !empty($_POST['teacher_id']) ? $_POST['teacher_id'] : null;

        if (empty($subject_code) || empty($subject_name)) {
            echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO subjects (subject_code, subject_name, credit, type, description, teacher_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$subject_code, $subject_name, $credit, $type, $description, $teacher_id]);
            echo json_encode(['status' => 'success', 'message' => 'เพิ่มข้อมูลรายวิชาเรียบร้อยแล้ว']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการเพิ่มข้อมูล: ' . $e->getMessage()]);
        }
        break;

    case 'read':
        try {
            $stmt = $pdo->query("SELECT s.*, t.first_name, t.last_name FROM subjects s LEFT JOIN teachers t ON s.teacher_id = t.id ORDER BY s.subject_code ASC");
            $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $subjects]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'data' => [], 'message' => $e->getMessage()]);
        }
        break;

    case 'update':
        $id = $_POST['id'] ?? '';
        $subject_code = $_POST['subject_code'] ?? '';
        $subject_name = $_POST['subject_name'] ?? '';
        $credit = $_POST['credit'] ?? 0;
        $type = $_POST['type'] ?? 'core';
        $description = $_POST['description'] ?? '';
        $teacher_id = !empty($_POST['teacher_id']) ? $_POST['teacher_id'] : null;

        if (empty($id) || empty($subject_code) || empty($subject_name)) {
            echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("UPDATE subjects SET subject_code = ?, subject_name = ?, credit = ?, type = ?, description = ?, teacher_id = ? WHERE id = ?");
            $stmt->execute([$subject_code, $subject_name, $credit, $type, $description, $teacher_id, $id]);
            echo json_encode(['status' => 'success', 'message' => 'แก้ไขข้อมูลเรียบร้อยแล้ว']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการอัปเดตข้อมูล: ' . $e->getMessage()]);
        }
        break;

    case 'delete':
        $id = $_POST['id'] ?? '';
        if (empty($id)) {
            echo json_encode(['status' => 'error', 'message' => 'ไม่พบรหัสอ้างอิงรายวิชา']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM subjects WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'ลบข้อมูลเรียบร้อยแล้ว']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการลบข้อมูล: ' . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Action ไม่ถูกต้อง']);
        break;
}
?>
