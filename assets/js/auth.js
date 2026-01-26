// Auth page JavaScript functionality

function togglePassword() {
    const passwordInput = document.getElementById('password');
    const passwordIcon = document.getElementById('password-icon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        passwordIcon.className = 'fas fa-eye-slash';
    } else {
        passwordInput.type = 'password';
        passwordIcon.className = 'fas fa-eye';
    }
}

function toggleRoleSelector() {
    const roleSelector = document.getElementById('role-selector');
    const toggleBtn = document.querySelector('.demo-toggle');
    
    if (roleSelector.classList.contains('hidden')) {
        roleSelector.classList.remove('hidden');
        toggleBtn.textContent = 'Ẩn lựa chọn vai trò';
        toggleBtn.style.background = '#0A66C2';
        toggleBtn.style.color = 'white';
    } else {
        roleSelector.classList.add('hidden');
        toggleBtn.textContent = 'Nhấn để truy cập Demo Account';
        toggleBtn.style.background = 'none';
        toggleBtn.style.color = '#0A66C2';
    }
}

function selectRole(role) {
    // Remove active class from all options
    document.querySelectorAll('.role-option').forEach(option => {
        option.classList.remove('active');
    });
    
    // Add active class to selected option
    const selectedOption = document.querySelector(`input[value="${role}"]`).closest('.role-option');
    selectedOption.classList.add('active');
    
    // Check the radio button
    document.querySelector(`input[value="${role}"]`).checked = true;
    
    // Update hidden input
    document.getElementById('selected-role').value = role;
}

function loginWithGoogle() {
    alert('Tính năng đăng nhập Google sẽ được tích hợp trong phiên bản tiếp theo');
}

function loginWithFacebook() {
    alert('Tính năng đăng nhập Facebook sẽ được tích hợp trong phiên bản tiếp theo');
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Auto-detect admin login
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            if (this.value.toLowerCase() === 'admin') {
                selectRole('admin');
                
                // Show role selector if hidden
                const roleSelector = document.getElementById('role-selector');
                if (roleSelector && roleSelector.classList.contains('hidden')) {
                    toggleRoleSelector();
                }
            }
        });
    }
    
    // Initialize role from remembered value (will be set by PHP)
    const rememberedRole = document.getElementById('selected-role')?.value;
    if (rememberedRole) {
        selectRole(rememberedRole);
    }
});
// Additional functions for register page

function togglePassword(fieldId) {
    const passwordInput = document.getElementById(fieldId);
    const iconId = fieldId === 'password' ? 'password-icon' : 'confirm-password-icon';
    const passwordIcon = document.getElementById(iconId);
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        passwordIcon.className = 'fas fa-eye-slash';
    } else {
        passwordInput.type = 'password';
        passwordIcon.className = 'fas fa-eye';
    }
}

function registerWithGoogle() {
    alert('Tính năng đăng ký Google sẽ được tích hợp trong phiên bản tiếp theo');
}

function registerWithFacebook() {
    alert('Tính năng đăng ký Facebook sẽ được tích hợp trong phiên bản tiếp theo');
}

function useSavedRefCode() {
    const savedRefCode = document.querySelector('.debug-item:nth-child(3)')?.textContent.split(': ')[1] || '';
    const refInput = document.getElementById('ref_code');
    refInput.value = savedRefCode;
    
    // Ẩn thông báo
    const infoDiv = document.querySelector('.ref-code-info');
    if (infoDiv) {
        infoDiv.style.display = 'none';
    }
}

function clearSavedRefCode() {
    if (confirm('Bạn có chắc chắn muốn xóa mã giới thiệu đã lưu?')) {
        // Xóa cookie
        document.cookie = 'ref_code=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
        
        // Ẩn thông báo
        const infoDiv = document.querySelector('.ref-code-info');
        if (infoDiv) {
            infoDiv.style.display = 'none';
        }
        
        // Clear input
        const refInput = document.getElementById('ref_code');
        refInput.value = '';
    }
}

