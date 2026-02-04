<?php
// Professional Orders Management  
$page_title = "Quản lý Đơn hàng";
$breadcrumb = [
    ['text' => 'Dashboard', 'url' => '?page=admin&module=dashboard'],
    ['text' => 'Đơn hàng', 'url' => null]
];

// Mock orders data
$orders = [
    [
        'id' => 'ORD001',
        'customer_name' => 'Nguyễn Văn A',
        'customer_email' => 'nguyenvana@example.com',
        'product' => 'Khóa học Web Development',
        'amount' => 1500000,
        'status' => 'completed',
        'payment_method' => 'bank_transfer',
        'created_at' => '2024-02-04 10:30:00'
    ],
    [
        'id' => 'ORD002',
        'customer_name' => 'Trần Thị B',
        'customer_email' => 'tranthib@example.com',
        'product' => 'Khóa học UI/UX Design',
        'amount' => 2000000,
        'status' => 'pending',
        'payment_method' => 'momo',
        'created_at' => '2024-02-04 09:15:00'
    ],
    [
        'id' => 'ORD003',
        'customer_name' => 'Lê Văn C',
        'customer_email' => 'levanc@example.com',
        'product' => 'Khóa học Mobile App',
        'amount' => 1800000,
        'status' => 'processing',
        'payment_method' => 'vnpay',
        'created_at' => '2024-02-03 16:20:00'
    ]
];

// Get filter parameters
$filterStatus = $_GET['status'] ?? '';
$searchQuery = $_GET['search'] ?? '';

// Apply filters
$filteredOrders = $orders;

if ($searchQuery) {
    $filteredOrders = array_filter($filteredOrders, function($o) use ($searchQuery) {
        return stripos($o['id'], $searchQuery) !== false || 
               stripos($o['customer_name'], $searchQuery) !== false ||
               stripos($o['customer_email'], $searchQuery) !== false;
    });
}

if ($filterStatus) {
    $filteredOrders = array_filter($filteredOrders, function($o) use ($filterStatus) {
        return $o['status'] === $filterStatus;
    });
}

// Stats
$stats = [
    'total' => count($orders),
    'completed' => count(array_filter($orders, function($o) { return $o['status'] === 'completed'; })),
    'pending' => count(array_filter($orders, function($o) { return $o['status'] === 'pending'; })),
    'revenue' => array_sum(array_column(array_filter($orders, function($o) { return $o['status'] === 'completed'; }), 'amount'))
];
?>

<div class="admin-page-header">
    <div class="page-header-left">
        <h1><?php echo $page_title; ?></h1>
        <div class="admin-breadcrumb">
            <?php foreach ($breadcrumb as $index => $crumb): ?>
                <?php if ($crumb['url']): ?>
                    <a href="<?php echo $crumb['url']; ?>"><?php echo $crumb['text']; ?></a>
                <?php else: ?>
                    <span class="current"><?php echo $crumb['text']; ?></span>
                <?php endif; ?>
                <?php if ($index < count($breadcrumb) - 1): ?>
                    <span class="delimiter">/</span>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Stats Summary -->
<div class="admin-stats-summary">
    <div class="stat-item">
        <span class="stat-label">Tổng đơn hàng:</span>
        <span class="stat-value"><?php echo $stats['total']; ?></span>
    </div>
    <div class="stat-item">
        <span class="stat-label">Hoàn thành:</span>
        <span class="stat-value text-success"><?php echo $stats['completed']; ?></span>
    </div>
    <div class="stat-item">
        <span class="stat-label">Chờ xử lý:</span>
        <span class="stat-value" style="color: #F59E0B;"><?php echo $stats['pending']; ?></span>
    </div>
    <div class="stat-item">
        <span class="stat-label">Doanh thu:</span>
        <span class="stat-value text-success"><?php echo number_format($stats['revenue'], 0, ',', '.'); ?> ₫</span>
    </div>
</div>

