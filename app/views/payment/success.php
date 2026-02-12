<?php
/**
 * Payment Success Page - Dynamic Version
 */

// 1. Khởi tạo View an toàn & ServiceManager
require_once __DIR__ . '/../../../core/view_init.php';

// Chọn service phù hợp cho payment success (ưu tiên inject từ routing)
$service = isset($currentService) ? $currentService : ($publicService ?? null);

// 2. Khởi tạo biến dữ liệu
$successData = [];
$order = null;
$orderItems = [];
$totalAmount = 0;
$showErrorMessage = false;
$errorMessage = '';

try {
    // Get order information từ PublicService
    $orderId = $_GET['order_id'] ?? null;
    if ($service && method_exists($service, 'getPaymentSuccessData')) {
        $successData = $service->getPaymentSuccessData($orderId);
    } else {
        $successData = [];
    }
    
    $order = $successData['order'] ?? null;
    $orderItems = $successData['order_items'] ?? [];
    $totalAmount = $successData['total_amount'] ?? 0;
    
} catch (Exception $e) {
    if (isset($errorHandler)) {
        $result = $errorHandler->handleViewError($e, 'payment_success', ['order_id' => $orderId ?? null]);
        $showErrorMessage = true;
        $errorMessage = $result['message'];
    }
    
    // Fallback demo data
    $orderId = $orderId ?: 'DEMO' . rand(1000, 9999);
    $order = [
        'id' => $orderId,
        'status' => 'completed',
        'payment_method' => 'sepay',
        'total_amount' => 250000,
        'created_at' => date('Y-m-d H:i:s')
    ];
    $totalAmount = 250000;
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