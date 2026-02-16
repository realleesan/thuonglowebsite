// auth.js - Enhanced Authentication with Security Features

// Password strength checker
function checkPasswordStrength(password) {
    const requirements = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /\d/.test(password),
        special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
    };
    
    const score = Object.values(requirements).filter(Boolean).length;
    const strength = score < 3 ? 'weak' : score < 5 ? 'medium' : 'strong';
    
    return { requirements, score, strength };
}

// Update password strength indicator
function updatePasswordStrength(inputId, strengthId) {
    const input = document.getElementById(inputId);
    const strengthDiv = document.getElementById(strengthId);
    
    if (!input || !strengthDiv) return;
    
    const password = input.value;
    const { requirements, strength } = checkPasswordStrength(password);
    
    let strengthText = '';
    let strengthClass = '';
    
    if (password.length === 0) {
        strengthDiv.innerHTML = '';
        return;
    }
    
    switch (strength) {
        case 'weak':
            strengthText = 'Yếu';
            strengthClass = 'strength-weak';
            break;
        case 'medium':
            strengthText = 'Trung bình';
            strengthClass = 'strength-medium';
            break;
        case 'strong':
            strengthText = 'Mạnh';
            strengthClass = 'strength-strong';
            break;
    }
    
    const requirementsList = [
        { key: 'length', text: 'Ít nhất 8 ký tự' },
        { key: 'uppercase', text: 'Chữ hoa' },
        { key: 'lowercase', text: 'Chữ thường' },
        { key: 'number', text: 'Số' },
        { key: 'special', text: 'Ký tự đặc biệt' }
    ];
    
    const requirementsHtml = requirementsList.map(req => 
        `<span class="${requirements[req.key] ? 'req-met' : 'req-unmet'}">${req.text}</span>`
    ).join(' • ');
    
    strengthDiv.innerHTML = `
        <div class="strength-indicator ${strengthClass}">
            <span class="strength-text">Độ mạnh: ${strengthText}</span>
        </div>
        <div class="requirements">${requirementsHtml}</div>
    `;
}

// Check password match
function checkPasswordMatch(passwordId, confirmId, matchId) {
    const password = document.getElementById(passwordId);
    const confirm = document.getElementById(confirmId);
    const matchDiv = document.getElementById(matchId);
    
    if (!password || !confirm || !matchDiv) return;
    
    const passwordValue = password.value;
    const confirmValue = confirm.value;
    
    if (confirmValue.length === 0) {
        matchDiv.innerHTML = '';
        return;
    }
    
    if (passwordValue === confirmValue) {
        matchDiv.innerHTML = '<span class="match-success">✓ Mật khẩu khớp</span>';
        confirm.setCustomValidity('');
    } else {
        matchDiv.innerHTML = '<span class="match-error">✗ Mật khẩu không khớp</span>';
        confirm.setCustomValidity('Mật khẩu xác nhận không khớp');
    }
}

// CSRF token refresh (for long forms)
function refreshCsrfToken() {
    fetch('/auth/csrf-token', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.csrf_token) {
            const csrfInputs = document.querySelectorAll('input[name="csrf_token"]');
            csrfInputs.forEach(input => {
                input.value = data.csrf_token;
            });
        }
    })
    .catch(error => {
        console.warn('Failed to refresh CSRF token:', error);
    });
}

// Rate limiting warning
function showRateLimitWarning(seconds) {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            const originalText = submitBtn.textContent;
            
            const countdown = setInterval(() => {
                submitBtn.textContent = `Vui lòng chờ ${seconds}s`;
                seconds--;
                
                if (seconds < 0) {
                    clearInterval(countdown);
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            }, 1000);
        }
    });
}

