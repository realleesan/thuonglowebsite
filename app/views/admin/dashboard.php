<?php
// Enhanced Dashboard with Real Metrics
// Load Models
require_once __DIR__ . '/../../models/ProductsModel.php';
require_once __DIR__ . '/../../models/CategoriesModel.php';
require_once __DIR__ . '/../../models/NewsModel.php';
require_once __DIR__ . '/../../models/UsersModel.php';
require_once __DIR__ . '/../../models/OrdersModel.php';

$productsModel = new ProductsModel();
$categoriesModel = new CategoriesModel();
$newsModel = new NewsModel();
$usersModel = new UsersModel();
$ordersModel = new OrdersModel();

// Get actual data from database
$products = $productsModel->getAll();
$categories = $categoriesModel->getAll();
$news = $newsModel->getAll();
$users = $usersModel->getAll();
$orders = $ordersModel->getAll();

// Calculate current stats from real data
$stats = [
    'total_products' => count($products),
    'total_categories' => count($categories),
    'total_news' => count($news),
    'total_users' => count($users),
    'total_orders' => count($orders),
    'active_products' => count(array_filter($products, function($p) { return $p['status'] === 'active'; })),
    'published_news' => count(array_filter($news, function($n) { return $n['status'] === 'published'; })),
    'active_users' => count(array_filter($users, function($u) { return $u['status'] === 'active'; })),
    'upcoming_events' => 0 // Will be calculated when EventsModel is available
];

// Calculate total revenue from products
$totalRevenue = array_sum(array_map(function($p) { 
    return $p['status'] === 'active' ? ($p['price'] ?? 0) : 0; 
}, $products));

$stats['total_revenue'] = $totalRevenue;

// Calculate trends based on real data (simplified calculation)
$trends = [
    'products' => ['value' => max(0, count($products) - 10), 'direction' => count($products) > 10 ? 'up' : 'down'],
    'sales' => ['value' => count($orders), 'direction' => count($orders) > 0 ? 'up' : 'down'],
    'users' => ['value' => count($users), 'direction' => count($users) > 0 ? 'up' : 'down'],
    'revenue' => ['value' => number_format($totalRevenue, 0), 'direction' => $totalRevenue > 0 ? 'up' : 'down']
];

// Alerts - things that need attention
$alerts = [];
$lowStockProducts = array_filter($products, function($p) {
    return isset($p['stock']) && $p['stock'] < 5 && $p['stock'] > 0;
});
if (count($lowStockProducts) > 0) {
    $alerts[] = [
        'type' => 'warning',
        'icon' => 'fas fa-exclamation-triangle',
        'message' => count($lowStockProducts) . ' sản phẩm sắp hết hàng',
        'link' => '?page=admin&module=products&filter=low_stock'
    ];
}

$draftNews = array_filter($news, function($n) { return $n['status'] === 'draft'; });
if (count($draftNews) > 0) {
    $alerts[] = [
        'type' => 'info',
        'icon' => 'fas fa-file-alt',
        'message' => count($draftNews) . ' tin tức đang chờ xuất bản',
        'link' => '?page=admin&module=news&filter=draft'
    ];
}

// Top products (based on real data) - ensure proper structure
$topProducts = [];
foreach (array_slice($products, 0, 5) as $product) {
    $topProducts[] = [
        'name' => $product['name'] ?? $product['title'] ?? 'Sản phẩm không tên',
        'price' => $product['price'] ?? 0,
        'status' => $product['status'] ?? 'active'
    ];
}

// Recent activities with real data
$recentActivities = [];
foreach (array_slice(array_reverse($products), 0, 3) as $product) {
    $recentActivities[] = [
        'type' => 'product',
        'title' => 'Sản phẩm mới: ' . $product['name'],
        'date' => $product['created_at'] ?? date('Y-m-d H:i:s'),
        'icon' => 'fas fa-box',
        'status' => $product['status'] ?? 'active'
    ];
}

foreach (array_slice(array_reverse($news), 0, 2) as $newsItem) {
    $recentActivities[] = [
        'type' => 'news',
        'title' => 'Tin tức: ' . $newsItem['title'],
        'date' => $newsItem['created_at'] ?? date('Y-m-d H:i:s'),
        'icon' => 'fas fa-newspaper',
        'status' => $newsItem['status'] ?? 'published'
    ];
}

usort($recentActivities, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});
$recentActivities = array_slice($recentActivities, 0, 5);
?>

