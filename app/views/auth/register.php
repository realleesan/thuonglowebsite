<?php
require_once __DIR__ . '/auth.php';

// Initialize ViewDataService (should already be available from view_init.php)
if (!isset($viewDataService)) {
    require_once __DIR__ . '/../../core/view_init.php';
}

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
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ';
    } elseif ($password !== $confirmPassword) {
        $error = 'Mật khẩu xác nhận không khớp';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự';
    } else {
        try {
            // Đăng ký người dùng thực tế
            $user = registerUser($fullName, $email, $phone, $password, $refCode);
            
            if ($user) {
                $success = 'Đăng ký thành công! Đang chuyển hướng...';
            } else {
                $error = 'Đăng ký thất bại, vui lòng thử lại';
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// Get view data
$viewData = $viewDataService->getAuthRegisterData();
$refCodeFromUrl = $viewData['ref_code_from_url'];
?>

<main class="page-content">
    <section class="auth-section register-page">
        <div class="container">
            <h1 class="page-title-main"><?php echo $viewData['page_title']; ?></h1>

            <div class="auth-panel register-panel">
                <h2 class="auth-heading">Đăng ký</h2>
                <p class="auth-subheading">Tham gia ThuongLo.com để khám phá nguồn hàng chất lượng</p>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                    <script>
                        setTimeout(function() {
                            window.location.href = '<?php echo page_url('home'); ?>';
                        }, 2000);
                    </script>
                <?php endif; ?>

                <form method="POST" action="<?php echo $viewData['form_action']; ?>" id="registerForm" class="auth-form">
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
                                <button type="button" class="password-toggle" onclick="toggleAuthPassword('password')"
                                        aria-label="Hiển thị mật khẩu" aria-pressed="false" data-label-show="Hiển thị mật khẩu"
                                        data-label-hide="Ẩn mật khẩu">
                                    <span class="password-toggle-icon" id="password-icon" aria-hidden="true"></span>
                                </button>
                            </div>
                            <div id="passwordStrength" class="password-strength"></div>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Xác nhận mật khẩu <span class="required">*</span></label>
                            <div class="password-wrapper">
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control"
                                       placeholder="Nhập lại mật khẩu" required>
                                <button type="button" class="password-toggle" onclick="toggleAuthPassword('confirm_password')"
                                        aria-label="Hiển thị lại mật khẩu" aria-pressed="false" data-label-show="Hiển thị lại mật khẩu"
                                        data-label-hide="Ẩn mật khẩu">
                                    <span class="password-toggle-icon" id="confirm-password-icon" aria-hidden="true"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="ref_code">Mã giới thiệu</label>
                        <input type="text" id="ref_code" name="ref_code"
                               placeholder="Nhập mã giới thiệu (nếu có)"
                               value="<?php echo htmlspecialchars($refCodeFromUrl ?: ''); ?>"
                               class="form-control <?php echo $refCodeFromUrl ? 'readonly' : ''; ?>"
                               <?php echo $refCodeFromUrl ? 'readonly' : ''; ?>>

                        <?php if ($refCodeFromUrl): ?>
                            <div class="ref-code-info">
                                <span class="icon">✓</span>
                                Mã giới thiệu đã được tự động điền từ link giới thiệu
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

                    <button type="submit" class="btn-primary auth-submit-btn">Tạo tài khoản</button>
                </form>

                <div class="register-link">
                    Đã có tài khoản? <a href="<?php echo $viewData['login_url']; ?>">Đăng nhập ngay</a>
                </div>
            </div>
        </div>
    </section>
</main>