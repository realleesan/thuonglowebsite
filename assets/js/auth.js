// auth.js - Phiên bản Final (Hỗ trợ tìm icon thông minh)

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

// ... (Giữ nguyên các phần logic Role Demo và Social Login bên dưới của bạn) ...
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

document.addEventListener('DOMContentLoaded', function() {
    // Khởi tạo trạng thái icon password
    initPasswordToggleIcons();
    
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            if (this.value.toLowerCase() === 'admin') window.selectRole('admin');
        });
    }
});