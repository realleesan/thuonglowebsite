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
                                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <?php else: ?>
                                <div class="cart-item-placeholder">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="cart-item-details">
                            <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                            <p class="cart-item-description"><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
                            <div class="cart-item-meta">
                                <span class="cart-item-sku">SKU: <?php echo htmlspecialchars($item['sku'] ?? 'N/A'); ?></span>
                            </div>
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
                            <div class="cart-item-unit-price">
                                <?php echo number_format($item['price'], 0, ',', '.'); ?> VNĐ
                            </div>
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
                            
                            <div class="cart-summary-row">
                                <span>Tạm tính:</span>
                                <span><?php echo number_format($cartSummary['total_amount'], 0, ',', '.'); ?> VNĐ</span>
                            </div>
                            
                            <div class="cart-summary-row">
                                <span>Phí vận chuyển:</span>
                                <span>Miễn phí</span>
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

<style>
.user-content-with-sidebar {
    display: flex;
    gap: 20px;
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.user-cart {
    flex: 1;
}

.cart-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.cart-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    font-size: 14px;
    border: none;
    cursor: pointer;
}

.cart-btn-primary {
    background: #3b82f6;
    color: white;
}

.cart-btn-secondary {
    background: #f3f4f6;
    color: #374151;
}

.cart-btn-full {
    width: 100%;
    justify-content: center;
}

.cart-empty {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.cart-empty-icon {
    font-size: 64px;
    color: #d1d5db;
    margin-bottom: 20px;
}

.cart-content {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 30px;
}

.cart-items {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.cart-item {
    display: grid;
    grid-template-columns: 80px 1fr auto auto auto;
    gap: 20px;
    align-items: center;
    padding: 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.cart-item-image img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
}

.cart-item-placeholder {
    width: 80px;
    height: 80px;
    background: #f3f4f6;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
}

.cart-item-details h4 {
    margin: 0 0 8px 0;
    font-size: 16px;
}

.cart-item-description {
    color: #666;
    font-size: 14px;
    margin: 0 0 8px 0;
}

.cart-item-meta {
    font-size: 12px;
    color: #999;
}

.quantity-controls {
    display: flex;
    align-items: center;
    gap: 8px;
}

.quantity-btn {
    width: 32px;
    height: 32px;
    border: 1px solid #ddd;
    background: white;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.quantity-input {
    width: 60px;
    height: 32px;
    text-align: center;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.cart-item-price {
    text-align: right;
}

.cart-item-unit-price {
    font-size: 14px;
    color: #666;
}

.cart-item-total-price {
    font-size: 16px;
    font-weight: bold;
    color: #333;
}

.cart-item-remove {
    width: 40px;
    height: 40px;
    border: none;
    background: #fee2e2;
    color: #dc2626;
    border-radius: 8px;
    cursor: pointer;
}

.cart-summary-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    position: sticky;
    top: 20px;
}

.cart-summary-details {
    margin: 20px 0;
}

.cart-summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 12px;
}

.cart-summary-total {
    font-weight: bold;
    font-size: 18px;
    padding-top: 12px;
    border-top: 1px solid #eee;
}

.cart-summary-actions {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
</style>