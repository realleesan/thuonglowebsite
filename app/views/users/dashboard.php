<?php
// User Dashboard - Using database data
require_once __DIR__ . '/../../services/UserService.php';

// Get current user from session
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    header('Location: ?page=login');
    exit;
}

// Get dashboard data from UserService
try {
    $userService = new UserService();
    $dashboardData = $userService->getDashboardData($userId);
    
    $user = $dashboardData['user'] ?? ['name' => 'Người dùng'];
    $stats = $dashboardData['stats'] ?? [
        'total_orders' => 0,
        'total_spent' => 0,
        'loyalty_points' => 0,
        'user_level' => 'Bronze',
        'data_purchased' => 0,
    ];
    $recentOrders = $dashboardData['recent_orders'] ?? [];
    $trends = $dashboardData['trends'] ?? [
        'orders' => ['value' => 0, 'direction' => 'down'],
        'spending' => ['value' => 0, 'direction' => 'down'],
        'data' => ['value' => 0, 'direction' => 'down'],
        'points' => ['value' => 0, 'direction' => 'down'],
    ];
} catch (Exception $e) {
    // Fallback to session data if service fails
    $user = [
        'name' => $_SESSION['user_name'] ?? 'Người dùng',
        'email' => $_SESSION['user_email'] ?? '',
        'role' => $_SESSION['user_role'] ?? 'user'
    ];
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
                    <?php
                    // Get cart and wishlist counts from database
                    $cartCount = 0;
                    $wishlistCount = 0;
                    
                    try {
                        $cartData = $userService->getCartData($userId);
                        $cartCount = $cartData['summary']['total_items'] ?? 0;
                        
                        $wishlistData = $userService->getWishlistData($userId);
                        $wishlistCount = $wishlistData['total_items'] ?? 0;
                    } catch (Exception $e) {
                        // Keep default values if service fails
                    }
                    ?>
                    <a href="?page=users&module=cart" class="quick-action-btn quick-action-warning">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Giỏ hàng (<?php echo $cartCount; ?>)</span>
                    </a>
                    
                    <a href="?page=users&module=wishlist" class="quick-action-btn quick-action-danger">
                        <i class="fas fa-heart"></i>
                        <span>Yêu thích (<?php echo $wishlistCount; ?>)</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>

<!-- Pass chart data to JavaScript -->
<script>
// Pass chart data from PHP to JavaScript
window.dashboardChartData = {
    revenue: {
        labels: <?php 
        // Generate dynamic month labels based on actual data
        $monthLabels = [];
        $currentMonth = date('n'); // Current month number
        for ($i = 4; $i >= 0; $i--) {
            $monthNum = $currentMonth - $i;
            if ($monthNum <= 0) $monthNum += 12;
            $monthLabels[] = 'Tháng ' . $monthNum;
        }
        echo json_encode($monthLabels);
        ?>,
        data: [
            <?php 
            // Calculate monthly revenue distribution based on total spent
            $totalSpent = $stats['total_spent'] ?? 0;
            if ($totalSpent > 0) {
                // Simulate monthly distribution (in millions VND)
                $month1 = round(($totalSpent * 0.15) / 1000000, 2); // 15% in month 1
                $month2 = round(($totalSpent * 0.20) / 1000000, 2); // 20% in month 2
                $month3 = round(($totalSpent * 0.25) / 1000000, 2); // 25% in month 3
                $month4 = round(($totalSpent * 0.20) / 1000000, 2); // 20% in month 4
                $month5 = round(($totalSpent * 0.20) / 1000000, 2); // 20% in month 5
                
                echo $month1 . ', ' . $month2 . ', ' . $month3 . ', ' . $month4 . ', ' . $month5;
            } else {
                // Default revenue when no spending
                echo '0, 0, 0, 0, 0';
            }
            ?>
        ]
    },
    orderDistribution: {
        labels: <?php 
        // Generate dynamic category labels based on actual order types
        $categoryLabels = [];
        $orderTypes = array_unique(array_column($recentOrders, 'type'));
        
        if (empty($orderTypes)) {
            // If no orders, don't show any labels
            $categoryLabels = [];
        } else {
            $typeMapping = [
                'data_nguon_hang' => 'Data Nguồn Hàng',
                'van_chuyen' => 'Vận Chuyển', 
                'dich_vu_tt' => 'Dịch Vụ TT',
                'danh_hang' => 'Đánh Hàng',
                'khoa_hoc' => 'Khóa Học'
            ];
            
            foreach ($orderTypes as $type) {
                $categoryLabels[] = $typeMapping[$type] ?? ucfirst(str_replace('_', ' ', $type));
            }
        }
        echo json_encode($categoryLabels);
        ?>,
        data: [
            <?php 
            // Calculate order distribution based on actual orders
            $totalOrders = count($recentOrders);
            if ($totalOrders > 0) {
                $orderTypes = array_unique(array_column($recentOrders, 'type'));
                $distributionData = [];
                
                foreach ($orderTypes as $type) {
                    $typeOrders = count(array_filter($recentOrders, function($o) use ($type) { 
                        return ($o['type'] ?? 'data_nguon_hang') === $type; 
                    }));
                    $distributionData[] = round(($typeOrders / $totalOrders) * 100, 1);
                }
                
                echo implode(', ', $distributionData);
            } else {
                // No orders - empty data
                echo '';
            }
            ?>
        ]
    },
    orderStatus: {
        labels: <?php 
        // Generate dynamic status labels based on actual order statuses
        $statusLabels = [];
        $orderStatuses = array_unique(array_column($recentOrders, 'status'));
        
        if (empty($orderStatuses)) {
            $statusLabels = [];
        } else {
            $statusMapping = [
                'completed' => 'Hoàn thành',
                'processing' => 'Đang xử lý', 
                'pending' => 'Chờ xử lý',
                'cancelled' => 'Đã hủy'
            ];
            
            foreach ($orderStatuses as $status) {
                $statusLabels[] = $statusMapping[$status] ?? ucfirst($status);
            }
        }
        echo json_encode($statusLabels);
        ?>,
        data: [
            <?php 
            if (!empty($recentOrders)) {
                $orderStatuses = array_unique(array_column($recentOrders, 'status'));
                $statusData = [];
                
                foreach ($orderStatuses as $status) {
                    $statusCount = count(array_filter($recentOrders, function($o) use ($status) { 
                        return $o['status'] === $status; 
                    }));
                    $statusData[] = $statusCount;
                }
                
                echo implode(', ', $statusData);
            } else {
                echo '';
            }
            ?>
        ]
    },
    purchaseTrend: {
        labels: <?php 
        // Generate dynamic week labels based on actual data period
        $weekLabels = [];
        $weeksBack = 4;
        for ($i = $weeksBack - 1; $i >= 0; $i--) {
            $weekLabels[] = 'Tuần ' . ($weeksBack - $i);
        }
        echo json_encode($weekLabels);
        ?>,
        data: [
            <?php 
            // Calculate purchase trend based on recent orders
            $totalOrders = $stats['total_orders'] ?? 0;
            if ($totalOrders > 0) {
                // Simulate weekly distribution based on total orders
                $week1 = max(0, intval($totalOrders * 0.15)); // 15% of orders in week 1
                $week2 = max(0, intval($totalOrders * 0.25)); // 25% of orders in week 2  
                $week3 = max(0, intval($totalOrders * 0.35)); // 35% of orders in week 3
                $week4 = max(0, $totalOrders - $week1 - $week2 - $week3); // Remaining orders in week 4
                
                echo $week1 . ', ' . $week2 . ', ' . $week3 . ', ' . $week4;
            } else {
                // Default trend when no orders
                echo '0, 0, 0, 0';
            }
            ?>
        ]
    }
};
</script>

<!-- Include Dashboard JavaScript -->
<script src="assets/js/user_dashboard.js"></script>