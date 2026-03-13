// Admin Products Module JavaScript
// Tái cấu trúc cho sản phẩm số (Data Nguồn Hàng)

// ============================================================
// GLOBAL BEFOREUNLOAD KILL-SWITCH for Products pages
// Runs immediately (not waiting for DOMContentLoaded) so it
// fires BEFORE any beforeunload listener added by other modules
// (admin_events.js, admin_users.js, etc.)
// ============================================================
(function () {
    var urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('module') === 'products') {
        // Capturing phase so we run first
        window.addEventListener('beforeunload', function (e) {
            // Prevent any other handler from showing the dialog
            e.stopImmediatePropagation();
            // Remove ANY onbeforeunload assignment
            window.onbeforeunload = null;
        }, true); // capture = true → runs before bubbling/AT-target listeners
    }
})();

// Global functions - accessible from HTML
function handleImageFileSelect(input) {
    const imagePreview = document.getElementById('imagePreview');
    const imageUrlInput = document.getElementById('image_url');
    
    if (!imagePreview) {
        console.log('Image preview element not found');
        return;
    }
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            imagePreview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
            // Clear the URL input when file is selected
            if (imageUrlInput) imageUrlInput.value = '';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Legacy function name for compatibility
function previewImage(input) {
    handleImageFileSelect(input);
}

document.addEventListener('DOMContentLoaded', function () {
    console.log('DOM Content Loaded - initializing products page');
    initProductsPage();
    
    // Fallback: Direct event delegation for delete buttons
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-btn') || e.target.closest('.delete-btn')) {
            console.log('Delete button clicked via delegation');
            const btn = e.target.classList.contains('delete-btn') ? e.target : e.target.closest('.delete-btn');
            const modal = document.getElementById('deleteModal');
            const nameElement = document.getElementById('deleteProductName');
            
            if (modal && btn.dataset.id) {
                e.preventDefault();
                if (nameElement) {
                    nameElement.textContent = btn.dataset.name || 'sản phẩm này';
                }
                modal.classList.add('show');
                document.body.style.overflow = 'hidden';
                
                // Store ID for confirmation
                modal.dataset.deleteId = btn.dataset.id;
            }
        }
        
        // Handle confirm delete - delete directly via AJAX
        if (e.target.id === 'confirmDeleteBtn') {
            const modal = document.getElementById('deleteModal');
            const deleteId = modal ? modal.dataset.deleteId : null;
            if (deleteId) {
                if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) {
                    // Create and submit a form to delete
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '?page=admin&module=products&action=delete_direct&id=' + deleteId;
                    document.body.appendChild(form);
                    form.submit();
                }
            }
        }
        
        // Handle modal close
        if (e.target.classList.contains('modal-close') || e.target.onclick === 'closeDeleteModal()') {
            const modal = document.getElementById('deleteModal');
            if (modal) {
                modal.classList.remove('show');
                document.body.style.overflow = '';
                delete modal.dataset.deleteId;
            }
        }
    });
});

function initProductsPage() {
    // Check if we're on products page
    if (!document.querySelector('.products-page')) {
        console.log('Not on products page, skipping initialization');
        return;
    }

    console.log('Initializing products page...');

    // AGGRESSIVE: Completely disable beforeunload
    window.onbeforeunload = null;
    window.formChanged = false;
    
    // Move modal to body to avoid admin layout conflicts
    const modal = document.getElementById('deleteModal');
    if (modal && modal.parentNode !== document.body) {
        document.body.appendChild(modal);
        console.log('Modal moved to body');
    }
    
    // Initialize all components
    initTabs();
    initDeleteModal();
    initJsonPreview();
    initImagePreview();
    
    // Also try to initialize delete modal after a short delay
    setTimeout(function() {
        console.log('Re-initializing delete modal after delay...');
        initDeleteModal();
    }, 500);
}

// Tab Switching
function initTabs() {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    
    if (tabButtons.length) {
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetTab = this.dataset.tab;
                
                // Update buttons
                tabButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                // Update content
                tabContents.forEach(content => {
                    content.classList.remove('active');
                    if (content.id === targetTab) {
                        content.classList.add('active');
                    }
                });
            });
        });
    }
    
    // View page tabs - also initialize these
    initViewTabs();
}

function initViewTabs() {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    if (!tabBtns.length) return;
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const target = this.dataset.tab;
            
            // Update buttons
            tabBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Update panes
            tabPanes.forEach(pane => {
                pane.classList.remove('active');
                if (pane.id === target) {
                    pane.classList.add('active');
                }
            });
        });
    });
}

// Remove debug logs from console
function initDeleteModal() {
    // Old modal functionality removed - using new productDeleteModal instead
    return;
}

