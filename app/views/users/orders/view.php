<?php
// User Orders View - Order Details
require_once __DIR__ . '/../../../services/UserService.php';

// Get current user from session
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    header('Location: ?page=login');
    exit;
}

// Get order ID from URL
$orderId = $_GET['id'] ?? '';

// Get orders data from UserService
try {
    $userService = new UserService();
    $ordersData = $userService->getOrdersData($userId, 100);
    $orders = $ordersData['orders'] ?? [];
    $accountData = $userService->getAccountData($userId);
    $user = $accountData['user'] ?? [];
} catch (Exception $e) {
    $orders = [];
    $user = [];
}

// Find the specific order - convert both to int for comparison
$order = null;
$orderIdInt = (int) $orderId;
foreach ($orders as $orderItem) {
    if ((int)$orderItem['id'] === $orderIdInt) {
        $order = $orderItem;
        break;
    }
}

// Redirect if order not found
if (!$order) {
    header('Location: ?page=users&module=orders');
    exit;
}

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
    'bank_transfer' => 'Chuyển khoản ngân hàng',
    'momo' => 'Ví MoMo',
    'zalopay' => 'ZaloPay',
    'vnpay' => 'VNPay'
];
?>

<div class="user-content-with-sidebar">
    <!-- User Sidebar -->
    <?php include 'app/views/_layout/user_sidebar.php'; ?>
    
    <!-- Order Details Content -->
    <div class="user-orders">
        <!-- Order Header -->
        <div class="order-detail-header">
            <div class="order-detail-header-left">
                <div class="order-breadcrumb">
                    <a href="?page=users&module=orders">Đơn hàng</a>
                    <i class="fas fa-chevron-right"></i>
                    <span>Chi tiết đơn hàng #<?php echo htmlspecialchars($order['id']); ?></span>
                </div>
                <h1>Đơn hàng #<?php echo htmlspecialchars($order['id']); ?></h1>
                <p>Đặt hàng ngày <?php echo date('d/m/Y H:i', strtotime($order['date'])); ?></p>
            </div>
            <div class="order-detail-actions">
                
                <?php if ($order['status'] === 'processing' || $order['status'] === 'pending'): ?>
                <?php endif; ?>
                
                <?php if ($order['status'] === 'completed'): ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Order Detail Card -->
        <div class="order-detail-main-card">
            <!-- Header with Status -->
            <div class="order-detail-card-header">
                <div class="order-detail-title">
                    <h3>Thông tin đơn hàng</h3>
                    <span class="orders-badge orders-badge-<?php 
                        echo $order['status'] === 'completed' ? 'success' : 
                            ($order['status'] === 'processing' ? 'warning' : 
                            ($order['status'] === 'cancelled' ? 'danger' : 'info')); 
                    ?>">
                        <?php echo $statusLabels[$order['status']] ?? $order['status']; ?>
                    </span>
                </div>
                <p class="order-status-desc">
                    <?php 
                    switch($order['status']) {
                        case 'completed': echo 'Đơn hàng đã hoàn thành. Cảm ơn bạn đã mua hàng!'; break;
                        case 'processing': echo 'Đơn hàng đang được xử lý.'; break;
                        case 'pending': echo 'Đơn hàng đang chờ xử lý.'; break;
                        case 'cancelled': echo 'Đơn hàng đã bị hủy.'; break;
                    }
                    ?>
                </p>
            </div>
            
            <div class="order-detail-card-body">
                <!-- Product with Actions -->
                <div class="order-product-full">
                    <div class="order-product-main">
                        <div class="order-product-img-wrap">
                            <?php 
                                $productImg = !empty($order['product_image']) ? htmlspecialchars($order['product_image']) : base_url() . 'assets/images/home/no-image.png';
                            ?>
                            <img src="<?php echo $productImg; ?>" alt="<?php echo htmlspecialchars($order['product_name']); ?>" onerror="this.src='<?php echo base_url(); ?>assets/images/home/no-image.png'">
                        </div>
                        <div class="order-product-details">
                            <h4 class="order-product-name"><?php echo htmlspecialchars($order['product_name']); ?></h4>
                            <div class="order-product-meta" style="display: flex; gap: 15px; font-size: 0.85rem; color: #666; margin-top: 5px;">
                                <?php if (!empty($order['category_name'])): ?>
                                <span class="order-product-cat"><i class="fas fa-folder"></i> <?php echo htmlspecialchars($order['category_name']); ?></span>
                                <?php endif; ?>
                                <span class="order-product-qty"><i class="fas fa-shopping-basket"></i> Số lượng: <?php echo $order['quantity'] ?? 1; ?></span>
                            </div>
                        </div>
                        <div class="order-product-amount"><?php echo number_format($order['amount'], 0, ',', '.'); ?> VNĐ</div>
                    </div>
                    
                    <!-- Product Actions Row -->
                    <?php if ($order['status'] === 'completed' && !empty($order['product_id']) && ($order['quota_remaining'] ?? 0) > 0 && !($order['is_expired'] ?? false)): ?>
                    <div class="order-product-actions-row">
                        <button class="btn-action btn-view-data" onclick="viewOrderData(<?php echo (int)$order['product_id']; ?>)">
                            <i class="fas fa-eye"></i> Xem dữ liệu
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Order & Customer Info -->
                <div class="order-meta-section">
                    <div class="order-meta-row">
                        <div class="meta-item">
                            <span class="meta-label">Mã đơn:</span>
                            <span class="meta-value">#<?php echo htmlspecialchars($order['id']); ?></span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Ngày đặt:</span>
                            <span class="meta-value"><?php echo date('d/m/Y H:i', strtotime($order['date'])); ?></span>
                        </div>
                    </div>
                    <div class="order-meta-row">
                        <div class="meta-item">
                            <span class="meta-label">Thanh toán:</span>
                            <span class="meta-value"><?php echo $paymentLabels[$order['payment_method']] ?? $order['payment_method']; ?></span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Mã GD:</span>
                            <span class="meta-value"><?php echo $order['order_number'] ?? 'N/A'; ?></span>
                        </div>
                    </div>
                    <div class="order-meta-row">
                        <div class="meta-item">
                            <span class="meta-label">Khách hàng:</span>
                            <span class="meta-value"><?php echo htmlspecialchars($user['fullname'] ?? $user['name'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Email:</span>
                            <span class="meta-value"><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></span>
                        </div>
                    </div>
                    <div class="order-meta-row">
                        <div class="meta-item">
                            <span class="meta-label">SĐT:</span>
                            <span class="meta-value"><?php echo !empty($user['phone']) ? htmlspecialchars($user['phone']) : 'N/A'; ?></span>
                        </div>
                        
                    </div>
                </div>
                
                <!-- Payment Summary -->
                <div class="order-payment-box">
                    <div class="payment-line">
                        <span class="pay-label">Tạm tính:</span>
                        <span class="pay-value"><?php echo number_format($order['amount'], 0, ',', '.'); ?> VNĐ</span>
                    </div>
                    <?php if (($order['discount'] ?? 0) > 0): ?>
                    <div class="payment-line">
                        <span class="pay-label">Giảm giá:</span>
                        <span class="pay-value discount">-<?php echo number_format($order['discount'], 0, ',', '.'); ?> VNĐ</span>
                    </div>
                    <?php endif; ?>
                    <div class="payment-line total-line">
                        <span class="pay-label">Tổng cộng:</span>
                        <span class="pay-value total-amount"><?php echo number_format($order['amount'], 0, ',', '.'); ?> VNĐ</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="order-detail-footer">
            <?php if ($order['status'] === 'processing' || $order['status'] === 'pending'): ?>
            <a href="?page=users&module=orders&action=delete&id=<?php echo $order['id']; ?>" 
               class="orders-btn orders-btn-secondary">
                <i class="fas fa-times"></i>
                Hủy đơn hàng
            </a>
            <?php endif; ?>
            
            <a href="?page=users&module=orders" class="orders-btn orders-btn-primary">
                <i class="fas fa-arrow-left"></i>
                Quay lại danh sách
            </a>
        </div>
    </div>
</div>

<!-- Include Orders JavaScript -->
<script src="assets/js/user_orders.js"></script>
<script>
// Handle reorder functionality
function handleReorder(orderId) {
    if (confirm('Bạn có muốn thêm sản phẩm này vào giỏ hàng?')) {
        // Show loading state
        const button = event.target.closest('button');
        const originalContent = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
        button.disabled = true;
        
        // Simulate API call
        setTimeout(() => {
            alert('Đã thêm sản phẩm vào giỏ hàng thành công!');
            
            // Reset button
            button.innerHTML = originalContent;
            button.disabled = false;
            
            // Redirect to cart
            window.location.href = '?page=users&module=cart';
        }, 1000);
    }
}
</script>