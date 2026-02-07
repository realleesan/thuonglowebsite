/**
 * Affiliate Chart Configuration
 * Initialize and configure Chart.js charts for affiliate dashboard
 */

(function() {
    'use strict';
    
    /**
     * Initialize charts when DOM is ready
     */
    function initCharts() {
        // Get chart data from data attributes
        const chartsGrid = document.querySelector('.charts-grid');
        if (!chartsGrid) {
            console.log('Charts grid not found');
            return;
        }
        
        // Parse chart data from data attributes
        const chartData = {
            revenue: {
                labels: JSON.parse(chartsGrid.dataset.revenueLabels || '[]'),
                data: JSON.parse(chartsGrid.dataset.revenueData || '[]')
            },
            clicks: {
                labels: JSON.parse(chartsGrid.dataset.clicksLabels || '[]'),
                data: JSON.parse(chartsGrid.dataset.clicksData || '[]')
            },
            conversion: {
                labels: JSON.parse(chartsGrid.dataset.conversionLabels || '[]'),
                data: JSON.parse(chartsGrid.dataset.conversionData || '[]')
            }
        };
        
        // Check if Chart.js is loaded
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded');
            return;
        }
        
        // Revenue Chart (Line Chart)
        const revenueCtx = document.getElementById('revenueChart');
        if (revenueCtx && chartData.revenue.labels.length > 0) {
            new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: chartData.revenue.labels,
                    datasets: [{
                        label: 'Doanh thu (VNĐ)',
                        data: chartData.revenue.data,
                        borderColor: '#356DF1',
                        backgroundColor: 'rgba(53, 109, 241, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#356DF1',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#1F2937',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            padding: 12,
                            borderColor: '#356DF1',
                            borderWidth: 1,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return new Intl.NumberFormat('vi-VN', {
                                        style: 'currency',
                                        currency: 'VND'
                                    }).format(context.parsed.y);
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return (value / 1000000) + 'M';
                                }
                            },
                            grid: {
                                color: '#E5E7EB'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }
        
        // Clicks Chart (Bar Chart)
        const clicksCtx = document.getElementById('clicksChart');
        if (clicksCtx && chartData.clicks.labels.length > 0) {
            new Chart(clicksCtx, {
                type: 'bar',
                data: {
                    labels: chartData.clicks.labels,
                    datasets: [{
                        label: 'Lượt click',
                        data: chartData.clicks.data,
                        backgroundColor: '#10B981',
                        borderRadius: 8,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#1F2937',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            padding: 12,
                            borderColor: '#10B981',
                            borderWidth: 1,
                            displayColors: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString();
                                }
                            },
                            grid: {
                                color: '#E5E7EB'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }
        
        // Conversion Chart (Doughnut Chart)
        const conversionCtx = document.getElementById('conversionChart');
        if (conversionCtx && chartData.conversion.labels.length > 0) {
            new Chart(conversionCtx, {
                type: 'doughnut',
                data: {
                    labels: chartData.conversion.labels,
                    datasets: [{
                        data: chartData.conversion.data,
                        backgroundColor: [
                            '#10B981', // Hoàn thành - Green
                            '#F59E0B', // Đang xử lý - Orange
                            '#EF4444'  // Đã hủy - Red
                        ],
                        borderWidth: 0,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    aspectRatio: 1.5,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                font: {
                                    size: 12,
                                    family: 'Inter'
                                },
                                usePointStyle: true,
                                pointStyle: 'circle',
                                boxWidth: 8,
                                boxHeight: 8
                            }
                        },
                        tooltip: {
                            backgroundColor: '#1F2937',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            padding: 12,
                            displayColors: true,
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': ' + context.parsed + '%';
                                }
                            }
                        }
                    },
                    cutout: '60%',
                    layout: {
                        padding: {
                            top: 10,
                            bottom: 10
                        }
                    }
                }
            });
        }
        
        console.log('Charts initialized successfully');
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCharts);
    } else {
        initCharts();
    }
    
})();
