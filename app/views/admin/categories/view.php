<?php
// Load ViewDataService and ErrorHandler
require_once __DIR__ . '/../../../services/ViewDataService.php';
require_once __DIR__ . '/../../../services/ErrorHandler.php';

try {
    $viewDataService = new ViewDataService();
    
    // Get category ID from URL
    $category_id = (int)($_GET['id'] ?? 0);
    
    // Get category details using ViewDataService
    $categoryData = $viewDataService->getAdminCategoryDetailsData($category_id);
    $category = $categoryData['category'];
    $category_products = $categoryData['products'];
    
    // Redirect if category not found
    if (!$category) {
        header('Location: ?page=admin&module=categories&error=not_found');
        exit;
    }
    
} catch (Exception $e) {
    ErrorHandler::logError('Admin Categories View', $e->getMessage());
    header('Location: ?page=admin&module=categories&error=system_error');
    exit;
}

// Format date function
function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}

// Format price function
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' VNĐ';
}
?>

<div class="categories-page categories-view-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-eye"></i>
                Chi Tiết Danh Mục
            </h1>
            <p class="page-description">Xem thông tin chi tiết danh mục: <?= htmlspecialchars($category['name']) ?></p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=categories&action=edit&id=<?= $category['id'] ?>" class="btn btn-warning">
                <i class="fas fa-edit"></i>
                Chỉnh sửa
            </a>
            <button type="button" class="btn btn-danger delete-btn" 
                    data-id="<?= $category['id'] ?>" data-name="<?= htmlspecialchars($category['name']) ?>">
                <i class="fas fa-trash"></i>
                Xóa
            </button>
            <a href="?page=admin&module=categories" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Quay lại danh sách
            </a>
        </div>
    </div>

    <!-- Category Details -->
    <div class="details-container">
        <div class="details-grid">
            <!-- Left Column -->
            <div class="details-column">
                <!-- Basic Information -->
                <div class="details-section">
                    <h3 class="section-title">Thông Tin Cơ Bản</h3>
                    
                    <div class="details-content">
                        <div class="detail-row">
                            <label>ID:</label>
                            <span class="detail-value"><?= $category['id'] ?></span>
                        </div>
                        
                        <div class="detail-row">
                            <label>Tên danh mục:</label>
                            <span class="detail-value"><?= htmlspecialchars($category['name']) ?></span>
                        </div>
                        
                        <div class="detail-row">
                            <label>Slug:</label>
                            <span class="detail-value">
                                <code><?= htmlspecialchars($category['slug']) ?></code>
                            </span>
                        </div>
                        
                        <div class="detail-row">
                            <label>Trạng thái:</label>
                            <span class="detail-value">
                                <span class="status-badge status-<?= $category['status'] ?>">
                                    <?= $category['status'] == 'active' ? 'Hoạt động' : 'Không hoạt động' ?>
                                </span>
                            </span>
                        </div>
                        
                        <div class="detail-row">
                            <label>Số sản phẩm:</label>
                            <span class="detail-value">
                                <span class="product-count-badge">
                                    <?= $category['products_count'] ?? 0 ?> sản phẩm
                                </span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="details-section">
                    <h3 class="section-title">Mô Tả</h3>
                    
                    <div class="details-content">
                        <div class="description-content">
                            <?= nl2br(htmlspecialchars($category['description'])) ?>
                        </div>
                    </div>
                </div>

                <!-- SEO Information -->
                <div class="details-section">
                    <h3 class="section-title">Thông Tin SEO</h3>
                    
                    <div class="details-content">
                        <div class="detail-row">
                            <label>Tiêu đề SEO:</label>
                            <span class="detail-value">
                                <?= htmlspecialchars($category['meta_title'] ?? 'Chưa thiết lập') ?>
                            </span>
                        </div>
                        
                        <div class="detail-row">
                            <label>Mô tả SEO:</label>
                            <span class="detail-value">
                                <?= htmlspecialchars($category['meta_description'] ?? 'Chưa thiết lập') ?>
                            </span>
                        </div>
                        
                        <div class="detail-row">
                            <label>Từ khóa:</label>
                            <span class="detail-value">
                                <?= htmlspecialchars($category['keywords'] ?? 'Chưa thiết lập') ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="details-column">
                <!-- Category Image -->
                <div class="details-section">
                    <h3 class="section-title">Hình Ảnh</h3>
                    
                    <div class="details-content">
                        <div class="category-image-display">
                            <?php if (!empty($category['image'])): ?>
                                <img src="<?= htmlspecialchars($category['image']) ?>" 
                                     alt="<?= htmlspecialchars($category['name']) ?>"
                                     class="category-image">
                            <?php else: ?>
                                <div class="no-image">
                                    <i class="fas fa-image"></i>
                                    <p>Chưa có hình ảnh</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Display Settings -->
                <div class="details-section">
                    <h3 class="section-title">Cài Đặt Hiển Thị</h3>
                    
                    <div class="details-content">
                        <div class="detail-row">
                            <label>Thứ tự sắp xếp:</label>
                            <span class="detail-value"><?= $category['sort_order'] ?? 0 ?></span>
                        </div>
                        
                        <div class="detail-row">
                            <label>Hiển thị trong menu:</label>
                            <span class="detail-value">
                                <span class="badge <?= ($category['show_in_menu'] ?? 1) ? 'badge-success' : 'badge-secondary' ?>">
                                    <?= ($category['show_in_menu'] ?? 1) ? 'Có' : 'Không' ?>
                                </span>
                            </span>
                        </div>
                        
                        <div class="detail-row">
                            <label>Danh mục nổi bật:</label>
                            <span class="detail-value">
                                <span class="badge <?= ($category['featured'] ?? 0) ? 'badge-warning' : 'badge-secondary' ?>">
                                    <?= ($category['featured'] ?? 0) ? 'Có' : 'Không' ?>
                                </span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Timestamps -->
                <div class="details-section">
                    <h3 class="section-title">Thời Gian</h3>
                    
                    <div class="details-content">
                        <div class="detail-row">
                            <label>Ngày tạo:</label>
                            <span class="detail-value"><?= formatDate($category['created_at']) ?></span>
                        </div>
                        
                        <div class="detail-row">
                            <label>Cập nhật cuối:</label>
                            <span class="detail-value">
                                <?= formatDate($category['updated_at'] ?? $category['created_at']) ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="details-section">
                    <h3 class="section-title">Thao Tác Nhanh</h3>
                    
                    <div class="details-content">
                        <div class="quick-actions">
                            <a href="?page=admin&module=categories&action=edit&id=<?= $category['id'] ?>" 
                               class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                                Chỉnh sửa
                            </a>
                            
                            <a href="?page=admin&module=products&category=<?= $category['id'] ?>" 
                               class="btn btn-sm btn-info">
                                <i class="fas fa-box"></i>
                                Xem sản phẩm
                            </a>
                            
                            <button type="button" class="btn btn-sm btn-success" onclick="duplicateCategory()">
                                <i class="fas fa-copy"></i>
                                Nhân bản
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Products in Category -->
    <?php if (!empty($category_products)): ?>
        <div class="related-section">
            <h3 class="section-title">
                <i class="fas fa-box"></i>
                Sản Phẩm Trong Danh Mục (<?= count($category_products) ?>)
            </h3>
            
            <div class="products-grid">
                <?php foreach (array_slice($category_products, 0, 6) as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?= $product['image'] ?>" alt="<?= htmlspecialchars($product['name']) ?>"
                                 onerror="this.src='<?php echo asset_url('images/placeholder.jpg'); ?>'"">
                        </div>
                        <div class="product-info">
                            <h4 class="product-name"><?= htmlspecialchars($product['name']) ?></h4>
                            <p class="product-price"><?= formatPrice($product['price']) ?></p>
                            <div class="product-meta">
                                <span class="stock-info">Tồn: <?= $product['stock'] ?></span>
                                <span class="status-badge status-<?= $product['status'] ?>">
                                    <?= $product['status'] == 'active' ? 'Hoạt động' : 'Không hoạt động' ?>
                                </span>
                            </div>
                            <div class="product-actions">
                                <a href="?page=admin&module=products&action=view&id=<?= $product['id'] ?>" 
                                   class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                    Xem
                                </a>
                                <a href="?page=admin&module=products&action=edit&id=<?= $product['id'] ?>" 
                                   class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                    Sửa
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (count($category_products) > 6): ?>
                <div class="view-all-products">
                    <a href="?page=admin&module=products&category=<?= $category['id'] ?>" class="btn btn-outline">
                        <i class="fas fa-arrow-right"></i>
                        Xem tất cả <?= count($category_products) ?> sản phẩm
                    </a>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="related-section">
            <h3 class="section-title">
                <i class="fas fa-box"></i>
                Sản Phẩm Trong Danh Mục
            </h3>
            
            <div class="no-products">
                <i class="fas fa-inbox"></i>
                <p>Chưa có sản phẩm nào trong danh mục này</p>
                <a href="?page=admin&module=products&action=add&category=<?= $category['id'] ?>" 
                   class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Thêm sản phẩm đầu tiên
                </a>
            </div>
        </div>
    <?php endif; ?>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Xác nhận xóa</h3>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa danh mục <strong id="deleteCategoryName"></strong>?</p>
                <p class="text-danger">Hành động này không thể hoàn tác!</p>
                <?php if (!empty($category_products)): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Danh mục này có <?= count($category_products) ?> sản phẩm. Bạn cần xử lý các sản phẩm này trước khi xóa danh mục.
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelDelete">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDelete" 
                        <?= !empty($category_products) ? 'disabled' : '' ?>>
                    Xóa
                </button>
            </div>
        </div>
    </div>
</div>

