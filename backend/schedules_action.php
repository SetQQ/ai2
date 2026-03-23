<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'read':
        $class_id = $_POST['class_id'] ?? 0;
        if (empty($class_id)) {
            echo json_encode(['status' => 'success', 'data' => []]);
            exit;
        }

        try {
            $sql = "SELECT s.*, 
                    sub.subject_name, sub.subject_code, 
                    t.first_name AS teacher_fname, t.last_name AS teacher_lname,
                    c.room_name
                    FROM schedules s
                    LEFT JOIN subjects sub ON s.subject_id = sub.id
                    LEFT JOIN teachers t ON s.teacher_id = t.id
                    LEFT JOIN classrooms c ON s.classroom_id = c.id
                    WHERE s.class_id = ?
                    ORDER BY 
                      FIELD(s.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
                      s.start_time ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$class_id]);
            $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $schedules]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'create':
        $class_id = $_POST['class_id'] ?? '';
        $subject_id = $_POST['subject_id'] ?? '';
        $teacher_id = $_POST['teacher_id'] ?? '';
        $classroom_id = $_POST['classroom_id'] ?? '';
        $day_of_week = $_POST['day_of_week'] ?? '';
        $start_time = $_POST['start_time'] ?? '';
        $end_time = $_POST['end_time'] ?? '';

        if (empty($class_id) || empty($subject_id) || empty($teacher_id) || empty($classroom_id) || empty($day_of_week) || empty($start_time) || empty($end_time)) {
            echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);
            exit;
        }

        try {
            // Check for time overlap for the same teacher or classroom
            $sqlCheck = "SELECT id FROM schedules 
                         WHERE day_of_week = ? 
                         AND ((start_time < ? AND end_time > ?) OR (start_time < ? AND end_time > ?))
                         AND (teacher_id = ? OR classroom_id = ? OR class_id = ?)";
            $stmtCheck = $pdo->prepare($sqlCheck);
            $stmtCheck->execute([$day_of_week, $end_time, $start_time, $end_time, $start_time, $teacher_id, $classroom_id, $class_id]);
            if ($stmtCheck->rowCount() > 0) {
                echo json_encode(['status' => 'error', 'message' => 'มีตารางเวลาทับซ้อน (ครู, ห้องเรียน, หรือชั้นเรียนซ้ำในเวลาเดียวกัน)']);
                exit;
            }

            $stmt = $pdo->prepare("INSERT INTO schedules (class_id, subject_id, teacher_id, classroom_id, day_of_week, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$class_id, $subject_id, $teacher_id, $classroom_id, $day_of_week, $start_time, $end_time]);
            echo json_encode(['status' => 'success', 'message' => 'เพิ่มข้อมูลตารางเรียนเรียบร้อยแล้ว']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'delete':
        $id = $_POST['id'] ?? 0;
        try {
            $stmt = $pdo->prepare("DELETE FROM schedules WHERE id=?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'ลบข้อมูลคาบเรียนเรียบร้อยแล้ว']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
?>
