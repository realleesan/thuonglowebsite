<?php
// User Wishlist Index - Favorite Products
require_once __DIR__ . '/../../../services/UserService.php';

// Get current user from session
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    header('Location: ?page=login');
    exit;
}

// Get wishlist data from UserService
try {
    $userService = new UserService();
    $wishlistData = $userService->getWishlistData($userId);
    $wishlistItems = $wishlistData['items'] ?? [];
    $totalItems = $wishlistData['total_items'] ?? 0;
} catch (Exception $e) {
    $wishlistItems = [];
    $totalItems = 0;
}
?>

<div class="user-content-with-sidebar">
    <!-- User Sidebar -->
    <?php include 'app/views/_layout/user_sidebar.php'; ?>
    
    <!-- Wishlist Content -->
    <div class="user-wishlist">
        <!-- Wishlist Header -->
        <div class="wishlist-header">
            <div class="wishlist-header-left">
                <h1>Danh sách yêu thích</h1>
                <p>Quản lý các sản phẩm bạn quan tâm (<?php echo $totalItems; ?> sản phẩm)</p>
            </div>
            <div class="wishlist-actions">
                <a href="?page=products" class="wishlist-btn wishlist-btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Tiếp tục mua sắm
                </a>
            </div>
        </div>

        <?php if (empty($wishlistItems)): ?>
            <!-- Empty Wishlist -->
            <div class="wishlist-empty">
                <div class="wishlist-empty-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <h3>Danh sách yêu thích trống</h3>
                <p>Bạn chưa có sản phẩm nào trong danh sách yêu thích</p>
                <a href="?page=products" class="wishlist-btn wishlist-btn-primary">
                    <i class="fas fa-shopping-bag"></i>
                    Khám phá sản phẩm
                </a>
            </div>
        <?php else: ?>
            <!-- Wishlist Items - List Layout like Cart -->
            <div class="wishlist-content">
                <div class="wishlist-items">
                    <?php foreach ($wishlistItems as $item): ?>
                    <div class="wishlist-item" data-item-id="<?php echo $item['id']; ?>">
                        <div class="wishlist-item-image">
                            <?php if (!empty($item['image'])): ?>
                                <?php $imageUrl = (strpos($item['image'], 'http') === 0) ? $item['image'] : ($item['image'] ?? ''); ?>
                                <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <?php else: ?>
                                <div class="wishlist-item-placeholder">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="wishlist-item-details">
                            <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                            <?php if (!empty($item['short_description'])): ?>
                            <p class="wishlist-item-description"><?php echo htmlspecialchars($item['short_description']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($item['sku'])): ?>
                            <div class="wishlist-item-meta">
                                <span class="wishlist-item-sku">Mã: <?php echo htmlspecialchars($item['sku']); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="wishlist-item-price">
                            <?php if (!empty($item['original_price']) && $item['original_price'] > $item['price']): ?>
                            <div class="wishlist-item-unit-price">
                                <?php echo number_format($item['original_price'], 0, ',', '.'); ?> VNĐ
                            </div>
                            <?php endif; ?>
                            <div class="wishlist-item-total-price">
                                <?php echo number_format($item['price'], 0, ',', '.'); ?> VNĐ
                            </div>
                        </div>
                        
                        <div class="wishlist-item-actions">
                            <button type="button" class="wishlist-btn-small wishlist-add-cart" onclick="addToCartFromWishlist(<?php echo $item['product_id']; ?>, this)" title="Thêm vào giỏ hàng">
                                <i class="fas fa-shopping-cart"></i>
                            </button>
                            <button type="button" class="wishlist-item-remove" onclick="removeFromWishlist(<?php echo $item['product_id']; ?>, this)" title="Xóa khỏi yêu thích">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Wishlist Summary -->
                <div class="wishlist-summary">
                    <div class="wishlist-summary-info">
                        <span class="wishlist-summary-label">Tổng sản phẩm:</span>
                        <span class="wishlist-summary-count"><?php echo $totalItems; ?> sản phẩm</span>
                    </div>
                    <div class="wishlist-summary-actions">
                        <button type="button" class="wishlist-btn wishlist-btn-danger" onclick="clearAllWishlist()">
                            <i class="fas fa-trash-alt"></i> Xóa tất cả
                        </button>
                        <a href="?page=products" class="wishlist-btn wishlist-btn-primary">
                            <i class="fas fa-shopping-bag"></i> Tiếp tục mua sắm
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Include Wishlist JavaScript -->
<script src="assets/js/user_wishlist.js"></script>

<!-- Inline Wishlist Functions -->
<script>
// Remove single item from wishlist
function removeFromWishlist(productId, button) {
    if (!confirm('Bạn có chắc muốn xóa sản phẩm này khỏi yêu thích?')) {
        return;
    }
    
    fetch('api.php?action=wishlist/remove', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ product_id: productId }),
        credentials: 'same-origin'
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.success) {
            alert(data.message);
            setTimeout(function() { window.location.reload(); }, 1000);
        } else {
            alert(data.message || 'Có lỗi xảy ra');
        }
    })
    .catch(function(err) {
        console.error(err);
        alert('Có lỗi xảy ra');
    });
}

// Add to cart from wishlist
function addToCartFromWishlist(productId, button) {
    console.log('Adding product to cart, ID:', productId);
    fetch('api.php?action=cart/add', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ product_id: productId, quantity: 1 }),
        credentials: 'same-origin'
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        console.log('Cart response:', data);
        if (data.success) {
            alert(data.message || 'Đã thêm vào giỏ hàng');
            setTimeout(function() { window.location.reload(); }, 500);
        } else {
            alert(data.message || 'Có lỗi xảy ra');
        }
    })
    .catch(function(err) {
        console.error(err);
        alert('Có lỗi xảy ra');
    });
}

// Clear all wishlist
function clearAllWishlist() {
    if (!confirm('Bạn có chắc muốn xóa tất cả sản phẩm khỏi yêu thích?')) {
        return;
    }
    
    fetch('api.php?action=wishlist/clear', {
        method: 'POST',
        credentials: 'same-origin'
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.success) {
            alert(data.message);
            setTimeout(function() { window.location.reload(); }, 1000);
        } else {
            alert(data.message || 'Có lỗi xảy ra');
        }
    })
    .catch(function(err) {
        console.error(err);
        alert('Có lỗi xảy ra');
    });
}
</script>
