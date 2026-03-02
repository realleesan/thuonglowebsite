<?php
// User Wishlist View - View Wishlist Item Details
require_once __DIR__ . '/../../../services/UserService.php';

// Get current user from session
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    header('Location: ?page=login');
    exit;
}

// Get wishlist item ID from URL
$itemId = $_GET['id'] ?? null;

// Get wishlist data from UserService
try {
    $userService = new UserService();
    $wishlistData = $userService->getWishlistData($userId);
    $wishlistItems = $wishlistData['items'] ?? [];
    
    // Find the specific item
    $wishlistItem = null;
    foreach ($wishlistItems as $item) {
        if ($item['id'] == $itemId) {
            $wishlistItem = $item;
            break;
        }
    }
} catch (Exception $e) {
    $wishlistItems = [];
    $wishlistItem = null;
}
?>

<div class="user-content-with-sidebar">
    <!-- User Sidebar -->
    <?php include 'app/views/_layout/user_sidebar.php'; ?>
    
    <!-- Wishlist View Content -->
    <div class="user-wishlist">
        <!-- Header -->
        <div class="wishlist-header">
            <div class="wishlist-header-left">
                <h1>Chi tiết sản phẩm</h1>
                <p>Xem thông tin chi tiết của sản phẩm yêu thích</p>
            </div>
            <div class="wishlist-actions">
                <a href="?page=users&module=wishlist" class="wishlist-btn wishlist-btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Quay lại danh sách
                </a>
            </div>
        </div>

        <?php if (!$wishlistItem): ?>
            <!-- Item Not Found -->
            <div class="wishlist-empty">
                <div class="wishlist-empty-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3>Không tìm thấy sản phẩm</h3>
                <p>Sản phẩm này không còn trong danh sách yêu thích của bạn</p>
                <a href="?page=users&module=wishlist" class="wishlist-btn wishlist-btn-primary">
                    <i class="fas fa-heart"></i>
                    Xem danh sách yêu thích
                </a>
            </div>
        <?php else: ?>
            <!-- Product Details -->
            <div class="wishlist-view-details">
                <div class="wishlist-view-grid">
                    <!-- Image Section -->
                    <div class="wishlist-view-image">
                        <?php if (!empty($wishlistItem['image'])): ?>
                            <img src="<?php echo htmlspecialchars($wishlistItem['image']); ?>" alt="<?php echo htmlspecialchars($wishlistItem['name']); ?>">
                        <?php else: ?>
                            <div class="wishlist-view-image-placeholder">
                                <i class="fas fa-image"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Info Section -->
                    <div class="wishlist-view-info">
                        <div class="wishlist-view-category">
                            <?php echo htmlspecialchars($wishlistItem['category'] ?? 'Sản phẩm'); ?>
                        </div>
                        <h2 class="wishlist-view-name"><?php echo htmlspecialchars($wishlistItem['name']); ?></h2>
                        
                        <?php if (!empty($wishlistItem['short_description'])): ?>
                        <div class="wishlist-view-description">
                            <?php echo nl2br(htmlspecialchars($wishlistItem['short_description'])); ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="wishlist-view-price">
                            <div class="wishlist-view-price-current">
                                <?php echo number_format($wishlistItem['price'] ?? 0, 0, ',', '.'); ?> VNĐ
                            </div>
                            <?php if (!empty($wishlistItem['original_price']) && $wishlistItem['original_price'] > $wishlistItem['price']): ?>
                            <div class="wishlist-view-price-original">
                                <?php echo number_format($wishlistItem['original_price'], 0, ',', '.'); ?> VNĐ
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="wishlist-view-meta">
                            <?php if (!empty($wishlistItem['sku'])): ?>
                            <div class="wishlist-view-meta-item">
                                <span class="wishlist-view-meta-label">SKU:</span>
                                <span class="wishlist-view-meta-value"><?php echo htmlspecialchars($wishlistItem['sku']); ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($wishlistItem['stock'])): ?>
                            <div class="wishlist-view-meta-item">
                                <span class="wishlist-view-meta-label">Tình trạng:</span>
                                <span class="wishlist-view-meta-value <?php echo ($wishlistItem['stock'] > 0) ? 'in-stock' : 'out-of-stock'; ?>">
                                    <?php echo ($wishlistItem['stock'] > 0) ? 'Còn hàng' : 'Hết hàng'; ?>
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="wishlist-view-actions">
                            <form method="POST" action="?page=users&module=cart&action=add" style="display: inline;">
                                <input type="hidden" name="product_id" value="<?php echo $wishlistItem['product_id']; ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="wishlist-btn wishlist-btn-primary">
                                    <i class="fas fa-shopping-cart"></i>
                                    Thêm vào giỏ hàng
                                </button>
                            </form>
                            <a href="?page=users&module=wishlist&action=delete&id=<?php echo $wishlistItem['id']; ?>" class="wishlist-btn wishlist-btn-danger">
                                <i class="fas fa-trash"></i>
                                Xóa khỏi yêu thích
                            </a>
                            <a href="?page=details&id=<?php echo $wishlistItem['product_id']; ?>" class="wishlist-btn wishlist-btn-outline">
                                <i class="fas fa-eye"></i>
                                Xem chi tiết
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.wishlist-view-details {
    background: white;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    overflow: hidden;
}

.wishlist-view-grid {
    display: grid;
    grid-template-columns: 400px 1fr;
    gap: 40px;
}

@media (max-width: 768px) {
    .wishlist-view-grid {
        grid-template-columns: 1fr;
    }
}

.wishlist-view-image {
    background: #f9fafb;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 300px;
}

.wishlist-view-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.wishlist-view-image-placeholder {
    width: 100%;
    height: 300px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 64px;
    color: #d1d5db;
}

.wishlist-view-info {
    padding: 30px;
}

.wishlist-view-category {
    font-size: 14px;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}

.wishlist-view-name {
    font-size: 24px;
    font-weight: 600;
    color: #111827;
    margin: 0 0 16px 0;
}

.wishlist-view-description {
    color: #6b7280;
    line-height: 1.6;
    margin-bottom: 24px;
}

.wishlist-view-price {
    display: flex;
    align-items: baseline;
    gap: 12px;
    margin-bottom: 24px;
}

.wishlist-view-price-current {
    font-size: 28px;
    font-weight: 700;
    color: #dc2626;
}

.wishlist-view-price-original {
    font-size: 18px;
    color: #9ca3af;
    text-decoration: line-through;
}

.wishlist-view-meta {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 24px;
    padding: 16px;
    background: #f9fafb;
    border-radius: 8px;
}

.wishlist-view-meta-item {
    display: flex;
    justify-content: space-between;
}

.wishlist-view-meta-label {
    color: #6b7280;
    font-size: 14px;
}

.wishlist-view-meta-value {
    color: #111827;
    font-weight: 500;
    font-size: 14px;
}

.wishlist-view-meta-value.in-stock {
    color: #16a34a;
}

.wishlist-view-meta-value.out-of-stock {
    color: #dc2626;
}

.wishlist-view-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.wishlist-btn-danger {
    background: #fee2e2;
    color: #dc2626;
    border: none;
}

.wishlist-btn-danger:hover {
    background: #fecaca;
    color: #b91c1c;
}

.wishlist-btn-outline {
    background: transparent;
    color: #356DF1;
    border: 1px solid #356DF1;
}

.wishlist-btn-outline:hover {
    background: #eff6ff;
}
</style>
