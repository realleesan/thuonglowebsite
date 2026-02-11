// User Dashboard Chart.js Configuration - Simple & Clean
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

    // Load user data and initialize charts
    loadUserDataAndCharts();

    async function loadUserDataAndCharts() {
        try {
            const response = await fetch('api.php?action=getUserDashboardData');
            const userData = await response.json();
            
            // Initialize all charts with user data
            initRevenueChart(userData.chartData.revenue);
            initOrderDistributionChart(userData.chartData.orderDistribution);
            initOrderStatusChart(userData.chartData.orderStatus);
            initPurchaseTrendChart(userData.chartData.purchaseTrend);
            
        } catch (error) {
            console.error('Error loading user data:', error);
            // Initialize with fallback data
            initChartsWithFallbackData();
        }
    }

    function initRevenueChart(data) {
        const revenueCtx = document.getElementById('revenueChart');
        if (!revenueCtx) return;

        new Chart(revenueCtx.getContext('2d'), {
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

    function initOrderDistributionChart(data) {
        const orderDistributionCtx = document.getElementById('orderDistributionChart');
        if (!orderDistributionCtx) return;

        new Chart(orderDistributionCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: data.labels,
                datasets: [{
                    data: data.data,
                    backgroundColor: [
                        colors.primary,
                        colors.success,
                        colors.info,
                        colors.warning,
                        colors.danger
                    ],
                    borderWidth: 0,
                    cutout: '60%'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: {
                                size: 11
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
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed + '%';
                            }
                        }
                    }
                }
            }
        });
    }

    function initOrderStatusChart(data) {
        const orderStatusCtx = document.getElementById('orderStatusChart');
        if (!orderStatusCtx) return;

        new Chart(orderStatusCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Số đơn hàng',
                    data: data.data,
                    backgroundColor: [
                        colors.success,
                        colors.info,
                        colors.warning,
                        colors.danger
                    ],
                    borderRadius: 4,
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
        if (!purchaseTrendCtx) return;

        new Chart(purchaseTrendCtx.getContext('2d'), {
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

    function initChartsWithFallbackData() {
        // Fallback data if JSON loading fails
        const fallbackData = {
            revenue: {
                labels: ['Tháng 10', 'Tháng 11', 'Tháng 12', 'Tháng 1', 'Tháng 2'],
                data: [2.5, 4.2, 3.8, 5.1, 4.0]
            },
            orderDistribution: {
                labels: ['Data Nguồn Hàng', 'Vận Chuyển', 'Dịch Vụ TT', 'Đánh Hàng', 'Khóa Học'],
                data: [45, 20, 15, 12, 8]
            },
            orderStatus: {
                labels: ['Hoàn thành', 'Đang xử lý', 'Chờ xử lý', 'Đã hủy'],
                data: [18, 3, 2, 1]
            },
            purchaseTrend: {
                labels: ['Tuần 1', 'Tuần 2', 'Tuần 3', 'Tuần 4'],
                data: [3, 5, 4, 7]
            }
        };

        initRevenueChart(fallbackData.revenue);
        initOrderDistributionChart(fallbackData.orderDistribution);
        initOrderStatusChart(fallbackData.orderStatus);
        initPurchaseTrendChart(fallbackData.purchaseTrend);
    }

    // Chart period change handler
    const revenueChartPeriod = document.getElementById('revenueChartPeriod');
    if (revenueChartPeriod) {
        revenueChartPeriod.addEventListener('change', function() {
            const period = this.value;
            console.log('Revenue chart period changed to:', period);
        });
    }
});