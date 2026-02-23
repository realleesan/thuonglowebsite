// Admin Users Module JavaScript

document.addEventListener('DOMContentLoaded', function () {
    // Initialize users page functionality
    initUsersPage();
});

function initUsersPage() {
    // Check if we're on users page
    if (!document.querySelector('.users-page')) {
        return;
    }

    // Initialize all components
    initSelectAllCheckbox();
    initBulkActions();
    initDeleteModal();
    initFormValidation();
    initImagePreview();
    initTabFunctionality();
    initUsersSpecificFeatures();
}

// Select All Checkbox Functionality
function initSelectAllCheckbox() {
    const selectAllCheckbox = document.getElementById('select-all');
    const userCheckboxes = document.querySelectorAll('.user-checkbox');

    if (!selectAllCheckbox || userCheckboxes.length === 0) return;

    selectAllCheckbox.addEventListener('change', function () {
        userCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActions();
    });

    userCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const checkedCount = document.querySelectorAll('.user-checkbox:checked').length;
            selectAllCheckbox.checked = checkedCount === userCheckboxes.length;
            selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < userCheckboxes.length;
            updateBulkActions();
        });
    });
}

// Bulk Actions Functionality
function initBulkActions() {
    const bulkActionSelect = document.getElementById('bulk-action');
    const applyBulkBtn = document.getElementById('apply-bulk');

    if (!bulkActionSelect || !applyBulkBtn) return;

    bulkActionSelect.addEventListener('change', function () {
        const checkedCount = document.querySelectorAll('.user-checkbox:checked').length;
        applyBulkBtn.disabled = checkedCount === 0 || !this.value;
    });

    applyBulkBtn.addEventListener('click', function () {
        const selectedIds = Array.from(document.querySelectorAll('.user-checkbox:checked'))
            .map(checkbox => checkbox.value);
        const action = bulkActionSelect.value;

        if (selectedIds.length === 0 || !action) return;

        const actionText = {
            'activate': 'kích hoạt',
            'deactivate': 'vô hiệu hóa',
            'delete': 'xóa'
        };

        if (confirm(`Bạn có chắc chắn muốn ${actionText[action]} ${selectedIds.length} người dùng đã chọn?`)) {
            // Actual implementation: submit a form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '?page=admin&module=users&action=bulk';

            const idsInput = document.createElement('input');
            idsInput.type = 'hidden';
            idsInput.name = 'ids';
            idsInput.value = JSON.stringify(selectedIds);

            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'bulk_action';
            actionInput.value = action;

            form.appendChild(idsInput);
            form.appendChild(actionInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function updateBulkActions() {
    const checkedCount = document.querySelectorAll('.user-checkbox:checked').length;
    const bulkActionSelect = document.getElementById('bulk-action');
    const applyBulkBtn = document.getElementById('apply-bulk');

    if (bulkActionSelect && applyBulkBtn) {
        bulkActionSelect.disabled = checkedCount === 0;
        applyBulkBtn.disabled = checkedCount === 0 || !bulkActionSelect.value;
    }
}

function resetBulkSelections() {
    const selectAllCheckbox = document.getElementById('select-all');
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    const bulkActionSelect = document.getElementById('bulk-action');

    if (selectAllCheckbox) selectAllCheckbox.checked = false;
    userCheckboxes.forEach(checkbox => checkbox.checked = false);
    if (bulkActionSelect) bulkActionSelect.value = '';
    updateBulkActions();
}
// Delete Modal Functionality
function initDeleteModal() {
    const deleteModal = document.getElementById('deleteModal');
    const deleteButtons = document.querySelectorAll('.delete-btn');
    const deleteUserName = document.getElementById('deleteUserName');
    const cancelDeleteBtn = document.getElementById('cancelDelete');
    const confirmDeleteBtn = document.getElementById('confirmDelete');
    const modalClose = document.querySelector('.modal-close');

    if (!deleteModal) return;

    let currentDeleteId = null;

    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            currentDeleteId = this.dataset.id;
            if (deleteUserName) {
                deleteUserName.textContent = this.dataset.name;
            }
            deleteModal.style.display = 'flex';
        });
    });

    function closeModal() {
        deleteModal.style.display = 'none';
        currentDeleteId = null;
    }

    if (cancelDeleteBtn) {
        cancelDeleteBtn.addEventListener('click', closeModal);
    }

    if (modalClose) {
        modalClose.addEventListener('click', closeModal);
    }

    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function () {
            if (currentDeleteId) {
                window.location.href = `?page=admin&module=users&action=delete&id=${currentDeleteId}`;
            }
        });
    }

    // Close modal when clicking outside
    deleteModal.addEventListener('click', function (e) {
        if (e.target === this) {
            closeModal();
        }
    });
}

