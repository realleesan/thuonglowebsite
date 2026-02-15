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

    // Initialize charts with data from PHP (passed via data attributes or global variables)
    initializeCharts();

    function initializeCharts() {
        // Get chart data from PHP (should be set in the PHP view)
        const chartData = window.dashboardChartData || null;
        
        if (chartData) {
            // Initialize all charts with data from PHP
            if (chartData.revenue) initRevenueChart(chartData.revenue);
            if (chartData.orderDistribution) initOrderDistributionChart(chartData.orderDistribution);
            if (chartData.orderStatus) initOrderStatusChart(chartData.orderStatus);
            if (chartData.purchaseTrend) initPurchaseTrendChart(chartData.purchaseTrend);
        } else {
            console.warn('No chart data available from server');
        }
    }

    function initRevenueChart(data) {
        const revenueCtx = document.getElementById('revenueChart');
        if (!revenueCtx || !data.labels || !data.data) return;

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
        if (!orderDistributionCtx || !data.labels || !data.data) return;

        // Generate colors dynamically based on number of categories
        const dynamicColors = [];
        const baseColors = [colors.primary, colors.success, colors.info, colors.warning, colors.danger];
        
        for (let i = 0; i < data.labels.length; i++) {
            dynamicColors.push(baseColors[i % baseColors.length]);
        }

        new Chart(orderDistributionCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: data.labels,
                datasets: [{
                    data: data.data,
                    backgroundColor: dynamicColors,
                    borderWidth: 0
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
        if (!orderStatusCtx || !data.labels || !data.data) return;

        // Generate colors dynamically based on number of status types
        const dynamicColors = [];
        const statusColors = [colors.success, colors.info, colors.warning, colors.danger];
        
        for (let i = 0; i < data.labels.length; i++) {
            dynamicColors.push(statusColors[i % statusColors.length]);
        }

        new Chart(orderStatusCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Số đơn hàng',
                    data: data.data,
                    backgroundColor: dynamicColors,
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
        if (!purchaseTrendCtx || !data.labels || !data.data) return;

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