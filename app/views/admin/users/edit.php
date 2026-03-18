<?php
/**
 * Admin Users Edit
 * Sử dụng AdminService thông qua ServiceManager
 */

// Khởi tạo View & ServiceManager
require_once __DIR__ . '/../../../../core/view_init.php';

// Chọn service admin (được inject từ index.php)
$service = isset($currentService) ? $currentService : ($adminService ?? null);

try {
    // Get user ID from URL
    $user_id = (int)($_GET['id'] ?? 0);
    
    // Get user details using AdminService
    $userData = $service->getUserDetailsData($user_id);
    $user = $userData['user'];
    
    // Redirect if user not found
    if (!$user) {
        header('Location: ?page=admin&module=users&error=not_found');
        exit;
    }
    
} catch (Exception $e) {
    $errorHandler->logError('Admin Users Edit', $e->getMessage());
    header('Location: ?page=admin&module=users&error=system_error');
    exit;
}

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
    
    // Password validation (only if provided)
    if (!empty($password)) {
        if (strlen($password) < 6) {
            $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự';
        }
        
        if ($password !== $confirm_password) {
            $errors[] = 'Xác nhận mật khẩu không khớp';
        }
    }

    // If no errors, update database
    if (empty($errors)) {
        try {
            require_once 'app/models/UsersModel.php';
            $usersModel = new UsersModel();
            
            $updateData = [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
                'role' => $role,
                'status' => $status
            ];
            
            // Only update password if provided
            if (!empty($password)) {
                require_once 'app/services/PasswordHasher.php';
                $passwordHasher = new PasswordHasher();
                $updateData['password'] = $passwordHasher->hash($password);
            }
            
            $updated = $usersModel->update($user_id, $updateData);
            
            if ($updated) {
                // Use PRG pattern - redirect after successful POST
                if (!headers_sent($filename, $linenum)) {
                    header('Location: ?page=admin&module=users&action=view&id=' . $user_id . '&updated=1');
                    exit;
                } else {
                    // Fallback: if headers sent, use JavaScript redirect
                    ?>
                    <script>
                    window.location.href = "?page=admin&module=users&action=view&id=<?= $user_id ?>&updated=1";
                    </script>
                    <div style="padding:20px;text-align:center;">
                        <p>Đang chuyển hướng...</p>
                        <a href="?page=admin&module=users&action=view&id=<?= $user_id ?>&updated=1">Nhấn vào đây nếu không tự chuyển</a>
                    </div>
                    <?php
                    exit;
                }
            } else {
                $errors[] = 'Có lỗi xảy ra khi cập nhật người dùng';
            }
        } catch (Exception $e) {
            $errorHandler->logError('Update User', $e->getMessage());
            $errors[] = 'Có lỗi xảy ra: ' . $e->getMessage();
        }
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