<?php
// User Cart Index - Shopping Cart
require_once __DIR__ . '/../../../services/UserService.php';

// Get current user from session
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    header('Location: ?page=login');
    exit;
}

// Get cart data from UserService
try {
    $userService = new UserService();
    $cartData = $userService->getCartData($userId);
    $cartItems = $cartData['items'] ?? [];
    $cartSummary = $cartData['summary'] ?? [
        'total_items' => 0,
        'total_amount' => 0,
    ];
} catch (Exception $e) {
    $cartItems = [];
    $cartSummary = [
        'total_items' => 0,
        'total_amount' => 0,
    ];
}
?>

<div class="user-content-with-sidebar">
    <!-- User Sidebar -->
    <?php include 'app/views/_layout/user_sidebar.php'; ?>
    
    <!-- Cart Content -->
    <div class="user-cart">
        <!-- Cart Header -->
        <div class="cart-header">
            <div class="cart-header-left">
                <h1>Giỏ hàng của bạn</h1>
                <p>Quản lý các sản phẩm trong giỏ hàng</p>
            </div>
            <div class="cart-actions">
                <a href="?page=products" class="cart-btn cart-btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Tiếp tục mua sắm
                </a>
            </div>
        </div>

        <?php if (empty($cartItems)): ?>
            <!-- Empty Cart -->
            <div class="cart-empty">
                <div class="cart-empty-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h3>Giỏ hàng trống</h3>
                <p>Bạn chưa có sản phẩm nào trong giỏ hàng</p>
                <a href="?page=products" class="cart-btn cart-btn-primary">
                    <i class="fas fa-shopping-bag"></i>
                    Khám phá sản phẩm
                </a>
            </div>
        <?php else: ?>
            <!-- Cart Items -->
            <div class="cart-content">
                <div class="cart-items">
                    <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item">
                        <div class="cart-item-image">
                            <?php if (!empty($item['image'])): ?>
                                <?php $imageUrl = (strpos($item['image'], 'http') === 0) ? $item['image'] : ($item['image'] ?? ''); ?>
                                <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <?php else: ?>
                                <div class="cart-item-placeholder">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="cart-item-details">
                            <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                            <?php if (!empty($item['short_description'])): ?>
                            <p class="cart-item-description"><?php echo htmlspecialchars($item['short_description']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($item['sku'])): ?>
                            <div class="cart-item-meta">
                                <span class="cart-item-sku">Mã: <?php echo htmlspecialchars($item['sku']); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="cart-item-quantity">
                            <label>Số lượng:</label>
                            <div class="quantity-controls">
                                <button type="button" class="quantity-btn quantity-decrease" data-item-id="<?php echo $item['id']; ?>">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" class="quantity-input" value="<?php echo $item['quantity']; ?>" min="1" data-item-id="<?php echo $item['id']; ?>">
                                <button type="button" class="quantity-btn quantity-increase" data-item-id="<?php echo $item['id']; ?>">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="cart-item-price">
                            <?php if (!empty($item['original_price']) && $item['original_price'] > $item['price']): ?>
                            <div class="cart-item-unit-price">
                                <?php echo number_format($item['original_price'], 0, ',', '.'); ?> VNĐ
                            </div>
                            <?php endif; ?>
                            <div class="cart-item-total-price">
                                <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?> VNĐ
                            </div>
                        </div>
                        
                        <div class="cart-item-actions">
                            <button type="button" class="cart-item-remove" data-item-id="<?php echo $item['id']; ?>" title="Xóa khỏi giỏ hàng">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Cart Summary -->
                <div class="cart-summary">
                    <div class="cart-summary-card">
                        <h3>Tóm tắt đơn hàng</h3>
                        
                        <div class="cart-summary-details">
                            <div class="cart-summary-row">
                                <span>Tổng sản phẩm:</span>
                                <span><?php echo $cartSummary['total_items']; ?> sản phẩm</span>
                            </div>
                            
                            <div class="cart-summary-row cart-summary-total">
                                <span>Tổng cộng:</span>
                                <span><?php echo number_format($cartSummary['total_amount'], 0, ',', '.'); ?> VNĐ</span>
                            </div>
                        </div>
                        
                        <div class="cart-summary-actions">
                            <a href="?page=payment&module=checkout" class="cart-btn cart-btn-primary cart-btn-full">
                                <i class="fas fa-credit-card"></i>
                                Thanh toán
                            </a>
                            
                            <button type="button" class="cart-btn cart-btn-secondary cart-btn-full" id="clearCart">
                                <i class="fas fa-trash"></i>
                                Xóa tất cả
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Include Cart JavaScript -->
<script src="assets/js/user_cart.js"></script>