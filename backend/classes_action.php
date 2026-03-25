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
        $class_code = $_POST['class_code'] ?? null;
        if(empty($class_code)) $class_code = null;
        $class_name = $_POST['class_name'] ?? '';
        $level = $_POST['level'] ?? null;
        if(empty($level)) $level = null;

        if (empty($class_name)) {
            echo json_encode(['status' => 'error', 'message' => 'บรรทัดชื่อชั้นเรียนห้ามว่างเปล่า']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO classes (class_code, class_name, level) VALUES (?, ?, ?)");
            $stmt->execute([$class_code, $class_name, $level]);
            echo json_encode(['status' => 'success', 'message' => 'เพิ่มระดับชั้นเรียนเรียบร้อยแล้ว']);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                echo json_encode(['status' => 'error', 'message' => 'ชื่อชั้นเรียนหรือรหัสชั้นเรียน นี้มีอยู่ในระบบแล้ว']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการเพิ่มข้อมูล: ' . $e->getMessage()]);
            }
        }
        break;

    case 'read':
        try {
            // Sort intuitively if possible, otherwise by id
            $stmt = $pdo->query("SELECT * FROM classes ORDER BY id ASC");
            $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $classes]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'data' => [], 'message' => $e->getMessage()]);
        }
        break;

    case 'update':
        $id = $_POST['id'] ?? '';
        $class_code = $_POST['class_code'] ?? null;
        if(empty($class_code)) $class_code = null;
        $class_name = $_POST['class_name'] ?? '';
        $level = $_POST['level'] ?? null;
        if(empty($level)) $level = null;

        if (empty($id) || empty($class_name)) {
            echo json_encode(['status' => 'error', 'message' => 'โปรดกรอกชื่อชั้นเรียนให้ครบถ้วน']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("UPDATE classes SET class_code = ?, class_name = ?, level = ? WHERE id = ?");
            $stmt->execute([$class_code, $class_name, $level, $id]);
            echo json_encode(['status' => 'success', 'message' => 'แก้ไขข้อมูลเรียบร้อยแล้ว']);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                echo json_encode(['status' => 'error', 'message' => 'ชื่อชั้นเรียนหรือรหัสชั้นเรียน นี้มีอยู่ในระบบแล้ว']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการอัปเดตข้อมูล: ' . $e->getMessage()]);
            }
        }
        break;

    case 'delete':
        $id = $_POST['id'] ?? '';
        if (empty($id)) {
            echo json_encode(['status' => 'error', 'message' => 'ไม่พบรหัสอ้างอิงระดับชั้นเรียน']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM classes WHERE id = ?");
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