// JSON Preview
function initJsonPreview() {
    const benefitsInput = document.getElementById('benefits');
    const benefitsPreview = document.getElementById('benefitsPreview');
    
    function renderBenefitsPreview(val) {
        if (!benefitsPreview) return;
        if (!val || !val.trim()) {
            benefitsPreview.innerHTML = '<p class="preview-empty" style="color:#999;margin:0;">Xem trước lợi ích sẽ hiển thị ở đây...</p>';
            return;
        }
        try {
            const parsed = JSON.parse(val);
            if (Array.isArray(parsed) && parsed.length) {
                benefitsPreview.innerHTML = '<ul style="margin:0;padding-left:20px;">' + 
                    parsed.map(item => '<li style="margin-bottom:4px;">' + escapeHtml(String(item)) + '</li>').join('') + 
                    '</ul>';
            } else {
                benefitsPreview.innerHTML = '<p class="preview-empty" style="color:#999;margin:0;">JSON phải là mảng</p>';
            }
        } catch (e) {
            benefitsPreview.innerHTML = '<p style="color:#e53e3e;margin:0;font-size:12px;">⚠ JSON không hợp lệ</p>';
        }
    }
    
    if (benefitsInput) {
        benefitsInput.addEventListener('input', function() { renderBenefitsPreview(this.value); });
        // Trigger immediately for pre-filled data
        renderBenefitsPreview(benefitsInput.value);
    }
    
    // Data structure preview
    const dataStructureInput = document.getElementById('data_structure');
    const dataStructurePreview = document.getElementById('dataStructurePreview');
    
    function renderStructurePreview(val) {
        if (!dataStructurePreview) return;
        if (!val || !val.trim()) {
            dataStructurePreview.innerHTML = '<p class="preview-empty" style="color:#999;margin:0;">Xem trước cấu trúc sẽ hiển thị ở đây...</p>';
            return;
        }
        try {
            const parsed = JSON.parse(val);
            if (Array.isArray(parsed)) {
                let html = '';
                parsed.forEach((section, index) => {
                    html += '<div class="structure-section" style="margin-bottom:12px;">';
                    html += '<strong style="color:#374151;">' + (index + 1) + '. ' + escapeHtml(section.title || 'Nhóm thông tin') + '</strong>';
                    if (section.items && Array.isArray(section.items)) {
                        html += '<ul style="margin-top:4px;padding-left:20px;">';
                        section.items.forEach(item => {
                            html += '<li style="color:#4b5563;">' + escapeHtml(item.title || '') + '</li>';
                        });
                        html += '</ul>';
                    }
                    html += '</div>';
                });
                dataStructurePreview.innerHTML = html || '<p class="preview-empty" style="color:#999;margin:0;">Chưa có cấu trúc</p>';
            }
        } catch (e) {
            dataStructurePreview.innerHTML = '<p style="color:#e53e3e;margin:0;font-size:12px;">⚠ JSON không hợp lệ</p>';
        }
    }
    
    if (dataStructureInput) {
        dataStructureInput.addEventListener('input', function() { renderStructurePreview(this.value); });
        // Trigger immediately for pre-filled data
        renderStructurePreview(dataStructureInput.value);
    }
}


// Image Preview
function initImagePreview() {
    const imageInput = document.getElementById('imageFile');
    const imageUrlInput = document.getElementById('imageUrlInput');
    const imagePreview = document.getElementById('imagePreview');
    const imageUrl = document.getElementById('image_url');
    
    if (!imagePreview) {
        console.log('Image preview element not found');
        return;
    }
    
    console.log('Image preview initialized');
    
    // File input handler - add event listener directly
    if (imageInput) {
        console.log('Image file input found, adding change listener');
        imageInput.addEventListener('change', function(e) {
            console.log('File selected:', this.files);
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    } else {
        console.log('Image file input NOT found');
    }
    
    // URL input handler
    if (imageUrlInput) {
        imageUrlInput.addEventListener('input', function() {
            if (this.value) {
                // Create image element with proper error handling
                const img = document.createElement('img');
                img.src = this.value;
                img.alt = 'Preview';
                img.onerror = function() {
                    imagePreview.innerHTML = '<i class="fas fa-image"></i><p>Lỗi hình ảnh</p>';
                };
                imagePreview.innerHTML = '';
                imagePreview.appendChild(img);
                // Clear the hidden URL input when URL is entered - but we want to keep the value
                // Actually, let's set the hidden input to the URL
                if (imageUrl) imageUrl.value = this.value;
            }
        });
    }
    
    // Click to upload - ensure this works
    imagePreview.style.cursor = 'pointer';
    imagePreview.onclick = function(e) {
        e.preventDefault();
        console.log('Image preview clicked');
        if (imageInput) {
            console.log('Clicking file input');
            imageInput.click();
        } else {
            console.log('Image input not found');
        }
    };
}

// Utility Functions
function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&',
        '<': '<',
        '>': '>',
        '"': '"',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

// Product specific features
function initProductSpecificFeatures() {
    // Toggle between digital and physical product fields
    const productType = document.getElementById('type');
    if (productType) {
        productType.addEventListener('change', function() {
            const isDataProduct = this.value === 'data_nguon_hang';
            const dataFields = document.querySelectorAll('.data-product-field');
            dataFields.forEach(field => {
                field.style.display = isDataProduct ? 'block' : 'none';
            });
        });
    }
}
