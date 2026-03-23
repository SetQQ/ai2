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
        $subject_id = $_POST['subject_id'] ?? 0;
        $semester = $_POST['semester'] ?? 0;
        $academic_year = $_POST['academic_year'] ?? '';

        if (empty($class_level) || empty($subject_id) || empty($semester) || empty($academic_year)) {
            echo json_encode(['status' => 'error', 'message' => 'กรุณางระบุข้อมูลให้ครบถ้วน']);
            exit;
        }

        try {
            $sql = "SELECT s.id as student_id, s.student_code, s.first_name, s.last_name, g.score, g.grade 
                    FROM students s
                    LEFT JOIN grades g ON s.id = g.student_id 
                       AND g.subject_id = ? 
                       AND g.semester = ? 
                       AND g.academic_year = ?
                    WHERE s.class_level = ?
                    ORDER BY s.student_code ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$subject_id, $semester, $academic_year, $class_level]);
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

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
?>
