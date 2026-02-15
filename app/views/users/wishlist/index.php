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

<style>
.user-content-with-sidebar {
    display: flex;
    gap: 20px;
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.user-wishlist {
    flex: 1;
}

.wishlist-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.wishlist-btn {
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

.wishlist-btn-primary {
    background: #3b82f6;
    color: white;
}

.wishlist-btn-secondary {
    background: #f3f4f6;
    color: #374151;
}

.wishlist-btn-outline {
    background: transparent;
    color: #3b82f6;
    border: 1px solid #3b82f6;
}

.wishlist-btn-danger {
    background: #dc2626;
    color: white;
}

.wishlist-empty {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.wishlist-empty-icon {
    font-size: 64px;
    color: #d1d5db;
    margin-bottom: 20px;
}

.wishlist-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.wishlist-item {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}

.wishlist-item:hover {
    transform: translateY(-2px);
}

.wishlist-item-image {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.wishlist-item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.wishlist-item-placeholder {
    width: 100%;
    height: 100%;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
    font-size: 48px;
}

.wishlist-item-overlay {
    position: absolute;
    top: 10px;
    right: 10px;
}

.wishlist-remove-btn {
    width: 40px;
    height: 40px;
    border: none;
    background: rgba(255, 255, 255, 0.9);
    color: #dc2626;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.wishlist-remove-btn:hover {
    background: #dc2626;
    color: white;
}

.wishlist-item-content {
    padding: 20px;
}

.wishlist-item-content h4 {
    margin: 0 0 8px 0;
    font-size: 16px;
    line-height: 1.4;
}

.wishlist-item-description {
    color: #666;
    font-size: 14px;
    margin: 0 0 12px 0;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.wishlist-item-meta {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    color: #999;
    margin-bottom: 12px;
}

.wishlist-item-price {
    margin-bottom: 16px;
}

.wishlist-price {
    font-size: 18px;
    font-weight: bold;
    color: #333;
}

.wishlist-price-original {
    font-size: 14px;
    color: #999;
    text-decoration: line-through;
    margin-right: 8px;
}

.wishlist-price-sale {
    font-size: 18px;
    font-weight: bold;
    color: #dc2626;
}

.wishlist-item-actions {
    display: flex;
    gap: 8px;
}

.wishlist-item-actions .wishlist-btn {
    flex: 1;
    justify-content: center;
    font-size: 12px;
    padding: 8px 12px;
}

.wishlist-bulk-actions {
    display: flex;
    justify-content: center;
    gap: 16px;
    padding: 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
</style>