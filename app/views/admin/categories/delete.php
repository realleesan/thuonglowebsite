<?php
/**
 * Admin Categories Delete
 * Sử dụng AdminService thông qua ServiceManager
 */

// Khởi tạo View & ServiceManager
require_once __DIR__ . '/../../../../core/view_init.php';

// Chọn service admin (được inject từ index.php)
$service = isset($currentService) ? $currentService : ($adminService ?? null);

try {
    // Get category ID from URL
    $category_id = (int)($_GET['id'] ?? 0);
    
    if (!$category_id) {
        header('Location: ?page=admin&module=categories&error=not_found');
        exit;
    }
    
    // Get category data from service
    $categoryData = $service->getCategoryDetailsData($category_id);
    $category = $categoryData['category'];
    $category_products = $categoryData['products'];
    
    // Redirect if category not found
    if (!$category) {
        header('Location: ?page=admin&module=categories&error=not_found');
        exit;
    }
    
    // Get all categories for move modal
    $allCategoriesData = $service->getActiveCategoriesForDropdown();
    $allCategories = $allCategoriesData['categories'];
    
} catch (Exception $e) {
    $errorHandler->logError('Admin Categories Delete Error', $e);
    header('Location: ?page=admin&module=categories&error=1');
    exit;
}

$has_products = !empty($category_products);
$errors = [];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $confirm = $_POST['confirm'] ?? '';
    
    if ($confirm !== 'DELETE') {
        $errors[] = 'Vui lòng nhập "DELETE" để xác nhận xóa';
    }
    
    if ($has_products) {
        $errors[] = 'Không thể xóa danh mục có sản phẩm. Vui lòng xóa hoặc chuyển các sản phẩm sang danh mục khác trước.';
    }
    
    // If no errors, delete from database
    if (empty($errors)) {
        if ($categoriesModel->delete($category_id)) {
            $success = true;
            header('Location: ?page=admin&module=categories&success=deleted');
            exit;
        } else {
            $errors[] = 'Có lỗi xảy ra khi xóa danh mục';
        }
    }
}
?>

