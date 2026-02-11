// User Orders JavaScript - Interactive functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize orders functionality
    initOrdersSearch();
    initOrdersFilters();
    initOrdersActions();
    
    console.log('User Orders JavaScript loaded successfully');
});

// Search functionality
function initOrdersSearch() {
    const searchInput = document.getElementById('ordersSearch');
    
    if (searchInput) {
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                applyFilters();
            }, 300);
        });
        
        // Clear search on Escape key
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                this.value = '';
                applyFilters();
            }
        });
    }
}

// Filter functionality
function initOrdersFilters() {
    const statusFilter = document.getElementById('statusFilter');
    const typeFilter = document.getElementById('typeFilter');
    
    if (statusFilter) {
        statusFilter.addEventListener('change', applyFilters);
    }
    
    if (typeFilter) {
        typeFilter.addEventListener('change', applyFilters);
    }
}

// Apply filters and update URL
function applyFilters() {
    const searchQuery = document.getElementById('ordersSearch')?.value || '';
    const statusFilter = document.getElementById('statusFilter')?.value || 'all';
    const typeFilter = document.getElementById('typeFilter')?.value || 'all';
    
    // Build URL parameters
    const params = new URLSearchParams(window.location.search);
    params.set('page', 'users');
    params.set('module', 'orders');
    
    if (searchQuery) {
        params.set('search', searchQuery);
    } else {
        params.delete('search');
    }
    
    if (statusFilter !== 'all') {
        params.set('status', statusFilter);
    } else {
        params.delete('status');
    }
    
    if (typeFilter !== 'all') {
        params.set('type', typeFilter);
    } else {
        params.delete('type');
    }
    
    // Update URL and reload page
    const newUrl = window.location.pathname + '?' + params.toString();
    window.location.href = newUrl;
}

// Orders actions functionality
function initOrdersActions() {
    // Handle reorder buttons
    const reorderButtons = document.querySelectorAll('.orders-action-reorder');
    reorderButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            handleReorder(this);
        });
    });
    
    // Handle delete confirmations
    const deleteButtons = document.querySelectorAll('.orders-action-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Bạn có chắc chắn muốn hủy đơn hàng này?')) {
                e.preventDefault();
            }
        });
    });
    
    // Handle order item clicks (for mobile)
    const orderItems = document.querySelectorAll('.orders-item');
    orderItems.forEach(item => {
        item.addEventListener('click', function(e) {
            // Only handle click if not clicking on action buttons
            if (!e.target.closest('.orders-item-actions')) {
                const viewButton = this.querySelector('.orders-action-view');
                if (viewButton && window.innerWidth <= 768) {
                    window.location.href = viewButton.href;
                }
            }
        });
    });
}

// Handle reorder functionality
function handleReorder(button) {
    const orderItem = button.closest('.orders-item');
    const orderId = orderItem.querySelector('.orders-item-id strong').textContent.replace('#', '');
    
    // Show loading state
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
    button.style.pointerEvents = 'none';
    
    // Simulate API call
    setTimeout(() => {
        // In a real application, this would make an API call
        showMessage('Đã thêm sản phẩm vào giỏ hàng thành công!', 'success');
        
        // Reset button
        button.innerHTML = '<i class="fas fa-redo"></i> Đặt lại';
        button.style.pointerEvents = 'auto';
        
        // Optionally redirect to cart
        setTimeout(() => {
            window.location.href = '?page=users&module=cart';
        }, 1500);
    }, 1000);
}

// Show success/error messages
function showMessage(message, type = 'info') {
    // Remove existing messages
    const existingMessages = document.querySelectorAll('.orders-message');
    existingMessages.forEach(msg => msg.remove());
    
    // Create new message
    const messageDiv = document.createElement('div');
    messageDiv.className = `orders-message orders-message-${type}`;
    messageDiv.textContent = message;
    
    // Insert at the top of orders content
    const ordersContent = document.querySelector('.user-orders');
    if (ordersContent) {
        ordersContent.insertBefore(messageDiv, ordersContent.firstChild);
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            messageDiv.style.opacity = '0';
            setTimeout(() => {
                messageDiv.remove();
            }, 300);
        }, 5000);
    }
}

// Utility functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}

// Export functions for use in other scripts
window.OrdersManager = {
    applyFilters,
    showMessage,
    formatCurrency,
    formatDate
};

// Handle responsive behavior
function handleResponsive() {
    const isMobile = window.innerWidth <= 768;
    
    // Add mobile-specific behaviors
    if (isMobile) {
        // Make order items more touch-friendly
        const orderItems = document.querySelectorAll('.orders-item');
        orderItems.forEach(item => {
            item.style.cursor = 'pointer';
        });
    }
}

// Listen for window resize
window.addEventListener('resize', handleResponsive);
handleResponsive(); // Call on initial load

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + K to focus search
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const searchInput = document.getElementById('ordersSearch');
        if (searchInput) {
            searchInput.focus();
            searchInput.select();
        }
    }
    
    // Escape to clear filters
    if (e.key === 'Escape') {
        const searchInput = document.getElementById('ordersSearch');
        const statusFilter = document.getElementById('statusFilter');
        const typeFilter = document.getElementById('typeFilter');
        
        if (searchInput && searchInput.value) {
            searchInput.value = '';
            applyFilters();
        } else if (statusFilter && statusFilter.value !== 'all') {
            statusFilter.value = 'all';
            applyFilters();
        } else if (typeFilter && typeFilter.value !== 'all') {
            typeFilter.value = 'all';
            applyFilters();
        }
    }
});

// Print functionality (for order details)
function printOrder(orderId) {
    window.print();
}

// Export order data (CSV format)
function exportOrders() {
    // This would typically make an API call to generate and download a CSV
    showMessage('Chức năng xuất dữ liệu đang được phát triển', 'info');
}

// Bulk actions (for future implementation)
function initBulkActions() {
    // Checkbox selection
    const selectAllCheckbox = document.getElementById('selectAllOrders');
    const orderCheckboxes = document.querySelectorAll('.order-checkbox');
    
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            orderCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActions();
        });
    }
    
    orderCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
    });
}

function updateBulkActions() {
    const selectedOrders = document.querySelectorAll('.order-checkbox:checked');
    const bulkActionsBar = document.getElementById('bulkActionsBar');
    
    if (bulkActionsBar) {
        if (selectedOrders.length > 0) {
            bulkActionsBar.style.display = 'flex';
            bulkActionsBar.querySelector('.selected-count').textContent = selectedOrders.length;
        } else {
            bulkActionsBar.style.display = 'none';
        }
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Add any additional initialization here
    console.log('Orders page fully loaded');
});