<?php
// Load ViewDataService and ErrorHandler
require_once __DIR__ . '/../../../services/ViewDataService.php';
require_once __DIR__ . '/../../../services/ErrorHandler.php';

try {
    $viewDataService = new ViewDataService();
    
    // Get user ID from URL
    $user_id = (int)($_GET['id'] ?? 0);
    
    // Get user details using ViewDataService
    $userData = $viewDataService->getAdminUserDetailsData($user_id);
    $user = $userData['user'];
    
    // Redirect if user not found
    if (!$user) {
        header('Location: ?page=admin&module=users&error=not_found');
        exit;
    }
    
} catch (Exception $e) {
    ErrorHandler::logError('Admin Users Edit', $e->getMessage());
    header('Location: ?page=admin&module=users&error=system_error');
    exit;
}

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate form data
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $role = $_POST['role'] ?? '';
    $status = $_POST['status'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($name)) {
        $errors[] = 'Tên người dùng là bắt buộc';
    }
    
    if (empty($email)) {
        $errors[] = 'Email là bắt buộc';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email không hợp lệ';
    }
    
    if (empty($phone)) {
        $errors[] = 'Số điện thoại là bắt buộc';
    }
    
    if (empty($role)) {
        $errors[] = 'Vai trò là bắt buộc';
    }
    
    // Password validation (only if provided)
    if (!empty($password)) {
        if (strlen($password) < 6) {
            $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự';
        }
        
        if ($password !== $confirm_password) {
            $errors[] = 'Xác nhận mật khẩu không khớp';
        }
    }

    // If no errors, simulate save
    if (empty($errors)) {
        $success = true;
        // In real app: update database
        // Update user array for display
        $user['name'] = $name;
        $user['email'] = $email;
        $user['phone'] = $phone;
        $user['address'] = $address;
        $user['role'] = $role;
        $user['status'] = $status;
    }
} else {
    // Pre-fill form with existing data
    $_POST = $user;
}

// Get role display name
function getRoleDisplayName($role) {
    $roles = [
        'admin' => 'Quản trị viên',
        'user' => 'Người dùng',
        'agent' => 'Đại lý'
    ];
    return $roles[$role] ?? $role;
}
?>

