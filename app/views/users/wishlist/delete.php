<?php
// User Wishlist Delete - Remove from Wishlist
require_once __DIR__ . '/../../../services/UserService.php';

// Get current user from session
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    header('Location: ?page=login');
    exit;
}

// Get wishlist item ID from URL
$itemId = $_GET['id'] ?? null;

// Initialize UserService
$userService = new UserService();

// Handle delete confirmation
$message = '';
$messageType = '';
$deleted = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        if ($itemId) {
            $result = $userService->removeFromWishlist($userId, $itemId);
        }
        
        if ($result) {
            $deleted = true;
            $message = 'Đã xóa sản phẩm khỏi danh sách yêu thích!';
            $messageType = 'success';
        } else {
            $message = 'Xóa thất bại. Vui lòng thử lại.';
            $messageType = 'error';
        }
    } catch (Exception $e) {
        $message = 'Đã xảy ra lỗi: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Get wishlist data to show item info
try {
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
    
    <!-- Wishlist Delete Content -->
    <div class="user-wishlist">
        <!-- Header -->
        <div class="wishlist-header">
            <div class="wishlist-header-left">
                <h1>Xóa khỏi yêu thích</h1>
                <p>Xóa sản phẩm khỏi danh sách yêu thích của bạn</p>
            </div>
            <div class="wishlist-actions">
                <a href="?page=users&module=wishlist" class="wishlist-btn wishlist-btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Quay lại danh sách
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
                    <a href="?page=users&module=wishlist" class="wishlist-btn wishlist-btn-primary">
                        <i class="fas fa-heart"></i>
                        Quay lại danh sách yêu thích
                    </a>
                    <a href="?page=products" class="wishlist-btn wishlist-btn-secondary">
                        <i class="fas fa-shopping-bag"></i>
                        Tiếp tục mua sắm
                    </a>
                </div>
            </div>
        <?php elseif (!$wishlistItem): ?>
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
            <!-- Delete Confirmation -->
            <div class="delete-confirmation">
                <div class="delete-confirmation-card">
                    <div class="delete-warning-icon">
                        <i class="fas fa-heart-broken"></i>
                    </div>
                    <h3>Xóa khỏi yêu thích?</h3>
                    <p>Bạn có chắc chắn muốn xóa sản phẩm này khỏi danh sách yêu thích?</p>
                    
                    <div class="delete-item-preview">
                        <div class="delete-item-image">
                            <?php if (!empty($wishlistItem['image'])): ?>
                                <img src="<?php echo htmlspecialchars($wishlistItem['image']); ?>" alt="<?php echo htmlspecialchars($wishlistItem['name']); ?>">
                            <?php else: ?>
                                <div class="delete-item-placeholder">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="delete-item-info">
                            <h4><?php echo htmlspecialchars($wishlistItem['name']); ?></h4>
                            <div class="delete-item-price">
                                <?php echo number_format($wishlistItem['price'] ?? 0, 0, ',', '.'); ?> VNĐ
                            </div>
                        </div>
                    </div>
                    
                    <form method="POST" class="delete-form">
                        <div class="delete-actions">
                            <button type="submit" name="confirm_delete" value="1" class="wishlist-btn wishlist-btn-danger">
                                <i class="fas fa-trash"></i>
                                Xóa khỏi yêu thích
                            </button>
                            <a href="?page=users&module=wishlist" class="wishlist-btn wishlist-btn-secondary">
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
    color: #dc2626;
}

.delete-form {
    margin-top: 0;
}

.delete-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
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
</style>
