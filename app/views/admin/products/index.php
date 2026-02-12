<?php
/**
 * Admin Products Index - Dynamic Version
 * Sử dụng AdminService thông qua ServiceManager
 */

// Khởi tạo View & ServiceManager
require_once __DIR__ . '/../../../../core/view_init.php';

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
$per_page = 10;

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
    return date('d/m/Y H:i', strtotime($date));
}
?>

<div class="products-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-box"></i>
                Quản Lý Sản Phẩm
            </h1>
            <p class="page-description">Quản lý danh sách sản phẩm của hệ thống</p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=products&action=add" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Thêm Sản Phẩm
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

    <!-- Filters -->
    <div class="filters-section">
        <form method="GET" class="filters-form">
            <input type="hidden" name="page" value="admin">
            <input type="hidden" name="module" value="products">
            
            <div class="filter-group">
                <div class="filter-item">
                    <label for="search">Tìm kiếm:</label>
                    <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Tên sản phẩm, mô tả...">
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
                        <i class="fas fa-search"></i>
                        Lọc
                    </button>
                    <a href="?page=admin&module=products" class="btn btn-outline">
                        <i class="fas fa-times"></i>
                        Xóa bộ lọc
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Results Info -->
    <div class="results-info">
        <span class="results-count">
            Hiển thị <?= count($products) ?> trong tổng số <?= $total ?> sản phẩm
        </span>
    </div>

    <!-- Products Table -->
    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th width="60">ID</th>
                    <th width="80">Hình ảnh</th>
                    <th>Tên sản phẩm</th>
                    <th width="150">Danh mục</th>
                    <th width="120">Giá</th>
                    <th width="80">Tồn kho</th>
                    <th width="100">Trạng thái</th>
                    <th width="120">Ngày tạo</th>
                    <th width="120">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="9" class="no-data">
                            <i class="fas fa-inbox"></i>
                            <p>Không tìm thấy sản phẩm nào</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?= $product['id'] ?></td>
                            <td>
                                <div class="product-image">
                                    <img src="<?= htmlspecialchars($product['image'] ?? '') ?>" 
                                         alt="<?= htmlspecialchars($product['name']) ?>" 
                                         onerror="this.src='assets/images/placeholder.jpg'">
                                </div>
                            </td>
                            <td>
                                <div class="product-info">
                                    <h4 class="product-name"><?= htmlspecialchars($product['name']) ?></h4>
                                    <p class="product-description"><?= htmlspecialchars(substr($product['description'] ?? '', 0, 100)) ?>...</p>
                                </div>
                            </td>
                            <td>
                                <span class="category-badge">
                                    <?= htmlspecialchars($product['category_name'] ?? 'N/A') ?>
                                </span>
                            </td>
                            <td class="price-cell">
                                <?= formatPrice($product['price'] ?? 0) ?>
                            </td>
                            <td>
                                <span class="stock-badge <?= ($product['stock'] ?? 0) < 10 ? 'low-stock' : '' ?>">
                                    <?= $product['stock'] ?? 0 ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-<?= $product['status'] ?>">
                                    <?= $product['status'] == 'active' ? 'Hoạt động' : 'Không hoạt động' ?>
                                </span>
                            </td>
                            <td><?= formatDate($product['created_at'] ?? date('Y-m-d H:i:s')) ?></td>
                            <td>
                                <div class="action-buttons">
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
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
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