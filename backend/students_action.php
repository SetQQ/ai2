<?php
// backend/students_action.php
session_name('SCHOOL_SECURE_SESSION');
session_start();
require_once '../config/database.php';

// Allow only Admin or Teacher to manage
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}


header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$uploadDir = '../uploads/profiles/';

// Create directory if it doesn't exist securely
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Function to handle secure image upload
function handleImageUpload() {
    global $uploadDir;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profile_image']['tmp_name'];
        $fileSize = $_FILES['profile_image']['size'];
        $fileName = $_FILES['profile_image']['name'];
        
        // 1. Verify MIME type using Fileinfo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $fileTmpPath);
        finfo_close($finfo);
        
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($mime, $allowedMimeTypes)) {
            echo json_encode(['status' => 'error', 'message' => 'ประเภทไฟล์ไม่ถูกต้อง (อัพโหลดได้เฉพาะ JPG, PNG, GIF)']);
            exit;
        }

        // 2. Validate File Size (Limit to 2MB)
        if ($fileSize > 2097152) {
            echo json_encode(['status' => 'error', 'message' => 'ขนาดไฟล์รูปภาพเกิน 2MB']);
            exit;
        }

        // 3. Generate secure random filename
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $newFileName = bin2hex(random_bytes(16)) . '.' . $extension;
        $destPath = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            return $newFileName;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดระหว่างอัพโหลดไฟล์รูปภาพ']);
            exit;
        }
    }
    return null;
}

try {
    switch ($action) {
        case 'create':
            $profileImage = handleImageUpload();
            $stmt = $pdo->prepare("INSERT INTO students (student_code, first_name, last_name, class_level, phone, parent_phone, dob, profile_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['student_code'],
                $_POST['first_name'],
                $_POST['last_name'],
                $_POST['class_level'] ?? null,
                $_POST['phone'] ?? null,
                $_POST['parent_phone'] ?? null,
                !empty($_POST['dob']) ? $_POST['dob'] : null,
                $profileImage
            ]);
            echo json_encode(['status' => 'success', 'message' => 'เพิ่มข้อมูลสำเร็จ']);
            break;

        case 'read':
            $stmt = $pdo->query("SELECT * FROM students ORDER BY created_at DESC");
            $students = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $students]);
            break;

        case 'update':
            $profileImage = handleImageUpload();
            $id = $_POST['id'];
            
            if ($profileImage) {
                // Fetch old image & delete it if new image uploaded
                $stmt = $pdo->prepare("SELECT profile_image FROM students WHERE id = ?");
                $stmt->execute([$id]);
                $oldImage = $stmt->fetchColumn();
                if ($oldImage && file_exists($uploadDir . $oldImage)) {
                    unlink($uploadDir . $oldImage);
                }
                
                $stmt = $pdo->prepare("UPDATE students SET student_code = ?, first_name = ?, last_name = ?, class_level = ?, phone = ?, parent_phone = ?, dob = ?, profile_image = ? WHERE id = ?");
                $stmt->execute([$_POST['student_code'], $_POST['first_name'], $_POST['last_name'], $_POST['class_level'] ?? null, $_POST['phone'] ?? null, $_POST['parent_phone'] ?? null, !empty($_POST['dob']) ? $_POST['dob'] : null, $profileImage, $id]);
            } else {
                // Update without changing image
                $stmt = $pdo->prepare("UPDATE students SET student_code = ?, first_name = ?, last_name = ?, class_level = ?, phone = ?, parent_phone = ?, dob = ? WHERE id = ?");
                $stmt->execute([$_POST['student_code'], $_POST['first_name'], $_POST['last_name'], $_POST['class_level'] ?? null, $_POST['phone'] ?? null, $_POST['parent_phone'] ?? null, !empty($_POST['dob']) ? $_POST['dob'] : null, $id]);
            }
            echo json_encode(['status' => 'success', 'message' => 'แก้ไขข้อมูลสำเร็จ']);
            break;

        case 'delete':
            $id = $_POST['id'];
            // Fetch old image & delete it
            $stmt = $pdo->prepare("SELECT profile_image FROM students WHERE id = ?");
            $stmt->execute([$id]);
            $oldImage = $stmt->fetchColumn();
            if ($oldImage && file_exists($uploadDir . $oldImage)) {
                unlink($uploadDir . $oldImage);
            }

            $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'ลบข้อมูลสำเร็จ']);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
} catch (PDOException $e) {
    if ($e->getCode() == 23000) { 
        echo json_encode(['status' => 'error', 'message' => 'รหัสประจำตัวนักเรียนซ้ำในระบบ']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
