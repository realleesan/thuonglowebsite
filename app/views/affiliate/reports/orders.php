<?php
/**
 * Reports - Báo Cáo Đơn Hàng
 * Analytics về orders, revenue, products
 */

// 1. Khởi tạo View & ServiceManager
require_once __DIR__ . '/../../../../core/view_init.php';

// 2. Chọn service affiliate (được inject từ index.php)
$service = isset($currentService) ? $currentService : ($affiliateService ?? null);

// Initialize data variables
$totalOrders = 0;
$totalRevenue = 0;
$totalCommission = 0;
$avgOrderValue = 0;
$ordersByDate = [];
$ordersByProduct = [];

try {
    if ($service) {
        // Get current affiliate ID from session
        $affiliateId = $_SESSION['user_id'] ?? 1;
        
        // Get dashboard data FIRST for affiliate info (needed by header)
        $dashboardData = $service->getDashboardData($affiliateId);
        $affiliateInfo = $dashboardData['affiliate'] ?? [
            'name' => '',
            'email' => ''
        ];
        
        $stats = $dashboardData['stats'] ?? [];
        
        $totalOrders = $stats['total_orders'] ?? 0;
        $totalRevenue = $stats['total_revenue'] ?? 0;
        $totalCommission = $stats['total_commission'] ?? 0;
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
    }
} catch (Exception $e) {
    $errorHandler->handleViewError($e, 'affiliate_reports_orders', []);
}

// Page title
$page_title = 'Báo Cáo Đơn Hàng';
$load_chartjs = true;

// Include master layout
ob_start();
?>

// Include master layout
ob_start();
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-content">
        <h1 class="page-title">
            <i class="fas fa-shopping-cart"></i>
            Báo Cáo Đơn Hàng
        </h1>
        <p class="page-description">Phân tích đơn hàng và doanh thu</p>
    </div>
    <div class="page-header-actions">
        <button type="button" class="btn btn-outline" onclick="exportOrdersReport()">
            <i class="fas fa-file-excel"></i>
            <span>Xuất Excel</span>
        </button>
    </div>
</div>

<!-- Stats Cards -->
<div class="reports-stats">
    <div class="stat-card stat-card-primary">
        <div class="stat-icon">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Tổng Đơn Hàng</div>
            <div class="stat-value"><?php echo number_format($totalOrders); ?></div>
            <div class="stat-footer">
                <span class="stat-note">Tất cả đơn hàng</span>
            </div>
        </div>
    </div>

    <div class="stat-card stat-card-success">
        <div class="stat-icon">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Tổng Doanh Thu</div>
            <div class="stat-value"><?php echo number_format($totalRevenue); ?> đ</div>
            <div class="stat-footer">
                <span class="stat-note">Từ khách hàng</span>
            </div>
        </div>
    </div>

    <div class="stat-card stat-card-warning">
        <div class="stat-icon">
            <i class="fas fa-hand-holding-usd"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Tổng Hoa Hồng</div>
            <div class="stat-value"><?php echo number_format($totalCommission); ?> đ</div>
            <div class="stat-footer">
                <span class="stat-note">Thu nhập của bạn</span>
            </div>
        </div>
    </div>

    <div class="stat-card stat-card-info">
        <div class="stat-icon">
            <i class="fas fa-receipt"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Giá Trị TB</div>
            <div class="stat-value"><?php echo number_format($avgOrderValue); ?> đ</div>
            <div class="stat-footer">
                <span class="stat-note">Trung bình/đơn</span>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="reports-charts">
    <!-- Revenue by Date Chart -->
    <div class="chart-card chart-card-full">
        <div class="chart-card-header">
            <h3 class="chart-title">
                <i class="fas fa-chart-area"></i>
                Doanh Thu & Hoa Hồng Theo Ngày
            </h3>
        </div>
        <div class="chart-card-body">
            <canvas id="revenueByDateChart"
                    data-labels='<?php echo json_encode(array_column($ordersByDate, 'date')); ?>'
                    data-revenue='<?php echo json_encode(array_column($ordersByDate, 'revenue')); ?>'
                    data-commission='<?php echo json_encode(array_column($ordersByDate, 'commission')); ?>'>
            </canvas>
        </div>
    </div>
</div>

<!-- Products Performance -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-box"></i>
            Hiệu Quả Sản Phẩm
        </h3>
    </div>
    <div class="card-body">
        <div class="products-grid">
            <?php foreach ($ordersByProduct as $product): ?>
            <div class="product-performance-card">
                <div class="product-header">
                    <h4 class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></h4>
                    <span class="product-percentage"><?php echo number_format($product['percentage'], 1); ?>%</span>
                </div>
                <div class="product-stats">
                    <div class="product-stat">
                        <span class="stat-label">Đơn hàng</span>
                        <span class="stat-value"><?php echo number_format($product['orders']); ?></span>
                    </div>
                    <div class="product-stat">
                        <span class="stat-label">Doanh thu</span>
                        <span class="stat-value"><?php echo number_format($product['revenue']); ?> đ</span>
                    </div>
                    <div class="product-stat">
                        <span class="stat-label">Hoa hồng</span>
                        <span class="stat-value text-success"><?php echo number_format($product['commission']); ?> đ</span>
                    </div>
                </div>
                <div class="product-progress">
                    <div class="progress-bar-container">
                        <div class="progress-bar" style="width: <?php echo $product['percentage']; ?>%"></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../_layout/affiliate_master.php';
?>
