<?php 
require_once 'includes/auth_check.php'; 
checkRole(['admin']); 
require_once 'setup_students_table.php';

try {
    $classes = $pdo->query("SELECT id, class_name FROM classes ORDER BY class_name ASC")->fetchAll();
} catch (PDOException $e) {
    $classes = [];
}
include 'includes/header.php'; 
include 'includes/sidebar.php'; 
?>

<!-- Main Content -->
<div id="content">
    
    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top shadow-sm bg-light">
        <div class="container-fluid">
            <!-- Sidebar Toggle Button -->
            <button type="button" id="sidebarToggle" class="btn btn-outline-secondary me-3">
                <i class="fas fa-bars"></i>
            </button>
            <h5 class="mb-0 fw-bold d-none d-md-block">ระบบจัดการข้อมูลนักเรียน</h5>
            <ul class="navbar-nav ms-auto align-items-center flex-row">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdownMenuLink" data-bs-toggle="dropdown">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <span class="d-none d-sm-inline fw-semibold"><?= htmlspecialchars($_SESSION['first_name'] ?? 'User') ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> ออกจากระบบ</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Page Content -->
    <div class="container-fluid p-4">
        
        <div class="row align-items-center mb-4">
            <div class="col-md-6">
                <h3 class="fw-bold">
                    <i class="fas fa-user-graduate me-2"></i> ข้อมูลนักเรียน
                </h3>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <button class="btn btn-primary" onclick="openAddModal()">
                    <i class="fas fa-plus me-1"></i> เพิ่มนักเรียน
                </button>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-9">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                            <input type="text" id="searchInput" class="form-control" placeholder="ค้นหาชื่อ, นามสกุล หรือรหัสนักเรียน..." onkeyup="filterTable()">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select id="statusFilter" class="form-select" onchange="filterTable()">
                            <option value="">ทุกสถานะ</option>
                            <option value="Active">กำลังเรียน</option>
                            <option value="Graduated">จบการศึกษา</option>
                            <option value="Resigned">ลาออก</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">รหัสนักเรียน</th>
                            <th>นักเรียน</th>
                            <th>เพศ</th>
                            <th>ชั้นเรียน</th>
                            <th>สถานะ</th>
                            <th>การติดต่อ</th>
                            <th class="text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody id="studentTableBody">
                        <!-- Content loaded by JS -->
                    </tbody>
                </table>
            </div>
            <div id="loadingSpinner" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">กำลังโหลดข้อมูล...</p>
            </div>
        </div>

    </div>
</div>

