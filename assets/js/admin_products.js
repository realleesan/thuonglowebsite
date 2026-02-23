// Admin Products Module JavaScript

document.addEventListener('DOMContentLoaded', function () {
    // Initialize products page functionality
    initProductsPage();
});

function initProductsPage() {
    // Check if we're on products page
    if (!document.querySelector('.products-page')) {
        return;
    }

    // Initialize all components
    initSelectAllCheckbox();
    initBulkActions();
    initDeleteModal();
    initFormValidation();
    initImagePreview();
    initCharacterCount();
    initProductSpecificFeatures();
}

// Select All Checkbox Functionality
function initSelectAllCheckbox() {
    const selectAllCheckbox = document.getElementById('select-all');
    const productCheckboxes = document.querySelectorAll('.product-checkbox');

    if (!selectAllCheckbox || productCheckboxes.length === 0) return;

    selectAllCheckbox.addEventListener('change', function () {
        productCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActions();
    });

    productCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const checkedCount = document.querySelectorAll('.product-checkbox:checked').length;
            selectAllCheckbox.checked = checkedCount === productCheckboxes.length;
            selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < productCheckboxes.length;
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
        const checkedCount = document.querySelectorAll('.product-checkbox:checked').length;
        applyBulkBtn.disabled = checkedCount === 0 || !this.value;
    });

    applyBulkBtn.addEventListener('click', function () {
        const selectedIds = Array.from(document.querySelectorAll('.product-checkbox:checked'))
            .map(checkbox => checkbox.value);
        const action = bulkActionSelect.value;

        if (selectedIds.length === 0 || !action) return;

        const actionText = {
            'activate': 'kích hoạt',
            'deactivate': 'vô hiệu hóa',
            'delete': 'xóa'
        };

        if (confirm(`Bạn có chắc chắn muốn ${actionText[action]} ${selectedIds.length} sản phẩm đã chọn?`)) {
            // Actual implementation: submit a form or redirect with params
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '?page=admin&module=products&action=bulk';

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
    const checkedCount = document.querySelectorAll('.product-checkbox:checked').length;
    const bulkActionSelect = document.getElementById('bulk-action');
    const applyBulkBtn = document.getElementById('apply-bulk');

    if (bulkActionSelect && applyBulkBtn) {
        bulkActionSelect.disabled = checkedCount === 0;
        applyBulkBtn.disabled = checkedCount === 0 || !bulkActionSelect.value;
    }
}

function resetBulkSelections() {
    const selectAllCheckbox = document.getElementById('select-all');
    const productCheckboxes = document.querySelectorAll('.product-checkbox');
    const bulkActionSelect = document.getElementById('bulk-action');

    if (selectAllCheckbox) selectAllCheckbox.checked = false;
    productCheckboxes.forEach(checkbox => checkbox.checked = false);
    if (bulkActionSelect) bulkActionSelect.value = '';
    updateBulkActions();
}

// Delete Modal Functionality
function initDeleteModal() {
    const deleteModal = document.getElementById('deleteModal');
    const deleteButtons = document.querySelectorAll('.delete-btn');
    const deleteProductName = document.getElementById('deleteProductName');
    const cancelDeleteBtn = document.getElementById('cancelDelete');
    const confirmDeleteBtn = document.getElementById('confirmDelete');
    const modalClose = document.querySelector('.modal-close');

    if (!deleteModal) return;

    let currentDeleteId = null;

    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            currentDeleteId = this.dataset.id;
            if (deleteProductName) {
                deleteProductName.textContent = this.dataset.name;
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
                window.location.href = `?page=admin&module=products&action=delete&id=${currentDeleteId}`;
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

            if (!isValid) {
                e.preventDefault();
                alert('Vui lòng điền đầy đủ các trường bắt buộc!');
            }
        });
    });
}

// Image Preview Functionality
function initImagePreview() {
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('imagePreview');

    if (!imageInput || !imagePreview) return;

    const originalImageHTML = imagePreview.innerHTML;

    imageInput.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                imagePreview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
            };
            reader.readAsDataURL(file);
        } else {
            imagePreview.innerHTML = originalImageHTML;
        }
    });

    // Click to upload
    imagePreview.addEventListener('click', function () {
        imageInput.click();
    });
}

// Character Count for SEO Fields
function initCharacterCount() {
    const metaTitle = document.getElementById('meta_title');
    const metaDescription = document.getElementById('meta_description');

    function updateCharCount(input, maxLength) {
        if (!input) return;

        const currentLength = input.value.length;
        const small = input.nextElementSibling;
        if (small && small.tagName === 'SMALL') {
            small.textContent = `${currentLength}/${maxLength} ký tự`;
            small.className = currentLength > maxLength ? 'text-danger' : '';
        }
    }

    if (metaTitle) {
        metaTitle.addEventListener('input', function () {
            updateCharCount(this, 60);
        });
        // Initialize
        updateCharCount(metaTitle, 60);
    }

    if (metaDescription) {
        metaDescription.addEventListener('input', function () {
            updateCharCount(this, 160);
        });
        // Initialize
        updateCharCount(metaDescription, 160);
    }
}

