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
    case 'get_students':
        $class_id = $_POST['class_id'] ?? '';
        $attendance_date = $_POST['attendance_date'] ?? '';

        if (empty($class_id) || empty($attendance_date)) {
            echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);
            exit;
        }

        // Authorization check for Teacher
        if ($_SESSION['role'] === 'teacher') {
            $teacher_id = $_SESSION['teacher_id'] ?? 0;
            $stmtCheck = $pdo->prepare("SELECT id FROM schedules WHERE teacher_id = ? AND class_id = ? LIMIT 1");
            $stmtCheck->execute([$teacher_id, $class_id]);
            if ($stmtCheck->rowCount() === 0) {
                echo json_encode(['status' => 'error', 'message' => 'คุณไม่ได้สอนในชั้นเรียนนี้ ไม่สามารถดูรายชื่อได้']);
                exit;
            }
        }

        try {
            $sql = "SELECT s.id as student_id, s.student_code, s.first_name, s.last_name, a.status, a.remarks
                    FROM students s
                    LEFT JOIN attendance a ON s.id = a.student_id AND a.attendance_date = ?
                    WHERE s.class_id = ?
                    ORDER BY s.student_code ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$attendance_date, $class_id]);
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

        $class_level_string = $_POST['class_level'] ?? '';

        // Authorization check for Teacher
        if ($_SESSION['role'] === 'teacher') {
            $teacher_id = $_SESSION['teacher_id'] ?? 0;
            $stmtCheck = $pdo->prepare("SELECT s.id FROM schedules s 
                                         JOIN classes c ON s.class_id = c.id 
                                         WHERE s.teacher_id = ? AND c.class_name = ? LIMIT 1");
            $stmtCheck->execute([$teacher_id, $class_level_string]);
            if ($stmtCheck->rowCount() === 0) {
                echo json_encode(['status' => 'error', 'message' => 'การดำเนินการถูกปฏิเสธ: คุณไม่มีสิทธิ์บันทึกเวลาเรียนของชั้นนี้']);
                exit;
            }
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
