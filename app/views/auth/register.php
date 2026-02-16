<main class="page-content">
    <section class="auth-section register-page">
        <div class="container">
            <div class="auth-panel register-panel">
                <h2 class="auth-heading">Đăng ký</h2>
                <p class="auth-subheading">Tham gia ThuongLo.com để khám phá nguồn hàng chất lượng</p>

                <?php 
                // Get field-specific errors
                $fieldErrors = $_SESSION['flash_errors'] ?? [];
                if (!empty($fieldErrors)) {
                    unset($_SESSION['flash_errors']); // Clear after displaying
                }
                ?>

                <form method="POST" action="<?php echo $viewData['form_action']; ?>" id="registerForm" class="auth-form">
                    <!-- CSRF Protection -->
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($viewData['csrf_token'] ?? ''); ?>">
                    
                    <div class="form-group">
                        <label for="name">Họ và tên <span class="required">*</span></label>
                        <input type="text" id="name" name="name" class="form-control <?php echo isset($fieldErrors['name']) ? 'error' : ''; ?>"
                               placeholder="Nhập họ và tên đầy đủ" required
                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                               autocomplete="name">
                        <?php if (isset($fieldErrors['name'])): ?>
                            <div class="field-error"><?php echo htmlspecialchars($fieldErrors['name']); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="username">Tên đăng nhập <span class="required">*</span></label>
                        <input type="text" id="username" name="username" class="form-control <?php echo isset($fieldErrors['username']) ? 'error' : ''; ?>"
                               placeholder="Nhập tên đăng nhập" required
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                               autocomplete="username" pattern="[a-zA-Z0-9_]{3,20}"
                               title="Tên đăng nhập chỉ chứa chữ cái, số và dấu gạch dưới, từ 3-20 ký tự">
                        <small class="form-help">Tên đăng nhập chỉ chứa chữ cái, số và dấu gạch dưới, từ 3-20 ký tự</small>
                        <?php if (isset($fieldErrors['username'])): ?>
                            <div class="field-error"><?php echo htmlspecialchars($fieldErrors['username']); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email <span class="required">*</span></label>
                            <input type="email" id="email" name="email" class="form-control <?php echo isset($fieldErrors['email']) ? 'error' : ''; ?>"
                                   placeholder="Nhập địa chỉ email" required
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                   autocomplete="email">
                            <?php if (isset($fieldErrors['email'])): ?>
                                <div class="field-error"><?php echo htmlspecialchars($fieldErrors['email']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="phone">Số điện thoại <span class="required">*</span></label>
                            <input type="tel" id="phone" name="phone" class="form-control <?php echo isset($fieldErrors['phone']) ? 'error' : ''; ?>"
                                   placeholder="Nhập số điện thoại" required
                                   value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                                   autocomplete="tel">
                            <?php if (isset($fieldErrors['phone'])): ?>
                                <div class="field-error"><?php echo htmlspecialchars($fieldErrors['phone']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">Mật khẩu <span class="required">*</span></label>
                            <div class="password-wrapper">
                                <input type="password" id="password" name="password" class="form-control <?php echo isset($fieldErrors['password']) ? 'error' : ''; ?>"
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
                            <?php if (isset($fieldErrors['password'])): ?>
                                <div class="field-error"><?php echo htmlspecialchars($fieldErrors['password']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Xác nhận mật khẩu <span class="required">*</span></label>
                            <div class="password-wrapper">
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control <?php echo isset($fieldErrors['password_confirmation']) ? 'error' : ''; ?>"
                                       placeholder="Nhập lại mật khẩu" required
                                       autocomplete="new-password">
                                <button type="button" class="password-toggle" onclick="toggleAuthPassword('confirm_password')"
                                        aria-label="Hiển thị lại mật khẩu" aria-pressed="false" data-label-show="Hiển thị lại mật khẩu"
                                        data-label-hide="Ẩn mật khẩu">
                                    <span class="password-toggle-icon" id="confirm-password-icon" aria-hidden="true"></span>
                                </button>
                            </div>
                            <div id="passwordMatch" class="password-match"></div>
                            <?php if (isset($fieldErrors['password_confirmation'])): ?>
                                <div class="field-error"><?php echo htmlspecialchars($fieldErrors['password_confirmation']); ?></div>
                            <?php endif; ?>
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

                    <!-- Account Type Selection -->
                    <div class="form-group">
                        <label class="form-label">Loại tài khoản</label>
                        <div class="radio-group">
                            <div class="radio-option">
                                <input type="radio" id="role_user" name="account_type" value="user" checked>
                                <label for="role_user">Người dùng</label>
                            </div>
                            <div class="radio-option">
                                <input type="radio" id="role_agent" name="account_type" value="agent">
                                <label for="role_agent">Đại lý</label>
                            </div>
                        </div>
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