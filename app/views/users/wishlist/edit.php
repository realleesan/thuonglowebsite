<?php
// User Wishlist Edit - Edit Wishlist Item
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

// Handle form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $notes = $_POST['notes'] ?? '';
    
    try {
        if ($itemId) {
            $result = $userService->updateWishlistNotes($userId, $itemId, $notes);
        }
        
        if ($result) {
            $message = 'Cập nhật ghi chú thành công!';
            $messageType = 'success';
            // Refresh wishlist data
            $wishlistData = $userService->getWishlistData($userId);
            $wishlistItems = $wishlistData['items'] ?? [];
            
            // Find the updated item
            foreach ($wishlistItems as $item) {
                if ($item['id'] == $itemId) {
                    $wishlistItem = $item;
                    break;
                }
            }
        } else {
            $message = 'Cập nhật thất bại. Vui lòng thử lại.';
            $messageType = 'error';
        }
    } catch (Exception $e) {
        $message = 'Đã xảy ra lỗi: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Get wishlist data
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
    
    <!-- Wishlist Edit Content -->
    <div class="user-wishlist">
        <!-- Header -->
        <div class="wishlist-header">
            <div class="wishlist-header-left">
                <h1>Chỉnh sửa yêu thích</h1>
                <p>Cập nhật ghi chú cho sản phẩm yêu thích</p>
            </div>
            <div class="wishlist-actions">
                <a href="?page=users&module=wishlist" class="wishlist-btn wishlist-btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Quay lại danh sách
                </a>
            </div>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

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
            <!-- Edit Form -->
            <div class="wishlist-edit-form">
                <div class="wishlist-edit-grid">
                    <!-- Product Image -->
                    <div class="wishlist-edit-image">
                        <?php if (!empty($wishlistItem['image'])): ?>
                            <img src="<?php echo htmlspecialchars($wishlistItem['image']); ?>" alt="<?php echo htmlspecialchars($wishlistItem['name']); ?>">
                        <?php else: ?>
                            <div class="wishlist-edit-image-placeholder">
                                <i class="fas fa-image"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Form Section -->
                    <div class="wishlist-edit-info">
                        <h2 class="wishlist-edit-name"><?php echo htmlspecialchars($wishlistItem['name']); ?></h2>
                        
                        <div class="wishlist-edit-price">
                            <?php echo number_format($wishlistItem['price'] ?? 0, 0, ',', '.'); ?> VNĐ
                        </div>
                        
                        <form method="POST" class="wishlist-edit-form-main">
                            <div class="form-group">
                                <label for="notes">Ghi chú cá nhân:</label>
                                <textarea id="notes" name="notes" rows="4" placeholder="Thêm ghi chú cho sản phẩm này..."><?php echo htmlspecialchars($wishlistItem['notes'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="wishlist-edit-actions">
                                <button type="submit" class="wishlist-btn wishlist-btn-primary">
                                    <i class="fas fa-save"></i>
                                    Lưu ghi chú
                                </button>
                                <a href="?page=users&module=wishlist&action=view&id=<?php echo $wishlistItem['id']; ?>" class="wishlist-btn wishlist-btn-secondary">
                                    <i class="fas fa-eye"></i>
                                    Xem chi tiết
                                </a>
                                <a href="?page=users&module=wishlist&action=delete&id=<?php echo $wishlistItem['id']; ?>" class="wishlist-btn wishlist-btn-danger">
                                    <i class="fas fa-trash"></i>
                                    Xóa khỏi yêu thích
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.wishlist-edit-form {
    background: white;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    overflow: hidden;
}

.wishlist-edit-grid {
    display: grid;
    grid-template-columns: 350px 1fr;
    gap: 40px;
}

@media (max-width: 768px) {
    .wishlist-edit-grid {
        grid-template-columns: 1fr;
    }
}

.wishlist-edit-image {
    background: #f9fafb;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 250px;
}

.wishlist-edit-image img {
    width: 100%;
    height: 250px;
    object-fit: cover;
}

.wishlist-edit-image-placeholder {
    width: 100%;
    height: 250px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 64px;
    color: #d1d5db;
}

.wishlist-edit-info {
    padding: 30px;
}

.wishlist-edit-name {
    font-size: 22px;
    font-weight: 600;
    color: #111827;
    margin: 0 0 16px 0;
}

.wishlist-edit-price {
    font-size: 20px;
    font-weight: 600;
    color: #dc2626;
    margin-bottom: 24px;
}

.wishlist-edit-form-main {
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

.form-group textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    font-family: inherit;
    resize: vertical;
}

.form-group textarea:focus {
    outline: none;
    border-color: #356DF1;
}

.wishlist-edit-actions {
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
