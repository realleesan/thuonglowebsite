<?php
// Load fake data
$fake_data = json_decode(file_get_contents(__DIR__ . '/../data/fake_data.json'), true);
$categories = $fake_data['categories'];
$products = $fake_data['products'];

// Count products per category
$product_count = [];
foreach ($products as $product) {
    $category_id = $product['category_id'];
    $product_count[$category_id] = ($product_count[$category_id] ?? 0) + 1;
}

// Search and filter
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';

// Filter categories
$filtered_categories = $categories;

if (!empty($search)) {
    $filtered_categories = array_filter($filtered_categories, function($category) use ($search) {
        return stripos($category['name'], $search) !== false || 
               stripos($category['description'], $search) !== false ||
               stripos($category['slug'], $search) !== false;
    });
}

if (!empty($status_filter)) {
    $filtered_categories = array_filter($filtered_categories, function($category) use ($status_filter) {
        return $category['status'] == $status_filter;
    });
}

// Pagination
$per_page = 10;
$total_categories = count($filtered_categories);
$total_pages = ceil($total_categories / $per_page);
$current_page = max(1, min($total_pages, (int)($_GET['page'] ?? 1)));
$offset = ($current_page - 1) * $per_page;
$paged_categories = array_slice($filtered_categories, $offset, $per_page);

// Format date function
function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}
?>

<div class="categories-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-tags"></i>
                Quản Lý Danh Mục
            </h1>
            <p class="page-description">Quản lý danh mục sản phẩm của hệ thống</p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=categories&action=add" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Thêm Danh Mục
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-section">
        <form method="GET" class="filters-form">
            <input type="hidden" name="page" value="admin">
            <input type="hidden" name="module" value="categories">
            
            <div class="filter-group">
                <div class="filter-item">
                    <label for="search">Tìm kiếm:</label>
                    <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Tên danh mục, mô tả, slug...">
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
                    <a href="?page=admin&module=categories" class="btn btn-outline">
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
            Hiển thị <?= count($paged_categories) ?> trong tổng số <?= $total_categories ?> danh mục
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

    <!-- Categories Table -->
    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th width="40">
                        <input type="checkbox" id="select-all">
                    </th>
                    <th width="60">ID</th>
                    <th>Tên danh mục</th>
                    <th width="200">Slug</th>
                    <th>Mô tả</th>
                    <th width="120">Số sản phẩm</th>
                    <th width="100">Trạng thái</th>
                    <th width="120">Ngày tạo</th>
                    <th width="120">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($paged_categories)): ?>
                    <tr>
                        <td colspan="9" class="no-data">
                            <i class="fas fa-inbox"></i>
                            <p>Không tìm thấy danh mục nào</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($paged_categories as $category): ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="category-checkbox" value="<?= $category['id'] ?>">
                            </td>
                            <td><?= $category['id'] ?></td>
                            <td>
                                <div class="category-info">
                                    <h4 class="category-name"><?= htmlspecialchars($category['name']) ?></h4>
                                </div>
                            </td>
                            <td>
                                <code class="slug-text"><?= htmlspecialchars($category['slug']) ?></code>
                            </td>
                            <td>
                                <p class="category-description">
                                    <?= htmlspecialchars(substr($category['description'], 0, 100)) ?>
                                    <?= strlen($category['description']) > 100 ? '...' : '' ?>
                                </p>
                            </td>
                            <td>
                                <span class="product-count-badge">
                                    <?= $product_count[$category['id']] ?? 0 ?> sản phẩm
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-<?= $category['status'] ?>">
                                    <?= $category['status'] == 'active' ? 'Hoạt động' : 'Không hoạt động' ?>
                                </span>
                            </td>
                            <td><?= formatDate($category['created_at']) ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="?page=admin&module=categories&action=view&id=<?= $category['id'] ?>" 
                                       class="btn btn-sm btn-info" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="?page=admin&module=categories&action=edit&id=<?= $category['id'] ?>" 
                                       class="btn btn-sm btn-warning" title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger delete-btn" 
                                            data-id="<?= $category['id'] ?>" data-name="<?= htmlspecialchars($category['name']) ?>" 
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
                    <a href="?page=admin&module=categories&<?= http_build_query(array_merge($_GET, ['page' => $current_page - 1])) ?>" 
                       class="pagination-btn">
                        <i class="fas fa-chevron-left"></i>
                        Trước
                    </a>
                <?php endif; ?>

                <?php
                $start_page = max(1, $current_page - 2);
                $end_page = min($total_pages, $current_page + 2);
                
                if ($start_page > 1): ?>
                    <a href="?page=admin&module=categories&<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>" 
                       class="pagination-number">1</a>
                    <?php if ($start_page > 2): ?>
                        <span class="pagination-dots">...</span>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <a href="?page=admin&module=categories&<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
                       class="pagination-number <?= $i == $current_page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($end_page < $total_pages): ?>
                    <?php if ($end_page < $total_pages - 1): ?>
                        <span class="pagination-dots">...</span>
                    <?php endif; ?>
                    <a href="?page=admin&module=categories&<?= http_build_query(array_merge($_GET, ['page' => $total_pages])) ?>" 
                       class="pagination-number"><?= $total_pages ?></a>
                <?php endif; ?>

                <?php if ($current_page < $total_pages): ?>
                    <a href="?page=admin&module=categories&<?= http_build_query(array_merge($_GET, ['page' => $current_page + 1])) ?>" 
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
                <p>Bạn có chắc chắn muốn xóa danh mục <strong id="deleteCategoryName"></strong>?</p>
                <p class="text-danger">Hành động này không thể hoàn tác!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelDelete">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Xóa</button>
            </div>
        </div>
    </div>
</div>