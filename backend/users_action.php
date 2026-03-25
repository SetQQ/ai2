<?php
session_name('SCHOOL_SECURE_SESSION');
session_start();
require_once '../config/database.php';

// Check auth and role (Only Admins should manage users, but handling simply for now)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Forbidden']);
    exit;
}


$action = $_POST['action'] ?? '';

switch ($action) {
    case 'read':
        try {
            $stmt = $pdo->query("SELECT id, username, role, first_name, last_name, is_active, created_at FROM users ORDER BY role ASC, first_name ASC");
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $users]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'create':
        $username = $_POST['username'] ?? '';
        $raw_password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'student';
        $first_name = $_POST['first_name'] ?? '';
        $last_name = $_POST['last_name'] ?? '';
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (empty($username) || empty($raw_password) || empty($first_name)) {
            echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);
            exit;
        }

        try {
            $hashedPassword = password_hash($raw_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role, first_name, last_name, is_active) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$username, $hashedPassword, $role, $first_name, $last_name, $is_active]);
            $user_id = $pdo->lastInsertId();

            // Auto-create linked profile
            if ($role === 'teacher') {
                $teacher_code = 'TCH' . date('YmdHis'); // Placeholder
                $stmtProfile = $pdo->prepare("INSERT INTO teachers (user_id, teacher_code, first_name, last_name) VALUES (?, ?, ?, ?)");
                $stmtProfile->execute([$user_id, $teacher_code, $first_name, $last_name]);
            } elseif ($role === 'student') {
                $student_code = 'STD' . date('YmdHis'); // Placeholder
                $stmtProfile = $pdo->prepare("INSERT INTO students (user_id, student_code, first_name, last_name) VALUES (?, ?, ?, ?)");
                $stmtProfile->execute([$user_id, $student_code, $first_name, $last_name]);
            }

            echo json_encode(['status' => 'success', 'message' => 'เพิ่มข้อมูลผู้ใช้งานเรียบร้อยแล้ว']);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Unique constraint violation
                echo json_encode(['status' => 'error', 'message' => 'Username นี้มีอยู่ในระบบแล้ว']);
            } else {
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
        break;

    case 'update':
        $id = $_POST['id'] ?? 0;
        $username = $_POST['username'] ?? '';
        $role = $_POST['role'] ?? 'student';
        $first_name = $_POST['first_name'] ?? '';
        $last_name = $_POST['last_name'] ?? '';
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $new_password = $_POST['password'] ?? '';

        try {
            // Read Old Role to check if role changed
            $stmtOld = $pdo->prepare("SELECT role FROM users WHERE id = ?");
            $stmtOld->execute([$id]);
            $oldRole = $stmtOld->fetchColumn();

            if (!empty($new_password)) {
                $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET username=?, password_hash=?, role=?, first_name=?, last_name=?, is_active=? WHERE id=?");
                $stmt->execute([$username, $hashedPassword, $role, $first_name, $last_name, $is_active, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET username=?, role=?, first_name=?, last_name=?, is_active=? WHERE id=?");
                $stmt->execute([$username, $role, $first_name, $last_name, $is_active, $id]);
            }

            // If role remains the same, sync names to profile
            if ($oldRole === $role) {
                if ($role === 'teacher') {
                    $stmtProfile = $pdo->prepare("UPDATE teachers SET first_name=?, last_name=? WHERE user_id=?");
                    $stmtProfile->execute([$first_name, $last_name, $id]);
                } elseif ($role === 'student') {
                    $stmtProfile = $pdo->prepare("UPDATE students SET first_name=?, last_name=? WHERE user_id=?");
                    $stmtProfile->execute([$first_name, $last_name, $id]);
                }
            }

            echo json_encode(['status' => 'success', 'message' => 'อัปเดตข้อมูลผู้ใช้งานเรียบร้อยแล้ว']);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                echo json_encode(['status' => 'error', 'message' => 'Username นี้มีอยู่ในระบบแล้ว']);
            } else {
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
        break;

    case 'delete':
        $id = $_POST['id'] ?? 0;
        // ป้องกันไม่ให้ลบตัวเอง
        if ($id == $_SESSION['user_id']) {
            echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถลบบัญชีผู้ใช้ที่กำลังใช้งานอยู่ได้']);
            exit;
        }
        
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'ลบข้อมูลเรียบร้อยแล้ว']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
?>
