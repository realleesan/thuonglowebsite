/**
 * Admin Settings Module JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeSettingsModule();
});

function initializeSettingsModule() {
    // Initialize all components
    initializeTableFeatures();
    initializeBulkActions();
    initializeDeleteModal();
    initializeFormValidation();
    initializeTabs();
    initializeTooltips();
}

/**
 * Table Features
 */
function initializeTableFeatures() {
    // Select all checkbox functionality
    const selectAllCheckbox = document.getElementById('select-all');
    const settingCheckboxes = document.querySelectorAll('.setting-checkbox');
    
    if (selectAllCheckbox && settingCheckboxes.length > 0) {
        selectAllCheckbox.addEventListener('change', function() {
            settingCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActionsState();
        });
        
        // Individual checkbox change
        settingCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateSelectAllState();
                updateBulkActionsState();
            });
        });
    }
}

function updateSelectAllState() {
    const selectAllCheckbox = document.getElementById('select-all');
    const settingCheckboxes = document.querySelectorAll('.setting-checkbox');
    
    if (selectAllCheckbox && settingCheckboxes.length > 0) {
        const checkedCount = document.querySelectorAll('.setting-checkbox:checked').length;
        
        if (checkedCount === 0) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = false;
        } else if (checkedCount === settingCheckboxes.length) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = true;
        } else {
            selectAllCheckbox.indeterminate = true;
            selectAllCheckbox.checked = false;
        }
    }
}

/**
 * Bulk Actions
 */
function initializeBulkActions() {
    const bulkActionSelect = document.getElementById('bulk-action');
    const applyBulkButton = document.getElementById('apply-bulk');
    
    if (bulkActionSelect && applyBulkButton) {
        applyBulkButton.addEventListener('click', function() {
            const selectedAction = bulkActionSelect.value;
            const checkedSettings = document.querySelectorAll('.setting-checkbox:checked');
            
            if (!selectedAction) {
                showNotification('Vui lòng chọn hành động', 'warning');
                return;
            }
            
            if (checkedSettings.length === 0) {
                showNotification('Vui lòng chọn ít nhất một cài đặt', 'warning');
                return;
            }
            
            const settingKeys = Array.from(checkedSettings).map(cb => cb.value);
            executeBulkAction(selectedAction, settingKeys);
        });
    }
}

function updateBulkActionsState() {
    const bulkActionSelect = document.getElementById('bulk-action');
    const applyBulkButton = document.getElementById('apply-bulk');
    const checkedCount = document.querySelectorAll('.setting-checkbox:checked').length;
    
    if (bulkActionSelect && applyBulkButton) {
        const hasSelection = checkedCount > 0;
        bulkActionSelect.disabled = !hasSelection;
        applyBulkButton.disabled = !hasSelection;
        
        if (!hasSelection) {
            bulkActionSelect.value = '';
        }
    }
}

function executeBulkAction(action, settingKeys) {
    switch (action) {
        case 'delete':
            confirmBulkDelete(settingKeys);
            break;
        default:
            showNotification('Hành động không được hỗ trợ', 'error');
    }
}

function confirmBulkDelete(settingKeys) {
    const message = `Bạn có chắc chắn muốn xóa ${settingKeys.length} cài đặt đã chọn?\n\nHành động này không thể hoàn tác!`;
    
    if (confirm(message)) {
        // Demo: simulate bulk delete
        showNotification(`Đã xóa ${settingKeys.length} cài đặt (Demo)`, 'success');
        
        // Remove rows from table (demo)
        settingKeys.forEach(key => {
            const checkbox = document.querySelector(`.setting-checkbox[value="${key}"]`);
            if (checkbox) {
                const row = checkbox.closest('tr');
                if (row) {
                    row.remove();
                }
            }
        });
        
        // Reset bulk actions
        document.getElementById('select-all').checked = false;
        updateBulkActionsState();
        
        // In real app: send AJAX request to delete settings
        // deleteBulkSettings(settingKeys);
    }
}

