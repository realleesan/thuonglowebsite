// User Dashboard Chart.js Configuration - Clean & Professional
document.addEventListener('DOMContentLoaded', function() {
    // Chart.js default configuration
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.font.size = 12;
    Chart.defaults.color = '#374151';

    // Colors from design system
    const colors = {
        primary: '#356DF1',
        secondary: '#000000',
        success: '#10B981',
        warning: '#F59E0B',
        danger: '#EF4444',
        info: '#3B82F6',
        light: '#F9FAFB',
        border: '#E5E7EB'
    };

    // Store chart instances to destroy them before recreating
    const chartInstances = {};
    
    // Flag to prevent multiple initializations
    let isChartsInitialized = false;

    // Initialize charts with data from PHP (passed via data attributes or global variables)
    if (!isChartsInitialized) {
        initializeCharts();
        isChartsInitialized = true;
    }

    function initializeCharts() {
        // First, destroy all existing charts to ensure clean state
        destroyAllCharts();
        
        // Get chart data from PHP (should be set in the PHP view)
        const chartData = window.dashboardChartData || null;
        
        // Only proceed if we're on the dashboard page with chart data
        if (!chartData) {
            // Silently return if no chart data (other user pages)
            return;
        }
        
        // Check if we have any chart elements on the page
        const hasChartElements = document.getElementById('orderStatusChart') || 
                              document.getElementById('spendingChart') || 
                              document.getElementById('revenueChart') || 
                              document.getElementById('categoryChart') || 
                              document.getElementById('purchaseTrendChart');
        
        if (!hasChartElements) {
            // No chart elements found, probably not on dashboard page
            return;
        }
        
        try {
            // Initialize all charts with data from PHP
            if (chartData.orderStatus && document.getElementById('orderStatusChart')) {
                initOrderStatusChart(chartData.orderStatus);
            }
            if (chartData.monthlySpending && document.getElementById('spendingChart')) {
                initSpendingChart(chartData.monthlySpending);
            }
            if (chartData.orderDistribution && document.getElementById('categoryChart')) {
                initCategoryChart(chartData.orderDistribution);
            }
            if (chartData.revenue && document.getElementById('revenueChart')) {
                initRevenueChart(chartData.revenue);
            }
            if (chartData.purchaseTrend && document.getElementById('purchaseTrendChart')) {
                initPurchaseTrendChart(chartData.purchaseTrend);
            }
        } catch (error) {
            console.error('Error initializing charts:', error);
            // Try to cleanup on error
            destroyAllCharts();
        }
    }

    function destroyAllCharts() {
        Object.keys(chartInstances).forEach(key => {
            if (chartInstances[key]) {
                try {
                    chartInstances[key].destroy();
                } catch (e) {
                    console.warn('Error destroying chart:', key, e);
                }
                chartInstances[key] = null;
            }
        });
    }

    function initSpendingChart(data) {
        const spendingCtx = document.getElementById('spendingChart');
        if (!spendingCtx || !data.labels || !data.data) return;

        // Check if chart already exists on this canvas and destroy it
        const existingChart = Chart.getChart(spendingCtx);
        if (existingChart) {
            existingChart.destroy();
        }

        chartInstances.spending = new Chart(spendingCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Chi tiêu (VNĐ)',
                    data: data.data,
                    backgroundColor: colors.primary,
                    borderRadius: 6,
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
                        backgroundColor: '#ffffff',
                        titleColor: '#374151',
                        bodyColor: '#374151',
                        borderColor: colors.border,
                        borderWidth: 1,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return 'Chi tiêu: ' + context.parsed.y.toLocaleString('vi-VN') + 'đ';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: colors.border,
                            drawBorder: false
                        },
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000000) return (value / 1000000) + 'M';
                                if (value >= 1000) return (value / 1000) + 'K';
                                return value;
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#6B7280',
                            font: {
                                size: 11
                            }
                        }
                    }
                }
            }
        });
    }

    function initRevenueChart(data) {
        const revenueCtx = document.getElementById('revenueChart');
        if (!revenueCtx || !data.labels || !data.data) return;

        // Check if chart already exists on this canvas and destroy it
        const existingChart = Chart.getChart(revenueCtx);
        if (existingChart) {
            existingChart.destroy();
        }

        chartInstances.revenue = new Chart(revenueCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Chi tiêu (triệu VNĐ)',
                    data: data.data,
                    borderColor: colors.primary,
                    backgroundColor: colors.primary + '20',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: colors.primary,
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
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
                        backgroundColor: '#ffffff',
                        titleColor: '#374151',
                        bodyColor: '#374151',
                        borderColor: colors.border,
                        borderWidth: 1,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y + ' triệu VNĐ';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: colors.border,
                            drawBorder: false
                        },
                        ticks: {
                            callback: function(value) {
                                return value + 'M';
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#6B7280'
                        }
                    }
                }
            }
        });
    }

    function initOrderStatusChart(data) {
        const orderStatusCtx = document.getElementById('orderStatusChart');
        if (!orderStatusCtx) return;
        
        // Check if chart already exists on this canvas and destroy it
        const existingChart = Chart.getChart(orderStatusCtx);
        if (existingChart) {
            existingChart.destroy();
        }
        
        if (!data.labels || !data.data || data.labels.length === 0) {
            // Show empty state message
            const ctx = orderStatusCtx.getContext('2d');
            ctx.clearRect(0, 0, orderStatusCtx.width, orderStatusCtx.height);
            ctx.font = '14px Inter';
            ctx.fillStyle = '#9ca3af';
            ctx.textAlign = 'center';
            ctx.fillText('Chưa có dữ liệu đơn hàng', orderStatusCtx.width / 2, orderStatusCtx.height / 2);
            return;
        }

        // Status colors mapping
        const statusColorMap = {
            'Hoàn thành': colors.success,
            'Đang xử lý': colors.info,
            'Chờ xử lý': colors.warning,
            'Đã hủy': colors.danger
        };
        
        const dynamicColors = data.labels.map(label => statusColorMap[label] || colors.primary);

        chartInstances.orderStatus = new Chart(orderStatusCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: data.labels,
                datasets: [{
                    data: data.data,
                    backgroundColor: dynamicColors,
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: '#ffffff',
                        titleColor: '#374151',
                        bodyColor: '#374151',
                        borderColor: colors.border,
                        borderWidth: 1,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                return context.label + ': ' + context.parsed + ' đơn (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    function initCategoryChart(data) {
        const categoryCtx = document.getElementById('categoryChart');
        if (!categoryCtx) return;
        
        // Check if chart already exists on this canvas and destroy it
        const existingChart = Chart.getChart(categoryCtx);
        if (existingChart) {
            existingChart.destroy();
        }
        
        if (!data.labels || !data.data || data.labels.length === 0) {
            // Show empty state message
            const ctx = categoryCtx.getContext('2d');
            ctx.clearRect(0, 0, categoryCtx.width, categoryCtx.height);
            ctx.font = '14px Inter';
            ctx.fillStyle = '#9ca3af';
            ctx.textAlign = 'center';
            ctx.fillText('Chưa có dữ liệu danh mục', categoryCtx.width / 2, categoryCtx.height / 2);
            return;
        }

        // Generate colors dynamically
        const baseColors = [colors.primary, colors.success, colors.info, colors.warning, colors.danger];
        const dynamicColors = data.labels.map((_, i) => baseColors[i % baseColors.length]);

        chartInstances.category = new Chart(categoryCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Số đơn hàng',
                    data: data.data,
                    backgroundColor: dynamicColors,
                    borderRadius: 6,
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
                        backgroundColor: '#ffffff',
                        titleColor: '#374151',
                        bodyColor: '#374151',
                        borderColor: colors.border,
                        borderWidth: 1,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y + ' đơn hàng';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: colors.border,
                            drawBorder: false
                        },
                        ticks: {
                            precision: 0
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#6B7280',
                            font: {
                                size: 11
                            }
                        }
                    }
                }
            }
        });
    }

    function initPurchaseTrendChart(data) {
        const purchaseTrendCtx = document.getElementById('purchaseTrendChart');
        if (!purchaseTrendCtx || !data.labels || !data.data) return;

        // Check if chart already exists on this canvas and destroy it
        const existingChart = Chart.getChart(purchaseTrendCtx);
        if (existingChart) {
            existingChart.destroy();
        }

        chartInstances.purchaseTrend = new Chart(purchaseTrendCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Số đơn hàng',
                    data: data.data,
                    borderColor: colors.success,
                    backgroundColor: colors.success + '20',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: colors.success,
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
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
                        backgroundColor: '#ffffff',
                        titleColor: '#374151',
                        bodyColor: '#374151',
                        borderColor: colors.border,
                        borderWidth: 1,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y + ' đơn hàng';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: colors.border,
                            drawBorder: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#6B7280'
                        }
                    }
                }
            }
        });
    }

    // Chart period change handler
    const revenueChartPeriod = document.getElementById('revenueChartPeriod');
    if (revenueChartPeriod) {
        revenueChartPeriod.addEventListener('change', function() {
            const period = this.value;
            // Reload page with new period parameter
            const url = new URL(window.location);
            url.searchParams.set('chart_period', period);
            window.location.href = url.toString();
        });
    }
});