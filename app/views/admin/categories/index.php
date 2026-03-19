<?php
/**
 * Admin Categories Index
 * Sử dụng AdminService thông qua ServiceManager
 */

// Chọn service admin (được inject từ index.php)
$service = isset($currentService) ? $currentService : ($adminService ?? null);

try {
    // Search and filter parameters
    $search = $_GET['search'] ?? '';
    $status_filter = $_GET['status'] ?? '';
    $current_page = max(1, (int)($_GET['page'] ?? 1));
    $per_page = 10;
    
    // Prepare filters for AdminService
    $filters = [
        'search' => $search,
        'status' => $status_filter
    ];
    
    // Get categories data using AdminService
    $categoriesData = $service->getCategoriesData($current_page, $per_page, $filters);
    
    $paged_categories = $categoriesData['categories'];
    $total_categories = $categoriesData['total'];
    $pagination = $categoriesData['pagination'];
    $total_pages = $pagination['last_page'];
    
} catch (Exception $e) {
    $errorHandler->logError('Admin Categories Index', $e->getMessage());
    $paged_categories = [];
    $total_categories = 0;
    $total_pages = 1;
    $current_page = 1;
}

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
                    <th width="80">Hình ảnh</th>
                    <th>Tên danh mục</th>
                    <th width="150">Slug</th>
                    <th>Mô tả</th>
                    <th width="100">Số sản phẩm</th>
                    <th width="100">Trạng thái</th>
                    <th width="120">Ngày tạo</th>
                    <th width="120">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($paged_categories)): ?>
                    <tr>
                        <td colspan="10" class="no-data">
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
                                <?php if (!empty($category['image'])): ?>
                                    <img src="<?= htmlspecialchars($category['image']) ?>" alt="<?= htmlspecialchars($category['name']) ?>" class="category-thumbnail" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="category-image-placeholder" style="display:none;">
                                        <i class="fas fa-folder"></i>
                                    </div>
                                <?php else: ?>
                                    <div class="category-image-placeholder">
                                        <i class="fas fa-folder"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="category-info">
                                    <h4 class="category-name"><?= htmlspecialchars($category['name']) ?></h4>
                                </div>
                            </td>
                            <td>
                                <code class="slug-text"><?= htmlspecialchars($category['slug']) ?></code>
                            </td>
                            <td>
                                <?php $desc = $category['description'] ?? ''; ?>
                                <?php if (!empty($desc)): ?>
                                    <p class="category-description">
                                        <?= htmlspecialchars(mb_substr($desc, 0, 80)) ?>
                                        <?= mb_strlen($desc) > 80 ? '...' : '' ?>
                                    </p>
                                <?php else: ?>
                                    <span class="text-muted">Chưa có mô tả</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="product-count-badge">
                                    <?= $category['products_count'] ?? 0 ?> sản phẩm
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
    <div id="productDeleteModal" style="display: none;">
        <div class="product-modal-overlay"></div>
        <div class="product-modal-container">
            <div class="product-modal-header">
                <h3>Xác nhận xóa</h3>
                <button class="product-modal-close" onclick="closeProductDeleteModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa danh mục "<strong id="productDeleteName"></strong>"?</p>
                <p class="product-modal-warning">Hành động này không thể hoàn tác!</p>
            </div>
            <div class="product-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeProductDeleteModal()">Hủy</button>
                <button type="button" class="btn btn-danger" id="prConfirmDeleteBtn">Xóa</button>
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

    .product-modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        padding: 16px 20px;
        border-top: 1px solid #e5e7eb;
        background: #f9fafb;
        border-radius: 0 0 12px 12px;
    }

    .product-modal-warning {
        color: #dc2626 !important;
        font-size: 13px;
        font-weight: 500;
    }
    </style>

    <script>
    // Delete button click handler
    document.addEventListener('DOMContentLoaded', function() {
        const deleteButtons = document.querySelectorAll('.delete-btn');
        deleteButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name || 'danh mục này';
                showProductDeleteModal(id, name);
            });
        });
    });

    window.showProductDeleteModal = function(id, name) {
        const modal = document.getElementById('productDeleteModal');
        const nameElement = document.getElementById('productDeleteName');
    
        if (modal) {
            if (nameElement) {
                nameElement.textContent = name || 'danh mục này';
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

    // Handle confirm delete - AJAX
    document.addEventListener('click', function(e) {
        if (e.target.id === 'prConfirmDeleteBtn') {
            const modal = document.getElementById('productDeleteModal');
            const deleteId = modal ? modal.dataset.deleteId : null;
            if (deleteId) {
                // AJAX delete
                fetch('?page=admin&module=categories&action=delete&id=' + deleteId, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeProductDeleteModal();
                        // Reload page to show updated list
                        window.location.reload();
                    } else {
                        alert(data.message || 'Có lỗi xảy ra khi xóa danh mục');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi xóa danh mục');
                });
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
</div>