<?php
// User Cart View - View Cart Details
require_once __DIR__ . '/../../../services/UserService.php';

// Get current user from session
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    header('Location: ?page=login');
    exit;
}

// Get cart item ID from URL
$itemId = $_GET['id'] ?? null;

// Get cart data from UserService
try {
    $userService = new UserService();
    $cartData = $userService->getCartData($userId);
    $cartItems = $cartData['items'] ?? [];
    $cartSummary = $cartData['summary'] ?? [
        'total_items' => 0,
        'total_amount' => 0,
    ];
    
    // Find the specific item
    $cartItem = null;
    foreach ($cartItems as $item) {
        if ($item['id'] == $itemId) {
            $cartItem = $item;
            break;
        }
    }
} catch (Exception $e) {
    $cartItems = [];
    $cartSummary = [
        'total_items' => 0,
        'total_amount' => 0,
    ];
    $cartItem = null;
}
?>

<div class="user-content-with-sidebar">
    <!-- User Sidebar -->
    <?php include 'app/views/_layout/user_sidebar.php'; ?>
    
    <!-- Cart View Content -->
    <div class="user-cart">
        <!-- Header -->
        <div class="cart-header">
            <div class="cart-header-left">
                <h1>Chi tiết sản phẩm</h1>
                <p>Xem thông tin chi tiết của sản phẩm trong giỏ hàng</p>
            </div>
            <div class="cart-actions">
                <a href="?page=users&module=cart" class="cart-btn cart-btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Quay lại giỏ hàng
                </a>
            </div>
        </div>

        <?php if (!$cartItem): ?>
            <!-- Item Not Found -->
            <div class="cart-empty">
                <div class="cart-empty-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3>Không tìm thấy sản phẩm</h3>
                <p>Sản phẩm này không còn trong giỏ hàng của bạn</p>
                <a href="?page=users&module=cart" class="cart-btn cart-btn-primary">
                    <i class="fas fa-shopping-cart"></i>
                    Xem giỏ hàng
                </a>
            </div>
        <?php else: ?>
            <!-- Product Details -->
            <div class="cart-view-details">
                <div class="cart-view-grid">
                    <!-- Image Section -->
                    <div class="cart-view-image">
                        <?php if (!empty($cartItem['image'])): ?>
                            <img src="<?php echo htmlspecialchars($cartItem['image']); ?>" alt="<?php echo htmlspecialchars($cartItem['name']); ?>">
                        <?php else: ?>
                            <div class="cart-view-image-placeholder">
                                <i class="fas fa-image"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Info Section -->
                    <div class="cart-view-info">
                        <div class="cart-view-category">
                            <?php echo htmlspecialchars($cartItem['category'] ?? 'Sản phẩm'); ?>
                        </div>
                        <h2 class="cart-view-name"><?php echo htmlspecialchars($cartItem['name']); ?></h2>
                        
                        <?php if (!empty($cartItem['description'])): ?>
                        <div class="cart-view-description">
                            <?php echo nl2br(htmlspecialchars($cartItem['description'])); ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="cart-view-meta">
                            <div class="cart-view-meta-item">
                                <span class="cart-view-meta-label">Số lượng:</span>
                                <span class="cart-view-meta-value"><?php echo $cartItem['quantity'] ?? 1; ?></span>
                            </div>
                            <?php if (!empty($cartItem['sku'])): ?>
                            <div class="cart-view-meta-item">
                                <span class="cart-view-meta-label">SKU:</span>
                                <span class="cart-view-meta-value"><?php echo htmlspecialchars($cartItem['sku']); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="cart-view-price">
                            <div class="cart-view-price-label">Giá:</div>
                            <div class="cart-view-price-value">
                                <?php echo number_format($cartItem['price'] ?? 0, 0, ',', '.'); ?> VNĐ
                            </div>
                        </div>
                        
                        <div class="cart-view-total">
                            <div class="cart-view-total-label">Thành tiền:</div>
                            <div class="cart-view-total-value">
                                <?php echo number_format(($cartItem['price'] ?? 0) * ($cartItem['quantity'] ?? 1), 0, ',', '.'); ?> VNĐ
                            </div>
                        </div>
                        
                        <div class="cart-view-actions">
                            <a href="?page=users&module=cart&action=edit&id=<?php echo $cartItem['id']; ?>" class="cart-btn cart-btn-primary">
                                <i class="fas fa-edit"></i>
                                Chỉnh sửa
                            </a>
                            <a href="?page=users&module=cart&action=delete&id=<?php echo $cartItem['id']; ?>" class="cart-btn cart-btn-danger">
                                <i class="fas fa-trash"></i>
                                Xóa
                            </a>
                            <a href="?page=checkout" class="cart-btn cart-btn-success">
                                <i class="fas fa-credit-card"></i>
                                Thanh toán
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.cart-view-details {
    background: white;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    overflow: hidden;
}

.cart-view-grid {
    display: grid;
    grid-template-columns: 400px 1fr;
    gap: 40px;
}

@media (max-width: 768px) {
    .cart-view-grid {
        grid-template-columns: 1fr;
    }
}

.cart-view-image {
    background: #f9fafb;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 300px;
}

.cart-view-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.cart-view-image-placeholder {
    width: 100%;
    height: 300px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 64px;
    color: #d1d5db;
}

.cart-view-info {
    padding: 30px;
}

.cart-view-category {
    font-size: 14px;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}

.cart-view-name {
    font-size: 24px;
    font-weight: 600;
    color: #111827;
    margin: 0 0 16px 0;
}

.cart-view-description {
    color: #6b7280;
    line-height: 1.6;
    margin-bottom: 24px;
}

.cart-view-meta {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 24px;
    padding: 16px;
    background: #f9fafb;
    border-radius: 8px;
}

.cart-view-meta-item {
    display: flex;
    justify-content: space-between;
}

.cart-view-meta-label {
    color: #6b7280;
    font-size: 14px;
}

.cart-view-meta-value {
    color: #111827;
    font-weight: 500;
    font-size: 14px;
}

.cart-view-price {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    background: #eff6ff;
    border-radius: 8px;
    margin-bottom: 12px;
}

.cart-view-price-label {
    color: #6b7280;
    font-size: 14px;
}

.cart-view-price-value {
    font-size: 20px;
    font-weight: 600;
    color: #356DF1;
}

.cart-view-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    background: #f0fdf4;
    border-radius: 8px;
    margin-bottom: 24px;
}

.cart-view-total-label {
    color: #111827;
    font-size: 16px;
    font-weight: 500;
}

.cart-view-total-value {
    font-size: 24px;
    font-weight: 700;
    color: #16a34a;
}

.cart-view-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.cart-btn-danger {
    background: #fee2e2;
    color: #dc2626;
    border: none;
}

.cart-btn-danger:hover {
    background: #fecaca;
    color: #b91c1c;
}

.cart-btn-success {
    background: #dcfce7;
    color: #16a34a;
    border: none;
}

.cart-btn-success:hover {
    background: #bbf7d0;
    color: #15803d;
}
</style>