<div class="users-edit-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-user-edit"></i>
                Chỉnh Sửa Người Dùng
            </h1>
            <p class="page-description">Cập nhật thông tin người dùng: <?= htmlspecialchars($user['name']) ?></p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=users&action=view&id=<?= $user['id'] ?>" class="btn btn-info">
                <i class="fas fa-eye"></i>
                Xem chi tiết
            </a>
            <a href="?page=admin&module=users" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i>
                Quay lại danh sách
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                <strong>Có lỗi xảy ra:</strong>
                <ul class="error-list">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <span>Cập nhật thông tin người dùng thành công! (Demo)</span>
        </div>
    <?php endif; ?>

    <!-- Form Container -->
    <div class="form-container">
        <form method="POST" class="admin-form" enctype="multipart/form-data">
            <div class="form-grid">
                <!-- Left Column -->
                <div class="form-column">
                    <!-- Basic Information -->
                    <div class="form-section">
                        <h3 class="section-title">Thông Tin Cơ Bản</h3>
                        
                        <div class="form-group">
                            <label for="user_id">ID người dùng</label>
                            <input type="text" id="user_id" value="<?= $user['id'] ?>" class="readonly" readonly>
                            <small>ID duy nhất của người dùng trong hệ thống</small>
                        </div>

                        <div class="form-group">
                            <label for="name" class="required">Tên người dùng</label>
                            <input type="text" id="name" name="name" required 
                                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                                   placeholder="Nhập tên đầy đủ">
                            <small>Tên hiển thị của người dùng trong hệ thống</small>
                        </div>

                        <div class="form-group">
                            <label for="email" class="required">Email</label>
                            <input type="email" id="email" name="email" required 
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                   placeholder="user@example.com">
                            <small>Email sẽ được sử dụng để đăng nhập</small>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone" class="required">Số điện thoại</label>
                                <input type="tel" id="phone" name="phone" required 
                                       value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                                       placeholder="0901234567">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="address">Địa chỉ</label>
                            <textarea id="address" name="address" rows="3" 
                                      placeholder="Nhập địa chỉ đầy đủ"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="created_at">Ngày tạo</label>
                            <input type="text" id="created_at" value="<?= date('d/m/Y H:i', strtotime($user['created_at'])) ?>" 
                                   class="readonly" readonly>
                        </div>
                    </div>

                    <!-- Avatar Upload -->
                    <div class="form-section">
                        <h3 class="section-title">Ảnh Đại Diện</h3>
                        
                        <div class="form-group">
                            <label for="avatar">Cập nhật ảnh đại diện</label>
                            <div class="image-upload-container">
                                <div class="image-preview" id="avatarPreview">
                                    <div class="avatar-circle large">
                                        <?= strtoupper(substr($user['name'], 0, 2)) ?>
                                    </div>
                                </div>
                                <input type="file" id="avatar" name="avatar" class="image-input" 
                                       accept="image/*">
                                <div class="image-upload-info">
                                    <div class="current-image-info">
                                        Ảnh hiện tại: Avatar mặc định
                                    </div>
                                    <small>Định dạng: JPG, PNG, GIF. Kích thước tối đa: 2MB</small>
                                    <small>Kích thước khuyến nghị: 200x200px</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="form-column">
                    <!-- Account Settings -->
                    <div class="form-section">
                        <h3 class="section-title">Cài Đặt Tài Khoản</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="role" class="required">Vai trò</label>
                                <select id="role" name="role" required>
                                    <option value="">Chọn vai trò</option>
                                    <option value="user" <?= ($_POST['role'] ?? '') == 'user' ? 'selected' : '' ?>>
                                        Người dùng
                                    </option>
                                    <option value="agent" <?= ($_POST['role'] ?? '') == 'agent' ? 'selected' : '' ?>>
                                        Đại lý
                                    </option>
                                    <option value="admin" <?= ($_POST['role'] ?? '') == 'admin' ? 'selected' : '' ?>>
                                        Quản trị viên
                                    </option>
                                </select>
                                <small>Vai trò hiện tại: <strong><?= getRoleDisplayName($user['role']) ?></strong></small>
                            </div>

                            <div class="form-group">
                                <label for="status" class="required">Trạng thái</label>
                                <select id="status" name="status" required>
                                    <option value="">Chọn trạng thái</option>
                                    <option value="active" <?= ($_POST['status'] ?? '') == 'active' ? 'selected' : '' ?>>
                                        Hoạt động
                                    </option>
                                    <option value="inactive" <?= ($_POST['status'] ?? '') == 'inactive' ? 'selected' : '' ?>>
                                        Không hoạt động
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Password Change -->
                    <div class="form-section">
                        <h3 class="section-title">Đổi Mật Khẩu</h3>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <span>Để giữ nguyên mật khẩu hiện tại, hãy để trống các trường bên dưới</span>
                        </div>

                        <div class="form-group">
                            <label for="password">Mật khẩu mới</label>
                            <input type="password" id="password" name="password" 
                                   placeholder="Nhập mật khẩu mới (để trống nếu không đổi)">
                            <small>Mật khẩu phải có ít nhất 6 ký tự</small>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Xác nhận mật khẩu mới</label>
                            <input type="password" id="confirm_password" name="confirm_password" 
                                   placeholder="Nhập lại mật khẩu mới">
                        </div>

                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="force_password_change" value="1">
                                Yêu cầu đổi mật khẩu lần đăng nhập tiếp theo
                            </label>
                        </div>
                    </div>

                    <!-- Additional Settings -->
                    <div class="form-section">
                        <h3 class="section-title">Cài Đặt Bổ Sung</h3>
                        
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="send_notification_email" value="1">
                                Gửi email thông báo thay đổi
                            </label>
                            <small>Gửi email thông báo cho người dùng về việc cập nhật thông tin</small>
                        </div>

                        <div class="form-group">
                            <label for="notes">Ghi chú</label>
                            <textarea id="notes" name="notes" rows="4" 
                                      placeholder="Ghi chú về người dùng này..."><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
                            <small>Thông tin bổ sung về người dùng (chỉ admin xem được)</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Cập Nhật
                </button>
                <button type="button" class="btn btn-secondary" onclick="resetForm()">
                    <i class="fas fa-undo"></i>
                    Khôi phục
                </button>
                <a href="?page=admin&module=users&action=view&id=<?= $user['id'] ?>" class="btn btn-info">
                    <i class="fas fa-eye"></i>
                    Xem chi tiết
                </a>
                <a href="?page=admin&module=users" class="btn btn-outline">
                    <i class="fas fa-times"></i>
                    Hủy
                </a>
            </div>
        </form>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions-container">
        <div class="quick-actions">
            <h4>Thao Tác Nhanh</h4>
            <div class="quick-actions-grid">
                <button type="button" class="quick-action-btn" onclick="resetPassword()">
                    <i class="fas fa-key"></i>
                    <span>Reset mật khẩu</span>
                </button>
                <button type="button" class="quick-action-btn" onclick="sendWelcomeEmail()">
                    <i class="fas fa-envelope"></i>
                    <span>Gửi email chào mừng</span>
                </button>
                <button type="button" class="quick-action-btn" onclick="viewLoginHistory()">
                    <i class="fas fa-history"></i>
                    <span>Lịch sử đăng nhập</span>
                </button>
                <button type="button" class="quick-action-btn danger" onclick="deactivateUser()">
                    <i class="fas fa-ban"></i>
                    <span>Vô hiệu hóa</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function resetPassword() {
    if (confirm('Bạn có chắc chắn muốn reset mật khẩu cho người dùng này?')) {
        alert('Đã gửi email reset mật khẩu (Demo)');
    }
}

function sendWelcomeEmail() {
    if (confirm('Gửi lại email chào mừng cho người dùng này?')) {
        alert('Đã gửi email chào mừng (Demo)');
    }
}

function viewLoginHistory() {
    alert('Chức năng xem lịch sử đăng nhập (Demo)');
}

function deactivateUser() {
    if (confirm('Bạn có chắc chắn muốn vô hiệu hóa tài khoản này?')) {
        document.getElementById('status').value = 'inactive';
        alert('Đã vô hiệu hóa tài khoản (Demo)');
    }
}
</script>