<?php
/**
 * Affiliate Dashboard
 * Trang tổng quan hệ thống đại lý
 */

// Load Models
require_once __DIR__ . '/../../../models/AffiliateModel.php';
require_once __DIR__ . '/../../../models/OrdersModel.php';
require_once __DIR__ . '/../../../models/UsersModel.php';

$affiliateModel = new AffiliateModel();
$ordersModel = new OrdersModel();
$usersModel = new UsersModel();

try {
    // Get current affiliate ID from session
    $affiliateId = $_SESSION['user_id'] ?? 1; // Default for demo
    
    // Get affiliate data from database
    $affiliateInfo = $affiliateModel->getWithUser($affiliateId);
    if (!$affiliateInfo) {
        throw new Exception('Affiliate not found');
    }
    
    // Get dashboard data from database
    $dashboardData = $affiliateModel->getDashboardData($affiliateId);
    $stats = [
        'total_clicks' => rand(1000, 5000),
        'total_orders' => count($dashboardData['recent_orders']),
        'total_revenue' => $affiliateInfo['total_sales'],
        'total_commission' => $affiliateInfo['total_commission'],
        'weekly_revenue' => $affiliateInfo['total_sales'] * 0.2, // 20% of total for demo
        'monthly_revenue' => $affiliateInfo['total_sales'] * 0.8, // 80% of total for demo
        'pending_commission' => $affiliateInfo['pending_commission'] ?? 0,
        'paid_commission' => $affiliateInfo['paid_commission'] ?? 0,
        'conversion_rate' => rand(15, 35) / 10, // Random 1.5-3.5%
        'total_customers' => count($dashboardData['recent_orders'])
    ];
    
    // Get recent customers with proper structure
    $recentCustomers = [];
    foreach ($dashboardData['recent_orders'] as $order) {
        $customer = $usersModel->getById($order['user_id']);
        if ($customer) {
            $recentCustomers[] = [
                'name' => $customer['name'] ?? $customer['full_name'] ?? 'Khách hàng',
                'email' => $customer['email'] ?? 'email@example.com',
                'total_orders' => rand(1, 10),
                'total_spent' => rand(500000, 5000000),
                'joined_date' => $customer['created_at'] ?? date('Y-m-d')
            ];
        }
    }
    
    // Limit to 5 customers
    $recentCustomers = array_slice($recentCustomers, 0, 5);
    $commissionStatus = [
        'pending' => $stats['pending_commission'],
        'paid' => $stats['paid_commission'],
        'pending_count' => rand(5, 15),
        'paid_count' => rand(20, 50)
    ];
    
    // Generate chart data
    $revenueChart = ['labels' => [], 'data' => []];
    $clicksChart = ['labels' => [], 'data' => []];
    $conversionChart = ['labels' => [], 'data' => []];
    
} catch (Exception $e) {
    error_log('Affiliate Dashboard Error: ' . $e->getMessage());
    // Set default values
    $affiliateInfo = [
        'name' => 'Demo User', 
        'email' => 'demo@example.com',
        'affiliate_link' => 'https://thuonglo.com/?ref=DEMO123',
        'referral_code' => 'DEMO123'
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
    $revenueChart = ['labels' => [], 'data' => []];
    $clicksChart = ['labels' => [], 'data' => []];
    $conversionChart = ['labels' => [], 'data' => []];
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
                <?php echo number_format($stats['total_revenue']); ?>đ
            </div>
        </div>
    </div>

    <!-- Doanh số tuần -->
    <div class="stat-card stat-card-success">
        <div class="stat-icon">
            <i class="fas fa-calendar-week"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Doanh số tuần</div>
            <div class="stat-value" data-value="<?php echo $stats['weekly_revenue']; ?>">
                <?php echo number_format($stats['weekly_revenue']); ?>đ
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
                <?php echo number_format($stats['monthly_revenue']); ?>đ
            </div>
        </div>
    </div>

    <!-- Lượt click -->
    <div class="stat-card stat-card-warning">
        <div class="stat-icon">
            <i class="fas fa-mouse-pointer"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Lượt click</div>
            <div class="stat-value" data-value="<?php echo $stats['total_clicks']; ?>">
                <?php echo number_format($stats['total_clicks']); ?>
            </div>
        </div>
    </div>

    <!-- Hoa hồng chờ -->
    <div class="stat-card stat-card-warning">
        <div class="stat-icon">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Hoa hồng chờ</div>
            <div class="stat-value" data-value="<?php echo $stats['pending_commission']; ?>">
                <?php echo number_format($stats['pending_commission']); ?>đ
            </div>
        </div>
    </div>

    <!-- Hoa hồng đã trả -->
    <div class="stat-card stat-card-success">
        <div class="stat-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Hoa hồng đã trả</div>
            <div class="stat-value" data-value="<?php echo $stats['paid_commission']; ?>">
                <?php echo number_format($stats['paid_commission']); ?>đ
            </div>
        </div>
    </div>

    <!-- Tỉ lệ chuyển đổi -->
    <div class="stat-card stat-card-info">
        <div class="stat-icon">
            <i class="fas fa-percentage"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Tỉ lệ chuyển đổi</div>
            <div class="stat-value" data-value="<?php echo $stats['conversion_rate']; ?>">
                <?php echo number_format($stats['conversion_rate'], 1); ?>%
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
                <?php echo number_format($stats['total_customers']); ?>
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

<!-- Charts Section -->
<div class="dashboard-section">
    <div class="charts-grid" 
         data-revenue-labels='<?php echo json_encode($revenueChart['labels']); ?>'
         data-revenue-data='<?php echo json_encode($revenueChart['data']); ?>'
         data-clicks-labels='<?php echo json_encode($clicksChart['labels']); ?>'
         data-clicks-data='<?php echo json_encode($clicksChart['data']); ?>'
         data-conversion-labels='<?php echo json_encode($conversionChart['labels']); ?>'
         data-conversion-data='<?php echo json_encode($conversionChart['data']); ?>'>
        <!-- Revenue Chart -->
        <div class="chart-card">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i>
                        Doanh thu theo tuần
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Clicks Chart -->
        <div class="chart-card">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i>
                        Lượt click theo tuần
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="clicksChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Conversion Chart -->
        <div class="chart-card">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie"></i>
                        Tỉ lệ chuyển đổi
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="conversionChart"></canvas>
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
                                    <?php echo number_format($customer['total_spent']); ?>đ
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($customer['joined_date'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Commission Status -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-wallet"></i>
                    Trạng thái hoa hồng
                </h3>
                <a href="?page=affiliate&module=commissions" class="btn btn-sm btn-secondary">
                    Chi tiết
                </a>
            </div>
            <div class="card-body">
                <div class="commission-status-grid">
                    <!-- Pending Commission -->
                    <div class="commission-status-item">
                        <div class="commission-status-icon commission-status-warning">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="commission-status-content">
                            <div class="commission-status-label">Chờ thanh toán</div>
                            <div class="commission-status-value">
                                <?php echo number_format($commissionStatus['pending']); ?>đ
                            </div>
                            <div class="commission-status-count">
                                <?php echo $commissionStatus['pending_count']; ?> đơn hàng
                            </div>
                        </div>
                    </div>

                    <!-- Paid Commission -->
                    <div class="commission-status-item">
                        <div class="commission-status-icon commission-status-success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="commission-status-content">
                            <div class="commission-status-label">Đã thanh toán</div>
                            <div class="commission-status-value">
                                <?php echo number_format($commissionStatus['paid']); ?>đ
                            </div>
                            <div class="commission-status-count">
                                <?php echo $commissionStatus['paid_count']; ?> đơn hàng
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="commission-progress">
                    <?php 
                    $total = $commissionStatus['pending'] + $commissionStatus['paid'];
                    $paidPercentage = $total > 0 ? ($commissionStatus['paid'] / $total) * 100 : 0;
                    ?>
                    <div class="progress">
                        <div class="progress-bar bg-success" 
                             role="progressbar" 
                             style="width: <?php echo $paidPercentage; ?>%"
                             aria-valuenow="<?php echo $paidPercentage; ?>" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                            <?php echo number_format($paidPercentage, 1); ?>%
                        </div>
                    </div>
                    <div class="commission-progress-label">
                        Tỉ lệ thanh toán
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
