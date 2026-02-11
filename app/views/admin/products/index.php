<?php
// Load Models
require_once __DIR__ . '/../../../models/ProductsModel.php';
require_once __DIR__ . '/../../../models/CategoriesModel.php';

$productsModel = new ProductsModel();
$categoriesModel = new CategoriesModel();

// Get categories for filter dropdown
$categories = $categoriesModel->getActive();

// Search and filter parameters
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$status_filter = $_GET['status'] ?? '';
$current_page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;

// Build search filters
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

// Get total count for pagination
$conditions = [];
$bindings = [];

if (!empty($search)) {
    $conditions[] = "(name LIKE ? OR description LIKE ?)";
    $searchTerm = "%{$search}%";
    $bindings = array_merge($bindings, [$searchTerm, $searchTerm]);
}

if (!empty($category_filter)) {
    $conditions[] = "category_id = ?";
    $bindings[] = $category_filter;
}

if (!empty($status_filter)) {
    $conditions[] = "status = ?";
    $bindings[] = $status_filter;
}

// Get total count
$countSql = "SELECT COUNT(*) as total FROM products";
if (!empty($conditions)) {
    $countSql .= " WHERE " . implode(' AND ', $conditions);
}
$totalResult = $productsModel->db->query($countSql, $bindings);
$total_products = $totalResult[0]['total'] ?? 0;

// Calculate pagination
$total_pages = ceil($total_products / $per_page);
$current_page = max(1, min($total_pages, $current_page));
$offset = ($current_page - 1) * $per_page;

// Get products with category info
$sql = "
    SELECT p.*, c.name as category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
";
if (!empty($conditions)) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}
$sql .= " ORDER BY p.created_at DESC LIMIT {$per_page} OFFSET {$offset}";

$filtered_products = $productsModel->db->query($sql, $bindings);
    });
}

if (!empty($status_filter)) {
    $filtered_products = array_filter($filtered_products, function($product) use ($status_filter) {
        return $product['status'] == $status_filter;
    });
}

// Pagination
$per_page = 10;
$total_products = count($filtered_products);
$total_pages = ceil($total_products / $per_page);
$current_page = max(1, min($total_pages, (int)($_GET['page'] ?? 1)));
$offset = ($current_page - 1) * $per_page;
$paged_products = array_slice($filtered_products, $offset, $per_page);

// Format price function
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' VNĐ';
}

// Format date function
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
            Hiển thị <?= count($paged_products) ?> trong tổng số <?= $total_products ?> sản phẩm
        </span>
        
        <!-- Bulk Actions -->
        <div class="bulk-actions">
            <select id="bulk-action" disabled>
                <option value="">Hành động hàng loạt</option>
                <option value="activate">Kích hoạt</option>
                <option value="deactivate">Vô hiệu hóa</option>
                <option value="delete">Xóa</option>
            </select>
            <button type="button" id="apply-bulk" class="btn btn-secondary" disabled>
                Áp dụng
            </button>
        </div>
    </div>

    <!-- Products Table -->
    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th width="40">
                        <input type="checkbox" id="select-all">
                    </th>
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
                <?php if (empty($paged_products)): ?>
                    <tr>
                        <td colspan="10" class="no-data">
                            <i class="fas fa-inbox"></i>
                            <p>Không tìm thấy sản phẩm nào</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($paged_products as $product): ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="product-checkbox" value="<?= $product['id'] ?>">
                            </td>
                            <td><?= $product['id'] ?></td>
                            <td>
                                <div class="product-image">
                                    <img src="<?= $product['image'] ?>" alt="<?= htmlspecialchars($product['name']) ?>" 
                                         onerror="this.src='<?php echo asset_url('images/placeholder.jpg'); ?>'"">
                                </div>
                            </td>
                            <td>
                                <div class="product-info">
                                    <h4 class="product-name"><?= htmlspecialchars($product['name']) ?></h4>
                                    <p class="product-description"><?= htmlspecialchars(substr($product['description'], 0, 100)) ?>...</p>
                                </div>
                            </td>
                            <td>
                                <span class="category-badge">
                                    <?= htmlspecialchars($category_lookup[$product['category_id']] ?? 'N/A') ?>
                                </span>
                            </td>
                            <td class="price-cell">
                                <?= formatPrice($product['price']) ?>
                            </td>
                            <td>
                                <span class="stock-badge <?= $product['stock'] < 10 ? 'low-stock' : '' ?>">
                                    <?= $product['stock'] ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-<?= $product['status'] ?>">
                                    <?= $product['status'] == 'active' ? 'Hoạt động' : 'Không hoạt động' ?>
                                </span>
                            </td>
                            <td><?= formatDate($product['created_at']) ?></td>
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
    <?php if ($total_pages > 1): ?>
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
                $start_page = max(1, $current_page - 2);
                $end_page = min($total_pages, $current_page + 2);
                
                if ($start_page > 1): ?>
                    <a href="?page=admin&module=products&<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>" 
                       class="pagination-number">1</a>
                    <?php if ($start_page > 2): ?>
                        <span class="pagination-dots">...</span>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <a href="?page=admin&module=products&<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
                       class="pagination-number <?= $i == $current_page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($end_page < $total_pages): ?>
                    <?php if ($end_page < $total_pages - 1): ?>
                        <span class="pagination-dots">...</span>
                    <?php endif; ?>
                    <a href="?page=admin&module=products&<?= http_build_query(array_merge($_GET, ['page' => $total_pages])) ?>" 
                       class="pagination-number"><?= $total_pages ?></a>
                <?php endif; ?>

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

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Xác nhận xóa</h3>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa sản phẩm <strong id="deleteProductName"></strong>?</p>
                <p class="text-danger">Hành động này không thể hoàn tác!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelDelete">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Xóa</button>
            </div>
        </div>
    </div>
</div>