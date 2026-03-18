<?php
/**
 * Admin Products Index - Tái cấu trúc cho sản phẩm số (Data Nguồn Hàng)
 * Designed for digital products / data products
 */

// Chọn service admin (được inject từ index.php)
$service = isset($currentService) ? $currentService : ($adminService ?? null);

if (!$service) {
    throw new Exception('AdminService is not available');
}

// Get parameters
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$status_filter = $_GET['status'] ?? '';
$current_page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 15;

// Build filters
$filters = [];
if (!empty($search)) {
    $filters['search'] = $search;
}
if (!empty($category_filter)) {
    $filters['category_id'] = $category_filter;
}
if (!empty($status_filter)) {
    $filters['status'] = $status_filter;
}

// Initialize data variables
$products = [];
$categories = [];
$pagination = [];
$total = 0;
$showErrorMessage = false;
$errorMessage = '';

try {
    // Get admin products data từ AdminService
    $productsData = $service->getProductsData($current_page, $per_page, $filters);
    
    // Extract data
    $products = $productsData['products'] ?? [];
    $categories = $productsData['categories'] ?? [];
    $pagination = $productsData['pagination'] ?? [];
    $total = $productsData['total'] ?? 0;
    
} catch (Exception $e) {
    // Handle errors gracefully
    $result = $errorHandler->handleViewError($e, 'admin_products', []);
    $showErrorMessage = true;
    $errorMessage = $result['message'];
    
    // Use empty state
    $products = [];
    $categories = [];
    $pagination = ['current_page' => 1, 'total' => 0, 'last_page' => 1];
    $total = 0;
}

// Helper functions
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' VNĐ';
}

function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

function getTypeLabel($type) {
    $types = [
        'data_nguon_hang' => 'Data Nguồn Hàng',
        'khoa_hoc' => 'Khóa Học',
        'tool' => 'Công Cụ',
        'dich_vu' => 'Dịch Vụ',
        'van_chuyen' => 'Vận Chuyển'
    ];
    return $types[$type] ?? $type;
}
?>

