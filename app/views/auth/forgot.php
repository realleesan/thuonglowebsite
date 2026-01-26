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
<?php
$segments = explode('/', trim($_SERVER['SCRIPT_NAME'], '/'));
$base = '/' . ($segments[0] ?? '') . '/';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu - ThuongLo.com</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $base; ?>assets/css/auth.css">
    <link rel="stylesheet" href="<?php echo $base; ?>assets/css/forgot.css">
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
                <div class="verification-extra">
                    <form method="POST" action="" class="inline-form">
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
                    <form method="POST" action="" class="inline-form">
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

    
    <script>window.resetContact = '<?php echo addslashes($_SESSION['reset_contact'] ?? ''); ?>';</script>
    <script src="<?php echo $base; ?>assets/js/forgot.js"></script>
    <script src="<?php echo $base; ?>assets/js/auth.js"></script>
</body>
</html>