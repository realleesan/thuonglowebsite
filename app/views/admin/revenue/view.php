<?php
/**
 * Admin Revenue View
 * Sử dụng AdminService thông qua ServiceManager
 */

// Khởi tạo View & ServiceManager
require_once __DIR__ . '/../../../../core/view_init.php';

// Chọn service admin (được inject từ index.php)
$service = isset($currentService) ? $currentService : ($adminService ?? null);

try {
    // Get filters from URL
    $filters = [
        'date_from' => $_GET['date_from'] ?? '',
        'date_to' => $_GET['date_to'] ?? '',
        'product_id' => $_GET['product_id'] ?? '',
        'user_id' => $_GET['user_id'] ?? '',
        'affiliate_id' => $_GET['affiliate_id'] ?? ''
    ];
    
    // Get revenue data using AdminService
    $revenueData = $service->getRevenueData($filters);
    $filtered_orders = $revenueData['orders'];
    $products = $revenueData['products'];
    $users = $revenueData['users'];
    $affiliates = $revenueData['affiliates'];
    $revenue_stats = $revenueData['stats'];
    
} catch (Exception $e) {
    $errorHandler->logError('Admin Revenue View Error', $e);
    $filtered_orders = [];
    $products = [];
    $users = [];
    $affiliates = [];
    $revenue_stats = ['total_revenue' => 0, 'total_orders' => 0, 'avg_order_value' => 0];
}
foreach ($affiliates as $affiliate) {
    $affiliate_lookup[$affiliate['user_id']] = $affiliate;
}

// Parameters
$tab = $_GET['tab'] ?? 'overview';
$date_from = $_GET['date_from'] ?? date('Y-m-01');
$date_to = $_GET['date_to'] ?? date('Y-m-d');
$period = $_GET['period'] ?? 'month';

// Filter orders by date
$filtered_orders = array_filter($orders, function($order) use ($date_from, $date_to) {
    $order_date = date('Y-m-d', strtotime($order['created_at']));
    return $order_date >= $date_from && $order_date <= $date_to;
});

// Calculate detailed statistics
$stats = [
    'total_revenue' => 0,
    'total_orders' => count($filtered_orders),
    'avg_order_value' => 0,
    'conversion_rate' => 0,
    'by_status' => [
        'completed' => ['count' => 0, 'revenue' => 0],
        'processing' => ['count' => 0, 'revenue' => 0],
        'pending' => ['count' => 0, 'revenue' => 0],
        'cancelled' => ['count' => 0, 'revenue' => 0]
    ],
    'by_product' => [],
    'by_customer' => [],
    'by_payment_method' => [],
    'by_date' => []
];

foreach ($filtered_orders as $order) {
    $stats['total_revenue'] += $order['total'];
    $stats['by_status'][$order['status']]['count']++;
    $stats['by_status'][$order['status']]['revenue'] += $order['total'];
    
    // By product
    $product_id = $order['product_id'];
    if (!isset($stats['by_product'][$product_id])) {
        $stats['by_product'][$product_id] = [
            'product' => $product_lookup[$product_id] ?? null,
            'orders' => 0,
            'revenue' => 0,
            'quantity' => 0
        ];
    }
    $stats['by_product'][$product_id]['orders']++;
    $stats['by_product'][$product_id]['revenue'] += $order['total'];
    $stats['by_product'][$product_id]['quantity'] += $order['quantity'];
    
    // By customer
    $user_id = $order['user_id'];
    if (!isset($stats['by_customer'][$user_id])) {
        $stats['by_customer'][$user_id] = [
            'user' => $user_lookup[$user_id] ?? null,
            'orders' => 0,
            'revenue' => 0
        ];
    }
    $stats['by_customer'][$user_id]['orders']++;
    $stats['by_customer'][$user_id]['revenue'] += $order['total'];
    
    // By payment method
    $payment_method = $order['payment_method'];
    if (!isset($stats['by_payment_method'][$payment_method])) {
        $stats['by_payment_method'][$payment_method] = [
            'orders' => 0,
            'revenue' => 0
        ];
    }
    $stats['by_payment_method'][$payment_method]['orders']++;
    $stats['by_payment_method'][$payment_method]['revenue'] += $order['total'];
    
    // By date
    $date = date('Y-m-d', strtotime($order['created_at']));
    if (!isset($stats['by_date'][$date])) {
        $stats['by_date'][$date] = [
            'orders' => 0,
            'revenue' => 0
        ];
    }
    $stats['by_date'][$date]['orders']++;
    $stats['by_date'][$date]['revenue'] += $order['total'];
}

$stats['avg_order_value'] = $stats['total_orders'] > 0 ? $stats['total_revenue'] / $stats['total_orders'] : 0;

