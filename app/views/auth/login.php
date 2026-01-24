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
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - ThuongLo.com</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            padding-bottom: 80px;
        }

        .login-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 420px;
            padding: 40px;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-title {
            font-size: 24px;
            font-weight: 600;
            color: #1a1a1a;
            text-align: center;
            margin-bottom: 32px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-control {
            width: 100%;
            padding: 16px;
            border: 2px solid #e5e5e5;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: white;
            color: #333;
        }

        .form-control::placeholder {
            color: #999;
        }

        .form-control:focus {
            outline: none;
            border-color: #0A66C2;
            box-shadow: 0 0 0 3px rgba(10, 102, 194, 0.1);
        }

        .password-wrapper {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #999;
            cursor: pointer;
            font-size: 18px;
            padding: 0;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .password-toggle:hover {
            color: #666;
        }

        .social-login {
            margin: 24px 0;
        }

        .social-login-text {
            text-align: center;
            color: #666;
            font-size: 14px;
            margin-bottom: 16px;
        }

        .social-buttons {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
        }

        .social-btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .social-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .social-btn.google {
            background-color: #EF4444;
            color: white;
        }

        .social-btn.facebook {
            background-color: #0A66C2;
            color: white;
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #666;
        }

        .remember-me input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #0A66C2;
        }

        .forgot-password {
            color: #0A66C2;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        .btn-login {
            width: 100%;
            background-color: #0A66C2;
            color: white;
            border: none;
            padding: 16px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 24px;
        }

        .btn-login:hover {
            background-color: #094d92;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(10, 102, 194, 0.3);
        }

        .register-link {
            text-align: center;
            font-size: 14px;
            color: #666;
        }

        .register-link a {
            color: #0A66C2;
            text-decoration: none;
            font-weight: 500;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        .role-demo {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #e5e5e5;
        }

        .role-demo-text {
            color: #666;
            font-size: 14px;
            margin-bottom: 12px;
        }

        .demo-toggle {
            background: none;
            border: 2px solid #0A66C2;
            color: #0A66C2;
            padding: 8px 16px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .demo-toggle:hover {
            background: #0A66C2;
            color: white;
        }

        .role-selector {
            margin-top: 16px;
            padding: 16px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e5e5e5;
        }

        .role-options {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .role-option {
            display: flex;
            align-items: center;
            padding: 8px 12px;
            background: white;
            border-radius: 6px;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .role-option:hover {
            border-color: #0A66C2;
            background: #f0f8ff;
        }

        .role-option.active {
            border-color: #0A66C2;
            background: #f0f8ff;
        }

        .role-option input[type="radio"] {
            margin-right: 8px;
            accent-color: #0A66C2;
        }

        .role-option label {
            margin: 0;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
        }

        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }

        .alert-success {
            background: #efe;
            color: #363;
            border: 1px solid #cfc;
        }

        /* Debug Console */
        .debug-console {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #1a1a1a;
            color: #00ff00;
            padding: 10px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            z-index: 1000;
            border-top: 2px solid #333;
        }

        .debug-console .debug-item {
            display: inline-block;
            margin-right: 20px;
            padding: 2px 8px;
            background: #333;
            border-radius: 3px;
        }

        .debug-console .debug-label {
            color: #ffff00;
            font-weight: bold;
        }

        .hidden {
            display: none !important;
        }

        /* Responsive */
        @media (max-width: 480px) {
            body {
                padding: 15px;
                padding-bottom: 80px;
            }

            .login-container {
                padding: 30px 20px;
            }

            .social-buttons {
                flex-direction: column;
            }

            .remember-forgot {
                flex-direction: column;
                gap: 12px;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1 class="login-title">Đăng nhập với tài khoản của bạn</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?> Đang chuyển hướng...</div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <input type="tel" id="phone" name="phone" class="form-control" 
                       placeholder="Số điện thoại hoặc email" required
                       value="<?php echo htmlspecialchars($rememberedPhone); ?>">
            </div>
            
            <div class="form-group">
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" class="form-control" 
                           placeholder="Mật khẩu" required>
                    <button type="button" class="password-toggle" onclick="togglePassword()">
                        <i class="fas fa-eye" id="password-icon"></i>
                    </button>
                </div>
            </div>
            
            <div class="social-login">
                <div class="social-login-text">Hoặc đăng nhập với:</div>
                <div class="social-buttons">
                    <button type="button" class="social-btn google" onclick="loginWithGoogle()">
                        <i class="fab fa-google"></i>
                        Google
                    </button>
                    <button type="button" class="social-btn facebook" onclick="loginWithFacebook()">
                        <i class="fab fa-facebook-f"></i>
                        Facebook
                    </button>
                </div>
            </div>
            
            <div class="remember-forgot">
                <label class="remember-me">
                    <input type="checkbox" name="remember_me" <?php echo $rememberedPhone ? 'checked' : ''; ?>>
                    Ghi nhớ tài khoản
                </label>
                <a href="forgot.php" class="forgot-password">Quên mật khẩu?</a>
            </div>
            
            <button type="submit" class="btn-login">Đăng nhập</button>
            
            <input type="hidden" name="role" id="selected-role" value="<?php echo htmlspecialchars($rememberedRole); ?>">
        </form>
        
        <div class="register-link">
            Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a>
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

    <!-- Debug Console -->
    <div class="debug-console">
        <span class="debug-item">
            <span class="debug-label">Status:</span> <?php echo $debugInfo['status']; ?>
        </span>
        <span class="debug-item">
            <span class="debug-label">Role:</span> <?php echo $debugInfo['role']; ?>
        </span>
        <span class="debug-item">
            <span class="debug-label">Ref Code:</span> <?php echo $debugInfo['ref_code']; ?>
        </span>
        <span class="debug-item">
            <span class="debug-label">Security:</span> <?php echo $debugInfo['security_alert']; ?>
        </span>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const passwordIcon = document.getElementById('password-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                passwordIcon.className = 'fas fa-eye';
            }
        }
        
        function toggleRoleSelector() {
            const roleSelector = document.getElementById('role-selector');
            const toggleBtn = document.querySelector('.demo-toggle');
            
            if (roleSelector.classList.contains('hidden')) {
                roleSelector.classList.remove('hidden');
                toggleBtn.textContent = 'Ẩn lựa chọn vai trò';
                toggleBtn.style.background = '#0A66C2';
                toggleBtn.style.color = 'white';
            } else {
                roleSelector.classList.add('hidden');
                toggleBtn.textContent = 'Nhấn để truy cập Demo Account';
                toggleBtn.style.background = 'none';
                toggleBtn.style.color = '#0A66C2';
            }
        }
        
        function selectRole(role) {
            // Remove active class from all options
            document.querySelectorAll('.role-option').forEach(option => {
                option.classList.remove('active');
            });
            
            // Add active class to selected option
            const selectedOption = document.querySelector(`input[value="${role}"]`).closest('.role-option');
            selectedOption.classList.add('active');
            
            // Check the radio button
            document.querySelector(`input[value="${role}"]`).checked = true;
            
            // Update hidden input
            document.getElementById('selected-role').value = role;
        }
        
        function loginWithGoogle() {
            alert('Tính năng đăng nhập Google sẽ được tích hợp trong phiên bản tiếp theo');
        }
        
        function loginWithFacebook() {
            alert('Tính năng đăng nhập Facebook sẽ được tích hợp trong phiên bản tiếp theo');
        }
        
        // Auto-detect admin login
        document.getElementById('phone').addEventListener('input', function() {
            if (this.value.toLowerCase() === 'admin') {
                selectRole('admin');
                
                // Show role selector if hidden
                const roleSelector = document.getElementById('role-selector');
                if (roleSelector.classList.contains('hidden')) {
                    toggleRoleSelector();
                }
            }
        });
        
        // Initialize role from remembered value
        window.addEventListener('load', function() {
            const rememberedRole = '<?php echo $rememberedRole; ?>';
            if (rememberedRole) {
                selectRole(rememberedRole);
            }
        });
    </script>
</body>
</html>