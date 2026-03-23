<?php 
require_once 'includes/auth_check.php'; 
// Only admins should ideally access this, but we will allow view for now or restrict later
// checkRole(['admin']); 
require_once 'config/database.php';

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

            <!-- Search Bar -->
            <form class="d-none d-md-flex ms-4" style="width: 300px;">
                <div class="input-group">
                    <input type="text" class="form-control border-end-0 bg-light" placeholder="ค้นหาข้อมูลผู้ใช้งาน..." aria-label="Search">
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-primary-custom mb-0">
                <i class="fas fa-users-cog me-2"></i> ระบบจัดการผู้ใช้งาน
            </h3>
            <button class="btn btn-primary-custom shadow-sm" onclick="openAddModal()">
                <i class="fas fa-plus-circle me-1"></i> เพิ่มผู้ใช้งาน
            </button>
        </div>

        <!-- Data Table Card -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="usersTable">
                        <thead class="table-light">
                            <tr>
                                <th>ลำดับ</th>
                                <th>Username</th>
                                <th>ชื่อ-นามสกุล</th>
                                <th>สิทธิ์การใช้งาน (Role)</th>
                                <th>สถานะ</th>
                                <th>วันที่สร้าง</th>
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
</div>

<!-- Add/Edit User Modal -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow">
      <form id="userForm">
        <div class="modal-header bg-primary-custom text-white">
          <h5 class="modal-title fw-bold" id="userModalLabel">เพิ่มบัญชีผู้ใช้งานใหม่</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
            <input type="hidden" name="action" id="actionType" value="create">
            <input type="hidden" name="id" id="userId" value="">
            
            <div class="mb-3">
                <label for="username" class="form-label fw-bold">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label fw-bold">Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="พิมพ์เพื่อตั้งรหัสใหม่ (เว้นว่างไว้ถ้าไม่ต้องการเปลี่ยน)">
                <small class="text-muted" id="passwordHelp">สำหรับสร้างใหม่จำเป็นต้องระบุ สำหรับแก้ไขหากปล่อยว่างรหัสเดิมจะยังคงอยู่</small>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="firstName" class="form-label fw-bold">ชื่อ (First Name)</label>
                    <input type="text" class="form-control" id="firstName" name="first_name" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="lastName" class="form-label fw-bold">นามสกุล (Last Name)</label>
                    <input type="text" class="form-control" id="lastName" name="last_name" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="role" class="form-label fw-bold">สิทธิ์การเข้าถึง (Role)</label>
                <select class="form-select" id="role" name="role" required>
                    <option value="student">นักเรียน (Student)</option>
                    <option value="teacher">คุณครู (Teacher)</option>
                    <option value="admin">ผู้ดูแลระบบ (Admin)</option>
                </select>
            </div>

            <div class="form-check form-switch mt-4">
                <input class="form-check-input" type="checkbox" id="isActive" name="is_active" value="1" checked>
                <label class="form-check-label fw-bold" for="isActive">เปิดใช้งานบัญชี (Active)</label>
            </div>
            
        </div>
        <div class="modal-footer bg-light">
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
    loadUsers();

    $('#userForm').on('submit', function(e) {
        e.preventDefault();
        
        let formData = $(this).serialize();
        
        // Custom validaton for create
        if ($('#actionType').val() === 'create' && $('#password').val().trim() === '') {
            alert('กรุณาระบุรหัสผ่านสำหรับการสร้างบัญชีใหม่');
            return;
        }

        $.ajax({
            url: 'backend/users_action.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#userModal').modal('hide');
                    loadUsers();
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

function loadUsers() {
    $.ajax({
        url: 'backend/users_action.php',
        type: 'POST',
        data: { action: 'read' },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                let tbody = $('#usersTable tbody');
                tbody.empty();
                
                if(response.data.length === 0) {
                    tbody.append('<tr><td colspan="7" class="text-center text-muted py-4">ไม่พบข้อมูลผู้ใช้งาน</td></tr>');
                    return;
                }
                
                $.each(response.data, function(index, user) {
                    let roleBadge = '';
                    if (user.role === 'admin') roleBadge = '<span class="badge bg-danger">Admin</span>';
                    else if (user.role === 'teacher') roleBadge = '<span class="badge bg-primary">Teacher</span>';
                    else roleBadge = '<span class="badge bg-success">Student</span>';

                    let statusBadge = user.is_active == 1 ? '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>ใช้งาน</span>' : '<span class="badge bg-secondary"><i class="fas fa-ban me-1"></i>ระงับ</span>';
                    
                    let createdDate = new Date(user.created_at).toLocaleDateString('th-TH');

                    let tr = `
                        <tr>
                            <td class="text-muted fw-bold">${index + 1}</td>
                            <td class="fw-semibold">${user.username}</td>
                            <td>${user.first_name} ${user.last_name}</td>
                            <td>${roleBadge}</td>
                            <td>${statusBadge}</td>
                            <td>${createdDate}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-warning me-1" onclick='openEditModal(${JSON.stringify(user)})' title="แก้ไข">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteUser(${user.id})" title="ลบ">
                                    <i class="fas fa-trash-alt"></i>
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
    $('#userForm')[0].reset();
    $('#userId').val('');
    $('#actionType').val('create');
    $('#userModalLabel').text('เพิ่มบัญชีผู้ใช้งานใหม่');
    $('#password').prop('required', true);
    $('#passwordHelp').text('จำเป็นต้องระบุรหัสผ่านขั้นต่ำ 6 ตัวอักษร');
    $('#userModal').modal('show');
}

function openEditModal(user) {
    $('#userForm')[0].reset();
    $('#userId').val(user.id);
    $('#username').val(user.username);
    $('#firstName').val(user.first_name);
    $('#lastName').val(user.last_name);
    $('#role').val(user.role);
    $('#isActive').prop('checked', user.is_active == 1);
    
    $('#actionType').val('update');
    $('#userModalLabel').text('แก้ไขข้อมูลผู้ใช้งาน');
    $('#password').prop('required', false);
    $('#passwordHelp').text('เว้นว่างไว้หากไม่ต้องการเปลี่ยนรหัสผ่าน');
    
    $('#userModal').modal('show');
}

function deleteUser(id) {
    if (confirm('ระบบจะทำการลบบัญชีผู้ใช้นี้อย่างถาวร คุณแน่ใจหรือไม่?')) {
        $.ajax({
            url: 'backend/users_action.php',
            type: 'POST',
            data: { action: 'delete', id: id },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    loadUsers();
                } else {
                    alert('Error: ' + response.message);
                }
            }
        });
    }
}
</script>
