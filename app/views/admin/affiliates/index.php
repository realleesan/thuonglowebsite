<?php
/**
 * Admin Affiliates Index
 * Sử dụng AdminService thông qua ServiceManager
 */

// Chọn service admin (được inject từ index.php)
$service = isset($currentService) ? $currentService : ($adminService ?? null);

// Get error handler if available
$errorHandler = null;
if (isset($GLOBALS['errorHandler'])) {
    $errorHandler = $GLOBALS['errorHandler'];
} elseif (class_exists('ErrorHandler')) {
    $errorHandler = new ErrorHandler();
}

try {
    // Get filter parameters
    $filters = [
        'search' => $_GET['search'] ?? '',
        'status' => $_GET['status'] ?? ''
    ];
    
    $current_page = max(1, (int)($_GET['page'] ?? 1));
    $per_page = 10;
    
    // Get affiliates data using AdminService
    $affiliatesData = $service->getAffiliatesData($current_page, $per_page, $filters);
    $affiliates = $affiliatesData['affiliates'] ?? [];
    $pagination = $affiliatesData['pagination'] ?? ['current_page' => 1, 'last_page' => 1, 'per_page' => 10];
    $total_affiliates = $affiliatesData['total'] ?? 0;
    $stats = $affiliatesData['stats'] ?? [];
    
    // Calculate pagination variables for template
    $total_pages = $pagination['last_page'];
    $paged_affiliates = $affiliates;
    
} catch (Exception $e) {
    $errorHandler->logError('Admin Affiliates Index View Error', $e);
    $affiliates = [];
    $paged_affiliates = [];
    $total_affiliates = 0;
    $total_pages = 1;
    $current_page = 1;
    $filters = ['search' => '', 'status' => ''];
}

// Extract filter values for template
$search = $filters['search'];
$status_filter = $filters['status'];

// Format price function
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' VNĐ';
}

// Format date function
function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}
?>

<div class="affiliates-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-handshake"></i>
                Quản Lý Đại Lý
            </h1>
            <p class="page-description">Quản lý danh sách đại lý và hoa hồng của hệ thống</p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=affiliates&action=add" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Thêm Đại Lý
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-section">
        <form method="GET" class="filters-form">
            <input type="hidden" name="page" value="admin">
            <input type="hidden" name="module" value="affiliates">
            
            <div class="filter-group">
                <div class="filter-item">
                    <label for="search">Tìm kiếm:</label>
                    <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Tên đại lý, email, mã giới thiệu...">
                </div>
                
                <div class="filter-item">
                    <label for="status">Trạng thái:</label>
                    <select id="status" name="status">
                        <option value="">Tất cả trạng thái</option>
                        <option value="active" <?= $status_filter == 'active' ? 'selected' : '' ?>>Hoạt động</option>
                        <option value="inactive" <?= $status_filter == 'inactive' ? 'selected' : '' ?>>Không hoạt động</option>
                        <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>Chờ duyệt</option>
                    </select>
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-search"></i>
                        Lọc
                    </button>
                    <a href="?page=admin&module=affiliates" class="btn btn-outline">
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
            Hiển thị <?= count($paged_affiliates) ?> trong tổng số <?= $total_affiliates ?> đại lý
        </span>
    </div>

    <!-- Affiliates Table -->
    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th width="40">
                        <input type="checkbox" id="select-all">
                    </th>
                    <th width="60">ID</th>
                    <th>Tên đại lý</th>
                    <th width="150">Email</th>
                    <th width="120">Mã giới thiệu</th>
                    <th width="100">Hoa hồng (%)</th>
                    <th width="130">Tổng hoa hồng</th>
                    <th width="100">Trạng thái</th>
                    <th width="120">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($paged_affiliates)): ?>
                    <tr>
                        <td colspan="9" class="no-data">
                            <i class="fas fa-inbox"></i>
                            <p>Không tìm thấy đại lý nào</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($paged_affiliates as $affiliate): ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="affiliate-checkbox" value="<?= $affiliate['id'] ?>">
                            </td>
                            <td><?= $affiliate['id'] ?></td>
                            <td>
                                <div class="affiliate-info">
                                    <h4 class="affiliate-name"><?= htmlspecialchars($affiliate['user_name'] ?? 'N/A') ?></h4>
                                    <p class="affiliate-phone"><?= htmlspecialchars($affiliate['user_phone'] ?? '') ?></p>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($affiliate['user_email'] ?? 'N/A') ?></td>
                            <td>
                                <span class="referral-code">
                                    <?= htmlspecialchars($affiliate['referral_code']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="rate-badge">
                                    <?= htmlspecialchars($affiliate['commission_rate'] ?? '0') ?>%
                                </span>
                            </td>
                            <td>
                                <span class="commission-total">
                                    <?= isset($affiliate['total_commission']) ? formatPrice($affiliate['total_commission']) : '0 VNĐ' ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $status = $affiliate['status'] ?? 'active';
                                $status_class = ($status === 'active') ? 'status-active' : 'status-inactive';
                                $status_text = ($status === 'active') ? 'Hoạt động' : 'Không hoạt động';
                                ?>
                                <span class="status-badge <?= $status_class ?>"><?= $status_text ?></span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="?page=admin&module=affiliates&action=view&id=<?= $affiliate['id'] ?>" 
                                       class="btn btn-sm btn-info" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="?page=admin&module=affiliates&action=edit&id=<?= $affiliate['id'] ?>" 
                                       class="btn btn-sm btn-warning" title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger delete-btn" 
                                            data-id="<?= $affiliate['id'] ?>" data-name="<?= htmlspecialchars($affiliate['user_name'] ?? 'N/A') ?>" 
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
                    <a href="?page=admin&module=affiliates&<?= http_build_query(array_merge($_GET, ['page' => $current_page - 1])) ?>" 
                       class="pagination-btn">
                        <i class="fas fa-chevron-left"></i>
                        Trước
                    </a>
                <?php endif; ?>

                <?php
                $start_page = max(1, $current_page - 2);
                $end_page = min($total_pages, $current_page + 2);
                
                if ($start_page > 1): ?>
                    <a href="?page=admin&module=affiliates&<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>" 
                       class="pagination-number">1</a>
                    <?php if ($start_page > 2): ?>
                        <span class="pagination-dots">...</span>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <a href="?page=admin&module=affiliates&<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
                       class="pagination-number <?= $i == $current_page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($end_page < $total_pages): ?>
                    <?php if ($end_page < $total_pages - 1): ?>
                        <span class="pagination-dots">...</span>
                    <?php endif; ?>
                    <a href="?page=admin&module=affiliates&<?= http_build_query(array_merge($_GET, ['page' => $total_pages])) ?>" 
                       class="pagination-number"><?= $total_pages ?></a>
                <?php endif; ?>

                <?php if ($current_page < $total_pages): ?>
                    <a href="?page=admin&module=affiliates&<?= http_build_query(array_merge($_GET, ['page' => $current_page + 1])) ?>" 
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

    // Handle confirm delete - redirect to delete action
    document.addEventListener('click', function(e) {
        if (e.target.id === 'prConfirmDeleteBtn') {
            const modal = document.getElementById('productDeleteModal');
            const deleteId = modal ? modal.dataset.deleteId : null;
            if (deleteId) {
                window.location.href = '?page=admin&module=affiliates&action=delete&id=' + deleteId;
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