<?php 
require_once 'includes/auth_check.php'; 
require_once 'config/database.php';

// Fetch unique class levels from students for the dropdown
try {
    $stmtClasses = $pdo->query("SELECT DISTINCT class_level FROM students WHERE class_level IS NOT NULL AND class_level != '' ORDER BY class_level ASC");
    $classesList = $stmtClasses->fetchAll(PDO::FETCH_COLUMN);

    $stmtSubjects = $pdo->query("SELECT id, subject_name, subject_code FROM subjects ORDER BY subject_name ASC");
    $subjectsList = $stmtSubjects->fetchAll(PDO::FETCH_ASSOC);
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-primary-custom mb-0">
                <i class="fas fa-star me-2"></i> บันทึกผลการเรียน
            </h3>
        </div>

        <!-- Filter Card -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body p-4 bg-light">
                <form id="filterForm" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="class_level" class="form-label fw-bold">ระดับชั้น/ห้อง</label>
                        <select class="form-select border-primary" id="class_level" name="class_level" required>
                            <option value="">-- เลือกระดับชั้น --</option>
                            <?php foreach($classesList as $c): ?>
                                <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="subject_id" class="form-label fw-bold">รายวิชา</label>
                        <select class="form-select border-primary" id="subject_id" name="subject_id" required>
                            <option value="">-- เลือกวิชา --</option>
                            <?php foreach($subjectsList as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['subject_code'] . ' - ' . $s['subject_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="semester" class="form-label fw-bold">ภาคเรียน</label>
                        <select class="form-select border-primary" id="semester" name="semester" required>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3 (ฤดูร้อน)</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="academic_year" class="form-label fw-bold">ปีการศึกษา</label>
                        <input type="number" class="form-control border-primary" id="academic_year" name="academic_year" value="<?= $currentYear ?>" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary-custom w-100 fw-bold shadow-sm">
                            <i class="fas fa-search me-1"></i> ดึงข้อมูลนักเรียน
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div id="gradesContainer" style="display:none;">
            <!-- Save Button Top -->
            <div class="d-flex justify-content-end mb-3">
                <button class="btn btn-success fw-bold shadow-sm px-4" onclick="saveGrades()" id="saveGradesBtn">
                    <i class="fas fa-save me-2"></i> บันทึกข้อมูลผลการเรียนทั้งหมด
                </button>
            </div>

            <!-- Data Table Card -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="gradesTable">
                            <thead class="table-primary-custom text-white">
                                <tr>
                                    <th class="ps-4">รหัสนักเรียน</th>
                                    <th>ชื่อ - นามสกุล</th>
                                    <th style="width: 150px;">คะแนน (0-100)</th>
                                    <th style="width: 150px;">เกรด</th>
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
            <h5>กรุณาเลือก ระดับชั้น, วิชา, เทอม และ ปีการศึกษา เพื่อแสดงรายชื่อนักเรียน</h5>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
let currentStudents = [];

$(document).ready(function() {
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
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
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
        
        let tr = `
            <tr>
                <td class="ps-4 fw-bold text-muted">${student.student_code}</td>
                <td class="fw-semibold">${student.first_name} ${student.last_name}</td>
                <td>
                    <input type="number" step="0.01" min="0" max="100" class="form-control score-input" 
                           value="${scoreVal}" data-id="${student.student_id}" 
                           oninput="handleScoreChange(this, ${student.student_id})">
                </td>
                <td>
                    <input type="text" class="form-control grade-input fw-bold text-center" 
                           id="grade_${student.student_id}" value="${gradeVal}" data-id="${student.student_id}"
                           oninput="handleGradeChange(this, ${student.student_id})">
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
        alert('ยังไม่มีการกรอกคะแนน หรือเกรด สำหรับนักเรียนคนใดเลย');
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
                alert('บันทึกผลการเรียนเรียบร้อยแล้ว');
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
            btn.html(originalText);
            btn.prop('disabled', false);
        }
    });
}
</script>
