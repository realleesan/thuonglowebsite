<?php
require_once 'auth.php';

// Xử lý đăng nhập
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = sanitize($_POST['phone'] ?? '');
    $password = sanitize($_POST['password'] ?? '');
    $role = sanitize($_POST['role'] ?? 'user');
    $remember = isset($_POST['remember_me']);
    
    if (empty($phone) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ thông tin';
    } else {
        // Mô phỏng đăng nhập - luôn thành công
        if (mockLogin($phone, $password, $role)) {
            // Xử lý Remember Me
            if ($remember) {
                if (!headers_sent()) {
                    setcookie('remember_phone', $phone, time() + (30 * 24 * 60 * 60), '/');
                    setcookie('remember_role', $role, time() + (30 * 24 * 60 * 60), '/');
                } else {
                    $_SESSION['remember_phone'] = $phone;
                    $_SESSION['remember_role'] = $role;
                }
            } else {
                if (!headers_sent()) {
                    setcookie('remember_phone', '', time() - 3600, '/');
                    setcookie('remember_role', '', time() - 3600, '/');
                } else {
                    unset($_SESSION['remember_phone'], $_SESSION['remember_role']);
                }
            }
            
            $success = 'Đăng nhập thành công!';
            
            // Chuyển hướng đến dashboard tương ứng
            $dashboardUrl = $_SESSION['dashboard_url'] ?? '?page=users&module=dashboard';
            
            // Chuyển hướng ngay lập tức với JavaScript
            echo '<script>
                setTimeout(function() {
                    window.location.href = "' . $dashboardUrl . '";
                }, 1000);
            </script>';
            
            // Dừng xử lý để tránh hiển thị form lại
            exit;
        } else {
            $error = 'Đăng nhập thất bại';
        }
    }
}

// Lấy thông tin debug
$debugInfo = getDebugInfo();
$rememberedPhone = $_SESSION['remember_phone'] ?? ($_COOKIE['remember_phone'] ?? '');
$rememberedRole = $_SESSION['remember_role'] ?? ($_COOKIE['remember_role'] ?? 'user');
?>

<main class="page-content">
    <section class="auth-section login-page">
        <div class="container">
            <h1 class="page-title-main">Account</h1>

            <div class="auth-panel">
                <h2 class="auth-heading">Đăng nhập</h2>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?> Đang chuyển hướng...</div>
                    <script>
                        setTimeout(function() {
                            window.location.href = '<?php echo page_url('home'); ?>';
                        }, 2000);
                    </script>
                <?php endif; ?>

                <form method="POST" action="<?php echo form_url(); ?>" class="auth-form">
                    <div class="form-group">
                        <label for="phone" class="form-label">Tên đăng nhập hoặc email</label>
                        <input type="tel" id="phone" name="phone" class="form-control"
                               placeholder="Username or email" required
                               value="<?php echo htmlspecialchars($rememberedPhone); ?>">
                    </div>
        

                    <div class="form-group">
                        <label for="password" class="form-label">Mật khẩu</label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" class="form-control"
                                   placeholder="Password" required>
                            <button type="button" class="password-toggle" onclick="toggleAuthPassword('password')"
                                    aria-label="Hiển thị mật khẩu" aria-pressed="false" data-label-show="Hiển thị mật khẩu"
                                    data-label-hide="Ẩn mật khẩu">
                                <span class="password-toggle-icon" id="password-icon" aria-hidden="true"></span>
                            </button>
                        </div>
                    </div>

                    <div class="remember-forgot">
                        <label class="remember-me">
                            <input type="checkbox" name="remember_me" <?php echo $rememberedPhone ? 'checked' : ''; ?>>
                            Remember Me
                        </label>
                        <div class="register-link" style="margin-top: 15px; margin-bottom: 10px;">
                            <a href="<?php echo page_url('forgot', ['reset' => 'true']); ?>">Quên mật khẩu?</a>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary auth-submit-btn">Đăng nhập</button>

                    <input type="hidden" name="role" id="selected-role" value="<?php echo htmlspecialchars($rememberedRole); ?>">
                </form>

                <div class="register-link">
                    Not a member yet? <a href="<?php echo page_url('register'); ?>">Register now</a>
                </div>

                <div class="role-demo">
                    <div class="role-demo-text">Demo Account - Chọn vai trò để test giao diện</div>
                    <button type="button" class="demo-toggle" onclick="toggleRoleSelector()">
                        Nhấn để chọn tài khoản demo
                    </button>

                    <div class="role-selector hidden" id="role-selector">
                        <div class="role-options">
                            <div class="role-option <?php echo $rememberedRole === 'user' ? 'active' : ''; ?>" onclick="selectRole('user')">
                                <input type="radio" id="role_user" name="demo_role" value="user" <?php echo $rememberedRole === 'user' ? 'checked' : ''; ?>>
                                <label for="role_user">
                                    <strong>Khách hàng</strong><br>
                                    <small>SĐT: 0901234567 | MK: 123456</small>
                                </label>
                            </div>
                            <div class="role-option <?php echo $rememberedRole === 'agent' ? 'active' : ''; ?>" onclick="selectRole('agent')">
                                <input type="radio" id="role_agent" name="demo_role" value="agent" <?php echo $rememberedRole === 'agent' ? 'checked' : ''; ?>>
                                <label for="role_agent">
                                    <strong>Đại lý</strong><br>
                                    <small>SĐT: 0907654321 | MK: 123456</small>
                                </label>
                            </div>
                            <div class="role-option <?php echo $rememberedRole === 'admin' ? 'active' : ''; ?>" onclick="selectRole('admin')">
                                <input type="radio" id="role_admin" name="demo_role" value="admin" <?php echo $rememberedRole === 'admin' ? 'checked' : ''; ?>>
                                <label for="role_admin">
                                    <strong>Quản trị viên</strong><br>
                                    <small>TK: admin | MK: admin123</small>
                                </label>
                            </div>
                        </div>
                        <div class="demo-instructions">
                            <p><strong>Hướng dẫn:</strong></p>
                            <p>1. Chọn vai trò → Thông tin sẽ tự động điền</p>
                            <p>2. Nhấn "Đăng nhập" → Chuyển đến dashboard tương ứng</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>