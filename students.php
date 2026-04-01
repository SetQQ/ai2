<?php 
require_once 'includes/auth_check.php'; 
checkRole(['admin']); 
require_once 'setup_students_table.php';

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
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
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
            <button class="btn btn-primary-custom" id="btnAddStudent">
                <i class="fas fa-plus me-1"></i> เพิ่มนักเรียน
            </button>
        </div>

        <!-- Search and Filter -->
        <div class="card dashboard-card mb-4">
            <div class="card-body p-4">
                <div class="row g-2">
                    <div class="col-md-9">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                            <input type="text" id="searchInput" class="form-control" placeholder="ค้นหาชื่อ, นามสกุล หรือรหัสนักเรียน...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select id="statusFilter" class="form-select">
                            <option value="">ทุกสถานะ</option>
                            <option value="Active">กำลังเรียน</option>
                            <option value="Graduated">จบการศึกษา</option>
                            <option value="Resigned">ลาออก</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card dashboard-card">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
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
            </div>
            <div id="loadingSpinner" class="text-center py-5">
                <div class="spinner-border text-primary-custom" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">กำลังโหลดข้อมูล...</p>
            </div>
        </div>

    </div>
</div>

<!-- Student Modal -->
<div class="modal fade" id="studentModal" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="studentForm" autocomplete="off">
        <div class="modal-header bg-primary-custom text-white">
          <h5 class="modal-title" id="studentModalLabel">ข้อมูลนักเรียน</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="studentId" name="id">
            <input type="hidden" id="actionType" name="action" value="create">

            <div class="mb-3">
                <label class="form-label fw-bold">รหัสประจำตัวนักเรียน <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="studentCode" name="student_code" required>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">ชื่อ <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="firstName" name="first_name" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">นามสกุล <span class="text-danger">*</span></label>
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
                    <input type="text" class="form-control" id="className" name="class_name" placeholder="กรอกชั้นเรียน เช่น ม.1/1">
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
// เก็บข้อมูลนักเรียนทั้งหมดไว้ใน object โดยใช้ id เป็น key
// เพื่อหลีกเลี่ยงการส่ง JSON ผ่าน onclick attribute (ซึ่งทำให้เกิดปัญหากับชื่อภาษาไทย)
let studentsMap = {};
let studentModalInstance = null;

$(document).ready(function () {

    // สร้าง Bootstrap Modal instance ครั้งเดียว
    studentModalInstance = new bootstrap.Modal(document.getElementById('studentModal'), {
        keyboard: true,
        backdrop: true
    });

    loadStudents();

    // ปุ่มเพิ่มนักเรียน
    $('#btnAddStudent').on('click', function () {
        openAddModal();
    });

    // Submit form
    $('#studentForm').on('submit', function (e) {
        e.preventDefault();
        let formData = $(this).serialize();
        let submitBtn = $(this).find('[type=submit]');
        submitBtn.prop('disabled', true).text('กำลังบันทึก...');

        $.ajax({
            url: 'backend/students_action.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (response) {
                submitBtn.prop('disabled', false).text('บันทึกข้อมูล');
                if (response.status === 'success') {
                    studentModalInstance.hide();
                    loadStudents();
                    Swal.fire('สำเร็จ', response.message, 'success');
                } else {
                    Swal.fire('เกิดข้อผิดพลาด', response.message, 'error');
                }
            },
            error: function () {
                submitBtn.prop('disabled', false).text('บันทึกข้อมูล');
                Swal.fire('เกิดข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้', 'error');
            }
        });
    });

    // Event delegation: ปุ่มแก้ไข (ใช้ data-id แทน onclick JSON)
    $(document).on('click', '.btn-edit-student', function () {
        let id = $(this).data('id');
        let s = studentsMap[id];
        if (s) openEditModal(s);
    });

    // Event delegation: ปุ่มลบ
    $(document).on('click', '.btn-delete-student', function () {
        let id = $(this).data('id');
        deleteStudent(id);
    });

    // Search & Filter
    $('#searchInput').on('keyup', filterTable);
    $('#statusFilter').on('change', filterTable);
});

