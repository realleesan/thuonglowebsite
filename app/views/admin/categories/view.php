<?php
/**
 * Admin Categories View
 * Sử dụng AdminService thông qua ServiceManager
 */

// Khởi tạo View & ServiceManager
require_once __DIR__ . '/../../../../core/view_init.php';

// Chọn service admin (được inject từ index.php)
$service = isset($currentService) ? $currentService : ($adminService ?? null);

// Check for success message after redirect
$updated = isset($_GET['updated']) && $_GET['updated'] == '1';

try {
    // Get category ID from URL
    $category_id = (int)($_GET['id'] ?? 0);
    
    // Get category details using AdminService
    $categoryData = $service->getCategoryDetailsData($category_id);
    $category = $categoryData['category'];
    $category_products = $categoryData['products'];
    
    // Redirect if category not found
    if (!$category) {
        header('Location: ?page=admin&module=categories&error=not_found');
        exit;
    }
    
} catch (Exception $e) {
    $errorHandler->logError('Admin Categories View', $e->getMessage());
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
    <?php if ($updated): ?>
        <div class="alert alert-success" style="margin: 20px;">
            <i class="fas fa-check-circle"></i> Cập nhật danh mục thành công!
        </div>
    <?php endif; ?>
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
            <a href="?page=admin&module=categories" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Quay lại danh sách
            </a>
            <a href="?page=admin&module=categories&action=edit&id=<?= $category['id'] ?>" class="btn btn-warning">
                <i class="fas fa-edit"></i>
                Chỉnh sửa
            </a>
            <button type="button" class="btn btn-danger delete-btn" 
                    data-id="<?= $category['id'] ?>" data-name="<?= htmlspecialchars($category['name']) ?>">
                <i class="fas fa-trash"></i>
                Xóa
            </button>
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
            </div>
        </div>
    </div>

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
                        // Redirect to categories list after successful delete
                        window.location.href = '?page=admin&module=categories';
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

