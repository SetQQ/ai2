<?php 
require_once 'includes/auth_check.php'; 
require_once 'setup_teachers_table.php'; // Ensure table exists
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
                <i class="fas fa-chalkboard-teacher me-2"></i> ระบบจัดการข้อมูลบุคลากรครู
            </h3>
            <button class="btn btn-primary-custom" onclick="openAddModal()">
                <i class="fas fa-plus me-1"></i> เพิ่มข้อมูลครู
            </button>
        </div>

        <div class="card dashboard-card">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>รหัสประจำตัว</th>
                                <th>รูปโปรไฟล์</th>
                                <th>ชื่อ-นามสกุล</th>
                                <th>การติดต่อ</th>
                                <th>หมวดวิชา</th>
                                <th class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody id="teacherTableBody">
                            <tr><td colspan="5" class="text-center text-muted">กำลังโหลดข้อมูล...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Teacher Modal -->
<div class="modal fade" id="teacherModal" tabindex="-1" aria-labelledby="teacherModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="teacherForm" enctype="multipart/form-data">
        <div class="modal-header bg-primary-custom text-white">
          <h5 class="modal-title" id="teacherModalLabel">เพิ่ม/แก้ไขข้อมูลครู</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="teacherId" name="id">
            <input type="hidden" id="actionType" name="action" value="create">

            <div class="mb-3">
                <label for="teacherCode" class="form-label">รหัสประจำตัวครู (Teacher ID)</label>
                <input type="text" class="form-control" id="teacherCode" name="teacher_code" required>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="firstName" class="form-label">ชื่อ</label>
                    <input type="text" class="form-control" id="firstName" name="first_name" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="lastName" class="form-label">นามสกุล</label>
                    <input type="text" class="form-control" id="lastName" name="last_name" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label">เบอร์โทรศัพท์</label>
                    <input type="text" class="form-control" id="phone" name="phone">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="lineId" class="form-label">Line ID</label>
                    <input type="text" class="form-control" id="lineId" name="line_id">
                </div>
            </div>
            <div class="mb-3">
                <label for="department" class="form-label">หมวดวิชาที่สังกัด <span class="text-danger">*</span></label>
                <select class="form-select" id="department" name="department" required>
                    <option value="" disabled selected>เลือกหมวดวิชา</option>
                    <option value="วิทยาศาสตร์">วิทยาศาสตร์</option>
                    <option value="คณิตศาสตร์">คณิตศาสตร์</option>
                    <option value="ภาษาไทย">ภาษาไทย</option>
                    <option value="ภาษาต่างประเทศ">ภาษาต่างประเทศ</option>
                    <option value="สังคมศึกษา">สังคมศึกษา</option>
                    <option value="ศิลปะ">ศิลปะ</option>
                    <option value="พลศึกษา">พลศึกษา</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="profileImage" class="form-label">รูปโปรไฟล์ (ไม่บังคับ - ไฟล์ JPG, PNG หรือ GIF เท่านั้น)</label>
                <input class="form-control" type="file" id="profileImage" name="profile_image" accept="image/jpeg, image/png, image/gif">
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
    loadTeachers();

    // Handle Form Submit (Add/Edit)
    $('#teacherForm').on('submit', function(e) {
        e.preventDefault();
        
        let formData = new FormData(this);
        
        $.ajax({
            url: 'backend/teachers_action.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#teacherModal').modal('hide');
                    loadTeachers();
                    // Optionally, show a success toast here
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

function loadTeachers() {
    $.ajax({
        url: 'backend/teachers_action.php',
        type: 'GET',
        data: { action: 'read' },
        dataType: 'json',
        success: function(response) {
            let html = '';
            if (response.data.length > 0) {
                $.each(response.data, function(index, teacher) {
                    let defaultAvatar = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(teacher.first_name) + '&background=random&color=fff';
                    let imgSrc = teacher.profile_image ? 'uploads/profiles/' + teacher.profile_image : defaultAvatar;
                    let lineTxt = teacher.line_id ? `<br><small class="text-success"><i class="fab fa-line"></i> ${teacher.line_id}</small>` : '';
                    let phoneTxt = teacher.phone ? `<i class="fas fa-phone-alt small text-muted"></i> ${teacher.phone}` : '-';

                    html += `<tr>
                                <td class="fw-semibold text-primary-custom">${teacher.teacher_code}</td>
                                <td><img src="${imgSrc}" class="rounded-circle shadow-sm" style="width: 45px; height: 45px; object-fit: cover;" alt="Profile"></td>
                                <td>${teacher.first_name} ${teacher.last_name}</td>
                                <td>${phoneTxt} ${lineTxt}</td>
                                <td><span class="badge bg-secondary">${teacher.department}</span></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-warning me-1" onclick='openEditModal(${JSON.stringify(teacher).replace(/'/g, "&apos;")})'>
                                        <i class="fas fa-edit"></i> แก้ไข
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteTeacher(${teacher.id})">
                                        <i class="fas fa-trash-alt"></i> ลบ
                                    </button>
                                </td>
                             </tr>`;
                });
            } else {
                html = '<tr><td colspan="6" class="text-center text-muted">ไม่มีข้อมูลบุคลากรครูในระบบ</td></tr>';
            }
            $('#teacherTableBody').html(html);
        }
    });
}

function openAddModal() {
    $('#teacherForm')[0].reset();
    $('#teacherId').val('');
    $('#actionType').val('create');
    $('#teacherModalLabel').text('เพิ่มข้อมูลครู');
    $('#teacherModal').modal('show');
}

function openEditModal(teacher) {
    $('#teacherForm')[0].reset();
    $('#teacherId').val(teacher.id);
    $('#teacherCode').val(teacher.teacher_code);
    $('#firstName').val(teacher.first_name);
    $('#lastName').val(teacher.last_name);
    $('#phone').val(teacher.phone);
    $('#lineId').val(teacher.line_id);
    $('#department').val(teacher.department);
    $('#profileImage').val(''); // Clear file input
    $('#actionType').val('update');
    $('#teacherModalLabel').text('แก้ไขข้อมูลครู');
    $('#teacherModal').modal('show');
}

function deleteTeacher(id) {
    if (confirm('คุณแน่ใจหรือไม่ว่าต้องการลบข้อมูลครูท่านนี้?')) {
        $.ajax({
            url: 'backend/teachers_action.php',
            type: 'POST',
            data: { action: 'delete', id: id },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    loadTeachers();
                } else {
                    alert('Error: ' + response.message);
                }
            }
        });
    }
}
</script>
