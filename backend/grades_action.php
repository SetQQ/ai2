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
        $subject_id = $_POST['subject_id'] ?? 0;
        $semester = $_POST['semester'] ?? 0;
        $academic_year = $_POST['academic_year'] ?? '';

        if (empty($class_id) || empty($subject_id) || empty($semester) || empty($academic_year)) {
            echo json_encode(['status' => 'error', 'message' => 'กรุณาระบุข้อมูลให้ครบถ้วน']);
            exit;
        }

        // Authorization check for Teacher
        if ($_SESSION['role'] === 'teacher') {
            $teacher_id = $_SESSION['teacher_id'] ?? 0;
            $stmtCheck = $pdo->prepare("SELECT id FROM schedules WHERE teacher_id = ? AND subject_id = ? LIMIT 1");
            $stmtCheck->execute([$teacher_id, $subject_id]);
            if ($stmtCheck->rowCount() === 0) {
                echo json_encode(['status' => 'error', 'message' => 'คุณไม่ได้สอนวิชานี้ ไม่สามารถจัดการคะแนนได้']);
                exit;
            }
        }

        try {
            $sql = "SELECT s.id as student_id, s.student_code, s.first_name, s.last_name, g.score, g.grade,
                           (SELECT COUNT(*) FROM attendance a WHERE a.student_id = s.id AND a.class_id = ? AND a.status = 'present') as present_days,
                           (SELECT COUNT(*) FROM attendance a WHERE a.student_id = s.id AND a.class_id = ?) as total_days
                    FROM students s
                    LEFT JOIN grades g ON s.id = g.student_id 
                       AND g.subject_id = ? 
                       AND g.semester = ? 
                       AND g.academic_year = ?
                    WHERE s.class_id = ?
                    ORDER BY s.student_code ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$class_id, $class_id, $subject_id, $semester, $academic_year, $class_id]);
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['status' => 'success', 'data' => $students]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'save_grades':
        $grades_data = json_decode($_POST['grades_data'] ?? '[]', true);
        
        if (empty($grades_data) || !is_array($grades_data)) {
            echo json_encode(['status' => 'error', 'message' => 'ไม่มีข้อมูลสำหรับบันทึก']);
            exit;
        }

        if ($_SESSION['role'] === 'student') {
            echo json_encode(['status' => 'error', 'message' => 'นักเรียนไม่มีสิทธิ์บันทึกคะแนน']);
            exit;
        }

        // Authorization check for Teacher - checking first row subject_id
        if ($_SESSION['role'] === 'teacher' && !empty($grades_data)) {
            $teacher_id = $_SESSION['teacher_id'] ?? 0;
            $subject_id = $grades_data[0]['subject_id'] ?? 0;
            $stmtCheck = $pdo->prepare("SELECT id FROM schedules WHERE teacher_id = ? AND subject_id = ? LIMIT 1");
            $stmtCheck->execute([$teacher_id, $subject_id]);
            if ($stmtCheck->rowCount() === 0) {
                echo json_encode(['status' => 'error', 'message' => 'คุณไม่มีสิทธิ์บันทึกคะแนนในวิชานี้']);
                exit;
            }
        }

        try {
            $pdo->beginTransaction();
            $sql = "INSERT INTO grades (student_id, subject_id, semester, academic_year, score, grade) 
                    VALUES (?, ?, ?, ?, ?, ?) 
                    ON DUPLICATE KEY UPDATE score = VALUES(score), grade = VALUES(grade)";
            $stmt = $pdo->prepare($sql);
            
            foreach ($grades_data as $row) {
                // If score is empty string, we can insert NULL
                $score = ($row['score'] === '') ? null : $row['score'];
                $grade = ($row['grade'] === '') ? null : $row['grade'];
                
                $stmt->execute([
                    $row['student_id'],
                    $row['subject_id'],
                    $row['semester'],
                    $row['academic_year'],
                    $score,
                    $grade
                ]);
            }
            
            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'บันทึกคะแนนเรียบร้อยแล้ว']);
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'get_my_grades':
        if ($_SESSION['role'] !== 'student') {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit;
        }
        $student_id = $_SESSION['student_id'] ?? 0;
        try {
            // Fetch grades and also current class attendance summary
            $sql = "SELECT g.*, sub.subject_name, sub.subject_code,
                           (SELECT COUNT(*) FROM attendance a JOIN students s ON a.student_id = s.id WHERE a.student_id = g.student_id AND a.class_id = s.class_id AND a.status = 'present') as present_days,
                           (SELECT COUNT(*) FROM attendance a JOIN students s ON a.student_id = s.id WHERE a.student_id = g.student_id AND a.class_id = s.class_id) as total_days
                    FROM grades g
                    JOIN subjects sub ON g.subject_id = sub.id
                    WHERE g.student_id = ?
                    ORDER BY g.academic_year DESC, g.semester DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$student_id]);
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;
}
?>