function loadStudents() {
    $('#loadingSpinner').show();
    $('#studentTableBody').hide();
    studentsMap = {};

    $.ajax({
        url: 'backend/students_action.php',
        type: 'GET',
        data: { action: 'read' },
        dataType: 'json',
        success: function (response) {
            $('#loadingSpinner').hide();
            $('#studentTableBody').show();
            let html = '';
            if (response.data && response.data.length > 0) {
                $.each(response.data, function (index, s) {
                    studentsMap[s.id] = s; // เก็บข้อมูลทั้งหมดใน map ด้วย id
                    let genderTxt = s.gender === 'Male' ? 'ชาย' : (s.gender === 'Female' ? 'หญิง' : '-');
                    let statusClass = 'bg-success';
                    if (s.status === 'Graduated') statusClass = 'bg-info';
                    if (s.status === 'Resigned') statusClass = 'bg-danger';

                    html += `<tr>
                                <td class="ps-3">${s.student_code}</td>
                                <td>
                                    <div class="fw-bold">${s.first_name} ${s.last_name}</div>
                                    <small class="text-muted">${formatThaiDate(s.dob)}</small>
                                </td>
                                <td>${genderTxt}</td>
                                <td>${s.class_level || '-'}</td>
                                <td><span class="badge ${statusClass}">${translateStatus(s.status)}</span></td>
                                <td>
                                    <small>โทร: ${s.phone || '-'}</small><br>
                                    <small>ผปค: ${s.parent_phone || '-'}</small>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-warning me-1 btn-edit-student" data-id="${s.id}" type="button">
                                        <i class="fas fa-edit"></i> แก้ไข
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger btn-delete-student" data-id="${s.id}" type="button">
                                        <i class="fas fa-trash-alt"></i> ลบ
                                    </button>
                                </td>
                             </tr>`;
                });
            } else {
                html = '<tr><td colspan="7" class="text-center py-4 text-muted">ไม่พบข้อมูลนักเรียน</td></tr>';
            }
            $('#studentTableBody').html(html);
        },
        error: function () {
            $('#loadingSpinner').hide();
            $('#studentTableBody').show().html('<tr><td colspan="7" class="text-center py-4 text-danger">เกิดข้อผิดพลาดในการโหลดข้อมูล</td></tr>');
        }
    });
}

function openAddModal() {
    document.getElementById('studentForm').reset();
    $('#studentId').val('');
    $('#actionType').val('create');
    $('#studentModalLabel').text('เพิ่มข้อมูลนักเรียน');
    studentModalInstance.show();
}

function openEditModal(s) {
    document.getElementById('studentForm').reset();
    $('#studentId').val(s.id || '');
    $('#actionType').val('update');
    $('#studentCode').val(s.student_code || '');
    $('#firstName').val(s.first_name || '');
    $('#lastName').val(s.last_name || '');
    $('#gender').val(s.gender || 'Male');
    $('#dob').val(s.dob || '');
    $('#className').val(s.class_level || '');
    $('#status').val(s.status || 'Active');
    $('#phone').val(s.phone || '');
    $('#parentPhone').val(s.parent_phone || '');
    $('#address').val(s.address || '');

    $('#studentModalLabel').text('แก้ไขข้อมูลนักเรียน');
    studentModalInstance.show();
}

function formatThaiDate(dateStr) {
    if (!dateStr) return '-';
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
    $('#studentTableBody tr').filter(function () {
        let text = $(this).text().toLowerCase();
        let sMatch = status === '' || $(this).find('td:eq(4)').text().indexOf(translateStatus(status)) > -1;
        $(this).toggle(text.indexOf(input) > -1 && sMatch);
    });
}

function deleteStudent(id) {
    Swal.fire({
        title: 'ยืนยันการลบ?',
        text: 'ยืนยันการลบข้อมูลนักเรียนรายนี้?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'ตกลง',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'backend/students_action.php',
                type: 'POST',
                data: { action: 'delete', id: id },
                dataType: 'json',
                success: function (response) {
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
