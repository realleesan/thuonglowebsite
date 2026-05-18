<?php
// User Account Edit - Edit Account Information
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simple img_url function for avatar
function local_img_url($file) {
    // Tạo URL đầy đủ như sidebar
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    return $protocol . "://" . $host . "/assets/images/" . ltrim($file, '/');
}

// Get current user from session first
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    header('Location: ?page=login');
    exit;
}

// Fallback user data from session
$user = [
    'id' => $_SESSION['user_id'],
    'name' => $_SESSION['user_name'] ?? 'User',
    'username' => $_SESSION['username'] ?? '',
    'email' => $_SESSION['user_email'] ?? '',
    'phone' => $_SESSION['phone'] ?? '',
    'address' => $_SESSION['address'] ?? '',
    'role' => $_SESSION['user_role'] ?? 'user',
    'points' => $_SESSION['points'] ?? 0,
    'level' => $_SESSION['level'] ?? 'Bronze',
    'status' => 'active',
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s')
];

// Try to get account data from UserService (optional)
try {
    require_once __DIR__ . '/../../../services/UserService.php';
    $userService = new UserService();
    $accountData = $userService->getAccountData($userId);
    if (isset($accountData['user']) && !empty($accountData['user'])) {
        $user = array_merge($user, $accountData['user']);
    }
} catch (Exception $e) {
    // UserService failed, continue with session data
    error_log("UserService failed: " . $e->getMessage());
}

// Check if user has existing password for the password change form
$hasPassword = false;
try {
    require_once 'app/models/UsersModel.php';
    $usersModel = new UsersModel();
    $currentUser = $usersModel->find($userId);
    $hasPassword = $currentUser && !empty($currentUser['password']);
} catch (Exception $e) {
    // Database query failed, assume no password
    error_log("UsersModel query failed: " . $e->getMessage());
    $hasPassword = false;
}

// Handle form submission (mock)
$success_message = '';
$error_message = '';

