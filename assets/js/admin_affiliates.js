/**
 * Admin Affiliates Module JavaScript
 */

document.addEventListener('DOMContentLoaded', function () {

    // Initialize all affiliate functionality
    initAffiliateTable();
    initBulkActions();
    initDeleteModal();
    initFormValidation();
    initCommissionCalculator();

    /**
     * Initialize affiliate table functionality
     */
    function initAffiliateTable() {
        // Select all checkbox functionality
        const selectAllCheckbox = document.getElementById('select-all');
        const affiliateCheckboxes = document.querySelectorAll('.affiliate-checkbox');

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function () {
                affiliateCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateBulkActionsState();
            });
        }

        // Individual checkbox change
        affiliateCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                updateSelectAllState();
                updateBulkActionsState();
            });
        });

        // Update select all state based on individual checkboxes
        function updateSelectAllState() {
            if (!selectAllCheckbox) return;

            const checkedCount = document.querySelectorAll('.affiliate-checkbox:checked').length;
            const totalCount = affiliateCheckboxes.length;

            selectAllCheckbox.checked = checkedCount === totalCount;
            selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < totalCount;
        }

        // Update bulk actions state
        function updateBulkActionsState() {
            const checkedCount = document.querySelectorAll('.affiliate-checkbox:checked').length;
            const bulkActionSelect = document.getElementById('bulk-action');
            const applyBulkButton = document.getElementById('apply-bulk');

            if (bulkActionSelect && applyBulkButton) {
                bulkActionSelect.disabled = checkedCount === 0;
                applyBulkButton.disabled = checkedCount === 0 || !bulkActionSelect.value;
            }
        }
    }

    /**
     * Initialize bulk actions
     */
    function initBulkActions() {
        const bulkActionSelect = document.getElementById('bulk-action');
        const applyBulkButton = document.getElementById('apply-bulk');

        if (bulkActionSelect) {
            bulkActionSelect.addEventListener('change', function () {
                const checkedCount = document.querySelectorAll('.affiliate-checkbox:checked').length;
                if (applyBulkButton) {
                    applyBulkButton.disabled = checkedCount === 0 || !this.value;
                }
            });
        }

        if (applyBulkButton) {
            applyBulkButton.addEventListener('click', function () {
                const action = bulkActionSelect.value;
                const checkedIds = Array.from(document.querySelectorAll('.affiliate-checkbox:checked'))
                    .map(cb => cb.value);

                if (action && checkedIds.length > 0) {
                    handleBulkAction(action, checkedIds);
                }
            });
        }
    }

    /**
     * Handle bulk actions
     */
    function handleBulkAction(action, ids) {
        let message = '';

        switch (action) {
            case 'activate':
                message = `Kích hoạt ${ids.length} đại lý đã chọn?`;
                break;
            case 'deactivate':
                message = `Vô hiệu hóa ${ids.length} đại lý đã chọn?`;
                break;
            case 'delete':
                message = `Xóa ${ids.length} đại lý đã chọn? Hành động này không thể hoàn tác!`;
                break;
            default:
                return;
        }

        if (confirm(message)) {
            // Actual implementation: submit a form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '?page=admin&module=affiliates&action=bulk';

            const idsInput = document.createElement('input');
            idsInput.type = 'hidden';
            idsInput.name = 'ids';
            idsInput.value = JSON.stringify(ids);

            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'bulk_action';
            actionInput.value = action;

            form.appendChild(idsInput);
            form.appendChild(actionInput);
            document.body.appendChild(form);
            form.submit();
        }
    }

    /**
     * Initialize delete modal
     */
    function initDeleteModal() {
        const deleteButtons = document.querySelectorAll('.delete-btn');
        const deleteModal = document.getElementById('deleteModal');
        const cancelDeleteBtn = document.getElementById('cancelDelete');
        const confirmDeleteBtn = document.getElementById('confirmDelete');
        const modalClose = document.querySelector('.modal-close');

        if (!deleteModal) return;

        // Open delete modal
        deleteButtons.forEach(button => {
            button.addEventListener('click', function () {
                const affiliateId = this.dataset.id;
                const affiliateName = this.dataset.name;

                document.getElementById('deleteAffiliateName').textContent = affiliateName;
                deleteModal.style.display = 'flex';

                // Set up confirm delete handler
                confirmDeleteBtn.onclick = function () {
                    handleDeleteAffiliate(affiliateId, affiliateName);
                };
            });
        });

        // Close modal handlers
        [cancelDeleteBtn, modalClose].forEach(element => {
            if (element) {
                element.addEventListener('click', function () {
                    deleteModal.style.display = 'none';
                });
            }
        });

        // Close modal when clicking outside
        window.addEventListener('click', function (event) {
            if (event.target === deleteModal) {
                deleteModal.style.display = 'none';
            }
        });
    }

    /**
     * Handle delete affiliate
     */
    function handleDeleteAffiliate(id, name) {
        if (confirm(`Bạn có chắc chắn muốn xóa đại lý "${name}"?`)) {
            window.location.href = `?page=admin&module=affiliates&action=delete&id=${id}`;
        }
    }

    /**
     * Initialize form validation
     */
    function initFormValidation() {
        const forms = document.querySelectorAll('.admin-form');

        forms.forEach(form => {
            form.addEventListener('submit', function (e) {
                if (!validateForm(this)) {
                    e.preventDefault();
                }
            });

            // Real-time validation
            const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
            inputs.forEach(input => {
                input.addEventListener('blur', function () {
                    validateField(this);
                });

                input.addEventListener('input', function () {
                    clearFieldError(this);
                });
            });
        });
    }

    /**
     * Validate form
     */
    function validateForm(form) {
        let isValid = true;
        const requiredFields = form.querySelectorAll('input[required], select[required], textarea[required]');

        requiredFields.forEach(field => {
            if (!validateField(field)) {
                isValid = false;
            }
        });

        // Custom validation for referral code
        const referralCodeInput = form.querySelector('input[name="referral_code"]');
        if (referralCodeInput) {
            const code = referralCodeInput.value.trim();
            if (code && !/^[A-Z0-9]{3,20}$/.test(code)) {
                showFieldError(referralCodeInput, 'Mã giới thiệu chỉ được chứa chữ cái in hoa và số (3-20 ký tự)');
                isValid = false;
            }
        }

        // Custom validation for commission rate
        const commissionInput = form.querySelector('input[name="commission_rate"]');
        if (commissionInput) {
            const rate = parseFloat(commissionInput.value);
            if (rate && (rate <= 0 || rate > 50)) {
                showFieldError(commissionInput, 'Tỷ lệ hoa hồng phải từ 0.1% đến 50%');
                isValid = false;
            }
        }

        return isValid;
    }

    /**
     * Validate individual field
     */
    function validateField(field) {
        const value = field.value.trim();

        if (field.hasAttribute('required') && !value) {
            showFieldError(field, 'Trường này không được để trống');
            return false;
        }

        clearFieldError(field);
        return true;
    }

    /**
     * Show field error
     */
    function showFieldError(field, message) {
        field.classList.add('error');

        // Remove existing error message
        const existingError = field.parentNode.querySelector('.field-error');
        if (existingError) {
            existingError.remove();
        }

        // Add new error message
        const errorElement = document.createElement('small');
        errorElement.className = 'field-error text-danger';
        errorElement.textContent = message;
        field.parentNode.appendChild(errorElement);
    }

    /**
     * Clear field error
     */
    function clearFieldError(field) {
        field.classList.remove('error');
        const errorElement = field.parentNode.querySelector('.field-error');
        if (errorElement) {
            errorElement.remove();
        }
    }

    /**
     * Initialize commission calculator
     */
    function initCommissionCalculator() {
        const sampleSalesInput = document.getElementById('sample_sales');
        const commissionRateInput = document.getElementById('commission_rate');
        const calculatedCommissionSpan = document.getElementById('calculated_commission');

        if (sampleSalesInput && commissionRateInput && calculatedCommissionSpan) {
            function calculateCommission() {
                const sales = parseFloat(sampleSalesInput.value) || 0;
                const rate = parseFloat(commissionRateInput.value) || 0;
                const commission = sales * rate / 100;

                calculatedCommissionSpan.textContent =
                    new Intl.NumberFormat('vi-VN').format(commission) + ' VNĐ';
            }

            sampleSalesInput.addEventListener('input', calculateCommission);
            commissionRateInput.addEventListener('input', calculateCommission);

            // Initial calculation
            calculateCommission();
        }
    }

    /**
     * Show notification
     */
    function showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${getNotificationIcon(type)}"></i>
                <span>${message}</span>
            </div>
            <button class="notification-close">&times;</button>
        `;

        // Add styles
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${getNotificationColor(type)};
            color: white;
            padding: 12px 16px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1001;
            display: flex;
            align-items: center;
            gap: 12px;
            max-width: 400px;
            animation: slideIn 0.3s ease-out;
        `;

        // Add to page
        document.body.appendChild(notification);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.style.animation = 'slideOut 0.3s ease-in';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 300);
            }
        }, 5000);

        // Manual close
        const closeBtn = notification.querySelector('.notification-close');
        closeBtn.addEventListener('click', () => {
            notification.remove();
        });
    }

    /**
     * Get notification icon
     */
    function getNotificationIcon(type) {
        switch (type) {
            case 'success': return 'check-circle';
            case 'error': return 'exclamation-triangle';
            case 'warning': return 'exclamation-triangle';
            default: return 'info-circle';
        }
    }

    /**
     * Get notification color
     */
    function getNotificationColor(type) {
        switch (type) {
            case 'success': return '#10B981';
            case 'error': return '#EF4444';
            case 'warning': return '#F59E0B';
            default: return '#3B82F6';
        }
    }
});