<div class="categories-page categories-delete-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-trash"></i>
                Xóa Danh Mục
            </h1>
            <p class="page-description">Xóa danh mục: <?= htmlspecialchars($category['name']) ?></p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=categories&action=view&id=<?= $category['id'] ?>" class="btn btn-info">
                <i class="fas fa-eye"></i>
                Xem chi tiết
            </a>
            <a href="?page=admin&module=categories" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Quay lại danh sách
            </a>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            Xóa danh mục thành công!
            <div class="alert-actions">
                <a href="?page=admin&module=categories" class="btn btn-sm btn-primary">
                    Quay lại danh sách
                </a>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i>
            <ul class="error-list">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Delete Warning -->
    <div class="delete-warning-container">
        <div class="delete-warning">
            <div class="warning-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            
            <div class="warning-content">
                <h3>Cảnh báo: Hành động không thể hoàn tác!</h3>
                <p>Bạn đang chuẩn bị xóa danh mục <strong><?= htmlspecialchars($category['name']) ?></strong>.</p>
                
                <?php if ($has_products): ?>
                    <div class="products-warning">
                        <h4><i class="fas fa-box"></i> Danh mục này có <?= count($category_products) ?> sản phẩm:</h4>
                        <ul class="products-list">
                            <?php foreach (array_slice($category_products, 0, 5) as $product): ?>
                                <li>
                                    <a href="?page=admin&module=products&action=view&id=<?= $product['id'] ?>">
                                        <?= htmlspecialchars($product['name']) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                            <?php if (count($category_products) > 5): ?>
                                <li>... và <?= count($category_products) - 5 ?> sản phẩm khác</li>
                            <?php endif; ?>
                        </ul>
                        
                        <div class="products-actions">
                            <p><strong>Bạn cần thực hiện một trong các hành động sau trước khi xóa danh mục:</strong></p>
                            <ul>
                                <li>Xóa tất cả sản phẩm trong danh mục này</li>
                                <li>Chuyển các sản phẩm sang danh mục khác</li>
                            </ul>
                            
                            <div class="action-buttons">
                                <a href="?page=admin&module=products&category=<?= $category['id'] ?>" 
                                   class="btn btn-info">
                                    <i class="fas fa-box"></i>
                                    Quản lý sản phẩm
                                </a>
                                <button type="button" class="btn btn-warning" onclick="showMoveProductsModal()">
                                    <i class="fas fa-arrows-alt"></i>
                                    Chuyển sản phẩm
                                </button>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="safe-delete">
                        <p><i class="fas fa-check-circle text-success"></i> Danh mục này không có sản phẩm nào, có thể xóa an toàn.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Category Information -->
    <div class="category-info-container">
        <h3>Thông Tin Danh Mục Sẽ Bị Xóa</h3>
        
        <div class="category-info-grid">
            <div class="info-column">
                <div class="info-item">
                    <label>ID:</label>
                    <span><?= $category['id'] ?></span>
                </div>
                
                <div class="info-item">
                    <label>Tên:</label>
                    <span><?= htmlspecialchars($category['name']) ?></span>
                </div>
                
                <div class="info-item">
                    <label>Slug:</label>
                    <span><code><?= htmlspecialchars($category['slug']) ?></code></span>
                </div>
                
                <div class="info-item">
                    <label>Trạng thái:</label>
                    <span class="status-badge status-<?= $category['status'] ?>">
                        <?= $category['status'] == 'active' ? 'Hoạt động' : 'Không hoạt động' ?>
                    </span>
                </div>
            </div>
            
            <div class="info-column">
                <div class="info-item">
                    <label>Số sản phẩm:</label>
                    <span class="product-count-badge">
                        <?= count($category_products) ?> sản phẩm
                    </span>
                </div>
                
                <div class="info-item">
                    <label>Ngày tạo:</label>
                    <span><?= date('d/m/Y H:i', strtotime($category['created_at'])) ?></span>
                </div>
                
                <div class="info-item">
                    <label>Cập nhật cuối:</label>
                    <span><?= date('d/m/Y H:i', strtotime($category['updated_at'] ?? $category['created_at'])) ?></span>
                </div>
            </div>
        </div>
        
        <div class="description-preview">
            <label>Mô tả:</label>
            <div class="description-content">
                <?= nl2br(htmlspecialchars(substr($category['description'], 0, 200))) ?>
                <?= strlen($category['description']) > 200 ? '...' : '' ?>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Form -->
    <?php if (!$success): ?>
        <div class="delete-form-container">
            <form method="POST" class="delete-form">
                <h3>Xác Nhận Xóa</h3>
                
                <div class="confirmation-input">
                    <label for="confirm">
                        Để xác nhận xóa, vui lòng nhập <strong>DELETE</strong> vào ô bên dưới:
                    </label>
                    <input type="text" id="confirm" name="confirm" 
                           placeholder="Nhập DELETE để xác nhận" 
                           autocomplete="off" required>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-danger" 
                            <?= $has_products ? 'disabled' : '' ?>>
                        <i class="fas fa-trash"></i>
                        <?= $has_products ? 'Không thể xóa' : 'Xóa Danh Mục' ?>
                    </button>
                    <a href="?page=admin&module=categories&action=view&id=<?= $category['id'] ?>" 
                       class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        Hủy
                    </a>
                </div>
                
                <?php if ($has_products): ?>
                    <p class="form-note">
                        <i class="fas fa-info-circle"></i>
                        Không thể xóa danh mục có sản phẩm. Vui lòng xử lý các sản phẩm trước.
                    </p>
                <?php endif; ?>
            </form>
        </div>
    <?php endif; ?>

    <!-- Move Products Modal -->
    <div id="moveProductsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Chuyển Sản Phẩm Sang Danh Mục Khác</h3>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <p>Chọn danh mục đích để chuyển <?= count($category_products) ?> sản phẩm:</p>
                
                <div class="form-group">
                    <label for="target_category">Danh mục đích:</label>
                    <select id="target_category" class="form-control">
                        <option value="">Chọn danh mục</option>
                        <?php foreach ($allCategories as $cat): ?>
                            <?php if ($cat['id'] != $category['id']): ?>
                                <option value="<?= $cat['id'] ?>">
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="products-preview">
                    <h4>Sản phẩm sẽ được chuyển:</h4>
                    <ul>
                        <?php foreach ($category_products as $product): ?>
                            <li><?= htmlspecialchars($product['name']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="moveProducts()">
                    <i class="fas fa-arrows-alt"></i>
                    Chuyển Sản Phẩm
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeMoveProductsModal()">
                    Hủy
                </button>
            </div>
        </div>
    </div>
</div>

