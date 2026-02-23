/**
 * Admin Contact Module JavaScript
 */

document.addEventListener('DOMContentLoaded', function () {

    // Initialize all contact functionality
    initContactTable();
    initBulkActions();
    initDeleteModal();
    initReplyModal();
    initQuickActions();

    /**
     * Initialize contact table functionality
     */
    function initContactTable() {
        // Select all checkbox functionality
        const selectAllCheckbox = document.getElementById('select-all');
        const contactCheckboxes = document.querySelectorAll('.contact-checkbox');

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function () {
                contactCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateBulkActionsState();
            });
        }

        // Individual checkbox change
        contactCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                updateSelectAllState();
                updateBulkActionsState();
            });
        });

        // Update select all state based on individual checkboxes
        function updateSelectAllState() {
            if (!selectAllCheckbox) return;

            const checkedCount = document.querySelectorAll('.contact-checkbox:checked').length;
            const totalCount = contactCheckboxes.length;

            selectAllCheckbox.checked = checkedCount === totalCount;
            selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < totalCount;
        }

        // Update bulk actions state
        function updateBulkActionsState() {
            const checkedCount = document.querySelectorAll('.contact-checkbox:checked').length;
            const bulkActionSelect = document.getElementById('bulk-action');
            const applyBulkButton = document.getElementById('apply-bulk');

            if (bulkActionSelect && applyBulkButton) {
                bulkActionSelect.disabled = checkedCount === 0;
                applyBulkButton.disabled = checkedCount === 0 || !bulkActionSelect.value;
            }
        }
    }

    /**
     * Initialize bulk actions
     */
    function initBulkActions() {
        const bulkActionSelect = document.getElementById('bulk-action');
        const applyBulkButton = document.getElementById('apply-bulk');

        if (bulkActionSelect) {
            bulkActionSelect.addEventListener('change', function () {
                const checkedCount = document.querySelectorAll('.contact-checkbox:checked').length;
                if (applyBulkButton) {
                    applyBulkButton.disabled = checkedCount === 0 || !this.value;
                }
            });
        }

        if (applyBulkButton) {
            applyBulkButton.addEventListener('click', function () {
                const action = bulkActionSelect.value;
                const checkedIds = Array.from(document.querySelectorAll('.contact-checkbox:checked'))
                    .map(cb => cb.value);

                if (action && checkedIds.length > 0) {
                    handleBulkAction(action, checkedIds);
                }
            });
        }
    }

    /**
     * Handle bulk actions
     */
    function handleBulkAction(action, ids) {
        let message = '';

        switch (action) {
            case 'mark-read':
                message = `Đánh dấu đã đọc ${ids.length} liên hệ đã chọn?`;
                break;
            case 'mark-replied':
                message = `Đánh dấu đã trả lời ${ids.length} liên hệ đã chọn?`;
                break;
            case 'delete':
                message = `Xóa ${ids.length} liên hệ đã chọn? Hành động này không thể hoàn tác!`;
                break;
            default:
                return;
        }

        if (confirm(message)) {
            // Actual implementation: submit a form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '?page=admin&module=contact&action=bulk';

            const idsInput = document.createElement('input');
            idsInput.type = 'hidden';
            idsInput.name = 'ids';
            idsInput.value = JSON.stringify(ids);

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
     * Initialize delete modal
     */
    function initDeleteModal() {
        const deleteButtons = document.querySelectorAll('.delete-btn');
        const deleteModal = document.getElementById('deleteModal');
        const cancelDeleteBtn = document.getElementById('cancelDelete');
        const confirmDeleteBtn = document.getElementById('confirmDelete');
        const modalClose = deleteModal?.querySelector('.modal-close');

        if (!deleteModal) return;

        // Open delete modal
        deleteButtons.forEach(button => {
            button.addEventListener('click', function () {
                const contactId = this.dataset.id;
                const contactName = this.dataset.name;

                const nameElement = document.getElementById('deleteContactName');
                if (nameElement) {
                    nameElement.textContent = contactName;
                }
                deleteModal.style.display = 'flex';

                // Set up confirm delete handler
                if (confirmDeleteBtn) {
                    confirmDeleteBtn.onclick = function () {
                        handleDeleteContact(contactId, contactName);
                    };
                }
            });
        });

        // Close modal handlers
        [cancelDeleteBtn, modalClose].forEach(element => {
            if (element) {
                element.addEventListener('click', function () {
                    deleteModal.style.display = 'none';
                });
            }
        });

        // Close modal when clicking outside
        window.addEventListener('click', function (event) {
            if (event.target === deleteModal) {
                deleteModal.style.display = 'none';
            }
        });
    }

    /**
     * Handle delete contact
     */
    function handleDeleteContact(id, name) {
        if (confirm(`Bạn có chắc chắn muốn xóa liên hệ từ "${name}"?`)) {
            window.location.href = `?page=admin&module=contact&action=delete&id=${id}`;
        }
    }

    /**
     * Initialize reply modal
     */
    function initReplyModal() {
        const replyButtons = document.querySelectorAll('.reply-btn');
        const replyModal = document.getElementById('replyModal');
        const cancelReplyBtn = document.getElementById('cancelReply');
        const confirmReplyBtn = document.getElementById('confirmReply');
        const modalClose = replyModal?.querySelector('.modal-close');

        if (!replyModal) return;

        // Open reply modal
        replyButtons.forEach(button => {
            button.addEventListener('click', function () {
                const email = this.dataset.email;
                const subject = this.dataset.subject;

                const emailElement = document.getElementById('replyEmail');
                const subjectElement = document.getElementById('replySubject');

                if (emailElement) emailElement.textContent = email;
                if (subjectElement) subjectElement.textContent = subject;

                replyModal.style.display = 'flex';

                // Set up confirm reply handler
                if (confirmReplyBtn) {
                    confirmReplyBtn.onclick = function () {
                        handleReplyEmail(email, subject);
                    };
                }
            });
        });

        // Close modal handlers
        [cancelReplyBtn, modalClose].forEach(element => {
            if (element) {
                element.addEventListener('click', function () {
                    replyModal.style.display = 'none';
                });
            }
        });

        // Close modal when clicking outside
        window.addEventListener('click', function (event) {
            if (event.target === replyModal) {
                replyModal.style.display = 'none';
            }
        });
    }

    /**
     * Handle reply email
     */
    function handleReplyEmail(email, subject) {
        // Create mailto link
        const mailtoLink = `mailto:${email}?subject=${encodeURIComponent(subject)}`;

        // Open email client
        window.location.href = mailtoLink;

        // Close modal
        const replyModal = document.getElementById('replyModal');
        if (replyModal) {
            replyModal.style.display = 'none';
        }

        showNotification('Đã mở ứng dụng email', 'info');
    }

    /**
     * Initialize quick actions
     */
    function initQuickActions() {
        // Mark as read buttons
        const markReadButtons = document.querySelectorAll('.mark-read-btn');
        markReadButtons.forEach(button => {
            button.addEventListener('click', function () {
                const contactId = this.dataset.id;
                handleMarkAsRead(contactId);
            });
        });

        // Mark as replied buttons
        const markRepliedButtons = document.querySelectorAll('.mark-replied-btn');
        markRepliedButtons.forEach(button => {
            button.addEventListener('click', function () {
                const contactId = this.dataset.id;
                handleMarkAsReplied(contactId);
            });
        });

        // Call phone buttons
        const callButtons = document.querySelectorAll('.call-btn');
        callButtons.forEach(button => {
            button.addEventListener('click', function () {
                const phone = this.dataset.phone;
                handleCallPhone(phone);
            });
        });
    }

    /**
     * Handle mark as read
     */
    function handleMarkAsRead(id) {
        // Actual implementation: redirect to mark as read action
        window.location.href = `?page=admin&module=contact&action=mark_read&id=${id}`;
    }

    /**
     * Handle mark as replied
     */
    function handleMarkAsReplied(id) {
        if (confirm('Đánh dấu liên hệ này là đã trả lời?')) {
            // Actual implementation: redirect to mark as replied action
            window.location.href = `?page=admin&module=contact&action=mark_replied&id=${id}`;
        }
    }

    /**
     * Handle call phone
     */
    function handleCallPhone(phone) {
        if (confirm(`Gọi điện thoại đến số ${phone}?`)) {
            // Create tel link
            const telLink = `tel:${phone}`;
            window.location.href = telLink;

            showNotification(`Đang gọi ${phone}`, 'info');
        }
    }

    /**
     * Show notification
     */
    function showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${getNotificationIcon(type)}"></i>
                <span>${message}</span>
            </div>
            <button class="notification-close">&times;</button>
        `;

        // Add styles
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${getNotificationColor(type)};
            color: white;
            padding: 12px 16px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1001;
            display: flex;
            align-items: center;
            gap: 12px;
            max-width: 400px;
            animation: slideIn 0.3s ease-out;
        `;

        // Add to page
        document.body.appendChild(notification);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.style.animation = 'slideOut 0.3s ease-in';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 300);
            }
        }, 5000);

        // Manual close
        const closeBtn = notification.querySelector('.notification-close');
        closeBtn.addEventListener('click', () => {
            notification.remove();
        });
    }

    /**
     * Get notification icon
     */
    function getNotificationIcon(type) {
        switch (type) {
            case 'success': return 'check-circle';
            case 'error': return 'exclamation-triangle';
            case 'warning': return 'exclamation-triangle';
            default: return 'info-circle';
        }
    }

    /**
     * Get notification color
     */
    function getNotificationColor(type) {
        switch (type) {
            case 'success': return '#10B981';
            case 'error': return '#EF4444';
            case 'warning': return '#F59E0B';
            default: return '#3B82F6';
        }
    }
});

