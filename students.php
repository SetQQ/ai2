<?php 
require_once 'includes/auth_check.php'; 
require_once 'setup_students_table.php'; // Ensure table exists
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
                <i class="fas fa-user-graduate me-2"></i> ระบบจัดการข้อมูลนักเรียน
            </h3>
            <button class="btn btn-primary-custom" onclick="openAddModal()">
                <i class="fas fa-plus me-1"></i> เพิ่มข้อมูลนักเรียน
            </button>
        </div>

        <div class="card dashboard-card">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>รหัสนักเรียน</th>
                                <th>รูปโปรไฟล์</th>
                                <th>ชื่อ-นามสกุล</th>
                                <th>วันเกิด</th>
                                <th>ระดับชั้น</th>
                                <th>การติดต่อ</th>
                                <th class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody id="studentTableBody">
                            <tr><td colspan="6" class="text-center text-muted">กำลังโหลดข้อมูล...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Student Modal -->
<div class="modal fade" id="studentModal" tabindex="-1" aria-labelledby="studentModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="studentForm" enctype="multipart/form-data">
        <div class="modal-header bg-primary-custom text-white">
          <h5 class="modal-title" id="studentModalLabel">เพิ่ม/แก้ไขข้อมูลนักเรียน</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="studentId" name="id">
            <input type="hidden" id="actionType" name="action" value="create">

            <div class="mb-3">
                <label for="studentCode" class="form-label">รหัสประจำตัวนักเรียน (Student ID)</label>
                <input type="text" class="form-control" id="studentCode" name="student_code" required>
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
                    <label for="classLevel" class="form-label">ระดับชั้นเรียน (เช่น ม.1/1)</label>
                    <input type="text" class="form-control" id="classLevel" name="class_level">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="dob" class="form-label">วัน/เดือน/ปีเกิด</label>
                    <input type="date" class="form-control" id="dob" name="dob">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label">เบอร์โทรศัพท์นักเรียน</label>
                    <input type="text" class="form-control" id="phone" name="phone">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="parentPhone" class="form-label">เบอร์ผู้ปกครอง</label>
                    <input type="text" class="form-control" id="parentPhone" name="parent_phone">
                </div>
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
    loadStudents();

    // Handle Form Submit (Add/Edit)
    $('#studentForm').on('submit', function(e) {
        e.preventDefault();
        
        let formData = new FormData(this);
        
        $.ajax({
            url: 'backend/students_action.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#studentModal').modal('hide');
                    loadStudents();
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

function loadStudents() {
    $.ajax({
        url: 'backend/students_action.php',
        type: 'GET',
        data: { action: 'read' },
        dataType: 'json',
        success: function(response) {
            let html = '';
            if (response.data.length > 0) {
                $.each(response.data, function(index, student) {
                    let defaultAvatar = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(student.first_name) + '&background=random&color=fff';
                    let imgSrc = student.profile_image ? 'uploads/profiles/' + student.profile_image : defaultAvatar;
                    
                    let phoneTxt = student.phone ? `นร: ${student.phone}` : 'นร: -';
                    let parentPhoneTxt = student.parent_phone ? `<br><small class="text-muted">ผปค: ${student.parent_phone}</small>` : '';
                    
                    // Format dob nicely
                    let dobTxt = '-';
                    if (student.dob) {
                        let d = new Date(student.dob);
                        dobTxt = d.toLocaleDateString('th-TH', { year: 'numeric', month: 'short', day: 'numeric' });
                    }

                    html += `<tr>
                                <td class="fw-semibold text-primary-custom">${student.student_code}</td>
                                <td><img src="${imgSrc}" class="rounded-circle shadow-sm" style="width: 45px; height: 45px; object-fit: cover;" alt="Profile"></td>
                                <td>${student.first_name} ${student.last_name}</td>
                                <td>${dobTxt}</td>
                                <td><span class="badge bg-info text-dark">${student.class_level || '-'}</span></td>
                                <td>${phoneTxt} ${parentPhoneTxt}</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-warning me-1" onclick='openEditModal(${JSON.stringify(student).replace(/'/g, "&apos;")})'>
                                        <i class="fas fa-edit"></i> แก้ไข
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteStudent(${student.id})">
                                        <i class="fas fa-trash-alt"></i> ลบ
                                    </button>
                                </td>
                             </tr>`;
                });
            } else {
                html = '<tr><td colspan="6" class="text-center text-muted">ไม่มีข้อมูลนักเรียนในระบบ</td></tr>';
            }
            $('#studentTableBody').html(html);
        }
    });
}

function openAddModal() {
    $('#studentForm')[0].reset();
    $('#studentId').val('');
    $('#actionType').val('create');
    $('#studentModalLabel').text('เพิ่มข้อมูลนักเรียน');
    $('#studentModal').modal('show');
}

function openEditModal(student) {
    $('#studentForm')[0].reset();
    $('#studentId').val(student.id);
    $('#studentCode').val(student.student_code);
    $('#firstName').val(student.first_name);
    $('#lastName').val(student.last_name);
    $('#dob').val(student.dob);
    $('#classLevel').val(student.class_level);
    $('#phone').val(student.phone);
    $('#parentPhone').val(student.parent_phone);
    $('#profileImage').val(''); // Clear file input
    $('#actionType').val('update');
    $('#studentModalLabel').text('แก้ไขข้อมูลนักเรียน');
    $('#studentModal').modal('show');
}

function deleteStudent(id) {
    if (confirm('คุณแน่ใจหรือไม่ว่าต้องการลบข้อมูลนักเรียนนี้?')) {
        $.ajax({
            url: 'backend/students_action.php',
            type: 'POST',
            data: { action: 'delete', id: id },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    loadStudents();
                } else {
                    alert('Error: ' + response.message);
                }
            }
        });
    }
}
</script>
