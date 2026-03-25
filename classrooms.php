<?php 
require_once 'includes/auth_check.php'; 
checkRole(['admin']); 
require_once 'config/database.php';

include 'includes/header.php'; 
include 'includes/sidebar.php'; 

// Fetch classes for dropdown
$stmtClasses = $pdo->query("SELECT id, class_name FROM classes ORDER BY id ASC");
$classesList = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);

// Fetch teachers for dropdown
$stmtTeachers = $pdo->query("SELECT id, first_name, last_name FROM teachers ORDER BY first_name ASC");
$teachersList = $stmtTeachers->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Main Content -->
<div id="content">
    
    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top shadow-sm">
        <div class="container-fluid">
            <!-- Sidebar Toggle Button -->
            <button type="button" id="sidebarToggle" class="btn btn-primary-custom">
                <i class="fas fa-bars"></i>
            </button>
            <ul class="navbar-nav ms-auto align-items-center flex-row">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="bg-primary-custom text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <span class="d-none d-sm-inline fw-semibold text-dark"><?= htmlspecialchars($_SESSION['first_name'] ?? 'User') ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="navbarDropdownMenuLink">
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> ออกจากระบบ</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Page Content -->
    <div class="container-fluid p-4">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-primary-custom mb-0">
                <i class="fas fa-door-open me-2"></i> ระบบจัดการข้อมูลห้องเรียน
            </h3>
            <button class="btn btn-primary-custom" onclick="openAddModal()">
                <i class="fas fa-plus me-1"></i> เพิ่มห้องเรียน
            </button>
        </div>

        <div class="card dashboard-card">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>รหัสห้อง (Room Code)</th>
                                <th>ระดับชั้น</th>
                                <th>ครูประจำชั้น</th>
                                <th>ความจุ (คน)</th>
                                <th class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody id="classroomTableBody">
                            <tr><td colspan="4" class="text-center text-muted">กำลังโหลดข้อมูล...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Classroom Modal -->
<div class="modal fade" id="classroomModal" tabindex="-1" aria-labelledby="classroomModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="classroomForm">
        <div class="modal-header bg-primary-custom text-white">
          <h5 class="modal-title" id="classroomModalLabel">เพิ่ม/แก้ไขห้องเรียน</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="classroomId" name="id">
            <input type="hidden" id="actionType" name="action" value="create">

            <div class="mb-3">
                <label for="roomCode" class="form-label">รหัสห้อง (เช่น R401)</label>
                <input type="text" class="form-control" id="roomCode" name="room_code" required>
            </div>
            <div class="mb-3">
                <label for="classId" class="form-label">ระดับชั้นเรียน</label>
                <select class="form-select" id="classId" name="class_id" required>
                    <option value="">-- เลือกระดับชั้นเรียน --</option>
                    <?php foreach($classesList as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['class_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="teacherId" class="form-label">ครูประจำชั้น</label>
                <select class="form-select" id="teacherId" name="teacher_id">
                    <option value="">-- ไม่ระบุ --</option>
                    <?php foreach($teachersList as $t): ?>
                        <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="capacity" class="form-label">ความจุนักเรียน (คน)</label>
                <input type="number" class="form-control" id="capacity" name="capacity" value="40" min="1" required>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
          <button type="submit" class="btn btn-primary-custom">บันทึกข้อมูล</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
$(document).ready(function() {
    loadClassrooms();

    // Handle Form Submit
    $('#classroomForm').on('submit', function(e) {
        e.preventDefault();
        
        let formData = $(this).serialize();
        
        $.ajax({
            url: 'backend/classrooms_action.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#classroomModal').modal('hide');
                    loadClassrooms();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
            }
        });
    });
});

function loadClassrooms() {
    $.ajax({
        url: 'backend/classrooms_action.php',
        type: 'GET',
        data: { action: 'read' },
        dataType: 'json',
        success: function(response) {
            let html = '';
            if (response.data && response.data.length > 0) {
                $.each(response.data, function(index, room) {
                    let teacherName = room.teacher_id ? `${room.teacher_first_name} ${room.teacher_last_name}` : '<span class="text-muted">ไม่ระบุ</span>';
                    
                    html += `<tr>
                                <td class="fw-semibold text-primary-custom">${room.room_code || '-'}</td>
                                <td>${room.class_name}</td>
                                <td>${teacherName}</td>
                                <td>${room.capacity}</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-warning me-1" onclick='openEditModal(${JSON.stringify(room).replace(/'/g, "&apos;")})'>
                                        <i class="fas fa-edit"></i> แก้ไข
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteClassroom(${room.id})">
                                        <i class="fas fa-trash-alt"></i> ลบ
                                    </button>
                                </td>
                             </tr>`;
                });
            } else {
                html = '<tr><td colspan="5" class="text-center text-muted">ไม่มีข้อมูลห้องเรียนในระบบ</td></tr>';
            }
            $('#classroomTableBody').html(html);
        },
        error: function() {
            $('#classroomTableBody').html('<tr><td colspan="5" class="text-center text-danger">เกิดข้อผิดพลาดในการโหลดข้อมูล</td></tr>');
        }
    });
}

function openAddModal() {
    $('#classroomForm')[0].reset();
    $('#classroomId').val('');
    $('#actionType').val('create');
    $('#classroomModalLabel').text('เพิ่มข้อมูลห้องเรียน');
    $('#classroomModal').modal('show');
}

function openEditModal(room) {
    $('#classroomForm')[0].reset();
    $('#classroomId').val(room.id);
    $('#roomCode').val(room.room_code);
    $('#classId').val(room.class_id);
    $('#teacherId').val(room.teacher_id || '');
    $('#capacity').val(room.capacity);
    $('#actionType').val('update');
    $('#classroomModalLabel').text('แก้ไขข้อมูลห้องเรียน');
    $('#classroomModal').modal('show');
}

function deleteClassroom(id) {
    if (confirm('คุณแน่ใจหรือไม่ว่าต้องการลบข้อมูลข้อมูลห้องเรียนนี้?')) {
        $.ajax({
            url: 'backend/classrooms_action.php',
            type: 'POST',
            data: { action: 'delete', id: id },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    loadClassrooms();
                } else {
                    alert('Error: ' + response.message);
                }
            }
        });
    }
}
</script>
