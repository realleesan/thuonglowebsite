/**
 * Admin Events JavaScript
 * Handles all JavaScript functionality for Events module
 */

// DOM Ready
document.addEventListener('DOMContentLoaded', function() {
    initializeEventsModule();
});

// Initialize Events Module
function initializeEventsModule() {
    // Initialize based on current page
    const currentPage = getCurrentPage();
    
    switch(currentPage) {
        case 'index':
            initializeIndexPage();
            break;
        case 'add':
            initializeAddPage();
            break;
        case 'edit':
            initializeEditPage();
            break;
        case 'view':
            initializeViewPage();
            break;
        case 'delete':
            initializeDeletePage();
            break;
    }
    
    // Initialize common functionality
    initializeCommonFeatures();
}

// Get current page type
function getCurrentPage() {
    const url = window.location.href;
    if (url.includes('action=add')) return 'add';
    if (url.includes('action=edit')) return 'edit';
    if (url.includes('action=view')) return 'view';
    if (url.includes('action=delete')) return 'delete';
    return 'index';
}

// Initialize Index Page
function initializeIndexPage() {
    // Select all checkbox functionality
    const selectAllCheckbox = document.getElementById('select-all');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const eventCheckboxes = document.querySelectorAll('.event-checkbox');
            eventCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActions();
        });
    }
    
    // Individual checkbox functionality
    const eventCheckboxes = document.querySelectorAll('.event-checkbox');
    eventCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
    });
    
    // Bulk actions
    const bulkActionSelect = document.getElementById('bulk-action');
    const applyBulkBtn = document.getElementById('apply-bulk');
    
    if (applyBulkBtn) {
        applyBulkBtn.addEventListener('click', function() {
            const selectedIds = getSelectedEventIds();
            const action = bulkActionSelect.value;
            
            if (selectedIds.length === 0) {
                alert('Vui lòng chọn ít nhất một sự kiện');
                return;
            }
            
            if (!action) {
                alert('Vui lòng chọn hành động');
                return;
            }
            
            if (confirm(`Bạn có chắc chắn muốn ${getBulkActionText(action)} ${selectedIds.length} sự kiện đã chọn?`)) {
                // In real implementation, this would make an AJAX call
                alert(`Đã ${getBulkActionText(action)} ${selectedIds.length} sự kiện`);
                location.reload();
            }
        });
    }
    
    // Delete buttons
    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const eventId = this.getAttribute('data-id');
            const eventName = this.getAttribute('data-name');
            showDeleteModal(eventId, eventName);
        });
    });
}

// Initialize Add Page
function initializeAddPage() {
    // Generate slug from title
    const titleInput = document.getElementById('title');
    if (titleInput) {
        titleInput.addEventListener('keyup', generateSlugFromTitle);
    }
    
    // Image preview
    const imageInput = document.getElementById('image');
    if (imageInput) {
        imageInput.addEventListener('change', function() {
            previewImage(this);
        });
    }
    
    // Date validation
    initializeDateValidation();
    
    // Price formatting
    initializePriceFormatting();
    
    // Form submission
    const form = document.querySelector('.admin-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validateEventForm()) {
                e.preventDefault();
            }
        });
    }
}

// Initialize Edit Page
function initializeEditPage() {
    // Same as add page plus additional features
    initializeAddPage();
    
    // Form change detection
    initializeFormChangeDetection();
    
    // Preview functionality
    const previewBtn = document.querySelector('[onclick="previewEvent()"]');
    if (previewBtn) {
        previewBtn.onclick = null;
        previewBtn.addEventListener('click', previewEvent);
    }
}

// Initialize View Page
function initializeViewPage() {
    // Tab functionality
    initializeTabFunctionality();
    
    // Image zoom
    initializeImageZoom();
    
    // Delete functionality
    const deleteBtn = document.querySelector('.delete-btn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            const eventId = this.getAttribute('data-id');
            const eventName = this.getAttribute('data-name');
            showDeleteModal(eventId, eventName);
        });
    }
    
    // Analytics chart
    initializeAnalyticsChart();
}

