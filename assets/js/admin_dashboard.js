// Admin Dashboard Chart.js Configuration
// ThuongLo Admin Dashboard - Phase 2

// Chart.js Configuration and Data
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

    // 1. Revenue Chart (Line Chart)
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: ['Tuần 1', 'Tuần 2', 'Tuần 3', 'Tuần 4'],
            datasets: [{
                label: 'Doanh thu (triệu VNĐ)',
                data: [12.5, 19.2, 15.8, 25.3],
                borderColor: colors.primary,
                backgroundColor: colors.primary + '20',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: colors.primary,
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8
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

    // 2. Top Products Chart (Bar Chart)
    const topProductsCtx = document.getElementById('topProductsChart').getContext('2d');
    const topProductsChart = new Chart(topProductsCtx, {
        type: 'bar',
        data: {
            labels: ['Data Premium', 'Khóa học cơ bản', 'Tool Auto', 'Gói VIP', 'Khóa nâng cao'],
            datasets: [{
                label: 'Số lượng bán',
                data: [45, 32, 28, 22, 18],
                backgroundColor: [
                    colors.primary,
                    colors.success,
                    colors.info,
                    colors.warning,
                    colors.danger
                ],
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
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y + ' sản phẩm';
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
                        maxRotation: 45,
                        color: '#6B7280'
                    }
                }
            }
        }
    });

    // 3. Orders Status Chart (Doughnut Chart)
    const ordersStatusCtx = document.getElementById('ordersStatusChart').getContext('2d');
    const ordersStatusChart = new Chart(ordersStatusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Hoàn thành', 'Đang xử lý', 'Chờ xử lý', 'Đã hủy'],
            datasets: [{
                data: [65, 20, 10, 5],
                backgroundColor: [
                    colors.success,
                    colors.info,
                    colors.warning,
                    colors.danger
                ],
                borderWidth: 0,
                cutout: '70%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        pointStyle: 'circle'
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

    // 4. New Users Chart (Line Chart)
    const newUsersCtx = document.getElementById('newUsersChart').getContext('2d');
    const newUsersChart = new Chart(newUsersCtx, {
        type: 'line',
        data: {
            labels: ['Tuần 1', 'Tuần 2', 'Tuần 3', 'Tuần 4'],
            datasets: [{
                label: 'Người dùng mới',
                data: [8, 12, 15, 22],
                borderColor: colors.success,
                backgroundColor: colors.success + '20',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: colors.success,
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8
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
                            return context.parsed.y + ' người dùng';
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

    // Chart period change handlers
    document.getElementById('revenueChartPeriod').addEventListener('change', function() {
        const period = this.value;
        let newLabels, newData;
        
        switch(period) {
            case '7days':
                newLabels = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];
                newData = [2.1, 3.5, 2.8, 4.2, 3.9, 5.1, 4.7];
                break;
            case '30days':
                newLabels = ['Tuần 1', 'Tuần 2', 'Tuần 3', 'Tuần 4'];
                newData = [12.5, 19.2, 15.8, 25.3];
                break;
            case '12months':
                newLabels = ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'];
                newData = [45, 52, 48, 61, 55, 67, 73, 69, 78, 82, 85, 91];
                break;
        }
        
        revenueChart.data.labels = newLabels;
        revenueChart.data.datasets[0].data = newData;
        revenueChart.update();
    });

    // Dashboard period change handler
    document.getElementById('dashboardPeriod').addEventListener('change', function() {
        // In a real application, this would reload data from server
        console.log('Dashboard period changed to:', this.value);
    });
});