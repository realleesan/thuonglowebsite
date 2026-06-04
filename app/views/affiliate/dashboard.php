<?php
/**
 * Affiliate Dashboard
 * Trang tổng quan hệ thống đại lý
 */

// Make global services available in view scope
global $affiliateService, $currentService;

// Chọn service affiliate (được inject từ index.php)
$service = isset($currentService) ? $currentService : ($affiliateService ?? null);

// Nếu không có AffiliateService, dừng sớm để tránh lỗi
if (!$service) {
    throw new Exception('AffiliateService is not available');
}

// Initialize data variables
$affiliateInfo = [
    'name' => '',
    'email' => '',
    'affiliate_link' => '',
    'referral_code' => ''
];
$stats = [
    'total_clicks' => 0,
    'total_orders' => 0,
    'total_revenue' => 0,
    'total_commission' => 0,
    'weekly_revenue' => 0,
    'monthly_revenue' => 0,
    'pending_commission' => 0,
    'paid_commission' => 0,
    'conversion_rate' => 0,
    'total_customers' => 0
];
$recentCustomers = [];
$commissionStatus = [
    'pending' => 0, 
    'paid' => 0,
    'pending_count' => 0,
    'paid_count' => 0
];
$revenueChart = ['labels' => [], 'data' => [], 'commission' => []];
$clicksChart = ['labels' => [], 'data' => []];
$conversionChart = ['labels' => [], 'data' => []];

