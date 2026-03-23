<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$masterDataPages = ['teachers.php', 'students.php', 'subjects.php', 'classes.php', 'classrooms.php'];
$isMasterDataActive = in_array($currentPage, $masterDataPages);
?>
        <!-- Sidebar -->
        <nav id="sidebar">
            <div class="sidebar-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <i class="fas fa-school fs-3 me-2"></i>
                    <h5 class="mb-0 fw-bold">สาธิตวิทยา</h5>
                </div>
                <button id="sidebarToggleClose" class="btn text-white d-md-none p-0">
                    <i class="fas fa-times fs-4"></i>
                </button>
            </div>

            <div class="px-3 pb-2 text-center mt-3">
                <div class="bg-white text-primary-custom rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 70px; height: 70px;">
                    <i class="fas fa-user-shield fs-1"></i>
                </div>
                <h6 class="mb-0"><?= htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']) ?></h6>
                <small class="text-white-50">Role: <?= ucfirst(htmlspecialchars($_SESSION['role'])) ?></small>
            </div>

            <ul class="list-unstyled components mt-2">
                <!-- Module 6: Dashboard -->
                <li class="<?= ($currentPage == 'index.php') ? 'active' : '' ?>">
                    <a href="index.php">
                        <i class="fas fa-chart-line me-2 w-20px"></i> แดชบอร์ด
                    </a>
                </li>

                <!-- Module 1: Master Data -->
                <li class="<?= $isMasterDataActive ? 'active' : '' ?>">
                    <a href="#masterDataSubmenu" data-bs-toggle="collapse" aria-expanded="<?= $isMasterDataActive ? 'true' : 'false' ?>" class="dropdown-toggle d-flex align-items-center justify-content-between">
                        <span><i class="fas fa-database me-2 w-20px"></i> จัดการข้อมูลพื้นฐาน</span>
                    </a>
                    <ul class="collapse list-unstyled <?= $isMasterDataActive ? 'show' : '' ?>" id="masterDataSubmenu">
                        <li class="<?= ($currentPage == 'teachers.php') ? 'active' : '' ?>"><a href="teachers.php"><i class="fas fa-chalkboard-teacher me-2"></i> ข้อมูลครู</a></li>
                        <li class="<?= ($currentPage == 'students.php') ? 'active' : '' ?>"><a href="students.php"><i class="fas fa-user-graduate me-2"></i> ข้อมูลนักเรียน</a></li>
                        <li class="<?= ($currentPage == 'subjects.php') ? 'active' : '' ?>"><a href="#"><i class="fas fa-book me-2"></i> ข้อมูลรายวิชา</a></li>
                        <li class="<?= ($currentPage == 'classes.php') ? 'active' : '' ?>"><a href="#"><i class="fas fa-layer-group me-2"></i> ข้อมูลระดับชั้นเรียน</a></li>
                        <li class="<?= ($currentPage == 'classrooms.php') ? 'active' : '' ?>"><a href="#"><i class="fas fa-door-open me-2"></i> ข้อมูลห้องเรียน</a></li>
                    </ul>
                </li>

                <!-- Module 2: Schedule -->
                <li>
                    <a href="#">
                        <i class="fas fa-calendar-alt me-2 w-20px"></i> จัดการตารางเรียน
                    </a>
                </li>

                <!-- Module 3: Grading -->
                <li>
                    <a href="#">
                        <i class="fas fa-star me-2 w-20px"></i> บันทึกผลการเรียน
                    </a>
                </li>

                <!-- Module 5: Attendance -->
                <li>
                    <a href="#">
                        <i class="fas fa-clipboard-user me-2 w-20px"></i> ระบบบันทึกเวลาเรียน
                    </a>
                </li>

                <!-- Module 4: Authentication & RBAC -->
                <li>
                    <a href="#">
                        <i class="fas fa-users-cog me-2 w-20px"></i> จัดการผู้ใช้งาน
                    </a>
                </li>
            </ul>
        </nav>
