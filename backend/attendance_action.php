<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'get_students':
        $class_level = $_POST['class_level'] ?? '';
        $attendance_date = $_POST['attendance_date'] ?? '';

        if (empty($class_level) || empty($attendance_date)) {
            echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);
            exit;
        }

        try {
            $sql = "SELECT s.id as student_id, s.student_code, s.first_name, s.last_name, a.status, a.remarks
                    FROM students s
                    LEFT JOIN attendance a ON s.id = a.student_id AND a.attendance_date = ?
                    WHERE s.class_level = ?
                    ORDER BY s.student_code ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$attendance_date, $class_level]);
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['status' => 'success', 'data' => $students]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'save_attendance':
        $attendance_data = json_decode($_POST['attendance_data'] ?? '[]', true);
        $attendance_date = $_POST['attendance_date'] ?? '';
        
        $class_id = 1; // Since class_level is a string in student table, we can just log a default or query the real class ID if necessary. For attendance tracking by class_level, the unique constraint handles date + student_id. I will just pass a hardcoded 0 or fetch from classes. Wait, attendance schema expects class_id. I will set it to 0 or we can bypass it if it's not strictly necessary. But schema has: class_id INT NOT NULL. Let's fix that in logic.
        // Actually, we can look up the class_id based on class_level string, or just use 0 if the FK allows it. Wait, if it has a foreign key to `classes(id)`, we must find the valid `classes.id`.
        
        if (empty($attendance_data) || !is_array($attendance_data) || empty($attendance_date)) {
            echo json_encode(['status' => 'error', 'message' => 'ไม่มีข้อมูลสำหรับบันทึก']);
            exit;
        }

        try {
            // Find class_id from class_level string (if it matches class_name)
            // Just for safety if FK constraint is strict
            $class_level_string = $_POST['class_level'] ?? '';
            $stmtClass = $pdo->prepare("SELECT id FROM classes WHERE class_name = ? LIMIT 1");
            $stmtClass->execute([$class_level_string]);
            $classRow = $stmtClass->fetch();
            $class_id = $classRow ? $classRow['id'] : 1; // fallback to 1

            $pdo->beginTransaction();
            $sql = "INSERT INTO attendance (student_id, class_id, attendance_date, status, remarks) 
                    VALUES (?, ?, ?, ?, ?) 
                    ON DUPLICATE KEY UPDATE status = VALUES(status), remarks = VALUES(remarks)";
            $stmt = $pdo->prepare($sql);
            
            foreach ($attendance_data as $row) {
                if (empty($row['status'])) continue;
                
                $remarks = $row['remarks'] ?? '';
                $stmt->execute([
                    $row['student_id'],
                    $class_id,
                    $attendance_date,
                    $row['status'],
                    $remarks
                ]);
            }
            
            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'บันทึกการเช็คชื่อเรียบร้อยแล้ว']);
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
?>
