/**
 * Affiliate Profile Module JavaScript
 * Xử lý logic cho Profile settings
 */

(function() {
    'use strict';

    // ===================================
    // Tab Switching
    // ===================================
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');

    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabName = this.getAttribute('data-tab');
            
            // Remove active class from all buttons and panes
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabPanes.forEach(pane => pane.classList.remove('active'));
            
            // Add active class to clicked button and corresponding pane
            this.classList.add('active');
            document.getElementById(tabName + '-tab').classList.add('active');
        });
    });

    // ===================================
    // Avatar Preview
    // ===================================
    window.previewAvatar = function(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                document.getElementById('avatarPreview').src = e.target.result;
            };
            
            reader.readAsDataURL(input.files[0]);
        }
    };

    // ===================================
    // Personal Info Form
    // ===================================
    const personalInfoForm = document.getElementById('personalInfoForm');
    if (personalInfoForm) {
        personalInfoForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            showLoading();
            
            setTimeout(function() {
                hideLoading();
                showAlert('Thông tin cá nhân đã được cập nhật thành công!', 'success');
            }, 1500);
        });
    }

    window.resetPersonalForm = function() {
        if (confirm('Bạn có chắc muốn đặt lại form?')) {
            document.getElementById('personalInfoForm').reset();
        }
    };

    // ===================================
    // Bank Account Form
    // ===================================
    const bankAccountForm = document.getElementById('bankAccountForm');
    if (bankAccountForm) {
        bankAccountForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const accountHolder = this.querySelector('[name="account_holder"]').value;
            
            // Validate uppercase
            if (accountHolder !== accountHolder.toUpperCase()) {
                showAlert('Tên chủ tài khoản phải viết IN HOA, không dấu', 'warning');
                return;
            }
            
            showLoading();
            
            setTimeout(function() {
                hideLoading();
                showAlert('Thông tin ngân hàng đã được cập nhật thành công!', 'success');
            }, 1500);
        });
    }

    window.resetBankForm = function() {
        if (confirm('Bạn có chắc muốn đặt lại form?')) {
            document.getElementById('bankAccountForm').reset();
        }
    };

    // ===================================
    // Password Toggle
    // ===================================
    window.togglePassword = function(inputId) {
        const input = document.getElementById(inputId);
        const button = input.nextElementSibling;
        const icon = button.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    };

    // ===================================
    // Password Strength
    // ===================================
    const newPasswordInput = document.getElementById('newPassword');
    if (newPasswordInput) {
        newPasswordInput.addEventListener('input', function() {
            const password = this.value;
            checkPasswordStrength(password);
            checkPasswordRequirements(password);
        });
    }

    function checkPasswordStrength(password) {
        const strengthBar = document.getElementById('passwordStrength');
        const strengthFill = document.getElementById('strengthFill');
        const strengthText = document.getElementById('strengthText');
        
        if (!password) {
            strengthBar.style.display = 'none';
            return;
        }
        
        strengthBar.style.display = 'block';
        
        let strength = 0;
        
        // Length
        if (password.length >= 8) strength++;
        if (password.length >= 12) strength++;
        
        // Uppercase
        if (/[A-Z]/.test(password)) strength++;
        
        // Lowercase
        if (/[a-z]/.test(password)) strength++;
        
        // Numbers
        if (/[0-9]/.test(password)) strength++;
        
        // Special characters
        if (/[^A-Za-z0-9]/.test(password)) strength++;
        
        // Remove all classes
        strengthFill.classList.remove('weak', 'medium', 'strong');
        strengthText.classList.remove('weak', 'medium', 'strong');
        
        if (strength <= 2) {
            strengthFill.classList.add('weak');
            strengthText.classList.add('weak');
            strengthText.textContent = 'Yếu';
        } else if (strength <= 4) {
            strengthFill.classList.add('medium');
            strengthText.classList.add('medium');
            strengthText.textContent = 'Trung bình';
        } else {
            strengthFill.classList.add('strong');
            strengthText.classList.add('strong');
            strengthText.textContent = 'Mạnh';
        }
    }

    function checkPasswordRequirements(password) {
        // Length
        const reqLength = document.getElementById('req-length');
        if (password.length >= 8) {
            reqLength.classList.add('valid');
        } else {
            reqLength.classList.remove('valid');
        }
        
        // Uppercase
        const reqUppercase = document.getElementById('req-uppercase');
        if (/[A-Z]/.test(password)) {
            reqUppercase.classList.add('valid');
        } else {
            reqUppercase.classList.remove('valid');
        }
        
        // Lowercase
        const reqLowercase = document.getElementById('req-lowercase');
        if (/[a-z]/.test(password)) {
            reqLowercase.classList.add('valid');
        } else {
            reqLowercase.classList.remove('valid');
        }
        
        // Number
        const reqNumber = document.getElementById('req-number');
        if (/[0-9]/.test(password)) {
            reqNumber.classList.add('valid');
        } else {
            reqNumber.classList.remove('valid');
        }
    }

    // ===================================
    // Change Password Form
    // ===================================
    const changePasswordForm = document.getElementById('changePasswordForm');
    if (changePasswordForm) {
        changePasswordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const currentPassword = this.querySelector('[name="current_password"]').value;
            const newPassword = this.querySelector('[name="new_password"]').value;
            const confirmPassword = this.querySelector('[name="confirm_password"]').value;
            
            // Validate
            if (newPassword !== confirmPassword) {
                showAlert('Mật khẩu xác nhận không khớp!', 'error');
                return;
            }
            
            if (newPassword.length < 8) {
                showAlert('Mật khẩu phải có ít nhất 8 ký tự!', 'error');
                return;
            }
            
            if (!/[A-Z]/.test(newPassword)) {
                showAlert('Mật khẩu phải có ít nhất 1 chữ hoa!', 'error');
                return;
            }
            
            if (!/[a-z]/.test(newPassword)) {
                showAlert('Mật khẩu phải có ít nhất 1 chữ thường!', 'error');
                return;
            }
            
            if (!/[0-9]/.test(newPassword)) {
                showAlert('Mật khẩu phải có ít nhất 1 số!', 'error');
                return;
            }
            
            showLoading();
            
            setTimeout(function() {
                hideLoading();
                showAlert('Mật khẩu đã được thay đổi thành công!', 'success');
                changePasswordForm.reset();
                document.getElementById('passwordStrength').style.display = 'none';
            }, 1500);
        });
    }

    window.resetPasswordForm = function() {
        if (confirm('Bạn có chắc muốn đặt lại form?')) {
            document.getElementById('changePasswordForm').reset();
            document.getElementById('passwordStrength').style.display = 'none';
            
            // Reset requirements
            document.querySelectorAll('.password-requirements li').forEach(li => {
                li.classList.remove('valid');
            });
        }
    };

    // ===================================
    // Initialize
    // ===================================
    console.log('Profile Module Initialized');

})();
