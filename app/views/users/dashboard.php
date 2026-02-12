<?php
// User Dashboard với UserService
require_once __DIR__ . '/../../../core/view_init.php';

// Chọn service user (ưu tiên inject từ routing)
$service = isset($currentService) ? $currentService : ($userService ?? null);

$user = ['name' => 'Người dùng'];
$stats = [
    'total_orders' => 0,
    'total_spent' => 0,
    'loyalty_points' => 0,
    'user_level' => 'Bronze',
    'data_purchased' => 0,
];
$recentOrders = [];
$trends = [
    'orders' => ['value' => 0, 'direction' => 'down'],
    'spending' => ['value' => 0, 'direction' => 'down'],
    'data' => ['value' => 0, 'direction' => 'down'],
    'points' => ['value' => 0, 'direction' => 'down'],
];

if ($service) {
    $userId = $_SESSION['user_id'] ?? 0;
    if ($userId) {
        $data = $service->getDashboardData((int) $userId);
        $user = $data['user'] ?? $user;
        $stats = $data['stats'] ?? $stats;
        $recentOrders = $data['recent_orders'] ?? $recentOrders;
        $trends = $data['trends'] ?? $trends;
    }
}

// Quick actions based on user activity
$quickActions = [
    [
        'title' => 'Mua Data Nguồn Hàng',
        'icon' => 'fas fa-database',
        'link' => '?page=products&category=data_nguon_hang',
        'color' => 'primary'
    ],
    [
        'title' => 'Xem Đơn Hàng',
        'icon' => 'fas fa-shopping-bag',
        'link' => '?page=users&module=orders',
        'color' => 'success'
    ],
    [
        'title' => 'Thanh Toán Giỏ Hàng',
        'icon' => 'fas fa-credit-card',
        'link' => '?page=users&module=cart',
        'color' => 'warning'
    ],
    [
        'title' => 'Khóa Học Mới',
        'icon' => 'fas fa-graduation-cap',
        'link' => '?page=products&category=khoa_hoc',
        'color' => 'info'
    ]
];
?>