// Form Validation
function initFormValidation() {
    const forms = document.querySelectorAll('.admin-form');

    forms.forEach(form => {
        form.addEventListener('submit', function (e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('error');
                    isValid = false;
                } else {
                    field.classList.remove('error');
                }
            });

            // Password confirmation validation
            const password = form.querySelector('#password');
            const confirmPassword = form.querySelector('#confirm_password');

            if (password && confirmPassword && password.value && password.value !== confirmPassword.value) {
                confirmPassword.classList.add('error');
                isValid = false;
                alert('Mật khẩu xác nhận không khớp!');
            }

            if (!isValid) {
                e.preventDefault();
                alert('Vui lòng điền đầy đủ các trường bắt buộc!');
            }
        });
    });
}

// Image Preview Functionality
function initImagePreview() {
    const avatarInput = document.getElementById('avatar');
    const avatarPreview = document.getElementById('avatarPreview');

    if (!avatarInput || !avatarPreview) return;

    const originalAvatarHTML = avatarPreview.innerHTML;

    avatarInput.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                avatarPreview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">`;
            };
            reader.readAsDataURL(file);
        } else {
            avatarPreview.innerHTML = originalAvatarHTML;
        }
    });

    // Click to upload
    avatarPreview.addEventListener('click', function () {
        avatarInput.click();
    });
}
// Tab Functionality for User View
function initTabFunctionality() {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    if (tabBtns.length === 0) return;

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const targetTab = this.dataset.tab;

            // Remove active class from all tabs and contents
            tabBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));

            // Add active class to clicked tab and corresponding content
            this.classList.add('active');
            const targetContent = document.getElementById(targetTab);
            if (targetContent) {
                targetContent.classList.add('active');
            }
        });
    });
}

// Users-specific Features
function initUsersSpecificFeatures() {
    initPasswordStrengthIndicator();
    initRoleChangeWarning();
    initEmailValidation();
    initPhoneFormatting();
}

// Password Strength Indicator
function initPasswordStrengthIndicator() {
    const passwordInput = document.getElementById('password');

    if (!passwordInput) return;

    passwordInput.addEventListener('input', function () {
        const password = this.value;
        const strength = calculatePasswordStrength(password);

        // Remove existing strength indicator
        let strengthIndicator = document.getElementById('password-strength');
        if (strengthIndicator) {
            strengthIndicator.remove();
        }

        if (password.length > 0) {
            // Create strength indicator
            strengthIndicator = document.createElement('div');
            strengthIndicator.id = 'password-strength';
            strengthIndicator.className = `password-strength strength-${strength.level}`;
            strengthIndicator.innerHTML = `
                <div class="strength-bar">
                    <div class="strength-fill" style="width: ${strength.percentage}%"></div>
                </div>
                <small class="strength-text">${strength.text}</small>
            `;

            this.parentNode.appendChild(strengthIndicator);
        }
    });
}

function calculatePasswordStrength(password) {
    let score = 0;

    if (password.length >= 6) score += 1;
    if (password.length >= 8) score += 1;
    if (/[a-z]/.test(password)) score += 1;
    if (/[A-Z]/.test(password)) score += 1;
    if (/[0-9]/.test(password)) score += 1;
    if (/[^A-Za-z0-9]/.test(password)) score += 1;

    const levels = [
        { level: 'weak', text: 'Yếu', percentage: 20 },
        { level: 'fair', text: 'Trung bình', percentage: 40 },
        { level: 'good', text: 'Tốt', percentage: 60 },
        { level: 'strong', text: 'Mạnh', percentage: 80 },
        { level: 'very-strong', text: 'Rất mạnh', percentage: 100 }
    ];

    const levelIndex = Math.min(Math.floor(score / 1.2), levels.length - 1);
    return levels[levelIndex];
}

// Role Change Warning
function initRoleChangeWarning() {
    const roleSelect = document.getElementById('role');

    if (!roleSelect) return;

    const originalRole = roleSelect.value;

    roleSelect.addEventListener('change', function () {
        const newRole = this.value;

        if (originalRole && newRole !== originalRole) {
            if (newRole === 'admin') {
                alert('Cảnh báo: Bạn đang cấp quyền quản trị viên cho người dùng này!');
            } else if (originalRole === 'admin' && newRole !== 'admin') {
                alert('Cảnh báo: Bạn đang thu hồi quyền quản trị viên của người dùng này!');
            }
        }
    });
}

