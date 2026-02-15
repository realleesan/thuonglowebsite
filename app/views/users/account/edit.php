<?php
// User Account Edit - Edit Account Information
require_once __DIR__ . '/../../../services/UserService.php';

// Get current user from session
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    header('Location: ?page=login');
    exit;
}

// Get account data from UserService
try {
    $userService = new UserService();
    $accountData = $userService->getAccountData($userId);
    $user = $accountData['user'] ?? [];
} catch (Exception $e) {
    // Fallback to session data if service fails
    $user = [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'] ?? 'User',
        'username' => $_SESSION['username'] ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'phone' => '',
        'address' => '',
        'role' => $_SESSION['user_role'] ?? 'user',
        'points' => 0,
        'level' => 'Bronze',
        'status' => 'active',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
}

// Handle form submission (mock)
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mock form processing
    if (isset($_POST['update_profile'])) {
        $success_message = 'Thông tin tài khoản đã được cập nhật thành công!';
    } elseif (isset($_POST['change_password'])) {
        $success_message = 'Mật khẩu đã được thay đổi thành công!';
    } elseif (isset($_POST['update_security'])) {
        $success_message = 'Cài đặt bảo mật đã được cập nhật!';
    }
}
?>

<div class="user-content-with-sidebar">
    <!-- User Sidebar -->
    <?php include 'app/views/_layout/user_sidebar.php'; ?>
    
    <!-- Account Edit Content -->
    <div class="user-account">
        <!-- Account Header -->
        <div class="account-header">
            <div class="account-header-left">
                <h1>Chỉnh sửa tài khoản</h1>
                <p>Cập nhật thông tin cá nhân và cài đặt bảo mật</p>
            </div>
            <div class="account-actions">
                <a href="?page=users&module=account" class="account-btn account-btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Quay lại
                </a>
                <a href="?page=users&module=account&action=view" class="account-btn account-btn-secondary">
                    <i class="fas fa-eye"></i>
                    Xem chi tiết
                </a>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <!-- Account Content Grid -->
        <div class="account-content">
            <!-- Profile Information Form -->
            <div class="profile-card profile-card-full">
                <div class="profile-card-header">
                    <h3>Thông tin cá nhân</h3>
                </div>
                <div class="profile-card-content">
                    <form method="POST" class="account-form" enctype="multipart/form-data">
                        <!-- Avatar Upload -->
                        <div class="profile-avatar-section">
                            <div class="profile-avatar">
                                <?php if (!empty($user['avatar'])): ?>
                                    <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar">
                                <?php else: ?>
                                    <div class="profile-avatar-placeholder">
                                        <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="profile-avatar-info">
                                <h2>Ảnh đại diện</h2>
                                <p>Chọn ảnh đại diện cho tài khoản của bạn</p>
                                <input type="file" id="avatar-upload" name="avatar" accept="image/*" style="display: none;">
                                <label for="avatar-upload" class="account-btn account-btn-secondary">
                                    <i class="fas fa-camera"></i>
                                    Thay đổi ảnh
                                </label>
                            </div>
                        </div>

                        <!-- Profile Form Fields -->
                        <div class="profile-info-grid">
                            <div class="form-group">
                                <label for="name" class="form-label required">Họ và tên</label>
                                <input type="text" id="name" name="name" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                <div class="form-text">Tên hiển thị trên tài khoản của bạn</div>
                            </div>

                            <div class="form-group">
                                <label for="email" class="form-label required">Email</label>
                                <input type="email" id="email" name="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                <div class="form-text">Email dùng để đăng nhập và nhận thông báo</div>
                            </div>

                            <div class="form-group">
                                <label for="phone" class="form-label">Số điện thoại</label>
                                <input type="tel" id="phone" name="phone" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['phone']); ?>">
                                <div class="form-text">Số điện thoại liên hệ (tùy chọn)</div>
                            </div>

                            <div class="form-group">
                                <label for="address" class="form-label">Địa chỉ</label>
                                <textarea id="address" name="address" class="form-control" rows="3"><?php echo htmlspecialchars($user['address']); ?></textarea>
                                <div class="form-text">Địa chỉ giao hàng mặc định</div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" name="update_profile" class="account-btn account-btn-primary">
                                <i class="fas fa-save"></i>
                                Lưu thay đổi
                            </button>
                            <button type="reset" class="account-btn account-btn-secondary">
                                <i class="fas fa-undo"></i>
                                Khôi phục
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Secondary Cards Grid -->
            <div class="secondary-cards-grid">
                <!-- Password Change Form -->
                <div class="profile-card">
                    <div class="profile-card-header">
                        <h3 id="password">Thay đổi mật khẩu</h3>
                    </div>
                    <div class="profile-card-content">
                        <form method="POST" class="account-form">
                            <div class="form-group">
                                <label for="current_password" class="form-label required">Mật khẩu hiện tại</label>
                                <div style="position: relative;">
                                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                                    <button type="button" class="password-toggle" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #6b7280;">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="new_password" class="form-label required">Mật khẩu mới</label>
                                <div style="position: relative;">
                                    <input type="password" id="new_password" name="new_password" class="form-control" required>
                                    <button type="button" class="password-toggle" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #6b7280;">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">Mật khẩu phải có ít nhất 6 ký tự</div>
                            </div>

                            <div class="form-group">
                                <label for="confirm_password" class="form-label required">Xác nhận mật khẩu mới</label>
                                <div style="position: relative;">
                                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                                    <button type="button" class="password-toggle" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #6b7280;">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" name="change_password" class="account-btn account-btn-primary">
                                    <i class="fas fa-key"></i>
                                    Thay đổi mật khẩu
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Security Settings -->
                <div class="profile-card">
                    <div class="profile-card-header">
                        <h3 id="security">Cài đặt bảo mật</h3>
                    </div>
                    <div class="profile-card-content">
                        <form method="POST" class="account-form">
                            <div class="security-section">
                                <div class="security-item">
                                    <div class="security-item-info">
                                        <h4>Xác thực 2 bước</h4>
                                        <p>Tăng cường bảo mật tài khoản với xác thực 2 bước</p>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" name="two_factor_enabled" <?php echo ($user['two_factor_enabled'] ?? false) ? 'checked' : ''; ?>>
                                        <span class="slider"></span>
                                    </label>
                                </div>

                                <div class="security-item" id="notifications">
                                    <div class="security-item-info">
                                        <h4>Thông báo đăng nhập</h4>
                                        <p>Nhận thông báo khi có đăng nhập từ thiết bị mới</p>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" name="login_notifications" <?php echo ($user['login_notifications'] ?? true) ? 'checked' : ''; ?>>
                                        <span class="slider"></span>
                                    </label>
                                </div>

                                <div class="security-item">
                                    <div class="security-item-info">
                                        <h4>Thông báo email</h4>
                                        <p>Nhận thông báo về hoạt động tài khoản qua email</p>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" name="email_notifications" <?php echo ($user['email_notifications'] ?? true) ? 'checked' : ''; ?>>
                                        <span class="slider"></span>
                                    </label>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" name="update_security" class="account-btn account-btn-primary">
                                    <i class="fas fa-shield-alt"></i>
                                    Cập nhật bảo mật
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="profile-card">
                <div class="profile-card-header">
                    <h3>Vùng nguy hiểm</h3>
                </div>
                <div class="profile-card-content">
                    <div class="delete-account-section">
                        <h3>Xóa tài khoản</h3>
                        <p>Khi bạn xóa tài khoản, tất cả dữ liệu sẽ bị xóa vĩnh viễn và không thể khôi phục. Hãy chắc chắn về quyết định này.</p>
                        <a href="?page=users&module=account&action=delete" class="account-btn account-btn-danger delete-account-btn">
                            <i class="fas fa-trash"></i>
                            Xóa tài khoản
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Account JavaScript -->
<script src="assets/js/user_account.js"></script>

<!-- Toggle Switch Styles -->
<style>
.switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 24px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 24px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .slider {
    background-color: #356DF1;
}

input:checked + .slider:before {
    transform: translateX(26px);
}
</style>