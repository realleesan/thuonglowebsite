<?php
// Professional Products Management
$page_title = "Quản lý Sản phẩm";
$breadcrumb = [
    ['text' => 'Dashboard', 'url' => '?page=admin&module=dashboard'],
    ['text' => 'Sản phẩm', 'url' => null]
];

// Load fake data
$fake_data_file = __DIR__ . '/../data/fake_data.json';
$allProducts = [];
$categories = [];

if (file_exists($fake_data_file)) {
    $json_data = json_decode(file_get_contents($fake_data_file), true);
    $allProducts = $json_data['products'] ?? [];
    $categories = $json_data['categories'] ?? [];
}

// Get filter parameters
$searchQuery = $_GET['search'] ?? '';
$filterCategory = $_GET['category'] ?? '';
$filterStatus = $_GET['status'] ?? '';
$sortBy = $_GET['sort'] ?? 'newest';
$page = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
$perPage = 20;

// Apply filters
$products = $allProducts;

// Search
if ($searchQuery) {
    $products = array_filter($products, function($p) use ($searchQuery) {
        return stripos($p['name'], $searchQuery) !== false || 
               stripos($p['description'], $searchQuery) !== false;
    });
}

// Filter by category
if ($filterCategory) {
    $products = array_filter($products, function($p) use ($filterCategory) {
        return $p['category_id'] == $filterCategory;
    });
}

// Filter by status
if ($filterStatus) {
    $products = array_filter($products, function($p) use ($filterStatus) {
        return $p['status'] === $filterStatus;
    });
}

// Sort
switch ($sortBy) {
    case 'name_asc':
        usort($products, function($a, $b) { return strcmp($a['name'], $b['name']); });
        break;
    case 'name_desc':
        usort($products, function($a, $b) { return strcmp($b['name'], $a['name']); });
        break;
    case 'price_asc':
        usort($products, function($a, $b) { return $a['price'] - $b['price']; });
        break;
    case 'price_desc':
        usort($products, function($a, $b) { return $b['price'] - $a['price']; });
        break;
    case 'oldest':
        usort($products, function($a, $b) { return strtotime($a['created_at']) - strtotime($b['created_at']); });
        break;
    case 'newest':
    default:
        usort($products, function($a, $b) { return strtotime($b['created_at']) - strtotime($a['created_at']); });
        break;
}

// Pagination
$totalProducts = count($products);
$totalPages = ceil($totalProducts / $perPage);
$offset = ($page - 1) * $perPage;
$products = array_slice($products, $offset, $perPage);

// Stats for display
$stats = [
    'total' => count($allProducts),
    'active' => count(array_filter($allProducts, function($p) { return $p['status'] === 'active'; })),
    'inactive' => count(array_filter($allProducts, function($p) { return $p['status'] !== 'active'; })),
];
?>

<div class="admin-page-header">
    <div class="page-header-left">
        <h1><?php echo $page_title; ?></h1>
        <div class="admin-breadcrumb">
            <?php foreach ($breadcrumb as $index => $crumb): ?>
                <?php if ($crumb['url']): ?>
                    <a href="<?php echo $crumb['url']; ?>"><?php echo $crumb['text']; ?></a>
                <?php else: ?>
                    <span class="current"><?php echo $crumb['text']; ?></span>
                <?php endif; ?>
                <?php if ($index < count($breadcrumb) - 1): ?>
                    <span class="delimiter">/</span>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="page-header-right">
        <a href="?page=admin&module=products&action=change" class="admin-btn admin-btn-primary">
            <i class="fas fa-plus"></i> Thêm sản phẩm mới
        </a>
    </div>
</div>

<!-- Stats Summary -->
<div class="admin-stats-summary">
    <div class="stat-item">
        <span class="stat-label">Tổng cộng:</span>
        <span class="stat-value"><?php echo $stats['total']; ?></span>
    </div>
    <div class="stat-item">
        <span class="stat-label">Hoạt động:</span>
        <span class="stat-value text-success"><?php echo $stats['active']; ?></span>
    </div>
    <div class="stat-item">
        <span class="stat-label">Không hoạt động:</span>
        <span class="stat-value text-muted"><?php echo $stats['inactive']; ?></span>
    </div>
