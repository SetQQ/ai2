<?php 
require_once 'includes/auth_check.php'; 
require_once 'config/database.php';

// Fetch unique class levels from students for the dropdown
try {
    if ($_SESSION['role'] === 'admin') {
        $stmtClasses = $pdo->query("SELECT DISTINCT c.id, c.class_name FROM classes c JOIN students s ON c.id = s.class_id ORDER BY c.class_name ASC");
        $classesList = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);

        $stmtSubjects = $pdo->query("SELECT id, subject_name, subject_code FROM subjects ORDER BY subject_name ASC");
        $subjectsList = $stmtSubjects->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($_SESSION['role'] === 'teacher') {
        $teacher_id = $_SESSION['teacher_id'] ?? 0;
        
        $stmtClasses = $pdo->prepare("SELECT DISTINCT c.id, c.class_name 
                                     FROM schedules s
                                     JOIN classes c ON s.class_id = c.id
                                     WHERE s.teacher_id = ?");
        $stmtClasses->execute([$teacher_id]);
        $classesList = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);

        $stmtSubjects = $pdo->prepare("SELECT DISTINCT sub.id, sub.subject_name, sub.subject_code 
                                      FROM schedules s
                                      JOIN subjects sub ON s.subject_id = sub.id
                                      WHERE s.teacher_id = ?");
        $stmtSubjects->execute([$teacher_id]);
        $subjectsList = $stmtSubjects->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Student - maybe no lists needed if we auto-fetch
        $classesList = [];
        $subjectsList = [];
    }
} catch (PDOException $e) {
    $classesList = [];
    $subjectsList = [];
}

$currentYear = date('Y') + 543; // Thai year estimation

include 'includes/header.php'; 
include 'includes/sidebar.php'; 
?>

<!-- Main Content -->
<div id="content">
    
    <nav class="navbar navbar-expand-lg navbar-light sticky-top shadow-sm">
        <div class="container-fluid">
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
                    <i class="fas fa-star me-2"></i> <?= ($_SESSION['role'] === 'student') ? 'ผลการเรียนของฉัน' : 'บันทึกผลการเรียน' ?>
                </h3>
                <?php if ($_SESSION['role'] !== 'student'): ?>
                    <p class="text-muted mb-0">เลือกชั้นเรียนและวิชาที่ต้องการ เพื่อจัดการคะแนนและผลการเรียน</p>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($_SESSION['role'] !== 'student'): ?>
        <div class="row mb-4 justify-content-center">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                    <div class="card-body p-4 bg-white">
                        <form id="filterForm" class="row g-3 align-items-center">
                            <div class="col-md-3">
                                <label for="class_id" class="form-label fw-bold text-dark">ระดับชั้น/ห้อง</label>
                                <select class="form-select form-select-lg border-primary shadow-sm" id="class_id" name="class_id" required>
                                    <option value="">-- เลือกระดับชั้น --</option>
                                    <?php foreach($classesList as $c): ?>
                                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['class_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="subject_id" class="form-label fw-bold text-dark">รายวิชา</label>
                                <select class="form-select form-select-lg border-primary shadow-sm" id="subject_id" name="subject_id" required>
                                    <option value="">-- เลือกวิชา --</option>
                                    <?php foreach($subjectsList as $s): ?>
                                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['subject_code'] . ' - ' . $s['subject_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="semester" class="form-label fw-bold text-dark">ภาคเรียน</label>
                                <select class="form-select form-select-lg border-primary shadow-sm" id="semester" name="semester" required>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3 (ฤดูร้อน)</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="academic_year" class="form-label fw-bold text-dark">ปีการศึกษา</label>
                                <input type="number" class="form-control form-control-lg border-primary shadow-sm" id="academic_year" name="academic_year" value="<?= $currentYear ?>" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label d-none d-md-block">&nbsp;</label>
                                <button type="submit" class="btn btn-primary-custom btn-lg w-100 fw-bold shadow-sm">
                                    <i class="fas fa-search me-2"></i> ดึงข้อมูล
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div id="gradesContainer" style="display:none;">
            <!-- Save Button Top -->
            <?php if ($_SESSION['role'] !== 'student'): ?>
            <div class="d-flex justify-content-end mb-3">
                <button class="btn btn-success fw-bold shadow-sm px-4" onclick="saveGrades()" id="saveGradesBtn">
                    <i class="fas fa-save me-2"></i> บันทึกข้อมูลผลการเรียนทั้งหมด
                </button>
            </div>
            <?php endif; ?>

            <!-- Data Table Card -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="gradesTable">
                            <thead class="table-primary-custom text-white">
                                <tr>
                                    <th class="ps-4">รหัสนักเรียน</th>
                                    <th>ชื่อ-นามสกุล</th>
                                    <th class="text-center">เวลาเรียน (%)</th>
                                    <th class="text-center" style="width: 150px;">คะแนน</th>
                                    <th class="text-center" style="width: 150px;">เกรด</th>
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
        
        <?php if ($_SESSION['role'] !== 'student'): ?>
        <div id="noDataAlert" class="alert alert-info border-0 shadow-sm text-center py-5">
            <i class="fas fa-info-circle fa-3x mb-3 text-info"></i>
            <h5>กรุณาเลือก ระดับชั้น, วิชา, เทอม และ ปีการศึกษา เพื่อแสดงรายชื่อนักเรียน</h5>
        </div>
        <?php endif; ?>

    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
let currentStudents = [];
const userRole = '<?= $_SESSION['role'] ?>';

$(document).ready(function() {
    // If Student, auto-fetch
    if (userRole === 'student') {
        fetchStudentGrades();
    }
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        
        let formData = $(this).serialize();
        formData += '&action=get_students';

        let btn = $(this).find('button[type="submit"]');
        let originalText = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin"></i> กำลังโหลด...');
        btn.prop('disabled', true);

        $.ajax({
            url: 'backend/grades_action.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                btn.html(originalText);
                btn.prop('disabled', false);
                
                if (response.status === 'success') {
                    currentStudents = response.data;
                    renderTable();
                    $('#noDataAlert').hide();
                    $('#gradesContainer').fadeIn();
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

function calculateGrade(score) {
    if (score === '' || score === null) return '';
    let val = parseFloat(score);
    if (isNaN(val)) return '';
    if (val < 0 || val > 100) return 'Err';
    
    if (val >= 80) return '4';
    if (val >= 75) return '3.5';
    if (val >= 70) return '3';
    if (val >= 65) return '2.5';
    if (val >= 60) return '2';
    if (val >= 55) return '1.5';
    if (val >= 50) return '1';
    return '0'; // For F
}

function handleScoreChange(inputElement, studentId) {
    let score = $(inputElement).val();
    let gradeInput = $('#grade_' + studentId);
    let calculated = calculateGrade(score);
    gradeInput.val(calculated);
    
    // update currentStudents array
    let student = currentStudents.find(s => s.student_id == studentId);
    if(student) {
        student.score = score;
        student.grade = calculated;
    }
}

function handleGradeChange(inputElement, studentId) {
    let grade = $(inputElement).val();
    let student = currentStudents.find(s => s.student_id == studentId);
    if(student) {
        student.grade = grade;
    }
}

function renderTable() {
    let tbody = $('#gradesTable tbody');
    tbody.empty();
    
    if(currentStudents.length === 0) {
        tbody.append('<tr><td colspan="4" class="text-center text-muted py-4">ไม่พบรายชื่อนักเรียนในระดับชั้นนี้</td></tr>');
        $('#saveGradesBtn').prop('disabled', true);
        return;
    }
    
    $('#saveGradesBtn').prop('disabled', false);
    
    $.each(currentStudents, function(index, student) {
        let scoreVal = student.score !== null ? student.score : '';
        let gradeVal = student.grade !== null ? student.grade : '';
        
        let attendancePct = 0;
        if (student.total_days > 0) {
            attendancePct = (student.present_days / student.total_days) * 100;
        }
        let attendanceColor = 'text-success';
        if (attendancePct < 60) attendanceColor = 'text-danger';
        else if (attendancePct < 80) attendanceColor = 'text-warning';

        let tr = `
            <tr>
                <td class="ps-4 fw-bold text-muted">${student.student_code}</td>
                <td class="fw-semibold">${student.first_name} ${student.last_name}</td>
                <td class="text-center">
                    <span class="fw-bold ${attendanceColor}">${attendancePct.toFixed(1)}%</span>
                    <small class="d-block text-muted">(${student.present_days}/${student.total_days} วัน)</small>
                </td>
                <td>
                    ${(userRole === 'student') ? 
                        `<div class="fw-bold text-center">${scoreVal || '-'}</div>` : 
                        `<input type="number" step="0.01" min="0" max="100" class="form-control score-input text-center" 
                           value="${scoreVal}" data-id="${student.student_id}" 
                           oninput="handleScoreChange(this, ${student.student_id})">`
                    }
                </td>
                <td>
                    ${(userRole === 'student') ? 
                        `<div class="text-center"><span class="badge bg-primary fs-6">${gradeVal || '-'}</span></div>` : 
                        `<input type="text" class="form-control grade-input fw-bold text-center" 
                           id="grade_${student.student_id}" value="${gradeVal}" data-id="${student.student_id}"
                           oninput="handleGradeChange(this, ${student.student_id})">`
                    }
                </td>
            </tr>
        `;
        tbody.append(tr);
    });
}

function saveGrades() {
    // Collect specific identifiers from the filter form
    let payload = [];
    let subject_id = $('#subject_id').val();
    let semester = $('#semester').val();
    let academic_year = $('#academic_year').val();
    
    currentStudents.forEach(function(student) {
        // Only save if they have entered a score or grade
        if(student.score !== null && student.score !== '' || student.grade !== null && student.grade !== '') {
            payload.push({
                student_id: student.student_id,
                subject_id: subject_id,
                semester: semester,
                academic_year: academic_year,
                score: student.score || '',
                grade: student.grade || ''
            });
        }
    });
    
    if(payload.length === 0) {
        Swal.fire('ข้อมูลไม่ครบ', 'ยังไม่มีการกรอกคะแนน หรือเกรด สำหรับนักเรียนคนใดเลย', 'warning');
        return;
    }
    
    let btn = $('#saveGradesBtn');
    let originalText = btn.html();
    btn.html('<i class="fas fa-spinner fa-spin"></i> กำลังบันทึก...');
    btn.prop('disabled', true);

    $.ajax({
        url: 'backend/grades_action.php',
        type: 'POST',
        data: {
            action: 'save_grades',
            grades_data: JSON.stringify(payload)
        },
        dataType: 'json',
        success: function(response) {
            btn.html(originalText);
            btn.prop('disabled', false);
            if (response.status === 'success') {
                Swal.fire('สำเร็จ!', 'บันทึกผลการเรียนเรียบร้อยแล้ว', 'success');
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
function fetchStudentGrades() {
    $.ajax({
        url: 'backend/grades_action.php',
        type: 'POST',
        data: { action: 'get_my_grades' },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                renderStudentGradesTable(response.data);
                $('#gradesContainer').fadeIn();
            }
        }
    });
}

function renderStudentGradesTable(data) {
    let tbody = $('#gradesTable tbody');
    tbody.empty();
    
    // Change headers for student
    $('#gradesTable thead').html(`
        <tr>
            <th class="ps-4">รหัสวิชา</th>
            <th>ชื่อวิชา</th>
            <th class="text-center">เวลาเรียน (%)</th>
            <th class="text-center">คะแนน</th>
            <th class="text-center">เกรด</th>
        </tr>
    `);

    if(data.length === 0) {
        tbody.append('<tr><td colspan="4" class="text-center text-muted py-4">ยังไม่มีข้อมูลผลการเรียน</td></tr>');
        return;
    }
    
    $.each(data, function(index, g) {
        let attendancePct = 0;
        if (g.total_days > 0) {
            attendancePct = (g.present_days / g.total_days) * 100;
        }
        let attendanceColor = 'text-success';
        if (attendancePct < 60) attendanceColor = 'text-danger';
        else if (attendancePct < 80) attendanceColor = 'text-warning';

        tbody.append(`
            <tr>
                <td class="ps-4 fw-bold text-muted">${g.subject_code}</td>
                <td class="fw-semibold">${g.subject_name}</td>
                <td class="text-center">
                    <span class="${attendanceColor} fw-bold">${attendancePct.toFixed(1)}%</span>
                </td>
                <td class="text-center font-monospace">${g.score || '-'}</td>
                <td class="text-center"><span class="badge bg-primary px-3">${g.grade || '-'}</span></td>
            </tr>
        `);
    });
}
</script>
