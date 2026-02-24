/**
 * Affiliate Reports Module JavaScript
 * Xử lý charts và export reports
 */

(function() {
    'use strict';

    // ===================================
    // Export Functions
    // ===================================
    
    /**
     * Export clicks report to Excel via API
     */
    window.exportClicksReport = function() {
        const dateFrom = document.getElementById('dateFrom')?.value || '';
        const dateTo = document.getElementById('dateTo')?.value || '';
        const sourceFilter = document.getElementById('sourceFilter')?.value || '';
        
        const params = new URLSearchParams();
        if (dateFrom) params.append('date_from', dateFrom);
        if (dateTo) params.append('date_to', dateTo);
        if (sourceFilter) params.append('source', sourceFilter);
        params.append('export', '1');
        
        // Redirect to export endpoint
        window.location.href = '/api/affiliate/reports/clicks/export?' + params.toString();
    };

    /**
     * Export orders report to Excel via API
     */
    window.exportOrdersReport = function() {
        const dateFrom = document.getElementById('dateFrom')?.value || '';
        const dateTo = document.getElementById('dateTo')?.value || '';
        const statusFilter = document.getElementById('statusFilter')?.value || '';
        
        const params = new URLSearchParams();
        if (dateFrom) params.append('date_from', dateFrom);
        if (dateTo) params.append('date_to', dateTo);
        if (statusFilter) params.append('status', statusFilter);
        params.append('export', '1');
        
        // Redirect to export endpoint
        window.location.href = '/api/affiliate/reports/orders/export?' + params.toString();
    };
    
    /**
     * Refresh clicks data via API
     */
    window.refreshClicksData = function() {
        const container = document.getElementById('clicksDataContainer');
        if (!container) {
            showAlert('Không tìm thấy container dữ liệu', 'error');
            return;
        }
        
        showLoading();
        
        fetch('/api/affiliate/reports/clicks/data')
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                // Update summary cards
                updateSummaryCard('totalClicks', data.total_clicks);
                updateSummaryCard('uniqueClicks', data.unique_clicks);
                updateSummaryCard('clickRate', data.click_rate + '%');
                
                // Update chart if exists
                if (data.chart_data && window.clicksChart) {
                    window.clicksChart.data.labels = data.chart_data.labels;
                    window.clicksChart.data.datasets[0].data = data.chart_data.clicks;
                    window.clicksChart.data.datasets[1].data = data.chart_data.unique;
                    window.clicksChart.update();
                }
                
                showAlert('Đã làm mới dữ liệu', 'success');
            } else {
                showAlert('Không thể làm mới dữ liệu', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            showAlert('Lỗi kết nối', 'error');
            console.error('Refresh error:', error);
        });
    };
    
    /**
     * Refresh orders data via API
     */
    window.refreshOrdersData = function() {
        const container = document.getElementById('ordersDataContainer');
        if (!container) {
            showAlert('Không tìm thấy container dữ liệu', 'error');
            return;
        }
        
        showLoading();
        
        fetch('/api/affiliate/reports/orders/data')
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                // Update summary cards
                updateSummaryCard('totalOrders', data.total_orders);
                updateSummaryCard('totalRevenue', formatNumber(data.total_revenue) + ' đ');
                updateSummaryCard('totalCommission', formatNumber(data.total_commission) + ' đ');
                
                // Update chart if exists
                if (data.chart_data && window.revenueChart) {
                    window.revenueChart.data.labels = data.chart_data.labels;
                    window.revenueChart.data.datasets[0].data = data.chart_data.revenue;
                    window.revenueChart.data.datasets[1].data = data.chart_data.commission;
                    window.revenueChart.update();
                }
                
                showAlert('Đã làm mới dữ liệu', 'success');
            } else {
                showAlert('Không thể làm mới dữ liệu', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            showAlert('Lỗi kết nối', 'error');
            console.error('Refresh error:', error);
        });
    };
    
    /**
     * Update summary card value
     */
    function updateSummaryCard(id, value) {
        const el = document.getElementById(id);
        if (el) {
            el.textContent = value;
            // Add animation
            el.style.color = '#10B981';
            setTimeout(() => {
                el.style.color = '';
            }, 1000);
        }
    }
    
    /**
     * Format number with thousand separators
     */
    function formatNumber(number) {
        return new Intl.NumberFormat('vi-VN').format(number);
    }

    // ===================================
    // Charts Initialization
    // ===================================
    
    // Clicks by Date Chart
    const clicksByDateCanvas = document.getElementById('clicksByDateChart');
    if (clicksByDateCanvas && typeof Chart !== 'undefined') {
        const labels = JSON.parse(clicksByDateCanvas.dataset.labels || '[]');
        const clicks = JSON.parse(clicksByDateCanvas.dataset.clicks || '[]');
        const unique = JSON.parse(clicksByDateCanvas.dataset.unique || '[]');
        
        window.clicksChart = new Chart(clicksByDateCanvas, {
            type: 'line',
            data: {
                labels: labels.map(date => {
                    const d = new Date(date);
                    return d.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit' });
                }),
                datasets: [
                    {
                        label: 'Total Clicks',
                        data: clicks,
                        borderColor: '#356DF1',
                        backgroundColor: 'rgba(53, 109, 241, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Unique Clicks',
                        data: unique,
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('vi-VN');
                            }
                        }
                    }
                }
            }
        });
    }

    // Clicks by Source Chart
    const clicksBySourceCanvas = document.getElementById('clicksBySourceChart');
    if (clicksBySourceCanvas && typeof Chart !== 'undefined') {
        const labels = JSON.parse(clicksBySourceCanvas.dataset.labels || '[]');
        const clicks = JSON.parse(clicksBySourceCanvas.dataset.clicks || '[]');
        
        new Chart(clicksBySourceCanvas, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: clicks,
                    backgroundColor: [
                        '#356DF1',
                        '#10B981',
                        '#F59E0B',
                        '#EF4444'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    }
                }
            }
        });
    }

    // Revenue by Date Chart
    const revenueByDateCanvas = document.getElementById('revenueByDateChart');
    if (revenueByDateCanvas && typeof Chart !== 'undefined') {
        const labels = JSON.parse(revenueByDateCanvas.dataset.labels || '[]');
        const revenue = JSON.parse(revenueByDateCanvas.dataset.revenue || '[]');
        const commission = JSON.parse(revenueByDateCanvas.dataset.commission || '[]');
        
        window.revenueChart = new Chart(revenueByDateCanvas, {
            type: 'bar',
            data: {
                labels: labels.map(date => {
                    const d = new Date(date);
                    return d.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit' });
                }),
                datasets: [
                    {
                        label: 'Doanh Thu',
                        data: revenue,
                        backgroundColor: '#356DF1',
                        borderRadius: 6
                    },
                    {
                        label: 'Hoa Hồng',
                        data: commission,
                        backgroundColor: '#10B981',
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('vi-VN') + ' đ';
                            }
                        }
                    }
                }
            }
        });
    }

    // ===================================
    // Initialize
    // ===================================
    console.log('Reports Module Initialized');

})();
