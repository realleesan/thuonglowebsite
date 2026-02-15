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

// Find the specific order
$order = null;
foreach ($orders as $orderItem) {
    if ($orderItem['id'] === $orderId) {
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

// Mock additional order details
$orderDetails = [
    'customer_info' => [
        'name' => $user['name'] ?? 'Nguyễn Văn An',
        'email' => $user['email'] ?? 'nguyenvanan@example.com',
        'phone' => $user['phone'] ?? '0123456789',
        'address' => $user['address'] ?? '123 Đường ABC, Quận 1, TP.HCM'
    ],
    'order_timeline' => [
        [
            'status' => 'Đặt hàng',
            'date' => $order['date'],
            'description' => 'Đơn hàng được tạo thành công',
            'completed' => true
        ],
        [
            'status' => 'Xác nhận',
            'date' => date('Y-m-d H:i:s', strtotime($order['date'] . ' +30 minutes')),
            'description' => 'Đơn hàng đã được xác nhận',
            'completed' => true
        ],
        [
            'status' => 'Xử lý',
            'date' => date('Y-m-d H:i:s', strtotime($order['date'] . ' +2 hours')),
            'description' => $order['status'] === 'completed' ? 'Đơn hàng đang được xử lý' : 'Đang xử lý đơn hàng',
            'completed' => in_array($order['status'], ['processing', 'completed'])
        ],
        [
            'status' => 'Hoàn thành',
            'date' => $order['status'] === 'completed' ? date('Y-m-d H:i:s', strtotime($order['date'] . ' +1 day')) : null,
            'description' => 'Đơn hàng đã hoàn thành',
            'completed' => $order['status'] === 'completed'
        ]
    ],
    'payment_info' => [
        'subtotal' => $order['amount'],
        'discount' => 0,
        'tax' => 0,
        'total' => $order['amount'],
        'transaction_id' => 'TXN' . strtoupper(substr(md5($order['id']), 0, 8))
    ]
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
                <button onclick="window.print()" class="orders-btn orders-btn-secondary">
                    <i class="fas fa-print"></i>
                    In đơn hàng
                </button>
                
                <?php if ($order['status'] === 'processing' || $order['status'] === 'pending'): ?>
                <a href="?page=contact" class="orders-btn orders-btn-secondary">
                    <i class="fas fa-headset"></i>
                    Liên hệ hỗ trợ
                </a>
                <?php endif; ?>
                
                <?php if ($order['status'] === 'completed'): ?>
                <button onclick="handleReorder('<?php echo $order['id']; ?>')" class="orders-btn orders-btn-primary">
                    <i class="fas fa-redo"></i>
                    Đặt lại
                </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Order Status -->
        <div class="order-status-card">
            <div class="order-status-info">
                <div class="order-status-badge">
                    <span class="orders-badge orders-badge-<?php 
                        echo $order['status'] === 'completed' ? 'success' : 
                            ($order['status'] === 'processing' ? 'warning' : 
                            ($order['status'] === 'cancelled' ? 'danger' : 'info')); 
                    ?>">
                        <?php echo $statusLabels[$order['status']] ?? $order['status']; ?>
                    </span>
                </div>
                <div class="order-status-text">
                    <h3>
                        <?php 
                        switch($order['status']) {
                            case 'completed':
                                echo 'Đơn hàng đã hoàn thành';
                                break;
                            case 'processing':
                                echo 'Đơn hàng đang được xử lý';
                                break;
                            case 'pending':
                                echo 'Đơn hàng đang chờ xử lý';
                                break;
                            case 'cancelled':
                                echo 'Đơn hàng đã bị hủy';
                                break;
                            default:
                                echo 'Trạng thái không xác định';
                        }
                        ?>
                    </h3>
                    <p>
                        <?php 
                        switch($order['status']) {
                            case 'completed':
                                echo 'Cảm ơn bạn đã mua hàng. Đơn hàng của bạn đã được hoàn thành thành công.';
                                break;
                            case 'processing':
                                echo 'Chúng tôi đang xử lý đơn hàng của bạn. Bạn sẽ nhận được thông báo khi hoàn thành.';
                                break;
                            case 'pending':
                                echo 'Đơn hàng của bạn đang chờ được xác nhận và xử lý.';
                                break;
                            case 'cancelled':
                                echo 'Đơn hàng này đã bị hủy. Nếu có thắc mắc, vui lòng liên hệ hỗ trợ.';
                                break;
                        }
                        ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Order Content Grid -->
        <div class="order-detail-content">
            <!-- Order Information -->
            <div class="order-info-card">
                <div class="order-card-header">
                    <h3>Thông tin đơn hàng</h3>
                </div>
                <div class="order-card-content">
                    <div class="order-product-detail">
                        <div class="order-product-image">
                            <div class="order-product-placeholder">
                                <i class="fas fa-<?php 
                                    echo $order['type'] === 'data_nguon_hang' ? 'database' : 
                                        ($order['type'] === 'van_chuyen' ? 'truck' : 
                                        ($order['type'] === 'dich_vu_tt' ? 'credit-card' : 
                                        ($order['type'] === 'khoa_hoc' ? 'graduation-cap' : 'cog'))); 
                                ?>"></i>
                            </div>
                        </div>
                        <div class="order-product-info">
                            <h4><?php echo htmlspecialchars($order['product_name']); ?></h4>
                            <p class="order-product-category">
                                <i class="fas fa-tag"></i>
                                <?php echo $typeLabels[$order['type']] ?? $order['type']; ?>
                            </p>
                            <div class="order-product-price">
                                <?php echo number_format($order['amount'], 0, ',', '.'); ?> VNĐ
                            </div>
                        </div>
                    </div>
                    
                    <div class="order-details-grid">
                        <div class="order-detail-row">
                            <span class="order-detail-label">Mã đơn hàng:</span>
                            <span class="order-detail-value">#<?php echo htmlspecialchars($order['id']); ?></span>
                        </div>
                        
                        <div class="order-detail-row">
                            <span class="order-detail-label">Ngày đặt:</span>
                            <span class="order-detail-value"><?php echo date('d/m/Y H:i', strtotime($order['date'])); ?></span>
                        </div>
                        
                        <div class="order-detail-row">
                            <span class="order-detail-label">Phương thức thanh toán:</span>
                            <span class="order-detail-value"><?php echo $paymentLabels[$order['payment_method']] ?? $order['payment_method']; ?></span>
                        </div>
                        
                        <div class="order-detail-row">
                            <span class="order-detail-label">Mã giao dịch:</span>
                            <span class="order-detail-value"><?php echo $orderDetails['payment_info']['transaction_id']; ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="order-info-card">
                <div class="order-card-header">
                    <h3>Thông tin khách hàng</h3>
                </div>
                <div class="order-card-content">
                    <div class="customer-info-grid">
                        <div class="customer-info-item">
                            <div class="customer-info-label">Họ và tên:</div>
                            <div class="customer-info-value"><?php echo htmlspecialchars($orderDetails['customer_info']['name']); ?></div>
                        </div>
                        
                        <div class="customer-info-item">
                            <div class="customer-info-label">Email:</div>
                            <div class="customer-info-value"><?php echo htmlspecialchars($orderDetails['customer_info']['email']); ?></div>
                        </div>
                        
                        <div class="customer-info-item">
                            <div class="customer-info-label">Số điện thoại:</div>
                            <div class="customer-info-value"><?php echo htmlspecialchars($orderDetails['customer_info']['phone']); ?></div>
                        </div>
                        
                        <div class="customer-info-item">
                            <div class="customer-info-label">Địa chỉ:</div>
                            <div class="customer-info-value"><?php echo htmlspecialchars($orderDetails['customer_info']['address']); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Summary -->
            <div class="order-info-card">
                <div class="order-card-header">
                    <h3>Tóm tắt thanh toán</h3>
                </div>
                <div class="order-card-content">
                    <div class="payment-summary">
                        <div class="payment-row">
                            <span class="payment-label">Tạm tính:</span>
                            <span class="payment-value"><?php echo number_format($orderDetails['payment_info']['subtotal'], 0, ',', '.'); ?> VNĐ</span>
                        </div>
                        
                        <?php if ($orderDetails['payment_info']['discount'] > 0): ?>
                        <div class="payment-row">
                            <span class="payment-label">Giảm giá:</span>
                            <span class="payment-value payment-discount">-<?php echo number_format($orderDetails['payment_info']['discount'], 0, ',', '.'); ?> VNĐ</span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($orderDetails['payment_info']['tax'] > 0): ?>
                        <div class="payment-row">
                            <span class="payment-label">Thuế:</span>
                            <span class="payment-value"><?php echo number_format($orderDetails['payment_info']['tax'], 0, ',', '.'); ?> VNĐ</span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="payment-row payment-total">
                            <span class="payment-label">Tổng cộng:</span>
                            <span class="payment-value"><?php echo number_format($orderDetails['payment_info']['total'], 0, ',', '.'); ?> VNĐ</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Timeline -->
            <div class="order-info-card order-timeline-card">
                <div class="order-card-header">
                    <h3>Lịch sử đơn hàng</h3>
                </div>
                <div class="order-card-content">
                    <div class="order-timeline">
                        <?php foreach ($orderDetails['order_timeline'] as $index => $timeline): ?>
                        <div class="timeline-item <?php echo $timeline['completed'] ? 'timeline-completed' : 'timeline-pending'; ?>">
                            <div class="timeline-marker">
                                <?php if ($timeline['completed']): ?>
                                    <i class="fas fa-check"></i>
                                <?php else: ?>
                                    <i class="fas fa-clock"></i>
                                <?php endif; ?>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-title"><?php echo htmlspecialchars($timeline['status']); ?></div>
                                <div class="timeline-description"><?php echo htmlspecialchars($timeline['description']); ?></div>
                                <?php if ($timeline['date']): ?>
                                <div class="timeline-date"><?php echo date('d/m/Y H:i', strtotime($timeline['date'])); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="order-detail-footer">
            <?php if ($order['status'] === 'processing' || $order['status'] === 'pending'): ?>
            <a href="?page=users&module=orders&action=delete&id=<?php echo $order['id']; ?>" 
               class="orders-btn orders-btn-secondary"
               onclick="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này?')">
                <i class="fas fa-times"></i>
                Hủy đơn hàng
            </a>
            <?php endif; ?>
            
            <a href="?page=contact" class="orders-btn orders-btn-secondary">
                <i class="fas fa-headset"></i>
                Liên hệ hỗ trợ
            </a>
            
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