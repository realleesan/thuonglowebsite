/**
 * Admin News Module JavaScript
 * Handles all interactive functionality for news management
 */

document.addEventListener('DOMContentLoaded', function () {
    initializeNewsModule();
});

function initializeNewsModule() {
    // Initialize common functionality
    initializeSelectAll();
    initializeBulkActions();
    initializeDeleteModals();
    initializeImagePreview();
    initializeTabs();
    initializeFormValidation();
    initializeSlugGeneration();
    initializeImageZoom();
    initializeAnalyticsChart();
}

/**
 * Select All Functionality
 */
function initializeSelectAll() {
    const selectAllCheckbox = document.getElementById('select-all');
    const itemCheckboxes = document.querySelectorAll('.news-checkbox');

    if (selectAllCheckbox && itemCheckboxes.length > 0) {
        selectAllCheckbox.addEventListener('change', function () {
            itemCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActionsState();
        });

        itemCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                const checkedCount = document.querySelectorAll('.news-checkbox:checked').length;
                selectAllCheckbox.checked = checkedCount === itemCheckboxes.length;
                selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < itemCheckboxes.length;
                updateBulkActionsState();
            });
        });
    }
}

/**
 * Bulk Actions
 */
function initializeBulkActions() {
    const bulkActionSelect = document.getElementById('bulk-action');
    const applyBulkButton = document.getElementById('apply-bulk');

    if (bulkActionSelect && applyBulkButton) {
        applyBulkButton.addEventListener('click', function () {
            const selectedAction = bulkActionSelect.value;
            const checkedItems = document.querySelectorAll('.news-checkbox:checked');

            if (!selectedAction) {
                showNotification('Vui lòng chọn hành động', 'warning');
                return;
            }

            if (checkedItems.length === 0) {
                showNotification('Vui lòng chọn ít nhất một tin tức', 'warning');
                return;
            }

            const itemIds = Array.from(checkedItems).map(cb => cb.value);
            executeBulkAction(selectedAction, itemIds);
        });
    }
}

function updateBulkActionsState() {
    const checkedItems = document.querySelectorAll('.news-checkbox:checked');
    const bulkActionSelect = document.getElementById('bulk-action');
    const applyBulkButton = document.getElementById('apply-bulk');

    if (bulkActionSelect && applyBulkButton) {
        const hasSelection = checkedItems.length > 0;
        bulkActionSelect.disabled = !hasSelection;
        applyBulkButton.disabled = !hasSelection;

        if (!hasSelection) {
            bulkActionSelect.value = '';
        }
    }
}

