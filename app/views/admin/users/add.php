<?php
// Handle form submission (demo)
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
    
    if (empty($password)) {
        $errors[] = 'Mật khẩu là bắt buộc';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Xác nhận mật khẩu không khớp';
    }

    // If no errors, simulate save
    if (empty($errors)) {
        $success = true;
        // In real app: save to database
        // Redirect after successful save
        // header('Location: ?page=admin&module=users&success=added');
        // exit;
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

    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <span>Thêm người dùng thành công! (Demo)</span>
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

                    <!-- Avatar Upload -->
                    <div class="form-section">
                        <h3 class="section-title">Ảnh Đại Diện</h3>
                        
                        <div class="form-group">
                            <label for="avatar">Chọn ảnh đại diện</label>
                            <div class="image-upload-container">
                                <div class="image-preview" id="avatarPreview">
                                    <i class="fas fa-user"></i>
                                    <p>Chọn ảnh đại diện</p>
                                </div>
                                <input type="file" id="avatar" name="avatar" class="image-input" 
                                       accept="image/*">
                                <div class="image-upload-info">
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

                    <!-- Additional Settings -->
                    <div class="form-section">
                        <h3 class="section-title">Cài Đặt Bổ Sung</h3>
                        
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="send_welcome_email" value="1" checked>
                                Gửi email chào mừng
                            </label>
                            <small>Gửi email thông báo tài khoản đã được tạo</small>
                        </div>

                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="require_password_change" value="1">
                                Yêu cầu đổi mật khẩu lần đầu đăng nhập
                            </label>
                            <small>Người dùng phải đổi mật khẩu khi đăng nhập lần đầu</small>
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