// Categories Module JavaScript

document.addEventListener('DOMContentLoaded', function () {

    // Initialize categories functionality
    initCategoriesIndex();
    initCategoriesForm();
    initCategoriesView();
    initCategoriesDelete();

});

// Categories Index Page Functions
function initCategoriesIndex() {
    if (!document.querySelector('.categories-page')) return;

    // Select all checkbox functionality
    const selectAllCheckbox = document.getElementById('select-all');
    const categoryCheckboxes = document.querySelectorAll('.category-checkbox');
    const bulkActionSelect = document.getElementById('bulk-action');
    const applyBulkBtn = document.getElementById('apply-bulk');

    if (selectAllCheckbox && categoryCheckboxes.length > 0) {
        selectAllCheckbox.addEventListener('change', function () {
            categoryCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActions();
        });

        categoryCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                updateSelectAllState();
                updateBulkActions();
            });
        });
    }

    // Update select all state
    function updateSelectAllState() {
        if (!selectAllCheckbox) return;

        const checkedCount = document.querySelectorAll('.category-checkbox:checked').length;
        const totalCount = categoryCheckboxes.length;

        selectAllCheckbox.checked = checkedCount === totalCount;
        selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < totalCount;
    }

    // Update bulk actions state
    function updateBulkActions() {
        const checkedCount = document.querySelectorAll('.category-checkbox:checked').length;
        const hasSelection = checkedCount > 0;

        if (bulkActionSelect) bulkActionSelect.disabled = !hasSelection;
        if (applyBulkBtn) applyBulkBtn.disabled = !hasSelection;
    }

    // Apply bulk actions
    if (applyBulkBtn) {
        applyBulkBtn.addEventListener('click', function () {
            const selectedIds = Array.from(document.querySelectorAll('.category-checkbox:checked'))
                .map(cb => cb.value);
            const action = bulkActionSelect.value;

            if (!action || selectedIds.length === 0) {
                alert('Vui lòng chọn hành động và ít nhất một danh mục');
                return;
            }

            const actionText = {
                'activate': 'kích hoạt',
                'deactivate': 'vô hiệu hóa',
                'delete': 'xóa'
            };

            if (confirm(`Bạn có chắc chắn muốn ${actionText[action]} ${selectedIds.length} danh mục đã chọn?`)) {
                // Actual implementation: submit a form
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '?page=admin&module=categories&action=bulk';

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

    // Delete button functionality
    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const categoryId = this.dataset.id;
            const categoryName = this.dataset.name;

            document.getElementById('deleteCategoryName').textContent = categoryName;
            document.getElementById('deleteModal').style.display = 'block';

            document.getElementById('confirmDelete').onclick = function () {
                window.location.href = `?page=admin&module=categories&action=delete&id=${categoryId}`;
            };
        });
    });

    // Close delete modal
    const cancelDeleteBtn = document.getElementById('cancelDelete');
    const modalCloseBtn = document.querySelector('#deleteModal .modal-close');

    if (cancelDeleteBtn) {
        cancelDeleteBtn.addEventListener('click', function () {
            document.getElementById('deleteModal').style.display = 'none';
        });
    }

    if (modalCloseBtn) {
        modalCloseBtn.addEventListener('click', function () {
            document.getElementById('deleteModal').style.display = 'none';
        });
    }

    // Close modal when clicking outside
    window.addEventListener('click', function (event) {
        const modal = document.getElementById('deleteModal');
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
}

// Categories Form Functions (Add/Edit)
function initCategoriesForm() {
    if (!document.querySelector('.categories-add-page') && !document.querySelector('.categories-edit-page')) return;

    // Auto-generate slug from name
    const generateSlugBtn = document.getElementById('generateSlug');
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');

    if (generateSlugBtn && nameInput && slugInput) {
        generateSlugBtn.addEventListener('click', function () {
            const name = nameInput.value;
            if (name) {
                const slug = generateSlugFromName(name);
                slugInput.value = slug;
            }
        });

        // Auto-generate slug when typing name (only if slug is empty or auto-generated)
        nameInput.addEventListener('input', function () {
            if (!slugInput.value || slugInput.dataset.autoGenerated !== 'false') {
                const slug = generateSlugFromName(this.value);
                slugInput.value = slug;
                slugInput.dataset.autoGenerated = 'true';
            }
        });

        // Mark slug as manually edited
        slugInput.addEventListener('input', function () {
            this.dataset.autoGenerated = 'false';
        });
    }

    // Image preview functionality
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('imagePreview');

    if (imageInput && imagePreview) {
        imageInput.addEventListener('change', function (e) {
            const file = e.target.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    imagePreview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                };
                reader.readAsDataURL(file);
            } else {
                imagePreview.innerHTML = '<i class="fas fa-image"></i><p>Chọn hình ảnh</p>';
            }
        });
    }

    // Form validation
    const form = document.querySelector('.admin-form');
    if (form) {
        form.addEventListener('submit', function (e) {
            if (!validateCategoryForm()) {
                e.preventDefault();
            }
        });
    }
}

