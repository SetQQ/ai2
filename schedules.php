<?php 
require_once 'includes/auth_check.php'; 
require_once 'config/database.php';

// Fetch lookups for dropdowns
try {
    $classesList = $pdo->query("SELECT DISTINCT c.id, c.class_name FROM classes c JOIN students s ON c.id = s.class_id ORDER BY c.class_name ASC")->fetchAll(PDO::FETCH_ASSOC);
    $subjectsList = $pdo->query("SELECT id, subject_name, subject_code FROM subjects ORDER BY subject_name ASC")->fetchAll(PDO::FETCH_ASSOC);
    $teachersList = $pdo->query("SELECT id, first_name, last_name FROM teachers ORDER BY first_name ASC")->fetchAll(PDO::FETCH_ASSOC);
    $classroomsList = $pdo->query("SELECT id, room_code, room_name FROM classrooms ORDER BY room_code ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $classesList = $subjectsList = $teachersList = $classroomsList = [];
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
                    <i class="fas fa-calendar-alt me-2"></i> <?= ($_SESSION['role'] === 'admin') ? 'จัดการตารางเรียน' : 'ตารางเรียน' ?>
                </h3>
                <?php if($_SESSION['role'] === 'admin'): ?>
                    <p class="text-muted mb-0">ปรับปรุงตารางเรียนของแต่ละชั้นเรียนได้ง่ายๆ ที่นี่</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="row mb-4 justify-content-center">
            <?php if($_SESSION['role'] === 'admin'): ?>
            <div class="col-md-10 col-lg-8">
                <div class="card border-0 shadow-sm bg-white overflow-hidden">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-4 text-center text-md-end mb-3 mb-md-0">
                                <label for="filterClass" class="form-label mb-0 fw-bold text-dark fs-5">เลือกระดับชั้นเรียน:</label>
                            </div>
                            <div class="col-md-8">
                                <select id="filterClass" class="form-select form-select-lg border-primary shadow-sm">
                                    <option value="">-- กรุณาเลือกระดับชั้นเรียน เพื่อแสดงข้อมูล --</option>
                                    <?php foreach($classesList as $c): ?>
                                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['class_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
                <input type="hidden" id="filterClass" value="<?= $_SESSION['role'] === 'teacher' ? 'teacher_all' : ($_SESSION['class_id'] ?? '') ?>">
            <?php endif; ?>
        </div>

        <div id="scheduleContainer" style="display: none;">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <div id="gridWrapper" class="table-responsive">
                        <!-- Grid generated by JS -->
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

<!-- Add/Edit Schedule Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1" aria-labelledby="scheduleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content border-0 shadow">
      <form id="scheduleForm">
        <div class="modal-header bg-primary-custom text-white">
          <h5 class="modal-title fw-bold" id="scheduleModalLabel">จัดการคาบเรียน</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
            <input type="hidden" name="id" id="scheduleId" value="">
            <input type="hidden" name="action" id="actionType" value="create">
            <input type="hidden" name="class_id" id="modalClassId" value="">
            
            <input type="hidden" name="day_of_week" id="dayOfWeek" value="">
            <input type="hidden" name="start_time" id="startTime" value="">
            <input type="hidden" name="end_time" id="endTime" value="">

            <div class="row mb-4 bg-light p-3 rounded mx-1 border">
                <div class="col-12">
                    <h6 class="fw-bold text-primary border-bottom pb-2 mb-3"><i class="far fa-clock me-1"></i> เวลาเรียนที่เลือก</h6>
                    <div class="row text-center">
                        <div class="col-4">
                            <span class="text-muted d-block small">วันในสัปดาห์</span>
                            <span id="displayDay" class="fw-bold fs-5 text-dark"></span>
                        </div>
                        <div class="col-4">
                            <span class="text-muted d-block small">เวลาเริ่มต้น</span>
                            <span id="displayStartTime" class="fw-bold fs-5 text-success"></span>
                        </div>
                        <div class="col-4">
                            <span class="text-muted d-block small">เวลาสิ้นสุด</span>
                            <span id="displayEndTime" class="fw-bold fs-5 text-danger"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="subjectId" class="form-label fw-bold">วิชาเรียน</label>
                    <select class="form-select border-primary" id="subjectId" name="subject_id" required>
                        <option value="">-- เลือกวิชา --</option>
                        <?php foreach($subjectsList as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['subject_code'].' - '.$s['subject_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="teacherId" class="form-label fw-bold">ครูผู้สอน</label>
                    <select class="form-select border-primary" id="teacherId" name="teacher_id" required>
                        <option value="">-- เลือกครู --</option>
                        <?php foreach($teachersList as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['first_name'].' '.$t['last_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="classroomId" class="form-label fw-bold">สถานที่เรียน (ห้องเรียน)</label>
                    <select class="form-select border-primary" id="classroomId" name="classroom_id">
                        <option value="">-- ระบุหรือไม่ระบุก็ได้ --</option>
                        <?php foreach($classroomsList as $cr): ?>
                            <option value="<?= $cr['id'] ?>"><?= htmlspecialchars($cr['room_name'] ?: 'ยังไม่กำหนดชื่อห้อง') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
        </div>
        <div class="modal-footer bg-light px-4 py-3 d-flex justify-content-between">
          <button type="button" class="btn btn-danger" id="btnDeleteSchedule" style="display: none;" onclick="deleteCurrentSchedule()">
            <i class="fas fa-trash-alt me-1"></i> ลบคาบเรียนนี้
          </button>
          <div class="ms-auto">
              <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">ยกเลิก</button>
              <button type="submit" class="btn btn-primary-custom" id="btnSaveSchedule">บันทึกข้อมูล</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<style>
.schedule-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
}
.empty-slot:hover {
    background-color: #f8f9fa!important;
    border-color: var(--bs-primary)!important;
}
.empty-slot:hover i {
    opacity: 1!important;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const userRole = '<?= $_SESSION['role'] ?>';
const juniorPeriods = [
    { start: '08:30:00', end: '09:30:00', label: 'คาบ 1<br><small>08:30-09:30</small>', isLunch: false },
    { start: '09:30:00', end: '10:30:00', label: 'คาบ 2<br><small>09:30-10:30</small>', isLunch: false },
    { start: '10:30:00', end: '11:30:00', label: 'คาบ 3<br><small>10:30-11:30</small>', isLunch: false },
    { start: '11:30:00', end: '12:30:00', label: 'พักกลางวัน<br><small>11:30-12:30</small>', isLunch: true },
    { start: '12:30:00', end: '13:30:00', label: 'คาบ 4<br><small>12:30-13:30</small>', isLunch: false },
    { start: '13:30:00', end: '14:30:00', label: 'คาบ 5<br><small>13:30-14:30</small>', isLunch: false },
    { start: '14:30:00', end: '15:30:00', label: 'คาบ 6<br><small>14:30-15:30</small>', isLunch: false }
];

const seniorPeriods = [
    { start: '08:30:00', end: '09:25:00', label: 'คาบ 1<br><small>08:30-09:25</small>', isLunch: false },
    { start: '09:25:00', end: '10:20:00', label: 'คาบ 2<br><small>09:25-10:20</small>', isLunch: false },
    { start: '10:20:00', end: '11:15:00', label: 'คาบ 3<br><small>10:20-11:15</small>', isLunch: false },
    { start: '11:15:00', end: '12:10:00', label: 'คาบ 4<br><small>11:15-12:10</small>', isLunch: false },
    { start: '12:10:00', end: '13:05:00', label: 'พักกลางวัน<br><small>12:10-13:05</small>', isLunch: true },
    { start: '13:05:00', end: '14:00:00', label: 'คาบ 5<br><small>13:05-14:00</small>', isLunch: false },
    { start: '14:00:00', end: '14:55:00', label: 'คาบ 6<br><small>14:00-14:55</small>', isLunch: false },
    { start: '14:55:00', end: '15:50:00', label: 'คาบ 7<br><small>14:55-15:50</small>', isLunch: false }
];

const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
const dayLabels = {
    'Monday': { label: 'จันทร์', thai: 'วันจันทร์', bgColor: '#ffc107', textColor: '#000' },
    'Tuesday': { label: 'อังคาร', thai: 'วันอังคาร', bgColor: '#ffb6c1', textColor: '#000' },
    'Wednesday': { label: 'พุธ', thai: 'วันพุธ', bgColor: '#28a745', textColor: '#fff' },
    'Thursday': { label: 'พฤหัสบดี', thai: 'วันพฤหัสบดี', bgColor: '#fd7e14', textColor: '#fff' },
    'Friday': { label: 'ศุกร์', thai: 'วันศุกร์', bgColor: '#0dcaf0', textColor: '#000' }
};

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

    // Auto-load for Student
    if ($('#filterClass').is('input[type="hidden"]')) {
        let classId = $('#filterClass').val();
        if (classId) {
            $('#noClassAlert').hide();
            $('#scheduleContainer').show();
            loadSchedules(classId);
        }
    }

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
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด',
                        text: response.message
                    });
                }
            },
            error: function() {
                Swal.fire('Error', 'เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
            }
        });
    });
});