// Check for success parameter from redirect
if (isset($_GET['success'])) {
    if ($_GET['success'] == '1') {
        $success_message = 'Thông tin tài khoản đã được cập nhật thành công!';
    } elseif ($_GET['success'] == 'password') {
        $success_message = 'Mật khẩu đã được thay đổi thành công!';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'app/models/UsersModel.php';
    $usersModel = new UsersModel();
    
    if (isset($_POST['update_profile'])) {
        // Update profile information
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        
        $updateData = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $result = $usersModel->update($userId, $updateData);
        if ($result) {
            $success_message = 'Thông tin tài khoản đã được cập nhật thành công!';
            // Update session data
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $_SESSION['phone'] = $phone;
            
            // Update $user variable to reflect changes immediately
            $user['name'] = $name;
            $user['email'] = $email;
            $user['phone'] = $phone;
            $user['updated_at'] = date('Y-m-d H:i:s');
            
            // Redirect to avoid POST resubmit warning using JavaScript
            echo '<script>window.location.href = "?page=users&module=account&action=edit&success=1";</script>';
            exit;
        } else {
            $error_message = 'Không thể cập nhật thông tin tài khoản.';
        }
    } elseif (isset($_POST['change_password'])) {
        // Change password
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validate new password
        if (strlen($newPassword) < 6) {
            $error_message = 'Mật khẩu mới phải có ít nhất 6 ký tự.';
        } elseif ($newPassword !== $confirmPassword) {
            $error_message = 'Mật khẩu xác nhận không khớp.';
        } else {
            // Re-check if user has password for verification
            try {
                $currentUser = $usersModel->find($userId);
                if ($currentUser && !empty($currentUser['password'])) {
                    // User has a password - verify current password
                    if (!password_verify($currentPassword, $currentUser['password'])) {
                        // Try MD5 for legacy accounts
                        if (md5($currentPassword) !== $currentUser['password']) {
                            $error_message = 'Mật khẩu hiện tại không đúng.';
                        }
                    }
                }
            } catch (Exception $e) {
                // Database query failed, continue without password verification
                error_log("Password verification query failed: " . $e->getMessage());
            }
            
            // If no error, update password
            if (empty($error_message)) {
                $result = $usersModel->updatePasswordSecure($userId, $newPassword, false);
                if ($result) {
                    // Redirect to prevent POST resubmission using JavaScript
                    echo '<script>window.location.href = "?page=users&module=account&action=edit&success=password";</script>';
                    exit;
                } else {
                    $error_message = 'Không thể thay đổi mật khẩu.';
                }
            }
        }
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
                            <?php if ($hasPassword): ?>
                            <div class="form-group">
                                <label for="current_password" class="form-label required">Mật khẩu hiện tại</label>
                                <div class="password-wrapper">
                                    <input type="password" id="current_password" name="current_password" class="form-control" autocomplete="current-password" required>
                                    <button type="button" class="password-toggle" onclick="toggleAuthPassword('current_password')"
                                            aria-label="Hiển thị mật khẩu" aria-pressed="false" data-label-show="Hiển thị mật khẩu"
                                            data-label-hide="Ẩn mật khẩu">
                                        <span class="password-toggle-icon" id="current-password-icon" aria-hidden="true"></span>
                                    </button>
                                </div>
                            </div>
                            <?php else: ?>
                            <input type="hidden" name="current_password" value="">
                            <?php endif; ?>

                            <div class="form-group">
                                <label for="new_password" class="form-label required">Mật khẩu mới</label>
                                <div class="password-wrapper">
                                    <input type="password" id="new_password" name="new_password" class="form-control" autocomplete="new-password" required>
                                    <button type="button" class="password-toggle" onclick="toggleAuthPassword('new_password')"
                                            aria-label="Hiển thị mật khẩu" aria-pressed="false" data-label-show="Hiển thị mật khẩu"
                                            data-label-hide="Ẩn mật khẩu">
                                        <span class="password-toggle-icon" id="new-password-icon" aria-hidden="true"></span>
                                    </button>
                                </div>
                                <div class="form-text">Mật khẩu phải có ít nhất 6 ký tự</div>
                            </div>

                            <div class="form-group">
                                <label for="confirm_password" class="form-label required">Xác nhận mật khẩu mới</label>
                                <div class="password-wrapper">
                                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" autocomplete="new-password" required>
                                    <button type="button" class="password-toggle" onclick="toggleAuthPassword('confirm_password')"
                                            aria-label="Hiển thị mật khẩu" aria-pressed="false" data-label-show="Hiển thị mật khẩu"
                                            data-label-hide="Ẩn mật khẩu">
                                        <span class="password-toggle-icon" id="confirm-password-icon" aria-hidden="true"></span>
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
</div>

<!-- Include Account JavaScript -->
<script src="assets/js/user_account.js"></script>
<!-- Include Auth JavaScript for password toggle -->
<script src="assets/js/auth.js"></script>

<!-- Include User Sidebar CSS for avatar styling -->
<link rel="stylesheet" href="assets/css/user_sidebar.css">

<!-- Password Toggle Styles -->
<style>
.password-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.password-toggle {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #6b7280;
    cursor: pointer;
    padding: 5px;
    font-size: 14px;
    z-index: 10;
    opacity: 0.7;
    transition: opacity 0.2s;
}

.password-toggle:hover {
    opacity: 1;
    color: #374151;
}

.password-toggle:focus {
    outline: none;
}

.password-toggle-icon {
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    font-size: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 1.1em;
}

.password-toggle-icon::before {
    content: "\f06e";
}

.password-toggle-icon.is-hidden::before {
    content: "\f070";
}
</style>

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

/* Avatar styling - same as sidebar */
.profile-avatar {
    width: 80px;
    height: 80px;
    border-radius: 8px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f3f4f6;
}

.profile-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
</style>