<?php
// User Orders Index - List All Orders
require_once __DIR__ . '/../../../services/UserService.php';

// Get current user from session
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    header('Location: ?page=login');
    exit;
}

// Get orders data from UserService
try {
    $userService = new UserService();
    $ordersData = $userService->getOrdersData($userId, 50);
    $orders = $ordersData['orders'] ?? [];
} catch (Exception $e) {
    $orders = [];
}

// Filter and search functionality
$statusFilter = $_GET['status'] ?? 'all';
$categoryFilter = $_GET['category'] ?? 'all';
$searchQuery = $_GET['search'] ?? '';

// Get unique categories from orders
$categories = [];
foreach ($orders as $order) {
    if (!empty($order['category_name']) && !in_array($order['category_name'], $categories)) {
        $categories[] = $order['category_name'];
    }
}
sort($categories);

// Apply filters
$filteredOrders = $orders;

if ($statusFilter !== 'all') {
    $filteredOrders = array_filter($filteredOrders, function($order) use ($statusFilter) {
        return $order['status'] === $statusFilter;
    });
}

if ($categoryFilter !== 'all') {
    $filteredOrders = array_filter($filteredOrders, function($order) use ($categoryFilter) {
        return ($order['category_name'] ?? '') === $categoryFilter;
    });
}

if (!empty($searchQuery)) {
    $filteredOrders = array_filter($filteredOrders, function($order) use ($searchQuery) {
        return stripos($order['product_name'], $searchQuery) !== false || 
               stripos($order['id'], $searchQuery) !== false;
    });
}