function loadSchedules(classId) {
    $.ajax({
        url: 'backend/schedules_action.php',
        type: 'POST',
        data: { action: 'read', class_id: classId },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                renderGrid(response.data, response.is_junior_high);
            }
        }
    });
}

function renderGrid(scheduleData, isJuniorHigh) {
    let periods = isJuniorHigh ? juniorPeriods : seniorPeriods;
    let wrapper = $('#gridWrapper');
    
    let table = '<table class="table table-bordered text-center align-middle" style="table-layout: fixed; min-width: 900px;">';
    
    // Header Row
    table += '<thead class="table-light"><tr>';
    table += '<th style="width: 80px;" class="fw-bold fs-6">วัน / เวลา</th>';
    periods.forEach(p => {
        table += `<th>${p.label}</th>`;
    });
    table += '</tr></thead><tbody>';

    // Body Rows
    days.forEach(day => {
        let dayConfig = dayLabels[day];
        table += `<tr><td style="background-color: ${dayConfig.bgColor}; color: ${dayConfig.textColor}; font-weight: bold; font-size: 1.1rem; border-right: 2px solid #dee2e6;">${dayConfig.label}</td>`;
        
        periods.forEach(p => {
            if (p.isLunch) {
                table += `<td class="bg-secondary bg-opacity-10 text-muted fw-bold" style="background-image: repeating-linear-gradient(45deg, transparent, transparent 10px, rgba(0,0,0,.03) 10px, rgba(0,0,0,.03) 20px);">
                            <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                <i class="fas fa-utensils mb-1 fs-5"></i>
                                <span>พัก</span>
                            </div>
                          </td>`;
            } else {
                let sch = scheduleData.find(s => s.day_of_week === day && s.start_time >= p.start && s.start_time < p.end);
                
                if (sch) {
                    let clickAction = (userRole === 'admin') ? `onclick='openEditModal(${JSON.stringify(sch).replace(/'/g, "&apos;")})'` : '';
                    let classBadge = (userRole === 'teacher' && sch.class_name) ? `<span class="badge bg-secondary mb-1" style="font-size: 0.7rem;">${sch.class_name}</span>` : '';
                    
                    let roomText = sch.room_name;
                    let roomBadge = roomText ? `<span class="badge bg-info text-dark mb-1" style="font-size: 0.7rem; margin-left:2px;">${roomText.trim()}</span>` : '';
                    
                    table += `<td class="p-2 position-relative">
                        <div class="schedule-card p-2 rounded shadow-sm bg-white h-100 d-flex flex-column justify-content-center border" style="cursor: ${(userRole === 'admin') ? 'pointer' : 'default'}; border-left: 4px solid var(--bs-primary) !important; font-size: 0.85rem; min-height: 90px; transition: 0.2s;" ${clickAction}>
                            <div class="d-flex justify-content-center flex-wrap">${classBadge}${roomBadge}</div>
                            <div class="fw-bold text-primary text-truncate pb-1 border-bottom border-light" title="${sch.subject_code}">${sch.subject_code}</div>
                            <div class="text-truncate text-dark mt-1" title="${sch.subject_name}" style="font-size: 0.8rem;">${sch.subject_name}</div>
                            <div class="text-truncate mt-1 text-muted" title="${sch.teacher_fname} ${sch.teacher_lname}" style="font-size: 0.75rem;"><i class="fas fa-user me-1"></i>${sch.teacher_fname} ${sch.teacher_lname.substring(0,1)}.</div>
                        </div>
                    </td>`;
                } else {
                    let clickAction = (userRole === 'admin') ? `onclick="openSlotAddModal('${day}', '${p.start}', '${p.end}')"` : '';
                    table += `<td class="p-2">
                        <div class="empty-slot text-muted h-100 w-100 d-flex flex-column align-items-center justify-content-center" style="cursor: ${(userRole === 'admin') ? 'pointer' : 'default'}; border: 2px dashed #dee2e6; border-radius: 6px; min-height: 90px; transition: 0.2s;" ${clickAction}>
                            <i class="fas fa-plus-circle text-primary opacity-25 fs-4 mb-1"></i>
                            <small class="opacity-50">ว่าง</small>
                        </div>
                    </td>`;
                }
            }
        });
        table += '</tr>';
    });

    table += '</tbody></table>';
    wrapper.html(table);
}

