<?php
session_name('SCHOOL_SECURE_SESSION');
session_start();
require_once '../config/database.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}


$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'create':
        $room_code = $_POST['room_code'] ?? '';
        $room_name = $_POST['room_name'] ?? '';

        if (empty($room_code)) {
            echo json_encode(['status' => 'error', 'message' => 'รหัสห้องจำเป็นต้องกรอก']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO classrooms (room_code, room_name) VALUES (?, ?)");
            $stmt->execute([$room_code, $room_name]);
            echo json_encode(['status' => 'success', 'message' => 'เพิ่มห้องเรียนเรียบร้อยแล้ว']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการเพิ่มข้อมูล: ' . $e->getMessage()]);
        }
        break;

    case 'read':
        try {
            $sql = "SELECT id, room_code, room_name FROM classrooms ORDER BY room_code ASC";
            $stmt = $pdo->query($sql);
            $classrooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $classrooms]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'data' => [], 'message' => $e->getMessage()]);
        }
        break;

    case 'update':
        $id = $_POST['id'] ?? '';
        $room_code = $_POST['room_code'] ?? '';
        $room_name = $_POST['room_name'] ?? '';

        if (empty($id) || empty($room_code)) {
            echo json_encode(['status' => 'error', 'message' => 'รหัสห้องจำเป็นต้องกรอก']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("UPDATE classrooms SET room_code = ?, room_name = ? WHERE id = ?");
            $stmt->execute([$room_code, $room_name, $id]);
            echo json_encode(['status' => 'success', 'message' => 'แก้ไขข้อมูลเรียบร้อยแล้ว']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการอัปเดตข้อมูล: ' . $e->getMessage()]);
        }
        break;

    case 'delete':
        $id = $_POST['id'] ?? '';
        if (empty($id)) {
            echo json_encode(['status' => 'error', 'message' => 'ไม่พบรหัสอ้างอิงห้องเรียน']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM classrooms WHERE id = ?");
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
