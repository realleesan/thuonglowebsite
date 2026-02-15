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
$typeFilter = $_GET['type'] ?? 'all';
$searchQuery = $_GET['search'] ?? '';

// Apply filters
$filteredOrders = $orders;

if ($statusFilter !== 'all') {
    $filteredOrders = array_filter($filteredOrders, function($order) use ($statusFilter) {
        return $order['status'] === $statusFilter;
    });
}

if ($typeFilter !== 'all') {
    $filteredOrders = array_filter($filteredOrders, function($order) use ($typeFilter) {
        return ($order['type'] ?? 'data_nguon_hang') === $typeFilter;
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
$processingOrders = count(array_filter($orders, function($order) { return $order['status'] === 'processing'; }));
$totalSpent = array_sum(array_column($orders, 'amount'));

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

        <!-- Orders Statistics -->
        <div class="orders-stats">
            <div class="orders-stat-card">
                <div class="orders-stat-icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="orders-stat-content">
                    <h3><?php echo $totalOrders; ?></h3>
                    <p>Tổng đơn hàng</p>
                </div>
            </div>
            
            <div class="orders-stat-card">
                <div class="orders-stat-icon orders-stat-icon-success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="orders-stat-content">
                    <h3><?php echo $completedOrders; ?></h3>
                    <p>Đã hoàn thành</p>
                </div>
            </div>
            
            <div class="orders-stat-card">
                <div class="orders-stat-icon orders-stat-icon-warning">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="orders-stat-content">
                    <h3><?php echo $processingOrders; ?></h3>
                    <p>Đang xử lý</p>
                </div>
            </div>
            
            <div class="orders-stat-card">
                <div class="orders-stat-icon orders-stat-icon-info">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="orders-stat-content">
                    <h3><?php echo number_format($totalSpent / 1000000, 1); ?>M</h3>
                    <p>Tổng chi tiêu</p>
                </div>
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
                <select class="orders-filter-select" id="statusFilter">
                    <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>Tất cả trạng thái</option>
                    <option value="completed" <?php echo $statusFilter === 'completed' ? 'selected' : ''; ?>>Hoàn thành</option>
                    <option value="processing" <?php echo $statusFilter === 'processing' ? 'selected' : ''; ?>>Đang xử lý</option>
                    <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Chờ xử lý</option>
                    <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                </select>
                
                <select class="orders-filter-select" id="typeFilter">
                    <option value="all" <?php echo $typeFilter === 'all' ? 'selected' : ''; ?>>Tất cả loại</option>
                    <option value="data_nguon_hang" <?php echo $typeFilter === 'data_nguon_hang' ? 'selected' : ''; ?>>Data Nguồn Hàng</option>
                    <option value="van_chuyen" <?php echo $typeFilter === 'van_chuyen' ? 'selected' : ''; ?>>Vận Chuyển</option>
                    <option value="dich_vu_tt" <?php echo $typeFilter === 'dich_vu_tt' ? 'selected' : ''; ?>>Dịch Vụ TT</option>
                    <option value="danh_hang" <?php echo $typeFilter === 'danh_hang' ? 'selected' : ''; ?>>Đánh Hàng</option>
                    <option value="khoa_hoc" <?php echo $typeFilter === 'khoa_hoc' ? 'selected' : ''; ?>>Khóa Học</option>
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
                    
                    <div class="orders-item-content">
                        <div class="orders-item-product">
                            <div class="orders-product-info">
                                <h4><?php echo htmlspecialchars($order['product_name']); ?></h4>
                                <p class="orders-product-type">
                                    <i class="fas fa-tag"></i>
                                    <?php echo $typeLabels[$order['type']] ?? $order['type']; ?>
                                </p>
                            </div>
                        </div>
                        
                        <div class="orders-item-details">
                            <div class="orders-detail-item">
                                <span class="orders-detail-label">Số tiền:</span>
                                <span class="orders-detail-value orders-amount">
                                    <?php echo number_format($order['amount'], 0, ',', '.'); ?> VNĐ
                                </span>
                            </div>
                            
                            <div class="orders-detail-item">
                                <span class="orders-detail-label">Thanh toán:</span>
                                <span class="orders-detail-value">
                                    <?php echo $paymentLabels[$order['payment_method']] ?? $order['payment_method']; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="orders-item-actions">
                        <a href="?page=users&module=orders&action=view&id=<?php echo $order['id']; ?>" 
                           class="orders-action-btn orders-action-view">
                            <i class="fas fa-eye"></i>
                            Chi tiết
                        </a>
                        
                        <?php if ($order['status'] === 'processing' || $order['status'] === 'pending'): ?>
                        <a href="?page=users&module=orders&action=delete&id=<?php echo $order['id']; ?>" 
                           class="orders-action-btn orders-action-delete"
                           onclick="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này?')">
                            <i class="fas fa-times"></i>
                            Hủy đơn hàng
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($order['status'] === 'processing'): ?>
                        <a href="?page=contact" class="orders-action-btn orders-action-support">
                            <i class="fas fa-headset"></i>
                            Hỗ trợ
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($order['status'] === 'completed'): ?>
                        <a href="#" class="orders-action-btn orders-action-reorder">
                            <i class="fas fa-redo"></i>
                            Đặt lại
                        </a>
                        <?php endif; ?>
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