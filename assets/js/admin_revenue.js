// Revenue Module JavaScript

document.addEventListener('DOMContentLoaded', function () {
    // Initialize revenue module
    initRevenueModule();
    initRevenueCharts();
});

function initRevenueModule() {
    // Initialize date filters
    initDateFilters();

    // Initialize export functions
    initExportFunctions();

    // Initialize chart interactions
    initChartInteractions();

    // Initialize responsive tables
    initResponsiveTables();
}

// =============================================
// Revenue Charts (data from JSON script tag)
// =============================================
var revenueChart = null;
var statusChart = null;

var STATUS_COLORS = {
    completed: '#10B981',
    processing: '#3B82F6',
    pending: '#F59E0B',
    cancelled: '#EF4444'
};

function initRevenueCharts() {
    var dataEl = document.getElementById('revenue-chart-data');
    if (!dataEl) return;

    var chartData;
    try {
        chartData = JSON.parse(dataEl.textContent);
    } catch (e) {
        console.warn('[Revenue] Không thể parse chart data:', e.message);
        return;
    }

    // --- Revenue Trend Chart ---
    var revenueCanvas = document.getElementById('revenue-chart');
    if (revenueCanvas) {
        revenueChart = new Chart(revenueCanvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: chartData.labels || [],
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: chartData.revenues || [],
                    borderColor: '#356DF1',
                    backgroundColor: 'rgba(53, 109, 241, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (value) {
                                return new Intl.NumberFormat('vi-VN').format(value) + ' VNĐ';
                            }
                        }
                    }
                },
                elements: { point: { radius: 4, hoverRadius: 6 } }
            }
        });
    }

    // --- Revenue by Status Doughnut Chart ---
    var statusCanvas = document.getElementById('status-chart');
    if (statusCanvas && chartData.status) {
        var s = chartData.status;
        statusChart = new Chart(statusCanvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Hoàn thành', 'Đang xử lý', 'Chờ xử lý', 'Đã hủy'],
                datasets: [{
                    data: [s.completed || 0, s.processing || 0, s.pending || 0, s.cancelled || 0],
                    backgroundColor: [
                        STATUS_COLORS.completed,
                        STATUS_COLORS.processing,
                        STATUS_COLORS.pending,
                        STATUS_COLORS.cancelled
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 20, usePointStyle: true } },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                var value = context.parsed;
                                var total = context.dataset.data.reduce(function (a, b) { return a + b; }, 0);
                                var pct = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return context.label + ': ' + new Intl.NumberFormat('vi-VN').format(value) + ' VNĐ (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });
    }
}

// Toggle chart type (gọi từ button onclick trong view)
window.toggleChartType = function (chartId) {
    if (chartId === 'revenue-chart' && revenueChart) {
        if (revenueChart.config.type === 'line') {
            revenueChart.config.type = 'bar';
            revenueChart.data.datasets[0].backgroundColor = '#356DF1';
            revenueChart.data.datasets[0].borderColor = '#356DF1';
            revenueChart.data.datasets[0].fill = false;
        } else {
            revenueChart.config.type = 'line';
            revenueChart.data.datasets[0].backgroundColor = 'rgba(53, 109, 241, 0.1)';
            revenueChart.data.datasets[0].borderColor = '#356DF1';
            revenueChart.data.datasets[0].fill = true;
        }
        revenueChart.update();
    }
};

// Export report (placeholder)
window.exportReport = function () {
    handleExport('excel');
};

window.exportDetailReport = function () {
    handleExport('excel');
};


// Date Filters
function initDateFilters() {
    const dateFromInput = document.getElementById('date_from');
    const dateToInput = document.getElementById('date_to');
    const periodSelect = document.getElementById('period');

    if (dateFromInput && dateToInput) {
        // Validate date range
        dateFromInput.addEventListener('change', function () {
            if (dateToInput.value && this.value > dateToInput.value) {
                alert('Ngày bắt đầu không thể lớn hơn ngày kết thúc');
                this.value = dateToInput.value;
            }
        });

        dateToInput.addEventListener('change', function () {
            if (dateFromInput.value && this.value < dateFromInput.value) {
                alert('Ngày kết thúc không thể nhỏ hơn ngày bắt đầu');
                this.value = dateFromInput.value;
            }
        });
    }

    // Period quick select
    if (periodSelect) {
        periodSelect.addEventListener('change', function () {
            const period = this.value;
            const today = new Date();
            let fromDate, toDate;

            switch (period) {
                case 'day':
                    fromDate = toDate = formatDate(today);
                    break;
                case 'month':
                    fromDate = formatDate(new Date(today.getFullYear(), today.getMonth(), 1));
                    toDate = formatDate(today);
                    break;
                case 'quarter':
                    const quarter = Math.floor(today.getMonth() / 3);
                    fromDate = formatDate(new Date(today.getFullYear(), quarter * 3, 1));
                    toDate = formatDate(today);
                    break;
                case 'year':
                    fromDate = formatDate(new Date(today.getFullYear(), 0, 1));
                    toDate = formatDate(today);
                    break;
            }

            if (dateFromInput) dateFromInput.value = fromDate;
            if (dateToInput) dateToInput.value = toDate;
        });
    }
}

// Export Functions
function initExportFunctions() {
    // Export buttons
    const exportButtons = document.querySelectorAll('[onclick*="export"]');
    exportButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const exportType = this.textContent.includes('Excel') ? 'excel' : 'pdf';
            handleExport(exportType);
        });
    });
}

