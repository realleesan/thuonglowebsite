<?php
/**
 * Admin Revenue Index
 * Sử dụng AdminService thông qua ServiceManager
 */

// Khởi tạo View & ServiceManager
require_once __DIR__ . '/../../../../core/view_init.php';

// Chọn service admin (được inject từ index.php)
$service = isset($currentService) ? $currentService : ($adminService ?? null);

try {
    // Date filter
    $date_from = $_GET['date_from'] ?? date('Y-m-01'); // First day of current month
    $date_to = $_GET['date_to'] ?? date('Y-m-d'); // Today
    $period = $_GET['period'] ?? 'month'; // month, quarter, year
    
    $filters = [
        'date_from' => $date_from,
        'date_to' => $date_to,
        'period' => $period
    ];
    
    // Get revenue data from service
    $revenueData = $service->getRevenueData($filters);
    $filtered_orders = $revenueData['orders'];
    $products = $revenueData['products'];
    $users = $revenueData['users'];
    $product_lookup = $revenueData['products'];
    $user_lookup = $revenueData['users'];
    $revenue_by_product = $revenueData['revenue_by_product'];
    $revenue_by_date = $revenueData['revenue_by_date'];
    
    // Extract statistics
    $stats = $revenueData['stats'];
    $total_revenue = $stats['total_revenue'];
    $completed_revenue = $stats['completed_revenue'];
    $pending_revenue = $stats['pending_revenue'];
    $total_orders = $stats['total_orders'];
    $completed_orders = $stats['completed_orders'];
    $pending_orders = $stats['pending_orders'];
    $processing_orders = $stats['processing_orders'];
    $cancelled_orders = $stats['cancelled_orders'];
    $revenue_by_status = $stats['revenue_by_status'];
    
} catch (Exception $e) {
    $errorHandler->logError('Admin Revenue Error', $e);
    $filtered_orders = [];
    $products = [];
    $users = [];
    $product_lookup = [];
    $user_lookup = [];
    $revenue_by_product = [];
    $revenue_by_date = [];
    $total_revenue = 0;
    $completed_revenue = 0;
    $pending_revenue = 0;
    $total_orders = 0;
    $completed_orders = 0;
    $pending_orders = 0;
    $processing_orders = 0;
    $cancelled_orders = 0;
    $revenue_by_status = ['completed' => 0, 'processing' => 0, 'pending' => 0, 'cancelled' => 0];
}

// Generate chart data for revenue by date
$chart_dates = [];
$chart_revenues = [];
$current_date = strtotime($date_from);
$end_date = strtotime($date_to);

while ($current_date <= $end_date) {
    $date_str = date('Y-m-d', $current_date);
    $chart_dates[] = date('d/m', $current_date);
    $chart_revenues[] = $revenue_by_date[$date_str] ?? 0;
    $current_date = strtotime('+1 day', $current_date);
}

// Format functions
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' VNĐ';
}

function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

function getStatusText($status) {
    $statuses = [
        'completed' => 'Hoàn thành',
        'processing' => 'Đang xử lý',
        'pending' => 'Chờ xử lý',
        'cancelled' => 'Đã hủy'
    ];
    return $statuses[$status] ?? $status;
}

function getStatusColor($status) {
    $colors = [
        'completed' => '#10B981',
        'processing' => '#3B82F6',
        'pending' => '#F59E0B',
        'cancelled' => '#EF4444'
    ];
    return $colors[$status] ?? '#6B7280';
}
?>

