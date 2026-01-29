<?php
require_once __DIR__ . '/auth.php'; 

// --- 0. LOGIC RESET (MỚI THÊM) ---
// Nếu người dùng bấm từ trang Login sang (có ?reset=true)
if (isset($_GET['reset']) && $_GET['reset'] == 'true') {
    // Xóa sạch session liên quan đến quy trình quên mật khẩu
    unset($_SESSION['forgot_step'], $_SESSION['reset_code'], $_SESSION['reset_contact'], $_SESSION['flash_error'], $_SESSION['flash_success']);
    
    // Chuyển hướng lại chính trang này (bỏ ?reset=true) để bắt đầu sạch sẽ
    header('Location: index.php?page=forgot');
    exit;
}

// --- 1. LẤY DỮ LIỆU TỪ SESSION (QUAN TRỌNG) ---
$step = $_SESSION['forgot_step'] ?? 'input'; 
$error = $_SESSION['flash_error'] ?? '';
$success = $_SESSION['flash_success'] ?? '';

// Xóa thông báo sau khi đã lấy ra (để F5 không hiện lại)
unset($_SESSION['flash_error'], $_SESSION['flash_success']);

// --- HÀM GIẢ LẬP (Giữ nguyên) ---
if (!function_exists('sendResetEmail')) {
    function sendResetEmail($email, $code) { return true; }
}
if (!function_exists('sendResetSMS')) {
    function sendResetSMS($phone, $code) { return true; }
}

// --- 2. XỬ LÝ POST REQUEST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'change_contact') {
        // Reset về bước 1
        unset($_SESSION['reset_code'], $_SESSION['reset_contact'], $_SESSION['reset_expires']);
        $_SESSION['forgot_step'] = 'input'; // Lưu vào session
        $_SESSION['flash_success'] = 'Mời nhập thông tin liên hệ mới.';
    } 
    elseif ($action === 'send_code') {
        $contact = sanitize($_POST['contact'] ?? '');
        if (empty($contact)) {
            $_SESSION['flash_error'] = 'Vui lòng nhập thông tin.';
        } else {
            // Giả lập gửi mã
            $code = rand(100000, 999999);
            $_SESSION['reset_code'] = $code;
            $_SESSION['reset_contact'] = $contact;
            $_SESSION['reset_expires'] = time() + 600;
            
            // Chuyển sang bước 2 (Lưu vào session)
            $_SESSION['forgot_step'] = 'verify';
            $_SESSION['flash_success'] = "Gửi thành công! Mã test của bạn là: <b>$code</b>";
        }
    } 
    elseif ($action === 'verify_code') {
        $inputCode = sanitize($_POST['verification_code'] ?? '');
        $savedCode = $_SESSION['reset_code'] ?? '';
        
        // Kiểm tra mã (Lưu ý: dùng != thay vì !== để so sánh lỏng giữa chuỗi và số)
        if (empty($savedCode) || $inputCode != $savedCode) {
            $_SESSION['flash_error'] = 'Mã xác thực sai hoặc đã hết hạn!';
            // Giữ nguyên step là verify
        } else {
            // Chuyển sang bước 3 (Lưu vào session)
            $_SESSION['forgot_step'] = 'reset';
            $_SESSION['flash_success'] = 'Mã đúng! Mời đặt mật khẩu mới.';
        }
    } 
    elseif ($action === 'reset_password') {
        $p1 = $_POST['new_password'] ?? '';
        $p2 = $_POST['confirm_password'] ?? '';
        
        if (strlen($p1) < 6) {
             $_SESSION['flash_error'] = 'Mật khẩu phải từ 6 ký tự trở lên!';
        } elseif ($p1 !== $p2) {
             $_SESSION['flash_error'] = 'Mật khẩu không khớp!';
        } else {
            // Thành công -> Xóa session rác
            unset($_SESSION['reset_code'], $_SESSION['forgot_step'], $_SESSION['reset_contact']);
            $_SESSION['flash_success'] = 'Đổi mật khẩu thành công! Vui lòng đăng nhập lại.';
            
            // Lưu session và Chuyển hướng về trang Login
            session_write_close(); 
            header('Location: index.php?page=login');
            exit;
        }
    }
    
    // --- CHUYỂN HƯỚNG VỀ CHÍNH TRANG NÀY ĐỂ TRÁNH LỖI FORM RESUBMISSION ---
    session_write_close(); // Đảm bảo Session được lưu trước khi chuyển trang
    header('Location: index.php?page=forgot');
    exit;
}
?>

