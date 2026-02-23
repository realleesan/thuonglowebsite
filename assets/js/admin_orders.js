// Admin Orders Module JavaScript

document.addEventListener('DOMContentLoaded', function () {
    // Initialize all order page functionality
    initOrdersIndex();
    initOrdersView();
    initOrdersEdit();
    initOrdersDelete();
});

// ===== ORDERS INDEX PAGE =====
function initOrdersIndex() {
    if (!document.querySelector('.orders-page')) return;

    // Select all checkbox functionality
    const selectAllCheckbox = document.getElementById('select-all');
    const orderCheckboxes = document.querySelectorAll('.order-checkbox');
    const bulkUpdateBtn = document.getElementById('bulk-update-status');
    const exportBtn = document.getElementById('export-orders');

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            orderCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActions();
        });
    }

    // Individual checkbox change
    orderCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            updateSelectAllState();
            updateBulkActions();
        });
    });

    // Update select all state
    function updateSelectAllState() {
        if (!selectAllCheckbox) return;

        const checkedCount = document.querySelectorAll('.order-checkbox:checked').length;
        const totalCount = orderCheckboxes.length;

        selectAllCheckbox.checked = checkedCount === totalCount;
        selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < totalCount;
    }

    // Update bulk actions state
    function updateBulkActions() {
        const checkedCount = document.querySelectorAll('.order-checkbox:checked').length;
        const bulkActionSelect = document.getElementById('bulk-action');
        const applyBulkBtn = document.getElementById('apply-bulk');

        if (bulkActionSelect) {
            bulkActionSelect.disabled = checkedCount === 0;
        }
        if (applyBulkBtn) {
            applyBulkBtn.disabled = checkedCount === 0;
        }
        if (bulkUpdateBtn) {
            bulkUpdateBtn.disabled = checkedCount === 0;
        }

        // Update selected count in modal
        const selectedCountSpan = document.getElementById('selectedCount');
        if (selectedCountSpan) {
            selectedCountSpan.textContent = checkedCount;
        }
    }

    // Bulk update status modal
    if (bulkUpdateBtn) {
        bulkUpdateBtn.addEventListener('click', function () {
            const checkedCount = document.querySelectorAll('.order-checkbox:checked').length;
            if (checkedCount === 0) {
                alert('Vui lòng chọn ít nhất một đơn hàng');
                return;
            }
            showModal('bulkUpdateModal');
        });
    }

    // Export orders
    if (exportBtn) {
        exportBtn.addEventListener('click', function () {
            // Actual implementation: redirect to export action
            const checkedOrders = Array.from(document.querySelectorAll('.order-checkbox:checked')).map(cb => cb.value);
            let url = '?page=admin&module=orders&action=export';
            if (checkedOrders.length > 0) {
                url += '&ids=' + JSON.stringify(checkedOrders);
            }
            window.location.href = url;
        });
    }

    // Delete button functionality
    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function () {
            const orderId = this.dataset.id;
            const customerName = this.dataset.customer;

            document.getElementById('deleteCustomerName').textContent = customerName;

            // Store order ID for deletion
            document.getElementById('confirmDelete').dataset.orderId = orderId;

            showModal('deleteModal');
        });
    });

    // Confirm delete
    const confirmDeleteBtn = document.getElementById('confirmDelete');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function () {
            const orderId = this.dataset.orderId;
            // Actual implementation: redirect to delete action
            window.location.href = `?page=admin&module=orders&action=delete&id=${orderId}`;
        });
    }

    // Bulk update confirm
    const confirmBulkUpdateBtn = document.getElementById('confirmBulkUpdate');
    if (confirmBulkUpdateBtn) {
        confirmBulkUpdateBtn.addEventListener('click', function () {
            const newStatus = document.getElementById('bulk-status').value;
            const checkedOrders = Array.from(document.querySelectorAll('.order-checkbox:checked')).map(cb => cb.value);

            if (!newStatus) {
                alert('Vui lòng chọn trạng thái mới');
                return;
            }

            // Actual implementation: submit a form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '?page=admin&module=orders&action=bulk_update';

            const idsInput = document.createElement('input');
            idsInput.type = 'hidden';
            idsInput.name = 'ids';
            idsInput.value = JSON.stringify(checkedOrders);

            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'status';
            actionInput.value = newStatus;

            form.appendChild(idsInput);
            form.appendChild(actionInput);
            document.body.appendChild(form);
            form.submit();
        });
    }
}