// Initialize Delete Page
function initializeDeletePage() {
    // Form validation
    const deleteForm = document.querySelector('.delete-form');
    if (deleteForm) {
        deleteForm.addEventListener('submit', validateDeleteForm);
    }
    
    // Real-time title validation
    const confirmTitleInput = document.getElementById('confirm_title');
    if (confirmTitleInput) {
        confirmTitleInput.addEventListener('input', validateTitleInput);
    }
    
    // Disable submit button initially
    const submitBtn = document.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = true;
    }
    
    // Warning before leaving page
    window.addEventListener('beforeunload', function(e) {
        const confirmTitle = document.getElementById('confirm_title');
        if (confirmTitle && confirmTitle.value.length > 0) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
}

// Initialize Common Features
function initializeCommonFeatures() {
    // Modal functionality
    initializeModals();
    
    // Tooltips
    initializeTooltips();
}

// Generate slug from title
function generateSlugFromTitle() {
    const title = document.getElementById('title').value;
    const slug = title
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/[\s-]+/g, '-')
        .replace(/^-+|-+$/g, '');
    
    const slugInput = document.getElementById('slug');
    if (slugInput) {
        slugInput.value = slug;
    }
}

// Preview image
function previewImage(input) {
    const preview = document.getElementById('preview-img');
    const placeholder = document.getElementById('preview-placeholder');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            if (preview) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            if (placeholder) {
                placeholder.style.display = 'none';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Save as draft
function saveDraft() {
    const statusSelect = document.getElementById('status');
    if (statusSelect) {
        statusSelect.value = 'upcoming';
        document.querySelector('form').submit();
    }
}

// Preview event
function previewEvent() {
    // In real implementation, this would open a preview in new tab
    alert('Chức năng xem trước sẽ được triển khai trong phiên bản tiếp theo');
}

// Initialize Date Validation
function initializeDateValidation() {
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    if (startDateInput) {
        startDateInput.addEventListener('change', function() {
            if (endDateInput && this.value && endDateInput.value && this.value >= endDateInput.value) {
                alert('Thời gian bắt đầu phải trước thời gian kết thúc');
                this.focus();
            }
        });
    }
    
    if (endDateInput) {
        endDateInput.addEventListener('change', function() {
            if (startDateInput && this.value && startDateInput.value && this.value <= startDateInput.value) {
                alert('Thời gian kết thúc phải sau thời gian bắt đầu');
                this.focus();
            }
        });
    }
}

// Initialize Price Formatting
function initializePriceFormatting() {
    const priceInput = document.getElementById('price');
    if (priceInput) {
        priceInput.addEventListener('input', function() {
            let value = this.value.replace(/[^0-9]/g, '');
            if (value) {
                this.value = parseInt(value);
            }
        });
    }
}

// Initialize Form Change Detection
function initializeFormChangeDetection() {
    let formChanged = false;
    
    document.querySelectorAll('input, textarea, select').forEach(element => {
        element.addEventListener('change', () => formChanged = true);
    });
    
    window.addEventListener('beforeunload', function(e) {
        if (formChanged) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
    
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', () => formChanged = false);
    }
}

// Initialize Tab Functionality
function initializeTabFunctionality() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    tabButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const tabName = this.textContent.trim().toLowerCase();
            let targetTab = '';
            
            if (tabName.includes('chi tiết')) targetTab = 'details';
            else if (tabName.includes('người tham gia')) targetTab = 'participants';
            else if (tabName.includes('phân tích')) targetTab = 'analytics';
            else if (tabName.includes('lịch sử')) targetTab = 'history';
            
            if (targetTab) {
                showTab(targetTab);
            }
        });
    });
}

// Show Tab
function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab
    const targetTab = document.getElementById(tabName + '-tab');
    const targetBtn = document.querySelector(`.tab-btn[onclick*="${tabName}"]`);
    
    if (targetTab) {
        targetTab.classList.add('active');
    }
    if (targetBtn) {
        targetBtn.classList.add('active');
    }
}

// Initialize Image Zoom
function initializeImageZoom() {
    const imageMain = document.querySelector('.event-image-main');
    if (imageMain) {
        imageMain.addEventListener('click', function() {
            const img = this.querySelector('img');
            if (img) {
                openImageZoom(img.src);
            }
        });
    }
    
    const zoomOverlay = document.getElementById('imageZoomOverlay');
    if (zoomOverlay) {
        zoomOverlay.addEventListener('click', closeImageZoom);
    }
    
    const zoomClose = document.querySelector('.zoom-close');
    if (zoomClose) {
        zoomClose.addEventListener('click', closeImageZoom);
    }
}

