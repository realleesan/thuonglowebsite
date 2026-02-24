/**
 * Affiliate Customers JavaScript
 * Handles customers filtering and interactions
 */

(function() {
    'use strict';
    
    /**
     * Filter customers
     */
    window.filterCustomers = function() {
        const searchTerm = document.getElementById('searchInput')?.value.toLowerCase() || '';
        const statusFilter = document.getElementById('statusFilter')?.value || '';
        const sortBy = document.getElementById('sortBy')?.value || '';
        
        const table = document.getElementById('customersTable');
        if (!table) return;
        
        const rows = Array.from(table.querySelectorAll('tbody tr'));
        
        // Filter rows
        rows.forEach(row => {
            const name = row.querySelector('.customer-name')?.textContent.toLowerCase() || '';
            const email = row.querySelector('.contact-item:nth-child(1)')?.textContent.toLowerCase() || '';
            const phone = row.querySelector('.contact-item:nth-child(2)')?.textContent.toLowerCase() || '';
            const statusBadge = row.querySelector('.badge')?.textContent.toLowerCase() || '';
            const status = statusBadge.includes('hoạt động') ? 'active' : 'inactive';
            
            const matchesSearch = !searchTerm || name.includes(searchTerm) || email.includes(searchTerm) || phone.includes(searchTerm);
            const matchesStatus = !statusFilter || status === statusFilter;
            
            row.style.display = matchesSearch && matchesStatus ? '' : 'none';
        });
        
        // Sort rows
        const visibleRows = rows.filter(row => row.style.display !== 'none');
        visibleRows.sort((a, b) => {
            switch(sortBy) {
                case 'registered_date_desc':
                    return new Date(b.querySelector('.customer-date')?.textContent.split('/').reverse().join('-') || '') - 
                           new Date(a.querySelector('.customer-date')?.textContent.split('/').reverse().join('-') || '');
                case 'registered_date_asc':
                    return new Date(a.querySelector('.customer-date')?.textContent.split('/').reverse().join('-') || '') - 
                           new Date(b.querySelector('.customer-date')?.textContent.split('/').reverse().join('-') || '');
                case 'total_spent_desc':
                    return parseInt(b.querySelector('.customer-spent')?.textContent.replace(/\D/g, '') || '0') - 
                           parseInt(a.querySelector('.customer-spent')?.textContent.replace(/\D/g, '') || '0');
                case 'total_spent_asc':
                    return parseInt(a.querySelector('.customer-spent')?.textContent.replace(/\D/g, '') || '0') - 
                           parseInt(b.querySelector('.customer-spent')?.textContent.replace(/\D/g, '') || '0');
                case 'total_orders_desc':
                    return parseInt(b.querySelector('.customer-orders .badge')?.textContent || '0') - 
                           parseInt(a.querySelector('.customer-orders .badge')?.textContent || '0');
                case 'total_orders_asc':
                    return parseInt(a.querySelector('.customer-orders .badge')?.textContent || '0') - 
                           parseInt(b.querySelector('.customer-orders .badge')?.textContent || '0');
                default:
                    return 0;
            }
        });
        
        // Re-append sorted rows
        const tbody = table.querySelector('tbody');
        if (tbody) {
            visibleRows.forEach(row => tbody.appendChild(row));
        }
    };
    
    /**
     * Reset filters
     */
    window.resetFilters = function() {
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const sortBy = document.getElementById('sortBy');
        
        if (searchInput) searchInput.value = '';
        if (statusFilter) statusFilter.value = '';
        if (sortBy) sortBy.value = 'registered_date_desc';
        
        const table = document.getElementById('customersTable');
        if (table) {
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => row.style.display = '');
        }
    };
    
    /**
     * Export customers to Excel via API
     */
    window.exportCustomers = function() {
        const searchTerm = document.getElementById('searchInput')?.value || '';
        const statusFilter = document.getElementById('statusFilter')?.value || '';
        const sortBy = document.getElementById('sortBy')?.value || '';
        
        const params = new URLSearchParams();
        if (searchTerm) params.append('search', searchTerm);
        if (statusFilter) params.append('status', statusFilter);
        if (sortBy) params.append('sort', sortBy);
        params.append('export', '1');
        
        // Redirect to export endpoint
        window.location.href = '/api/affiliate/customers/export?' + params.toString();
    };
    
    /**
     * View customer detail via API
     */
    window.viewCustomerDetail = function(customerId) {
        if (!customerId) return;
        
        // Redirect to customer detail page
        window.location.href = '?page=affiliate&module=customers&action=detail&id=' + customerId;
    };
    
    /**
     * Load customer orders via API
     */
    window.loadCustomerOrders = function(customerId, page = 1) {
        const ordersContainer = document.getElementById('customerOrders');
        if (!ordersContainer) return;
        
        ordersContainer.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Đang tải...</div>';
        
        fetch(`/api/affiliate/customers/${customerId}/orders?page=${page}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.orders) {
                if (data.orders.length === 0) {
                    ordersContainer.innerHTML = '<div class="empty-state">Chưa có đơn hàng nào</div>';
                    return;
                }
                
                let html = '<div class="orders-list">';
                data.orders.forEach(order => {
                    html += `
                        <div class="order-item">
                            <div class="order-header">
                                <span class="order-code">#${order.order_code}</span>
                                <span class="badge badge-${order.status === 'completed' ? 'success' : 'warning'}">${getStatusText(order.status)}</span>
                            </div>
                            <div class="order-body">
                                <div class="order-info">
                                    <span>Ngày: ${order.created_at}</span>
                                    <span>Tổng: ${formatNumber(order.total_amount)} đ</span>
                                </div>
                                <div class="order-commission">
                                    Hoa hồng: <strong>${formatNumber(order.commission_amount)} đ</strong>
                                </div>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                
                // Add pagination if needed
                if (data.total_pages > 1) {
                    html += '<div class="pagination">';
                    for (let i = 1; i <= data.total_pages; i++) {
                        html += `<button class="page-btn ${i === page ? 'active' : ''}" onclick="loadCustomerOrders(${customerId}, ${i})">${i}</button>`;
                    }
                    html += '</div>';
                }
                
                ordersContainer.innerHTML = html;
            } else {
                ordersContainer.innerHTML = '<div class="error-state">Không thể tải đơn hàng</div>';
            }
        })
        .catch(error => {
            console.error('Error loading customer orders:', error);
            ordersContainer.innerHTML = '<div class="error-state">Lỗi kết nối</div>';
        });
    };
    
    /**
     * Get status text in Vietnamese
     */
    function getStatusText(status) {
        const statusMap = {
            'pending': 'Chờ xử lý',
            'processing': 'Đang xử lý',
            'completed': 'Hoàn thành',
            'cancelled': 'Đã hủy'
        };
        return statusMap[status] || status;
    }
    
    /**
     * Format number with thousand separators
     */
    function formatNumber(number) {
        return new Intl.NumberFormat('vi-VN').format(number);
    }
    
    /**
     * Initialize customers module
     */
    function initCustomers() {
        console.log('Affiliate Customers initialized');
        
        // Auto-filter on input
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const sortBy = document.getElementById('sortBy');
        
        if (searchInput) searchInput.addEventListener('input', filterCustomers);
        if (statusFilter) statusFilter.addEventListener('change', filterCustomers);
        if (sortBy) sortBy.addEventListener('change', filterCustomers);
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCustomers);
    } else {
        initCustomers();
    }
    
})();