try {
    // Get current affiliate ID from session
    $affiliateId = $_SESSION['user_id'] ?? 0;
    
    // Validate affiliate is logged in
    if ($affiliateId <= 0) {
        throw new Exception('Vui lòng đăng nhập để xem dashboard');
    }
    
    // Get dashboard data từ AffiliateService
    $dashboardData = $service->getDashboardData($affiliateId);
    
    // Extract data
    $affiliateInfo = $dashboardData['affiliate'] ?? $affiliateInfo;
    $stats = $dashboardData['stats'] ?? $stats;
    $recentCustomers = $dashboardData['recent_customers'] ?? [];
    $commissionStatus = $dashboardData['commission_status'] ?? $commissionStatus;
    $revenueChart = $dashboardData['revenue_chart'] ?? ['labels' => [], 'data' => [], 'commission' => []];
    $clicksChart = $dashboardData['clicks_chart'] ?? ['labels' => [], 'data' => []];
    $conversionChart = $dashboardData['conversion_chart'] ?? ['labels' => [], 'data' => []];
    
} catch (Exception $e) {
    // Handle errors gracefully
    $result = $errorHandler->handleViewError($e, 'affiliate_dashboard', []);
    error_log('Affiliate Dashboard Error: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    // Use empty state data
    $emptyState = $service->handleEmptyState('affiliate_dashboard');
    $stats = $emptyState['product_stats'] ?? $stats;
}

// Set page info cho master layout
$page_title = 'Tổng quan';
$page_module = 'dashboard';
$load_chartjs = true; // Load Chart.js cho dashboard

// Include master layout
ob_start();
?>

<!-- Stat Cards Grid -->
<div class="stats-grid">
    <!-- Doanh số tổng -->
    <div class="stat-card stat-card-primary">
        <div class="stat-icon">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Doanh số tổng</div>
            <div class="stat-value" data-value="<?php echo $stats['total_revenue']; ?>">
                <?php echo number_format($stats['total_revenue'], 0, ',', '.'); ?>đ
            </div>
        </div>
    </div>

    <!-- Tổng hoa hồng -->
    <div class="stat-card stat-card-success">
        <div class="stat-icon">
            <i class="fas fa-coins"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Tổng hoa hồng</div>
            <div class="stat-value" data-value="<?php echo $stats['total_commission']; ?>">
                <?php echo number_format($stats['total_commission'], 0, ',', '.'); ?>đ
            </div>
        </div>
    </div>

    <!-- Doanh số tháng -->
    <div class="stat-card stat-card-info">
        <div class="stat-icon">
            <i class="fas fa-calendar-alt"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Doanh số tháng</div>
            <div class="stat-value" data-value="<?php echo $stats['monthly_revenue']; ?>">
                <?php echo number_format($stats['monthly_revenue'], 0, ',', '.'); ?>đ
            </div>
        </div>
    </div>

    <!-- Tổng khách hàng -->
    <div class="stat-card stat-card-primary">
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Tổng khách hàng</div>
            <div class="stat-value" data-value="<?php echo $stats['total_customers']; ?>">
                <?php echo number_format($stats['total_customers'], 0, ',', '.'); ?>
            </div>
        </div>
    </div>
</div>

<!-- Affiliate Info Section -->
<div class="dashboard-section">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-link"></i>
                Thông tin liên kết
            </h3>
        </div>
        <div class="card-body">
            <div class="affiliate-info-grid">
                <!-- Affiliate Link -->
                <div class="affiliate-info-item">
                    <label class="affiliate-info-label">Link giới thiệu</label>
                    <div class="affiliate-info-value">
                        <input type="text" 
                               class="form-control" 
                               value="<?php echo htmlspecialchars($affiliateInfo['affiliate_link']); ?>" 
                               readonly 
                               id="affiliateLink">
                        <button type="button" 
                                class="btn btn-primary btn-copy" 
                                onclick="copyToClipboard('<?php echo htmlspecialchars($affiliateInfo['affiliate_link']); ?>', this)">
                            <i class="fas fa-copy"></i>
                            Sao chép
                        </button>
                    </div>
                </div>

                <!-- Referral Code -->
                <div class="affiliate-info-item">
                    <label class="affiliate-info-label">Mã giới thiệu</label>
                    <div class="affiliate-info-value">
                        <input type="text" 
                               class="form-control" 
                               value="<?php echo htmlspecialchars($affiliateInfo['referral_code']); ?>" 
                               readonly 
                               id="referralCode">
                        <button type="button" 
                                class="btn btn-primary btn-copy" 
                                onclick="copyToClipboard('<?php echo htmlspecialchars($affiliateInfo['referral_code']); ?>', this)">
                            <i class="fas fa-copy"></i>
                            Sao chép
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Recent Customers & Commission Status -->
<div class="dashboard-section">
    <div class="dashboard-grid-2">
        <!-- Recent Customers -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-users"></i>
                    Khách hàng gần đây
                </h3>
                <a href="?page=affiliate&module=customers" class="btn btn-sm btn-secondary">
                    Xem tất cả
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Khách hàng</th>
                                <th>Đơn hàng</th>
                                <th>Doanh số</th>
                                <th>Ngày</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($recentCustomers, 0, 5) as $customer): ?>
                            <tr>
                                <td>
                                    <div class="customer-info">
                                        <div class="customer-avatar">
                                            <?php echo strtoupper(substr($customer['name'], 0, 1)); ?>
                                        </div>
                                        <div class="customer-details">
                                            <div class="customer-name"><?php echo htmlspecialchars($customer['name']); ?></div>
                                            <div class="customer-email"><?php echo htmlspecialchars($customer['email']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo $customer['total_orders']; ?> đơn</td>
                                <td class="text-success fw-semibold">
                                    <?php echo number_format($customer['total_spent'], 0, ',', '.'); ?>đ
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($customer['joined_date'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Revenue & Commission Chart -->
        <div class="card chart-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-line"></i>
                    Hiệu suất bán hàng (7 ngày qua)
                </h3>
                <a href="?page=affiliate&module=reports&action=orders" class="btn btn-sm btn-secondary">
                    Xem chi tiết
                </a>
            </div>
            <div class="card-body">
                <div class="charts-grid" 
                     style="display: block; width: 100%;"
                     data-revenue-labels="<?php echo htmlspecialchars(json_encode($revenueChart['labels'])); ?>"
                     data-revenue-data="<?php echo htmlspecialchars(json_encode($revenueChart['data'])); ?>"
                     data-commission-data="<?php echo htmlspecialchars(json_encode($revenueChart['commission'] ?? [])); ?>">
                    <div style="height: 300px; width: 100%; position: relative;">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../_layout/affiliate_master.php';
?>
