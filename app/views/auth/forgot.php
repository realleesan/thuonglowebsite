<?php
require_once __DIR__ . '/auth.php'; 

// Initialize ViewDataService (should already be available from view_init.php)
if (!isset($viewDataService)) {
    require_once __DIR__ . '/../../core/view_init.php';
}

if (!function_exists('forgot_redirect')) {
    function forgot_redirect(string $url): void {
        if (!headers_sent()) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            header('Location: ' . $url);
            exit;
        }

        echo '<script>window.location.href = ' . json_encode($url) . ';</script>';
        echo '<noscript><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"></noscript>';
        exit;
    }
}

// --- 0. LOGIC RESET (MỚI THÊM) ---
// Nếu người dùng bấm từ trang Login sang (có ?reset=true)
if (isset($_GET['reset']) && $_GET['reset'] == 'true') {
    // Xóa sạch session liên quan đến quy trình quên mật khẩu
    unset($_SESSION['forgot_step'], $_SESSION['reset_code'], $_SESSION['reset_contact'], $_SESSION['flash_error'], $_SESSION['flash_success']);
    
    // Chuyển hướng lại chính trang này (bỏ ?reset=true) để bắt đầu sạch sẽ
    forgot_redirect(page_url('forgot'));
}

// --- 1. LẤY DỮ LIỆU TỪ SESSION (QUAN TRỌNG) ---
$step = $_SESSION['forgot_step'] ?? 'input'; 
$error = $_SESSION['flash_error'] ?? '';
$success = $_SESSION['flash_success'] ?? '';

// Xóa thông báo sau khi đã lấy ra (để F5 không hiện lại)
unset($_SESSION['flash_error'], $_SESSION['flash_success']);

// --- HÀM XỬ LÝ THỰC TẾ ---
if (!function_exists('sendResetEmail')) {
    function sendResetEmail($email, $code) { 
        // TODO: Implement real email sending
        return true; 
    }
}
if (!function_exists('sendResetSMS')) {
    function sendResetSMS($phone, $code) { 
        // TODO: Implement real SMS sending
        return true; 
    }
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
            try {
                // Gửi mã xác thực thực tế
                $code = sendResetCode($contact);
                $_SESSION['forgot_step'] = 'verify';
                $_SESSION['flash_success'] = "Mã xác thực đã được gửi đến {$contact}";
            } catch (Exception $e) {
                $_SESSION['flash_error'] = $e->getMessage();
            }
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
        $contact = $_SESSION['reset_contact'] ?? '';
        $code = $_SESSION['reset_code'] ?? '';
        
        if (strlen($p1) < 6) {
             $_SESSION['flash_error'] = 'Mật khẩu phải từ 6 ký tự trở lên!';
        } elseif ($p1 !== $p2) {
             $_SESSION['flash_error'] = 'Mật khẩu không khớp!';
        } else {
            try {
                // Reset mật khẩu thực tế
                resetPassword($contact, $p1, $code);
                $_SESSION['flash_success'] = 'Đổi mật khẩu thành công! Vui lòng đăng nhập lại.';
                
                // Lưu session và Chuyển hướng về trang Login
                session_write_close(); 
                forgot_redirect(page_url('login'));
            } catch (Exception $e) {
                $_SESSION['flash_error'] = $e->getMessage();
            }
        }
    }
    
    // --- CHUYỂN HƯỚNG VỀ CHÍNH TRANG NÀY ĐỂ TRÁNH LỖI FORM RESUBMISSION ---
    session_write_close(); // Đảm bảo Session được lưu trước khi chuyển trang
    forgot_redirect(page_url('forgot'));
}

// Get view data
$viewData = $viewDataService->getAuthForgotData();
?>

