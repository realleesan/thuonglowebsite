<?php
/**
 * Password Reset View
 */

// Prevent direct access
if (!defined('THUONGLO_INIT')) {
    exit('Direct access not allowed');
}

$page_title = $page_title ?? 'Đặt lại mật khẩu';
$csrf_token = $csrf_token ?? '';
$token = $token ?? '';
$error = $error ?? null;
$errors = $errors ?? [];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - Thuong Lo</title>
    <link rel="stylesheet" href="/assets/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-form">
            <div class="auth-header">
                <h1>Đặt lại mật khẩu</h1>
                <p>Nhập mật khẩu mới của bạn</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $field => $message): ?>
                            <li><?= htmlspecialchars($message) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="/auth/process-reset" class="auth-form-content">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                <div class="form-group">
                    <label for="password">Mật khẩu mới</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        minlength="8"
                        placeholder="Nhập mật khẩu mới"
                    >
                    <div class="form-help">
                        Mật khẩu phải có ít nhất 8 ký tự, bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt
                    </div>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Xác nhận mật khẩu</label>
                    <input 
                        type="password" 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        required 
                        minlength="8"
                        placeholder="Nhập lại mật khẩu mới"
                    >
                </div>

                <button type="submit" class="btn btn-primary btn-full">
                    Đặt lại mật khẩu
                </button>
            </form>

            <div class="auth-footer">
                <p>
                    Nhớ mật khẩu? 
                    <a href="?page=login">Đăng nhập</a>
                </p>
            </div>
        </div>
    </div>

    <script src="/assets/js/auth.js"></script>
    <script>
        // Password confirmation validation
        document.addEventListener('DOMContentLoaded', function() {
            const password = document.getElementById('password');
            const passwordConfirmation = document.getElementById('password_confirmation');
            
            function validatePasswordMatch() {
                if (password.value !== passwordConfirmation.value) {
                    passwordConfirmation.setCustomValidity('Mật khẩu xác nhận không khớp');
                } else {
                    passwordConfirmation.setCustomValidity('');
                }
            }
            
            password.addEventListener('input', validatePasswordMatch);
            passwordConfirmation.addEventListener('input', validatePasswordMatch);
        });
    </script>
</body>
</html>