<?php
require_once 'auth.php';

// Xử lý quên mật khẩu
$error = '';
$success = '';
$step = 'input'; // input, verify, reset

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'send_code';
    
    if ($action === 'change_contact') {
        // Xóa thông tin reset cũ để cho phép nhập lại
        unset($_SESSION['reset_code'], $_SESSION['reset_contact'], $_SESSION['reset_type'], $_SESSION['reset_expires']);
        $step = 'input';
        $success = 'Bạn có thể nhập số điện thoại hoặc email khác.';
    } elseif ($action === 'send_code') {
        $contact = sanitize($_POST['contact'] ?? '');
        
        if (empty($contact)) {
            $error = 'Vui lòng nhập số điện thoại hoặc email';
        } else {
            // Xác định loại contact (email hoặc phone)
            $isEmail = filter_var($contact, FILTER_VALIDATE_EMAIL);
            
            if ($isEmail) {
                // Gửi email reset password
                $resetCode = rand(100000, 999999);
                $_SESSION['reset_code'] = $resetCode;
                $_SESSION['reset_contact'] = $contact;
                $_SESSION['reset_type'] = 'email';
                $_SESSION['reset_expires'] = time() + 600; // 10 phút
                
                // Mô phỏng gửi email
                if (sendResetEmail($contact, $resetCode)) {
                    $success = 'Mã xác thực đã được gửi đến email của bạn. Vui lòng kiểm tra hộp thư.';
                    $step = 'verify';
                } else {
                    $error = 'Không thể gửi email. Vui lòng thử lại sau.';
                }
            } else {
                // Kiểm tra format số điện thoại
                $phone = preg_replace('/[^0-9]/', '', $contact);
                if (strlen($phone) >= 10) {
                    // Gửi SMS
                    $resetCode = rand(100000, 999999);
                    $_SESSION['reset_code'] = $resetCode;
                    $_SESSION['reset_contact'] = $phone;
                    $_SESSION['reset_type'] = 'sms';
                    $_SESSION['reset_expires'] = time() + 600; // 10 phút
                    
                    // Mô phỏng gửi SMS
                    if (sendResetSMS($phone, $resetCode)) {
                        $success = 'Mã xác thực đã được gửi đến số điện thoại của bạn qua SMS.';
                        $step = 'verify';
                    } else {
                        $error = 'Không thể gửi SMS. Vui lòng thử lại sau.';
                    }
                } else {
                    $error = 'Số điện thoại không hợp lệ';
                }
            }
        }
    } elseif ($action === 'verify_code') {
        $inputCode = sanitize($_POST['verification_code'] ?? '');
        
        if (empty($inputCode)) {
            $error = 'Vui lòng nhập mã xác thực';
        } elseif (!isset($_SESSION['reset_code']) || time() > $_SESSION['reset_expires']) {
            $error = 'Mã xác thực đã hết hạn. Vui lòng yêu cầu mã mới.';
            $step = 'input';
        } elseif ($inputCode != $_SESSION['reset_code']) {
            $error = 'Mã xác thực không đúng';
            $step = 'verify';
        } else {
            $success = 'Xác thực thành công! Bạn có thể đặt lại mật khẩu.';
            $step = 'reset';
        }
    } elseif ($action === 'reset_password') {
        $newPassword = sanitize($_POST['new_password'] ?? '');
        $confirmPassword = sanitize($_POST['confirm_password'] ?? '');
        
        if (empty($newPassword) || empty($confirmPassword)) {
            $error = 'Vui lòng nhập đầy đủ thông tin';
            $step = 'reset';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'Mật khẩu xác nhận không khớp';
            $step = 'reset';
        } elseif (strlen($newPassword) < 6) {
            $error = 'Mật khẩu phải có ít nhất 6 ký tự';
            $step = 'reset';
        } else {
            // Mô phỏng reset password thành công
            unset($_SESSION['reset_code'], $_SESSION['reset_contact'], $_SESSION['reset_type'], $_SESSION['reset_expires']);
            $success = 'Đặt lại mật khẩu thành công! Bạn có thể đăng nhập với mật khẩu mới.';
            // Redirect về login sau 3 giây
            header("refresh:3;url=login.php");
        }
    }
}

// Kiểm tra step từ session
if (isset($_SESSION['reset_code']) && time() <= $_SESSION['reset_expires']) {
    if ($step === 'input') {
        $step = 'verify';
    }
}

$debugInfo = getDebugInfo();

/**
 * Mô phỏng gửi email reset password
 */