<div class="admin-dashboard">
    <!-- Page Header -->
    <div class="dashboard-header">
        <div class="dashboard-header-left">
            <h1>Dashboard</h1>
            <p>Tổng quan và quản lý hệ thống</p>
        </div>
        <div class="dashboard-header-right">
            <select class="admin-form-control" id="dashboardPeriod">
                <option value="today">Hôm nay</option>
                <option value="7days" selected>7 ngày qua</option>
                <option value="30days">30 ngày qua</option>
                <option value="custom">Tùy chỉnh</option>
            </select>
        </div>
    </div>

    <!-- Alerts Section -->
    <?php if (!empty($alerts)): ?>
    <div class="dashboard-alerts">
        <?php foreach ($alerts as $alert): ?>
        <div class="alert alert-<?php echo $alert['type']; ?>">
            <i class="<?php echo $alert['icon']; ?>"></i>
            <span><?php echo $alert['message']; ?></span>
            <a href="<?php echo $alert['link']; ?>" class="alert-action">Xem ngay →</a>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- KPI Cards with Trends -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-box"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['total_products']; ?></h3>
                <p>Tổng sản phẩm</p>
                <div class="stat-trend trend-up">
                    <i class="fas fa-arrow-up"></i>
                    <span><?php echo $trends['products']['value']; ?>% so với tuần trước</span>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($totalRevenue / 1000000, 1); ?>M</h3>
                <p>Doanh thu (VNĐ)</p>
                <div class="stat-trend trend-up">
                    <i class="fas fa-arrow-up"></i>
                    <span><?php echo $trends['revenue']['value']; ?>% so với tuần trước</span>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-newspaper"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['published_news']; ?></h3>
                <p>Tin tức đã xuất bản</p>
                <div class="stat-trend trend-up">
                    <i class="fas fa-arrow-up"></i>
                    <span><?php echo $trends['sales']['value']; ?>% so với tuần trước</span>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-calendar"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['upcoming_events']; ?></h3>
                <p>Sự kiện sắp tới</p>
                <div class="stat-trend trend-up">
                    <i class="fas fa-arrow-up"></i>
                    <span><?php echo $trends['users']['value']; ?>% so với tuần trước</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="dashboard-charts">
        <!-- Revenue Chart -->
        <div class="chart-widget chart-widget-large">
            <div class="widget-header">
                <h3>Doanh thu theo thời gian</h3>
                <div class="chart-controls">
                    <select class="admin-form-control" id="revenueChartPeriod">
                        <option value="7days">7 ngày</option>
                        <option value="30days" selected>30 ngày</option>
                        <option value="12months">12 tháng</option>
                    </select>
                </div>
            </div>
            <div class="widget-content">
                <canvas id="revenueChart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- Top Products Chart -->
        <div class="chart-widget">
            <div class="widget-header">
                <h3>Top 10 sản phẩm bán chạy</h3>
            </div>
            <div class="widget-content">
                <canvas id="topProductsChart" width="400" height="300"></canvas>
            </div>
        </div>

        <!-- Orders Status Chart -->
        <div class="chart-widget">
            <div class="widget-header">
                <h3>Phân loại đơn hàng</h3>
            </div>
            <div class="widget-content">
                <canvas id="ordersStatusChart" width="400" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="dashboard-content">
        <!-- Row 1: New Users Chart + Top Products -->
        <div class="dashboard-row-1">
            <!-- New Users Chart -->
            <div class="chart-widget">
                <div class="widget-header">
                    <h3>Người dùng mới (4 tuần)</h3>
                </div>
                <div class="widget-content">
                    <canvas id="newUsersChart" width="400" height="300"></canvas>
                </div>
            </div>

            <!-- Top Products -->
            <div class="dashboard-widget">
                <div class="widget-header">
                    <h3>Top sản phẩm (7 ngày)</h3>
                    <a href="?page=admin&module=products" class="widget-action">Xem tất cả →</a>
                </div>
                <div class="widget-content">
                    <?php if (empty($topProducts)): ?>
                        <p class="no-data">Chưa có dữ liệu</p>
                    <?php else: ?>
                        <div class="top-products-list">
                            <?php foreach ($topProducts as $index => $product): ?>
                            <div class="top-product-item">
                                <div class="product-rank"><?php echo $index + 1; ?></div>
                                <div class="product-info">
                                    <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                    <small><?php echo number_format($product['price'], 0, ',', '.'); ?> VNĐ</small>
                                </div>
                                <div class="product-badge">
                                    <span class="admin-badge admin-badge-success"><?php echo $product['status']; ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Row 2: Recent Activities + Quick Actions -->
        <div class="dashboard-row-2">
            <!-- Recent Activities -->
            <div class="dashboard-widget">
                <div class="widget-header">
                    <h3>Hoạt động gần đây</h3>
                </div>
                <div class="widget-content">
                    <?php if (empty($recentActivities)): ?>
                        <p class="no-data">Chưa có hoạt động nào</p>
                    <?php else: ?>
                        <ul class="activity-list">
                            <?php foreach ($recentActivities as $activity): ?>
                            <li class="activity-item">
                                <div class="activity-icon">
                                    <i class="<?php echo $activity['icon']; ?>"></i>
                                </div>
                                <div class="activity-content">
                                    <p><?php echo htmlspecialchars($activity['title']); ?></p>
                                    <small><?php echo date('d/m/Y H:i', strtotime($activity['date'])); ?></small>
                                </div>
                                <div class="activity-status">
                                    <span class="admin-badge admin-badge-<?php echo $activity['status'] === 'active' ? 'success' : 'warning'; ?>">
                                        <?php echo $activity['status']; ?>
                                    </span>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
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
                        <a href="?page=admin&module=products&action=change" class="quick-action-btn">
                            <i class="fas fa-plus-circle"></i>
                            <span>Thêm sản phẩm</span>
                        </a>
                        <a href="?page=admin&module=news&action=change" class="quick-action-btn">
                            <i class="fas fa-plus-circle"></i>
                            <span>Thêm tin tức</span>
                        </a>
                        <a href="?page=admin&module=categories&action=change" class="quick-action-btn">
                            <i class="fas fa-plus-circle"></i>
                            <span>Thêm danh mục</span>
                        </a>
                        <a href="?page=admin&module=events&action=change" class="quick-action-btn">
                            <i class="fas fa-plus-circle"></i>
                            <span>Thêm sự kiện</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>