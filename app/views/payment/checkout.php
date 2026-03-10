<?php
/**
 * Checkout Page - Dynamic Version
 */

// 1. Khởi tạo View an toàn & ServiceManager
require_once __DIR__ . '/../../../core/view_init.php';

// Prevent caching to ensure fresh checkout data
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// Kiểm tra đăng nhập - user phải đăng nhập mới thanh toán được
$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    // Lưu URL hiện tại để redirect sau khi login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '?page=checkout';
    header('Location: ?page=login');
    exit;
}

// 2. Khởi tạo biến dữ liệu
$checkoutData = [];
$cartItems = [];
$totalAmount = 0;
$showErrorMessage = false;
$errorMessage = '';

try {
    // Sử dụng global userService từ view_init.php
    global $userService;
    
    // Nếu chưa có, khởi tạo UserService
    if (!isset($userService)) {
        if (!class_exists('UserService')) {
            require_once __DIR__ . '/../../services/UserService.php';
        }
        $userService = new UserService();
    }
    
    if (!class_exists('ProductsModel')) {
        require_once __DIR__ . '/../../models/ProductsModel.php';
    }
    
    // Lấy tất cả giỏ hàng của user
    $cartData = $userService->getCartData($userId);
    $allCartItems = $cartData['items'] ?? [];
    
    // Xử lý selected_items từ JavaScript (dạng "1,2,3")
    $selectedItemsParam = $_GET['selected_items'] ?? '';
    $selectedItemIds = [];
    
    if (!empty($selectedItemsParam)) {
        // Chuyển đổi chuỗi "1,2,3" thành mảng [1, 2, 3]
        $selectedItemIds = array_map('intval', explode(',', $selectedItemsParam));
        $selectedItemIds = array_filter($selectedItemIds, function($id) { return $id > 0; });
        $selectedItemIds = array_values($selectedItemIds);
    }
    
    // Xử lý product_id (mua trực tiếp 1 sản phẩm)
    $productId = $_GET['product_id'] ?? null;
    
    if (!empty($selectedItemIds)) {
        // Lọc các sản phẩm được chọn trong giỏ hàng
        $cartItems = array_filter($allCartItems, function($item) use ($selectedItemIds) {
            return in_array((int)$item['id'], $selectedItemIds);
        });
        $cartItems = array_values($cartItems);
    } elseif ($productId) {
        // Mua trực tiếp 1 sản phẩm (từ trang chi tiết sản phẩm)
        $productModel = new ProductsModel();
        $product = $productModel->find($productId);
        if ($product) {
            $price = (float) ($product['sale_price'] ?? $product['price'] ?? 0);
            $cartItems = [[
                'id' => $product['id'],
                'product_id' => $product['id'],
                'name' => $product['name'],
                'price' => $price,
                'original_price' => $product['original_price'] ?? $price,
                'image' => $product['image'] ?? '',
                'quantity' => 1,
                'subtotal' => $price
            ]];
        }
    } else {
        // Không có sản phẩm nào được chọn - lấy tất cả giỏ hàng
        $cartItems = $allCartItems;
    }
    
    // Tính tổng tiền
    $totalAmount = 0;
    foreach ($cartItems as $item) {
        $price = $item['price'] ?? 0;
        $quantity = $item['quantity'] ?? 1;
        $totalAmount += $price * $quantity;
    }
    
    // Generate unique checkout token to prevent form caching issues
    $checkoutToken = bin2hex(random_bytes(16));
    
    // Nếu không có sản phẩm nào, chuyển về giỏ hàng
    if (empty($cartItems)) {
        header('Location: ?page=users&module=cart');
        exit;
    }
    
    $checkoutData = [
        'cart_items' => $cartItems,
        'total_amount' => $totalAmount
    ];
    
} catch (Exception $e) {
    // Log error chi tiết
    error_log('Checkout Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' line ' . $e->getLine());
    
    if (isset($errorHandler)) {
        $result = $errorHandler->handleViewError($e, 'checkout', [
            'selected_items' => $selectedItemsParam ?? null, 
            'product_id' => $productId ?? null,
            'user_id' => $userId ?? null
        ]);
        $showErrorMessage = true;
        $errorMessage = $result['message'] . ' (' . $e->getMessage() . ')';
    } else {
        $showErrorMessage = true;
        $errorMessage = 'Lỗi: ' . $e->getMessage();
    }
    
    // Fallback - lấy giỏ hàng mặc định
    $cartItems = [];
    $totalAmount = 0;
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
        <h1 class="checkout-title">Thanh toán đơn hàng</h1>

        <div class="checkout-wrap">
            <h3 class="mb-3">Đơn hàng của bạn</h3>

            <form action="<?php echo form_url('payment'); ?>" method="POST">
                <table class="order-table">
                    <thead>
                        <tr>
                            <th style="width: 70%;">Sản phẩm</th>
                            <th style="width: 30%;">Giá</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): ?>
                        <tr>
                            <td class="product-name">
                                <?php 
                                // Debug: log image value
                                $imageUrl = $item['image'] ?? '';
                                // error_log('Checkout image: ' . var_export($item, true));
                                if (!empty($imageUrl)) {
                                    $fullImageUrl = img_url($imageUrl);
                                    echo '<img src="' . $fullImageUrl . '" style="width: 50px; margin-right: 10px; vertical-align: middle; object-fit: cover; border-radius: 4px;" alt="' . htmlspecialchars($item['name']) . '" onerror="this.src=\'assets/images/no-image.png\';">';
                                } else {
                                    // Hiển thị ảnh mặc định
                                    echo '<img src="assets/images/no-image.png" style="width: 50px; margin-right: 10px; vertical-align: middle; object-fit: cover; border-radius: 4px;" alt="No image">';
                                }
                                ?>
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
                <input type="hidden" name="items[<?php echo $item['product_id'] ?? $item['id']; ?>][product_id]" value="<?php echo $item['product_id'] ?? $item['id']; ?>">
                <input type="hidden" name="items[<?php echo $item['product_id'] ?? $item['id']; ?>][quantity]" value="<?php echo $item['quantity'] ?? 1; ?>">
                <input type="hidden" name="items[<?php echo $item['product_id'] ?? $item['id']; ?>][price]" value="<?php echo $item['price'] ?? 0; ?>">
                <?php endforeach; ?>
                <input type="hidden" name="total_amount" value="<?php echo $totalAmount; ?>">
                <input type="hidden" name="checkout_token" value="<?php echo $checkoutToken; ?>">

                <h3 class="mb-3">Phương thức thanh toán</h3>
                <div class="payment-method-box">
                    <input type="radio" name="payment_method" value="sepay" checked id="pm_sepay">
                    <label for="pm_sepay">Chuyển khoản QR (SePay) </label>
                </div>
                <div class="payment-note">
                    <i class="fas fa-info-circle"></i> Dữ liệu sẽ được gửi sau khi thanh toán thành công.
                </div>
                <button type="submit" class="btn-place-order">Thanh toán ngay</button>
            </form>
        </div>
    </div>
</section>