/**
 * Global functions for affiliate management
 */

// Generate new referral code
function generateNewCode() {
    const code = 'AGENT' + String(Math.floor(Math.random() * 999) + 1).padStart(3, '0');
    const input = document.getElementById('referral_code');
    const display = document.getElementById('ref_code_display');

    if (input) {
        input.value = code;
        input.dispatchEvent(new Event('input'));
    }

    if (display) {
        display.textContent = code;
    }
}

// Copy referral code
function copyReferralCode() {
    const codeElement = document.querySelector('.referral-code');
    if (codeElement) {
        const code = codeElement.textContent;
        navigator.clipboard.writeText(code).then(() => {
            alert('Đã sao chép mã giới thiệu: ' + code);
        }).catch(() => {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = code;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            alert('Đã sao chép mã giới thiệu: ' + code);
        });
    }
}

// Copy link
function copyLink(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        const link = element.textContent;
        navigator.clipboard.writeText(link).then(() => {
            alert('Đã sao chép link giới thiệu!');
        }).catch(() => {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = link;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            alert('Đã sao chép link giới thiệu!');
        });
    }
}

// Reset form
function resetForm() {
    if (confirm('Bạn có chắc chắn muốn đặt lại form?')) {
        const form = document.querySelector('.admin-form');
        if (form) {
            form.reset();

            // Clear any error states
            form.querySelectorAll('.error').forEach(field => {
                field.classList.remove('error');
            });

            form.querySelectorAll('.field-error').forEach(error => {
                error.remove();
            });

            // Regenerate referral code if on add page
            if (document.getElementById('referral_code')) {
                generateNewCode();
            }

            // Recalculate commission
            const event = new Event('input');
            const commissionInput = document.getElementById('commission_rate');
            if (commissionInput) {
                commissionInput.dispatchEvent(event);
            }
        }
    }
}

// Add CSS animations
const adminAffiliatesStyles = document.createElement('style');
adminAffiliatesStyles.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .notification-content {
        display: flex;
        align-items: center;
        gap: 8px;
        flex: 1;
    }
    
    .notification-close {
        background: none;
        border: none;
        color: white;
        font-size: 18px;
        cursor: pointer;
        padding: 0;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: background 0.2s;
    }
    
    .notification-close:hover {
        background: rgba(255, 255, 255, 0.2);
    }
    
    .field-error {
        display: block !important;
        margin-top: 4px !important;
    }
    
    .form-group input.error,
    .form-group select.error,
    .form-group textarea.error {
        border-color: #EF4444 !important;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
    }
`;
document.head.appendChild(adminAffiliatesStyles);