<!-- Main Content -->
<main class="page-content">
    <section class="auth-section forgot-page">
        <div class="container">
            <div class="auth-panel">
                <h2 class="auth-heading">Khôi phục mật khẩu</h2>

                <div class="step-indicator">
                    <div class="step <?php echo ($step ?? 'input') === 'input' ? 'active' : 'completed'; ?>">
                        <span>1</span>
                        <p>Nhập thông tin</p>
                    </div>
                    <div class="step-line"></div>
                    <div class="step <?php echo ($step ?? '') === 'verify' ? 'active' : (($step ?? '') === 'reset' ? 'completed' : ''); ?>">
                        <span>2</span>
                        <p>Nhập mã</p>
                    </div>
                    <div class="step-line"></div>
                    <div class="step <?php echo ($step ?? '') === 'reset' ? 'active' : ''; ?>">
                        <span>3</span>
                        <p>Đổi mật khẩu</p>
                    </div>
                </div>

                <?php if (isset($error) && $error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if (isset($success) && $success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <?php $currentStep = $step ?? 'input'; ?>
                
                <?php if ($currentStep === 'input'): ?>
                    <form method="POST" action="<?php echo $viewData['form_action'] ?? '/auth/forgot'; ?>" class="auth-form">
                        <!-- CSRF Protection -->
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($viewData['csrf_token'] ?? ''); ?>">
                        <input type="hidden" name="action" value="send_code">
                        <div class="form-group">
                            <label for="contact">Email hoặc Số điện thoại</label>
                            <input type="text" id="contact" name="contact" class="form-control" 
                                   placeholder="Nhập Email hoặc SĐT" required autofocus
                                   autocomplete="username">
                        </div>
                        <button type="submit" class="btn-primary auth-submit-btn">Gửi mã xác thực</button>
                    </form>

                    <div class="forgot-info">
                        <p>Hệ thống sẽ gửi mã code 6 chữ số tới thông tin bạn cung cấp. Mã có hiệu lực trong 10 phút.</p>
                    </div>

                <?php elseif ($currentStep === 'verify'): ?>
                    <form method="POST" action="<?php echo $viewData['form_action'] ?? '/auth/forgot'; ?>" class="auth-form">
                        <!-- CSRF Protection -->
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($viewData['csrf_token'] ?? ''); ?>">
                        <div class="form-group">
                            <label for="verification_code">Nhập mã xác thực</label>
                            <input type="text" id="verification_code" name="verification_code" class="form-control code-input"
                                   placeholder="000000" maxlength="6" required autofocus
                                   pattern="[0-9]{6}" title="Mã xác thực phải là 6 chữ số">
                        </div>
                        <button type="submit" class="btn-primary auth-submit-btn" name="action" value="verify_code">Xác thực</button>

                        <div class="forgot-actions">
                            <button type="submit" name="action" value="change_contact" class="ref-code-action" formnovalidate>
                                Nhập lại thông tin khác
                            </button>
                        </div>
                    </form>

                <?php elseif ($currentStep === 'reset'): ?>
                    <form method="POST" action="<?php echo $viewData['form_action'] ?? '/auth/forgot'; ?>" class="auth-form">
                        <!-- CSRF Protection -->
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($viewData['csrf_token'] ?? ''); ?>">
                        <input type="hidden" name="action" value="reset_password">

                        <div class="form-group">
                            <label class="form-label" for="new_password">Mật khẩu mới</label>
                            <div class="password-wrapper">
                                <input type="password" id="new_password" name="new_password" class="form-control" 
                                       placeholder="Mật khẩu mới" required autofocus minlength="8"
                                       autocomplete="new-password">
                                <button type="button" class="password-toggle" onclick="toggleAuthPassword('new_password')"
                                        aria-label="Hiển thị mật khẩu mới" aria-pressed="false" data-label-show="Hiển thị mật khẩu mới"
                                        data-label-hide="Ẩn mật khẩu mới">
                                    <span class="password-toggle-icon" id="new-password-icon" aria-hidden="true"></span>
                                </button>
                            </div>
                            <div class="password-requirements">
                                <small>Mật khẩu phải có ít nhất 8 ký tự, bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="confirm_password">Nhập lại mật khẩu</label>
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

                        <button type="submit" class="btn-primary auth-submit-btn">Đổi mật khẩu</button>
                    </form>
                <?php endif; ?>

                <div class="register-link">
                    <a href="<?php echo ($viewData['login_url'] ?? '?page=login'); ?>">← Quay lại đăng nhập</a>
                </div>
            </div>
        </div>
    </section>
</main>