<section class="auth-section">
    <div class="container">
        <div class="auth-panel" style="max-width: 550px; margin: 0 auto;">
            
            <h1 class="auth-heading" style="text-align: center; margin-bottom: 30px;">Lấy lại mật khẩu</h1>
            
            <div class="step-indicator">
                <div class="step <?php echo $step === 'input' ? 'active' : 'completed'; ?>">
                    <span>1</span><p>Nhập tin</p>
                </div>
                <div class="step-line"></div>
                <div class="step <?php echo $step === 'verify' ? 'active' : ($step === 'reset' ? 'completed' : ''); ?>">
                    <span>2</span><p>Mã code</p>
                </div>
                <div class="step-line"></div>
                <div class="step <?php echo $step === 'reset' ? 'active' : ''; ?>">
                    <span>3</span><p>Đổi pass</p>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error" style="margin-bottom: 20px;"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success" style="margin-bottom: 20px;"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if ($step === 'input'): ?>
                <form method="POST" action="index.php?page=forgot" class="auth-form">
                    <input type="hidden" name="action" value="send_code">
                    <div class="form-group">
                        <input type="text" name="contact" class="form-control" placeholder="Nhập Email hoặc SĐT" required autofocus>
                    </div>
                    <button type="submit" class="btn-primary auth-submit-btn">Lấy mã (Test)</button>
                </form>

            <?php elseif ($step === 'verify'): ?>
                <form method="POST" action="index.php?page=forgot" class="auth-form">
                    <input type="hidden" name="action" value="verify_code">
                    <div class="form-group">
                        <input type="text" name="verification_code" class="form-control" 
                               style="text-align: center; font-size: 24px; letter-spacing: 5px;" 
                               placeholder="000000" maxlength="6" required autofocus>
                    </div>
                    <button type="submit" class="btn-primary auth-submit-btn">Xác thực</button>
                    
                    <div style="text-align:center; margin-top:15px;">
                        <button type="submit" name="action" value="change_contact" class="ref-code-action" style="border:none; background:none;">
                            Nhập lại SĐT khác?
                        </button>
                    </div>
                </form>

            <?php elseif ($step === 'reset'): ?>
                <form method="POST" action="index.php?page=forgot" class="auth-form">
                    <input type="hidden" name="action" value="reset_password">
        
                    <div class="form-group">
                        <label class="form-label" for="new_password">Mật khẩu mới</label>
                        <div class="password-wrapper">
                            <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Mật khẩu mới" required autofocus>
                            <button type="button" class="password-toggle" onclick="toggleAuthPassword('new_password')" aria-label="Hiển thị mật khẩu mới">
                                <i class="fa-solid fa-eye" id="new-password-icon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="confirm_password">Nhập lại mật khẩu</label>
                        <div class="password-wrapper">
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Nhập lại mật khẩu" required>
                            <button type="button" class="password-toggle" onclick="toggleAuthPassword('confirm_password')" aria-label="Hiển thị lại mật khẩu">
                                <i class="fa-solid fa-eye" id="confirm-password-icon"></i>
                            </button>
                        </div>
                    </div>
        
                    <button type="submit" class="btn-primary auth-submit-btn">Đổi mật khẩu</button>
                </form>
            <?php endif; ?>

            <div class="register-link" style="margin-top: 20px;">
                <a href="index.php?page=login">← Quay lại đăng nhập</a>
            </div>
        </div>
    </div>
</section>