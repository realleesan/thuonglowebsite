<?php
/**
 * Checkout Page - Dynamic Version
 */

// 1. Khởi tạo View an toàn & ServiceManager
require_once __DIR__ . '/../../../core/view_init.php';

// Chọn service phù hợp cho checkout (ưu tiên inject từ routing)
$service = isset($currentService) ? $currentService : ($publicService ?? null);

// 2. Khởi tạo biến dữ liệu
$checkoutData = [];
$cartItems = [];
$totalAmount = 0;
$showErrorMessage = false;
$errorMessage = '';

try {
    // Get checkout data từ PublicService
    $productId = $_GET['product_id'] ?? null;
    if ($service && method_exists($service, 'getCheckoutData')) {
        $checkoutData = $service->getCheckoutData($productId);
    } else {
        $checkoutData = [];
    }
    
    $cartItems = $checkoutData['cart_items'] ?? [];
    $totalAmount = $checkoutData['total_amount'] ?? 0;
    
} catch (Exception $e) {
    if (isset($errorHandler)) {
        $result = $errorHandler->handleViewError($e, 'checkout', ['product_id' => $productId ?? null]);
        $showErrorMessage = true;
        $errorMessage = $result['message'];
    }
    
    // Fallback demo data
    $cartItems = [[
        'id' => 1,
        'name' => 'Khóa học: Lập trình Web Fullstack (Demo)',
        'price' => 250000,
        'image' => 'home/home-banner-top.png',
        'quantity' => 1
    ]];
    $totalAmount = 250000;
}
?>

<!-- Error Message -->
<?php if ($showErrorMessage): ?>
<div class="error-message" style="background: #f8d7da; color: #721c24; padding: 15px; margin: 20px 0; border-radius: 5px; text-align: center;">
    <strong>Thông báo:</strong> <?php echo htmlspecialchars($errorMessage); ?>
</div>
<?php endif; ?>

<section class="payment-section">
    <div class="container">
        <h1 class="checkout-title">Thanh toán khóa học</h1>

        <div class="checkout-wrap">
            <h3 class="mb-3">Đơn hàng của bạn</h3>

            <form action="<?php echo form_url('payment'); ?>" method="POST">
                <table class="order-table">
                    <thead>
                        <tr>
                            <th style="width: 70%;">Sản phẩm</th>
                            <th style="width: 30%;">Tạm tính</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): ?>
                        <tr>
                            <td class="product-name">
                                <img src="<?php echo img_url($item['image']); ?>" style="width: 30px; margin-right: 10px; vertical-align: middle;">
                                <?php echo htmlspecialchars($item['name']); ?>
                                <?php if ($item['quantity'] > 1): ?>
                                    <span class="quantity"> × <?php echo $item['quantity']; ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="amount"><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>đ</td>
                        </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td><strong>Tổng cộng</strong></td>
                            <td class="amount" style="font-size: 18px; color: #d32f2f;"><?php echo number_format($totalAmount, 0, ',', '.'); ?>đ</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Hidden fields for order processing -->
                <?php foreach ($cartItems as $item): ?>
                <input type="hidden" name="items[<?php echo $item['id']; ?>][product_id]" value="<?php echo $item['id']; ?>">
                <input type="hidden" name="items[<?php echo $item['id']; ?>][quantity]" value="<?php echo $item['quantity']; ?>">
                <input type="hidden" name="items[<?php echo $item['id']; ?>][price]" value="<?php echo $item['price']; ?>">
                <?php endforeach; ?>
                <input type="hidden" name="total_amount" value="<?php echo $totalAmount; ?>">

                <h3 class="mb-3">Phương thức thanh toán</h3>
                <div class="payment-method-box">
                    <input type="radio" name="payment_method" value="sepay" checked id="pm_sepay">
                    <label for="pm_sepay">Chuyển khoản QR (SePay) </label>
                </div>
                <div class="payment-note">
                    <i class="fas fa-info-circle"></i> Đây là đơn hàng mô phỏng. Không cần thanh toán thật.
                </div>
                <button type="submit" class="btn-place-order">Đặt hàng ngay</button>
            </form>
        </div>
    </div>
</section>