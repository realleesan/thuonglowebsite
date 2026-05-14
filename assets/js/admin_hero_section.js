/**
 * Admin Hero Section Management JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeHeroSection();
});

function initializeHeroSection() {
    // Initialize color pickers
    initializeColorPickers();
    
    // Initialize button management
    initializeButtonManagement();
    
    // Initialize form validation
    initializeFormValidation();
    
    // Initialize confirmations
    initializeConfirmations();
    
    // Initialize AJAX forms
    initializeAjaxForms();
}

/**
 * Initialize color pickers with live preview
 */
function initializeColorPickers() {
    const colorInputs = document.querySelectorAll('input[type="color"]');
    
    colorInputs.forEach(function(colorInput) {
        const textInput = colorInput.previousElementSibling;
        
        if (textInput && textInput.type === 'text') {
            // Sync color picker with text input
            colorInput.addEventListener('change', function() {
                textInput.value = this.value;
                updatePreview();
            });
            
            textInput.addEventListener('input', function() {
                if (isValidColor(this.value)) {
                    colorInput.value = this.value;
                    updatePreview();
                }
            });
        }
    });
}

/**
 * Initialize button management
 */
function initializeButtonManagement() {
    // Add new button
    const addButtonBtn = document.querySelector('[onclick="addNewButton()"]');
    if (addButtonBtn) {
        addButtonBtn.addEventListener('click', addNewButton);
    }
    
    // Initialize existing button actions
     document.querySelectorAll('[onclick^="saveButton("]').forEach(function(btn) {
         const match = btn.getAttribute('onclick').match(/saveButton\((\d+)\)/);
         if (!match) return;
         const buttonId = match[1];
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            saveButton(buttonId);
        });
    });
    
    document.querySelectorAll('[onclick^="deleteButton"]').forEach(function(btn) {
        const buttonId = btn.getAttribute('onclick').match(/deleteButton\((\d+)\)/)[1];
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            deleteButton(buttonId);
        });
    });
}

/**
 * Initialize form validation
 */
function initializeFormValidation() {
    const form = document.getElementById('heroSectionForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (validateForm()) {
                saveHeroSection();
            }
        });
    }
}

/**
 * Initialize confirmation dialogs
 */
function initializeConfirmations() {
    // Override default confirm with custom modal
    window.confirm = function(message) {
        return showConfirmDialog(message);
    };
}

/**
 * Initialize AJAX forms
 */
function initializeAjaxForms() {
    // Add loading states to all AJAX forms
    document.querySelectorAll('form[data-ajax]').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            submitAjaxForm(form);
        });
    });
}

/**
 * Add new button to the form
 */
