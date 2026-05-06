/**
 * Admin Brands JavaScript
 * Handles all interactions for brands management pages
 */

(function() {
    'use strict';

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        initTabSwitching();
        initDeleteModals();
        initSelectAllCheckbox();
        initSlugGeneration();
    });

    /**
     * Tab Switching functionality
     */
    function initTabSwitching() {
        const tabButtons = document.querySelectorAll('.tabs-header .tab-btn');
        if (!tabButtons.length) return;

        tabButtons.forEach(function(btn) {
            btn.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');
                if (!tabId) return;

                // Remove active from all buttons and panes
                document.querySelectorAll('.tabs-header .tab-btn').forEach(function(b) {
                    b.classList.remove('active');
                });
                document.querySelectorAll('.tabs-content .tab-pane').forEach(function(p) {
                    p.classList.remove('active');
                });

                // Add active to clicked button and corresponding pane
                this.classList.add('active');
                const tabPane = document.getElementById(tabId);
                if (tabPane) {
                    tabPane.classList.add('active');
                }
            });
        });
    }

    /**
     * Delete Modal functionality
     */
    function initDeleteModals() {
        // Index page - multiple delete buttons
        const deleteButtons = document.querySelectorAll('.delete-btn');
        deleteButtons.forEach(function(btn) {
            btn.addEventListener('click', function() {
                const brandId = this.dataset.id;
                const brandName = this.dataset.name;
                const hasProducts = this.dataset.hasProducts === '1';

                const brandNameEl = document.getElementById('deleteBrandName');
                const warningEl = document.getElementById('deleteWarning');
                const confirmBtn = document.getElementById('confirmDelete');
                const modal = document.getElementById('deleteModal');

                if (brandNameEl) brandNameEl.textContent = brandName;
                if (warningEl) warningEl.style.display = hasProducts ? 'block' : 'none';

                if (confirmBtn) {
                    if (hasProducts) {
                        confirmBtn.style.display = 'none';
                    } else {
                        confirmBtn.style.display = 'inline-block';
                        confirmBtn.href = '?page=admin&module=brands&action=delete&id=' + brandId;
                    }
                }

                if (modal) modal.style.display = 'block';
            });
        });

        // Cancel delete
        const cancelDeleteBtn = document.getElementById('cancelDelete');
        if (cancelDeleteBtn) {
            cancelDeleteBtn.addEventListener('click', function() {
                const modal = document.getElementById('deleteModal');
                if (modal) modal.style.display = 'none';
            });
        }

        // Close modal via X button
        const closeModalBtn = document.querySelector('#deleteModal .modal-close');
        if (closeModalBtn) {
            closeModalBtn.addEventListener('click', function() {
                const modal = document.getElementById('deleteModal');
                if (modal) modal.style.display = 'none';
            });
        }

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target === modal && modal) {
                modal.style.display = 'none';
            }
        });
    }

    /**
     * Select All Checkbox functionality (index page)
     */
    function initSelectAllCheckbox() {
        const selectAllCheckbox = document.getElementById('select-all');
        const brandCheckboxes = document.querySelectorAll('.brand-checkbox');

        if (!selectAllCheckbox || !brandCheckboxes.length) return;

        selectAllCheckbox.addEventListener('change', function() {
            brandCheckboxes.forEach(function(checkbox) {
                checkbox.checked = selectAllCheckbox.checked;
            });
        });

        brandCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                const checkedCount = document.querySelectorAll('.brand-checkbox:checked').length;
                selectAllCheckbox.checked = checkedCount === brandCheckboxes.length;
                selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < brandCheckboxes.length;
            });
        });
    }

    /**
     * Slug Generation functionality (add/edit pages)
     */
    function initSlugGeneration() {
        const generateSlugBtn = document.getElementById('generateSlug');
        const nameInput = document.getElementById('name');
        const slugInput = document.getElementById('slug');

        if (!nameInput || !slugInput) return;

        // Generate slug button click
        if (generateSlugBtn) {
            generateSlugBtn.addEventListener('click', function() {
                const name = nameInput.value;
                if (name) {
                    const slug = generateSlugFromName(name);
                    slugInput.value = slug;
                }
            });
        }

        // Auto-generate slug when typing name (only if slug is empty or auto-generated)
        nameInput.addEventListener('input', function() {
            if (!slugInput.value || slugInput.dataset.autoGenerated !== 'false') {
                const slug = generateSlugFromName(this.value);
                slugInput.value = slug;
                slugInput.dataset.autoGenerated = 'true';
            }
        });

        // Mark slug as manually edited
        slugInput.addEventListener('input', function() {
            this.dataset.autoGenerated = 'false';
        });
    }

    /**
     * Generate slug from name
     */
    function generateSlugFromName(name) {
        if (!name) return '';
        return name.toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/[\s-]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    /**
     * Preview image when selected (add form)
     */
    window.previewImageAdd = function(input) {
        const preview = document.getElementById('imagePreview');
        if (!preview || !input.files || !input.files[0]) return;

        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">' +
                '<div class="image-overlay"><span><i class="fas fa-camera"></i> Click để thay đổi ảnh</span></div>';
        };
        reader.readAsDataURL(input.files[0]);
    };

    /**
     * Preview image when selected (edit form)
     */
    window.previewImageEdit = function(input) {
        const preview = document.getElementById('imagePreview');
        if (!preview || !input.files || !input.files[0]) return;

        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">' +
                '<button type="button" class="btn-remove-image" onclick="removeImageEdit()">' +
                '<i class="fas fa-trash"></i></button>';
        };
        reader.readAsDataURL(input.files[0]);
    };

    /**
     * Remove image (edit form)
     */
    window.removeImageEdit = function() {
        const preview = document.getElementById('imagePreview');
        const imageUrlInput = document.getElementById('image_url');
        const imageFileInput = document.getElementById('image_file');
        const removeImageFlag = document.getElementById('remove_image');

        if (!confirm('Bạn có chắc chắn muốn xóa hình ảnh này?')) return;

        if (preview) {
            preview.innerHTML = '<div class="image-placeholder">' +
                '<i class="fas fa-cloud-upload-alt"></i>' +
                '<span>Click để tải ảnh lên</span>' +
                '<small>Hoặc kéo thả ảnh vào đây</small></div>';
        }

        if (imageUrlInput) imageUrlInput.value = '';
        if (imageFileInput) imageFileInput.value = '';
        if (removeImageFlag) removeImageFlag.value = '1';
    };

    /**
     * Reset form function
     */
    window.resetForm = function() {
        if (!confirm('Bạn có chắc chắn muốn đặt lại form? Tất cả dữ liệu đã nhập sẽ bị mất.')) return;

        const form = document.querySelector('.admin-form');
        if (form) {
            form.reset();
            const imagePreview = document.getElementById('imagePreview');
            if (imagePreview) {
                imagePreview.innerHTML = '<div class="image-placeholder">' +
                    '<i class="fas fa-cloud-upload-alt"></i>' +
                    '<span>Click để tải ảnh lên</span>' +
                    '<small>Hoặc kéo thả ảnh vào đây</small></div>';
            }
        }
    };

})();
