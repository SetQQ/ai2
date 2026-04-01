<?php 
require_once 'includes/auth_check.php'; 
require_once 'config/database.php';

// Fetch classes from classes table for the dropdown
try {
    if ($_SESSION['role'] === 'admin') {
        $stmtClasses = $pdo->query("SELECT DISTINCT c.id, c.class_name FROM classes c JOIN students s ON c.id = s.class_id ORDER BY c.class_name ASC");
        $classesList = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Teachers only see classes they teach
        $teacher_id = $_SESSION['teacher_id'] ?? 0;
        $stmtClasses = $pdo->prepare("SELECT DISTINCT c.id, c.class_name 
                                     FROM schedules s
                                     JOIN classes c ON s.class_id = c.id
                                     WHERE s.teacher_id = ?");
        $stmtClasses->execute([$teacher_id]);
        $classesList = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $classesList = [];
}

$today = date('Y-m-d');

include 'includes/header.php'; 
include 'includes/sidebar.php'; 
?>

<!-- Main Content -->
<div id="content">
    
    <nav class="navbar navbar-expand-lg navbar-light sticky-top shadow-sm">
        <div class="container-fluid">
            <!-- Sidebar Toggle Button -->
            <button type="button" id="sidebarToggle" class="btn btn-primary-custom">
                <i class="fas fa-bars"></i>
            </button>
            <form class="d-none d-md-flex ms-4" style="width: 300px;">
                <div class="input-group">
                    <input type="text" class="form-control border-end-0 bg-light" placeholder="ค้นหา..." aria-label="Search">
                    <span class="input-group-text bg-light border-start-0 text-muted">
                        <i class="fas fa-search"></i>
                    </span>
                </div>
            </form>
            <ul class="navbar-nav ms-auto align-items-center flex-row">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userMenu" data-bs-toggle="dropdown">
                        <div class="bg-primary-custom text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <span class="d-none d-sm-inline fw-semibold text-dark"><?= htmlspecialchars($_SESSION['first_name'] ?? 'User') ?></span>
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
        <div class="row mb-4">
            <div class="col-12 text-center text-md-start">
                <h3 class="fw-bold text-primary-custom mb-2">
                    <i class="fas fa-clipboard-user me-2"></i> ระบบบันทึกเวลาเรียน
                </h3>
                <p class="text-muted mb-0">กรุณาเลือกชั้นเรียนและวันที่เพื่อเริ่มบันทึกการเข้าเรียน</p>
            </div>
        </div>

        <div class="row mb-4 justify-content-center">
            <div class="col-md-11 col-lg-10">
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                    <div class="card-body p-4 bg-white">
                        <form id="filterForm" class="row g-3 align-items-center">
                            <div class="col-md-4">
                                <label for="class_id" class="form-label fw-bold text-dark">ระดับชั้น/ห้อง</label>
                                <select class="form-select form-select-lg border-primary shadow-sm" id="class_id" name="class_id" required>
                                    <option value="">-- เลือกระดับชั้น --</option>
                                    <?php foreach($classesList as $c): ?>
                                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['class_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="attendance_date" class="form-label fw-bold text-dark">วันที่เช็คชื่อ</label>
                                <input type="date" class="form-control form-control-lg border-primary shadow-sm" id="attendance_date" name="attendance_date" value="<?= $today ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label d-none d-md-block">&nbsp;</label>
                                <button type="submit" class="btn btn-primary-custom btn-lg w-100 fw-bold shadow-sm">
                                    <i class="fas fa-search me-2"></i> ดึงรายชื่อนักเรียน
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div id="attendanceContainer" style="display:none;">
            <!-- Actions Top -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <button type="button" class="btn btn-outline-success btn-sm fw-bold shadow-sm" onclick="markAll('present')">
                    <i class="fas fa-check-double me-1"></i> เช็คมาเรียนทั้งหมด
                </button>
                <button class="btn btn-warning fw-bold shadow-sm px-4 text-dark" onclick="saveAttendance()" id="saveAttendanceBtn">
                    <i class="fas fa-save me-2"></i> บันทึกข้อมูลการเช็คชื่อ
                </button>
            </div>

            <!-- Data Table Card -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="attendanceTable">
                            <thead class="table-primary-custom text-white">
                                <tr>
                                    <th class="ps-4" style="width: 120px;">รหัสนักเรียน</th>
                                    <th>ชื่อ - นามสกุล</th>
                                    <th class="text-center" style="width: 400px;">สถานะการเข้าเรียน</th>
                                    <th>หมายเหตุ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data populated by AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div id="noDataAlert" class="alert alert-info border-0 shadow-sm text-center py-5">
            <i class="fas fa-info-circle fa-3x mb-3 text-info"></i>
            <h5>กรุณาเลือกระดับชั้นและวันที่ เพื่อแสดงรายชื่อเช็คเวลาเรียน</h5>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
let currentStudents = [];

$(document).ready(function() {
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        
        let class_id = $('#class_id').val();
        let attendance_date = $('#attendance_date').val();

        let btn = $(this).find('button[type="submit"]');
        let originalText = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin"></i> กำลังโหลด...');
        btn.prop('disabled', true);

        $.ajax({
            url: 'backend/attendance_action.php',
            type: 'POST',
            data: { action: 'get_students', class_id: class_id, attendance_date: attendance_date },
            dataType: 'json',
            success: function(response) {
                btn.html(originalText);
                btn.prop('disabled', false);
                
                if (response.status === 'success') {
                    currentStudents = response.data;
                    renderTable();
                    $('#noDataAlert').hide();
                    $('#attendanceContainer').fadeIn();
                } else {
                    Swal.fire('ข้อผิดพลาด', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('ข้อผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
                btn.html(originalText);
                btn.prop('disabled', false);
            }
        });
    });
});

function markAll(statusVal) {
    if(confirm('คุณต้องการเช็คสถานะให้นักเรียนทุกคนเป็น "มาเรียน" ใช่หรือไม่?')) {
        $('.status-radio[value="'+statusVal+'"]').prop('checked', true);
        
        // Update model array
        currentStudents.forEach(stu => stu.status = statusVal);
    }
}

function handleStatusChange(studentId, statusValue) {
    let student = currentStudents.find(s => s.student_id == studentId);
    if(student) {
        student.status = statusValue;
    }
}

function handleRemarksChange(inputElement, studentId) {
    let student = currentStudents.find(s => s.student_id == studentId);
    if(student) {
        student.remarks = $(inputElement).val();
    }
}

function renderTable() {
    let tbody = $('#attendanceTable tbody');
    tbody.empty();
    
    if(currentStudents.length === 0) {
        tbody.append('<tr><td colspan="4" class="text-center text-muted py-4">ไม่พบรายชื่อนักเรียนในระดับชั้นนี้</td></tr>');
        $('#saveAttendanceBtn').prop('disabled', true);
        return;
    }
    
    $('#saveAttendanceBtn').prop('disabled', false);
    
    $.each(currentStudents, function(index, student) {
        let sid = student.student_id;
        let sPresent = (student.status === 'present' || !student.status) ? 'checked' : '';
        let sAbsent  = (student.status === 'absent')  ? 'checked' : '';
        let sLate    = (student.status === 'late')    ? 'checked' : '';
        let sLeave   = (student.status === 'leave')   ? 'checked' : '';
        
        // Initially apply default status locally if not set from server
        if(!student.status) student.status = 'present'; 

        let remarks = student.remarks || '';

        let tr = `
            <tr>
                <td class="ps-4 fw-bold text-muted">${student.student_code}</td>
                <td class="fw-semibold">${student.first_name} ${student.last_name}</td>
                <td class="text-center">
                    <div class="btn-group shadow-sm" role="group" aria-label="Attendance Status">
                        <input type="radio" class="btn-check status-radio" name="status[${sid}]" id="present_${sid}" value="present" ${sPresent} onchange="handleStatusChange(${sid}, 'present')">
                        <label class="btn btn-outline-success px-3" for="present_${sid}">มา</label>

                        <input type="radio" class="btn-check status-radio" name="status[${sid}]" id="absent_${sid}" value="absent" ${sAbsent} onchange="handleStatusChange(${sid}, 'absent')">
                        <label class="btn btn-outline-danger px-3" for="absent_${sid}">ขาด</label>

                        <input type="radio" class="btn-check status-radio" name="status[${sid}]" id="late_${sid}" value="late" ${sLate} onchange="handleStatusChange(${sid}, 'late')">
                        <label class="btn btn-outline-warning text-dark px-3" for="late_${sid}">สาย</label>
                        
                        <input type="radio" class="btn-check status-radio" name="status[${sid}]" id="leave_${sid}" value="leave" ${sLeave} onchange="handleStatusChange(${sid}, 'leave')">
                        <label class="btn btn-outline-info text-dark px-3" for="leave_${sid}">ลา</label>
                    </div>
                </td>
                <td>
                    <input type="text" class="form-control bg-light border-0" value="${remarks}" placeholder="ระบุเหตุผล (ถ้ามี)" oninput="handleRemarksChange(this, ${sid})">
                </td>
            </tr>
        `;
        tbody.append(tr);
    });
}

function saveAttendance() {
    let payload = [];
    currentStudents.forEach(function(student) {
        payload.push({
            student_id: student.student_id,
            status: student.status,
            remarks: student.remarks || ''
        });
    });
    
    let attendance_date = $('#attendance_date').val();
    let class_id = $('#class_id').val();
    
    let btn = $('#saveAttendanceBtn');
    let originalText = btn.html();
    btn.html('<i class="fas fa-spinner fa-spin"></i> กำลังบันทึก...');
    btn.prop('disabled', true);

    $.ajax({
        url: 'backend/attendance_action.php',
        type: 'POST',
        data: {
            action: 'save_attendance',
            attendance_date: attendance_date,
            class_id: class_id,
            attendance_data: JSON.stringify(payload)
        },
        dataType: 'json',
        success: function(response) {
            btn.html(originalText);
            btn.prop('disabled', false);
            if (response.status === 'success') {
                Swal.fire('สำเร็จ!', 'บันทึกข้อมูลการเช็คชื่อเข้าเรียนเรียบร้อยแล้ว', 'success');
            } else {
                Swal.fire('ข้อผิดพลาด', response.message, 'error');
            }
        },
        error: function() {
            Swal.fire('ข้อผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
            btn.html(originalText);
            btn.prop('disabled', false);
        }
    });
}
</script>
