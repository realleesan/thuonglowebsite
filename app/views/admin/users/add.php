<?php
/**
 * Admin Users Add
 * Sử dụng AdminService thông qua ServiceManager
 */

// Khởi tạo View & ServiceManager
require_once __DIR__ . '/../../../../core/view_init.php';

// Chọn service admin (được inject từ index.php)
$service = isset($currentService) ? $currentService : ($adminService ?? null);

// Handle form submission
$errors = [];

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
    
    if (empty($password)) {
        $errors[] = 'Mật khẩu là bắt buộc';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Xác nhận mật khẩu không khớp';
    }

    // If no errors, save to database
    if (empty($errors)) {
        try {
            require_once 'app/models/UsersModel.php';
            $usersModel = new UsersModel();
            
            // Check if email already exists
            if ($usersModel->emailExists($email)) {
                $errors[] = 'Email đã tồn tại trong hệ thống';
            } else {
                // Check if phone already exists
                if (!empty($phone) && $usersModel->phoneExists($phone)) {
                    $errors[] = 'Số điện thoại đã tồn tại trong hệ thống';
                } else {
                    // Hash password
                    require_once 'app/services/PasswordHasher.php';
                    $passwordHasher = new PasswordHasher();
                    
                    $userData = [
                        'name' => $name,
                        'email' => $email,
                        'phone' => $phone,
                        'address' => $address,
                        'role' => $role,
                        'status' => $status,
                        'password' => $passwordHasher->hash($password)
                    ];
                    
                    $created = $usersModel->create($userData);
                    
                    if ($created) {
                        // Use PRG pattern - redirect after successful POST
                        if (!headers_sent($filename, $linenum)) {
                            header('Location: ?page=admin&module=users&added=1');
                            exit;
                        } else {
                            // Fallback: if headers sent, use JavaScript redirect
                            ?>
                            <script>
                            window.location.href = "?page=admin&module=users&added=1";
                            </script>
                            <div style="padding:20px;text-align:center;">
                                <p>Đang chuyển hướng...</p>
                                <a href="?page=admin&module=users&added=1">Nhấn vào đây nếu không tự chuyển</a>
                            </div>
                            <?php
                            exit;
                        }
                    } else {
                        $errors[] = 'Có lỗi xảy ra khi tạo người dùng';
                    }
                }
            }
        } catch (Exception $e) {
            $errorHandler->logError('Add User', $e->getMessage());
            $errors[] = 'Có lỗi xảy ra: ' . $e->getMessage();
        }
    }
}
?>

<div class="users-add-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-user-plus"></i>
                Thêm Người Dùng Mới
            </h1>
            <p class="page-description">Tạo tài khoản người dùng mới trong hệ thống</p>
        </div>
        <div class="page-header-right">
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
                                <small>Quyền hạn của người dùng trong hệ thống</small>
                            </div>

                            <div class="form-group">
                                <label for="status" class="required">Trạng thái</label>
                                <select id="status" name="status" required>
                                    <option value="">Chọn trạng thái</option>
                                    <option value="active" <?= ($_POST['status'] ?? 'active') == 'active' ? 'selected' : '' ?>>
                                        Hoạt động
                                    </option>
                                    <option value="inactive" <?= ($_POST['status'] ?? '') == 'inactive' ? 'selected' : '' ?>>
                                        Không hoạt động
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password" class="required">Mật khẩu</label>
                            <input type="password" id="password" name="password" required 
                                   placeholder="Nhập mật khẩu">
                            <small>Mật khẩu phải có ít nhất 6 ký tự</small>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password" class="required">Xác nhận mật khẩu</label>
                            <input type="password" id="confirm_password" name="confirm_password" required 
                                   placeholder="Nhập lại mật khẩu">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Tạo Người Dùng
                </button>
                <button type="button" class="btn btn-secondary" onclick="resetForm()">
                    <i class="fas fa-undo"></i>
                    Đặt lại
                </button>
                <a href="?page=admin&module=users" class="btn btn-outline">
                    <i class="fas fa-times"></i>
                    Hủy
                </a>
            </div>
        </form>
    </div>
</div>