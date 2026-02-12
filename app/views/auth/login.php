<?php
require_once __DIR__ . '/auth.php';

// Initialize ViewDataService (should already be available from view_init.php)
if (!isset($viewDataService)) {
    require_once __DIR__ . '/../../core/view_init.php';
}

// Xử lý đăng nhập
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = sanitize($_POST['phone'] ?? '');
    $password = sanitize($_POST['password'] ?? '');
    $remember = isset($_POST['remember_me']);
    
    if (empty($phone) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ thông tin';
    } else {
        // Đăng nhập thực tế với database
        $user = authenticateUser($phone, $password);
        
        if ($user) {
            // Kiểm tra trạng thái tài khoản
            if ($user['status'] !== 'active') {
                $error = 'Tài khoản của bạn đã bị khóa hoặc chưa được kích hoạt';
            } else {
                // Xử lý Remember Me
                if ($remember) {
                    if (!headers_sent()) {
                        setcookie('remember_phone', $phone, time() + (30 * 24 * 60 * 60), '/');
                        setcookie('remember_role', $user['role'], time() + (30 * 24 * 60 * 60), '/');
                    } else {
                        $_SESSION['remember_phone'] = $phone;
                        $_SESSION['remember_role'] = $user['role'];
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
            }
        } else {
            $error = 'Tên đăng nhập hoặc mật khẩu không đúng';
        }
    }
}

// Get view data
$viewData = $viewDataService->getAuthLoginData();
$rememberedPhone = $viewData['remembered_phone'];
$rememberedRole = $viewData['remembered_role'];
?>

<main class="page-content">
    <section class="auth-section login-page">
        <div class="container">
            <h1 class="page-title-main"><?php echo $viewData['page_title']; ?></h1>

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

                <form method="POST" action="<?php echo $viewData['form_action']; ?>" class="auth-form">
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
                </form>

                <div class="register-link">
                    Not a member yet? <a href="<?php echo page_url('register'); ?>">Register now</a>
                </div>
            </div>
        </div>
    </section>
</main>