/**
 * Delete Modal
 */
function initializeDeleteModal() {
    const deleteButtons = document.querySelectorAll('.delete-btn');
    const modal = document.getElementById('deleteModal');
    
    if (deleteButtons.length > 0 && modal) {
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const settingKey = this.dataset.key;
                const settingDescription = this.dataset.description;
                
                showDeleteModal(settingKey, settingDescription);
            });
        });
        
        // Modal close events
        const closeBtn = modal.querySelector('.modal-close');
        const cancelBtn = document.getElementById('cancelDelete');
        const confirmBtn = document.getElementById('confirmDelete');
        
        if (closeBtn) {
            closeBtn.addEventListener('click', () => hideDeleteModal());
        }
        
        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => hideDeleteModal());
        }
        
        if (confirmBtn) {
            confirmBtn.addEventListener('click', handleDeleteConfirm);
        }
        
        // Close on outside click
        window.addEventListener('click', function(e) {
            if (e.target === modal) {
                hideDeleteModal();
            }
        });
    }
}

function showDeleteModal(settingKey, settingDescription) {
    const modal = document.getElementById('deleteModal');
    const keyElement = document.getElementById('deleteSettingKey');
    
    if (modal && keyElement) {
        keyElement.textContent = settingKey;
        modal.style.display = 'block';
        
        // Store setting key for deletion
        modal.dataset.settingKey = settingKey;
    }
}

function hideDeleteModal() {
    const modal = document.getElementById('deleteModal');
    if (modal) {
        modal.style.display = 'none';
        delete modal.dataset.settingKey;
    }
}

function handleDeleteConfirm() {
    const modal = document.getElementById('deleteModal');
    const settingKey = modal.dataset.settingKey;
    
    if (settingKey) {
        // Demo: simulate delete
        showNotification(`Đã xóa cài đặt "${settingKey}" (Demo)`, 'success');
        
        // Remove row from table (demo)
        const checkbox = document.querySelector(`.setting-checkbox[value="${settingKey}"]`);
        if (checkbox) {
            const row = checkbox.closest('tr');
            if (row) {
                row.remove();
            }
        }
        
        hideDeleteModal();
        
        // In real app: send AJAX request to delete setting
        // deleteSetting(settingKey);
    }
}
/**
 * Form Validation
 */
function initializeFormValidation() {
    const forms = document.querySelectorAll('.admin-form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                return false;
            }
        });
        
        // Real-time validation
        const inputs = form.querySelectorAll('input, select, textarea');
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
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    
    inputs.forEach(input => {
        if (!validateField(input)) {
            isValid = false;
        }
    });
    
    return isValid;
}

function validateField(field) {
    const value = field.value.trim();
    const type = field.type || field.tagName.toLowerCase();
    let isValid = true;
    let errorMessage = '';
    
    // Required validation
    if (field.hasAttribute('required') && !value) {
        isValid = false;
        errorMessage = 'Trường này không được để trống';
    }
    
    // Type-specific validation
    if (value && isValid) {
        switch (type) {
            case 'email':
                if (!isValidEmail(value)) {
                    isValid = false;
                    errorMessage = 'Email không hợp lệ';
                }
                break;
            case 'url':
                if (!isValidUrl(value)) {
                    isValid = false;
                    errorMessage = 'URL không hợp lệ';
                }
                break;
            case 'number':
                if (!isValidNumber(value)) {
                    isValid = false;
                    errorMessage = 'Giá trị phải là số';
                }
                break;
        }
    }
    
    // Custom validation for setting key
    if (field.name === 'key' && value && isValid) {
        if (!/^[a-z0-9_]+$/.test(value)) {
            isValid = false;
            errorMessage = 'Tên cài đặt chỉ được chứa chữ thường, số và dấu gạch dưới';
        }
    }
    
    // Show/hide error
    if (isValid) {
        clearFieldError(field);
    } else {
        showFieldError(field, errorMessage);
    }
    
    return isValid;
}