function executeBulkAction(action, itemIds) {
    let actionText = '';
    let confirmMessage = '';

    switch (action) {
        case 'publish':
            actionText = 'xuất bản';
            confirmMessage = `Bạn có chắc chắn muốn xuất bản ${itemIds.length} tin tức đã chọn?`;
            break;
        case 'draft':
            actionText = 'chuyển thành nháp';
            confirmMessage = `Bạn có chắc chắn muốn chuyển ${itemIds.length} tin tức thành bản nháp?`;
            break;
        case 'archive':
            actionText = 'lưu trữ';
            confirmMessage = `Bạn có chắc chắn muốn lưu trữ ${itemIds.length} tin tức đã chọn?`;
            break;
        case 'delete':
            actionText = 'xóa';
            confirmMessage = `Bạn có chắc chắn muốn xóa ${itemIds.length} tin tức đã chọn? Hành động này không thể hoàn tác!`;
            break;
        default:
            showNotification('Hành động không hợp lệ', 'error');
            return;
    }

    if (confirm(confirmMessage)) {
        // Actual implementation: submit a form
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '?page=admin&module=news&action=bulk';

        const idsInput = document.createElement('input');
        idsInput.type = 'hidden';
        idsInput.name = 'ids';
        idsInput.value = JSON.stringify(itemIds);

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
 * Delete Modals
 */
function initializeDeleteModals() {
    const deleteButtons = document.querySelectorAll('.delete-btn');
    const deleteModal = document.getElementById('deleteModal');
    const cancelDeleteBtn = document.getElementById('cancelDelete');
    const confirmDeleteBtn = document.getElementById('confirmDelete');
    const modalClose = document.querySelector('.modal-close');

    if (deleteButtons.length > 0 && deleteModal) {
        deleteButtons.forEach(button => {
            button.addEventListener('click', function () {
                const newsId = this.getAttribute('data-id');
                const newsName = this.getAttribute('data-name');

                document.getElementById('deleteNewsName').textContent = newsName;

                // Update confirm delete link if it exists
                if (confirmDeleteBtn && confirmDeleteBtn.tagName === 'A') {
                    const currentHref = confirmDeleteBtn.getAttribute('href');
                    confirmDeleteBtn.setAttribute('href', currentHref.replace(/id=\d+/, `id=${newsId}`));
                }

                showModal(deleteModal);
            });
        });

        if (cancelDeleteBtn) {
            cancelDeleteBtn.addEventListener('click', () => hideModal(deleteModal));
        }

        if (modalClose) {
            modalClose.addEventListener('click', () => hideModal(deleteModal));
        }

        // Close modal when clicking outside
        deleteModal.addEventListener('click', function (e) {
            if (e.target === this) {
                hideModal(this);
            }
        });
    }
}

/**
 * Image Preview
 */
function initializeImagePreview() {
    const imageInput = document.getElementById('image');

    if (imageInput) {
        imageInput.addEventListener('change', function () {
            previewImage(this);
        });
    }
}

function previewImage(input) {
    const preview = document.getElementById('preview-img');
    const placeholder = document.getElementById('preview-placeholder');

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            if (preview && placeholder) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                placeholder.style.display = 'none';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

/**
 * Tab Functionality
 */
function initializeTabs() {
    const tabButtons = document.querySelectorAll('.tab-btn');

    tabButtons.forEach(button => {
        button.addEventListener('click', function () {
            const tabName = this.textContent.trim().toLowerCase();
            let tabId = '';

            // Map tab names to IDs
            if (tabName.includes('nội dung')) tabId = 'content';
            else if (tabName.includes('seo')) tabId = 'seo';
            else if (tabName.includes('phân tích')) tabId = 'analytics';
            else if (tabName.includes('lịch sử')) tabId = 'history';

            if (tabId) {
                showTab(tabId, this);
            }
        });
    });
}

function showTab(tabName, clickedButton = null) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });

    // Show selected tab
    const targetTab = document.getElementById(tabName + '-tab');
    if (targetTab) {
        targetTab.classList.add('active');
    }

    // Activate clicked button or find the right one
    if (clickedButton) {
        clickedButton.classList.add('active');
    } else {
        const buttons = document.querySelectorAll('.tab-btn');
        buttons.forEach(btn => {
            const btnText = btn.textContent.trim().toLowerCase();
            if ((tabName === 'content' && btnText.includes('nội dung')) ||
                (tabName === 'seo' && btnText.includes('seo')) ||
                (tabName === 'analytics' && btnText.includes('phân tích')) ||
                (tabName === 'history' && btnText.includes('lịch sử'))) {
                btn.classList.add('active');
            }
        });
    }
}

/**
 * Form Validation
 */