// Sort orders by date (newest first)
usort($filteredOrders, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

// Calculate statistics
$totalOrders = count($orders);
$completedOrders = count(array_filter($orders, function($order) { return $order['status'] === 'completed'; }));
// Processing includes both 'processing' and 'pending' statuses
$processingOrders = count(array_filter($orders, function($order) { return in_array($order['status'], ['processing', 'pending']); }));
$cancelledOrders = count(array_filter($orders, function($order) { return $order['status'] === 'cancelled'; }));
$totalSpent = array_sum(array_column(array_filter($orders, function($order) { return $order['status'] === 'completed'; }), 'amount'));

// Status and type mappings
$statusLabels = [
    'completed' => 'Hoàn thành',
    'processing' => 'Đang xử lý',
    'pending' => 'Chờ xử lý',
    'cancelled' => 'Đã hủy'
];

$typeLabels = [
    'data_nguon_hang' => 'Data Nguồn Hàng',
    'van_chuyen' => 'Vận Chuyển',
    'dich_vu_tt' => 'Dịch Vụ Thanh Toán',
    'danh_hang' => 'Đánh Hàng',
    'khoa_hoc' => 'Khóa Học',
    'tool' => 'Tool'
];

$paymentLabels = [
    'bank_transfer' => 'Chuyển khoản',
    'momo' => 'MoMo',
    'zalopay' => 'ZaloPay',
    'vnpay' => 'VNPay'
];
?>

<div class="user-content-with-sidebar">
    <!-- User Sidebar -->
    <?php include 'app/views/_layout/user_sidebar.php'; ?>
    
    <!-- Orders Content -->
    <div class="user-orders">
        <!-- Orders Header -->
        <div class="orders-header">
            <div class="orders-header-left">
                <h1>Quản lý đơn hàng</h1>
                <p>Theo dõi và quản lý tất cả đơn hàng của bạn</p>
            </div>
            <div class="orders-actions">
                <a href="?page=products" class="orders-btn orders-btn-primary">
                    <i class="fas fa-plus"></i>
                    Đặt hàng mới
                </a>
            </div>
        </div>

        <!-- Orders Statistics Compact -->
        <div class="orders-stats-compact">
            <div class="stat-item">
                <span class="stat-num"><?php echo $totalOrders; ?></span>
                <span class="stat-label">Tổng đơn</span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
                <span class="stat-num success"><?php echo $completedOrders; ?></span>
                <span class="stat-label">Hoàn thành</span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
                <span class="stat-num warning"><?php echo $processingOrders; ?></span>
                <span class="stat-label">Đang xử lý</span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
                <span class="stat-num danger"><?php echo $cancelledOrders; ?></span>
                <span class="stat-label">Đã hủy</span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item stat-money">
                <span class="stat-num info"><?php echo number_format($totalSpent, 0, ',', '.'); ?>đ</span>
                <span class="stat-label">Tổng chi tiêu</span>
            </div>
        </div>

        <!-- Orders Filters -->
        <div class="orders-filters">
            <div class="orders-filters-left">
                <div class="orders-search">
                    <i class="fas fa-search"></i>
                    <input type="text" 
                           placeholder="Tìm kiếm đơn hàng..." 
                           value="<?php echo htmlspecialchars($searchQuery); ?>"
                           id="ordersSearch">
                </div>
            </div>
            
            <div class="orders-filters-right">
                <select class="orders-filter-select" id="statusFilter" onchange="applyFilters()">
                    <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>Tất cả trạng thái</option>
                    <option value="completed" <?php echo $statusFilter === 'completed' ? 'selected' : ''; ?>>Hoàn thành</option>
                    <option value="processing" <?php echo $statusFilter === 'processing' ? 'selected' : ''; ?>>Đang xử lý</option>
                    <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Chờ xử lý</option>
                    <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                </select>

                <select class="orders-filter-select" id="categoryFilter" onchange="applyFilters()">
                    <option value="all" <?php echo $categoryFilter === 'all' ? 'selected' : ''; ?>>Tất cả danh mục</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $categoryFilter === $cat ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Orders List -->
        <div class="orders-list">
            <?php if (empty($filteredOrders)): ?>
                <div class="orders-empty">
                    <div class="orders-empty-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <h3>Không tìm thấy đơn hàng</h3>
                    <p>Không có đơn hàng nào phù hợp với bộ lọc của bạn</p>
                    <a href="?page=products" class="orders-btn orders-btn-primary">
                        Mua sắm ngay
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($filteredOrders as $order): ?>
                <div class="orders-item">
                    <div class="orders-item-header">
                        <div class="orders-item-id">
                            <strong>#<?php echo htmlspecialchars($order['id']); ?></strong>
                            <span class="orders-item-date"><?php echo date('d/m/Y', strtotime($order['date'])); ?></span>
                        </div>
                        <div class="orders-item-status">
                            <span class="orders-badge orders-badge-<?php 
                                echo $order['status'] === 'completed' ? 'success' : 
                                    ($order['status'] === 'processing' ? 'warning' : 
                                    ($order['status'] === 'cancelled' ? 'danger' : 'info')); 
                            ?>">
                                <?php echo $statusLabels[$order['status']] ?? $order['status']; ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="orders-item-body">
                        <!-- Left Column: Product Image -->
                        <div class="orders-item-image-col">
                            <?php if (!empty($order['product_image'])): ?>
                            <div class="orders-product-image">
                                <a href="?page=details&id=<?php echo (int)$order['product_id']; ?>">
                                    <img src="<?php echo htmlspecialchars($order['product_image']); ?>" alt="<?php echo htmlspecialchars($order['product_name']); ?>">
                                </a>
                            </div>
                            <?php else: ?>
                            <div class="orders-product-image">
                                <a href="?page=details&id=<?php echo (int)$order['product_id']; ?>">
                                    <div class="orders-product-placeholder">
                                        <i class="fas fa-<?php echo $order['type'] === 'data_nguon_hang' ? 'database' : ($order['type'] === 'van_chuyen' ? 'truck' : ($order['type'] === 'dich_vu_tt' ? 'credit-card' : ($order['type'] === 'khoa_hoc' ? 'graduation-cap' : 'cog'))); ?>"></i>
                                    </div>
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Right Column: All Info -->
                        <div class="orders-item-info-col">
                            <!-- Product Name -->
                            <h4 class="orders-product-name">
                                <a href="?page=details&id=<?php echo (int)$order['product_id']; ?>" class="product-name-link">
                                    <?php echo htmlspecialchars($order['product_name']); ?>
                                </a>
                            </h4>
                            
                            <!-- Category -->
                            <?php if (!empty($order['category_name'])): ?>
                            <p class="orders-product-category">
                                <i class="fas fa-folder"></i>
                                <?php echo htmlspecialchars($order['category_name']); ?>
                            </p>
                            <?php endif; ?>
                            
                            <!-- Quota & Expiry for completed orders -->
                            <?php if ($order['status'] === 'completed' && !empty($order['product_id'])): ?>
                                <?php if ($order['has_quota']): ?>
                                    <?php
                                        $quotaPercent = $order['quota_total'] > 0 ? round(($order['quota_remaining'] / $order['quota_total']) * 100) : 0;
                                        $quotaColor = $quotaPercent <= 20 ? '#dc3545' : ($quotaPercent <= 50 ? '#ff971a' : '#17a2b8');
                                    ?>
                                    <div class="orders-quota-row">
                                        <span class="quota-icon"><i class="fas fa-bolt"></i></span>
                                        <span class="quota-label">Quota:</span>
                                        <div class="quota-bar-small">
                                            <div class="quota-fill" style="width: <?php echo $quotaPercent; ?>%; background: <?php echo $quotaColor; ?>"></div>
                                        </div>
                                        <span class="quota-value"><?php echo $order['quota_remaining']; ?>/<?php echo $order['quota_total']; ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($order['expiry_date']): ?>
                                    <?php
                                        $daysLeft = $order['days_left'];
                                        $expiryColor = $daysLeft <= 0 ? '#dc3545' : ($daysLeft <= 7 ? '#ff971a' : '#28a745');
                                        $expiryText = $daysLeft < 0 ? 'Đã hết hạn' : ($daysLeft == 0 ? 'Hết hạn hôm nay' : 'Còn ' . $daysLeft . ' ngày');
                                    ?>
                                    <div class="orders-expiry-row" style="color: <?php echo $expiryColor; ?>">
                                        <i class="fas fa-clock"></i>
                                        <span><?php echo $expiryText; ?> (<?php echo date('d/m/Y', strtotime($order['expiry_date'])); ?>)</span>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <!-- Payment Info -->
                            <div class="orders-payment-row">
                                <div class="payment-item">
                                    <span class="payment-label">Số tiền:</span>
                                    <span class="payment-value orders-amount"><?php echo number_format($order['amount'], 0, ',', '.'); ?> VNĐ</span>
                                </div>
                                <div class="payment-item">
                                    <span class="payment-label">Thanh toán:</span>
                                    <span class="payment-value"><?php echo $paymentLabels[$order['payment_method']] ?? $order['payment_method']; ?></span>
                                </div>
                            </div>
                            
                            <!-- Actions -->
                            <div class="orders-item-actions-row">
                                <a href="?page=users&module=orders&action=view&id=<?php echo $order['id']; ?>" class="btn-detail">
                                    <i class="fas fa-eye"></i> Chi tiết
                                </a>
                                
                                <?php if ($order['status'] === 'completed' && !empty($order['product_id']) && $order['quota_remaining'] > 0 && !$order['is_expired']): ?>
                                <button class="btn-view-now" onclick="viewOrderData(<?php echo (int)$order['product_id']; ?>)">
                                    <i class="fas fa-eye"></i> Xem ngay
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Pagination (if needed) -->
        <?php if (count($filteredOrders) > 10): ?>
        <div class="orders-pagination">
            <button class="orders-pagination-btn orders-pagination-prev" disabled>
                <i class="fas fa-chevron-left"></i>
                Trước
            </button>
            
            <div class="orders-pagination-info">
                Trang 1 của 1
            </div>
            
            <button class="orders-pagination-btn orders-pagination-next" disabled>
                Sau
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Include Orders JavaScript -->
<script src="assets/js/user_orders.js"></script>
<script>
// Apply filters when select changes
function applyFilters() {
    const statusFilter = document.getElementById('statusFilter').value;
    const categoryFilter = document.getElementById('categoryFilter').value;
    
    // Build URL with filter parameters
    const url = new URL(window.location.href);
    url.searchParams.set('status', statusFilter);
    url.searchParams.set('category', categoryFilter);
    
    // Redirect to filtered page
    window.location.href = url.toString();
}

// Handle search input
document.getElementById('ordersSearch').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        const searchQuery = this.value;
        const url = new URL(window.location.href);
        url.searchParams.set('search', searchQuery);
        window.location.href = url.toString();
    }
});
</script>