// Open Image Zoom
function openImageZoom(imageSrc) {
    const zoomedImage = document.getElementById('zoomedImage');
    const overlay = document.getElementById('imageZoomOverlay');
    
    if (zoomedImage && overlay) {
        zoomedImage.src = imageSrc;
        overlay.style.display = 'flex';
    }
}

// Close Image Zoom
function closeImageZoom() {
    const overlay = document.getElementById('imageZoomOverlay');
    if (overlay) {
        overlay.style.display = 'none';
    }
}

// Initialize Analytics Chart
function initializeAnalyticsChart() {
    const chartCanvas = document.getElementById('registrationChart');
    if (chartCanvas) {
        const ctx = chartCanvas.getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 150);
        gradient.addColorStop(0, 'rgba(53, 109, 241, 0.3)');
        gradient.addColorStop(1, 'rgba(53, 109, 241, 0.05)');
        
        // Simple line chart simulation
        ctx.strokeStyle = '#356DF1';
        ctx.lineWidth = 2;
        ctx.fillStyle = gradient;
        
        const data = [2, 5, 3, 8, 6, 12, 10, 15, 8, 5];
        const width = 300;
        const height = 150;
        const padding = 20;
        
        ctx.beginPath();
        ctx.moveTo(padding, height - padding - (data[0] / 20 * (height - 2 * padding)));
        
        for (let i = 1; i < data.length; i++) {
            const x = padding + (i / (data.length - 1)) * (width - 2 * padding);
            const y = height - padding - (data[i] / 20 * (height - 2 * padding));
            ctx.lineTo(x, y);
        }
        
        ctx.stroke();
        
        // Fill area under curve
        ctx.lineTo(width - padding, height - padding);
        ctx.lineTo(padding, height - padding);
        ctx.closePath();
        ctx.fill();
    }
}

// Initialize Modals
function initializeModals() {
    // Delete modal
    const deleteModal = document.getElementById('deleteModal');
    const cancelDeleteBtn = document.getElementById('cancelDelete');
    const modalClose = document.querySelector('.modal-close');
    
    if (cancelDeleteBtn) {
        cancelDeleteBtn.addEventListener('click', function() {
            if (deleteModal) {
                deleteModal.style.display = 'none';
            }
        });
    }
    
    if (modalClose) {
        modalClose.addEventListener('click', function() {
            if (deleteModal) {
                deleteModal.style.display = 'none';
            }
        });
    }
}

// Show Delete Modal
function showDeleteModal(eventId, eventName) {
    const modal = document.getElementById('deleteModal');
    const nameElement = document.getElementById('deleteEventName');
    
    if (modal && nameElement) {
        nameElement.textContent = eventName;
        modal.style.display = 'flex';
    }
}

// Update Bulk Actions
function updateBulkActions() {
    const selectedIds = getSelectedEventIds();
    const bulkActionSelect = document.getElementById('bulk-action');
    const applyBulkBtn = document.getElementById('apply-bulk');
    
    if (bulkActionSelect && applyBulkBtn) {
        const hasSelection = selectedIds.length > 0;
        bulkActionSelect.disabled = !hasSelection;
        applyBulkBtn.disabled = !hasSelection;
    }
}

