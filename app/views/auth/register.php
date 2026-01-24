<?php
require_once 'auth.php';

// Xử lý đăng ký
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = sanitize($_POST['full_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $password = sanitize($_POST['password'] ?? '');
    $confirmPassword = sanitize($_POST['confirm_password'] ?? '');
    $refCode = sanitize($_POST['ref_code'] ?? '');
    
    // Validation
    if (empty($fullName) || empty($email) || empty($phone) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ thông tin bắt buộc';
    } elseif ($password !== $confirmPassword) {
        $error = 'Mật khẩu xác nhận không khớp';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự';
    } else {
        // Mô phỏng đăng ký - luôn thành công
        if (mockRegister($fullName, $email, $phone, $password, $refCode)) {
            $success = 'Đăng ký thành công! Đang chuyển hướng...';
            // Redirect sau 2 giây
            header("refresh:2;url=../users/dashboard.php");
        } else {
            $error = 'Đăng ký thất bại';
        }
    }
}

// Lấy mã giới thiệu chỉ từ URL (không từ Cookie)
$refCodeFromUrl = getRefCodeFromUrl();
$debugInfo = getDebugInfo();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - ThuongLo.com</title>
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

        .register-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 480px;
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

        .register-title {
            font-size: 24px;
            font-weight: 600;
            color: #1a1a1a;
            text-align: center;
            margin-bottom: 8px;
        }

        .register-subtitle {
            font-size: 14px;
            color: #666;
            text-align: center;
            margin-bottom: 32px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }

        .form-group label .required {
            color: #e74c3c;
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

        .form-control.readonly {
            background: #e9ecef;
            border-color: #28a745;
            color: #155724;
            font-weight: 600;
        }

        .form-control.readonly:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
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

        .ref-code-info {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            padding: 12px;
            margin-top: 8px;
            font-size: 13px;
            color: #155724;
        }

        .ref-code-info .icon {
            color: #28a745;
            margin-right: 5px;
        }

        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-row .form-group {
            flex: 1;
        }

        .social-register {
            margin: 24px 0;
        }

        .social-register-text {
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

        .btn-register {
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

        .btn-register:hover {
            background-color: #094d92;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(10, 102, 194, 0.3);
        }

        .login-link {
            text-align: center;
            font-size: 14px;
            color: #666;
        }

        .login-link a {
            color: #0A66C2;
            text-decoration: none;
            font-weight: 500;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .password-strength {
            margin-top: 5px;
            font-size: 12px;
        }

        .strength-weak { color: #e74c3c; }
        .strength-medium { color: #f39c12; }
        .strength-strong { color: #27ae60; }

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

        .terms-checkbox {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-bottom: 24px;
            font-size: 14px;
            color: #666;
        }

        .terms-checkbox input[type="checkbox"] {
            margin-top: 2px;
            accent-color: #0A66C2;
        }

        .terms-checkbox a {
            color: #0A66C2;
            text-decoration: none;
        }

        .terms-checkbox a:hover {
            text-decoration: underline;
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

        /* Responsive */
        @media (max-width: 480px) {
            body {
                padding: 15px;
                padding-bottom: 80px;
            }

            .register-container {
                padding: 30px 20px;
            }

            .form-row {
                flex-direction: column;
                gap: 0;
            }

            .social-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h1 class="register-title">Tạo tài khoản mới</h1>
        <p class="register-subtitle">Tham gia ThuongLo.com để khám phá nguồn hàng chất lượng</p>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="social-register">
            <div class="social-register-text">Đăng ký nhanh với:</div>
            <div class="social-buttons">
                <button type="button" class="social-btn google" onclick="registerWithGoogle()">
                    <i class="fab fa-google"></i>
                    Google
                </button>
                <button type="button" class="social-btn facebook" onclick="registerWithFacebook()">
                    <i class="fab fa-facebook-f"></i>
                    Facebook
                </button>
            </div>
        </div>
        
        <form method="POST" action="" id="registerForm">
            <div class="form-group">
                <label for="full_name">Họ và tên <span class="required">*</span></label>
                <input type="text" id="full_name" name="full_name" class="form-control" 
                       placeholder="Nhập họ và tên đầy đủ" required 
                       value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email <span class="required">*</span></label>
                    <input type="email" id="email" name="email" class="form-control" 
                           placeholder="Nhập địa chỉ email" required
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone">Số điện thoại <span class="required">*</span></label>
                    <input type="tel" id="phone" name="phone" class="form-control" 
                           placeholder="Nhập số điện thoại" required
                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Mật khẩu <span class="required">*</span></label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" class="form-control" 
                               placeholder="Nhập mật khẩu" required minlength="6">
                        <button type="button" class="password-toggle" onclick="togglePassword('password')">
                            <i class="fas fa-eye" id="password-icon"></i>
                        </button>
                    </div>
                    <div id="passwordStrength" class="password-strength"></div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Xác nhận mật khẩu <span class="required">*</span></label>
                    <div class="password-wrapper">
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                               placeholder="Nhập lại mật khẩu" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                            <i class="fas fa-eye" id="confirm-password-icon"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="ref_code">Mã giới thiệu</label>
                <input type="text" id="ref_code" name="ref_code" class="form-control" 
                       placeholder="Nhập mã giới thiệu (nếu có)"
                       value="<?php echo htmlspecialchars($refCodeFromUrl ?: ''); ?>"
                       <?php echo $refCodeFromUrl ? 'readonly class="form-control readonly"' : 'class="form-control"'; ?>>
                
                <?php if ($refCodeFromUrl): ?>
                    <div class="ref-code-info">
                        <span class="icon">✓</span>
                        Mã giới thiệu đã được tự động điền từ link giới thiệu
                    </div>
                <?php elseif (isset($_COOKIE['ref_code']) && !empty($_COOKIE['ref_code'])): ?>
                    <div class="ref-code-info" style="background: #fff3cd; border-color: #ffeaa7; color: #856404;">
                        <span class="icon">ℹ</span>
                        Bạn có mã giới thiệu đã lưu: <strong><?php echo htmlspecialchars($_COOKIE['ref_code']); ?></strong>
                        <button type="button" onclick="useSavedRefCode()" style="margin-left: 8px; background: none; border: none; color: #0A66C2; cursor: pointer; text-decoration: underline;">
                            Sử dụng
                        </button>
                        <button type="button" onclick="clearSavedRefCode()" style="margin-left: 8px; background: none; border: none; color: #dc3545; cursor: pointer; text-decoration: underline;">
                            Xóa
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <div class="terms-checkbox">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">
                    Tôi đồng ý với <a href="#" target="_blank">Điều khoản sử dụng</a> và 
                    <a href="#" target="_blank">Chính sách bảo mật</a> của ThuongLo.com
                </label>
            </div>
            
            <button type="submit" class="btn-register">Tạo tài khoản</button>
        </form>
        
        <div class="login-link">
            Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a>
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
        function togglePassword(fieldId) {
            const passwordInput = document.getElementById(fieldId);
            const iconId = fieldId === 'password' ? 'password-icon' : 'confirm-password-icon';
            const passwordIcon = document.getElementById(iconId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                passwordIcon.className = 'fas fa-eye';
            }
        }

        function registerWithGoogle() {
            alert('Tính năng đăng ký Google sẽ được tích hợp trong phiên bản tiếp theo');
        }
        
        function registerWithFacebook() {
            alert('Tính năng đăng ký Facebook sẽ được tích hợp trong phiên bản tiếp theo');
        }
        
        // Password strength checker
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthDiv = document.getElementById('passwordStrength');
            
            if (password.length === 0) {
                strengthDiv.textContent = '';
                return;
            }
            
            let strength = 0;
            let feedback = [];
            
            // Length check
            if (password.length >= 8) strength += 1;
            else feedback.push('ít nhất 8 ký tự');
            
            // Uppercase check
            if (/[A-Z]/.test(password)) strength += 1;
            else feedback.push('chữ hoa');
            
            // Lowercase check
            if (/[a-z]/.test(password)) strength += 1;
            else feedback.push('chữ thường');
            
            // Number check
            if (/\d/.test(password)) strength += 1;
            else feedback.push('số');
            
            // Special character check
            if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength += 1;
            else feedback.push('ký tự đặc biệt');
            
            let strengthText = '';
            let strengthClass = '';
            
            if (strength <= 2) {
                strengthText = 'Yếu - Cần: ' + feedback.slice(0, 2).join(', ');
                strengthClass = 'strength-weak';
            } else if (strength <= 3) {
                strengthText = 'Trung bình - Nên thêm: ' + feedback.slice(0, 1).join(', ');
                strengthClass = 'strength-medium';
            } else {
                strengthText = 'Mạnh - Mật khẩu tốt!';
                strengthClass = 'strength-strong';
            }
            
            strengthDiv.textContent = strengthText;
            strengthDiv.className = 'password-strength ' + strengthClass;
        });
        
        // Confirm password validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.setCustomValidity('Mật khẩu xác nhận không khớp');
                this.style.borderColor = '#e74c3c';
            } else {
                this.setCustomValidity('');
                this.style.borderColor = '#e5e5e5';
            }
        });
        
        // Phone number formatting
        document.getElementById('phone').addEventListener('input', function() {
            let value = this.value.replace(/\D/g, ''); // Remove non-digits
            
            // Format as Vietnamese phone number
            if (value.length > 0) {
                if (value.startsWith('84')) {
                    // International format
                    value = '+84 ' + value.substring(2);
                } else if (value.startsWith('0')) {
                    // Domestic format
                    if (value.length > 3) {
                        value = value.substring(0, 4) + ' ' + value.substring(4);
                    }
                    if (value.length > 8) {
                        value = value.substring(0, 9) + ' ' + value.substring(9);
                    }
                }
            }
            
            this.value = value;
        });
        
        // Auto-fill ref code from URL (chỉ khi có parameter ?ref= trong URL)
        window.addEventListener('load', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const refCode = urlParams.get('ref');
            
            // Chỉ tự động điền khi thực sự có ref parameter trong URL
            if (refCode && refCode.trim() !== '') {
                const refInput = document.getElementById('ref_code');
                refInput.value = refCode;
                refInput.classList.add('readonly');
                refInput.readOnly = true;
                
                // Show info message
                if (!document.querySelector('.ref-code-info')) {
                    const infoDiv = document.createElement('div');
                    infoDiv.className = 'ref-code-info';
                    infoDiv.innerHTML = '<span class="icon">✓</span> Mã giới thiệu đã được tự động điền từ link giới thiệu';
                    refInput.parentNode.appendChild(infoDiv);
                }
            }
        });
        
        // Xử lý ref code đã lưu
        function useSavedRefCode() {
            const savedRefCode = '<?php echo htmlspecialchars($_COOKIE['ref_code'] ?? ''); ?>';
            const refInput = document.getElementById('ref_code');
            refInput.value = savedRefCode;
            
            // Ẩn thông báo
            const infoDiv = document.querySelector('.ref-code-info');
            if (infoDiv) {
                infoDiv.style.display = 'none';
            }
        }

        function clearSavedRefCode() {
            if (confirm('Bạn có chắc chắn muốn xóa mã giới thiệu đã lưu?')) {
                // Xóa cookie
                document.cookie = 'ref_code=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
                
                // Ẩn thông báo
                const infoDiv = document.querySelector('.ref-code-info');
                if (infoDiv) {
                    infoDiv.style.display = 'none';
                }
                
                // Clear input nếu đang hiển thị saved ref code
                const refInput = document.getElementById('ref_code');
                if (refInput.value === '<?php echo htmlspecialchars($_COOKIE['ref_code'] ?? ''); ?>') {
                    refInput.value = '';
                }
            }
        }
        
        // Form validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const terms = document.getElementById('terms').checked;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Mật khẩu xác nhận không khớp!');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Mật khẩu phải có ít nhất 6 ký tự!');
                return false;
            }

            if (!terms) {
                e.preventDefault();
                alert('Vui lòng đồng ý với điều khoản sử dụng!');
                return false;
            }
        });

        // Email validation
        document.getElementById('email').addEventListener('blur', function() {
            const email = this.value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (email && !emailRegex.test(email)) {
                this.style.borderColor = '#e74c3c';
                this.setCustomValidity('Email không hợp lệ');
            } else {
                this.style.borderColor = '#e5e5e5';
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>