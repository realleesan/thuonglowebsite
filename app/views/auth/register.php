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
                            Tôi đồng ý với <a href="javascript:void(0)" class="terms-link" onclick="openModal('termsModal')">Điều khoản sử dụng</a> và <a href="javascript:void(0)" class="terms-link" onclick="openModal('privacyModal')">Chính sách bảo mật</a> của ThuongLo.com
                        </label>
                    </div>

                    <!-- Terms Modal -->
                    <div id="termsModal" class="modal-overlay">
                        <div class="modal-content">
                            <button class="modal-close" onclick="closeModal('termsModal')">&times;</button>
                            <h3>Điều khoản sử dụng</h3>
                            <div class="modal-body">
                                <h4>1. Chấp nhận điều khoản</h4>
                                <p>Bằng việc đăng ký tài khoản, bạn đồng ý tuân thủ và bị ràng buộc bởi các Điều khoản sử dụng này.</p>
                                <h4>2. Tài khoản người dùng</h4>
                                <p>Bạn có trách nhiệm duy trì tính bảo mật của tài khoản và mật khẩu. ThuongLo.com không chịu trách nhiệm cho bất kỳ thiệt hại nào phát sinh từ việc lộ thông tin tài khoản.</p>
                                <h4>3. Quyền sở hữu nội dung</h4>
                                <p>Bạn giữ quyền sở hữu đối với nội dung bạn đăng tải trên website. Tuy nhiên, khi đăng tải nội dung, bạn cấp quyền cho ThuongLo.com sử dụng nội dung đó để hiển thị trên nền tảng.</p>
                                <h4>4. Hành vi người dùng</h4>
                                <p>Bạn đồng ý không sử dụng website cho mục đích bất hợp pháp, lừa đảo, hoặc gây hại đến người khác. Mọi hành vi vi phạm sẽ bị xử lý theo quy định của pháp luật.</p>
                                <h4>5. Giới hạn trách nhiệm</h4>
                                <p>ThuongLo.com không chịu trách nhiệm cho bất kỳ thiệt hại trực tiếp, gián tiếp, hoặc ngẫu nhiên nào phát sinh từ việc sử dụng website.</p>
                                <h4>6. Thay đổi điều khoản</h4>
                                <p>Chúng tôi có quyền thay đổi các điều khoản này bất cứ lúc nào. Việc tiếp tục sử dụng sau khi thay đổi đồng nghĩa với việc bạn chấp nhận các điều khoản mới.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Privacy Modal -->
                    <div id="privacyModal" class="modal-overlay">
                        <div class="modal-content">
                            <button class="modal-close" onclick="closeModal('privacyModal')">&times;</button>
                            <h3>Chính sách bảo mật</h3>
                            <div class="modal-body">
                                <h4>1. Thu thập thông tin</h4>
                                <p>Chúng tôi thu thập thông tin cá nhân khi bạn đăng ký tài khoản, bao gồm: họ tên, email, số điện thoại, và các thông tin khác bạn cung cấp tự nguyện.</p>
                                <h4>2. Sử dụng thông tin</h4>
                                <p>Thông tin cá nhân được sử dụng để: cung cấp dịch vụ, cải thiện trải nghiệm, giao tiếp với bạn về các cập nhật và khuyến mãi.</p>
                                <h4>3. Bảo mật thông tin</h4>
                                <p>Chúng tôi áp dụng các biện pháp bảo mật hiện đại để bảo vệ thông tin cá nhân, bao gồm mã hóa dữ liệu và lưu trữ an toàn.</p>
                                <h4>4. Chia sẻ thông tin</h4>
                                <p>Chúng tôi không bán hoặc chia sẻ thông tin cá nhân cho bên thứ ba vì mục đích tiếp thị. Thông tin chỉ được chia sẻ khi có yêu cầu từ cơ quan pháp luật.</p>
                                <h4>5. Cookies</h4>
                                <p>Website sử dụng cookies để cải thiện trải nghiệm người dùng. Bạn có thể tắt cookies trong cài đặt trình duyệt nhưng một số chức năng có thể bị hạn chế.</p>
                                <h4>6. Quyền của người dùng</h4>
                                <p>Bạn có quyền yêu cầu truy cập, sửa đổi hoặc xóa thông tin cá nhân. Hãy liên hệ với chúng tôi để thực hiện các quyền này.</p>
                            </div>
                        </div>
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



<script>
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        setTimeout(() => {
            modal.classList.add('active');
        }, 10);
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('active');
        setTimeout(() => {
            e.target.style.display = 'none';
        }, 300);
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(modal => {
            modal.classList.remove('active');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        });
    }
});
</script>

<style>
/* Terms link styling - bold blue text */
.terms-link {
    color: #007bff;
    font-weight: 700;
    text-decoration: none;
}

.terms-link:hover {
    color: #0056b3;
    text-decoration: underline;
}

/* Modal Overlay */
.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    z-index: 9999;
    justify-content: center;
    align-items: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.modal-overlay.active {
    display: flex;
    opacity: 1;
}

/* Modal Content */
.modal-content {
    background: white;
    border-radius: 12px;
    padding: 0;
    max-width: 550px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    position: relative;
    transform: scale(0.8);
    transition: transform 0.3s ease;
    box-shadow: 0 15px 50px rgba(0, 0, 0, 0.4);
}

.modal-overlay.active .modal-content {
    transform: scale(1);
}

/* Modal Close Button */
.modal-close {
    position: absolute;
    top: 12px;
    right: 12px;
    width: 32px;
    height: 32px;
    border: none;
    background: #f0f0f0;
    border-radius: 50%;
    font-size: 20px;
    line-height: 1;
    cursor: pointer;
    color: #666;
    transition: all 0.2s ease;
    z-index: 10;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-close:hover {
    background: #e0e0e0;
    color: #333;
}

/* Modal Header */
.modal-content h3 {
    padding: 20px 24px 0 24px;
    margin: 0;
    color: #333;
    font-size: 20px;
    font-weight: 700;
}

/* Modal Body */
.modal-body {
    padding: 16px 24px 24px 24px;
}

.modal-body h4 {
    margin: 16px 0 8px 0;
    color: #333;
    font-size: 15px;
    font-weight: 600;
}

.modal-body h4:first-child {
    margin-top: 0;
}

.modal-body p {
    margin: 0 0 8px 0;
    line-height: 1.6;
    color: #555;
    font-size: 14px;
}

/* Scrollbar styling */
.modal-content::-webkit-scrollbar {
    width: 8px;
}

.modal-content::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.modal-content::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 4px;
}

.modal-content::-webkit-scrollbar-thumb:hover {
    background: #aaa;
}
</style>