<div class="revenue-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-chart-line"></i>
                Báo Cáo Doanh Thu
            </h1>
            <p class="page-description">Tổng quan doanh thu và phân tích kinh doanh</p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=revenue&action=view" class="btn btn-primary">
                <i class="fas fa-chart-bar"></i>
                Chi Tiết Doanh Thu
            </a>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="filters-section">
        <form method="GET" class="filters-form">
            <input type="hidden" name="page" value="admin">
            <input type="hidden" name="module" value="revenue">
            
            <div class="filter-group">
                <div class="filter-item">
                    <label for="date_from">Từ ngày:</label>
                    <input type="date" id="date_from" name="date_from" value="<?= $date_from ?>">
                </div>
                
                <div class="filter-item">
                    <label for="date_to">Đến ngày:</label>
                    <input type="date" id="date_to" name="date_to" value="<?= $date_to ?>">
                </div>
                
                <div class="filter-item">
                    <label for="period">Kỳ báo cáo:</label>
                    <select id="period" name="period">
                        <option value="day" <?= $period == 'day' ? 'selected' : '' ?>>Theo ngày</option>
                        <option value="month" <?= $period == 'month' ? 'selected' : '' ?>>Theo tháng</option>
                        <option value="quarter" <?= $period == 'quarter' ? 'selected' : '' ?>>Theo quý</option>
                        <option value="year" <?= $period == 'year' ? 'selected' : '' ?>>Theo năm</option>
                    </select>
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-search"></i>
                        Lọc
                    </button>
                    <button type="button" class="btn btn-outline" onclick="exportReport()">
                        <i class="fas fa-download"></i>
                        Xuất báo cáo
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Revenue Summary Cards -->
    <div class="revenue-summary">
        <div class="summary-card">
            <div class="summary-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="summary-content">
                <h3 class="summary-title">Tổng Doanh Thu</h3>
                <p class="summary-value"><?= formatPrice($total_revenue) ?></p>
                <span class="summary-period">Từ <?= formatDate($date_from) ?> đến <?= formatDate($date_to) ?></span>
            </div>
        </div>

        <div class="summary-card">
            <div class="summary-icon success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="summary-content">
                <h3 class="summary-title">Doanh Thu Hoàn Thành</h3>
                <p class="summary-value"><?= formatPrice($completed_revenue) ?></p>
                <span class="summary-period"><?= $completed_orders ?> đơn hàng</span>
            </div>
        </div>

        <div class="summary-card">
            <div class="summary-icon warning">
                <i class="fas fa-clock"></i>
            </div>
            <div class="summary-content">
                <h3 class="summary-title">Doanh Thu Chờ Xử Lý</h3>
                <p class="summary-value"><?= formatPrice($pending_revenue) ?></p>
                <span class="summary-period"><?= $pending_orders ?> đơn hàng</span>
            </div>
        </div>

        <div class="summary-card">
            <div class="summary-icon info">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="summary-content">
                <h3 class="summary-title">Tổng Đơn Hàng</h3>
                <p class="summary-value"><?= $total_orders ?></p>
                <span class="summary-period">Tất cả trạng thái</span>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-section">
        <!-- Revenue Trend Chart -->
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">
                    <i class="fas fa-line-chart"></i>
                    Xu Hướng Doanh Thu
                </h3>
                <div class="chart-actions">
                    <button type="button" class="btn btn-sm btn-outline" onclick="toggleChartType('revenue-chart')">
                        <i class="fas fa-exchange-alt"></i>
                        Đổi kiểu
                    </button>
                </div>
            </div>
            <div class="chart-body">
                <canvas id="revenue-chart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- Revenue by Status Chart -->
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">
                    <i class="fas fa-pie-chart"></i>
                    Doanh Thu Theo Trạng Thái
                </h3>
            </div>
            <div class="chart-body">
                <canvas id="status-chart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Products Table -->
    <div class="top-products-section">
        <div class="section-header">
            <h3 class="section-title">
                <i class="fas fa-trophy"></i>
                Top Sản Phẩm Bán Chạy
            </h3>
            <a href="?page=admin&module=revenue&action=view&tab=products" class="btn btn-sm btn-outline">
                Xem tất cả
            </a>
        </div>
        
        <div class="table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th width="60">Hạng</th>
                        <th>Sản phẩm</th>
                        <th width="120">Số đơn</th>
                        <th width="150">Doanh thu</th>
                        <th width="100">Tỷ lệ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($revenue_by_product)): ?>
                        <tr>
                            <td colspan="5" class="no-data">
                                <i class="fas fa-inbox"></i>
                                <p>Không có dữ liệu trong kỳ này</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $rank = 1;
                        $displayed = 0;
                        foreach ($revenue_by_product as $product_data): 
                            if ($displayed >= 10) break; // Show top 10 only
                            $product = $product_data['product'];
                            if (!$product) continue;
                            $percentage = $total_revenue > 0 ? ($product_data['revenue'] / $total_revenue) * 100 : 0;
                        ?>
                            <tr>
                                <td>
                                    <span class="rank-badge rank-<?= $rank <= 3 ? $rank : 'other' ?>">
                                        #<?= $rank ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="product-info">
                                        <h4 class="product-name"><?= htmlspecialchars($product['name']) ?></h4>
                                        <p class="product-price"><?= formatPrice($product['price']) ?></p>
                                    </div>
                                </td>
                                <td>
                                    <span class="orders-count"><?= $product_data['orders'] ?></span>
                                </td>
                                <td class="revenue-cell">
                                    <?= formatPrice($product_data['revenue']) ?>
                                </td>
                                <td>
                                    <div class="percentage-bar">
                                        <div class="percentage-fill" style="width: <?= $percentage ?>%"></div>
                                        <span class="percentage-text"><?= number_format($percentage, 1) ?>%</span>
                                    </div>
                                </td>
                            </tr>
                        <?php 
                            $rank++;
                            $displayed++;
                        endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="recent-orders-section">
        <div class="section-header">
            <h3 class="section-title">
                <i class="fas fa-clock"></i>
                Đơn Hàng Gần Đây
            </h3>
            <a href="?page=admin&module=orders" class="btn btn-sm btn-outline">
                Xem tất cả
            </a>
        </div>
        
        <div class="table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th width="80">Mã ĐH</th>
                        <th>Khách hàng</th>
                        <th>Sản phẩm</th>
                        <th width="120">Giá trị</th>
                        <th width="100">Trạng thái</th>
                        <th width="120">Ngày tạo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Sort orders by date (newest first) and take first 10
                    $recent_orders = $filtered_orders;
                    usort($recent_orders, function($a, $b) {
                        return strtotime($b['created_at']) - strtotime($a['created_at']);
                    });
                    $recent_orders = array_slice($recent_orders, 0, 10);
                    ?>
                    
                    <?php if (empty($recent_orders)): ?>
                        <tr>
                            <td colspan="6" class="no-data">
                                <i class="fas fa-inbox"></i>
                                <p>Không có đơn hàng nào trong kỳ này</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td>
                                    <strong>#<?= str_pad($order['id'], 4, '0', STR_PAD_LEFT) ?></strong>
                                </td>
                                <td>
                                    <div class="customer-info">
                                        <span class="customer-name">
                                            <?= htmlspecialchars($user_lookup[$order['user_id']]['name'] ?? 'N/A') ?>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="product-info">
                                        <span class="product-name">
                                            <?= htmlspecialchars($product_lookup[$order['product_id']]['name'] ?? 'N/A') ?>
                                        </span>
                                        <span class="product-quantity">x<?= $order['quantity'] ?></span>
                                    </div>
                                </td>
                                <td class="price-cell">
                                    <?= formatPrice($order['total']) ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= $order['status'] ?>">
                                        <?= getStatusText($order['status']) ?>
                                    </span>
                                </td>
                                <td><?= formatDate($order['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart.js Data (passed via data attributes, rendered by admin_revenue.js) -->
<script id="revenue-chart-data" type="application/json">
<?php echo json_encode([
    'labels'   => $chart_dates,
    'revenues' => $chart_revenues,
    'status'   => $revenue_by_status ?? ['completed' => 0, 'processing' => 0, 'pending' => 0, 'cancelled' => 0],
], JSON_UNESCAPED_UNICODE); ?>
</script>
