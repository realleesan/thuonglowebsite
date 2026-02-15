<?php
/**
 * Password Reset View
 */

// Prevent direct access
if (!defined('THUONGLO_INIT')) {
    exit('Direct access not allowed');
}

// Get data from controller/service
$page_title = $viewData['page_title'] ?? 'Đặt lại mật khẩu';
$csrf_token = $viewData['csrf_token'] ?? '';
$token = $viewData['token'] ?? '';
$error = $viewData['error'] ?? null;
$errors = $viewData['errors'] ?? [];
?>

<main class="page-content">
    <section class="auth-section reset-page">
        <div class="container">
            <div class="auth-panel">
                <h2 class="auth-heading">Đặt lại mật khẩu</h2>
                <p class="auth-subheading">Nhập mật khẩu mới của bạn</p>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <ul>
                            <?php foreach ($errors as $field => $message): ?>
                                <li><?php echo htmlspecialchars($message); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo $viewData['form_action'] ?? '/auth/process-reset'; ?>" class="auth-form">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                    <div class="form-group">
                        <label for="password">Mật khẩu mới</label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" class="form-control"
                                   placeholder="Nhập mật khẩu mới" required minlength="8"
                                   autocomplete="new-password">
                            <button type="button" class="password-toggle" onclick="toggleAuthPassword('password')"
                                    aria-label="Hiển thị mật khẩu" aria-pressed="false" data-label-show="Hiển thị mật khẩu"
                                    data-label-hide="Ẩn mật khẩu">
                                <span class="password-toggle-icon" id="password-icon" aria-hidden="true"></span>
                            </button>
                        </div>
                        <div class="password-requirements">
                            <small>Mật khẩu phải có ít nhất 8 ký tự, bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Xác nhận mật khẩu</label>
                        <div class="password-wrapper">
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"
                                   placeholder="Nhập lại mật khẩu mới" required minlength="8"
                                   autocomplete="new-password">
                            <button type="button" class="password-toggle" onclick="toggleAuthPassword('password_confirmation')"
                                    aria-label="Hiển thị lại mật khẩu" aria-pressed="false" data-label-show="Hiển thị lại mật khẩu"
                                    data-label-hide="Ẩn mật khẩu">
                                <span class="password-toggle-icon" id="password-confirmation-icon" aria-hidden="true"></span>
                            </button>
                        </div>
                        <div id="passwordMatch" class="password-match"></div>
                    </div>

                    <button type="submit" class="btn-primary auth-submit-btn">
                        Đặt lại mật khẩu
                    </button>
                </form>

                <div class="register-link">
                    Nhớ mật khẩu? <a href="<?php echo ($viewData['login_url'] ?? '?page=login'); ?>">Đăng nhập</a>
                </div>
            </div>
        </div>
    </section>
</main>