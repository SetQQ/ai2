<?php 
require_once 'includes/auth_check.php'; 
require_once 'config/database.php';

// Fetch lookups for dropdowns
try {
    $classesList = $pdo->query("SELECT id, class_name FROM classes ORDER BY level ASC")->fetchAll(PDO::FETCH_ASSOC);
    $subjectsList = $pdo->query("SELECT id, subject_name, subject_code FROM subjects ORDER BY subject_name ASC")->fetchAll(PDO::FETCH_ASSOC);
    $teachersList = $pdo->query("SELECT id, first_name, last_name FROM teachers ORDER BY first_name ASC")->fetchAll(PDO::FETCH_ASSOC);
    $roomsList = $pdo->query("SELECT id, room_name FROM classrooms ORDER BY room_name ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $classesList = $subjectsList = $teachersList = $roomsList = [];
}

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

            <!-- Search Bar -->
            <form class="d-none d-md-flex ms-4" style="width: 300px;">
                <div class="input-group">
                    <input type="text" class="form-control border-end-0 bg-light" placeholder="ค้นหาตารางเรียน..." aria-label="Search">
                    <span class="input-group-text bg-light border-start-0 text-muted">
                        <i class="fas fa-search"></i>
                    </span>
                </div>
            </form>

            <ul class="navbar-nav ms-auto align-items-center flex-row">
                <!-- User Profile -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="bg-primary-custom text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <span class="d-none d-sm-inline fw-semibold text-dark"><?= htmlspecialchars($_SESSION['first_name'] ?? 'User') ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="navbarDropdownMenuLink">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user-circle me-2"></i> โปรไฟล์ส่วนตัว</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i> ตั้งค่าระบบ</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> ออกจากระบบ</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Page Content -->
    <div class="container-fluid p-4">
        <div class="row mb-4 align-items-center">
            <div class="col-md-6 mb-3 mb-md-0">
                <h3 class="fw-bold text-primary-custom mb-0">
                    <i class="fas fa-calendar-alt me-2"></i> จัดการตารางเรียน
                </h3>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm bg-light">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <label for="filterClass" class="form-label mb-0 fw-bold text-dark me-3" style="min-width: 100px;">เลือกระดับชั้น:</label>
                        <select id="filterClass" class="form-select border-primary shadow-sm" style="max-width: 300px;">
                            <option value="">-- กรุณาเลือกระดับชั้นเรียน --</option>
                            <?php foreach($classesList as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['class_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div id="scheduleContainer" style="display: none;">
            <!-- Header for table -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-list me-2"></i> รายการคาบเรียนทั้งหมด</h5>
                <button class="btn btn-primary-custom shadow-sm" onclick="openAddModal()">
                    <i class="fas fa-plus-circle me-1"></i> เพิ่มคาบเรียน
                </button>
            </div>

            <!-- Data Table Card -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="schedulesTable">
                            <thead class="table-light">
                                <tr>
                                    <th>วัน</th>
                                    <th>เวลาเริ่มต้น</th>
                                    <th>เวลาสิ้นสุด</th>
                                    <th>วิชาเรียน</th>
                                    <th>ครูผู้สอน</th>
                                    <th>ห้องเรียน</th>
                                    <th>จัดการ</th>
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
        
        <div id="noClassAlert" class="alert alert-warning border-0 shadow-sm text-center py-5">
            <i class="fas fa-info-circle fa-3x mb-3 text-warning"></i>
            <h5>กรุณาเลือกระดับชั้นเรียนจากเมนูด้านขวาบน เพื่อแสดงตารางเรียน</h5>
        </div>

    </div>
</div>

<!-- Add Schedule Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1" aria-labelledby="scheduleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content border-0 shadow">
      <form id="scheduleForm">
        <div class="modal-header bg-primary-custom text-white">
          <h5 class="modal-title fw-bold" id="scheduleModalLabel">เพิ่มคาบเรียนสำหรับชั้นนี้</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
            <input type="hidden" name="action" id="actionType" value="create">
            <input type="hidden" name="class_id" id="modalClassId" value="">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="dayOfWeek" class="form-label fw-bold">วันในสัปดาห์</label>
                    <select class="form-select" id="dayOfWeek" name="day_of_week" required>
                        <option value="">-- เลือกวัน --</option>
                        <option value="Monday">จันทร์ (Monday)</option>
                        <option value="Tuesday">อังคาร (Tuesday)</option>
                        <option value="Wednesday">พุธ (Wednesday)</option>
                        <option value="Thursday">พฤหัสบดี (Thursday)</option>
                        <option value="Friday">ศุกร์ (Friday)</option>
                        <option value="Saturday">เสาร์ (Saturday)</option>
                        <option value="Sunday">อาทิตย์ (Sunday)</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="startTime" class="form-label fw-bold">เวลาเริ่ม</label>
                    <input type="time" class="form-control" id="startTime" name="start_time" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="endTime" class="form-label fw-bold">เวลาสิ้นสุด</label>
                    <input type="time" class="form-control" id="endTime" name="end_time" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="subjectId" class="form-label fw-bold">วิชาเรียน</label>
                    <select class="form-select" id="subjectId" name="subject_id" required>
                        <option value="">-- เลือกวิชา --</option>
                        <?php foreach($subjectsList as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['subject_code'].' - '.$s['subject_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="teacherId" class="form-label fw-bold">ครูผู้สอน</label>
                    <select class="form-select" id="teacherId" name="teacher_id" required>
                        <option value="">-- เลือกครู --</option>
                        <?php foreach($teachersList as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['first_name'].' '.$t['last_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="classroomId" class="form-label fw-bold">ห้องเรียน</label>
                    <select class="form-select" id="classroomId" name="classroom_id" required>
                        <option value="">-- เลือกห้อง --</option>
                        <?php foreach($roomsList as $r): ?>
                            <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['room_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
          <button type="submit" class="btn btn-primary-custom">บันทึกคาบเรียน</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
$(document).ready(function() {
    
    // Listen for filter changes
    $('#filterClass').change(function() {
        let classId = $(this).val();
        if(classId) {
            $('#noClassAlert').hide();
            $('#scheduleContainer').fadeIn();
            loadSchedules(classId);
        } else {
            $('#scheduleContainer').hide();
            $('#noClassAlert').fadeIn();
        }
    });

    $('#scheduleForm').on('submit', function(e) {
        e.preventDefault();
        
        let formData = $(this).serialize();

        $.ajax({
            url: 'backend/schedules_action.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#scheduleModal').modal('hide');
                    loadSchedules($('#filterClass').val());
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
            }
        });
    });
});

const dayTranslations = {
    'Monday': '<span class="badge bg-warning text-dark">จันทร์</span>',
    'Tuesday': '<span class="badge" style="background-color: #ffb6c1; color: #000;">อังคาร</span>',
    'Wednesday': '<span class="badge bg-success">พุธ</span>',
    'Thursday': '<span class="badge" style="background-color: #ff8c00;">พฤหัสบดี</span>',
    'Friday': '<span class="badge bg-info text-dark">ศุกร์</span>',
    'Saturday': '<span class="badge" style="background-color: #800080;">เสาร์</span>',
    'Sunday': '<span class="badge bg-danger">อาทิตย์</span>'
};

function loadSchedules(classId) {
    $.ajax({
        url: 'backend/schedules_action.php',
        type: 'POST',
        data: { action: 'read', class_id: classId },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                let tbody = $('#schedulesTable tbody');
                tbody.empty();
                
                if(response.data.length === 0) {
                    tbody.append('<tr><td colspan="7" class="text-center text-muted py-4">ยังไม่มีตารางเรียนสำหรับชั้นเรียนนี้</td></tr>');
                    return;
                }
                
                $.each(response.data, function(index, schedule) {
                    let dayHtml = dayTranslations[schedule.day_of_week] || schedule.day_of_week;
                    
                    let tr = `
                        <tr>
                            <td>${dayHtml}</td>
                            <td class="fw-bold text-success">${schedule.start_time.substring(0,5)}</td>
                            <td class="fw-bold text-danger">${schedule.end_time.substring(0,5)}</td>
                            <td class="fw-semibold">${schedule.subject_code} - ${schedule.subject_name}</td>
                            <td>${schedule.teacher_fname} ${schedule.teacher_lname}</td>
                            <td><i class="fas fa-door-open text-muted me-1"></i> ${schedule.room_name}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteSchedule(${schedule.id})" title="ลบคาบเรียน">
                                    <i class="fas fa-trash-alt"></i> ลบ
                                </button>
                            </td>
                        </tr>
                    `;
                    tbody.append(tr);
                });
            }
        }
    });
}

function openAddModal() {
    let currentClassId = $('#filterClass').val();
    if(!currentClassId) return;

    $('#scheduleForm')[0].reset();
    $('#modalClassId').val(currentClassId);
    $('#scheduleModal').modal('show');
}

function deleteSchedule(id) {
    if (confirm('คุณแน่ใจหรือไม่ที่จะลบรายการคาบเรียนนี้อย่างถาวร?')) {
        $.ajax({
            url: 'backend/schedules_action.php',
            type: 'POST',
            data: { action: 'delete', id: id },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    loadSchedules($('#filterClass').val());
                } else {
                    alert('Error: ' + response.message);
                }
            }
        });
    }
}
</script>