// Categories View Page Functions
function initCategoriesView() {
    if (!document.querySelector('.categories-view-page')) return;

    // Delete category functionality
    const deleteBtn = document.querySelector('.delete-btn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function () {
            const categoryId = this.dataset.id;
            const categoryName = this.dataset.name;

            document.getElementById('deleteCategoryName').textContent = categoryName;
            document.getElementById('deleteModal').style.display = 'block';

            document.getElementById('confirmDelete').onclick = function () {
                window.location.href = `?page=admin&module=categories&action=delete&id=${categoryId}`;
            };
        });
    }

    // Close delete modal
    const cancelDeleteBtn = document.getElementById('cancelDelete');
    const modalCloseBtn = document.querySelector('#deleteModal .modal-close');

    if (cancelDeleteBtn) {
        cancelDeleteBtn.addEventListener('click', function () {
            document.getElementById('deleteModal').style.display = 'none';
        });
    }

    if (modalCloseBtn) {
        modalCloseBtn.addEventListener('click', function () {
            document.getElementById('deleteModal').style.display = 'none';
        });
    }
}

// Categories Delete Page Functions
function initCategoriesDelete() {
    if (!document.querySelector('.categories-delete-page')) return;

    // Move products modal functionality
    const moveProductsModal = document.getElementById('moveProductsModal');
    if (moveProductsModal) {
        // Close modal functions
        window.closeMoveProductsModal = function () {
            moveProductsModal.style.display = 'none';
        };

        // Show modal function
        window.showMoveProductsModal = function () {
            moveProductsModal.style.display = 'block';
        };

        // Move products function
        window.moveProducts = function () {
            const targetCategory = document.getElementById('target_category').value;

            if (!targetCategory) {
                alert('Vui lòng chọn danh mục đích');
                return;
            }

            if (confirm('Bạn có chắc chắn muốn chuyển tất cả sản phẩm sang danh mục đã chọn?')) {
                // Actual implementation: redirect to move products action
                const categoryId = new URLSearchParams(window.location.search).get('id');
                window.location.href = `?page=admin&module=categories&action=move_products&id=${categoryId}&target=${targetCategory}`;
            }
        };

        // Close modal when clicking outside
        window.addEventListener('click', function (event) {
            if (event.target === moveProductsModal) {
                closeMoveProductsModal();
            }
        });

        // Close modal with X button
        const modalCloseBtn = moveProductsModal.querySelector('.modal-close');
        if (modalCloseBtn) {
            modalCloseBtn.addEventListener('click', closeMoveProductsModal);
        }
    }

    // Enable/disable delete button based on confirmation input
    const confirmInput = document.getElementById('confirm');
    const deleteBtn = document.querySelector('.delete-form button[type="submit"]');

    if (confirmInput && deleteBtn) {
        confirmInput.addEventListener('input', function () {
            const hasProducts = deleteBtn.hasAttribute('data-has-products');

            if (!hasProducts) {
                deleteBtn.disabled = this.value !== 'DELETE';
            }
        });
    }
}

// Helper Functions
function generateSlugFromName(name) {
    return name.toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/[\s-]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

function validateCategoryForm() {
    const name = document.getElementById('name').value.trim();
    const slug = document.getElementById('slug').value.trim();
    const description = document.getElementById('description').value.trim();

    if (!name) {
        alert('Vui lòng nhập tên danh mục');
        document.getElementById('name').focus();
        return false;
    }

    if (!slug) {
        alert('Vui lòng nhập slug');
        document.getElementById('slug').focus();
        return false;
    }

    if (!/^[a-z0-9-]+$/.test(slug)) {
        alert('Slug chỉ được chứa chữ thường, số và dấu gạch ngang');
        document.getElementById('slug').focus();
        return false;
    }

    if (!description) {
        alert('Vui lòng nhập mô tả danh mục');
        document.getElementById('description').focus();
        return false;
    }

    return true;
}

// Reset form function (global)
window.resetForm = function () {
    if (confirm('Bạn có chắc chắn muốn đặt lại form? Tất cả dữ liệu đã nhập sẽ bị mất.')) {
        const form = document.querySelector('.admin-form');
        if (form) {
            form.reset();
            const imagePreview = document.getElementById('imagePreview');
            if (imagePreview) {
                imagePreview.innerHTML = '<i class="fas fa-image"></i><p>Chọn hình ảnh</p>';
            }
        }
    }
};

// Duplicate category function (global)
window.duplicateCategory = function () {
    if (confirm('Bạn có muốn tạo bản sao của danh mục này?')) {
        const categoryId = new URLSearchParams(window.location.search).get('id');
        window.location.href = `?page=admin&module=categories&action=duplicate&id=${categoryId}`;
    }
};