/**
 * Global functions for contact management
 */

// Export contact data
function exportContacts() {
    if (confirm('Xuất dữ liệu liên hệ ra file CSV?')) {
        window.location.href = '?page=admin&module=contact&action=export';
    }
}

// Print contact details
function printContact() {
    window.print();
}

// Copy email address
function copyEmail(email) {
    navigator.clipboard.writeText(email).then(() => {
        showNotification(`Đã sao chép email: ${email}`, 'success');
    }).catch(() => {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = email;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showNotification(`Đã sao chép email: ${email}`, 'success');
    });
}

// Copy phone number
function copyPhone(phone) {
    navigator.clipboard.writeText(phone).then(() => {
        showNotification(`Đã sao chép số điện thoại: ${phone}`, 'success');
    }).catch(() => {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = phone;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showNotification(`Đã sao chép số điện thoại: ${phone}`, 'success');
    });
}

// Show notification function for global use
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${getNotificationIcon(type)}"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close">&times;</button>
    `;

    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${getNotificationColor(type)};
        color: white;
        padding: 12px 16px;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        z-index: 1001;
        display: flex;
        align-items: center;
        gap: 12px;
        max-width: 400px;
        animation: slideIn 0.3s ease-out;
    `;

    // Add to page
    document.body.appendChild(notification);

    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.style.animation = 'slideOut 0.3s ease-in';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 300);
        }
    }, 5000);

    // Manual close
    const closeBtn = notification.querySelector('.notification-close');
    closeBtn.addEventListener('click', () => {
        notification.remove();
    });
}

function getNotificationIcon(type) {
    switch (type) {
        case 'success': return 'check-circle';
        case 'error': return 'exclamation-triangle';
        case 'warning': return 'exclamation-triangle';
        default: return 'info-circle';
    }
}

function getNotificationColor(type) {
    switch (type) {
        case 'success': return '#10B981';
        case 'error': return '#EF4444';
        case 'warning': return '#F59E0B';
        default: return '#3B82F6';
    }
}

// Add CSS animations
const adminContactStyles = document.createElement('style');
adminContactStyles.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
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
        color: white;
        font-size: 18px;
        cursor: pointer;
        padding: 0;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: background 0.2s;
    }
    
    .notification-close:hover {
        background: rgba(255, 255, 255, 0.2);
    }
    
    .field-error {
        display: block !important;
        margin-top: 4px !important;
    }
    
    .form-group input.error,
    .form-group select.error,
    .form-group textarea.error {
        border-color: #EF4444 !important;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
    }
`;
document.head.appendChild(adminContactStyles);