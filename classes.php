<?php 
require_once 'includes/auth_check.php'; 

include 'includes/header.php'; 
include 'includes/sidebar.php'; 
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
                <i class="fas fa-layer-group me-2"></i> ระบบจัดการข้อมูลระดับชั้นเรียน
            </h3>
            <button class="btn btn-primary-custom" onclick="openAddModal()">
                <i class="fas fa-plus me-1"></i> เพิ่มระดับชั้นเรียน
            </button>
        </div>

        <div class="card dashboard-card">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>รหัสชั้นเรียน</th>
                                <th>ชื่อชั้นเรียน</th>
                                <th>ระดับช่วงชั้น</th>
                                <th class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody id="classTableBody">
                            <tr><td colspan="3" class="text-center text-muted">กำลังโหลดข้อมูล...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Class Modal -->
<div class="modal fade" id="classModal" tabindex="-1" aria-labelledby="classModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="classForm">
        <div class="modal-header bg-primary-custom text-white">
          <h5 class="modal-title" id="classModalLabel">เพิ่ม/แก้ไขระดับชั้นเรียน</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="classId" name="id">
            <input type="hidden" id="actionType" name="action" value="create">

            <div class="mb-3">
                <label for="classCode" class="form-label">รหัสชั้นเรียน</label>
                <input type="text" class="form-control" id="classCode" name="class_code" required>
            </div>
            <div class="mb-3">
                <label for="className" class="form-label">ชื่อชั้นเรียน (เช่น ม.1, ป.6)</label>
                <input type="text" class="form-control" id="className" name="class_name" required>
            </div>
            <div class="mb-3">
                <label for="level" class="form-label">ระดับช่วงชั้น (เช่น มัธยมศึกษาตอนต้น)</label>
                <input type="text" class="form-control" id="level" name="level">
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
    loadClasses();

    // Handle Form Submit
    $('#classForm').on('submit', function(e) {
        e.preventDefault();
        
        let formData = $(this).serialize();
        
        $.ajax({
            url: 'backend/classes_action.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#classModal').modal('hide');
                    loadClasses();
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

function loadClasses() {
    $.ajax({
        url: 'backend/classes_action.php',
        type: 'GET',
        data: { action: 'read' },
        dataType: 'json',
        success: function(response) {
            let html = '';
            if (response.data && response.data.length > 0) {
                $.each(response.data, function(index, cls) {
                    html += `<tr>
                                <td class="fw-semibold text-primary-custom">${cls.class_code || '-'}</td>
                                <td>${cls.class_name}</td>
                                <td>${cls.level || '-'}</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-warning me-1" onclick='openEditModal(${JSON.stringify(cls).replace(/'/g, "&apos;")})'>
                                        <i class="fas fa-edit"></i> แก้ไข
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteClass(${cls.id})">
                                        <i class="fas fa-trash-alt"></i> ลบ
                                    </button>
                                </td>
                             </tr>`;
                });
            } else {
                html = '<tr><td colspan="4" class="text-center text-muted">ไม่มีข้อมูลระดับชั้นเรียนในระบบ</td></tr>';
            }
            $('#classTableBody').html(html);
        },
        error: function() {
            $('#classTableBody').html('<tr><td colspan="4" class="text-center text-danger">เกิดข้อผิดพลาดในการโหลดข้อมูล</td></tr>');
        }
    });
}

function openAddModal() {
    $('#classForm')[0].reset();
    $('#classId').val('');
    $('#actionType').val('create');
    $('#classModalLabel').text('เพิ่มข้อมูลระดับชั้นเรียน');
    $('#classModal').modal('show');
}

function openEditModal(cls) {
    $('#classForm')[0].reset();
    $('#classId').val(cls.id);
    $('#classCode').val(cls.class_code);
    $('#className').val(cls.class_name);
    $('#level').val(cls.level);
    $('#actionType').val('update');
    $('#classModalLabel').text('แก้ไขข้อมูลระดับชั้นเรียน');
    $('#classModal').modal('show');
}

function deleteClass(id) {
    if (confirm('คุณแน่ใจหรือไม่ว่าต้องการลบข้อมูลข้อมูลระดับชั้นเรียนนี้? (อาจมีผลกระทบกับห้องเรียนที่เชื่อมโยงอยู่)')) {
        $.ajax({
            url: 'backend/classes_action.php',
            type: 'POST',
            data: { action: 'delete', id: id },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    loadClasses();
                } else {
                    alert('Error: ' + response.message);
                }
            }
        });
    }
}
</script>