<!-- Main Content -->
<div id="wrapper-container" class="wrapper-container forgot-wrapper">
    <div class="content-pusher">
        <div id="main-content">
            <div class="elementor elementor-forgot">

                <section class="forgot-form-section">
                    <div class="container">
                        <div class="auth-panel forgot-panel">
                            <h2 class="auth-heading"><?php echo $viewData['page_title']; ?></h2>

                            <div class="step-indicator">
                                <div class="step <?php echo $step === 'input' ? 'active' : 'completed'; ?>">
                                    <span>1</span>
                                    <p>Nhập thông tin</p>
                                </div>
                                <div class="step-line"></div>
                                <div class="step <?php echo $step === 'verify' ? 'active' : ($step === 'reset' ? 'completed' : ''); ?>">
                                    <span>2</span>
                                    <p>Nhập mã</p>
                                </div>
                                <div class="step-line"></div>
                                <div class="step <?php echo $step === 'reset' ? 'active' : ''; ?>">
                                    <span>3</span>
                                    <p>Đổi mật khẩu</p>
                                </div>
                            </div>

                            <?php if ($error): ?>
                                <div class="alert alert-error"><?php echo $error; ?></div>
                            <?php endif; ?>

                            <?php if ($success): ?>
                                <div class="alert alert-success"><?php echo $success; ?></div>
                            <?php endif; ?>

                            <?php if ($step === 'input'): ?>
                                <form method="POST" action="<?php echo $viewData['form_action']; ?>" class="auth-form">
                                    <input type="hidden" name="action" value="send_code">
                                    <div class="form-group">
                                        <label for="contact">Email hoặc Số điện thoại</label>
                                        <input type="text" id="contact" name="contact" class="form-control" placeholder="Nhập Email hoặc SĐT" required autofocus>
                                    </div>
                                    <button type="submit" class="btn-primary auth-submit-btn">Gửi mã xác thực</button>
                                </form>

                                <div class="forgot-info">
                                    <p>Hệ thống sẽ gửi mã code 6 chữ số tới thông tin bạn cung cấp. Mã có hiệu lực trong 10 phút.</p>
                                </div>

                            <?php elseif ($step === 'verify'): ?>
                                <form method="POST" action="<?php echo $viewData['form_action']; ?>" class="auth-form">
                                    <div class="form-group">
                                        <label for="verification_code">Nhập mã xác thực</label>
                                        <input type="text" id="verification_code" name="verification_code" class="form-control code-input"
                                               placeholder="000000" maxlength="6" required autofocus>
                                    </div>
                                    <button type="submit" class="btn-primary auth-submit-btn" name="action" value="verify_code">Xác thực</button>

                                    <div class="forgot-actions">
                                        <button type="submit" name="action" value="change_contact" class="ref-code-action" formnovalidate>
                                            Nhập lại thông tin khác
                                        </button>
                                    </div>
                                </form>

                            <?php elseif ($step === 'reset'): ?>
                                <form method="POST" action="<?php echo $viewData['form_action']; ?>" class="auth-form">
                                    <input type="hidden" name="action" value="reset_password">

                                    <div class="form-group">
                                        <label class="form-label" for="new_password">Mật khẩu mới</label>
                                        <div class="password-wrapper">
                                            <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Mật khẩu mới" required autofocus>
                                            <button type="button" class="password-toggle" onclick="toggleAuthPassword('new_password')"
                                                    aria-label="Hiển thị mật khẩu mới" aria-pressed="false" data-label-show="Hiển thị mật khẩu mới"
                                                    data-label-hide="Ẩn mật khẩu mới">
                                                <span class="password-toggle-icon" id="new-password-icon" aria-hidden="true"></span>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label" for="confirm_password">Nhập lại mật khẩu</label>
                                        <div class="password-wrapper">
                                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Nhập lại mật khẩu" required>
                                            <button type="button" class="password-toggle" onclick="toggleAuthPassword('confirm_password')"
                                                    aria-label="Hiển thị lại mật khẩu" aria-pressed="false" data-label-show="Hiển thị lại mật khẩu"
                                                    data-label-hide="Ẩn mật khẩu">
                                                <span class="password-toggle-icon" id="confirm-password-icon" aria-hidden="true"></span>
                                            </button>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn-primary auth-submit-btn">Đổi mật khẩu</button>
                                </form>
                            <?php endif; ?>

                            <div class="register-link">
                                <a href="<?php echo $viewData['login_url']; ?>">← Quay lại đăng nhập</a>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>