<!-- Filters -->
<div class="admin-filters-bar">
    <form method="GET" action="" class="filters-form">
        <input type="hidden" name="page" value="admin">
        <input type="hidden" name="module" value="orders">
        
        <div class="filter-search">
            <i class="fas fa-search"></i>
            <input type="text" name="search" placeholder="Tìm kiếm đơn hàng..." 
                   value="<?php echo htmlspecialchars($searchQuery); ?>" class="search-input">
        </div>
        
        <div class="filter-group">
            <select name="status" class="filter-select">
                <option value="">Tất cả trạng thái</option>
                <option value="pending" <?php echo $filterStatus === 'pending' ? 'selected' : ''; ?>>Chờ xử lý</option>
                <option value="processing" <?php echo $filterStatus === 'processing' ? 'selected' : ''; ?>>Đang xử lý</option>
                <option value="completed" <?php echo $filterStatus === 'completed' ? 'selected' : ''; ?>>Hoàn thành</option>
                <option value="cancelled" <?php echo $filterStatus === 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
            </select>
        </div>
        
        <button type="submit" class="admin-btn admin-btn-primary">
            <i class="fas fa-filter"></i> Lọc
        </button>
        
        <?php if ($searchQuery || $filterStatus): ?>
        <a href="?page=admin&module=orders" class="admin-btn admin-btn-secondary">
            <i class="fas fa-times"></i> Xóa bộ lọc
        </a>
        <?php endif; ?>
    </form>
</div>

<div class="admin-card">
    <div class="admin-card-body">
        <?php if (empty($filteredOrders)): ?>
            <div class="admin-empty-state">
                <i class="fas fa-shopping-cart" style="font-size: 48px; color: #9CA3AF; margin-bottom: 16px;"></i>
                <h3>Không tìm thấy đơn hàng</h3>
                <p>Thử thay đổi bộ lọc hoặc chờ đơn hàng mới</p>
            </div>
        <?php else: ?>
            <div class="admin-table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Khách hàng</th>
                            <th>Sản phẩm</th>
                            <th width="120">Số tiền</th>
                            <th width="120">Thanh toán</th>
                            <th width="120">Trạng thái</th>
                            <th width="150">Ngày đặt</th>
                            <th width="100">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($filteredOrders as $order): ?>
                        <tr>
                            <td><strong><?php echo $order['id']; ?></strong></td>
                            <td>
                                <div class="customer-info">
                                    <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong>
                                    <small><?php echo htmlspecialchars($order['customer_email']); ?></small>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($order['product']); ?></td>
                            <td><strong><?php echo number_format($order['amount'], 0, ',', '.'); ?> ₫</strong></td>
                            <td>
                                <?php
                                $paymentLabels = [
                                    'bank_transfer' => 'Chuyển khoản',
                                    'momo' => 'MoMo',
                                    'vnpay' => 'VNPay',
                                    'cash' => 'Tiền mặt'
                                ];
                                echo $paymentLabels[$order['payment_method']] ?? $order['payment_method'];
                                ?>
                            </td>
                            <td>
                                <?php
                                $statusClass = [
                                    'pending' => 'admin-badge-warning',
                                    'processing' => 'admin-badge-info',
                                    'completed' => 'admin-badge-success',
                                    'cancelled' => 'admin-badge-danger'
                                ];
                                $statusLabels = [
                                    'pending' => 'Chờ xử lý',
                                    'processing' => 'Đang xử lý',
                                    'completed' => 'Hoàn thành',
                                    'cancelled' => 'Đã hủy'
                                ];
                                ?>
                                <span class="admin-badge <?php echo $statusClass[$order['status']]; ?>">
                                    <?php echo $statusLabels[$order['status']]; ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                            <td class="admin-actions">
                                <a href="?page=admin&module=orders&action=view&id=<?php echo $order['id']; ?>" 
                                   class="admin-btn admin-btn-sm admin-btn-info" title="Xem">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.customer-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.customer-info strong {
    color: #1F2937;
    font-size: 14px;
    font-weight: 600;
}

.customer-info small {
    color: #6B7280;
    font-size: 12px;
}
</style>