<div class="user-content-with-sidebar">
    <!-- User Sidebar -->
    <?php include 'app/views/_layout/user_sidebar.php'; ?>
    
    <!-- Dashboard Content -->
    <div class="user-dashboard">
    <!-- Page Header -->
    <div class="dashboard-header">
        <div class="dashboard-header-left">
            <h1>Chào mừng trở lại, <?php echo htmlspecialchars($user['name'] ?? 'Người dùng'); ?>!</h1>
            <p>Tổng quan tài khoản và hoạt động của bạn</p>
        </div>
        <div class="dashboard-header-right">
            <div class="user-level-badge">
                <i class="fas fa-crown"></i>
                <span><?php echo $stats['user_level']; ?> Member</span>
            </div>
        </div>
    </div>

    <!-- KPI Cards with Trends -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-shopping-bag"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['total_orders']; ?></h3>
                <p>Tổng đơn hàng</p>
                <div class="stat-trend trend-up">
                    <i class="fas fa-arrow-up"></i>
                    <span><?php echo $trends['orders']['value']; ?>% so với tháng trước</span>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-wallet"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['total_spent'] / 1000000, 1); ?>M</h3>
                <p>Tổng chi tiêu (VNĐ)</p>
                <div class="stat-trend trend-up">
                    <i class="fas fa-arrow-up"></i>
                    <span><?php echo $trends['spending']['value']; ?>% so với tháng trước</span>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-database"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['data_purchased'] ?? 0; ?></h3>
                <p>Data đã mua</p>
                <div class="stat-trend trend-up">
                    <i class="fas fa-arrow-up"></i>
                    <span><?php echo $trends['data']['value']; ?>% so với tháng trước</span>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-star"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['loyalty_points']); ?></h3>
                <p>Điểm tích lũy</p>
                <div class="stat-trend trend-up">
                    <i class="fas fa-arrow-up"></i>
                    <span><?php echo $trends['points']['value']; ?>% so với tháng trước</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="dashboard-charts">
        <!-- Revenue Chart -->
        <div class="chart-widget chart-widget-large">
            <div class="widget-header">
                <h3>Chi tiêu theo thời gian</h3>
                <div class="chart-controls">
                    <select class="user-form-control" id="revenueChartPeriod">
                        <option value="5months" selected>5 tháng gần đây</option>
                        <option value="12months">12 tháng</option>
                        <option value="custom">Tùy chỉnh</option>
                    </select>
                </div>
            </div>
            <div class="widget-content">
                <canvas id="revenueChart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- Order Distribution Chart -->
        <div class="chart-widget">
            <div class="widget-header">
                <h3>Phân loại đơn hàng</h3>
            </div>
            <div class="widget-content">
                <canvas id="orderDistributionChart" width="400" height="300"></canvas>
            </div>
        </div>

        <!-- Order Status Chart -->
        <div class="chart-widget">
            <div class="widget-header">
                <h3>Trạng thái đơn hàng</h3>
            </div>
            <div class="widget-content">
                <canvas id="orderStatusChart" width="400" height="300"></canvas>
            </div>
        </div>

        <!-- Purchase Trend Chart -->
        <div class="chart-widget chart-widget-purchase-trend">
            <div class="widget-header">
                <h3>Xu hướng mua hàng (4 tuần)</h3>
            </div>
            <div class="widget-content">
                <canvas id="purchaseTrendChart" width="400" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="dashboard-content">
        <!-- Recent Orders -->
        <div class="dashboard-widget recent-orders-widget">
            <div class="widget-header">
                <h3>Đơn hàng gần đây</h3>
                <a href="?page=users&module=orders" class="widget-action">Xem tất cả →</a>
            </div>
            <div class="widget-content">
                <?php if (empty($recentOrders)): ?>
                    <p class="no-data">Chưa có đơn hàng nào</p>
                <?php else: ?>
                    <div class="recent-orders-list">
                        <?php foreach ($recentOrders as $order): ?>
                        <div class="order-item">
                            <div class="order-info">
                                <div class="order-id">#<?php echo $order['id']; ?></div>
                                <div class="order-product"><?php echo htmlspecialchars($order['product_name']); ?></div>
                                <div class="order-date"><?php echo date('d/m/Y', strtotime($order['date'])); ?></div>
                            </div>
                            <div class="order-amount">
                                <?php echo number_format($order['amount'], 0, ',', '.'); ?> VNĐ
                            </div>
                            <div class="order-status">
                                <span class="user-badge user-badge-<?php 
                                    echo $order['status'] === 'completed' ? 'success' : 
                                        ($order['status'] === 'processing' ? 'warning' : 
                                        ($order['status'] === 'cancelled' ? 'danger' : 'info')); 
                                ?>">
                                    <?php 
                                    // Status text mapping
                                    $statusLabels = [
                                        'completed' => 'Hoàn thành',
                                        'processing' => 'Đang xử lý',
                                        'pending' => 'Chờ xử lý',
                                        'cancelled' => 'Đã hủy'
                                    ];
                                    echo $statusLabels[$order['status']] ?? ucfirst($order['status']); 
                                    ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="dashboard-widget quick-actions-widget">
            <div class="widget-header">
                <h3>Thao tác nhanh</h3>
            </div>
            <div class="widget-content">
                <div class="quick-actions">
                    <?php foreach ($quickActions as $action): ?>
                    <a href="<?php echo $action['link']; ?>" class="quick-action-btn quick-action-<?php echo $action['color']; ?>">
                        <i class="<?php echo $action['icon']; ?>"></i>
                        <span><?php echo $action['title']; ?></span>
                    </a>
                    <?php endforeach; ?>
                    
                    <!-- Cart & Wishlist Actions -->
                    <a href="?page=users&module=cart" class="quick-action-btn quick-action-warning">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Giỏ hàng (0)</span>
                    </a>
                    
                    <a href="?page=users&module=wishlist" class="quick-action-btn quick-action-danger">
                        <i class="fas fa-heart"></i>
                        <span>Yêu thích (0)</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>