// ===== ORDERS VIEW PAGE =====
function initOrdersView() {
    if (!document.querySelector('.orders-view-page')) return;

    // Tab functionality
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', function () {
            const targetTab = this.dataset.tab;

            // Remove active class from all tabs and contents
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));

            // Add active class to clicked tab and corresponding content
            this.classList.add('active');
            document.getElementById(targetTab + '-tab').classList.add('active');
        });
    });

    // Image zoom functionality
    const productImages = document.querySelectorAll('.product-image-main img');
    productImages.forEach(img => {
        img.addEventListener('click', function () {
            showImageZoom(this.src, this.alt);
        });
    });

    // Send email button
    const sendEmailBtn = document.getElementById('send-email');
    if (sendEmailBtn) {
        sendEmailBtn.addEventListener('click', function () {
            const orderId = this.dataset.id;
            // Actual implementation: redirect to send email action
            window.location.href = `?page=admin&module=orders&action=send_email&id=${orderId}`;
        });
    }

    // Delete button functionality
    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function () {
            const orderId = this.dataset.id;
            const customerName = this.dataset.customer;

            document.getElementById('deleteCustomerName').textContent = customerName;
            document.getElementById('confirmDelete').dataset.orderId = orderId;

            showModal('deleteModal');
        });
    });

    // Confirm delete
    const confirmDeleteBtn = document.getElementById('confirmDelete');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function () {
            const orderId = this.dataset.orderId;
            // Actual implementation: redirect to delete action
            window.location.href = `?page=admin&module=orders&action=delete&id=${orderId}`;
        });
    }
}

// ===== ORDERS EDIT PAGE =====
function initOrdersEdit() {
    if (!document.querySelector('.orders-edit-page')) return;

    // Status change preview
    const statusSelect = document.getElementById('status');
    const adminNoteTextarea = document.getElementById('admin_note');
    const previewEmailBtn = document.getElementById('preview-email');

    // Preview email functionality
    if (previewEmailBtn) {
        previewEmailBtn.addEventListener('click', function () {
            updateEmailPreview();
            showModal('emailPreviewModal');
        });
    }

    // Update email preview content
    function updateEmailPreview() {
        const selectedStatus = statusSelect ? statusSelect.value : '';
        const adminNote = adminNoteTextarea ? adminNoteTextarea.value.trim() : '';

        // Update status in preview
        const statusPreview = document.getElementById('email-status-preview');
        if (statusPreview && selectedStatus) {
            const statusLabels = {
                'pending': 'Chờ xử lý',
                'processing': 'Đang xử lý',
                'completed': 'Hoàn thành',
                'cancelled': 'Đã hủy'
            };
            statusPreview.textContent = statusLabels[selectedStatus] || selectedStatus;
        }

        // Update note in preview
        const notePreview = document.getElementById('email-note-preview');
        const noteContent = document.getElementById('email-note-content');
        if (notePreview && noteContent) {
            if (adminNote) {
                noteContent.textContent = adminNote;
                notePreview.style.display = 'block';
            } else {
                notePreview.style.display = 'none';
            }
        }
    }

    // Close email preview
    const closeEmailPreviewBtn = document.getElementById('closeEmailPreview');
    if (closeEmailPreviewBtn) {
        closeEmailPreviewBtn.addEventListener('click', function () {
            hideModal('emailPreviewModal');
        });
    }

    // Form validation
    const editForm = document.querySelector('.admin-form');
    if (editForm) {
        editForm.addEventListener('submit', function (e) {
            const status = statusSelect ? statusSelect.value : '';

            if (!status) {
                e.preventDefault();
                alert('Vui lòng chọn trạng thái mới');
                statusSelect.focus();
                return false;
            }

            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang cập nhật...';
            }
        });
    }
}