function showFieldError(field, message) {
    clearFieldError(field);
    
    field.classList.add('error');
    
    const errorElement = document.createElement('div');
    errorElement.className = 'field-error';
    errorElement.textContent = message;
    
    field.parentNode.appendChild(errorElement);
}

function clearFieldError(field) {
    field.classList.remove('error');
    
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
}

// Validation helpers
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function isValidUrl(url) {
    try {
        new URL(url);
        return true;
    } catch {
        return false;
    }
}

function isValidNumber(value) {
    return !isNaN(value) && !isNaN(parseFloat(value));
}

/**
 * Tabs Functionality
 */
function initializeTabs() {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    if (tabBtns.length > 0 && tabContents.length > 0) {
        tabBtns.forEach(btn => {
            btn.addEventListener('click', function() {
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
}

/**
 * Tooltips
 */
function initializeTooltips() {
    const tooltipElements = document.querySelectorAll('[title]');
    
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
}

function showTooltip(e) {
    const element = e.target;
    const title = element.getAttribute('title');
    
    if (title) {
        // Remove title to prevent default tooltip
        element.setAttribute('data-original-title', title);
        element.removeAttribute('title');
        
        // Create custom tooltip
        const tooltip = document.createElement('div');
        tooltip.className = 'custom-tooltip';
        tooltip.textContent = title;
        document.body.appendChild(tooltip);
        
        // Position tooltip
        const rect = element.getBoundingClientRect();
        tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
        tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';
        
        // Store reference
        element._tooltip = tooltip;
    }
}

function hideTooltip(e) {
    const element = e.target;
    const originalTitle = element.getAttribute('data-original-title');
    
    if (originalTitle) {
        // Restore original title
        element.setAttribute('title', originalTitle);
        element.removeAttribute('data-original-title');
    }
    
    // Remove custom tooltip
    if (element._tooltip) {
        element._tooltip.remove();
        delete element._tooltip;
    }
}

/**
 * Notifications
 */
function showNotification(message, type = 'info', duration = 5000) {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    
    const icon = getNotificationIcon(type);
    notification.innerHTML = `
        <div class="notification-content">
            <i class="${icon}"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close">&times;</button>
    `;
    
    // Add to page
    let container = document.querySelector('.notifications-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'notifications-container';
        document.body.appendChild(container);
    }
    
    container.appendChild(notification);
    
    // Auto remove
    setTimeout(() => {
        removeNotification(notification);
    }, duration);
    
    // Manual close
    const closeBtn = notification.querySelector('.notification-close');
    closeBtn.addEventListener('click', () => {
        removeNotification(notification);
    });
}

function removeNotification(notification) {
    notification.classList.add('notification-removing');
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 300);
}

function getNotificationIcon(type) {
    const icons = {
        success: 'fas fa-check-circle',
        error: 'fas fa-exclamation-circle',
        warning: 'fas fa-exclamation-triangle',
        info: 'fas fa-info-circle'
    };
    return icons[type] || icons.info;
}

/**
 * Utility Functions
 */
function resetForm() {
    if (confirm('Bạn có chắc chắn muốn đặt lại form?')) {
        const form = document.querySelector('.admin-form');
        if (form) {
            form.reset();
            
            // Clear all errors
            const errorElements = form.querySelectorAll('.field-error');
            errorElements.forEach(error => error.remove());
            
            const errorFields = form.querySelectorAll('.error');
            errorFields.forEach(field => field.classList.remove('error'));
            
            // Trigger change events for dynamic fields
            const typeSelect = document.getElementById('type');
            if (typeSelect && typeof updateValueField === 'function') {
                updateValueField();
            }
        }
    }
}

// Export functions for global access
window.resetForm = resetForm;
window.showNotification = showNotification;