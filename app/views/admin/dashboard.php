<?php
/**
 * Admin Dashboard - Dynamic Version
 * Sử dụng AdminService thông qua ServiceManager
 */

// Make global services available in view scope
global $adminService, $currentService;

$service = isset($currentService) ? $currentService : ($adminService ?? null);

// Nếu vì lý do nào đó không có AdminService, dừng sớm để tránh lỗi khó đoán
if (!$service) {
    throw new Exception('AdminService is not available');
}

// Initialize data variables
$stats = [];
$trends = [];
$alerts = [];
$topProducts = [];
$recentActivities = [];
$chartsData = [];
$showErrorMessage = false;
$errorMessage = '';

try {
    // Get admin dashboard data từ AdminService
    $dashboardData = $service->getDashboardData();
    
    // Extract data
    $stats = $dashboardData['stats'] ?? [];
    $trends = $dashboardData['trends'] ?? [];
    $alerts = $dashboardData['alerts'] ?? [];
    $topProducts = $dashboardData['top_products'] ?? [];
    $recentActivities = $dashboardData['recent_activities'] ?? [];
    $chartsData = $dashboardData['charts_data'] ?? [];
    
} catch (Exception $e) {
    // Handle errors gracefully - display simple error
    $showErrorMessage = true;
    $errorMessage = 'Lỗi: ' . $e->getMessage();
    
    // Use empty state data
    $stats = [];
    $trends = [];
    $alerts = [];
    $topProducts = [];
    $recentActivities = [];
    $chartsData = [];
}

// Calculate derived values for display
$totalRevenue = $stats['total_revenue'] ?? 0;
?>

<div class="admin-dashboard">
    <!-- Page Header -->
    <div class="dashboard-header">
        <div class="dashboard-header-left">
            <h1>Dashboard</h1>
            <p>Tổng quan và quản lý hệ thống</p>
        </div>
        
    </div>

    <!-- Alerts Section -->
    <?php if ($showErrorMessage): ?>
    <div class="dashboard-alerts">
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo htmlspecialchars($errorMessage); ?></span>
        </div>
    </div>
    <?php endif; ?>
    
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

    <!-- KPI Cards with Trends - Data cập nhật qua AJAX -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #6366f1, #4f46e5);">
                <i class="fas fa-box"></i>
            </div>
            <div class="stat-content">
                <h3 id="stat-total-products"><?php echo $stats['total_products'] ?? 0; ?></h3>
                <p>Tổng sản phẩm</p>
                <div class="stat-trend trend-<?php echo ($trends['products']['direction'] ?? 'up'); ?>" id="trend-products">
                    <i class="fas fa-arrow-<?php echo ($trends['products']['direction'] ?? 'up'); ?>"></i>
                    <span><?php echo ($trends['products']['value'] ?? 0); ?>% so với tháng trước</span>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-content">
                <h3 id="stat-total-revenue">
                    <?php 
                    if ($totalRevenue >= 1000000000) {
                        echo number_format($totalRevenue / 1000000000, 1, ',', '.') . ' tỷ';
                    } else if ($totalRevenue >= 1000000) {
                        echo number_format($totalRevenue / 1000000, 1, ',', '.') . ' triệu';
                    } else {
                        echo number_format($totalRevenue, 0, ',', '.') . ' VNĐ';
                    }
                    ?>
                </h3>
                <p>Doanh thu hoàn thành (VNĐ)</p>
                <div class="stat-trend trend-<?php echo ($trends['revenue']['direction'] ?? 'up'); ?>" id="trend-revenue">
                    <i class="fas fa-arrow-<?php echo ($trends['revenue']['direction'] ?? 'up'); ?>"></i>
                    <span><?php echo ($trends['revenue']['value'] ?? 0); ?>% so với tháng trước</span>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
                <i class="fas fa-newspaper"></i>
            </div>
            <div class="stat-content">
                <h3 id="stat-published-news"><?php echo $stats['published_news'] ?? 0; ?></h3>
                <p>Tin tức đã xuất bản</p>
                <div class="stat-trend trend-<?php echo ($trends['news']['direction'] ?? 'up'); ?>" id="trend-news">
                    <i class="fas fa-arrow-<?php echo ($trends['news']['direction'] ?? 'up'); ?>"></i>
                    <span><?php echo ($trends['news']['value'] ?? 0); ?>% so với tháng trước</span>
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
                
            </div>
            <div class="widget-content" style="position:relative;">
                <canvas id="revenueChart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- Top Products Chart -->
        <div class="chart-widget">
            <div class="widget-header">
                <h3>Top sản phẩm bán chạy</h3>
            </div>
            <div class="widget-content" style="position:relative;">
                <canvas id="topProductsChart" width="400" height="300"></canvas>
            </div>
        </div>

        <!-- Orders Status Chart -->
        <div class="chart-widget">
            <div class="widget-header">
                <h3>Phân loại đơn hàng</h3>
            </div>
            <div class="widget-content" style="position:relative;">
                <canvas id="ordersStatusChart" width="400" height="300"></canvas>
                <div class="orders-status-summary" style="display: flex; flex-direction: column; gap: 8px; margin-top: 15px; font-size: 13px; color: #4b5563;">
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f3f4f6; padding-bottom: 4px;">
                        <span style="display: flex; align-items: center;"><span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: #10B981; margin-right: 8px;"></span>Hoàn thành</span>
                        <strong id="orders-completed-count">0</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f3f4f6; padding-bottom: 4px;">
                        <span style="display: flex; align-items: center;"><span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: #3B82F6; margin-right: 8px;"></span>Đang xử lý</span>
                        <strong id="orders-processing-count">0</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f3f4f6; padding-bottom: 4px;">
                        <span style="display: flex; align-items: center;"><span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: #F59E0B; margin-right: 8px;"></span>Chờ xử lý</span>
                        <strong id="orders-pending-count">0</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 4px;">
                        <span style="display: flex; align-items: center;"><span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: #EF4444; margin-right: 8px;"></span>Đã hủy</span>
                        <strong id="orders-cancelled-count">0</strong>
                    </div>
                </div>
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
                                    <small><?php echo number_format($product['price'] ?? 0, 0, ',', '.'); ?> VNĐ</small>
                                </div>
                                <div class="product-badge">
                                    <span class="admin-badge admin-badge-success">Đã bán: <?php echo $product['sales_count'] ?? 0; ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        
    </div>
</div>