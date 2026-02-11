// User Account JavaScript - Clean & Professional
document.addEventListener('DOMContentLoaded', function() {
    // Initialize account functionality
    initAccountFunctionality();
    
    function initAccountFunctionality() {
        // Form validation
        initFormValidation();
        
        // Avatar upload
        initAvatarUpload();
        
        // Password visibility toggle
        initPasswordToggle();
        
        // Delete account confirmation
        initDeleteConfirmation();
    }

    function initFormValidation() {
        const forms = document.querySelectorAll('.account-form');
        
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!validateForm(this)) {
                    e.preventDefault();
                }
            });
            
            // Real-time validation
            const inputs = form.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    validateField(this);
                });
                
                input.addEventListener('input', function() {
                    clearFieldError(this);
                });
            });
        });
    }

    function validateForm(form) {
        let isValid = true;
        const inputs = form.querySelectorAll('.form-control[required]');
        
        inputs.forEach(input => {
            if (!validateField(input)) {
                isValid = false;
            }
        });
        
        return isValid;
    }

    function validateField(field) {
        const value = field.value.trim();
        const fieldName = field.name;
        let isValid = true;
        let errorMessage = '';
        
        // Clear previous errors
        clearFieldError(field);
        
        // Required field validation
        if (field.hasAttribute('required') && !value) {
            errorMessage = 'Trường này là bắt buộc';
            isValid = false;
        }
        
        // Email validation
        else if (fieldName === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                errorMessage = 'Email không hợp lệ';
                isValid = false;
            }
        }
        
        // Phone validation
        else if (fieldName === 'phone' && value) {
            const phoneRegex = /^[0-9]{10,11}$/;
            if (!phoneRegex.test(value.replace(/\s/g, ''))) {
                errorMessage = 'Số điện thoại không hợp lệ';
                isValid = false;
            }
        }
        
        // Password validation
        else if (fieldName === 'password' && value) {
            if (value.length < 6) {
                errorMessage = 'Mật khẩu phải có ít nhất 6 ký tự';
                isValid = false;
            }
        }
        
        if (!isValid) {
            showFieldError(field, errorMessage);
        }
        
        return isValid;
    }

    function showFieldError(field, message) {
        field.classList.add('error');
        
        let errorElement = field.parentNode.querySelector('.form-error');
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.className = 'form-error';
            field.parentNode.appendChild(errorElement);
        }
        
        errorElement.textContent = message;
    }

    function clearFieldError(field) {
        field.classList.remove('error');
        
        const errorElement = field.parentNode.querySelector('.form-error');
        if (errorElement) {
            errorElement.remove();
        }
    }

    function initAvatarUpload() {
        const avatarInput = document.getElementById('avatar-upload');
        const avatarPreview = document.querySelector('.profile-avatar img');
        const avatarPlaceholder = document.querySelector('.profile-avatar-placeholder');
        
        if (avatarInput) {
            avatarInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            if (avatarPreview) {
                                avatarPreview.src = e.target.result;
                                avatarPreview.style.display = 'block';
                            }
                            if (avatarPlaceholder) {
                                avatarPlaceholder.style.display = 'none';
                            }
                        };
                        reader.readAsDataURL(file);
                    } else {
                        showAlert('Vui lòng chọn file hình ảnh hợp lệ', 'error');
                    }
                }
            });
        }
    }

    function initPasswordToggle() {
        const toggleButtons = document.querySelectorAll('.password-toggle');
        
        toggleButtons.forEach(button => {
            button.addEventListener('click', function() {
                const input = this.previousElementSibling;
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });
    }

    function initDeleteConfirmation() {
        const deleteButtons = document.querySelectorAll('.delete-account-btn');
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                if (confirm('Bạn có chắc chắn muốn xóa tài khoản? Hành động này không thể hoàn tác.')) {
                    window.location.href = this.href;
                }
            });
        });
    }

    function showAlert(message, type = 'info') {
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.textContent = message;
        alert.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            max-width: 300px;
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s ease;
        `;
        
        document.body.appendChild(alert);
        
        setTimeout(() => {
            alert.style.opacity = '1';
        }, 100);
        
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                if (document.body.contains(alert)) {
                    document.body.removeChild(alert);
                }
            }, 300);
        }, 5000);
    }

    // Phone number formatting
    function formatPhoneNumber(input) {
        let value = input.value.replace(/\D/g, '');
        if (value.length >= 10) {
            value = value.substring(0, 11);
            if (value.length === 10) {
                value = value.replace(/(\d{4})(\d{3})(\d{3})/, '$1 $2 $3');
            } else {
                value = value.replace(/(\d{4})(\d{3})(\d{4})/, '$1 $2 $3');
            }
        }
        input.value = value;
    }

    // Apply phone formatting to phone inputs
    const phoneInputs = document.querySelectorAll('input[name="phone"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function() {
            formatPhoneNumber(this);
        });
    });
});