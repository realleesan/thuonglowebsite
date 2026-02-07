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
     * Export customers (placeholder)
     */
    window.exportCustomers = function() {
        alert('Chức năng xuất Excel đang được phát triển');
    };
    
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