// Form validation enhancement
function enhanceFormValidation() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Check CSRF token
            const csrfToken = form.querySelector('input[name="csrf_token"]');
            if (csrfToken && !csrfToken.value) {
                e.preventDefault();
                alert('Token bảo mật không hợp lệ. Vui lòng tải lại trang.');
                return false;
            }
            
            // Check password strength for registration
            if (form.id === 'registerForm') {
                const password = form.querySelector('#password');
                if (password) {
                    const { strength } = checkPasswordStrength(password.value);
                    if (strength === 'weak') {
                        e.preventDefault();
                        alert('Mật khẩu quá yếu. Vui lòng chọn mật khẩu mạnh hơn.');
                        return false;
                    }
                }
            }
        });
    });
}

// Auto-logout warning
function setupAutoLogoutWarning(warningTime = 300000, logoutTime = 1800000) { // 5 min warning, 30 min logout
    let warningShown = false;
    
    setTimeout(() => {
        if (!warningShown) {
            warningShown = true;
            if (confirm('Phiên làm việc sắp hết hạn. Bạn có muốn gia hạn không?')) {
                // Extend session
                fetch('/auth/extend-session', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        warningShown = false;
                        setupAutoLogoutWarning(warningTime, logoutTime);
                    }
                });
            }
        }
    }, warningTime);
    
    setTimeout(() => {
        window.location.href = '/auth/logout?reason=timeout';
    }, logoutTime);
}

// Khởi tạo trạng thái icon khi trang load
function initPasswordToggleIcons() {
    // Tìm tất cả các password input và đặt icon về trạng thái mặc định
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    passwordInputs.forEach(input => {
        const wrapper = input.closest('.password-wrapper');
        if (wrapper) {
            const icon = wrapper.querySelector('.password-toggle-icon');
            if (icon) {
                // Mật khẩu mặc định bị ẩn → hiển thị icon mắt gạch chéo
                icon.classList.add('is-hidden');
            }
        }
    });
}

window.toggleAuthPassword = function(fieldId) {
    const input = document.getElementById(fieldId);
    if (!input) return;

    const wrapper = input.closest('.password-wrapper');
    const toggleBtn = wrapper ? wrapper.querySelector('.password-toggle') : null;

    // Lấy icon theo ID hoặc tìm trong wrapper
    let iconId;
    if (fieldId === 'password') iconId = 'password-icon';
    else if (fieldId === 'new_password') iconId = 'new-password-icon';
    else if (fieldId === 'confirm_password') iconId = 'confirm-password-icon';

    let icon = document.getElementById(iconId);

    if (!icon && wrapper) {
        icon = wrapper.querySelector('.password-toggle-icon, .password-toggle i, .password-toggle svg');
    }

    // Thực hiện đổi
    if (input.type === 'password') {
        // Đang ẩn password → chuyển sang hiển thị password
        input.type = 'text';
        if (toggleBtn) {
            toggleBtn.setAttribute('aria-pressed', 'true');
            const hideLabel = toggleBtn.dataset.labelHide || 'Ẩn mật khẩu';
            toggleBtn.setAttribute('aria-label', hideLabel);
        }
        if (icon) {
            // Khi password hiển thị → hiển thị icon mắt mở (không có is-hidden)
            icon.classList.remove('is-hidden');
        }
    } else {
        // Đang hiển thị password → chuyển sang ẩn password
        input.type = 'password';
        if (toggleBtn) {
            toggleBtn.setAttribute('aria-pressed', 'false');
            const showLabel = toggleBtn.dataset.labelShow || 'Hiển thị mật khẩu';
            toggleBtn.setAttribute('aria-label', showLabel);
        }
        if (icon) {
            // Khi password ẩn → hiển thị icon mắt gạch chéo (có is-hidden)
            icon.classList.add('is-hidden');
        }
    }
};

