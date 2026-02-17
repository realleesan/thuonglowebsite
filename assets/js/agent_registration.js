/**
 * Agent Registration Form JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Preserve form data from PHP session
    const preservedData = window.agentFormData || {};
    
    // Restore form values if they exist
    Object.keys(preservedData).forEach(function(key) {
        const element = document.querySelector(`[name="${key}"]`);
        if (element) {
            if (element.type === 'checkbox') {
                if (Array.isArray(preservedData[key])) {
                    preservedData[key].forEach(function(value) {
                        const checkbox = document.querySelector(`[name="${key}"][value="${value}"]`);
                        if (checkbox) checkbox.checked = true;
                    });
                }
            } else {
                element.value = preservedData[key];
            }
        }
    });
    
    // Form validation
    const form = document.querySelector('.agent-registration-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const errors = [];
            
            // Validate required fields (exclude readonly fields)
            const requiredFields = [
                { name: 'full_name', label: 'Họ và tên' },
                { name: 'phone_number', label: 'Số điện thoại' },
                { name: 'business_type', label: 'Loại hình kinh doanh' },
                { name: 'experience_years', label: 'Kinh nghiệm bán hàng' },
                { name: 'business_address', label: 'Địa chỉ kinh doanh' }
            ];
            
            requiredFields.forEach(function(field) {
                const element = document.querySelector(`[name="${field.name}"]`);
                // Skip validation for readonly fields
                if (element && !element.hasAttribute('readonly') && !element.value.trim()) {
                    isValid = false;
                    errors.push(`${field.label} là bắt buộc`);
                    element.classList.add('error');
                } else if (element) {
                    element.classList.remove('error');
                }
            });
            
            // Validate phone number format
            const phoneInput = document.querySelector('[name="phone_number"]');
            if (phoneInput && phoneInput.value) {
                const phonePattern = /^[0-9]{10,11}$/;
                if (!phonePattern.test(phoneInput.value)) {
                    isValid = false;
                    errors.push('Số điện thoại phải có 10-11 chữ số');
                    phoneInput.classList.add('error');
                }
            }
            
            // Show errors if any
            if (!isValid) {
                e.preventDefault();
                showFormErrors(errors);
            }
        });
    }
    
    // Real-time validation (skip readonly fields)
    const inputs = document.querySelectorAll('.form-control:not([readonly])');
    inputs.forEach(function(input) {
        input.addEventListener('blur', function() {
            validateField(this);
        });
        
        input.addEventListener('input', function() {
            // Remove error class when user starts typing
            this.classList.remove('error');
            const errorElement = this.parentNode.parentNode.querySelector('.field-error');
            if (errorElement) {
                errorElement.style.display = 'none';
            }
        });
    });
    
    // Terms checkbox validation
    const termsCheckbox = document.querySelector('#agent_terms');
    const submitButton = document.querySelector('.btn-submit');
    
    if (termsCheckbox && submitButton) {
        function updateSubmitButton() {
            submitButton.disabled = !termsCheckbox.checked;
            submitButton.style.opacity = termsCheckbox.checked ? '1' : '0.6';
        }
        
        termsCheckbox.addEventListener('change', updateSubmitButton);
        updateSubmitButton(); // Initial state
    }
});

function validateField(field) {
    const fieldName = field.getAttribute('name');
    const fieldValue = field.value.trim();
    let isValid = true;
    let errorMessage = '';
    
    // Check if field is required
    if (field.hasAttribute('required') && !fieldValue) {
        isValid = false;
        errorMessage = 'Trường này là bắt buộc';
    }
    
    // Specific validations
    switch (fieldName) {
        case 'phone_number':
            if (fieldValue && !/^[0-9]{10,11}$/.test(fieldValue)) {
                isValid = false;
                errorMessage = 'Số điện thoại phải có 10-11 chữ số';
            }
            break;
        case 'agent_email':
            if (fieldValue && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(fieldValue)) {
                isValid = false;
                errorMessage = 'Định dạng email không hợp lệ';
            }
            break;
    }
    
    // Show/hide error
    showFieldError(field, isValid ? '' : errorMessage);
    
    return isValid;
}

function showFieldError(field, message) {
    const fieldGroup = field.closest('.form-group');
    let errorElement = fieldGroup.querySelector('.field-error');
    
    if (message) {
        field.classList.add('error');
        
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.className = 'field-error';
            field.parentNode.parentNode.appendChild(errorElement);
        }
        
        errorElement.textContent = message;
        errorElement.style.display = 'flex';
    } else {
        field.classList.remove('error');
        if (errorElement) {
            errorElement.style.display = 'none';
        }
    }
}

function showFormErrors(errors) {
    // Create or update error summary
    let errorSummary = document.querySelector('.form-error-summary');
    
    if (!errorSummary) {
        errorSummary = document.createElement('div');
        errorSummary.className = 'form-error-summary';
        const form = document.querySelector('.agent-registration-form');
        form.insertBefore(errorSummary, form.firstChild);
    }
    
    errorSummary.innerHTML = `
        <div class="error-icon">⚠️</div>
        <div class="error-content">
            <h4>Vui lòng kiểm tra lại thông tin:</h4>
            <ul>
                ${errors.map(error => `<li>${error}</li>`).join('')}
            </ul>
        </div>
    `;
    
    errorSummary.style.display = 'flex';
    
    // Scroll to error summary
    errorSummary.scrollIntoView({ behavior: 'smooth', block: 'center' });
    
    // Auto hide after 10 seconds
    setTimeout(function() {
        if (errorSummary) {
            errorSummary.style.display = 'none';
        }
    }, 10000);
}