// Sort data
uasort($stats['by_product'], function($a, $b) { return $b['revenue'] - $a['revenue']; });
uasort($stats['by_customer'], function($a, $b) { return $b['revenue'] - $a['revenue']; });
ksort($stats['by_date']);

// Format functions
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' VNĐ';
}

function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

function getPaymentMethodText($method) {
    $methods = [
        'bank_transfer' => 'Chuyển khoản',
        'momo' => 'MoMo',
        'vnpay' => 'VNPay',
        'cash' => 'Tiền mặt'
    ];
    return $methods[$method] ?? $method;
}
?>
<div class="revenue-detail-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-chart-bar"></i>
                Chi Tiết Doanh Thu
            </h1>
            <p class="page-description">Phân tích chi tiết doanh thu từ <?= formatDate($date_from) ?> đến <?= formatDate($date_to) ?></p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=revenue" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Quay lại
            </a>
            <button type="button" class="btn btn-primary" onclick="exportDetailReport()">
                <i class="fas fa-file-excel"></i>
                Xuất Excel
            </button>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="filters-section">
        <form method="GET" class="filters-form">
            <input type="hidden" name="page" value="admin">
            <input type="hidden" name="module" value="revenue">
            <input type="hidden" name="action" value="view">
            <input type="hidden" name="tab" value="<?= $tab ?>">
            
            <div class="filter-group">
                <div class="filter-item">
                    <label for="date_from">Từ ngày:</label>
                    <input type="date" id="date_from" name="date_from" value="<?= $date_from ?>">
                </div>
                
                <div class="filter-item">
                    <label for="date_to">Đến ngày:</label>
                    <input type="date" id="date_to" name="date_to" value="<?= $date_to ?>">
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-search"></i>
                        Lọc
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Summary Stats -->
    <div class="detail-summary">
        <div class="summary-card">
            <div class="summary-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="summary-content">
                <h3 class="summary-title">Tổng Doanh Thu</h3>
                <p class="summary-value"><?= formatPrice($stats['total_revenue']) ?></p>
            </div>
        </div>

        <div class="summary-card">
            <div class="summary-icon info">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="summary-content">
                <h3 class="summary-title">Tổng Đơn Hàng</h3>
                <p class="summary-value"><?= $stats['total_orders'] ?></p>
            </div>
        </div>

        <div class="summary-card">
            <div class="summary-icon success">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="summary-content">
                <h3 class="summary-title">Giá Trị Đơn Hàng TB</h3>
                <p class="summary-value"><?= formatPrice($stats['avg_order_value']) ?></p>
            </div>
        </div>

        <div class="summary-card">
            <div class="summary-icon warning">
                <i class="fas fa-percentage"></i>
            </div>
            <div class="summary-content">
                <h3 class="summary-title">Tỷ Lệ Hoàn Thành</h3>
                <p class="summary-value">
                    <?= $stats['total_orders'] > 0 ? number_format(($stats['by_status']['completed']['count'] / $stats['total_orders']) * 100, 1) : 0 ?>%
                </p>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="tab-navigation">
        <a href="?page=admin&module=revenue&action=view&tab=overview&<?= http_build_query(['date_from' => $date_from, 'date_to' => $date_to]) ?>" 
           class="tab-link <?= $tab == 'overview' ? 'active' : '' ?>">
            <i class="fas fa-chart-pie"></i>
            Tổng quan
        </a>
        <a href="?page=admin&module=revenue&action=view&tab=products&<?= http_build_query(['date_from' => $date_from, 'date_to' => $date_to]) ?>" 
           class="tab-link <?= $tab == 'products' ? 'active' : '' ?>">
            <i class="fas fa-box"></i>
            Theo sản phẩm
        </a>
        <a href="?page=admin&module=revenue&action=view&tab=customers&<?= http_build_query(['date_from' => $date_from, 'date_to' => $date_to]) ?>" 
           class="tab-link <?= $tab == 'customers' ? 'active' : '' ?>">
            <i class="fas fa-users"></i>
            Theo khách hàng
        </a>
        <a href="?page=admin&module=revenue&action=view&tab=timeline&<?= http_build_query(['date_from' => $date_from, 'date_to' => $date_to]) ?>" 
           class="tab-link <?= $tab == 'timeline' ? 'active' : '' ?>">
            <i class="fas fa-calendar"></i>
            Theo thời gian
        </a>
    </div>

    <!-- Tab Content -->
    <div class="tab-content">
        <?php if ($tab == 'overview'): ?>
            <!-- Overview Tab -->
            <div class="overview-tab">
                <!-- Revenue by Status -->
                <div class="chart-section">
                    <div class="chart-container">
                        <div class="chart-header">
                            <h3 class="chart-title">Doanh Thu Theo Trạng Thái</h3>
                        </div>
                        <div class="chart-body">
                            <canvas id="status-detail-chart" width="400" height="300"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Payment Methods -->
                <div class="payment-methods-section">
                    <h3 class="section-title">
                        <i class="fas fa-credit-card"></i>
                        Phương Thức Thanh Toán
                    </h3>
                    <div class="payment-grid">
                        <?php foreach ($stats['by_payment_method'] as $method => $data): ?>
                            <div class="payment-card">
                                <div class="payment-icon">
                                    <i class="fas fa-<?= $method == 'bank_transfer' ? 'university' : ($method == 'momo' ? 'mobile-alt' : 'credit-card') ?>"></i>
                                </div>
                                <div class="payment-info">
                                    <h4><?= getPaymentMethodText($method) ?></h4>
                                    <p class="payment-revenue"><?= formatPrice($data['revenue']) ?></p>
                                    <span class="payment-orders"><?= $data['orders'] ?> đơn hàng</span>
                                </div>
                                <div class="payment-percentage">
                                    <?= $stats['total_revenue'] > 0 ? number_format(($data['revenue'] / $stats['total_revenue']) * 100, 1) : 0 ?>%
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

        <?php elseif ($tab == 'products'): ?>
            <!-- Products Tab -->
            <div class="products-tab">
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th width="60">Hạng</th>
                                <th>Sản phẩm</th>
                                <th width="100">Số đơn</th>
                                <th width="100">Số lượng</th>
                                <th width="150">Doanh thu</th>
                                <th width="100">Tỷ lệ</th>
                                <th width="120">Giá TB/đơn</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($stats['by_product'])): ?>
                                <tr>
                                    <td colspan="7" class="no-data">
                                        <i class="fas fa-inbox"></i>
                                        <p>Không có dữ liệu sản phẩm</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php 
                                $rank = 1;
                                foreach ($stats['by_product'] as $product_data): 
                                    $product = $product_data['product'];
                                    if (!$product) continue;
                                    $percentage = $stats['total_revenue'] > 0 ? ($product_data['revenue'] / $stats['total_revenue']) * 100 : 0;
                                    $avg_per_order = $product_data['orders'] > 0 ? $product_data['revenue'] / $product_data['orders'] : 0;
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
                                                <p class="product-price">Giá: <?= formatPrice($product['price']) ?></p>
                                            </div>
                                        </td>
                                        <td><?= $product_data['orders'] ?></td>
                                        <td><?= $product_data['quantity'] ?></td>
                                        <td class="revenue-cell">
                                            <?= formatPrice($product_data['revenue']) ?>
                                        </td>
                                        <td>
                                            <div class="percentage-bar">
                                                <div class="percentage-fill" style="width: <?= $percentage ?>%"></div>
                                                <span class="percentage-text"><?= number_format($percentage, 1) ?>%</span>
                                            </div>
                                        </td>
                                        <td><?= formatPrice($avg_per_order) ?></td>
                                    </tr>
                                <?php 
                                    $rank++;
                                endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($tab == 'customers'): ?>
            <!-- Customers Tab -->
            <div class="customers-tab">
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th width="60">Hạng</th>
                                <th>Khách hàng</th>
                                <th width="120">Số đơn hàng</th>
                                <th width="150">Tổng chi tiêu</th>
                                <th width="120">Chi tiêu TB</th>
                                <th width="100">Loại KH</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($stats['by_customer'])): ?>
                                <tr>
                                    <td colspan="6" class="no-data">
                                        <i class="fas fa-inbox"></i>
                                        <p>Không có dữ liệu khách hàng</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php 
                                $rank = 1;
                                foreach ($stats['by_customer'] as $customer_data): 
                                    $user = $customer_data['user'];
                                    if (!$user) continue;
                                    $avg_per_order = $customer_data['orders'] > 0 ? $customer_data['revenue'] / $customer_data['orders'] : 0;
                                    $customer_type = $customer_data['revenue'] >= 10000000 ? 'VIP' : ($customer_data['revenue'] >= 5000000 ? 'Premium' : 'Thường');
                                ?>
                                    <tr>
                                        <td>
                                            <span class="rank-badge rank-<?= $rank <= 3 ? $rank : 'other' ?>">
                                                #<?= $rank ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="customer-info">
                                                <h4 class="customer-name"><?= htmlspecialchars($user['name']) ?></h4>
                                                <p class="customer-email"><?= htmlspecialchars($user['email']) ?></p>
                                                <span class="customer-role"><?= ucfirst($user['role']) ?></span>
                                            </div>
                                        </td>
                                        <td><?= $customer_data['orders'] ?></td>
                                        <td class="revenue-cell">
                                            <?= formatPrice($customer_data['revenue']) ?>
                                        </td>
                                        <td><?= formatPrice($avg_per_order) ?></td>
                                        <td>
                                            <span class="customer-type-badge type-<?= strtolower($customer_type) ?>">
                                                <?= $customer_type ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php 
                                    $rank++;
                                endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($tab == 'timeline'): ?>
            <!-- Timeline Tab -->
            <div class="timeline-tab">
                <!-- Daily Revenue Chart -->
                <div class="chart-section">
                    <div class="chart-container">
                        <div class="chart-header">
                            <h3 class="chart-title">Doanh Thu Theo Ngày</h3>
                        </div>
                        <div class="chart-body">
                            <canvas id="daily-revenue-chart" width="400" height="300"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Daily Details Table -->
                <div class="daily-details-section">
                    <h3 class="section-title">
                        <i class="fas fa-calendar-day"></i>
                        Chi Tiết Theo Ngày
                    </h3>
                    <div class="table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Ngày</th>
                                    <th width="100">Số đơn</th>
                                    <th width="150">Doanh thu</th>
                                    <th width="120">Đơn hàng TB</th>
                                    <th width="100">So với hôm trước</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($stats['by_date'])): ?>
                                    <tr>
                                        <td colspan="5" class="no-data">
                                            <i class="fas fa-inbox"></i>
                                            <p>Không có dữ liệu theo ngày</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php 
                                    $previous_revenue = 0;
                                    foreach ($stats['by_date'] as $date => $data): 
                                        $avg_per_order = $data['orders'] > 0 ? $data['revenue'] / $data['orders'] : 0;
                                        $change_percent = $previous_revenue > 0 ? (($data['revenue'] - $previous_revenue) / $previous_revenue) * 100 : 0;
                                    ?>
                                        <tr>
                                            <td>
                                                <strong><?= formatDate($date) ?></strong>
                                                <br>
                                                <small><?= date('l', strtotime($date)) ?></small>
                                            </td>
                                            <td><?= $data['orders'] ?></td>
                                            <td class="revenue-cell">
                                                <?= formatPrice($data['revenue']) ?>
                                            </td>
                                            <td><?= formatPrice($avg_per_order) ?></td>
                                            <td>
                                                <?php if ($previous_revenue > 0): ?>
                                                    <span class="change-indicator <?= $change_percent >= 0 ? 'positive' : 'negative' ?>">
                                                        <i class="fas fa-arrow-<?= $change_percent >= 0 ? 'up' : 'down' ?>"></i>
                                                        <?= number_format(abs($change_percent), 1) ?>%
                                                    </span>
                                                <?php else: ?>
                                                    <span class="change-indicator neutral">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php 
                                        $previous_revenue = $data['revenue'];
                                    endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Chart.js Scripts -->
