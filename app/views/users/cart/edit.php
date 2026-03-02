<?php
// User Cart Edit - Edit Cart Item
require_once __DIR__ . '/../../../services/UserService.php';

// Get current user from session
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    header('Location: ?page=login');
    exit;
}

// Get cart item ID from URL
$itemId = $_GET['id'] ?? null;

// Handle form submission
$message = '';
$messageType = '';
$updated = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantity = (int)($_POST['quantity'] ?? 1);
    
    if ($itemId) {
        $updated = $userService->updateCartItem($userId, $itemId, $quantity);
    }
    
    if ($updated) {
        $message = 'Cập nhật số lượng thành công!';
        $messageType = 'success';
        // Refresh cart data after update
        $cartData = $userService->getCartData($userId);
        $cartItems = $cartData['items'] ?? [];
        
        // Find the updated item
        foreach ($cartItems as $item) {
            if ($item['id'] == $itemId) {
                $cartItem = $item;
                break;
            }
        }
    } else {
        $message = 'Cập nhật thất bại. Vui lòng thử lại.';
        $messageType = 'error';
    }
}

// Get cart data
try {
    $userService = new UserService();
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
    
    <!-- Cart Edit Content -->
    <div class="user-cart">
        <!-- Header -->
        <div class="cart-header">
            <div class="cart-header-left">
                <h1>Chỉnh sửa sản phẩm</h1>
                <p>Cập nhật thông tin sản phẩm trong giỏ hàng</p>
            </div>
            <div class="cart-actions">
                <a href="?page=users&module=cart" class="cart-btn cart-btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Quay lại giỏ hàng
                </a>
            </div>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

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
            <!-- Edit Form -->
            <div class="cart-edit-form">
                <div class="cart-edit-grid">
                    <!-- Product Image -->
                    <div class="cart-edit-image">
                        <?php if (!empty($cartItem['image'])): ?>
                            <img src="<?php echo htmlspecialchars($cartItem['image']); ?>" alt="<?php echo htmlspecialchars($cartItem['name']); ?>">
                        <?php else: ?>
                            <div class="cart-edit-image-placeholder">
                                <i class="fas fa-image"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Form Section -->
                    <div class="cart-edit-info">
                        <h2 class="cart-edit-name"><?php echo htmlspecialchars($cartItem['name']); ?></h2>
                        
                        <div class="cart-edit-price">
                            <span class="cart-edit-price-label">Giá:</span>
                            <span class="cart-edit-price-value">
                                <?php echo number_format($cartItem['price'] ?? 0, 0, ',', '.'); ?> VNĐ
                            </span>
                        </div>
                        
                        <form method="POST" class="cart-edit-form-main">
                            <div class="form-group">
                                <label for="quantity">Số lượng:</label>
                                <div class="quantity-selector">
                                    <button type="button" class="quantity-btn" onclick="decreaseQty()">-</button>
                                    <input type="number" id="quantity" name="quantity" value="<?php echo $cartItem['quantity'] ?? 1; ?>" min="1" max="99" required>
                                    <button type="button" class="quantity-btn" onclick="increaseQty()">+</button>
                                </div>
                            </div>
                            
                            <div class="cart-edit-total">
                                <span class="cart-edit-total-label">Thành tiền:</span>
                                <span class="cart-edit-total-value">
                                    <?php echo number_format(($cartItem['price'] ?? 0) * ($cartItem['quantity'] ?? 1), 0, ',', '.'); ?> VNĐ
                                </span>
                            </div>
                            
                            <div class="cart-edit-actions">
                                <button type="submit" class="cart-btn cart-btn-primary">
                                    <i class="fas fa-save"></i>
                                    Lưu thay đổi
                                </button>
                                <a href="?page=users&module=cart&action=view&id=<?php echo $cartItem['id']; ?>" class="cart-btn cart-btn-secondary">
                                    <i class="fas fa-eye"></i>
                                    Xem chi tiết
                                </a>
                                <a href="?page=users&module=cart&action=delete&id=<?php echo $cartItem['id']; ?>" class="cart-btn cart-btn-danger">
                                    <i class="fas fa-trash"></i>
                                    Xóa sản phẩm
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function decreaseQty() {
    const input = document.getElementById('quantity');
    if (parseInt(input.value) > 1) {
        input.value = parseInt(input.value) - 1;
        updateTotal();
    }
}

function increaseQty() {
    const input = document.getElementById('quantity');
    if (parseInt(input.value) < 99) {
        input.value = parseInt(input.value) + 1;
        updateTotal();
    }
}

function updateTotal() {
    const input = document.getElementById('quantity');
    const price = <?php echo $cartItem['price'] ?? 0; ?>;
    const total = parseInt(input.value) * price;
    document.querySelector('.cart-edit-total-value').textContent = new Intl.NumberFormat('vi-VN').format(total) + ' VNĐ';
}
</script>

<style>
.cart-edit-form {
    background: white;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    overflow: hidden;
}

.cart-edit-grid {
    display: grid;
    grid-template-columns: 350px 1fr;
    gap: 40px;
}

@media (max-width: 768px) {
    .cart-edit-grid {
        grid-template-columns: 1fr;
    }
}

.cart-edit-image {
    background: #f9fafb;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 250px;
}

.cart-edit-image img {
    width: 100%;
    height: 250px;
    object-fit: cover;
}

.cart-edit-image-placeholder {
    width: 100%;
    height: 250px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 64px;
    color: #d1d5db;
}

.cart-edit-info {
    padding: 30px;
}

.cart-edit-name {
    font-size: 22px;
    font-weight: 600;
    color: #111827;
    margin: 0 0 16px 0;
}

.cart-edit-price {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    background: #eff6ff;
    border-radius: 8px;
    margin-bottom: 24px;
}

.cart-edit-price-label {
    color: #6b7280;
    font-size: 14px;
}

.cart-edit-price-value {
    font-size: 20px;
    font-weight: 600;
    color: #356DF1;
}

.cart-edit-form-main {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-group label {
    font-size: 14px;
    font-weight: 500;
    color: #374151;
}

.quantity-selector {
    display: flex;
    align-items: center;
    gap: 8px;
}

.quantity-selector input {
    width: 80px;
    height: 40px;
    text-align: center;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 500;
}

.quantity-selector input:focus {
    outline: none;
    border-color: #356DF1;
}

.quantity-selector .quantity-btn {
    width: 40px;
    height: 40px;
    border: 1px solid #e5e7eb;
    background: white;
    border-radius: 8px;
    font-size: 18px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.quantity-selector .quantity-btn:hover {
    border-color: #356DF1;
    color: #356DF1;
}

.cart-edit-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    background: #f0fdf4;
    border-radius: 8px;
}

.cart-edit-total-label {
    font-size: 16px;
    font-weight: 500;
    color: #111827;
}

.cart-edit-total-value {
    font-size: 24px;
    font-weight: 700;
    color: #16a34a;
}

.cart-edit-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.alert {
    padding: 16px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert-success {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}
</style>
