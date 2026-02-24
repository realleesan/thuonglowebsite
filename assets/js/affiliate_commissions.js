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
     * Export commissions to Excel
     */
    window.exportCommissions = function() {
        const monthFilter = document.getElementById('monthFilter')?.value || '';
        const yearFilter = document.getElementById('yearFilter')?.value || '';
        const statusFilter = document.getElementById('statusFilter')?.value || '';
        const typeFilter = document.getElementById('typeFilter')?.value || '';
        
        const params = new URLSearchParams();
        if (monthFilter) params.append('month', monthFilter);
        if (yearFilter) params.append('year', yearFilter);
        if (statusFilter) params.append('status', statusFilter);
        if (typeFilter) params.append('type', typeFilter);
        params.append('export', '1');
        
        // Redirect to export endpoint
        window.location.href = '/api/affiliate/commissions/export?' + params.toString();
    };
    
    /**
     * View commission detail via API
     */
    window.viewCommissionDetail = function(id) {
        if (!id) return;
        
        // Show loading
        const modal = document.getElementById('commissionDetailModal');
        const modalContent = document.getElementById('commissionDetailContent');
        
        if (!modal || !modalContent) {
            // Fallback: redirect to detail page
            window.location.href = '?page=affiliate&module=commissions&action=detail&id=' + id;
            return;
        }
        
        modalContent.innerHTML = '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i> Đang tải...</div>';
        modal.style.display = 'flex';
        
        fetch('/api/affiliate/commissions/' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.commission) {
                const c = data.commission;
                modalContent.innerHTML = `
                    <div class="detail-header">
                        <h3>Chi tiết hoa hồng #${c.id}</h3>
                        <span class="badge badge-${c.status === 'completed' ? 'success' : (c.status === 'pending' ? 'warning' : 'error')}">${getStatusText(c.status)}</span>
                    </div>
                    <div class="detail-body">
                        <div class="detail-row">
                            <span class="label">Loại:</span>
                            <span class="value">${c.type === 'logistics' ? 'Logistics' : 'Data Subscription'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Số tiền đơn hàng:</span>
                            <span class="value">${formatNumber(c.order_amount)} đ</span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Hoa hồng:</span>
                            <span class="value highlight">${formatNumber(c.commission_amount)} đ</span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Tỷ lệ:</span>
                            <span class="value">${(c.commission_rate * 100).toFixed(1)}%</span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Đơn hàng ID:</span>
                            <span class="value">#${c.order_id}</span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Khách hàng:</span>
                            <span class="value">${c.customer_name || 'N/A'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Ngày tạo:</span>
                            <span class="value">${c.created_at}</span>
                        </div>
                        ${c.completed_at ? `
                        <div class="detail-row">
                            <span class="label">Ngày hoàn thành:</span>
                            <span class="value">${c.completed_at}</span>
                        </div>
                        ` : ''}
                    </div>
                    <div class="detail-footer">
                        <button onclick="closeCommissionDetailModal()" class="btn btn-secondary">Đóng</button>
                    </div>
                `;
            } else {
                modalContent.innerHTML = '<div class="error-message">Không thể tải thông tin hoa hồng</div>';
            }
        })
        .catch(error => {
            console.error('Error loading commission detail:', error);
            modalContent.innerHTML = '<div class="error-message">Lỗi kết nối. Vui lòng thử lại.</div>';
        });
    };
    
    /**
     * Close commission detail modal
     */
    window.closeCommissionDetailModal = function() {
        const modal = document.getElementById('commissionDetailModal');
        if (modal) {
            modal.style.display = 'none';
        }
    };
    
    /**
     * Get status text in Vietnamese
     */
    function getStatusText(status) {
        const statusMap = {
            'pending': 'Chờ xử lý',
            'completed': 'Hoàn thành',
            'cancelled': 'Đã hủy',
            'processing': 'Đang xử lý'
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
        
        // Close modal on outside click
        const modal = document.getElementById('commissionDetailModal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeCommissionDetailModal();
                }
            });
        }
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCommissions);
    } else {
        initCommissions();
    }
    
})();
