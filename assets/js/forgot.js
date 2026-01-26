/* JS moved from app/views/auth/forgot.php
   Exposes `togglePassword` and `resendCode` globally because HTML uses inline onclick handlers. */
(function(){
    function togglePassword(fieldId) {
        const passwordInput = document.getElementById(fieldId);
        const iconId = fieldId === 'new_password' ? 'new-password-icon' : 'confirm-password-icon';
        const passwordIcon = document.getElementById(iconId);

        if (!passwordInput) return;
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            if (passwordIcon) passwordIcon.className = 'fas fa-eye-slash';
        } else {
            passwordInput.type = 'password';
            if (passwordIcon) passwordIcon.className = 'fas fa-eye';
        }
    }

    function resendCode() {
        if (confirm('Bạn có muốn gửi lại mã xác thực?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.style.display = 'none';

            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'send_code';

            const contactInput = document.createElement('input');
            contactInput.type = 'hidden';
            contactInput.name = 'contact';
            contactInput.value = window.resetContact || '';

            form.appendChild(actionInput);
            form.appendChild(contactInput);
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Attach listeners after DOM ready
    function init() {
        const codeInput = document.getElementById('verification_code');
        if (codeInput) {
            codeInput.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '').substring(0, 6);
            });
        }

        const contactInput = document.getElementById('contact');
        if (contactInput) {
            contactInput.addEventListener('input', function() {
                const value = this.value.trim();
                const hintDiv = document.getElementById('contact-hint');

                if (value.length === 0) {
                    hintDiv.textContent = '';
                    hintDiv.className = 'input-hint';
                    this.style.borderColor = '#e5e5e5';
                    return;
                }

                const isEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
                const isPartialEmail = value.includes('@') && !isEmail;
                const isPhone = /^[0-9+\-\s()]{10,}$/.test(value);
                const isPartialPhone = /^[0-9+\-\s()]+$/.test(value) && value.length < 10;

                if (isEmail) {
                    hintDiv.textContent = '✓ Email hợp lệ - sẽ gửi mã qua email';
                    hintDiv.className = 'input-hint email';
                    this.style.borderColor = '#28a745';
                } else if (isPhone) {
                    hintDiv.textContent = '✓ Số điện thoại hợp lệ - sẽ gửi mã qua SMS';
                    hintDiv.className = 'input-hint phone';
                    this.style.borderColor = '#17a2b8';
                } else if (isPartialEmail) {
                    hintDiv.textContent = 'Đang nhập email...';
                    hintDiv.className = 'input-hint';
                    this.style.borderColor = '#e5e5e5';
                } else if (isPartialPhone) {
                    hintDiv.textContent = 'Đang nhập số điện thoại... (cần ít nhất 10 số)';
                    hintDiv.className = 'input-hint';
                    this.style.borderColor = '#e5e5e5';
                } else {
                    hintDiv.textContent = 'Vui lòng nhập email hoặc số điện thoại hợp lệ';
                    hintDiv.className = 'input-hint invalid';
                    this.style.borderColor = '#e74c3c';
                }
            });

            contactInput.addEventListener('blur', function() {
                const value = this.value.trim();
                if (value) {
                    const isEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
                    const isPhone = /^[0-9+\-\s()]{10,}$/.test(value);

                    if (!isEmail && !isPhone) {
                        this.setCustomValidity('Vui lòng nhập email hợp lệ hoặc số điện thoại');
                    } else {
                        this.setCustomValidity('');
                    }
                }
            });
        }

        const confirmInput = document.getElementById('confirm_password');
        if (confirmInput) {
            confirmInput.addEventListener('input', function() {
                const newPassword = document.getElementById('new_password')?.value;
                const confirmPassword = this.value;

                if (confirmPassword && newPassword !== confirmPassword) {
                    this.setCustomValidity('Mật khẩu xác nhận không khớp');
                    this.style.borderColor = '#e74c3c';
                } else {
                    this.setCustomValidity('');
                    this.style.borderColor = '#e5e5e5';
                }
            });
        }

        const resetForm = document.getElementById('resetForm');
        if (resetForm) {
            resetForm.addEventListener('submit', function(e) {
                const newPassword = document.getElementById('new_password')?.value || '';
                const confirmPassword = document.getElementById('confirm_password')?.value || '';

                if (newPassword !== confirmPassword) {
                    e.preventDefault();
                    alert('Mật khẩu xác nhận không khớp!');
                    return false;
                }

                if (newPassword.length < 6) {
                    e.preventDefault();
                    alert('Mật khẩu phải có ít nhất 6 ký tự!');
                    return false;
                }
            });
        }

        // Auto-focus verification code on load
        window.addEventListener('load', function() {
            const codeInput = document.getElementById('verification_code');
            if (codeInput) codeInput.focus();
        });

        // Attach click handlers for change-contact buttons
        const changeContactBtns = document.querySelectorAll('.change-contact-btn');
        changeContactBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                if (confirm('Bạn có chắc chắn muốn thay đổi số điện thoại/email? Mã xác thực hiện tại sẽ bị hủy.')) {
                    this.closest('form').submit();
                }
            });
        });
    }

    // expose global functions used by inline onclick attributes
    window.togglePassword = togglePassword;
    window.resendCode = resendCode;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