// Product-specific Features
function initProductSpecificFeatures() {
    initAutoSKUGeneration();
    initPriceFormatting();
    initTabFunctionality();
    initImageZoom();
    initChangeTracking();
}

// Auto-generate SKU from product name
function initAutoSKUGeneration() {
    const nameInput = document.getElementById('name');
    const skuInput = document.getElementById('sku');

    if (!nameInput || !skuInput) return;

    nameInput.addEventListener('blur', function () {
        if (!skuInput.value && this.value) {
            // Generate SKU from name
            const sku = this.value
                .toLowerCase()
                .replace(/[^a-z0-9\s]/g, '')
                .replace(/\s+/g, '-')
                .substring(0, 20) + '-' + Date.now().toString().slice(-4);
            skuInput.value = sku.toUpperCase();
        }
    });
}

// Price Formatting
function initPriceFormatting() {
    const priceInput = document.getElementById('price');

    if (!priceInput) return;

    priceInput.addEventListener('blur', function () {
        if (this.value) {
            this.value = Math.round(parseFloat(this.value));
        }
    });
}

// Tab Functionality for Product View
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

// Image Zoom Functionality
function initImageZoom() {
    const productImage = document.querySelector('.product-image-main img');

    if (!productImage) return;

    productImage.addEventListener('click', function () {
        // Create zoom overlay
        const overlay = document.createElement('div');
        overlay.className = 'image-zoom-overlay';
        overlay.innerHTML = `
            <div class="image-zoom-container">
                <img src="${this.src}" alt="${this.alt}">
                <button class="zoom-close">&times;</button>
            </div>
        `;

        document.body.appendChild(overlay);

        // Close zoom
        overlay.addEventListener('click', function (e) {
            if (e.target === this || e.target.classList.contains('zoom-close')) {
                document.body.removeChild(overlay);
            }
        });
    });
}

