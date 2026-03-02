<?php
// User Cart Delete - Delete Cart Item
require_once __DIR__ . '/../../../services/UserService.php';

// Get current user from session
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    header('Location: ?page=login');
    exit;
}

// Get cart item ID from URL
$itemId = $_GET['id'] ?? null;

// Initialize UserService
$userService = new UserService();

// Handle delete confirmation
$message = '';
$messageType = '';
$deleted = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    if ($itemId) {
        $deleted = $userService->removeFromCart($userId, $itemId);
    }
    
    if ($deleted) {
        $message = 'Xóa sản phẩm khỏi giỏ hàng thành công!';
        $messageType = 'success';
    } else {
        $message = 'Xóa thất bại. Vui lòng thử lại.';
        $messageType = 'error';
    }
}

// Get cart data to show item info
try {
    $cartData = $userService->getCartData($userId);
    $cartItems = $cartData['items'] ?? [];
    
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
    $cartItem = null;
}
?>

<div class="user-content-with-sidebar">
    <!-- User Sidebar -->
    <?php include 'app/views/_layout/user_sidebar.php'; ?>
    
    <!-- Cart Delete Content -->
    <div class="user-cart">
        <!-- Header -->
        <div class="cart-header">
            <div class="cart-header-left">
                <h1>Xóa sản phẩm</h1>
                <p>Xóa sản phẩm khỏi giỏ hàng của bạn</p>
            </div>
            <div class="cart-actions">
                <a href="?page=users&module=cart" class="cart-btn cart-btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Quay lại giỏ hàng
                </a>
            </div>
        </div>

        <?php if ($deleted): ?>
            <!-- Delete Success -->
            <div class="delete-success">
                <div class="delete-success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3><?php echo htmlspecialchars($message); ?></h3>
                <div class="delete-success-actions">
                    <a href="?page=users&module=cart" class="cart-btn cart-btn-primary">
                        <i class="fas fa-shopping-cart"></i>
                        Quay lại giỏ hàng
                    </a>
                    <a href="?page=products" class="cart-btn cart-btn-secondary">
                        <i class="fas fa-shopping-bag"></i>
                        Tiếp tục mua sắm
                    </a>
                </div>
            </div>
        <?php elseif (!$cartItem): ?>
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
            <!-- Delete Confirmation -->
            <div class="delete-confirmation">
                <div class="delete-confirmation-card">
                    <div class="delete-warning-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h3>Bạn có chắc chắn muốn xóa?</h3>
                    <p>Hành động này không thể hoàn tác.</p>
                    
                    <div class="delete-item-preview">
                        <div class="delete-item-image">
                            <?php if (!empty($cartItem['image'])): ?>
                                <img src="<?php echo htmlspecialchars($cartItem['image']); ?>" alt="<?php echo htmlspecialchars($cartItem['name']); ?>">
                            <?php else: ?>
                                <div class="delete-item-placeholder">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="delete-item-info">
                            <h4><?php echo htmlspecialchars($cartItem['name']); ?></h4>
                            <div class="delete-item-price">
                                <?php echo number_format($cartItem['price'] ?? 0, 0, ',', '.'); ?> VNĐ
                                <?php if (($cartItem['quantity'] ?? 1) > 1): ?>
                                    <span class="delete-item-qty">x<?php echo $cartItem['quantity']; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <form method="POST" class="delete-form">
                        <div class="delete-actions">
                            <button type="submit" name="confirm_delete" value="1" class="cart-btn cart-btn-danger">
                                <i class="fas fa-trash"></i>
                                Xóa sản phẩm
                            </button>
                            <a href="?page=users&module=cart" class="cart-btn cart-btn-secondary">
                                Hủy bỏ
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.delete-success {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
}

.delete-success-icon {
    font-size: 64px;
    color: #22c55e;
    margin-bottom: 20px;
}

.delete-success h3 {
    font-size: 24px;
    color: #111827;
    margin: 0 0 24px 0;
}

.delete-success-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
}

.delete-confirmation {
    display: flex;
    justify-content: center;
    padding: 40px 20px;
}

.delete-confirmation-card {
    background: white;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    padding: 40px;
    max-width: 500px;
    width: 100%;
    text-align: center;
}

.delete-warning-icon {
    width: 80px;
    height: 80px;
    background: #fee2e2;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 36px;
    color: #dc2626;
}

.delete-confirmation-card h3 {
    font-size: 24px;
    color: #111827;
    margin: 0 0 8px 0;
}

.delete-confirmation-card > p {
    color: #6b7280;
    margin: 0 0 24px 0;
}

.delete-item-preview {
    display: flex;
    gap: 16px;
    padding: 16px;
    background: #f9fafb;
    border-radius: 8px;
    margin-bottom: 24px;
    text-align: left;
}

.delete-item-image {
    width: 80px;
    height: 80px;
    border-radius: 8px;
    overflow: hidden;
    flex-shrink: 0;
    background: white;
}

.delete-item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.delete-item-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d1d5db;
    font-size: 24px;
}

.delete-item-info {
    flex: 1;
}

.delete-item-info h4 {
    font-size: 16px;
    font-weight: 600;
    color: #111827;
    margin: 0 0 8px 0;
}

.delete-item-price {
    font-size: 16px;
    font-weight: 600;
    color: #356DF1;
}

.delete-item-qty {
    color: #6b7280;
    font-weight: normal;
    margin-left: 8px;
}

.delete-form {
    margin-top: 0;
}

.delete-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
}
</style>