<!-- Student Modal -->
<div class="modal fade" id="studentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="studentForm" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title" id="studentModalLabel">ข้อมูลนักเรียน</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="studentId" name="id">
            <input type="hidden" id="actionType" name="action" value="create">

            <div class="row">
                <!-- Profile Image -->
                <div class="col-md-4 text-center border-end">
                    <div class="mb-3">
                        <img id="imagePreview" src="https://ui-avatars.com/api/?name=New+Student&size=150&background=random" class="rounded border mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                        <input class="form-control form-control-sm" type="file" id="profileImage" name="profile_image" accept="image/*" onchange="previewFile()">
                    </div>
                </div>

                <!-- Fields -->
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label fw-bold">รหัสประจำตัวนักเรียน</label>
                        <input type="text" class="form-control" id="studentCode" name="student_code" required>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ชื่อ</label>
                            <input type="text" class="form-control" id="firstName" name="first_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">นามสกุล</label>
                            <input type="text" class="form-control" id="lastName" name="last_name" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">เพศ</label>
                            <select class="form-select" id="gender" name="gender">
                                <option value="Male">ชาย</option>
                                <option value="Female">หญิง</option>
                                <option value="Other">อื่นๆ</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">วันเกิด</label>
                            <input type="date" class="form-control" id="dob" name="dob">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-7">
                            <label class="form-label fw-bold">ระดับชั้น/ห้องเรียน</label>
                            <input type="text" class="form-control" id="className" name="class_name" list="classList" placeholder="กรอกชั้นเรียน เช่น ม.1/1">
                            <datalist id="classList">
                                <?php foreach($classes as $c): ?>
                                    <option value="<?= htmlspecialchars($c['class_name']) ?>"></option>
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-bold">สถานะ</label>
                            <select class="form-select" id="status" name="status">
                                <option value="Active">กำลังเรียน</option>
                                <option value="Graduated">จบการศึกษา</option>
                                <option value="Resigned">ลาออก</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">เบอร์โทรศัพท์</label>
                            <input type="text" class="form-control" id="phone" name="phone">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">เบอร์ผู้ปกครอง</label>
                            <input type="text" class="form-control" id="parentPhone" name="parent_phone">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">ที่อยู่</label>
                        <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
          <button type="submit" class="btn btn-primary">บันทึกข้อมูล</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
$(document).ready(function() {
    loadStudents();

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
                    Swal.fire('สำเร็จ', response.message, 'success');
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }
        });
    });
});

function loadStudents() {
    $('#loadingSpinner').show();
    $.ajax({
        url: 'backend/students_action.php',
        type: 'GET',
        data: { action: 'read' },
        dataType: 'json',
        success: function(response) {
            $('#loadingSpinner').hide();
            let html = '';
            if (response.data.length > 0) {
                $.each(response.data, function(index, s) {
                    let defaultAvatar = `https://ui-avatars.com/api/?name=${encodeURIComponent(s.first_name)}&background=random&color=fff`;
                    let imgSrc = s.profile_image ? 'uploads/profiles/' + s.profile_image : defaultAvatar;
                    
                    let genderTxt = s.gender === 'Male' ? 'ชาย' : (s.gender === 'Female' ? 'หญิง' : '-');
                    
                    let statusClass = 'bg-success';
                    if(s.status === 'Graduated') statusClass = 'bg-info';
                    if(s.status === 'Resigned') statusClass = 'bg-danger';

                    html += `<tr>
                                <td class="ps-3">${s.student_code}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="${imgSrc}" class="rounded me-2" style="width: 35px; height: 35px; object-fit: cover;">
                                        <div>
                                            <div class="fw-bold">${s.first_name} ${s.last_name}</div>
                                            <small class="text-muted">${formatThaiDate(s.dob)}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>${genderTxt}</td>
                                <td>${s.class_level || '-'}</td>
                                <td><span class="badge ${statusClass}">${translateStatus(s.status)}</span></td>
                                <td>
                                    <small>โทร: ${s.phone || '-'}</small><br>
                                    <small>ผปค: ${s.parent_phone || '-'}</small>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary" onclick='openEditModal(${JSON.stringify(s).replace(/'/g, "&apos;")})'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteStudent(${s.id})">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                             </tr>`;
                });
            } else {
                html = '<tr><td colspan="7" class="text-center py-4 text-muted">ไม่พบข้อมูลนักเรียน</td></tr>';
            }
            $('#studentTableBody').html(html);
        }
    });
}

function openAddModal() {
    $('#studentForm')[0].reset();
    $('#studentId').val('');
    $('#actionType').val('create');
    $('#imagePreview').attr('src', 'https://ui-avatars.com/api/?name=New+Student&size=150&background=random');
    $('#studentModalLabel').text('เพิ่มข้อมูลนักเรียน');
    $('#studentModal').modal('show');
}

function openEditModal(s) {
    $('#studentForm')[0].reset();
    $('#studentId').val(s.id);
    $('#actionType').val('update');
    $('#studentCode').val(s.student_code);
    $('#firstName').val(s.first_name);
    $('#lastName').val(s.last_name);
    $('#gender').val(s.gender || 'Male');
    $('#dob').val(s.dob);
    $('#className').val(s.class_level);
    $('#status').val(s.status || 'Active');
    $('#phone').val(s.phone);
    $('#parentPhone').val(s.parent_phone);
    $('#address').val(s.address);
    
    let defaultAvatar = `https://ui-avatars.com/api/?name=${encodeURIComponent(s.first_name)}&background=random&color=fff`;
    $('#imagePreview').attr('src', s.profile_image ? 'uploads/profiles/' + s.profile_image : defaultAvatar);
    
    $('#studentModalLabel').text('แก้ไขข้อมูลนักเรียน');
    $('#studentModal').modal('show');
}

function previewFile() {
    const file = document.querySelector('#profileImage').files[0];
    const reader = new FileReader();
    reader.addEventListener("load", function () {
        document.querySelector('#imagePreview').src = reader.result;
    }, false);
    if (file) { reader.readAsDataURL(file); }
}

function formatThaiDate(dateStr) {
    if(!dateStr) return '-';
    let d = new Date(dateStr);
    return d.toLocaleDateString('th-TH', { day: 'numeric', month: 'short', year: 'numeric' });
}

function translateStatus(status) {
    const map = { 'Active': 'กำลังเรียน', 'Graduated': 'จบการศึกษา', 'Resigned': 'ลาออก' };
    return map[status] || status;
}

function filterTable() {
    let input = $('#searchInput').val().toLowerCase();
    let status = $('#statusFilter').val();
    $("#studentTableBody tr").filter(function() {
        let text = $(this).text().toLowerCase();
        let sMatch = status === "" || $(this).find('td:eq(4)').text().indexOf(translateStatus(status)) > -1;
        $(this).toggle(text.indexOf(input) > -1 && sMatch);
    });
}

function deleteStudent(id) {
    Swal.fire({
        title: 'ยืนยันการลบ?',
        text: "ยืนยันการลบข้อมูลนักเรียนรายนี้?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'ลบ',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'backend/students_action.php',
                type: 'POST',
                data: { action: 'delete', id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                         loadStudents();
                         Swal.fire('เรียบร้อย', 'ลบข้อมูลแล้ว', 'success');
                    }
                }
            });
        }
    });
}
</script>