// Change Tracking for Edit Forms
function initChangeTracking() {
    const formInputs = document.querySelectorAll('input, select, textarea');

    if (formInputs.length === 0) return;

    // Store original values for reset functionality
    const originalValues = {};
    let hasChanges = false;

    formInputs.forEach(input => {
        if (!input.classList.contains('readonly')) {
            originalValues[input.name] = input.value;

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

    // Reset form functionality
    window.resetForm = function () {
        if (confirm('Bạn có chắc chắn muốn khôi phục về giá trị ban đầu? Tất cả thay đổi sẽ bị mất.')) {
            // Reset to original values
            formInputs.forEach(input => {
                if (!input.classList.contains('readonly') && originalValues.hasOwnProperty(input.name)) {
                    input.value = originalValues[input.name];
                }
            });

            // Reset image preview if exists
            const imagePreview = document.getElementById('imagePreview');
            const imageInput = document.getElementById('image');
            if (imagePreview && imageInput) {
                // Reset to original image or placeholder
                const originalImg = imagePreview.querySelector('img');
                if (originalImg) {
                    // Keep original image
                } else {
                    imagePreview.innerHTML = `
                        <i class="fas fa-image"></i>
                        <p>Chọn hình ảnh</p>
                    `;
                }
                imageInput.value = '';
            }

            hasChanges = false;
        }
    };
}

// Global Functions for Product Actions
window.deleteProduct = function (id, name) {
    const deleteModal = document.getElementById('deleteModal');
    const deleteProductName = document.getElementById('deleteProductName');

    if (deleteModal && deleteProductName) {
        deleteProductName.textContent = name;
        deleteModal.style.display = 'flex';

        // Store the ID for confirmation
        window.currentDeleteId = id;
    }
};

window.deactivateProduct = function () {
    const deactivateModal = document.getElementById('deactivateModal');
    if (deactivateModal) {
        deactivateModal.style.display = 'flex';
    }
};

// Utility Functions
function showAlert(message, type = 'info') {
    // Create alert element
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : 'info-circle'}"></i>
        <span>${message}</span>
    `;

    // Insert at top of content
    const content = document.querySelector('.products-page');
    if (content) {
        content.insertBefore(alert, content.firstChild);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.parentNode.removeChild(alert);
            }
        }, 5000);
    }
}

function formatNumber(num) {
    return new Intl.NumberFormat('vi-VN').format(num);
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
}

// Export functions for use in other scripts if needed
window.ProductsModule = {
    showAlert,
    formatNumber,
    formatCurrency,
    resetBulkSelections,
    updateBulkActions
};

// Additional functionality for delete page
function initDeletePageFeatures() {
    // Enable/disable delete button based on checkbox
    const confirmCheckbox = document.getElementById('confirm-checkbox');
    const deleteBtn = document.getElementById('delete-btn');

    if (confirmCheckbox && deleteBtn) {
        confirmCheckbox.addEventListener('change', function () {
            deleteBtn.disabled = !this.checked;
        });
    }

    // Deactivate modal functionality
    const deactivateModal = document.getElementById('deactivateModal');
    const cancelDeactivateBtn = document.getElementById('cancelDeactivate');
    const confirmDeactivateBtn = document.getElementById('confirmDeactivate');
    const modalClose = document.querySelector('#deactivateModal .modal-close');

    if (deactivateModal) {
        window.deactivateProduct = function () {
            deactivateModal.style.display = 'flex';
        };

        function closeDeactivateModal() {
            deactivateModal.style.display = 'none';
        }

        if (cancelDeactivateBtn) {
            cancelDeactivateBtn.addEventListener('click', closeDeactivateModal);
        }

        if (modalClose) {
            modalClose.addEventListener('click', closeDeactivateModal);
        }

        if (confirmDeactivateBtn) {
            confirmDeactivateBtn.addEventListener('click', function () {
                const urlParams = new URLSearchParams(window.location.search);
                const productId = urlParams.get('id');
                if (productId) {
                    window.location.href = `?page=admin&module=products&action=deactivate&id=${productId}`;
                }
            });
        }

        // Close modal when clicking outside
        deactivateModal.addEventListener('click', function (e) {
            if (e.target === this) {
                closeDeactivateModal();
            }
        });
    }

    // Form submission confirmation
    const deleteForm = document.querySelector('.delete-form');
    if (deleteForm) {
        deleteForm.addEventListener('submit', function (e) {
            if (!confirm('Bạn có THỰC SỰ chắc chắn muốn xóa sản phẩm này? Hành động này KHÔNG THỂ hoàn tác!')) {
                e.preventDefault();
            }
        });
    }
}

// Update the main init function to include delete page features
function initProductsPage() {
    // Check if we're on products page
    if (!document.querySelector('.products-page')) {
        return;
    }

    // Initialize all components
    initSelectAllCheckbox();
    initBulkActions();
    initDeleteModal();
    initFormValidation();
    initImagePreview();
    initCharacterCount();
    initProductSpecificFeatures();
    initDeletePageFeatures(); // Add this line
}

// Global reset form function for add/edit pages
window.resetForm = function () {
    const isAddPage = document.querySelector('.products-add-page');
    const isEditPage = document.querySelector('.products-edit-page');

    if (isAddPage) {
        if (confirm('Bạn có chắc chắn muốn đặt lại form? Tất cả dữ liệu đã nhập sẽ bị xóa.')) {
            document.querySelector('.admin-form').reset();
            const imagePreview = document.getElementById('imagePreview');
            if (imagePreview) {
                imagePreview.innerHTML = `
                    <i class="fas fa-image"></i>
                    <p>Chọn hình ảnh</p>
                `;
            }
        }
    } else if (isEditPage) {
        // This will be handled by the change tracking functionality
        // which stores original values
        if (typeof window.originalFormValues !== 'undefined') {
            if (confirm('Bạn có chắc chắn muốn khôi phục về giá trị ban đầu? Tất cả thay đổi sẽ bị mất.')) {
                const formInputs = document.querySelectorAll('input, select, textarea');
                formInputs.forEach(input => {
                    if (!input.classList.contains('readonly') && window.originalFormValues.hasOwnProperty(input.name)) {
                        input.value = window.originalFormValues[input.name];
                    }
                });

                // Reset image preview to original
                const imagePreview = document.getElementById('imagePreview');
                const imageInput = document.getElementById('image');
                if (imagePreview && imageInput) {
                    // Reset to original image or placeholder
                    if (window.originalImageHTML) {
                        imagePreview.innerHTML = window.originalImageHTML;
                    }
                    imageInput.value = '';
                }
            }
        }
    }
};

// Store original form values for edit pages
document.addEventListener('DOMContentLoaded', function () {
    if (document.querySelector('.products-edit-page')) {
        const formInputs = document.querySelectorAll('input, select, textarea');
        window.originalFormValues = {};

        formInputs.forEach(input => {
            if (!input.classList.contains('readonly')) {
                window.originalFormValues[input.name] = input.value;
            }
        });

        // Store original image HTML
        const imagePreview = document.getElementById('imagePreview');
        if (imagePreview) {
            window.originalImageHTML = imagePreview.innerHTML;
        }
    }
});