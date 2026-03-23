<?php require_once 'includes/auth_check.php'; ?>
<?php require_once 'config/database.php'; ?>
<?php 
// Fetch statistics from database
try {
    $teacherCount = $pdo->query("SELECT COUNT(*) FROM teachers")->fetchColumn();
    $studentCount = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
    $subjectCount = $pdo->query("SELECT COUNT(*) FROM subjects")->fetchColumn();
    $classroomCount = $pdo->query("SELECT COUNT(*) FROM classrooms")->fetchColumn();
} catch (PDOException $e) {
    $teacherCount = $studentCount = $subjectCount = $classroomCount = 0;
}
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<!-- Main Content -->
<div id="content">
    
    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top shadow-sm">
        <div class="container-fluid">
            <!-- Sidebar Toggle Button -->
            <button type="button" id="sidebarToggle" class="btn btn-primary-custom">
                <i class="fas fa-bars"></i>
            </button>

            <!-- Search Bar (Optional, adds to modern feel) -->
            <form class="d-none d-md-flex ms-4" style="width: 300px;">
                <div class="input-group">
                    <input type="text" class="form-control border-end-0 bg-light" placeholder="ค้นหาข้อมูล..." aria-label="Search">
                    <span class="input-group-text bg-light border-start-0 text-muted">
                        <i class="fas fa-search"></i>
                    </span>
                </div>
            </form>

            <!-- Navbar Right Side -->
            <ul class="navbar-nav ms-auto align-items-center flex-row">
                <!-- Notifications -->
                <li class="nav-item me-3">
                    <a class="nav-link position-relative" href="#">
                        <i class="fas fa-bell fs-5 text-muted"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                            3
                            <span class="visually-hidden">unread messages</span>
                        </span>
                    </a>
                </li>
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

    <!-- Dashboard Content -->
    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-primary-custom mb-0">
                <i class="fas fa-chart-pie me-2"></i> ภาพรวมระบบ (Dashboard)
            </h3>
            <button class="btn btn-outline-primary d-none d-md-block shadow-sm">
                <i class="fas fa-download me-1"></i> ดาวน์โหลดรายงาน
            </button>
        </div>
        
        <!-- Statistic Cards -->
        <div class="row g-4 mb-4">
            <!-- Card 1 -->
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card h-100">
                    <div class="card-body p-4 d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted fw-semibold mb-1">จำนวนบุคลากรครู</p>
                            <h2 class="mb-0 text-primary-custom fw-bold" id="stat-teacher"><?= number_format($teacherCount) ?></h2>
                        </div>
                        <div class="icon-box text-primary-custom">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Card 2 -->
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card h-100">
                    <div class="card-body p-4 d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted fw-semibold mb-1">จำนวนนักเรียนทั้งหมด</p>
                            <h2 class="mb-0 text-success fw-bold" id="stat-student"><?= number_format($studentCount) ?></h2>
                        </div>
                        <div class="icon-box text-success" style="background-color: rgba(25,135,84,0.1);">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Card 3 -->
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card h-100">
                    <div class="card-body p-4 d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted fw-semibold mb-1">รายวิชาที่เปิดสอน</p>
                            <h2 class="mb-0 text-warning fw-bold" id="stat-subject"><?= number_format($subjectCount) ?></h2>
                        </div>
                        <div class="icon-box text-warning" style="background-color: rgba(255,193,7,0.1);">
                            <i class="fas fa-book"></i>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Card 4 -->
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card h-100">
                    <div class="card-body p-4 d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted fw-semibold mb-1">ห้องเรียนทั้งหมด</p>
                            <h2 class="mb-0 text-info fw-bold" id="stat-classroom"><?= number_format($classroomCount) ?></h2>
                        </div>
                        <div class="icon-box text-info" style="background-color: rgba(13,202,240,0.1);">
                            <i class="fas fa-door-open"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Charts Section -->
        <div class="row g-4">
            <!-- Line Chart: Attendance -->
            <div class="col-lg-7">
                <div class="card dashboard-card h-100">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                        <h6 class="fw-bold text-dark"><i class="fas fa-chart-line text-primary-custom me-2"></i> สถิติการมาเรียน</h6>
                    </div>
                    <div class="card-body p-4" style="position: relative; height: 350px;">
                        <canvas id="attendanceChart"></canvas>
                    </div>
                </div>
            </div>
            <!-- Bar Chart: Grade Distribution -->
            <div class="col-lg-5">
                <div class="card dashboard-card h-100">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                        <h6 class="fw-bold text-dark"><i class="fas fa-chart-bar text-primary-custom me-2"></i> ภาพรวมเกรดเฉลี่ย</h6>
                    </div>
                    <div class="card-body p-4" style="position: relative; height: 350px;">
                        <canvas id="gradeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

</div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
$(document).ready(function() {
    // Real-time Database Polling
    function fetchDashboardStats() {
        $.ajax({
            url: 'backend/dashboard_realtime.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    // Update UI using toLocaleString for number formats (commas)
                    $('#stat-teacher').text(Number(response.data.teacher_count).toLocaleString('th-TH'));
                    $('#stat-student').text(Number(response.data.student_count).toLocaleString('th-TH'));
                    $('#stat-subject').text(Number(response.data.subject_count).toLocaleString('th-TH'));
                    $('#stat-classroom').text(Number(response.data.classroom_count).toLocaleString('th-TH'));
                }
            }
        });
    }

    // Refresh dashboard stats every 5 seconds (5000 ms) automatically without reloading the page
    setInterval(fetchDashboardStats, 5000);
});
</script>