function handleExport(type) {
    const dateFrom = document.getElementById('date_from')?.value || '';
    const dateTo = document.getElementById('date_to')?.value || '';
    const period = document.getElementById('period')?.value || '';

    let url = `?page=admin&module=revenue&action=export&format=${type}`;
    if (dateFrom) url += `&date_from=${dateFrom}`;
    if (dateTo) url += `&date_to=${dateTo}`;
    if (period) url += `&period=${period}`;

    showNotification(`Đang chuẩn bị báo cáo ${type.toUpperCase()}...`, 'info');

    // In real app, we might use window.location.href or a hidden iframe/form
    window.location.href = url;
}

function generateExcelReport() {
    // This would typically call a server endpoint to generate Excel
    const data = collectReportData();

    // For demo purposes, show success message
    showNotification('Báo cáo Excel đã được tạo thành công!', 'success');

    // In real implementation, this would trigger download
    console.log('Excel report data:', data);
}

function generatePDFReport() {
    // This would typically call a server endpoint to generate PDF
    const data = collectReportData();

    // For demo purposes, show success message
    showNotification('Báo cáo PDF đã được tạo thành công!', 'success');

    // In real implementation, this would trigger download
    console.log('PDF report data:', data);
}

function collectReportData() {
    // Collect current page data for export
    const data = {
        dateRange: {
            from: document.getElementById('date_from')?.value,
            to: document.getElementById('date_to')?.value
        },
        summary: collectSummaryData(),
        tables: collectTableData()
    };

    return data;
}

function collectSummaryData() {
    const summaryCards = document.querySelectorAll('.summary-card');
    const summary = {};

    summaryCards.forEach(card => {
        const title = card.querySelector('.summary-title')?.textContent;
        const value = card.querySelector('.summary-value')?.textContent;
        if (title && value) {
            summary[title] = value;
        }
    });

    return summary;
}

function collectTableData() {
    const tables = document.querySelectorAll('.admin-table');
    const tableData = [];

    tables.forEach(table => {
        const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
        const rows = Array.from(table.querySelectorAll('tbody tr')).map(tr => {
            return Array.from(tr.querySelectorAll('td')).map(td => td.textContent.trim());
        });

        tableData.push({
            headers,
            rows
        });
    });

    return tableData;
}

// Chart Interactions
function initChartInteractions() {
    // Chart type toggle functionality is handled in the PHP-generated script
    // This function can be extended for additional chart interactions

    // Add chart hover effects
    const chartContainers = document.querySelectorAll('.chart-container');
    chartContainers.forEach(container => {
        container.addEventListener('mouseenter', function () {
            this.style.transform = 'translateY(-2px)';
        });

        container.addEventListener('mouseleave', function () {
            this.style.transform = 'translateY(0)';
        });
    });
}

// Responsive Tables
function initResponsiveTables() {
    const tables = document.querySelectorAll('.admin-table');

    tables.forEach(table => {
        // Add responsive wrapper if not exists
        if (!table.parentElement.classList.contains('table-container')) {
            const wrapper = document.createElement('div');
            wrapper.className = 'table-container';
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
        }

        // Add mobile-friendly features
        addMobileTableFeatures(table);
    });
}

function addMobileTableFeatures(table) {
    // Add data labels for mobile view
    const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
    const rows = table.querySelectorAll('tbody tr');

    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        cells.forEach((cell, index) => {
            if (headers[index]) {
                cell.setAttribute('data-label', headers[index]);
            }
        });
    });
}

// Utility Functions
function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function showLoadingState() {
    // Create loading overlay
    const overlay = document.createElement('div');
    overlay.id = 'loading-overlay';
    overlay.innerHTML = `
        <div class="loading-content">
            <div class="loading-spinner"></div>
            <p>Đang tạo báo cáo...</p>
        </div>
    `;
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    `;

    const loadingContent = overlay.querySelector('.loading-content');
    loadingContent.style.cssText = `
        background: white;
        padding: 40px;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    `;

    const spinner = overlay.querySelector('.loading-spinner');
    spinner.style.cssText = `
        width: 40px;
        height: 40px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #356DF1;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 16px;
    `;

    // Add spinner animation
    const adminRevenueStyles = document.createElement('style');
    adminRevenueStyles.textContent = `
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(adminRevenueStyles);

    document.body.appendChild(overlay);
}

function hideLoadingState() {
    const overlay = document.getElementById('loading-overlay');
    if (overlay) {
        overlay.remove();
    }
}

function showNotification(message, type = 'info') {
    // Create notification
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 16px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        z-index: 10000;
        transform: translateX(100%);
        transition: transform 0.3s ease;
    `;

    // Set background color based on type
    const colors = {
        success: '#10B981',
        error: '#EF4444',
        warning: '#F59E0B',
        info: '#3B82F6'
    };
    notification.style.background = colors[type] || colors.info;

    document.body.appendChild(notification);

    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);

    // Auto remove after 3 seconds
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

// Tab switching functionality
function switchTab(tabName) {
    // Update URL without page reload
    const url = new URL(window.location);
    url.searchParams.set('tab', tabName);
    window.history.pushState({}, '', url);

    // Reload page to show new tab content
    window.location.reload();
}

// Print functionality
function printReport() {
    window.print();
}

// (window.exportReport, window.exportDetailReport, window.toggleChartType được định nghĩa ở trên)