function sendResetEmail($email, $code) {
    // Trong thực tế sẽ sử dụng PHPMailer
    // Hiện tại chỉ mô phỏng
    logSecurityEvent('password_reset_email_sent', 'guest', "Email: $email, Code: $code");
    return true; // Giả lập gửi thành công
}

/**
 * Mô phỏng gửi SMS reset password
 */
function sendResetSMS($phone, $code) {
    // Trong thực tế sẽ tích hợp SMS API
    // Hiện tại chỉ mô phỏng
    logSecurityEvent('password_reset_sms_sent', 'guest', "Phone: $phone, Code: $code");
    return true; // Giả lập gửi thành công
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu - ThuongLo.com</title>
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

        .forgot-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 450px;
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

        .page-header {
            margin-bottom: 32px;
        }

        .breadcrumb {
            font-size: 18px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 24px;
        }

        .forgot-title {
            font-size: 24px;
            font-weight: 600;
            color: #1a1a1a;
            text-align: center;
            margin-bottom: 16px;
        }

        .forgot-subtitle {
            font-size: 14px;
            color: #666;
            text-align: center;
            line-height: 1.5;
            margin-bottom: 32px;
        }

        .form-group {
            margin-bottom: 24px;
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
            border-color: #4285f4;
            box-shadow: 0 0 0 3px rgba(66, 133, 244, 0.1);
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

        .btn-primary {
            width: 100%;
            background-color: #4285f4;
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

        .btn-primary:hover {
            background-color: #3367d6;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(66, 133, 244, 0.3);
        }

        .btn-secondary {
            width: 100%;
            background: none;
            color: #4285f4;
            border: 2px solid #4285f4;
            padding: 14px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background-color: #4285f4;
            color: white;
        }

        .back-link {
            text-align: center;
            font-size: 14px;
            color: #666;
        }

        .back-link a {
            color: #4285f4;
            text-decoration: none;
            font-weight: 500;
        }

        .back-link a:hover {
            text-decoration: underline;
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

        .verification-info {
            background: #f0f8ff;
            border: 1px solid #4285f4;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
            font-size: 14px;
            color: #1a1a1a;
        }

        .verification-info .icon {
            color: #4285f4;
            margin-right: 8px;
        }

        .code-input {
            text-align: center;
            font-size: 24px;
            font-weight: 600;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
        }

        .resend-code {
            text-align: center;
            margin-top: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        .resend-code button {
            background: none;
            border: none;
            color: #4285f4;
            cursor: pointer;
            font-size: 14px;
            text-decoration: underline;
            padding: 4px 8px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .resend-code button:hover {
            color: #3367d6;
            background: rgba(66, 133, 244, 0.1);
        }

        .separator {
            color: #ccc;
            font-size: 14px;
        }

        .change-contact-btn {
            background: none !important;
            border: none !important;
            color: #4285f4 !important;
            cursor: pointer !important;
            font-size: 13px !important;
            text-decoration: underline !important;
            padding: 4px 8px !important;
            border-radius: 4px !important;
            transition: all 0.3s ease !important;
        }

        .change-contact-btn:hover {
            color: #3367d6 !important;
            background: rgba(66, 133, 244, 0.1) !important;
        }

        .input-hint {
            font-size: 12px;
            margin-top: 6px;
            min-height: 16px;
            transition: all 0.3s ease;
        }

        .input-hint.email {
            color: #28a745;
        }

        .input-hint.phone {
            color: #17a2b8;
        }

        .input-hint.invalid {
            color: #e74c3c;
        }

        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 32px;
        }

        .step {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #e5e5e5;
            color: #999;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
            margin: 0 8px;
            position: relative;
        }

        .step.active {
            background: #4285f4;
            color: white;
        }

        .step.completed {
            background: #28a745;
            color: white;
        }

        .step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 100%;
            width: 24px;
            height: 2px;
            background: #e5e5e5;
            transform: translateY(-50%);
        }

        .step.completed:not(:last-child)::after {
            background: #28a745;
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

            .forgot-container {
                padding: 30px 20px;
            }

            .step-indicator {
                margin-bottom: 24px;
            }

            .step {
                width: 28px;
                height: 28px;
                font-size: 12px;
            }

            .resend-code {
                flex-direction: column;
                gap: 12px;
            }

            .separator {
                display: none;
            }

            .verification-info {
                font-size: 13px;
            }

            .change-contact-btn {
                font-size: 12px !important;
            }
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="page-header">
            <div class="breadcrumb">Tài khoản</div>
            
            <!-- Step Indicator -->
            <div class="step-indicator">
                <div class="step <?php echo $step === 'input' ? 'active' : ($step !== 'input' ? 'completed' : ''); ?>">1</div>
                <div class="step <?php echo $step === 'verify' ? 'active' : ($step === 'reset' ? 'completed' : ''); ?>">2</div>
                <div class="step <?php echo $step === 'reset' ? 'active' : ''; ?>">3</div>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if ($step === 'input'): ?>
            <!-- Bước 1: Nhập thông tin liên hệ -->
            <h1 class="forgot-title">Lấy lại mật khẩu</h1>
            <p class="forgot-subtitle">
                Quên mật khẩu? Vui lòng nhập số điện thoại hoặc địa chỉ email của bạn. 
                Bạn sẽ nhận được mã xác thực để tạo mật khẩu mới.
            </p>
            
            <form method="POST" action="">
                <input type="hidden" name="action" value="send_code">
                
                <div class="form-group">
                    <input type="text" id="contact" name="contact" class="form-control" 
                           placeholder="Số điện thoại hoặc email" required
                           value="<?php echo htmlspecialchars($_POST['contact'] ?? ''); ?>">
                    <div id="contact-hint" class="input-hint"></div>
                </div>
                
                <button type="submit" class="btn-primary">Gửi mã xác thực</button>
            </form>

        <?php elseif ($step === 'verify'): ?>
            <!-- Bước 2: Xác thực mã -->
            <h1 class="forgot-title">Xác thực mã</h1>
            <p class="forgot-subtitle">
                Nhập mã xác thực <?php echo $_SESSION['reset_type'] === 'email' ? 'đã được gửi đến email' : 'đã được gửi qua SMS'; ?> của bạn.
            </p>

            <div class="verification-info">
                <i class="fas fa-info-circle icon"></i>
                Mã xác thực đã được gửi đến: 
                <strong>
                    <?php 
                    $contact = $_SESSION['reset_contact'] ?? '';
                    if ($_SESSION['reset_type'] === 'email') {
                        echo substr($contact, 0, 3) . '***@' . substr(strrchr($contact, '@'), 1);
                    } else {
                        echo substr($contact, 0, 3) . '***' . substr($contact, -3);
                    }
                    ?>
                </strong>
                <div style="margin-top: 8px;">
                    <form method="POST" action="" style="display: inline;">
                        <input type="hidden" name="action" value="change_contact">
                        <button type="submit" class="change-contact-btn">
                            Sử dụng số điện thoại/email khác
                        </button>
                    </form>
                </div>
            </div>
            
            <form method="POST" action="">
                <input type="hidden" name="action" value="verify_code">
                
                <div class="form-group">
                    <input type="text" id="verification_code" name="verification_code" class="form-control code-input" 
                           placeholder="000000" required maxlength="6" pattern="[0-9]{6}">
                </div>
                
                <button type="submit" class="btn-primary">Xác thực</button>
                
                <div class="resend-code">
                    <button type="button" onclick="resendCode()">Gửi lại mã xác thực</button>
                    <span class="separator">|</span>
                    <form method="POST" action="" style="display: inline;">
                        <input type="hidden" name="action" value="change_contact">
                        <button type="submit" class="change-contact-btn">
                            Thay đổi số điện thoại/email
                        </button>
                    </form>
                </div>
            </form>

        <?php elseif ($step === 'reset'): ?>
            <!-- Bước 3: Đặt lại mật khẩu -->
            <h1 class="forgot-title">Đặt lại mật khẩu</h1>
            <p class="forgot-subtitle">
                Nhập mật khẩu mới cho tài khoản của bạn.
            </p>
            
            <form method="POST" action="" id="resetForm">
                <input type="hidden" name="action" value="reset_password">
                
                <div class="form-group">
                    <div class="password-wrapper">
                        <input type="password" id="new_password" name="new_password" class="form-control" 
                               placeholder="Mật khẩu mới" required minlength="6">
                        <button type="button" class="password-toggle" onclick="togglePassword('new_password')">
                            <i class="fas fa-eye" id="new-password-icon"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="password-wrapper">
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                               placeholder="Xác nhận mật khẩu mới" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                            <i class="fas fa-eye" id="confirm-password-icon"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn-primary">Đặt lại mật khẩu</button>
            </form>
        <?php endif; ?>

        <div class="back-link">
            <a href="login.php">← Quay lại đăng nhập</a>
        </div>
    </div>

    <!-- Debug Console -->
    <div class="debug-console">
        <span class="debug-item">
            <span class="debug-label">Status:</span> <?php echo $debugInfo['status']; ?>
        </span>
        <span class="debug-item">
            <span class="debug-label">Step:</span> <?php echo $step; ?>
        </span>
        <span class="debug-item">
            <span class="debug-label">Reset Type:</span> <?php echo $_SESSION['reset_type'] ?? 'N/A'; ?>
        </span>
        <span class="debug-item">
            <span class="debug-label">Code:</span> <?php echo $_SESSION['reset_code'] ?? 'N/A'; ?>
        </span>
    </div>

    <script>
        function togglePassword(fieldId) {
            const passwordInput = document.getElementById(fieldId);
            const iconId = fieldId === 'new_password' ? 'new-password-icon' : 'confirm-password-icon';
            const passwordIcon = document.getElementById(iconId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                passwordIcon.className = 'fas fa-eye';
            }
        }

        function resendCode() {
            if (confirm('Bạn có muốn gửi lại mã xác thực?')) {
                // Tạo form ẩn để gửi lại mã
                const form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'send_code';
                
                const contactInput = document.createElement('input');
                contactInput.type = 'hidden';
                contactInput.name = 'contact';
                contactInput.value = '<?php echo $_SESSION['reset_contact'] ?? ''; ?>';
                
                form.appendChild(actionInput);
                form.appendChild(contactInput);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Auto-format verification code input
        document.getElementById('verification_code')?.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').substring(0, 6);
        });

        // Contact input validation - cho phép cả email và phone
        document.getElementById('contact')?.addEventListener('input', function() {
            const value = this.value.trim();
            const hintDiv = document.getElementById('contact-hint');
            
            if (value.length === 0) {
                hintDiv.textContent = '';
                hintDiv.className = 'input-hint';
                this.style.borderColor = '#e5e5e5';
                return;
            }
            
            const isEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
            const isPartialEmail = value.includes('@') && !isEmail;
            const isPhone = /^[0-9+\-\s()]{10,}$/.test(value);
            const isPartialPhone = /^[0-9+\-\s()]+$/.test(value) && value.length < 10;
            
            if (isEmail) {
                hintDiv.textContent = '✓ Email hợp lệ - sẽ gửi mã qua email';
                hintDiv.className = 'input-hint email';
                this.style.borderColor = '#28a745';
            } else if (isPhone) {
                hintDiv.textContent = '✓ Số điện thoại hợp lệ - sẽ gửi mã qua SMS';
                hintDiv.className = 'input-hint phone';
                this.style.borderColor = '#17a2b8';
            } else if (isPartialEmail) {
                hintDiv.textContent = 'Đang nhập email...';
                hintDiv.className = 'input-hint';
                this.style.borderColor = '#e5e5e5';
            } else if (isPartialPhone) {
                hintDiv.textContent = 'Đang nhập số điện thoại... (cần ít nhất 10 số)';
                hintDiv.className = 'input-hint';
                this.style.borderColor = '#e5e5e5';
            } else {
                hintDiv.textContent = 'Vui lòng nhập email hoặc số điện thoại hợp lệ';
                hintDiv.className = 'input-hint invalid';
                this.style.borderColor = '#e74c3c';
            }
        });

        // Real-time validation feedback
        document.getElementById('contact')?.addEventListener('blur', function() {
            const value = this.value.trim();
            if (value) {
                const isEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
                const isPhone = /^[0-9+\-\s()]{10,}$/.test(value);
                
                if (!isEmail && !isPhone) {
                    this.setCustomValidity('Vui lòng nhập email hợp lệ hoặc số điện thoại');
                } else {
                    this.setCustomValidity('');
                }
            }
        });

        // Password confirmation validation
        document.getElementById('confirm_password')?.addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && newPassword !== confirmPassword) {
                this.setCustomValidity('Mật khẩu xác nhận không khớp');
                this.style.borderColor = '#e74c3c';
            } else {
                this.setCustomValidity('');
                this.style.borderColor = '#e5e5e5';
            }
        });

        // Form validation for reset password
        document.getElementById('resetForm')?.addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Mật khẩu xác nhận không khớp!');
                return false;
            }
            
            if (newPassword.length < 6) {
                e.preventDefault();
                alert('Mật khẩu phải có ít nhất 6 ký tự!');
                return false;
            }
        });

        // Auto-focus on verification code input
        window.addEventListener('load', function() {
            const codeInput = document.getElementById('verification_code');
            if (codeInput) {
                codeInput.focus();
            }
        });

        // Thêm event listener cho các nút thay đổi contact
        document.addEventListener('DOMContentLoaded', function() {
            const changeContactBtns = document.querySelectorAll('.change-contact-btn');
            changeContactBtns.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (confirm('Bạn có chắc chắn muốn thay đổi số điện thoại/email? Mã xác thực hiện tại sẽ bị hủy.')) {
                        this.closest('form').submit();
                    }
                });
            });
        });
    </script>
</body>
</html>