<div class="products-page products-index-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-database"></i>
                Quản Lý Data Nguồn Hàng
            </h1>
            <p class="page-description">Quản lý danh sách data nguồn hàng của hệ thống</p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=products&action=add" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Thêm Data Mới
            </a>
        </div>
    </div>

    <!-- Error Message -->
    <?php if ($showErrorMessage): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <span><?php echo htmlspecialchars($errorMessage); ?></span>
    </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="stats-row">
        <div class="stat-card-simple">
            <div class="stat-icon-simple stat-blue">
                <i class="fas fa-database"></i>
            </div>
            <div class="stat-content-simple">
                <div class="stat-number-simple"><?= $total ?></div>
                <div class="stat-label-simple">Tổng Data</div>
            </div>
        </div>
        <div class="stat-card-simple">
            <div class="stat-icon-simple stat-green">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content-simple">
                <div class="stat-number-simple"><?= is_array($products) && !empty($products) ? array_sum(array_column($products, 'sales_count')) : 0 ?></div>
                <div class="stat-label-simple">Đã Bán</div>
            </div>
        </div>
        <div class="stat-card-simple">
            <div class="stat-icon-simple stat-orange">
                <i class="fas fa-layer-group"></i>
            </div>
            <div class="stat-content-simple">
                <div class="stat-number-simple"><?= count($categories) ?></div>
                <div class="stat-label-simple">Danh Mục</div>
            </div>
        </div>
        <div class="stat-card-simple">
            <div class="stat-icon-simple stat-purple">
                <i class="fas fa-eye"></i>
            </div>
            <div class="stat-content-simple">
                <div class="stat-number-simple"><?= is_array($products) && !empty($products) ? number_format(array_sum(array_column($products, 'view_count'))) : 0 ?></div>
                <div class="stat-label-simple">Lượt Xem</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-section">
        <form method="GET" class="filters-form">
            <input type="hidden" name="page" value="admin">
            <input type="hidden" name="module" value="products">
            
            <div class="filter-group">
                <div class="filter-item filter-search">
                    <label for="search">Tìm kiếm:</label>
                    <div class="search-input-wrapper">
                        <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" 
                               placeholder="Tên data, mô tả...">
                    </div>
                </div>
                
                <div class="filter-item">
                    <label for="category">Danh mục:</label>
                    <select id="category" name="category">
                        <option value="">Tất cả danh mục</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>" <?= $category_filter == $category['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-item">
                    <label for="status">Trạng thái:</label>
                    <select id="status" name="status">
                        <option value="">Tất cả trạng thái</option>
                        <option value="active" <?= $status_filter == 'active' ? 'selected' : '' ?>>Hoạt động</option>
                        <option value="inactive" <?= $status_filter == 'inactive' ? 'selected' : '' ?>>Không hoạt động</option>
                    </select>
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-filter"></i>
                        Lọc
                    </button>
                    <a href="?page=admin&module=products" class="btn btn-outline">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Results Info -->
    <div class="results-info">
        <span class="results-count">
            Hiển thị <?= count($products) ?> / <?= $total ?> data
        </span>
    </div>

    <!-- Products Grid -->
    <div class="products-grid">
        <?php if (empty($products)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>Không tìm thấy data nào</h3>
                <p>Hãy thêm data mới hoặc thay đổi bộ lọc</p>
                <a href="?page=admin&module=products&action=add" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Thêm Data Mới
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <div class="product-card-header">
                        <div class="product-image-wrapper">
                            <img src="<?= htmlspecialchars($product['image'] ?? '') ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>" 
                                 onerror="this.onerror=null; this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><rect fill=%22%23f3f4f6%22 width=%22100%22 height=%22100%22/><text x=%2250%22 y=%2250%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%239ca3af%22>No Image</text></svg>'">
                        </div>
                    </div>
                    <div class="product-card-body">
                        <div class="product-category">
                            <i class="fas fa-folder"></i>
                            <?= htmlspecialchars($product['category_name'] ?? 'N/A') ?>
                        </div>
                        <h3 class="product-title" title="<?= htmlspecialchars($product['name']) ?>">
                            <?= htmlspecialchars($product['name']) ?>
                        </h3>
                        <p class="product-description">
                            <?= htmlspecialchars(mb_substr($product['description'] ?? '', 0, 100)) ?>...
                        </p>
                        
                        <div class="product-data-info">
                            <div class="data-info-item">
                                <i class="fas fa-list"></i>
                                <span><?= number_format($product['record_count'] ?? 0) ?> records</span>
                            </div>
                            <div class="data-info-item">
                                <i class="fas fa-hdd"></i>
                                <span><?= htmlspecialchars($product['data_size'] ?? '-') ?></span>
                            </div>
                            <div class="data-info-item">
                                <i class="fas fa-file-alt"></i>
                                <span><?= htmlspecialchars($product['data_format'] ?? '-') ?></span>
                            </div>
                            <div class="data-info-item">
                                <i class="fas fa-download"></i>
                                <span><?= $product['quota'] ?? 100 ?> quota</span>
                            </div>
                        </div>
                        
                        <div class="product-supplier">
                            <i class="fas fa-building"></i>
                            <?= htmlspecialchars(mb_substr($product['supplier_name'] ?? 'N/A', 0, 25)) ?>
                        </div>
                    </div>
                    <div class="product-card-footer">
                        <div class="product-price">
                            <?php if (!empty($product['sale_price']) && $product['sale_price'] < $product['price']): ?>
                                <span class="price-current"><?= formatPrice($product['sale_price']) ?></span>
                                <span class="price-original"><?= formatPrice($product['price']) ?></span>
                            <?php else: ?>
                                <span class="price-current"><?= formatPrice($product['price'] ?? 0) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="product-stats-mini">
                            <span class="stat-mini" title="Đã bán">
                                <i class="fas fa-shopping-cart"></i> <?= $product['sales_count'] ?? 0 ?>
                            </span>
                            <span class="stat-mini" title="Lượt xem">
                                <i class="fas fa-eye"></i> <?= $product['view_count'] ?? 0 ?>
                            </span>
                        </div>
                        <div class="product-status">
                            <span class="status-badge <?= $product['status'] == 'active' ? 'status-active' : 'status-inactive' ?>">
                                <?= $product['status'] == 'active' ? 'Hoạt động' : 'Không hoạt động' ?>
                            </span>
                        </div>
                    </div>
                    <div class="product-card-actions">
                        <a href="?page=admin&module=products&action=view&id=<?= $product['id'] ?>" 
                           class="btn btn-sm btn-info" title="Xem chi tiết">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="?page=admin&module=products&action=edit&id=<?= $product['id'] ?>" 
                           class="btn btn-sm btn-warning" title="Chỉnh sửa">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-danger delete-btn" 
                                data-id="<?= $product['id'] ?>" data-name="<?= htmlspecialchars($product['name']) ?>" 
                                onclick="showProductDeleteModal('<?= $product['id'] ?>', '<?= htmlspecialchars($product['name']) ?>')"
                                title="Xóa">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if (($pagination['last_page'] ?? 1) > 1): ?>
        <div class="pagination-container">
            <div class="pagination">
                <?php if ($current_page > 1): ?>
                    <a href="?page=admin&module=products&<?= http_build_query(array_merge($_GET, ['page' => $current_page - 1])) ?>" 
                       class="pagination-btn">
                        <i class="fas fa-chevron-left"></i>
                        Trước
                    </a>
                <?php endif; ?>

                <?php
                $total_pages = $pagination['last_page'] ?? 1;
                $start_page = max(1, $current_page - 2);
                $end_page = min($total_pages, $current_page + 2);
                
                for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <a href="?page=admin&module=products&<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
                       class="pagination-number <?= $i == $current_page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($current_page < $total_pages): ?>
                    <a href="?page=admin&module=products&<?= http_build_query(array_merge($_GET, ['page' => $current_page + 1])) ?>" 
                       class="pagination-btn">
                        Sau
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
            
            <div class="pagination-info">
                Trang <?= $current_page ?> / <?= $total_pages ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Delete Confirmation Modal - New Implementation -->
<div id="productDeleteModal" style="display: none;">
    <div class="product-modal-overlay"></div>
    <div class="product-modal-container">
        <div class="product-modal-header">
            <h3>Xác nhận xóa</h3>
            <button class="product-modal-close" onclick="closeProductDeleteModal()">&times;</button>
        </div>
        <div class="product-modal-body">
            <p>Bạn có chắc chắn muốn xóa data "<strong id="productDeleteName"></strong>"?</p>
            <p class="product-modal-warning">Hành động này không thể hoàn tác!</p>
        </div>
        <div class="product-modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeProductDeleteModal()">Hủy</button>
            <button type="button" class="btn btn-danger" id="productConfirmDeleteBtn">Xóa</button>
        </div>
    </div>
</div>

<style>
#productDeleteModal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    z-index: 999999;
}