// Logic Login Demo
window.toggleRoleSelector = function() {
    const roleSelector = document.getElementById('role-selector');
    const toggleBtn = document.querySelector('.demo-toggle');
    if (roleSelector && toggleBtn) {
        roleSelector.classList.toggle('hidden');
        if (!roleSelector.classList.contains('hidden')) {
            toggleBtn.textContent = 'Ẩn tài khoản demo';
            toggleBtn.style.background = '#356DF1';
            toggleBtn.style.color = 'white';
        } else {
            toggleBtn.textContent = 'Nhấn để chọn tài khoản demo';
            toggleBtn.style.background = 'none';
            toggleBtn.style.color = '#356DF1';
        }
    }
};

window.selectRole = function(role) {
    document.querySelectorAll('.role-option').forEach(opt => opt.classList.remove('active'));
    const radio = document.querySelector(`input[value="${role}"]`);
    if (radio) {
        radio.closest('.role-option').classList.add('active');
        radio.checked = true;
        document.getElementById('selected-role').value = role;
        
        // Tự động điền thông tin demo account
        fillDemoAccount(role);
    }
};

// Hàm điền thông tin demo account
window.fillDemoAccount = function(role) {
    const demoAccounts = {
        'user': {
            phone: '0901234567',
            password: '123456'
        },
        'agent': {
            phone: '0907654321',
            password: '123456'
        },
        'admin': {
            phone: 'admin',
            password: 'admin123'
        }
    };
    
    if (demoAccounts[role]) {
        const phoneInput = document.getElementById('phone');
        const passwordInput = document.getElementById('password');
        const roleInput = document.getElementById('selected-role');
        
        if (phoneInput) phoneInput.value = demoAccounts[role].phone;
        if (passwordInput) passwordInput.value = demoAccounts[role].password;
        if (roleInput) roleInput.value = role;
    }
};

window.loginWithGoogle = () => alert('Tính năng đang phát triển');
window.loginWithX = () => alert('Tính năng đang phát triển');
window.loginWithLinkedIn = () => alert('Tính năng đang phát triển');

// Agent role selection functionality
function initializeAgentRoleSelection() {
    const roleRadios = document.querySelectorAll('input[name="account_type"]');
    
    if (!roleRadios.length) return;
    
    roleRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            console.log('DEBUG: Role changed to:', this.value);
            // Just log the change, no UI updates needed
        });
    });
}

// Show Gmail requirement for agent registration (removed - not needed)
function showGmailRequirement() {
    // Removed per user request
}

// Hide Gmail requirement (removed - not needed)
function hideGmailRequirement() {
    // Removed per user request
}

// Validate email for agent registration
function validateAgentEmail() {
    const roleAgent = document.getElementById('role_agent');
    const emailInput = document.getElementById('email');
    
    if (!roleAgent || !emailInput || !roleAgent.checked) return true;
    
    const email = emailInput.value.trim();
    
    if (!email) {
        showEmailError('Email là bắt buộc cho đăng ký đại lý');
        return false;
    }
    
    if (!email.endsWith('@gmail.com')) {
        showEmailError('Chỉ chấp nhận địa chỉ Gmail (@gmail.com) cho đăng ký đại lý');
        return false;
    }
    
    if (!/^[a-zA-Z0-9._%+-]+@gmail\.com$/.test(email)) {
        showEmailError('Địa chỉ Gmail không hợp lệ');
        return false;
    }
    
    // Clear error if validation passes
    hideEmailError();
    return true;
}

function showEmailError(message) {
    const emailInput = document.getElementById('email');
    const emailGroup = emailInput?.closest('.form-group');
    
    if (emailGroup) {
        let errorDiv = emailGroup.querySelector('.email-error');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'field-error email-error';
            emailGroup.appendChild(errorDiv);
        }
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
        emailInput.classList.add('error');
    }
}

function hideEmailError() {
    const emailInput = document.getElementById('email');
    const emailGroup = emailInput?.closest('.form-group');
    const errorDiv = emailGroup?.querySelector('.email-error');
    
    if (errorDiv && emailInput) {
        errorDiv.style.display = 'none';
        errorDiv.textContent = '';
        emailInput.classList.remove('error');
    }
}

