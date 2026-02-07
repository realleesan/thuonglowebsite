/**
 * Affiliate Reports Module JavaScript
 * Xử lý charts và export reports
 */

(function() {
    'use strict';

    // ===================================
    // Export Functions
    // ===================================
    window.exportClicksReport = function() {
        showAlert('Tính năng xuất báo cáo đang được phát triển', 'info');
    };

    window.exportOrdersReport = function() {
        showAlert('Tính năng xuất báo cáo đang được phát triển', 'info');
    };

    // ===================================
    // Charts Initialization
    // ===================================
    
    // Clicks by Date Chart
    const clicksByDateCanvas = document.getElementById('clicksByDateChart');
    if (clicksByDateCanvas && typeof Chart !== 'undefined') {
        const labels = JSON.parse(clicksByDateCanvas.dataset.labels || '[]');
        const clicks = JSON.parse(clicksByDateCanvas.dataset.clicks || '[]');
        const unique = JSON.parse(clicksByDateCanvas.dataset.unique || '[]');
        
        new Chart(clicksByDateCanvas, {
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
        
        new Chart(revenueByDateCanvas, {
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
