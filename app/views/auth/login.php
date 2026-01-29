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
                setcookie('remember_phone', $phone, time() + (30 * 24 * 60 * 60), '/');
                setcookie('remember_role', $role, time() + (30 * 24 * 60 * 60), '/');
            }
            
            $success = 'Đăng nhập thành công!';
            // Redirect sau 2 giây
            header("refresh:2;url=../users/dashboard.php");
        } else {
            $error = 'Đăng nhập thất bại';
        }
    }
}

// Lấy thông tin debug
$debugInfo = getDebugInfo();
$rememberedPhone = $_COOKIE['remember_phone'] ?? '';
$rememberedRole = $_COOKIE['remember_role'] ?? 'user';
?>
<?php
$segments = explode('/', trim($_SERVER['SCRIPT_NAME'], '/'));
$base = '/' . ($segments[0] ?? '') . '/';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - ThuongLo.com</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $base; ?>assets/css/header.css">
    <link rel="stylesheet" href="<?php echo $base; ?>assets/css/home.css">
    <link rel="stylesheet" href="<?php echo $base; ?>assets/css/footer.css">
    <link rel="stylesheet" href="<?php echo $base; ?>assets/css/auth.css">
</head>
<body>
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
                                window.location.href = '<?php echo $base; ?>users/dashboard.php';
                            }, 2000);
                        </script>
                    <?php endif; ?>

                    <form method="POST" action="" class="auth-form">
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
                                <button type="button" class="password-toggle" onclick="toggleAuthPassword('password')">
                                    <i class="fa-solid fa-eye" id="password-icon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="social-login">
                            <div class="social-login-text">Or login with:</div>
                            <div class="social-buttons">
                                <button type="button" class="social-btn google" onclick="loginWithGoogle()">
                                    <i class="fab fa-google"></i>
                                    Google
                                </button>
                                <button type="button" class="social-btn x" onclick="loginWithX()">
                                    <i class="fab fa-x-twitter"></i>
                                    X.com
                                </button>
                                <button type="button" class="social-btn linkedin" onclick="loginWithLinkedIn()">
                                    <i class="fab fa-linkedin-in"></i>
                                    LinkedIn
                                </button>
                            </div>
                        </div>

                            <div class="remember-forgot">
                                <label class="remember-me">
                                    <input type="checkbox" name="remember_me" <?php echo $rememberedPhone ? 'checked' : ''; ?>>
                                    Remember Me
                                </label>
                                <div class="register-link" style="margin-top: 15px; margin-bottom: 10px;">
                                    <a href="index.php?page=forgot&reset=true">Quên mật khẩu?</a>
                                </div>
                            </div>

                        <button type="submit" class="btn-primary auth-submit-btn">Login</button>

                        <input type="hidden" name="role" id="selected-role" value="<?php echo htmlspecialchars($rememberedRole); ?>">
                    </form>

                    <div class="register-link">
                        Not a member yet? <a href="index.php?page=register">Register now</a>
                    </div>

                    <div class="role-demo">
                        <div class="role-demo-text">Bạn là?</div>
                        <button type="button" class="demo-toggle" onclick="toggleRoleSelector()">
                            Nhấn để truy cập với vai trò:
                        </button>

                        <div class="role-selector hidden" id="role-selector">
                            <div class="role-options">
                                <div class="role-option <?php echo $rememberedRole === 'user' ? 'active' : ''; ?>" onclick="selectRole('user')">
                                    <input type="radio" id="role_user" name="demo_role" value="user" <?php echo $rememberedRole === 'user' ? 'checked' : ''; ?>>
                                    <label for="role_user">Khách hàng</label>
                                </div>
                                <div class="role-option <?php echo $rememberedRole === 'agent' ? 'active' : ''; ?>" onclick="selectRole('agent')">
                                    <input type="radio" id="role_agent" name="demo_role" value="agent" <?php echo $rememberedRole === 'agent' ? 'checked' : ''; ?>>
                                    <label for="role_agent">Đại lý</label>
                                </div>
                                <div class="role-option <?php echo $rememberedRole === 'admin' ? 'active' : ''; ?>" onclick="selectRole('admin')">
                                    <input type="radio" id="role_admin" name="demo_role" value="admin" <?php echo $rememberedRole === 'admin' ? 'checked' : ''; ?>>
                                    <label for="role_admin">Quản trị</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script src="<?php echo $base; ?>assets/js/header.js"></script>
    <script src="<?php echo $base; ?>assets/js/footer.js"></script>
    <script src="<?php echo $base; ?>assets/js/auth.js?v=<?php echo time(); ?>"></script>
</body>
</html>