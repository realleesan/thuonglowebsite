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
                <div class="stat-number-simple"><?= array_sum(array_column($products, 'sales_count')) ?></div>
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
                <div class="stat-number-simple"><?= number_format(array_sum(array_column($products, 'view_count'))) ?></div>
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
                        <i class="fas fa-search"></i>
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
                                 onerror="this.src='assets/images/placeholder.jpg'">
                            <span class="product-type-badge"><?= getTypeLabel($product['type'] ?? 'data_nguon_hang') ?></span>
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
                                <span><?= $product['quota'] ?? 100 ?> lần tải</span>
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

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Xác nhận xóa</h3>
            <span class="modal-close" onclick="closeDeleteModal()">&times;</span>
        </div>
        <div class="modal-body">
            <p>Bạn có chắc chắn muốn xóa data "<strong id="deleteProductName"></strong>"?</p>
            <p class="text-warning">Hành động này không thể hoàn tác!</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Hủy</button>
            <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Xóa</button>
        </div>
    </div>
</div>

<style>
/* Stats Row */
.stats-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.stat-card-simple {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px;
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
}

.stat-icon-simple {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    font-size: 20px;
}

.stat-blue { background: #dbeafe; color: #1e40af; }
.stat-green { background: #d1fae5; color: #065f46; }
.stat-orange { background: #fef3c7; color: #92400e; }
.stat-purple { background: #f3e8ff; color: #7c3aed; }

.stat-content-simple {
    flex: 1;
}

.stat-number-simple {
    font-size: 24px;
    font-weight: 700;
    color: #111827;
}

.stat-label-simple {
    font-size: 13px;
    color: #6b7280;
}

/* Products Grid */
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px;
    margin-bottom: 24px;
}

.product-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    overflow: hidden;
    transition: all 0.3s ease;
}

.product-card:hover {
    border-color: #356DF1;
    box-shadow: 0 4px 12px rgba(53, 109, 241, 0.15);
    transform: translateY(-2px);
}

.product-card-header {
    position: relative;
    height: 160px;
    overflow: hidden;
    background: #f3f4f6;
}

.product-image-wrapper {
    width: 100%;
    height: 100%;
}

.product-image-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-type-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    padding: 4px 10px;
    background: rgba(53, 109, 241, 0.9);
    color: #fff;
    font-size: 11px;
    font-weight: 600;
    border-radius: 6px;
    text-transform: uppercase;
}

.product-card-body {
    padding: 16px;
}

.product-category {
    font-size: 12px;
    color: #6b7280;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.product-category i {
    color: #356DF1;
}

.product-title {
    font-size: 16px;
    font-weight: 600;
    color: #111827;
    margin: 0 0 8px 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.product-description {
    font-size: 13px;
    color: #6b7280;
    margin: 0 0 12px 0;
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.product-data-info {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
    margin-bottom: 12px;
    padding: 10px;
    background: #f9fafb;
    border-radius: 8px;
}

.data-info-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: #374151;
}

.data-info-item i {
    color: #356DF1;
    width: 14px;
}

.product-supplier {
    font-size: 12px;
    color: #6b7280;
    display: flex;
    align-items: center;
    gap: 6px;
}

.product-supplier i {
    color: #9ca3af;
}

.product-card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    background: #f9fafb;
    border-top: 1px solid #e5e7eb;
}

.product-price {
    display: flex;
    align-items: baseline;
    gap: 8px;
}

.price-current {
    font-size: 18px;
    font-weight: 700;
    color: #dc2626;
}

.price-original {
    font-size: 13px;
    color: #9ca3af;
    text-decoration: line-through;
}

.product-stats-mini {
    display: flex;
    gap: 12px;
}

.stat-mini {
    font-size: 12px;
    color: #6b7280;
    display: flex;
    align-items: center;
    gap: 4px;
}

.stat-mini i {
    font-size: 11px;
}

.product-status {
    flex-shrink: 0;
}

.product-card-actions {
    display: flex;
    justify-content: center;
    gap: 8px;
    padding: 12px;
    border-top: 1px solid #e5e7eb;
}

/* Empty State */
.empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
}

.empty-state i {
    font-size: 64px;
    color: #d1d5db;
    margin-bottom: 16px;
}

.empty-state h3 {
    font-size: 20px;
    color: #374151;
    margin: 0 0 8px 0;
}

.empty-state p {
    color: #6b7280;
    margin: 0 0 20px 0;
}

/* Search Input */
.search-input-wrapper {
    position: relative;
}

.search-input-wrapper i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
}

.search-input-wrapper input {
    padding-left: 40px;
}

/* Responsive */
@media (max-width: 1024px) {
    .stats-row {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 640px) {
    .stats-row {
        grid-template-columns: 1fr;
    }
    
    .products-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Delete Modal Functions
document.addEventListener('DOMContentLoaded', function() {
    const deleteBtns = document.querySelectorAll('.delete-btn');
    const deleteModal = document.getElementById('deleteModal');
    const deleteProductName = document.getElementById('deleteProductName');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    
    let currentDeleteId = null;
    
    deleteBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            currentDeleteId = this.dataset.id;
            deleteProductName.textContent = this.dataset.name;
            deleteModal.style.display = 'flex';
        });
    });
    
    window.closeDeleteModal = function() {
        deleteModal.style.display = 'none';
        currentDeleteId = null;
    };
    
    confirmDeleteBtn.addEventListener('click', function() {
        if (currentDeleteId) {
            window.location.href = '?page=admin&module=products&action=delete&id=' + currentDeleteId;
        }
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target === deleteModal) {
            closeDeleteModal();
        }
    });
});
</script>