// Get Selected Event IDs
function getSelectedEventIds() {
    const checkboxes = document.querySelectorAll('.event-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

// Get Bulk Action Text
function getBulkActionText(action) {
    const actions = {
        'upcoming': 'chuyển thành sắp diễn ra',
        'ongoing': 'chuyển thành đang diễn ra',
        'completed': 'chuyển thành đã kết thúc',
        'cancelled': 'hủy',
        'delete': 'xóa'
    };
    return actions[action] || action;
}

// Validate Event Form
function validateEventForm() {
    const title = document.getElementById('title').value.trim();
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    const location = document.getElementById('location').value.trim();
    const maxParticipants = document.getElementById('max_participants').value;
    
    if (!title) {
        alert('Vui lòng nhập tên sự kiện');
        document.getElementById('title').focus();
        return false;
    }
    
    if (!startDate) {
        alert('Vui lòng chọn thời gian bắt đầu');
        document.getElementById('start_date').focus();
        return false;
    }
    
    if (!endDate) {
        alert('Vui lòng chọn thời gian kết thúc');
        document.getElementById('end_date').focus();
        return false;
    }
    
    if (startDate >= endDate) {
        alert('Thời gian kết thúc phải sau thời gian bắt đầu');
        document.getElementById('end_date').focus();
        return false;
    }
    
    if (!location) {
        alert('Vui lòng nhập địa điểm');
        document.getElementById('location').focus();
        return false;
    }
    
    if (!maxParticipants || maxParticipants <= 0) {
        alert('Vui lòng nhập số lượng tham gia tối đa');
        document.getElementById('max_participants').focus();
        return false;
    }
    
    return true;
}

// Validate Delete Form
function validateDeleteForm(e) {
    const confirmTitle = document.getElementById('confirm_title').value;
    const expectedTitle = document.querySelector('[data-expected-title]')?.getAttribute('data-expected-title') || '';
    const confirmation = document.querySelector('input[name="confirmation"]:checked');
    
    if (confirmTitle !== expectedTitle) {
        e.preventDefault();
        alert('Tên sự kiện xác nhận không chính xác. Vui lòng nhập chính xác tên sự kiện.');
        return false;
    }
    
    if (!confirmation) {
        e.preventDefault();
        alert('Vui lòng xác nhận bằng cách tích vào checkbox.');
        return false;
    }
    
    const participantsConfirmation = document.querySelector('input[name="participants_confirmation"]');
    if (participantsConfirmation && !participantsConfirmation.checked) {
        e.preventDefault();
        alert('Vui lòng xác nhận rằng bạn hiểu việc xóa sẽ ảnh hưởng đến người đã đăng ký.');
        return false;
    }
    
    // Final confirmation
    let confirmMessage = 'Đây là lần xác nhận cuối cùng. Bạn có chắc chắn muốn xóa sự kiện này không?';
    
    if (!confirm(confirmMessage)) {
        e.preventDefault();
        return false;
    }
    
    return true;
}

// Validate Title Input
function validateTitleInput() {
    const confirmTitle = this.value;
    const expectedTitle = document.querySelector('[data-expected-title]')?.getAttribute('data-expected-title') || '';
    const submitBtn = document.querySelector('button[type="submit"]');
    
    if (confirmTitle === expectedTitle) {
        this.style.borderColor = '#10B981';
        this.style.backgroundColor = '#ECFDF5';
        if (submitBtn) submitBtn.disabled = false;
    } else {
        this.style.borderColor = '#EF4444';
        this.style.backgroundColor = '#FEF2F2';
        if (submitBtn) submitBtn.disabled = true;
    }
}

// Initialize Tooltips
function initializeTooltips() {
    // Simple tooltip implementation
    const elementsWithTooltips = document.querySelectorAll('[title]');
    elementsWithTooltips.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
}

// Show Tooltip
function showTooltip(e) {
    // Simple tooltip implementation
    const title = this.getAttribute('title');
    if (title) {
        this.setAttribute('data-original-title', title);
        this.removeAttribute('title');
        
        const tooltip = document.createElement('div');
        tooltip.className = 'custom-tooltip';
        tooltip.textContent = title;
        document.body.appendChild(tooltip);
        
        const rect = this.getBoundingClientRect();
        tooltip.style.left = rect.left + 'px';
        tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
    }
}

// Hide Tooltip
function hideTooltip(e) {
    const tooltip = document.querySelector('.custom-tooltip');
    if (tooltip) {
        tooltip.remove();
    }
    
    const originalTitle = this.getAttribute('data-original-title');
    if (originalTitle) {
        this.setAttribute('title', originalTitle);
        this.removeAttribute('data-original-title');
    }
}

// Export functions for global access
window.AdminEvents = {
    generateSlugFromTitle,
    previewImage,
    saveDraft,
    previewEvent,
    showTab,
    openImageZoom,
    closeImageZoom
};