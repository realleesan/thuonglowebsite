// Admin Products Module JavaScript
// Tái cấu trúc cho sản phẩm số (Data Nguồn Hàng)

document.addEventListener('DOMContentLoaded', function () {
    initProductsPage();
});

function initProductsPage() {
    // Check if we're on products page
    if (!document.querySelector('.products-page')) {
        return;
    }

    // Initialize all components
    initTabs();
    initDeleteModal();
    initJsonPreview();
    initImagePreview();
    initFormValidation();
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

// Delete Modal
function initDeleteModal() {
    const deleteBtns = document.querySelectorAll('.delete-btn');
    const deleteModal = document.getElementById('deleteModal');
    const deleteProductName = document.getElementById('deleteProductName');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    
    if (!deleteBtns.length || !deleteModal) return;
    
    let currentDeleteId = null;
    
    deleteBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            currentDeleteId = this.dataset.id;
            deleteProductName.textContent = this.dataset.name;
            deleteModal.style.display = 'flex';
        });
    });
    
    window.closeDeleteModal = function() {
        if (deleteModal) {
            deleteModal.style.display = 'none';
        }
        currentDeleteId = null;
    };
    
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            if (currentDeleteId) {
                window.location.href = '?page=admin&module=products&action=delete&id=' + currentDeleteId;
            }
        });
    }
    
    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target === deleteModal) {
            closeDeleteModal();
        }
    });
}

// JSON Preview
function initJsonPreview() {
    const benefitsInput = document.getElementById('benefits');
    const benefitsPreview = document.getElementById('benefitsPreview');
    
    if (!benefitsInput || !benefitsPreview) return;
    
    benefitsInput.addEventListener('input', function() {
        try {
            const parsed = JSON.parse(this.value);
            if (Array.isArray(parsed)) {
                benefitsPreview.innerHTML = '<ul>' + 
                    parsed.map(item => '<li>' + escapeHtml(item) + '</li>').join('') + 
                    '</ul>';
            } else {
                benefitsPreview.innerHTML = '<p class="preview-empty">JSON phải là mảng</p>';
            }
        } catch (e) {
            benefitsPreview.innerHTML = '<p class="preview-empty">JSON không hợp lệ</p>';
        }
    });
    
    // Data structure preview
    const dataStructureInput = document.getElementById('data_structure');
    const dataStructurePreview = document.getElementById('dataStructurePreview');
    
    if (dataStructureInput && dataStructurePreview) {
        dataStructureInput.addEventListener('input', function() {
            try {
                const parsed = JSON.parse(this.value);
                if (Array.isArray(parsed)) {
                    let html = '';
                    parsed.forEach((section, index) => {
                        html += '<div class="structure-section">';
                        html += '<strong>' + (index + 1) + '. ' + escapeHtml(section.title || 'Nhóm thông tin') + '</strong>';
                        if (section.items && Array.isArray(section.items)) {
                            html += '<ul>';
                            section.items.forEach(item => {
                                html += '<li>' + escapeHtml(item.title || '') + '</li>';
                            });
                            html += '</ul>';
                        }
                        html += '</div>';
                    });
                    dataStructurePreview.innerHTML = html || '<p class="preview-empty">Chưa có cấu trúc</p>';
                }
            } catch (e) {
                dataStructurePreview.innerHTML = '<p class="preview-empty">JSON không hợp lệ</p>';
            }
        });
    }
}

// Image Preview
function initImagePreview() {
    const imageInput = document.getElementById('image');
    const imageUrlInput = document.getElementById('imageUrlInput');
    const imagePreview = document.getElementById('imagePreview');
    const imageUrl = document.getElementById('image_url');
    
    if (!imagePreview) return;
    
    // URL input handler
    if (imageUrlInput) {
        imageUrlInput.addEventListener('input', function() {
            if (this.value) {
                imagePreview.innerHTML = '<img src="' + this.value + '" alt="Preview" onerror="this.parentElement.innerHTML=\'<i class=\'fas fa-image\'></i><p>Lỗi hình ảnh</p>\'">';
                if (imageUrl) imageUrl.value = this.value;
            }
        });
    }
    
    // File input handler
    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
    
    // Click to upload
    imagePreview.addEventListener('click', function() {
        if (imageInput) {
            imageInput.click();
        }
    });
}

// Form Validation
function initFormValidation() {
    const productForm = document.getElementById('productForm');
    
    if (!productForm) return;
    
    productForm.addEventListener('submit', function(e) {
        const name = document.getElementById('name');
        const categoryId = document.getElementById('category_id');
        const price = document.getElementById('price');
        const description = document.getElementById('description');
        
        let isValid = true;
        let errors = [];
        
        // Validate required fields
        if (name && !name.value.trim()) {
            name.style.borderColor = '#dc2626';
            isValid = false;
        } else if (name) {
            name.style.borderColor = '#d1d5db';
        }
        
        if (categoryId && !categoryId.value) {
            categoryId.style.borderColor = '#dc2626';
            isValid = false;
        } else if (categoryId) {
            categoryId.style.borderColor = '#d1d5db';
        }
        
        if (price && (!price.value || price.value <= 0)) {
            price.style.borderColor = '#dc2626';
            isValid = false;
        } else if (price) {
            price.style.borderColor = '#d1d5db';
        }
        
        if (description && !description.value.trim()) {
            description.style.borderColor = '#dc2626';
            isValid = false;
        } else if (description) {
            description.style.borderColor = '#d1d5db';
        }
        
        if (!isValid) {
            e.preventDefault();
            alert('Vui lòng điền đầy đủ các trường bắt buộc!');
        }
    });
    
    // Reset form
    window.resetForm = function() {
        if (confirm('Bạn có chắc chắn muốn đặt lại form?')) {
            productForm.reset();
            
            // Reset preview areas
            const benefitsPreview = document.getElementById('benefitsPreview');
            if (benefitsPreview) {
                benefitsPreview.innerHTML = '<p class="preview-empty">Nhập JSON để xem trước</p>';
            }
            
            const dataStructurePreview = document.getElementById('dataStructurePreview');
            if (dataStructurePreview) {
                dataStructurePreview.innerHTML = '<p class="preview-empty">Nhập JSON để xem trước</p>';
            }
            
            const imagePreview = document.getElementById('imagePreview');
            if (imagePreview) {
                imagePreview.innerHTML = '<i class="fas fa-image"></i><p>Chọn hình ảnh</p>';
            }
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
