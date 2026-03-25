<?php
session_name('SCHOOL_SECURE_SESSION');
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
            // Fetch class info to determine if it's Junior High or Senior High
            $stmtClass = $pdo->prepare("SELECT class_name, level FROM classes WHERE id = ?");
            $stmtClass->execute([$class_id]);
            $classInfo = $stmtClass->fetch(PDO::FETCH_ASSOC);

            // Determine if Junior High (ม.1, ม.2, ม.3)
            $is_junior_high = false;
            if ($classInfo) {
                if (strpos($classInfo['class_name'], 'ม.1') !== false || 
                    strpos($classInfo['class_name'], 'ม.2') !== false || 
                    strpos($classInfo['class_name'], 'ม.3') !== false) {
                    $is_junior_high = true;
                }
            }

            $sql = "SELECT s.*, 
                    sub.subject_name, sub.subject_code, 
                    t.first_name AS teacher_fname, t.last_name AS teacher_lname
                    FROM schedules s
                    LEFT JOIN subjects sub ON s.subject_id = sub.id
                    LEFT JOIN teachers t ON s.teacher_id = t.id
                    WHERE s.class_id = ?";

            // Data filtering by Role
            $params = [$class_id];
            if ($_SESSION['role'] === 'teacher' && isset($_SESSION['teacher_id'])) {
                $sql .= " AND s.teacher_id = ?";
                $params[] = $_SESSION['teacher_id'];
            } elseif ($_SESSION['role'] === 'student' && isset($_SESSION['class_id'])) {
                // Students only see their own class - enforced by class_id from session if possible
                // For now, if student selects different class, let it be handled by UI or enforce here
                if ($class_id != $_SESSION['class_id']) {
                     echo json_encode(['status' => 'success', 'data' => [], 'message' => 'Unauthorized class access']);
                     exit;
                }
            }

            $sql .= " ORDER BY 
                      FIELD(s.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
                      s.start_time ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode([
                'status' => 'success', 
                'data' => $schedules,
                'class_info' => $classInfo,
                'is_junior_high' => $is_junior_high
            ]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'create':
        // Restrict to Admin
        if ($_SESSION['role'] !== 'admin') {
            echo json_encode(['status' => 'error', 'message' => 'คุณไม่มีสิทธิ์แก้ไขตารางเรียน (Admin Only)']);
            exit;
        }
        $class_id = $_POST['class_id'] ?? '';
        $subject_id = $_POST['subject_id'] ?? '';
        $teacher_id = $_POST['teacher_id'] ?? '';
        $classroom_id = null; // Removed as per request
        $day_of_week = $_POST['day_of_week'] ?? '';
        $start_time = $_POST['start_time'] ?? '';
        $end_time = $_POST['end_time'] ?? '';

        if (empty($class_id) || empty($subject_id) || empty($teacher_id) || empty($day_of_week) || empty($start_time) || empty($end_time)) {
            echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);
            exit;
        }

        try {
            // Check for time overlap for the same teacher or class
            $sqlCheck = "SELECT id FROM schedules 
                         WHERE day_of_week = ? 
                         AND (start_time < ? AND end_time > ?) 
                         AND (teacher_id = ? OR class_id = ?)";
            $stmtCheck = $pdo->prepare($sqlCheck);
            $stmtCheck->execute([$day_of_week, $end_time, $start_time, $teacher_id, $class_id]);
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

    case 'update':
        if ($_SESSION['role'] !== 'admin') {
            echo json_encode(['status' => 'error', 'message' => 'คุณไม่มีสิทธิ์แก้ไขตารางเรียน (Admin Only)']);
            exit;
        }
        $id = $_POST['id'] ?? '';
        $class_id = $_POST['class_id'] ?? '';
        $subject_id = $_POST['subject_id'] ?? '';
        $teacher_id = $_POST['teacher_id'] ?? '';
        $classroom_id = null; // Removed as per request
        $day_of_week = $_POST['day_of_week'] ?? '';
        $start_time = $_POST['start_time'] ?? '';
        $end_time = $_POST['end_time'] ?? '';

        if (empty($id) || empty($class_id) || empty($subject_id) || empty($teacher_id) || empty($day_of_week) || empty($start_time) || empty($end_time)) {
            echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);
            exit;
        }

        try {
            // Check for time overlap (Exclude current record)
            $sqlCheck = "SELECT id FROM schedules 
                         WHERE day_of_week = ? 
                         AND (start_time < ? AND end_time > ?)
                         AND (teacher_id = ? OR class_id = ?)
                         AND id != ?";
            $stmtCheck = $pdo->prepare($sqlCheck);
            $stmtCheck->execute([$day_of_week, $end_time, $start_time, $teacher_id, $class_id, $id]);
            if ($stmtCheck->rowCount() > 0) {
                echo json_encode(['status' => 'error', 'message' => 'มีตารางเวลาทับซ้อน (ครู, ห้องเรียน, หรือชั้นเรียนซ้ำในเวลาเดียวกัน)']);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE schedules SET class_id = ?, subject_id = ?, teacher_id = ?, classroom_id = ?, day_of_week = ?, start_time = ?, end_time = ? WHERE id = ?");
            $stmt->execute([$class_id, $subject_id, $teacher_id, $classroom_id, $day_of_week, $start_time, $end_time, $id]);
            echo json_encode(['status' => 'success', 'message' => 'แก้ไขข้อมูลตารางเรียนเรียบร้อยแล้ว']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'delete':
        if ($_SESSION['role'] !== 'admin') {
            echo json_encode(['status' => 'error', 'message' => 'คุณไม่มีสิทธิ์ลบตารางเรียน (Admin Only)']);
            exit;
        }
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