.product-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
}

.product-modal-container {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    border-radius: 12px;
    width: 90%;
    max-width: 500px;
}

.product-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #e5e7eb;
}

.product-modal-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #111827;
}

.product-modal-close {
    background: none;
    border: none;
    font-size: 24px;
    color: #9ca3af;
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
}

.product-modal-close:hover {
    color: #374151;
    background: #f3f4f6;
}

.product-modal-body {
    padding: 20px;
}

.product-modal-body p {
    margin: 0 0 8px 0;
    color: #374151;
}

.product-modal-warning {
    color: #dc2626 !important;
    font-size: 13px;
    font-weight: 500;
}

.product-modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding: 16px 20px;
    border-top: 1px solid #e5e7eb;
    background: #f9fafb;
    border-radius: 0 0 12px 12px;
}
</style>

<script>
window.showProductDeleteModal = function(id, name) {
    const modal = document.getElementById('productDeleteModal');
    const nameElement = document.getElementById('productDeleteName');
    
    if (modal) {
        if (nameElement) {
            nameElement.textContent = name || 'sản phẩm này';
        }
        modal.style.display = 'block';
        modal.dataset.deleteId = id;
        document.body.style.overflow = 'hidden';
    }
};

window.closeProductDeleteModal = function() {
    const modal = document.getElementById('productDeleteModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
        delete modal.dataset.deleteId;
    }
};

// Handle confirm delete
document.addEventListener('click', function(e) {
    if (e.target.id === 'productConfirmDeleteBtn') {
        const modal = document.getElementById('productDeleteModal');
        const deleteId = modal ? modal.dataset.deleteId : null;
        if (deleteId) {
            window.location.href = '?page=admin&module=products&action=delete&id=' + deleteId;
        }
    }
});

// Close on overlay click
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('product-modal-overlay')) {
        closeProductDeleteModal();
    }
});

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('productDeleteModal');
        if (modal && modal.style.display === 'block') {
            closeProductDeleteModal();
        }
    }
});
</script>