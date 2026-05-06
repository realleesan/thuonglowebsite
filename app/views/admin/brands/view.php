<?php
/**
 * Admin Brands View
 * Sử dụng AdminService thông qua ServiceManager
 */

// Khởi tạo View & ServiceManager
require_once __DIR__ . '/../../../../core/view_init.php';

// Chọn service admin (được inject từ index.php)
$service = isset($currentService) ? $currentService : ($adminService ?? null);

// Check for success message after redirect
$updated = isset($_GET['updated']) && $_GET['updated'] == '1';

try {
    // Get brand ID from URL
    $brand_id = (int)($_GET['id'] ?? 0);

    // Get brand details using AdminService
    $brandData = $service->getBrandDetailsData($brand_id);
    $brand = $brandData['brand'];
    $brand_products = $brandData['products'];

    // Redirect if brand not found
    if (!$brand) {
        header('Location: ?page=admin&module=brands&error=not_found');
        exit;
    }

} catch (Exception $e) {
    $errorHandler->logError('Admin Brands View', $e->getMessage());
    header('Location: ?page=admin&module=brands&error=system_error');
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

<div class="brands-page brands-view-page">
    <?php if ($updated): ?>
        <div class="alert alert-success" style="margin: 20px;">
            <i class="fas fa-check-circle"></i> Cập nhật thương hiệu thành công!
        </div>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-eye"></i>
                Chi Tiết Thương Hiệu
            </h1>
            <p class="page-description">Xem thông tin chi tiết thương hiệu: <?= htmlspecialchars($brand['name']) ?></p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=brands" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Quay lại danh sách
            </a>
            <a href="?page=admin&module=brands&action=edit&id=<?= $brand['id'] ?>" class="btn btn-warning">
                <i class="fas fa-edit"></i>
                Chỉnh sửa
            </a>
            <button type="button" class="btn btn-danger delete-btn"
                    data-id="<?= $brand['id'] ?>" data-name="<?= htmlspecialchars($brand['name']) ?>"
                    data-has-products="<?= $brand['product_count'] > 0 ? '1' : '0' ?>">
                <i class="fas fa-trash"></i>
                Xóa
            </button>
        </div>
    </div>

    <!-- Brand Details -->
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
                            <span class="detail-value"><?= $brand['id'] ?></span>
                        </div>

                        <div class="detail-row">
                            <label>Tên thương hiệu:</label>
                            <span class="detail-value"><?= htmlspecialchars($brand['name']) ?></span>
                        </div>

                        <div class="detail-row">
                            <label>Slug:</label>
                            <span class="detail-value"><code><?= htmlspecialchars($brand['slug']) ?></code></span>
                        </div>

                        <?php if ($brand['website']): ?>
                        <div class="detail-row">
                            <label>Website:</label>
                            <span class="detail-value">
                                <a href="<?= htmlspecialchars($brand['website']) ?>" target="_blank">
                                    <?= htmlspecialchars($brand['website']) ?> <i class="fas fa-external-link-alt"></i>
                                </a>
                            </span>
                        </div>
                        <?php endif; ?>

                        <div class="detail-row">
                            <label>Thứ tự sắp xếp:</label>
                            <span class="detail-value"><?= $brand['sort_order'] ?></span>
                        </div>

                        <div class="detail-row">
                            <label>Trạng thái:</label>
                            <span class="detail-value">
                                <span class="status-badge <?= $brand['status'] === 'active' ? 'status-active' : 'status-inactive' ?>">
                                    <?= $brand['status'] === 'active' ? 'Hoạt động' : 'Không hoạt động' ?>
                                </span>
                            </span>
                        </div>

                        <div class="detail-row">
                            <label>Ngày tạo:</label>
                            <span class="detail-value"><?= formatDate($brand['created_at']) ?></span>
                        </div>

                        <div class="detail-row">
                            <label>Cập nhật lần cuối:</label>
                            <span class="detail-value"><?= formatDate($brand['updated_at']) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <?php if ($brand['description']): ?>
                <div class="details-section">
                    <h3 class="section-title">Mô Tả</h3>
                    <div class="details-content">
                        <p><?= nl2br(htmlspecialchars($brand['description'])) ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right Column -->
            <div class="details-column">
                <!-- Brand Image -->
                <div class="details-section">
                    <h3 class="section-title">Hình Ảnh</h3>
                    <div class="details-content">
                        <?php if ($brand['image']): ?>
                            <div class="brand-image">
                                <img src="<?= htmlspecialchars($brand['image']) ?>" alt="<?= htmlspecialchars($brand['name']) ?>">
                            </div>
                        <?php else: ?>
                            <div class="no-image-placeholder">
                                <i class="fas fa-image"></i>
                                <p>Chưa có hình ảnh</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="details-section">
                    <h3 class="section-title">Thống Kê</h3>
                    <div class="details-content">
                        <div class="stats-list">
                            <div class="stat-item">
                                <span class="stat-label">Số sản phẩm:</span>
                                <span class="stat-value"><?= $brand['product_count'] ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Products in this Brand -->
    <div class="details-section" style="margin-top: 30px;">
        <h3 class="section-title">
            <i class="fas fa-box"></i>
            Sản Phẩm Thuộc Thương Hiệu Này
            <span class="badge badge-info"><?= count($brand_products) ?> sản phẩm</span>
        </h3>

        <div class="details-content">
            <?php if (empty($brand_products)): ?>
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <p>Chưa có sản phẩm nào thuộc thương hiệu này</p>
                </div>
            <?php else: ?>
                <div class="products-mini-list">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th width="60">ID</th>
                                <th width="80">Hình</th>
                                <th>Tên sản phẩm</th>
                                <th>Danh mục</th>
                                <th>Giá</th>
                                <th>Trạng thái</th>
                                <th width="100">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($brand_products as $product): ?>
                                <tr>
                                    <td><?= $product['id'] ?></td>
                                    <td>
                                        <?php if ($product['image']): ?>
                                            <img src="<?= htmlspecialchars($product['image']) ?>" alt="" class="table-thumb">
                                        <?php else: ?>
                                            <div class="no-image"><i class="fas fa-image"></i></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($product['name']) ?></td>
                                    <td><?= htmlspecialchars($product['category_name'] ?? 'N/A') ?></td>
                                    <td><?= formatPrice($product['price']) ?></td>
                                    <td>
                                        <span class="status-badge status-<?= $product['status'] ?>">
                                            <?= $product['status'] === 'active' ? 'Hoạt động' : ($product['status'] === 'draft' ? 'Bản nháp' : ($product['status'] === 'out_of_stock' ? 'Hết hàng' : 'Không hoạt động')) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="?page=admin&module=products&action=view&id=<?= $product['id'] ?>" class="btn btn-sm btn-info" title="Xem">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="?page=admin&module=products&action=edit&id=<?= $product['id'] ?>" class="btn btn-sm btn-warning" title="Sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-exclamation-triangle"></i> Xác nhận xóa</h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <p>Bạn có chắc chắn muốn xóa thương hiệu <strong id="deleteBrandName"></strong>?</p>
            <div id="deleteWarning" class="alert alert-warning" style="display: none;">
                <i class="fas fa-exclamation-circle"></i>
                Thương hiệu này đang có sản phẩm. Bạn cần chuyển sản phẩm sang thương hiệu khác trước khi xóa.
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" id="cancelDelete" class="btn btn-secondary">Hủy</button>
            <a href="#" id="confirmDelete" class="btn btn-danger">Xóa</a>
        </div>
    </div>
</div>

