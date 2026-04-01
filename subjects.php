<?php 
require_once 'includes/auth_check.php'; 
checkRole(['admin']); 
require_once 'config/database.php';

try {
    $teachersList = $pdo->query("SELECT id, first_name, last_name FROM teachers ORDER BY first_name ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $teachersList = [];
}

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
                <i class="fas fa-book me-2"></i> ระบบจัดการข้อมูลรายวิชา
            </h3>
            <button class="btn btn-primary-custom" onclick="openAddModal()">
                <i class="fas fa-plus me-1"></i> เพิ่มรายวิชา
            </button>
        </div>

        <div class="card dashboard-card">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>รหัสวิชา</th>
                                <th>ชื่อรายวิชา</th>
                                <th>ครูผู้สอน</th>
                                <th>หน่วยกิต</th>
                                <th>ประเภท</th>
                                <th>คำอธิบาย</th>
                                <th class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody id="subjectTableBody">
                            <tr><td colspan="7" class="text-center text-muted">กำลังโหลดข้อมูล...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Subject Modal -->
<div class="modal fade" id="subjectModal" tabindex="-1" aria-labelledby="subjectModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="subjectForm">
        <div class="modal-header bg-primary-custom text-white">
          <h5 class="modal-title" id="subjectModalLabel">เพิ่ม/แก้ไขรายวิชา</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="subjectId" name="id">
            <input type="hidden" id="actionType" name="action" value="create">

            <div class="mb-3">
                <label for="subjectCode" class="form-label">รหัสวิชา (เช่น ว31101)</label>
                <input type="text" class="form-control" id="subjectCode" name="subject_code" required>
            </div>
            <div class="mb-3">
                <label for="subjectName" class="form-label">ชื่อรายวิชา</label>
                <input type="text" class="form-control" id="subjectName" name="subject_name" required>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="credit" class="form-label">หน่วยกิต</label>
                    <input type="number" step="0.5" min="0" class="form-control" id="credit" name="credit" value="1.0" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="type" class="form-label">ประเภทวิชา</label>
                    <select class="form-select" id="type" name="type">
                        <option value="core">วิชาพื้นฐาน</option>
                        <option value="elective">วิชาเพิ่มเติม</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">คำอธิบาย</label>
                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
            </div>
            <div class="mb-3">
                <label for="teacherId" class="form-label">ครูผู้สอนประจำวิชา</label>
                <select class="form-select" id="teacherId" name="teacher_id">
                    <option value="">-- ไม่ระบุ --</option>
                    <?php foreach($teachersList as $t): ?>
                        <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['first_name'].' '.$t['last_name']) ?></option>
                    <?php endforeach; ?>
                </select>
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
    loadSubjects();

    // Handle Form Submit
    $('#subjectForm').on('submit', function(e) {
        e.preventDefault();
        
        let formData = $(this).serialize();
        
        $.ajax({
            url: 'backend/subjects_action.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#subjectModal').modal('hide');
                    loadSubjects();
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

function loadSubjects() {
    $.ajax({
        url: 'backend/subjects_action.php',
        type: 'GET',
        data: { action: 'read' },
        dataType: 'json',
        success: function(response) {
            let html = '';
            if (response.data && response.data.length > 0) {
                $.each(response.data, function(index, subject) {
                    let typeBadge = subject.type === 'core' 
                        ? '<span class="badge bg-primary">พื้นฐาน</span>' 
                        : '<span class="badge bg-success">เพิ่มเติม</span>';
                    
                    let teacherName = (subject.first_name && subject.last_name) 
                        ? `<i class="fas fa-user-tie text-muted me-1"></i> ${subject.first_name} ${subject.last_name}` 
                        : '<span class="text-muted">-</span>';
                        
                    html += `<tr>
                                <td class="fw-semibold text-primary-custom">${subject.subject_code}</td>
                                <td>${subject.subject_name}</td>
                                <td>${teacherName}</td>
                                <td>${parseFloat(subject.credit).toFixed(1)}</td>
                                <td>${typeBadge}</td>
                                <td><small class="text-muted">${subject.description || '-'}</small></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-warning me-1" onclick='openEditModal(${JSON.stringify(subject).replace(/'/g, "&apos;")})'>
                                        <i class="fas fa-edit"></i> แก้ไข
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteSubject(${subject.id})">
                                        <i class="fas fa-trash-alt"></i> ลบ
                                    </button>
                                </td>
                             </tr>`;
                });
            } else {
                html = '<tr><td colspan="7" class="text-center text-muted">ไม่มีข้อมูลรายวิชาในระบบ</td></tr>';
            }
            $('#subjectTableBody').html(html);
        },
        error: function() {
            $('#subjectTableBody').html('<tr><td colspan="7" class="text-center text-danger">เกิดข้อผิดพลาดในการโหลดข้อมูล</td></tr>');
        }
    });
}

function openAddModal() {
    $('#subjectForm')[0].reset();
    $('#subjectId').val('');
    $('#teacherId').val('');

    $('#actionType').val('create');
    $('#subjectModalLabel').text('เพิ่มข้อมูลรายวิชา');
    $('#subjectModal').modal('show');
}

function openEditModal(subject) {
    $('#subjectForm')[0].reset();
    $('#subjectId').val(subject.id);
    $('#subjectCode').val(subject.subject_code);
    $('#subjectName').val(subject.subject_name);
    $('#credit').val(subject.credit);
    $('#type').val(subject.type);
    $('#description').val(subject.description);
    $('#teacherId').val(subject.teacher_id || '');

    $('#actionType').val('update');
    $('#subjectModalLabel').text('แก้ไขข้อมูลรายวิชา');
    $('#subjectModal').modal('show');
}

function deleteSubject(id) {
    Swal.fire({
        title: 'ยืนยันการลบ?',
        text: 'คุณแน่ใจหรือไม่ว่าต้องการลบข้อมูลรายวิชานี้?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'ตกลง',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'backend/subjects_action.php',
                type: 'POST',
                data: { action: 'delete', id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire('ลบแล้ว!', 'ลบข้อมูลรายวิชาเรียบร้อยแล้ว', 'success');
                        loadSubjects();
                    } else {
                        Swal.fire('ข้อผิดพลาด', response.message, 'error');
                    }
                }
            });
        }
    });
}
</script>