// ===== ORDERS DELETE PAGE =====
function initOrdersDelete() {
    if (!document.querySelector('.orders-delete-page')) return;

    // Form validation
    const deleteForm = document.querySelector('.delete-form');
    const confirmCheckbox = document.querySelector('input[name="confirm_delete"]');
    const deleteReasonTextarea = document.querySelector('textarea[name="delete_reason"]');

    if (deleteForm) {
        deleteForm.addEventListener('submit', function (e) {
            const reason = deleteReasonTextarea ? deleteReasonTextarea.value.trim() : '';
            const confirmed = confirmCheckbox ? confirmCheckbox.checked : false;

            if (!reason) {
                e.preventDefault();
                alert('Vui lòng nhập lý do xóa đơn hàng');
                deleteReasonTextarea.focus();
                return false;
            }

            if (!confirmed) {
                e.preventDefault();
                alert('Vui lòng xác nhận việc xóa đơn hàng');
                confirmCheckbox.focus();
                return false;
            }

            // Show confirmation dialog
            if (!confirm('Bạn có chắc chắn muốn xóa đơn hàng này? Hành động này không thể hoàn tác!')) {
                e.preventDefault();
                return false;
            }

            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xóa...';
            }
        });
    }
}

// ===== UTILITY FUNCTIONS =====

// Show modal
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';

        // Focus trap
        const focusableElements = modal.querySelectorAll('button, input, select, textarea, [tabindex]:not([tabindex="-1"])');
        if (focusableElements.length > 0) {
            focusableElements[0].focus();
        }
    }
}

// Hide modal
function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// Show image zoom
function showImageZoom(src, alt) {
    // Create zoom overlay
    const overlay = document.createElement('div');
    overlay.className = 'image-zoom-overlay';
    overlay.innerHTML = `
        <div class="image-zoom-container">
            <img src="${src}" alt="${alt}">
            <button class="zoom-close" type="button">&times;</button>
        </div>
    `;

    document.body.appendChild(overlay);
    document.body.style.overflow = 'hidden';

    // Close on click
    overlay.addEventListener('click', function (e) {
        if (e.target === overlay || e.target.classList.contains('zoom-close')) {
            document.body.removeChild(overlay);
            document.body.style.overflow = '';
        }
    });

    // Close on escape key
    const handleEscape = function (e) {
        if (e.key === 'Escape') {
            document.body.removeChild(overlay);
            document.body.style.overflow = '';
            document.removeEventListener('keydown', handleEscape);
        }
    };
    document.addEventListener('keydown', handleEscape);
}

// Show toast notification
function showToast(message, type = 'info') {
    // Remove existing toast
    const existingToast = document.querySelector('.toast');
    if (existingToast) {
        existingToast.remove();
    }

    // Create toast
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <div class="toast-content">
            <i class="fas fa-${getToastIcon(type)}"></i>
            <span>${message}</span>
        </div>
    `;

    // Add styles
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${getToastColor(type)};
        color: white;
        padding: 12px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 10000;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        font-weight: 500;
        max-width: 400px;
        animation: slideInRight 0.3s ease;
    `;

    document.body.appendChild(toast);

    // Auto remove after 3 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.remove();
                }
            }, 300);
        }
    }, 3000);
}

function getToastIcon(type) {
    const icons = {
        'success': 'check-circle',
        'error': 'exclamation-circle',
        'warning': 'exclamation-triangle',
        'info': 'info-circle'
    };
    return icons[type] || 'info-circle';
}

function getToastColor(type) {
    const colors = {
        'success': '#10B981',
        'error': '#EF4444',
        'warning': '#F59E0B',
        'info': '#3B82F6'
    };
    return colors[type] || '#3B82F6';
}

// Modal close functionality
document.addEventListener('click', function (e) {
    // Close modal when clicking outside or on close button
    if (e.target.classList.contains('modal') || e.target.classList.contains('modal-close')) {
        const modal = e.target.closest('.modal') || e.target;
        if (modal.classList.contains('modal')) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }

    // Cancel buttons
    if (e.target.id === 'cancelDelete' || e.target.id === 'cancelBulkUpdate') {
        const modal = e.target.closest('.modal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }
});

// Keyboard navigation
document.addEventListener('keydown', function (e) {
    // Close modal on Escape key
    if (e.key === 'Escape') {
        const openModal = document.querySelector('.modal[style*="flex"]');
        if (openModal) {
            openModal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }
});

// Add CSS animations
const adminOrdersStyles = document.createElement('style');
adminOrdersStyles.textContent = `
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
document.head.appendChild(adminOrdersStyles);