<?php
/**
 * Payment Success Page - Dynamic Version
 */

// 1. Khởi tạo View an toàn & ServiceManager
require_once __DIR__ . '/../../../core/view_init.php';

// Debug: Hiển thị lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Kiểm tra đăng nhập
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    header('Location: ?page=login');
    exit;
}

// Clear checkout session data after successful payment
unset($_SESSION['checkout_items'], $_SESSION['checkout_total'], $_SESSION['checkout_payment_method'], $_SESSION['checkout_session_time']);

// 2. Khởi tạo biến dữ liệu
$successData = [];
$order = null;
$orderItems = [];
$totalAmount = 0;
$showErrorMessage = false;
$errorMessage = '';

try {
    // Lấy order_id từ URL
    $orderId = $_GET['order_id'] ?? null;
    
    if (empty($orderId)) {
        // Không có order_id, chuyển về trang chủ
        header('Location: ./');
        exit;
    }
    
    // Thử load OrdersModel nếu có
    $order = null;
    $orderItems = [];
    $totalAmount = 0;
    $modelPath = __DIR__ . '/../../models/OrdersModel.php';
    
    if (file_exists($modelPath)) {
        require_once $modelPath;
        $ordersModel = new OrdersModel();
        $order = $ordersModel->findBy('order_number', $orderId);
        
        if ($order && isset($order['user_id']) && $order['user_id'] != $userId) {
            $order = null;
        }
        
        if ($order) {
            // Nếu đơn hàng đang ở trạng thái pending, cập nhật thành completed (bypass payment)
            if (isset($order['status']) && $order['status'] === 'pending') {
                try {
                    $ordersModel->update($order['id'], [
                        'status' => 'completed',
                        'payment_status' => 'paid'
                    ]);
                    // Reload để lấy dữ liệu mới
                    $order = $ordersModel->findBy('order_number', $orderId);
                } catch (Exception $e) {
                    error_log('Update order status error: ' . $e->getMessage());
                }
            }
            
            // Xóa sản phẩm khỏi giỏ hàng sau khi thanh toán
            try {
                require_once __DIR__ . '/../../models/CartModel.php';
                $cartModel = new CartModel();
                $cartModel->clearCart($userId);
            } catch (Exception $e) {
                error_log('Clear cart error: ' . $e->getMessage());
            }
            
            $itemsData = json_decode($order['items'] ?? '[]', true);
            $orderItems = $itemsData ?: [];
            $totalAmount = (float) ($order['total_amount'] ?? $order['total'] ?? 0);
        }
    } else {
        // OrdersModel không tồn tại, vẫn hiển thị trang thành công
        $order = [
            'order_number' => $orderId,
            'status' => 'completed',
            'payment_method' => 'sepay',
            'total' => $_SESSION['checkout_total'] ?? 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        $totalAmount = (float) $order['total'];
        
        // Xóa giỏ hàng
        try {
            require_once __DIR__ . '/../../models/CartModel.php';
            $cartModel = new CartModel();
            $cartModel->clearCart($userId);
        } catch (Exception $e) {
            // Ignore
        }
    }
    
} catch (Exception $e) {
    // Log error chi tiết
    error_log('Success Page Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' line ' . $e->getLine());
    
    if (isset($errorHandler)) {
        $result = $errorHandler->handleViewError($e, 'payment_success', ['order_id' => $orderId ?? null]);
        $showErrorMessage = true;
        $errorMessage = $result['message'] . ' (' . $e->getMessage() . ')';
    } else {
        $showErrorMessage = true;
        $errorMessage = 'Lỗi: ' . $e->getMessage();
    }
    
    // Set default values if order not found
    if (empty($order)) {
        $orderId = $orderId ?: 'ORD_' . bin2hex(random_bytes(4));
        $order = [
            'id' => $orderId,
            'status' => 'pending',
            'payment_method' => 'sepay',
            'total_amount' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
    }
    $totalAmount = $order['total_amount'] ?? 0;
}

// Format payment method
$paymentMethods = [
    'sepay' => 'SePay QR',
    'momo' => 'MoMo',
    'vnpay' => 'VNPay',
    'bank_transfer' => 'Chuyển khoản ngân hàng'
];
$paymentMethodName = $paymentMethods[$order['payment_method']] ?? 'Không xác định';

// Format status
$statusLabels = [
    'completed' => 'Đã thanh toán',
    'processing' => 'Đang xử lý',
    'pending' => 'Chờ thanh toán',
    'cancelled' => 'Đã hủy'
];
$statusLabel = $statusLabels[$order['status']] ?? 'Không xác định';
$statusColor = $order['status'] === 'completed' ? '#28a745' : '#ffc107';
$statusBg = $order['status'] === 'completed' ? '#d4edda' : '#fff3cd';
?>

<!-- Error Message -->
<?php if ($showErrorMessage): ?>
<div class="error-message" style="background: #f8d7da; color: #721c24; padding: 15px; margin: 20px 0; border-radius: 5px; text-align: center;">
    <strong>Thông báo:</strong> <?php echo htmlspecialchars($errorMessage); ?>
</div>
<?php endif; ?>

<section class="payment-section">
    <div class="container">
        <div class="success-box" style="text-align: center; max-width: 600px; margin: 0 auto;">
            <div class="success-animation-icon-container">
                <div class="success-checkmark">
                    <div class="check-icon">
                        <span class="icon-line line-tip"></span>
                        <span class="icon-line line-long"></span>
                        <div class="icon-circle"></div>
                        <div class="icon-fix"></div>
                    </div>
                </div>
            </div>

            <h1 class="checkout-title" style="margin-bottom: 10px;">
                <?php echo $order['status'] === 'completed' ? 'Thanh toán thành công!' : 'Đơn hàng đã được tạo!'; ?>
            </h1>
            <p style="color: #666; margin-bottom: 30px;">
                Cảm ơn bạn. Đơn hàng <strong>#<?php echo htmlspecialchars($orderId); ?></strong> 
                <?php echo $order['status'] === 'completed' ? 'đã được kích hoạt' : 'đang được xử lý'; ?>.
            </p>

            <table class="order-table">
                <tbody>
                    <tr>
                        <td><strong>Mã đơn hàng:</strong></td>
                        <td><?php echo htmlspecialchars($orderId); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Trạng thái:</strong></td>
                        <td>
                            <span style="color: <?php echo $statusColor; ?>; font-weight: bold; background: <?php echo $statusBg; ?>; padding: 5px 10px; border-radius: 15px;">
                                <?php echo $statusLabel; ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Phương thức:</strong></td>
                        <td><?php echo htmlspecialchars($paymentMethodName); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Tổng tiền:</strong></td>
                        <td><?php echo number_format($totalAmount, 0, ',', '.'); ?>đ</td>
                    </tr>
                    <?php if (isset($order['created_at'])): ?>
                    <tr>
                        <td><strong>Thời gian:</strong></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div style="margin-top: 30px; display: flex; gap: 10px; justify-content: center;">
                <a href="<?php echo page_url('home'); ?>" class="btn-place-order" style="text-decoration: none; background: #333;">Về trang chủ</a>
                <?php if ($order['status'] === 'completed'): ?>
                <a href="<?php echo page_url('users', 'dashboard'); ?>" class="btn-place-order" style="text-decoration: none;">Xem đơn hàng</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>