// Email Validation
function initEmailValidation() {
    const emailInput = document.getElementById('email');

    if (!emailInput) return;

    emailInput.addEventListener('blur', function () {
        const email = this.value.trim();

        if (email && !isValidEmail(email)) {
            this.classList.add('error');
            showFieldError(this, 'Email không hợp lệ');
        } else {
            this.classList.remove('error');
            hideFieldError(this);
        }
    });
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Phone Formatting
function initPhoneFormatting() {
    const phoneInput = document.getElementById('phone');

    if (!phoneInput) return;

    phoneInput.addEventListener('input', function () {
        let value = this.value.replace(/\D/g, ''); // Remove non-digits

        // Format Vietnamese phone number
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
// Utility Functions
function showFieldError(field, message) {
    hideFieldError(field); // Remove existing error

    const errorElement = document.createElement('small');
    errorElement.className = 'field-error text-danger';
    errorElement.textContent = message;

    field.parentNode.appendChild(errorElement);
}

function hideFieldError(field) {
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
}

// Global Functions for User Actions
window.deleteUser = function (id, name) {
    const deleteModal = document.getElementById('deleteModal');
    const deleteUserName = document.getElementById('deleteUserName');

    if (deleteModal && deleteUserName) {
        deleteUserName.textContent = name;
        deleteModal.style.display = 'flex';

        // Store the ID for confirmation
        window.currentDeleteId = id;
    }
};

window.editUser = function (id) {
    window.location.href = `?page=admin&module=users&action=edit&id=${id}`;
};

window.viewUser = function (id) {
    window.location.href = `?page=admin&module=users&action=view&id=${id}`;
};

// Reset Form Function
window.resetForm = function () {
    const isAddPage = document.querySelector('.users-add-page');
    const isEditPage = document.querySelector('.users-edit-page');

    if (isAddPage) {
        if (confirm('Bạn có chắc chắn muốn đặt lại form? Tất cả dữ liệu đã nhập sẽ bị xóa.')) {
            document.querySelector('.admin-form').reset();

            // Reset avatar preview
            const avatarPreview = document.getElementById('avatarPreview');
            if (avatarPreview) {
                avatarPreview.innerHTML = `
                    <i class="fas fa-user"></i>
                    <p>Chọn ảnh đại diện</p>
                `;
            }

            // Remove password strength indicator
            const strengthIndicator = document.getElementById('password-strength');
            if (strengthIndicator) {
                strengthIndicator.remove();
            }
        }
    } else if (isEditPage) {
        if (confirm('Bạn có chắc chắn muốn khôi phục về giá trị ban đầu? Tất cả thay đổi sẽ bị mất.')) {
            // This would restore original values in a real implementation
            location.reload();
        }
    }
};

// Export functions for use in other scripts if needed
window.UsersModule = {
    resetBulkSelections,
    updateBulkActions,
    showFieldError,
    hideFieldError,
    isValidEmail,
    calculatePasswordStrength
};

// Additional CSS for password strength indicator
const strengthCSS = `
<style>
.password-strength {
    margin-top: 8px;
}

.strength-bar {
    width: 100%;
    height: 4px;
    background: #E5E7EB;
    border-radius: 2px;
    overflow: hidden;
    margin-bottom: 4px;
}

.strength-fill {
    height: 100%;
    transition: width 0.3s ease;
    border-radius: 2px;
}

.strength-weak .strength-fill { background: #EF4444; }
.strength-fair .strength-fill { background: #F59E0B; }
.strength-good .strength-fill { background: #3B82F6; }
.strength-strong .strength-fill { background: #10B981; }
.strength-very-strong .strength-fill { background: #059669; }

.strength-text {
    font-size: 12px;
    font-weight: 500;
}

.strength-weak .strength-text { color: #EF4444; }
.strength-fair .strength-text { color: #F59E0B; }
.strength-good .strength-text { color: #3B82F6; }
.strength-strong .strength-text { color: #10B981; }
.strength-very-strong .strength-text { color: #059669; }

.field-error {
    display: block;
    margin-top: 4px;
}
</style>
`;

// Inject CSS
document.head.insertAdjacentHTML('beforeend', strengthCSS);

// Initialize change tracking for edit forms
document.addEventListener('DOMContentLoaded', function () {
    if (document.querySelector('.users-edit-page')) {
        const formInputs = document.querySelectorAll('input, select, textarea');
        let hasChanges = false;

        formInputs.forEach(input => {
            if (!input.classList.contains('readonly')) {
                input.addEventListener('change', function () {
                    hasChanges = true;
                });
            }
        });

        // Warn before leaving if there are unsaved changes
        window.addEventListener('beforeunload', function (e) {
            if (hasChanges) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    }
});