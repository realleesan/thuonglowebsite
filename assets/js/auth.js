// auth.js - Phiên bản Final (Hỗ trợ tìm icon thông minh)

window.toggleAuthPassword = function(fieldId) {
    const input = document.getElementById(fieldId);
    if (!input) return;

    // Cách 1: Tìm icon theo ID quy ước (Cũ)
    let iconId;
    if (fieldId === 'password') iconId = 'password-icon';
    else if (fieldId === 'new_password') iconId = 'new-password-icon';
    else if (fieldId === 'confirm_password') iconId = 'confirm-password-icon';
    let icon = document.getElementById(iconId);

    // Cách 2: (Mới - Mạnh mẽ hơn) Nếu không thấy ID, tự tìm icon nằm chung trong wrapper
    if (!icon) {
        const wrapper = input.closest('.password-wrapper');
        if (wrapper) {
            // Tìm thẻ i hoặc svg nằm trong button toggle
            icon = wrapper.querySelector('.password-toggle i, .password-toggle svg');
        }
    }

    // Thực hiện đổi
    if (input.type === 'password') {
        input.type = 'text';
        if (icon) {
            // Hỗ trợ cả FontAwesome (class) và SVG (nếu có)
            if (icon.classList.contains('fa-eye')) {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        }
    } else {
        input.type = 'password';
        if (icon) {
            if (icon.classList.contains('fa-eye-slash')) {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
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
            toggleBtn.textContent = 'Ẩn lựa chọn vai trò';
            toggleBtn.style.background = '#0A66C2';
            toggleBtn.style.color = 'white';
        } else {
            toggleBtn.textContent = 'Nhấn để truy cập Demo Account';
            toggleBtn.style.background = 'none';
            toggleBtn.style.color = '#0A66C2';
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
    }
};

window.loginWithGoogle = () => alert('Tính năng đang phát triển');
window.loginWithX = () => alert('Tính năng đang phát triển');
window.loginWithLinkedIn = () => alert('Tính năng đang phát triển');

document.addEventListener('DOMContentLoaded', function() {
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            if (this.value.toLowerCase() === 'admin') window.selectRole('admin');
        });
    }
});