function openSlotAddModal(day, start, end) {
    let currentClassId = $('#filterClass').val();
    if(!currentClassId) return;

    $('#scheduleForm')[0].reset();
    $('#scheduleId').val('');
    $('#actionType').val('create');
    $('#modalClassId').val(currentClassId);
    $('#subjectId').val('');
    $('#teacherId').val('');
    $('#classroomId').val('');
    
    // Set hidden inputs
    $('#dayOfWeek').val(day);
    $('#startTime').val(start);
    $('#endTime').val(end);
    
    // Display textual representation
    $('#displayDay').text(dayLabels[day].thai);
    $('#displayStartTime').text(start.substring(0,5));
    $('#displayEndTime').text(end.substring(0,5));

    $('#scheduleModalLabel').text('เพิ่มคาบเรียน');
    $('#btnDeleteSchedule').hide();
    
    $('#scheduleModal').modal('show');
}

function openEditModal(schedule) {
    $('#scheduleForm')[0].reset();
    $('#scheduleId').val(schedule.id);
    $('#actionType').val('update');
    $('#modalClassId').val(schedule.class_id);
    
    $('#dayOfWeek').val(schedule.day_of_week);
    $('#startTime').val(schedule.start_time);
    $('#endTime').val(schedule.end_time);
    
    $('#displayDay').text(dayLabels[schedule.day_of_week].thai);
    $('#displayStartTime').text(schedule.start_time.substring(0,5));
    $('#displayEndTime').text(schedule.end_time.substring(0,5));

    $('#subjectId').val(schedule.subject_id);
    $('#teacherId').val(schedule.teacher_id);
    $('#classroomId').val(schedule.classroom_id || '');
    
    $('#scheduleModalLabel').text('แก้ไขคาบเรียน');
    $('#btnDeleteSchedule').show();
    
    $('#scheduleModal').modal('show');
}

function deleteCurrentSchedule() {
    let id = $('#scheduleId').val();
    if(!id) return;
    
    Swal.fire({
        title: 'ยืนยันการลบ?',
        text: "คุณแน่ใจหรือไม่ที่จะลบรายวิชานี้ออกจากคาบเรียน?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'ตกลง',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'backend/schedules_action.php',
                type: 'POST',
                data: { action: 'delete', id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#scheduleModal').modal('hide');
                        loadSchedules($('#filterClass').val());
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                }
            });
        }
    });
}
</script>
