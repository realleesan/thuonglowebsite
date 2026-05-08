<?php
/**
 * Admin Brands Index
 * Sử dụng AdminService thông qua ServiceManager
 */

// Make errorHandler available globally
/** @var ErrorHandler $errorHandler */
global $errorHandler;

// Chọn service admin (được inject từ index.php)
$service = isset($currentService) ? $currentService : ($adminService ?? null);

try {
    // Search and filter parameters
    $search = $_GET['search'] ?? '';
    $status_filter = $_GET['status'] ?? '';
    $current_page = max(1, (int)($_GET['p'] ?? 1));
    $per_page = 10;

    // Prepare filters for AdminService
    $filters = [
        'search' => $search,
        'status' => $status_filter
    ];

    // Get brands data using AdminService
    $brandsData = $service->getBrandsData($current_page, $per_page, $filters);

    $paged_brands = $brandsData['brands'];
    $total_brands = $brandsData['total'];
    $pagination = $brandsData['pagination'];
    $total_pages = $pagination['last_page'];

} catch (Exception $e) {
    $errorHandler->logError('Admin Brands Index', $e->getMessage());
    $paged_brands = [];
    $total_brands = 0;
    $total_pages = 1;
    $current_page = 1;
}

// Format date function
function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}
?>

<div class="brands-page">
    <!-- Success/Error Messages -->
    <?php if (isset($_GET['success']) && $_GET['success'] === 'added'): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> Thêm thương hiệu thành công!
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['success']) && $_GET['success'] === 'updated'): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> Cập nhật thương hiệu thành công!
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['success']) && $_GET['success'] === 'deleted'): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> Xóa thương hiệu thành công!
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i>
            <?php
            switch ($_GET['error']) {
                case 'not_found': echo 'Không tìm thấy thương hiệu'; break;
                case 'system_error': echo 'Lỗi hệ thống. Vui lòng thử lại sau.'; break;
                case 'has_products': echo 'Không thể xóa thương hiệu đang có sản phẩm'; break;
                default: echo 'Đã xảy ra lỗi';
            }
            ?>
        </div>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-tag"></i>
                Quản Lý Thương Hiệu
            </h1>
            <p class="page-description">Quản lý các thương hiệu sản phẩm trong hệ thống</p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=brands&action=add" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Thêm Thương Hiệu
            </a>
        </div>
    </div>

    <!-- Stats Cards - Match Categories Style -->
    <div class="stats-row">
        <div class="stat-card-simple">
            <div class="stat-icon-simple stat-blue">
                <i class="fas fa-tag"></i>
            </div>
            <div class="stat-content-simple">
                <div class="stat-number-simple"><?= $total_brands ?></div>
                <div class="stat-label-simple">Tổng thương hiệu</div>
            </div>
        </div>
        <div class="stat-card-simple">
            <div class="stat-icon-simple stat-green">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content-simple">
                <div class="stat-number-simple"><?= count(array_filter($paged_brands, fn($b) => $b['status'] === 'active')) ?></div>
                <div class="stat-label-simple">Đang hoạt động</div>
            </div>
        </div>
        <div class="stat-card-simple">
            <div class="stat-icon-simple stat-orange">
                <i class="fas fa-eye"></i>
            </div>
            <div class="stat-content-simple">
                <div class="stat-number-simple"><?= count(array_filter($paged_brands, fn($b) => ($b['show_in_filter'] ?? 0) == 1)) ?></div>
                <div class="stat-label-simple">Hiển thị filter</div>
            </div>
        </div>
        <div class="stat-card-simple">
            <div class="stat-icon-simple stat-purple">
                <i class="fas fa-star"></i>
            </div>
            <div class="stat-content-simple">
                <div class="stat-number-simple"><?= count(array_filter($paged_brands, fn($b) => ($b['is_featured'] ?? 0) == 1)) ?></div>
                <div class="stat-label-simple">Nổi bật</div>
            </div>
        </div>
    </div>

    <!-- Filters & Search - Match Categories Style -->
    <div class="filters-section">
        <form method="GET" class="filters-form">
            <input type="hidden" name="page" value="admin">
            <input type="hidden" name="module" value="brands">
            
            <div class="filter-group">
                <div class="filter-item">
                    <label for="search">Tìm kiếm:</label>
                    <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Tên thương hiệu, slug...">
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
                    <a href="?page=admin&module=brands" class="btn btn-outline">
                        <i class="fas fa-times"></i>
                        Xóa bộ lọc
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Results Info - Match Categories -->
    <div class="results-info">
        <span class="results-count">
            Hiển thị <?= count($paged_brands) ?> trong tổng số <?= $total_brands ?> thương hiệu
        </span>
    </div>

    <!-- Brands Table - Match Categories Style -->
    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th width="40">
                        <input type="checkbox" id="select-all">
                    </th>
                    <th width="60">ID</th>
                    <th width="80">Hình ảnh</th>
                    <th>Tên thương hiệu</th>
                    <th width="150">Slug</th>
                    <th width="100">Sản phẩm</th>
                    <th width="100">Trạng thái</th>
                    <th width="80">Thứ tự</th>
                    <th width="120">Ngày tạo</th>
                    <th width="120">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($paged_brands)): ?>
                    <tr>
                        <td colspan="10" class="no-data">
                            <i class="fas fa-inbox"></i>
                            <p>Không tìm thấy thương hiệu nào</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($paged_brands as $brand): ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="brand-checkbox" value="<?= $brand['id'] ?>">
                            </td>
                            <td><?= $brand['id'] ?></td>
                            <td>
                                <?php if (!empty($brand['image'])): ?>
                                    <img src="<?= htmlspecialchars($brand['image']) ?>" alt="<?= htmlspecialchars($brand['name']) ?>" class="category-thumbnail" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="category-image-placeholder" style="display:none;">
                                        <i class="fas fa-tag"></i>
                                    </div>
                                <?php else: ?>
                                    <div class="category-image-placeholder">
                                        <i class="fas fa-tag"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="category-info">
                                    <h4 class="category-name"><?= htmlspecialchars($brand['name']) ?></h4>
                                    <?php if ($brand['website']): ?>
                                        <small><a href="<?= htmlspecialchars($brand['website']) ?>" target="_blank"><?= htmlspecialchars($brand['website']) ?></a></small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <code class="slug-text"><?= htmlspecialchars($brand['slug']) ?></code>
                            </td>
                            <td>
                                <span class="product-count-badge">
                                    <?= $brand['product_count'] ?? 0 ?> sản phẩm
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-<?= $brand['status'] ?>">
                                    <?= $brand['status'] == 'active' ? 'Hoạt động' : 'Không hoạt động' ?>
                                </span>
                            </td>
                            <td><?= $brand['sort_order'] ?? 0 ?></td>
                            <td><?= formatDate($brand['created_at'] ?? 'now') ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="?page=admin&module=brands&action=view&id=<?= $brand['id'] ?>" 
                                       class="btn btn-sm btn-info" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="?page=admin&module=brands&action=edit&id=<?= $brand['id'] ?>" 
                                       class="btn btn-sm btn-warning" title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger delete-btn" 
                                            data-id="<?= $brand['id'] ?>" data-name="<?= htmlspecialchars($brand['name']) ?>" 
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

    <!-- Pagination - Match Categories Style -->
    <?php if ($total_pages > 1): ?>
        <div class="pagination-container">
            <div class="pagination">
                <?php if ($current_page > 1): ?>
                    <a href="?page=admin&module=brands&<?= http_build_query(array_merge($_GET, ['p' => $current_page - 1])) ?>" 
                       class="pagination-btn">
                        <i class="fas fa-chevron-left"></i>
                        Trước
                    </a>
                <?php endif; ?>

                <?php
                $start_page = max(1, $current_page - 2);
                $end_page = min($total_pages, $current_page + 2);
                
                if ($start_page > 1): ?>
                    <a href="?page=admin&module=brands&<?= http_build_query(array_merge($_GET, ['p' => 1])) ?>" 
                       class="pagination-number">1</a>
                    <?php if ($start_page > 2): ?>
                        <span class="pagination-dots">...</span>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <a href="?page=admin&module=brands&<?= http_build_query(array_merge($_GET, ['p' => $i])) ?>" 
                       class="pagination-number <?= $i == $current_page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($end_page < $total_pages): ?>
                    <?php if ($end_page < $total_pages - 1): ?>
                        <span class="pagination-dots">...</span>
                    <?php endif; ?>
                    <a href="?page=admin&module=brands&<?= http_build_query(array_merge($_GET, ['p' => $total_pages])) ?>" 
                       class="pagination-number"><?= $total_pages ?></a>
                <?php endif; ?>

                <?php if ($current_page < $total_pages): ?>
                    <a href="?page=admin&module=brands&<?= http_build_query(array_merge($_GET, ['p' => $current_page + 1])) ?>" 
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

