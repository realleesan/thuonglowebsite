<main class="page-content">
    <section class="auth-section register-page">
        <div class="container">
            <h1 class="page-title-main"><?php echo htmlspecialchars($viewData['page_title']); ?></h1>

            <div class="auth-panel register-panel">
                <h2 class="auth-heading">Đăng ký</h2>
                <p class="auth-subheading">Tham gia ThuongLo.com để khám phá nguồn hàng chất lượng</p>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <script>
                        setTimeout(function() {
                            window.location.href = '<?php echo ($viewData['login_url'] ?? '?page=login'); ?>';
                        }, 2000);
                    </script>
                <?php endif; ?>

                <form method="POST" action="<?php echo $viewData['form_action']; ?>" id="registerForm" class="auth-form">
                    <!-- CSRF Protection -->
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($viewData['csrf_token'] ?? ''); ?>">
                    
                    <div class="form-group">
                        <label for="name">Họ và tên <span class="required">*</span></label>
                        <input type="text" id="name" name="name" class="form-control"
                               placeholder="Nhập họ và tên đầy đủ" required
                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                               autocomplete="name">
                    </div>

                    <div class="form-group">
                        <label for="username">Tên đăng nhập <span class="required">*</span></label>
                        <input type="text" id="username" name="username" class="form-control"
                               placeholder="Nhập tên đăng nhập" required
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                               autocomplete="username" pattern="[a-zA-Z0-9_]{3,20}"
                               title="Tên đăng nhập chỉ chứa chữ cái, số và dấu gạch dưới, từ 3-20 ký tự">
                        <small class="form-help">Tên đăng nhập chỉ chứa chữ cái, số và dấu gạch dưới, từ 3-20 ký tự</small>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email <span class="required">*</span></label>
                            <input type="email" id="email" name="email" class="form-control"
                                   placeholder="Nhập địa chỉ email" required
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                   autocomplete="email">
                        </div>

                        <div class="form-group">
                            <label for="phone">Số điện thoại <span class="required">*</span></label>
                            <input type="tel" id="phone" name="phone" class="form-control"
                                   placeholder="Nhập số điện thoại" required
                                   value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                                   autocomplete="tel">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">Mật khẩu <span class="required">*</span></label>
                            <div class="password-wrapper">
                                <input type="password" id="password" name="password" class="form-control"
                                       placeholder="Nhập mật khẩu" required minlength="8"
                                       autocomplete="new-password">
                                <button type="button" class="password-toggle" onclick="toggleAuthPassword('password')"
                                        aria-label="Hiển thị mật khẩu" aria-pressed="false" data-label-show="Hiển thị mật khẩu"
                                        data-label-hide="Ẩn mật khẩu">
                                    <span class="password-toggle-icon" id="password-icon" aria-hidden="true"></span>
                                </button>
                            </div>
                            <div id="passwordStrength" class="password-strength"></div>
                            <div class="password-requirements">
                                <small>Mật khẩu phải có ít nhất 8 ký tự, bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Xác nhận mật khẩu <span class="required">*</span></label>
                            <div class="password-wrapper">
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control"
                                       placeholder="Nhập lại mật khẩu" required
                                       autocomplete="new-password">
                                <button type="button" class="password-toggle" onclick="toggleAuthPassword('confirm_password')"
                                        aria-label="Hiển thị lại mật khẩu" aria-pressed="false" data-label-show="Hiển thị lại mật khẩu"
                                        data-label-hide="Ẩn mật khẩu">
                                    <span class="password-toggle-icon" id="confirm-password-icon" aria-hidden="true"></span>
                                </button>
                            </div>
                            <div id="passwordMatch" class="password-match"></div>
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
                    Đã có tài khoản? <a href="<?php echo ($viewData['login_url'] ?? '?page=login'); ?>">Đăng nhập ngay</a>
                </div>
            </div>
        </div>
    </section>
</main>