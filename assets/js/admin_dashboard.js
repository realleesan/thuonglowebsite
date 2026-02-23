/**
 * admin_dashboard.js
 *
 * Quản lý biểu đồ và dữ liệu cho trang Admin Dashboard.
 * Dữ liệu được tải động qua API thay vì hardcode.
 *
 * API base: api.php?path=admin/dashboard/<endpoint>
 */

(function () {
    'use strict';

    // ========================= CONFIG =========================

    const API_BASE = 'api.php?path=admin/dashboard/';
    const CHART_COLORS = {
        primary: 'rgba(99, 102, 241, 1)',
        primaryBg: 'rgba(99, 102, 241, 0.15)',
        success: '#10B981',
        info: '#3B82F6',
        warning: '#F59E0B',
        danger: '#EF4444',
        gray: '#E5E7EB',
    };

    // ========================= CHART INSTANCES =========================

    let revenueChart = null;
    let topProductsChart = null;
    let ordersStatusChart = null;
    let newUsersChart = null;

    // ========================= HELPERS =========================

    /**
     * Thực hiện fetch đến API và trả về dữ liệu.
     * @param {string} path - Đường dẫn sau API_BASE
     * @param {Object} params - Query params
     * @returns {Promise<Object>}
     */
    async function fetchApi(path, params = {}) {
        const url = new URL(API_BASE + path, window.location.origin + window.location.pathname.replace(/\/[^/]*$/, '/'));
        Object.entries(params).forEach(([k, v]) => v && url.searchParams.set(k, v));

        const response = await fetch(url.toString(), {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        return response.json();
    }

    /**
     * Hiển thị trạng thái loading cho một widget.
     * @param {string} canvasId - ID của canvas element
     */
    function showLoading(canvasId) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;
        const parent = canvas.closest('.widget-content, .chart-body, .card-body') || canvas.parentElement;
        if (parent && !parent.querySelector('.chart-loading')) {
            const loader = document.createElement('div');
            loader.className = 'chart-loading';
            loader.innerHTML = '<div class="chart-spinner"></div><p>Đang tải dữ liệu...</p>';
            loader.style.cssText = 'position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;background:rgba(255,255,255,.8);border-radius:8px;z-index:10;';
            parent.style.position = 'relative';
            parent.appendChild(loader);
        }
    }

    /**
     * Ẩn trạng thái loading.
     * @param {string} canvasId
     */
    function hideLoading(canvasId) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;
        const parent = canvas.closest('.widget-content, .chart-body, .card-body') || canvas.parentElement;
        const loader = parent && parent.querySelector('.chart-loading');
        if (loader) loader.remove();
    }

    /**
     * Cập nhật text của một element theo ID.
     * @param {string} id
     * @param {string|number} value
     */
    function setText(id, value) {
        const el = document.getElementById(id);
        if (el) el.textContent = value;
    }

    /**
     * Format currency VND.
     * @param {number} value - Giá trị (đồng)
     * @returns {string}
     */
    function formatCurrency(value) {
        if (value >= 1e9) return (value / 1e9).toFixed(1) + ' tỷ';
        if (value >= 1e6) return (value / 1e6).toFixed(1) + ' triệu';
        if (value >= 1e3) return (value / 1e3).toFixed(0) + 'K';
        return value.toLocaleString('vi-VN');
    }

    // ========================= STATISTICS CARDS =========================

    async function loadStatistics() {
        try {
            const json = await fetchApi('statistics');
            const data = json?.data?.data ?? json?.data ?? {};

            const stats = data;
            const trends = stats.trends ?? {};

            // Cập nhật các KPI cards
            setText('stat-total-products', stats.total_products ?? 0);
            setText('stat-total-revenue', formatCurrency(stats.total_revenue ?? 0));
            setText('stat-published-news', stats.published_news ?? 0);
            setText('stat-upcoming-events', stats.upcoming_events ?? 0);

            // Cập nhật trends
            updateTrend('trend-products', trends.products ?? { direction: 'up', value: 0 });
            updateTrend('trend-revenue', trends.revenue ?? { direction: 'up', value: 0 });
            updateTrend('trend-news', trends.news ?? { direction: 'up', value: 0 });
            updateTrend('trend-events', trends.events ?? { direction: 'up', value: 0 });
        } catch (err) {
            console.warn('[Dashboard] Không thể tải statistics:', err.message);
        }
    }

    function updateTrend(id, trend) {
        const el = document.getElementById(id);
        if (!el) return;
        const dir = trend.direction ?? 'up';
        const value = trend.value ?? 0;
        el.className = `stat-trend trend-${dir}`;
        el.innerHTML = `<i class="fas fa-arrow-${dir}"></i><span>${value}% so với tháng trước</span>`;
    }

    // ========================= REVENUE CHART =========================

    async function loadRevenueChart(period = '30days') {
        showLoading('revenueChart');
        try {
            const json = await fetchApi('revenue', { period });
            const chartData = json?.data?.data ?? {};
            const labels = chartData.labels ?? ['Không có dữ liệu'];
            const dataset = chartData.datasets?.[0] ?? {};
            const values = dataset.data ?? [0];

            if (revenueChart) {
                revenueChart.data.labels = labels;
                revenueChart.data.datasets[0].data = values;
                revenueChart.update('active');
            } else {
                const ctx = document.getElementById('revenueChart');
                if (!ctx) return;

                revenueChart = new Chart(ctx.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [{
                            label: 'Doanh thu (triệu VNĐ)',
                            data: values,
                            borderColor: CHART_COLORS.primary,
                            backgroundColor: CHART_COLORS.primaryBg,
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: CHART_COLORS.primary,
                            pointRadius: 5,
                            pointHoverRadius: 8,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: ctx => ctx.parsed.y.toFixed(2) + ' triệu VNĐ',
                                },
                            },
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: v => v + 'M',
                                },
                            },
                        },
                    },
                });
            }

            // Cập nhật tổng doanh thu badge nếu có
            const total = dataset.total ?? 0;
            setText('revenue-total-badge', total > 0 ? total.toFixed(2) + 'M VNĐ' : '');
        } catch (err) {
            console.warn('[Dashboard] Không thể tải revenue chart:', err.message);
        } finally {
            hideLoading('revenueChart');
        }
    }

    // ========================= TOP PRODUCTS CHART =========================

    async function loadTopProductsChart(period = '30days') {
        showLoading('topProductsChart');
        try {
            const json = await fetchApi('top-products', { period, limit: 5 });
            const data = json?.data?.data ?? {};
            const products = data.products ?? [];

            const labels = products.map(p => p.name ?? 'N/A');
            const values = products.map(p => p.sales_count ?? 0);

            if (topProductsChart) {
                topProductsChart.data.labels = labels;
                topProductsChart.data.datasets[0].data = values;
                topProductsChart.update('active');
            } else {
                const ctx = document.getElementById('topProductsChart');
                if (!ctx) return;

                topProductsChart = new Chart(ctx.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [{
                            label: 'Số lượng bán',
                            data: values,
                            backgroundColor: [
                                'rgba(99,102,241,.8)',
                                'rgba(16,185,129,.8)',
                                'rgba(59,130,246,.8)',
                                'rgba(245,158,11,.8)',
                                'rgba(239,68,68,.8)',
                            ],
                            borderRadius: 6,
                            borderWidth: 0,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: 'y',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: ctx => 'Đã bán: ' + ctx.parsed.x + ' SP',
                                },
                            },
                        },
                        scales: {
                            x: { beginAtZero: true, ticks: { precision: 0 } },
                        },
                    },
                });
            }
        } catch (err) {
            console.warn('[Dashboard] Không thể tải top products chart:', err.message);
        } finally {
            hideLoading('topProductsChart');
        }
    }

    // ========================= ORDERS STATUS CHART =========================

    async function loadOrdersStatusChart() {
        showLoading('ordersStatusChart');
        try {
            const json = await fetchApi('orders-status');
            const chartData = json?.data?.data ?? {};

            const labels = chartData.labels ?? ['Chưa có đơn hàng'];
            const dataset = chartData.datasets?.[0] ?? {};
            const values = dataset.data ?? [0];
            const colors = dataset.backgroundColor ?? [CHART_COLORS.gray];

            if (ordersStatusChart) {
                ordersStatusChart.data.labels = labels;
                ordersStatusChart.data.datasets[0].data = values;
                ordersStatusChart.data.datasets[0].backgroundColor = colors;
                ordersStatusChart.update('active');
            } else {
                const ctx = document.getElementById('ordersStatusChart');
                if (!ctx) return;

                ordersStatusChart = new Chart(ctx.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels,
                        datasets: [{
                            data: values,
                            backgroundColor: colors,
                            borderWidth: 3,
                            borderColor: '#fff',
                            hoverOffset: 8,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '65%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { boxWidth: 12, padding: 16 },
                            },
                            tooltip: {
                                callbacks: {
                                    label: ctx => ctx.label + ': ' + ctx.parsed.toFixed(1) + '%',
                                },
                            },
                        },
                    },
                });
            }

            // Cập nhật số liệu tổng nếu có
            const totals = chartData.totals ?? {};
            ['completed', 'processing', 'pending', 'cancelled'].forEach(status => {
                setText('orders-' + status + '-count', totals[status] ?? 0);
            });
        } catch (err) {
            console.warn('[Dashboard] Không thể tải orders status chart:', err.message);
        } finally {
            hideLoading('ordersStatusChart');
        }
    }

    // ========================= NEW USERS CHART =========================

    async function loadNewUsersChart(period = '4weeks') {
        showLoading('newUsersChart');
        try {
            const json = await fetchApi('new-users', { period });
            const chartData = json?.data?.data ?? {};

            const labels = chartData.labels ?? ['Tuần 1', 'Tuần 2', 'Tuần 3', 'Tuần 4'];
            const values = chartData.datasets?.[0]?.data ?? [0, 0, 0, 0];

            if (newUsersChart) {
                newUsersChart.data.labels = labels;
                newUsersChart.data.datasets[0].data = values;
                newUsersChart.update('active');
            } else {
                const ctx = document.getElementById('newUsersChart');
                if (!ctx) return;

                newUsersChart = new Chart(ctx.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [{
                            label: 'Người dùng mới',
                            data: values,
                            borderColor: CHART_COLORS.success,
                            backgroundColor: 'rgba(16,185,129,.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: CHART_COLORS.success,
                            pointRadius: 5,
                            pointHoverRadius: 8,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: ctx => ctx.parsed.y + ' người dùng mới',
                                },
                            },
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { precision: 0 },
                            },
                        },
                    },
                });
            }
        } catch (err) {
            console.warn('[Dashboard] Không thể tải new users chart:', err.message);
        } finally {
            hideLoading('newUsersChart');
        }
    }

    // ========================= DASHBOARD PERIOD CHANGE =========================

    /**
     * Reload tất cả charts khi period thay đổi.
     * @param {string} period
     */
    async function reloadAllCharts(period) {
        await Promise.all([
            loadRevenueChart(period),
            loadTopProductsChart(period),
        ]);
    }

    // ========================= NOTIFICATIONS =========================

    async function loadNotifications() {
        try {
            const json = await fetchApi('notifications', { limit: 3 });
            const data = json?.data?.data ?? {};

            const notifications = data.notifications ?? [];
            const unreadCount = data.unread_count ?? 0;

            // Cập nhật badge count
            const badge = document.getElementById('notifBadge') ?? document.querySelector('#notificationsBtn .badge');
            if (badge) {
                badge.textContent = unreadCount > 0 ? unreadCount : '';
                badge.style.display = unreadCount > 0 ? '' : 'none';
            }

            // Cập nhật dropdown body
            const body = document.getElementById('notificationsBody') ?? document.querySelector('#notificationsMenu .dropdown-body');
            if (body && notifications.length > 0) {
                body.innerHTML = notifications.map(n => `
                    <div class="notification-item">
                        <div class="notification-icon"><i class="${n.icon}"></i></div>
                        <div class="notification-content">
                            <p class="notification-text">${escapeHtml(n.message)}</p>
                            <span class="notification-time">${escapeHtml(n.time_ago)}</span>
                        </div>
                    </div>
                `).join('');
            }
        } catch (err) {
            console.warn('[Dashboard] Không thể tải notifications:', err.message);
        }
    }

    function escapeHtml(str) {
        const d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    // ========================= EVENT LISTENERS =========================

    function bindEvents() {
        // Revenue chart period selector
        const revenueSelect = document.getElementById('revenueChartPeriod');
        if (revenueSelect) {
            revenueSelect.addEventListener('change', function () {
                loadRevenueChart(this.value);
            });
        }

        // Dashboard-wide period selector
        const dashboardPeriod = document.getElementById('dashboardPeriod');
        if (dashboardPeriod) {
            dashboardPeriod.addEventListener('change', function () {
                reloadAllCharts(this.value);
            });
        }

        // Manual refresh button
        const refreshBtn = document.getElementById('dashboardRefreshBtn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', function () {
                const period = document.getElementById('dashboardPeriod')?.value ?? '30days';
                initDashboard(period);
            });
        }
    }

    // ========================= INIT =========================

    /**
     * Khởi tạo dashboard: tải tất cả data đồng thời.
     * @param {string} period
     */
    async function initDashboard(period = '30days') {
        await Promise.all([
            loadStatistics(),
            loadRevenueChart(period),
            loadTopProductsChart(period),
            loadOrdersStatusChart(),
            loadNewUsersChart(),
            loadNotifications(),
        ]);
    }

    // Chạy khi DOM sẵn sàng
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            bindEvents();
            initDashboard();
        });
    } else {
        bindEvents();
        initDashboard();
    }

    // Export ra window để dùng từ ngoài nếu cần
    window.AdminDashboard = { reload: initDashboard, loadNotifications };

})();