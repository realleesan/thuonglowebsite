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
                    Tiếp tục khám phá
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
            <!-- Wishlist Items -->
            <div class="wishlist-grid">
                <?php foreach ($wishlistItems as $item): ?>
                <div class="wishlist-item">
                    <div class="wishlist-item-image">
                        <?php if (!empty($item['image'])): ?>
                            <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <?php else: ?>
                            <div class="wishlist-item-placeholder">
                                <i class="fas fa-image"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="wishlist-item-overlay">
                            <button type="button" class="wishlist-remove-btn" data-item-id="<?php echo $item['id']; ?>" title="Xóa khỏi yêu thích">
                                <i class="fas fa-heart"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="wishlist-item-content">
                        <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                        <p class="wishlist-item-description"><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
                        
                        <div class="wishlist-item-meta">
                            <span class="wishlist-item-category"><?php echo htmlspecialchars($item['category'] ?? 'Sản phẩm'); ?></span>
                            <span class="wishlist-item-date">Thêm: <?php echo date('d/m/Y', strtotime($item['added_date'] ?? 'now')); ?></span>
                        </div>
                        
                        <div class="wishlist-item-price">
                            <?php if (isset($item['sale_price']) && $item['sale_price'] < $item['price']): ?>
                                <span class="wishlist-price-original"><?php echo number_format($item['price'], 0, ',', '.'); ?> VNĐ</span>
                                <span class="wishlist-price-sale"><?php echo number_format($item['sale_price'], 0, ',', '.'); ?> VNĐ</span>
                            <?php else: ?>
                                <span class="wishlist-price"><?php echo number_format($item['price'], 0, ',', '.'); ?> VNĐ</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="wishlist-item-actions">
                            <a href="?page=products&action=view&id=<?php echo $item['id']; ?>" class="wishlist-btn wishlist-btn-outline">
                                <i class="fas fa-eye"></i>
                                Xem chi tiết
                            </a>
                            
                            <button type="button" class="wishlist-btn wishlist-btn-primary add-to-cart-btn" data-product-id="<?php echo $item['id']; ?>">
                                <i class="fas fa-shopping-cart"></i>
                                Thêm vào giỏ
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Wishlist Actions -->
            <div class="wishlist-bulk-actions">
                <button type="button" class="wishlist-btn wishlist-btn-secondary" id="addAllToCart">
                    <i class="fas fa-shopping-cart"></i>
                    Thêm tất cả vào giỏ hàng
                </button>
                
                <button type="button" class="wishlist-btn wishlist-btn-danger" id="clearWishlist">
                    <i class="fas fa-trash"></i>
                    Xóa tất cả
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Include Wishlist JavaScript -->
<script src="assets/js/user_wishlist.js"></script>