function initializeFormValidation() {
    const forms = document.querySelectorAll('.admin-form, .delete-form');

    forms.forEach(form => {
        form.addEventListener('submit', function (e) {
            if (!validateForm(this)) {
                e.preventDefault();
                return false;
            }
        });

        // Real-time validation
        const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
        inputs.forEach(input => {
            input.addEventListener('blur', function () {
                validateField(this);
            });

            input.addEventListener('input', function () {
                if (this.classList.contains('error')) {
                    validateField(this);
                }
            });
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('input[required], textarea[required], select[required]');

    requiredFields.forEach(field => {
        if (!validateField(field)) {
            isValid = false;
        }
    });

    // Special validation for delete form
    if (form.classList.contains('delete-form')) {
        const confirmTitle = form.querySelector('#confirm_title');
        const expectedTitle = confirmTitle ? confirmTitle.getAttribute('placeholder') : '';

        if (confirmTitle && confirmTitle.value !== expectedTitle) {
            showFieldError(confirmTitle, 'Tiêu đề xác nhận không chính xác');
            isValid = false;
        }
    }

    return isValid;
}

function validateField(field) {
    const value = field.value.trim();
    let isValid = true;
    let errorMessage = '';

    // Required field validation
    if (field.hasAttribute('required') && !value) {
        errorMessage = 'Trường này không được để trống';
        isValid = false;
    }

    // Email validation
    if (field.type === 'email' && value && !isValidEmail(value)) {
        errorMessage = 'Email không hợp lệ';
        isValid = false;
    }

    // Slug validation
    if (field.name === 'slug' && value && !isValidSlug(value)) {
        errorMessage = 'Slug chỉ được chứa chữ thường, số và dấu gạch ngang';
        isValid = false;
    }

    if (isValid) {
        clearFieldError(field);
    } else {
        showFieldError(field, errorMessage);
    }

    return isValid;
}

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

function clearFieldError(field) {
    field.classList.remove('error');
    const errorElement = field.parentNode.querySelector('.field-error');
    if (errorElement) {
        errorElement.remove();
    }
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function isValidSlug(slug) {
    const slugRegex = /^[a-z0-9-]+$/;
    return slugRegex.test(slug);
}

/**
 * Slug Generation
 */
function initializeSlugGeneration() {
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');

    if (titleInput && slugInput) {
        titleInput.addEventListener('input', function () {
            if (!slugInput.value || slugInput.dataset.autoGenerated === 'true') {
                const slug = generateSlug(this.value);
                slugInput.value = slug;
                slugInput.dataset.autoGenerated = 'true';
            }
        });

        slugInput.addEventListener('input', function () {
            this.dataset.autoGenerated = 'false';
        });
    }
}

function generateSlug(text) {
    return text
        .toLowerCase()
        .trim()
        // Remove Vietnamese diacritics
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        // Replace special characters
        .replace(/[^a-z0-9\s-]/g, '')
        // Replace spaces and multiple hyphens with single hyphen
        .replace(/[\s-]+/g, '-')
        // Remove leading/trailing hyphens
        .replace(/^-+|-+$/g, '');
}

/**
 * Image Zoom
 */
function initializeImageZoom() {
    const zoomableImages = document.querySelectorAll('.news-image-main');
    const zoomOverlay = document.getElementById('imageZoomOverlay');

    zoomableImages.forEach(image => {
        image.addEventListener('click', function () {
            const imgSrc = this.querySelector('img').src;
            openImageZoom(imgSrc);
        });
    });

    if (zoomOverlay) {
        zoomOverlay.addEventListener('click', function (e) {
            if (e.target === this || e.target.classList.contains('zoom-close')) {
                closeImageZoom();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && zoomOverlay.style.display === 'flex') {
                closeImageZoom();
            }
        });
    }
}

function openImageZoom(imageSrc) {
    const zoomOverlay = document.getElementById('imageZoomOverlay');
    const zoomedImage = document.getElementById('zoomedImage');

    if (zoomOverlay && zoomedImage) {
        zoomedImage.src = imageSrc;
        zoomOverlay.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeImageZoom() {
    const zoomOverlay = document.getElementById('imageZoomOverlay');

    if (zoomOverlay) {
        zoomOverlay.style.display = 'none';
        document.body.style.overflow = '';
    }
}

/**
 * Analytics Chart
 */
function initializeAnalyticsChart() {
    const chartCanvas = document.getElementById('viewsChart');

    if (chartCanvas) {
        drawSimpleChart(chartCanvas);
    }
}

function drawSimpleChart(canvas) {
    const ctx = canvas.getContext('2d');
    const width = canvas.width;
    const height = canvas.height;
    const padding = 20;

    // Get data from data attributes or use empty array
    const chartElement = document.querySelector('[data-news-chart-data]');
    const data = chartElement ? JSON.parse(chartElement.dataset.newsChartData || '[]') : [0, 0, 0, 0, 0, 0, 0];
    const maxValue = Math.max(...data) || 1;

    // Clear canvas
    ctx.clearRect(0, 0, width, height);

    // Create gradient
    const gradient = ctx.createLinearGradient(0, 0, 0, height);
    gradient.addColorStop(0, 'rgba(53, 109, 241, 0.3)');
    gradient.addColorStop(1, 'rgba(53, 109, 241, 0.05)');

    // Draw line
    ctx.strokeStyle = '#356DF1';
    ctx.lineWidth = 2;
    ctx.fillStyle = gradient;

    ctx.beginPath();

    for (let i = 0; i < data.length; i++) {
        const x = padding + (i / (data.length - 1)) * (width - 2 * padding);
        const y = height - padding - (data[i] / maxValue * (height - 2 * padding));

        if (i === 0) {
            ctx.moveTo(x, y);
        } else {
            ctx.lineTo(x, y);
        }
    }

    ctx.stroke();

    // Fill area under curve
    ctx.lineTo(width - padding, height - padding);
    ctx.lineTo(padding, height - padding);
    ctx.closePath();
    ctx.fill();

    // Draw points
    ctx.fillStyle = '#356DF1';
    for (let i = 0; i < data.length; i++) {
        const x = padding + (i / (data.length - 1)) * (width - 2 * padding);
        const y = height - padding - (data[i] / maxValue * (height - 2 * padding));

        ctx.beginPath();
        ctx.arc(x, y, 3, 0, 2 * Math.PI);
        ctx.fill();
    }
}

/**
 * Modal Utilities
 */
function showModal(modal) {
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    // Focus first input in modal
    const firstInput = modal.querySelector('input, textarea, select, button');
    if (firstInput) {
        setTimeout(() => firstInput.focus(), 100);
    }
}

function hideModal(modal) {
    modal.style.display = 'none';
    document.body.style.overflow = '';
}

/**
 * Notification System
 */
function showNotification(message, type = 'info', duration = 3000) {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());

    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${getNotificationIcon(type)}"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;

    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        padding: 12px 16px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        display: flex;
        align-items: center;
        gap: 12px;
        max-width: 400px;
        animation: slideInRight 0.3s ease-out;
    `;

    // Set colors based on type
    switch (type) {
        case 'success':
            notification.style.backgroundColor = '#ECFDF5';
            notification.style.color = '#065F46';
            notification.style.borderLeft = '4px solid #10B981';
            break;
        case 'error':
            notification.style.backgroundColor = '#FEF2F2';
            notification.style.color = '#991B1B';
            notification.style.borderLeft = '4px solid #EF4444';
            break;
        case 'warning':
            notification.style.backgroundColor = '#FFFBEB';
            notification.style.color = '#92400E';
            notification.style.borderLeft = '4px solid #F59E0B';
            break;
        default:
            notification.style.backgroundColor = '#EFF6FF';
            notification.style.color = '#1E40AF';
            notification.style.borderLeft = '4px solid #3B82F6';
    }

    document.body.appendChild(notification);

    // Auto remove after duration
    if (duration > 0) {
        setTimeout(() => {
            if (notification.parentElement) {
                notification.style.animation = 'slideOutRight 0.3s ease-in';
                setTimeout(() => notification.remove(), 300);
            }
        }, duration);
    }
}

function getNotificationIcon(type) {
    switch (type) {
        case 'success': return 'check-circle';
        case 'error': return 'exclamation-circle';
        case 'warning': return 'exclamation-triangle';
        default: return 'info-circle';
    }
}

/**
 * Utility Functions
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatNumber(number) {
    return new Intl.NumberFormat('vi-VN').format(number);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Add CSS animations
const adminNewsStyles = document.createElement('style');
adminNewsStyles.textContent = `
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
    
    .notification-content {
        display: flex;
        align-items: center;
        gap: 8px;
        flex: 1;
    }
    
    .notification-close {
        background: none;
        border: none;
        cursor: pointer;
        opacity: 0.7;
        transition: opacity 0.2s;
    }
    
    .notification-close:hover {
        opacity: 1;
    }
    
    .field-error {
        color: #EF4444 !important;
        font-size: 12px !important;
        margin-top: 4px !important;
        display: block !important;
    }
`;
document.head.appendChild(adminNewsStyles);

// Global functions for inline event handlers
window.generateSlugFromTitle = function () {
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');

    if (titleInput && slugInput) {
        const slug = generateSlug(titleInput.value);
        slugInput.value = slug;
        slugInput.dataset.autoGenerated = 'true';
    }
};

window.previewImage = previewImage;
window.showTab = showTab;
window.openImageZoom = openImageZoom;
window.closeImageZoom = closeImageZoom;

window.saveDraft = function () {
    const statusSelect = document.getElementById('status');
    if (statusSelect) {
        statusSelect.value = 'draft';
        document.querySelector('form').submit();
    }
};

window.previewNews = function () {
    showNotification('Chức năng xem trước sẽ được triển khai trong phiên bản tiếp theo', 'info');
};