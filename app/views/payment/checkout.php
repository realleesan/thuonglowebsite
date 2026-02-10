<?php
// Load Models
require_once __DIR__ . '/../../models/ProductsModel.php';
require_once __DIR__ . '/../../models/OrdersModel.php';

$productsModel = new ProductsModel();
$ordersModel = new OrdersModel();

// Get cart items from session or URL parameters
$cartItems = [];
$totalAmount = 0;

// Check if product_id is provided in URL
if (isset($_GET['product_id'])) {
    $productId = (int)$_GET['product_id'];
    $product = $productsModel->getById($productId);
    
    if ($product) {
        $cartItems[] = [
            'id' => $product['id'],
            'name' => $product['name'] ?? $product['title'] ?? 'Sản phẩm',
            'price' => $product['price'] ?? 0,
            'image' => $product['image'] ?? 'home/home-banner-top.png',
            'quantity' => 1
        ];
        $totalAmount = $product['price'] ?? 0;
    }
} else {
    // Get from session cart (if cart system exists)
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $product = $productsModel->getById($item['product_id']);
            if ($product) {
                $cartItems[] = [
                    'id' => $product['id'],
                    'name' => $product['name'] ?? $product['title'] ?? 'Sản phẩm',
                    'price' => $product['price'] ?? 0,
                    'image' => $product['image'] ?? 'home/home-banner-top.png',
                    'quantity' => $item['quantity'] ?? 1
                ];
                $totalAmount += ($product['price'] ?? 0) * ($item['quantity'] ?? 1);
            }
        }
    }
}

// Fallback demo data if no items
if (empty($cartItems)) {
    $cartItems[] = [
        'id' => 1,
        'name' => 'Khóa học: Lập trình Web Fullstack (Demo)',
        'price' => 250000,
        'image' => 'home/home-banner-top.png',
        'quantity' => 1
    ];
    $totalAmount = 250000;
}
?>

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