<script>
<?php if ($tab == 'overview'): ?>
// Status Detail Chart
const statusDetailCtx = document.getElementById('status-detail-chart').getContext('2d');
const statusDetailChart = new Chart(statusDetailCtx, {
    type: 'bar',
    data: {
        labels: ['Hoàn thành', 'Đang xử lý', 'Chờ xử lý', 'Đã hủy'],
        datasets: [{
            label: 'Doanh thu (VNĐ)',
            data: [
                <?= $stats['by_status']['completed']['revenue'] ?>,
                <?= $stats['by_status']['processing']['revenue'] ?>,
                <?= $stats['by_status']['pending']['revenue'] ?>,
                <?= $stats['by_status']['cancelled']['revenue'] ?>
            ],
            backgroundColor: ['#10B981', '#3B82F6', '#F59E0B', '#EF4444'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return new Intl.NumberFormat('vi-VN').format(value) + ' VNĐ';
                    }
                }
            }
        }
    }
});
<?php endif; ?>

<?php if ($tab == 'timeline'): ?>
// Daily Revenue Chart
const dailyRevenueCtx = document.getElementById('daily-revenue-chart').getContext('2d');
const dailyRevenueChart = new Chart(dailyRevenueCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_map(function($date) { return date('d/m', strtotime($date)); }, array_keys($stats['by_date']))) ?>,
        datasets: [{
            label: 'Doanh thu (VNĐ)',
            data: <?= json_encode(array_values(array_map(function($data) { return $data['revenue']; }, $stats['by_date']))) ?>,
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
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return new Intl.NumberFormat('vi-VN').format(value) + ' VNĐ';
                    }
                }
            }
        }
    }
});
<?php endif; ?>

// Export detail report function
function exportDetailReport() {
    alert('Tính năng xuất báo cáo chi tiết sẽ được phát triển trong phiên bản tiếp theo.');
}
</script>