<!-- Delete Confirmation Modal - Match Categories Style -->
<div id="productDeleteModal" style="display: none;">
    <div class="product-modal-overlay"></div>
    <div class="product-modal-container">
        <div class="product-modal-header">
            <h3>Xác nhận xóa</h3>
            <button class="product-modal-close" onclick="closeProductDeleteModal()">&times;</button>
        </div>
        <div class="product-modal-body">
            <p>Bạn có chắc chắn muốn xóa thương hiệu "<strong id="productDeleteName"></strong>"?</p>
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
// Delete button click handler
window.showProductDeleteModal = function(id, name) {
    const modal = document.getElementById('productDeleteModal');
    const nameElement = document.getElementById('productDeleteName');
    
    if (modal) {
        if (nameElement) {
            nameElement.textContent = name || 'thương hiệu này';
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

// Handle delete button clicks
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name || 'thương hiệu này';
            showProductDeleteModal(id, name);
        });
    });
});

// Handle confirm delete
document.addEventListener('click', function(e) {
    if (e.target.id === 'productConfirmDeleteBtn') {
        const modal = document.getElementById('productDeleteModal');
        const deleteId = modal ? modal.dataset.deleteId : null;
        if (deleteId) {
            window.location.href = '?page=admin&module=brands&action=delete&id=' + deleteId;
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

