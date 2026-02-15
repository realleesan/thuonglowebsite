<main class="page-content">
    <section class="auth-section login-page">
        <div class="container">
            <div class="auth-panel">
                <h2 class="auth-heading">Đăng nhập</h2>

                <form method="POST" action="<?php echo $viewData['form_action']; ?>" class="auth-form">
                    <!-- CSRF Protection -->
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($viewData['csrf_token'] ?? ''); ?>">
                    
                    <div class="form-group">
                        <label for="login" class="form-label">Tài khoản</label>
                        <input type="text" id="login" name="login" class="form-control"
                               placeholder="Email, số điện thoại hoặc tên đăng nhập" required
                               value="<?php echo htmlspecialchars($_COOKIE['remembered_phone'] ?? ''); ?>"
                               autocomplete="username">
                    </div>
        
                    <div class="form-group">
                        <label for="password" class="form-label">Mật khẩu</label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" class="form-control"
                                   placeholder="Mật khẩu" required autocomplete="current-password">
                            <button type="button" class="password-toggle" onclick="toggleAuthPassword('password')"
                                    aria-label="Hiển thị mật khẩu" aria-pressed="false" data-label-show="Hiển thị mật khẩu"
                                    data-label-hide="Ẩn mật khẩu">
                                <span class="password-toggle-icon" id="password-icon" aria-hidden="true"></span>
                            </button>
                        </div>
                    </div>

                    <!-- Rate limiting warning -->
                    <?php if (isset($viewData['rate_limit_warning']) && $viewData['rate_limit_warning']): ?>
                        <div class="alert alert-warning">
                            <strong>Cảnh báo:</strong> Quá nhiều lần đăng nhập thất bại. 
                            Vui lòng thử lại sau <?php echo $viewData['lockout_time'] ?? '15'; ?> phút.
                        </div>
                    <?php endif; ?>

                    <div class="remember-forgot">
                        <label class="remember-me">
                            <input type="checkbox" name="remember_me" <?php echo ($_COOKIE['remembered_phone'] ?? '') ? 'checked' : ''; ?>>
                            Ghi nhớ đăng nhập
                        </label>
                        <div class="register-link" style="margin-top: 15px; margin-bottom: 10px;">
                            <a href="?page=forgot">Quên mật khẩu?</a>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary auth-submit-btn">Đăng nhập</button>
                </form>

                <div class="register-link">
                    Chưa có tài khoản? <a href="?page=register">Đăng ký ngay</a>
                </div>
            </div>
        </div>
    </section>
</main>