// Initialize register page specific functionality
document.addEventListener('DOMContentLoaded', function() {
    // Password strength checker
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strengthDiv = document.getElementById('passwordStrength');
            
            if (!strengthDiv) return;
            
            if (password.length === 0) {
                strengthDiv.textContent = '';
                return;
            }
            
            let strength = 0;
            let feedback = [];
            
            // Length check
            if (password.length >= 8) strength += 1;
            else feedback.push('ít nhất 8 ký tự');
            
            // Uppercase check
            if (/[A-Z]/.test(password)) strength += 1;
            else feedback.push('chữ hoa');
            
            // Lowercase check
            if (/[a-z]/.test(password)) strength += 1;
            else feedback.push('chữ thường');
            
            // Number check
            if (/\d/.test(password)) strength += 1;
            else feedback.push('số');
            
            // Special character check
            if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength += 1;
            else feedback.push('ký tự đặc biệt');
            
            let strengthText = '';
            let strengthClass = '';
            
            if (strength <= 2) {
                strengthText = 'Yếu - Cần: ' + feedback.slice(0, 2).join(', ');
                strengthClass = 'strength-weak';
            } else if (strength <= 3) {
                strengthText = 'Trung bình - Nên thêm: ' + feedback.slice(0, 1).join(', ');
                strengthClass = 'strength-medium';
            } else {
                strengthText = 'Mạnh - Mật khẩu tốt!';
                strengthClass = 'strength-strong';
            }
            
            strengthDiv.textContent = strengthText;
            strengthDiv.className = 'password-strength ' + strengthClass;
        });
    }
    
    // Confirm password validation
    const confirmPasswordInput = document.getElementById('confirm_password');
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.setCustomValidity('Mật khẩu xác nhận không khớp');
                this.style.borderColor = '#e74c3c';
            } else {
                this.setCustomValidity('');
                this.style.borderColor = '#e5e5e5';
            }
        });
    }
    
    // Phone number formatting
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, ''); // Remove non-digits
            
            // Format as Vietnamese phone number
            if (value.length > 0) {
                if (value.startsWith('84')) {
                    // International format
                    value = '+84 ' + value.substring(2);
                } else if (value.startsWith('0')) {
                    // Domestic format
                    if (value.length > 3) {
                        value = value.substring(0, 4) + ' ' + value.substring(4);
                    }
                    if (value.length > 8) {
                        value = value.substring(0, 9) + ' ' + value.substring(9);
                    }
                }
            }
            
            this.value = value;
        });
    }
    
    // Auto-fill ref code from URL
    const urlParams = new URLSearchParams(window.location.search);
    const refCode = urlParams.get('ref');
    
    if (refCode && refCode.trim() !== '') {
        const refInput = document.getElementById('ref_code');
        if (refInput) {
            refInput.value = refCode;
            refInput.classList.add('readonly');
            refInput.readOnly = true;
            
            // Show info message
            if (!document.querySelector('.ref-code-info')) {
                const infoDiv = document.createElement('div');
                infoDiv.className = 'ref-code-info';
                infoDiv.innerHTML = '<span class="icon">✓</span> Mã giới thiệu đã được tự động điền từ link giới thiệu';
                refInput.parentNode.appendChild(infoDiv);
            }
        }
    }
    
    // Form validation
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const terms = document.getElementById('terms').checked;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Mật khẩu xác nhận không khớp!');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Mật khẩu phải có ít nhất 6 ký tự!');
                return false;
            }

            if (!terms) {
                e.preventDefault();
                alert('Vui lòng đồng ý với điều khoản sử dụng!');
                return false;
            }
        });
    }

    // Email validation
    const emailInput = document.getElementById('email');
    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            const email = this.value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (email && !emailRegex.test(email)) {
                this.style.borderColor = '#e74c3c';
                this.setCustomValidity('Email không hợp lệ');
            } else {
                this.style.borderColor = '#e5e5e5';
                this.setCustomValidity('');
            }
        });
    }
});