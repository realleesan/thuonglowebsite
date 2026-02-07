/**
 * Affiliate Commissions JavaScript
 * Handles commissions filtering and interactions
 */

(function() {
    'use strict';
    
    /**
     * Filter commissions history
     */
    window.filterCommissions = function() {
        const monthFilter = document.getElementById('monthFilter')?.value || '';
        const yearFilter = document.getElementById('yearFilter')?.value || '';
        const statusFilter = document.getElementById('statusFilter')?.value || '';
        const typeFilter = document.getElementById('typeFilter')?.value || '';
        
        const table = document.getElementById('commissionsTable');
        if (!table) return;
        
        const rows = table.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const date = row.querySelector('.commission-date')?.textContent || '';
            const status = row.querySelector('.badge')?.textContent.toLowerCase() || '';
            const type = row.querySelector('.badge-purple, .badge-orange')?.textContent || '';
            
            // Extract month and year from date (format: dd/mm/yyyy)
            const dateParts = date.split('/');
            const rowMonth = dateParts[1] || '';
            const rowYear = dateParts[2] || '';
            
            const matchesMonth = !monthFilter || rowMonth === monthFilter;
            const matchesYear = !yearFilter || rowYear === yearFilter;
            const matchesStatus = !statusFilter || status.includes(statusFilter.toLowerCase());
            const matchesType = !typeFilter || type.includes(typeFilter);
            
            row.style.display = matchesMonth && matchesYear && matchesStatus && matchesType ? '' : 'none';
        });
    };
    
    /**
     * Reset commission filters
     */
    window.resetCommissionFilters = function() {
        const monthFilter = document.getElementById('monthFilter');
        const yearFilter = document.getElementById('yearFilter');
        const statusFilter = document.getElementById('statusFilter');
        const typeFilter = document.getElementById('typeFilter');
        
        if (monthFilter) monthFilter.value = '';
        if (yearFilter) yearFilter.value = '';
        if (statusFilter) statusFilter.value = '';
        if (typeFilter) typeFilter.value = '';
        
        const table = document.getElementById('commissionsTable');
        if (table) {
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => row.style.display = '');
        }
    };
    
    /**
     * Export commissions (placeholder)
     */
    window.exportCommissions = function() {
        alert('Chức năng xuất Excel đang được phát triển');
    };
    
    /**
     * View commission detail (placeholder)
     */
    window.viewCommissionDetail = function(id) {
        alert('Xem chi tiết hoa hồng #' + id);
    };
    
    /**
     * Initialize commissions module
     */
    function initCommissions() {
        console.log('Affiliate Commissions initialized');
        
        // Auto-filter on change
        const monthFilter = document.getElementById('monthFilter');
        const yearFilter = document.getElementById('yearFilter');
        const statusFilter = document.getElementById('statusFilter');
        const typeFilter = document.getElementById('typeFilter');
        
        if (monthFilter) monthFilter.addEventListener('change', filterCommissions);
        if (yearFilter) yearFilter.addEventListener('change', filterCommissions);
        if (statusFilter) statusFilter.addEventListener('change', filterCommissions);
        if (typeFilter) typeFilter.addEventListener('change', filterCommissions);
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCommissions);
    } else {
        initCommissions();
    }
    
})();