</div>

<!-- Filters and Search -->
<div class="admin-filters-bar">
    <form method="GET" action="" class="filters-form">
        <input type="hidden" name="page" value="admin">
        <input type="hidden" name="module" value="products">
        
        <div class="filter-search">
            <i class="fas fa-search"></i>
            <input type="text" name="search" placeholder="Tìm kiếm sản phẩm..." 
                   value="<?php echo htmlspecialchars($searchQuery); ?>" class="search-input">
        </div>
        
        <div class="filter-group">
            <select name="category" class="filter-select">
                <option value="">Tất cả danh mục</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo $filterCategory == $cat['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="filter-group">
            <select name="status" class="filter-select">
                <option value="">Tất cả trạng thái</option>
                <option value="active" <?php echo $filterStatus === 'active' ? 'selected' : ''; ?>>Hoạt động</option>
                <option value="inactive" <?php echo $filterStatus === 'inactive' ? 'selected' : ''; ?>>Không hoạt động</option>
            </select>
        </div>
        
        <div class="filter-group">
            <select name="sort" class="filter-select">
                <option value="newest" <?php echo $sortBy === 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
                <option value="oldest" <?php echo $sortBy === 'oldest' ? 'selected' : ''; ?>>Cũ nhất</option>
                <option value="name_asc" <?php echo $sortBy === 'name_asc' ? 'selected' : ''; ?>>Tên A-Z</option>
                <option value="name_desc" <?php echo $sortBy === 'name_desc' ? 'selected' : ''; ?>>Tên Z-A</option>
                <option value="price_asc" <?php echo $sortBy === 'price_asc' ? 'selected' : ''; ?>>Giá thấp → cao</option>
                <option value="price_desc" <?php echo $sortBy === 'price_desc' ? 'selected' : ''; ?>>Giá cao → thấp</option>
            </select>
        </div>
        
        <button type="submit" class="admin-btn admin-btn-primary">
            <i class="fas fa-filter"></i> Lọc
        </button>
        
        <?php if ($searchQuery || $filterCategory || $filterStatus || $sortBy !== 'newest'): ?>
        <a href="?page=admin&module=products" class="admin-btn admin-btn-secondary">
            <i class="fas fa-times"></i> Xóa bộ lọc
        </a>
        <?php endif; ?>
    </form>
</div>

<div class="admin-card">
    <!-- Bulk Actions -->
    <div class="bulk-actions-bar" id="bulkActionsBar" style="display: none;">
        <div class="bulk-actions-left">
            <input type="checkbox" id="selectAll" class="bulk-checkbox">
            <span class="selected-count">Đã chọn: <strong id="selectedCount">0</strong></span>
        </div>
        <div class="bulk-actions-right">
            <select id="bulkAction" class="admin-form-control">
                <option value="">Chọn hành động...</option>
                <option value="activate">Kích hoạt</option>
                <option value="deactivate">Vô hiệu hóa</option>
                <option value="delete">Xóa</option>
            </select>
            <button type="button" class="admin-btn admin-btn-primary" onclick="applyBulkAction()">
                Áp dụng
            </button>
        </div>
    </div>

    <div class="admin-card-body">
        <?php if (empty($products)): ?>
            <div class="admin-empty-state">
                <i class="fas fa-box-open" style="font-size: 48px; color: #9CA3AF; margin-bottom: 16px;"></i>
                <h3>Không tìm thấy sản phẩm</h3>
                <p>Thử thay đổi bộ lọc hoặc thêm sản phẩm mới</p>
                <a href="?page=admin&module=products&action=change" class="admin-btn admin-btn-primary">
                    <i class="fas fa-plus"></i> Thêm sản phẩm
                </a>
            </div>
        <?php else: ?>
            <div class="admin-table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th width="40">
                                <input type="checkbox" id="selectAllTable" class="bulk-checkbox" onchange="toggleAllCheckboxes(this)">
                            </th>
                            <th>Sản phẩm</th>
                            <th width="120">Giá</th>
                            <th width="150">Danh mục</th>
                            <th width="120">Trạng thái</th>
                            <th width="120">Ngày tạo</th>
                            <th width="150">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="product-checkbox bulk-checkbox" 
                                       value="<?php echo $product['id']; ?>" 
                                       onchange="updateBulkActions()">
                            </td>
                            <td>
                                <div class="product-cell">
                                    <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                    <small><?php echo htmlspecialchars(substr($product['description'], 0, 60)) . '...'; ?></small>
                                </div>
                            </td>
                            <td>
                                <strong><?php echo number_format($product['price'], 0, ',', '.'); ?> ₫</strong>
                            </td>
                            <td>
                                <?php 
                                $catName = 'N/A';
                                foreach ($categories as $cat) {
                                    if ($cat['id'] == $product['category_id']) {
                                        $catName = $cat['name'];
                                        break;
                                    }
                                }
                                ?>
                                <span class="admin-badge admin-badge-info"><?php echo htmlspecialchars($catName); ?></span>
                            </td>
                            <td>
                                <span class="admin-badge <?php echo $product['status'] === 'active' ? 'admin-badge-success' : 'admin-badge-warning'; ?>">
                                    <?php echo $product['status'] === 'active' ? 'Hoạt động' : 'Không hoạt động'; ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($product['created_at'])); ?></td>
                            <td class="admin-actions">
                                <a href="?page=admin&module=products&action=edit&id=<?php echo $product['id']; ?>" 
                                   class="admin-btn admin-btn-sm admin-btn-warning" title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?page=admin&module=products&action=delete&id=<?php echo $product['id']; ?>" 
                                   class="admin-btn admin-btn-sm admin-btn-danger" 
                                   onclick="return confirm('Bạn có chắc chắn muốn xóa?')" title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="admin-pagination">
                <div class="pagination-info">
                    Hiển thị <?php echo $offset + 1; ?>-<?php echo min($offset + $perPage, $totalProducts); ?> 
                    trong tổng số <?php echo $totalProducts; ?> sản phẩm
                </div>
                <div class="pagination-links">
                    <?php if ($page > 1): ?>
                    <a href="?page=admin&module=products&p=<?php echo $page - 1; ?>&search=<?php echo urlencode($searchQuery); ?>&category=<?php echo $filterCategory; ?>&status=<?php echo $filterStatus; ?>&sort=<?php echo $sortBy; ?>" 
                       class="pagination-link">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <a href="?page=admin&module=products&p=<?php echo $i; ?>&search=<?php echo urlencode($searchQuery); ?>&category=<?php echo $filterCategory; ?>&status=<?php echo $filterStatus; ?>&sort=<?php echo $sortBy; ?>" 
                       class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                    <a href="?page=admin&module=products&p=<?php echo $page + 1; ?>&search=<?php echo urlencode($searchQuery); ?>&category=<?php echo $filterCategory; ?>&status=<?php echo $filterStatus; ?>&sort=<?php echo $sortBy; ?>" 
                       class="pagination-link">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleAllCheckboxes(source) {
    const checkboxes = document.querySelectorAll('.product-checkbox');
    checkboxes.forEach(cb => cb.checked = source.checked);
    updateBulkActions();
}

function updateBulkActions() {
    const checkboxes = document.querySelectorAll('.product-checkbox:checked');
    const count = checkboxes.length;
    const bulkBar = document.getElementById('bulkActionsBar');
    const countEl = document.getElementById('selectedCount');
    
    if (count > 0) {
        bulkBar.style.display = 'flex';
        countEl.textContent = count;
    } else {
        bulkBar.style.display = 'none';
    }
}

function applyBulkAction() {
    const action = document.getElementById('bulkAction').value;
    const checkboxes = document.querySelectorAll('.product-checkbox:checked');
    const ids = Array.from(checkboxes).map(cb => cb.value);
    
    if (!action) {
        alert('Vui lòng chọn hành động');
        return;
    }
    
    if (ids.length === 0) {
        alert('Vui lòng chọn ít nhất một sản phẩm');
        return;
    }
    
    if (confirm(`Bạn có chắc chắn muốn ${action} ${ids.length} sản phẩm?`)) {
        // In real app, send AJAX request here
        alert('Chức năng này sẽ được triển khai với backend');
    }
}
</script>