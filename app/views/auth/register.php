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
<?php
$segments = explode('/', trim($_SERVER['SCRIPT_NAME'], '/'));
$base = '/' . ($segments[0] ?? '') . '/';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - ThuongLo.com</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $base; ?>assets/css/auth.css">
</head>
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

    <script src="<?php echo $base; ?>assets/js/auth.js"></script>
</body>
</html>