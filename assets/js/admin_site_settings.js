/**
 * Admin Site Settings - Logo Management JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin Site Settings JS loaded');
    
    // Initialize file upload previews
    initFileUploadPreviews();
    
    // Initialize form validations
    initFormValidations();
});

/**
 * Initialize file upload previews
 */
function initFileUploadPreviews() {
    const fileInputs = document.querySelectorAll('.file-input');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file
                if (!validateFile(file)) {
                    e.target.value = '';
                    return;
                }
                
                // Show file name
                const label = e.target.closest('.file-upload-label');
                const span = label.querySelector('span');
                if (span) {
                    span.textContent = file.name;
                    label.style.borderColor = '#356DF1';
                    label.style.background = '#F0F4FF';
                }
            }
        });
    });
}

/**
 * Preview image before upload
 */
function previewImage(input, settingKey) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Validate file
        if (!validateFile(file)) {
            input.value = '';
            return;
        }
        
        const reader = new FileReader();
        
        reader.onload = function(e) {
            // Find the preview image for this setting
            const card = input.closest('.setting-card');
            const previewImg = card.querySelector('.preview-image');
            
            if (previewImg) {
                previewImg.src = e.target.result;
                
                // Add animation
                previewImg.style.opacity = '0';
                setTimeout(() => {
                    previewImg.style.transition = 'opacity 0.3s ease';
                    previewImg.style.opacity = '1';
                }, 50);
            }
        };
        
        reader.readAsDataURL(file);
    }
}

/**
 * Validate file
 */
function validateFile(file) {
    // Check file type
    const allowedTypes = ['image/svg+xml', 'image/png', 'image/jpeg', 'image/jpg', 'image/gif', 'image/webp', 'image/x-icon'];
    if (!allowedTypes.includes(file.type)) {
        showNotification('Chỉ chấp nhận file ảnh (SVG, PNG, JPG, GIF, WEBP, ICO)', 'error');
        return false;
    }
    
    // Check file size (2MB)
    const maxSize = 2 * 1024 * 1024;
    if (file.size > maxSize) {
        showNotification('File không được vượt quá 2MB', 'error');
        return false;
    }
    
    return true;
}

/**
 * Initialize form validations
 */
function initFormValidations() {
    const forms = document.querySelectorAll('.logo-upload-form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const fileInput = form.querySelector('.file-input');
            
            if (!fileInput.files || fileInput.files.length === 0) {
                e.preventDefault();
                showNotification('Vui lòng chọn file để upload', 'warning');
                return false;
            }
            
            // Show loading state
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
            }
        });
    });
}

/**
 * Show notification
 */
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `flash-message flash-${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 90px;
        right: 20px;
        z-index: 10000;
        min-width: 300px;
        max-width: 500px;
        padding: 16px 20px;
        background: ${type === 'error' ? '#FEE2E2' : type === 'warning' ? '#FEF3C7' : type === 'success' ? '#D1FAE5' : '#DBEAFE'};
        color: ${type === 'error' ? '#991B1B' : type === 'warning' ? '#92400E' : type === 'success' ? '#065F46' : '#1E40AF'};
        border-left: 4px solid ${type === 'error' ? '#DC2626' : type === 'warning' ? '#F59E0B' : type === 'success' ? '#10B981' : '#3B82F6'};
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        font-family: 'Inter', sans-serif;
        font-size: 14px;
        animation: slideInRight 0.3s ease;
    `;
    
    const icon = type === 'error' ? 'exclamation-circle' : 
                 type === 'warning' ? 'exclamation-triangle' : 
                 type === 'success' ? 'check-circle' : 'info-circle';
    
    notification.innerHTML = `
        <i class="fas fa-${icon}" style="margin-right: 8px;"></i>
        ${message}
        <button onclick="this.parentElement.remove()" style="
            position: absolute;
            top: 8px;
            right: 8px;
            background: none;
            border: none;
            color: inherit;
            font-size: 18px;
            cursor: pointer;
            padding: 4px 8px;
            opacity: 0.7;
        ">&times;</button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

// Add CSS animation
const styleElement = document.createElement('style');
styleElement.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(styleElement);