// Enhanced form validation for registration
function validateRegistrationForm() {
    let isValid = true;
    
    // Password validation
    const password = document.getElementById('password')?.value;
    const confirmPassword = document.getElementById('confirm_password')?.value;
    
    if (password && confirmPassword && password !== confirmPassword) {
        isValid = false;
    }
    
    // Agent email validation (using main email field)
    if (!validateAgentEmail()) {
        isValid = false;
    }
    
    return isValid;
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Khởi tạo trạng thái icon password
    initPasswordToggleIcons();
    
    // Enhanced form validation
    enhanceFormValidation();
    
    // Password strength checking
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            updatePasswordStrength('password', 'passwordStrength');
        });
    }
    
    // Password confirmation checking
    const confirmPasswordInput = document.getElementById('confirm_password');
    if (confirmPasswordInput && passwordInput) {
        const checkMatch = () => checkPasswordMatch('password', 'confirm_password', 'passwordMatch');
        passwordInput.addEventListener('input', checkMatch);
        confirmPasswordInput.addEventListener('input', checkMatch);
    }
    
    // New password strength checking (for reset forms)
    const newPasswordInput = document.getElementById('new_password');
    if (newPasswordInput) {
        newPasswordInput.addEventListener('input', function() {
            updatePasswordStrength('new_password', 'passwordStrength');
        });
        
        const confirmNewPasswordInput = document.getElementById('confirm_password');
        if (confirmNewPasswordInput) {
            const checkNewMatch = () => checkPasswordMatch('new_password', 'confirm_password', 'passwordMatch');
            newPasswordInput.addEventListener('input', checkNewMatch);
            confirmNewPasswordInput.addEventListener('input', checkNewMatch);
        }
    }
    
    // CSRF token refresh every 30 minutes
    setInterval(refreshCsrfToken, 30 * 60 * 1000);
    
    // Setup auto-logout for authenticated users
    if (document.body.classList.contains('authenticated')) {
        setupAutoLogoutWarning();
    }
    
    // Demo account functionality
    const phoneInput = document.getElementById('phone') || document.getElementById('login');
    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            if (this.value.toLowerCase() === 'admin') window.selectRole('admin');
        });
    }
    
    // Handle rate limiting from server
    const rateLimitWarning = document.querySelector('.alert-warning');
    if (rateLimitWarning && rateLimitWarning.textContent.includes('Quá nhiều')) {
        const match = rateLimitWarning.textContent.match(/(\d+)/);
        if (match) {
            const minutes = parseInt(match[1]);
            showRateLimitWarning(minutes * 60);
        }
    }
    
    // Initialize agent role selection
    initializeAgentRoleSelection();
    
    // Add email validation for agent registration
    const emailInput = document.getElementById('email');
    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            const roleAgent = document.getElementById('role_agent');
            if (roleAgent && roleAgent.checked) {
                validateAgentEmail();
            }
        });
        
        emailInput.addEventListener('input', function() {
            // Clear error on input if it was showing
            const emailGroup = this.closest('.form-group');
            const errorDiv = emailGroup?.querySelector('.email-error');
            if (errorDiv && errorDiv.style.display === 'block') {
                setTimeout(() => {
                    const roleAgent = document.getElementById('role_agent');
                    if (roleAgent && roleAgent.checked) {
                        validateAgentEmail();
                    }
                }, 300); // Debounce validation
            }
        });
    }
    
    // Enhanced form submission
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            // Debug: Log form data before submission
            const formData = new FormData(this);
            console.log('DEBUG: Form submission data:');
            for (let [key, value] of formData.entries()) {
                console.log(`${key}: ${value}`);
            }
            
            // Check if agent role is selected
            const accountType = formData.get('account_type');
            const email = formData.get('email');
            console.log(`DEBUG: Account type: ${accountType}, Email: ${email}`);
            
            if (!validateRegistrationForm()) {
                e.preventDefault();
                return false;
            }
        });
    }
});