function addNewButton() {
    const container = document.getElementById('buttonsContainer');
    const buttonCount = container.querySelectorAll('.button-item').length;
    const buttonId = 'new-' + Date.now();
    
    const buttonHtml = `
        <div class="button-item" data-button-id="${buttonId}">
            <div class="button-header">
                <span class="button-title">Button ${buttonCount + 1}</span>
                <div class="button-actions">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="saveNewButton('${buttonId}')">
                        <i class="fas fa-save"></i> Lưu
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeButton('${buttonId}')">
                        <i class="fas fa-trash"></i> Xóa
                    </button>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Text Button <span class="text-danger">*</span></label>
                        <input type="text" class="form-control button-text" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">URL Button <span class="text-danger">*</span></label>
                        <input type="text" class="form-control button-url" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Style</label>
                        <select class="form-select button-style">
                            <option value="primary">Primary</option>
                            <option value="secondary">Secondary</option>
                            <option value="outline">Outline</option>
                            <option value="ghost">Ghost</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Thứ tự</label>
                        <input type="number" class="form-control button-order" min="1" value="${buttonCount + 1}">
                    </div>
                </div>
            </div>
            <div class="button-preview">
                <span class="btn-preview primary">Preview</span>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', buttonHtml);
    
    // Initialize new button events
    initializeNewButtonEvents(buttonId);
    
    // Show toast
    showToast('Button mới đã được thêm', 'success');
}

/**
 * Initialize events for new button
 */
function initializeNewButtonEvents(buttonId) {
    const buttonElement = document.querySelector(`[data-button-id="${buttonId}"]`);
    
    // Preview button on input change
    const textInput = buttonElement.querySelector('.button-text');
    const styleSelect = buttonElement.querySelector('.button-style');
    const preview = buttonElement.querySelector('.btn-preview');
    
    if (textInput && styleSelect && preview) {
        const updatePreview = function() {
            preview.textContent = textInput.value || 'Button';
            preview.className = 'btn-preview ' + styleSelect.value;
        };
        
        textInput.addEventListener('input', updatePreview);
        styleSelect.addEventListener('change', updatePreview);
    }
}

/**
 * Remove button
 */
function removeButton(buttonId) {
    const buttonElement = document.querySelector(`[data-button-id="${buttonId}"]`);
    if (buttonElement) {
        buttonElement.remove();
        showToast('Button đã được xóa', 'info');
    }
}

/**
 * Save new button
 */
function saveNewButton(buttonId) {
    const buttonElement = document.querySelector(`[data-button-id="${buttonId}"]`);
    if (!buttonElement) return;
    
    const data = {
        hero_section_id: document.querySelector('input[name="id"]').value,
        button_text: buttonElement.querySelector('.button-text').value,
        button_url: buttonElement.querySelector('.button-url').value,
        button_style: buttonElement.querySelector('.button-style').value,
        sort_order: buttonElement.querySelector('.button-order').value
    };
    
    // Validate
    if (!data.button_text || !data.button_url) {
        showToast('Vui lòng điền đầy đủ thông tin button', 'error');
        return;
    }
    
    // Show loading
    buttonElement.classList.add('loading');
    
    // Send AJAX request
    fetch('?page=admin&module=hero-section&action=createButton', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        buttonElement.classList.remove('loading');
        
        if (result.success) {
            showToast('Button đã được tạo thành công!', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showToast('Lỗi: ' + result.message, 'error');
        }
    })
    .catch(error => {
        buttonElement.classList.remove('loading');
        console.error('Error:', error);
        showToast('Có lỗi xảy ra. Vui lòng thử lại.', 'error');
    });
}

/**
 * Save existing button
 */
function saveButton(buttonId) {
    const buttonElement = document.querySelector(`[data-button-id="${buttonId}"]`);
    if (!buttonElement) return;
    
    const data = {
        hero_section_id: document.querySelector('input[name="id"]').value,
        button_text: buttonElement.querySelector('.button-text').value,
        button_url: buttonElement.querySelector('.button-url').value,
        button_style: buttonElement.querySelector('.button-style').value,
        sort_order: buttonElement.querySelector('.button-order').value
    };
    
    // Show loading
    buttonElement.classList.add('loading');
    
    // Send AJAX request
    fetch('?page=admin&module=hero-section&action=updateButton&id=' + buttonId, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        buttonElement.classList.remove('loading');
        
        if (result.success) {
            showToast('Button đã được cập nhật thành công!', 'success');
        } else {
            showToast('Lỗi: ' + result.message, 'error');
        }
    })
    .catch(error => {
        buttonElement.classList.remove('loading');
        console.error('Error:', error);
        showToast('Có lỗi xảy ra. Vui lòng thử lại.', 'error');
    });
}

/**
 * Delete button
 */
function deleteButton(buttonId) {
    if (!showConfirmDialog('Bạn có chắc muốn xóa button này?')) {
        return;
    }
    
    const buttonElement = document.querySelector(`[data-button-id="${buttonId}"]`);
    if (!buttonElement) return;
    
    // Show loading
    buttonElement.classList.add('loading');
    
    // Send AJAX request
    fetch('?page=admin&module=hero-section&action=deleteButton&id=' + buttonId, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(result => {
        buttonElement.classList.remove('loading');
        
        if (result.success) {
            buttonElement.remove();
            showToast('Button đã được xóa thành công!', 'success');
        } else {
            showToast('Lỗi: ' + result.message, 'error');
        }
    })
    .catch(error => {
        buttonElement.classList.remove('loading');
        console.error('Error:', error);
        showToast('Có lỗi xảy ra. Vui lòng thử lại.', 'error');
    });
}

/**
 * Save hero section
 */
function saveHeroSection() {
    const form = document.getElementById('heroSectionForm');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    // Convert checkbox to boolean
    data.is_active = data.is_active ? 1 : 0;
    
    // Show loading
    form.classList.add('loading');
    
    // Send AJAX request
    const heroSectionId = data.id;
    fetch('?page=admin&module=hero-section&action=update&id=' + heroSectionId, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        form.classList.remove('loading');
        
        if (result.success) {
            showToast('Hero Section đã được cập nhật thành công!', 'success');
        } else {
            showToast('Lỗi: ' + result.message, 'error');
        }
    })
    .catch(error => {
        form.classList.remove('loading');
        console.error('Error:', error);
        showToast('Có lỗi xảy ra. Vui lòng thử lại.', 'error');
    });
}

/**
 * Validate form
 */
function validateForm() {
    const form = document.getElementById('heroSectionForm');
    const titleMain = form.querySelector('[name="title_main"]');
    const titleHighlight = form.querySelector('[name="title_highlight"]');
    
    let isValid = true;
    
    // Remove previous error states
    form.querySelectorAll('.is-invalid').forEach(function(el) {
        el.classList.remove('is-invalid');
    });
    
    // Validate required fields
    if (!titleMain.value.trim()) {
        titleMain.classList.add('is-invalid');
        isValid = false;
    }
    
    if (!titleHighlight.value.trim()) {
        titleHighlight.classList.add('is-invalid');
        isValid = false;
    }
    
    if (!isValid) {
        showToast('Vui lòng điền các trường bắt buộc', 'error');
    }
    
    return isValid;
}

/**
 * Update preview
 */
function updatePreview() {
    // This function can be expanded to show live preview
    console.log('Preview updated');
}

/**
 * Check if color is valid
 */
function isValidColor(color) {
    const s = new Option().style;
    s.color = color;
    return s.color !== '';
}

/**
 * Show confirm dialog
 */
function showConfirmDialog(message) {
    // Simple confirm for now - can be replaced with modal
    return confirm(message);
}

/**
 * Show toast notification
 */
function showToast(message, type = 'info') {
    const toastContainer = document.querySelector('.toast-container') || createToastContainer();
    
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
        <div class="toast-content">
            <i class="fas fa-${getToastIcon(type)}"></i>
            <span>${message}</span>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}

/**
 * Create toast container
 */
function createToastContainer() {
    const container = document.createElement('div');
    container.className = 'toast-container';
    document.body.appendChild(container);
    return container;
}

/**
 * Get toast icon based on type
 */
function getToastIcon(type) {
    const icons = {
        success: 'check-circle',
        error: 'exclamation-circle',
        warning: 'exclamation-triangle',
        info: 'info-circle'
    };
    return icons[type] || 'info-circle';
}

/**
 * Add CSS for slide out animation
 */
const style = document.createElement('style');
style.textContent = `
    @keyframes slideOut {
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
