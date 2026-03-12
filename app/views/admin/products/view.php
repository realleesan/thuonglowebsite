<?php
/**
 * Admin Products View - Tái cấu trúc cho sản phẩm số (Data Nguồn Hàng)
 * Designed for digital products / data products
 * Using Tab Layout with Card Design
 */

// Khởi tạo View & ServiceManager
require_once __DIR__ . '/../../../../core/view_init.php';

// Get product ID from URL
$product_id = (int)($_GET['id'] ?? 0);

// Initialize data variables
$product = null;
$categories = [];
$product_orders = [];
$category = null;
$showErrorMessage = false;
$errorMessage = '';

try {
    if (!$product_id) {
        throw new Exception('ID sản phẩm không hợp lệ');
    }
    
    require_once __DIR__ . '/../../../models/ProductsModel.php';
    require_once __DIR__ . '/../../../models/CategoriesModel.php';
    
    $productsModel = new ProductsModel();
    $categoriesModel = new CategoriesModel();
    
    // Get product
    $products = $productsModel->query("SELECT * FROM products WHERE id = ?", [$product_id]);
    $product = !empty($products) ? $products[0] : null;
    
    if (!$product) {
        throw new Exception('Không tìm thấy sản phẩm');
    }
    
    // Get categories
    $categories = $categoriesModel->getActive();
    
    // Find category
    foreach ($categories as $c) {
        if ($c['id'] == $product['category_id']) {
            $category = $c;
            break;
        }
    }
    
    // Get orders for this product
    require_once __DIR__ . '/../../../models/OrdersModel.php';
    $ordersModel = new OrdersModel();
    
    $product_orders = $ordersModel->query("
        SELECT o.*, oi.quantity, oi.price as unit_price, oi.total as item_total
        FROM orders o
        INNER JOIN order_items oi ON o.id = oi.order_id
        WHERE oi.product_id = ?
        ORDER BY o.created_at DESC
    ", [$product_id]);
    
} catch (Exception $e) {
    $result = $errorHandler->handleViewError($e, 'admin_product_details', []);
    $showErrorMessage = true;
    $errorMessage = $result['message'];
    
    if (strpos($e->getMessage(), 'không tìm thấy') !== false) {
        header('Location: ?page=admin&module=products&error=not_found');
        exit;
    }
}

// Helper functions
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' VNĐ';
}

function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
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

// Decode JSON fields
$benefits = [];
if (!empty($product['benefits'])) {
    $benefits = json_decode($product['benefits'], true) ?: [];
}

$dataStructure = [];
if (!empty($product['data_structure'])) {
    $dataStructure = json_decode($product['data_structure'], true) ?: [];
}

$supplierSocial = [];
if (!empty($product['supplier_social'])) {
    $supplierSocial = json_decode($product['supplier_social'], true) ?: [];
}
?>

<div class="products-page products-view-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-eye"></i>
                Chi Tiết Data
            </h1>
            <p class="page-description">Thông tin chi tiết data #<?= $product['id'] ?></p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=products&action=edit&id=<?= $product['id'] ?>" class="btn btn-warning">
                <i class="fas fa-edit"></i>
                Chỉnh sửa
            </a>
            <button type="button" class="btn btn-danger delete-btn" 
                    data-id="<?= $product['id'] ?>" data-name="<?= htmlspecialchars($product['name']) ?>">
                <i class="fas fa-trash"></i>
                Xóa
            </button>
            <a href="?page=admin&module=products" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Quay lại danh sách
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

    <!-- Product Overview -->
    <div class="product-overview">
        <div class="product-overview-grid">
            <!-- Product Image -->
            <div class="product-image-section">
                <div class="product-image-main">
                    <img src="<?= $product['image'] ?>" alt="<?= htmlspecialchars($product['name']) ?>" 
                         onerror="this.src='assets/images/placeholder.jpg'">
                </div>
            </div>

            <!-- Product Info -->
            <div class="product-info-section">
                <div class="product-header">
                    <span class="product-type-badge"><?= getTypeLabel($product['type']) ?></span>
                    <h2 class="product-name-view"><?= htmlspecialchars($product['name']) ?></h2>
                    <span class="status-badge <?= $product['status'] == 'active' ? 'status-active' : 'status-inactive' ?>">
                        <?= $product['status'] == 'active' ? 'Hoạt động' : 'Không hoạt động' ?>
                    </span>
                </div>

                <div class="product-meta">
                    <div class="meta-item">
                        <span class="meta-label"><i class="fas fa-folder"></i> Danh mục:</span>
                        <span class="meta-value"><?= htmlspecialchars($category['name'] ?? 'N/A') ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label"><i class="fas fa-calendar"></i> Ngày tạo:</span>
                        <span class="meta-value"><?= formatDate($product['created_at']) ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label"><i class="fas fa-calendar-check"></i> Cập nhật:</span>
                        <span class="meta-value"><?= formatDate($product['updated_at']) ?></span>
                    </div>
                </div>

                <!-- Price Display -->
                <div class="product-price-display">
                    <?php if (!empty($product['sale_price']) && $product['sale_price'] < $product['price']): ?>
                        <span class="price-current"><?= formatPrice($product['sale_price']) ?></span>
                        <span class="price-original"><?= formatPrice($product['price']) ?></span>
                        <span class="price-discount">-<?= round((($product['price'] - $product['sale_price']) / $product['price']) * 100) ?>%</span>
                    <?php else: ?>
                        <span class="price-current"><?= formatPrice($product['price']) ?></span>
                    <?php endif; ?>
                </div>

                <?php if (!empty($product['short_description'])): ?>
                <div class="product-description-view">
                    <h4>Mô tả ngắn:</h4>
                    <p><?= nl2br(htmlspecialchars($product['short_description'])) ?></p>
                </div>
                <?php endif; ?>

                <?php if (!empty($product['description'])): ?>
                <div class="product-description-view">
                    <h4>Mô tả chi tiết:</h4>
                    <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                </div>
                <?php endif; ?>

                <!-- Stats -->
                <div class="product-stats">
                    <h4>Thống Kê:</h4>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon sales">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number"><?= $product['sales_count'] ?? 0 ?></div>
                                <div class="stat-label">Đã bán</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon views">
                                <i class="fas fa-eye"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number"><?= number_format($product['view_count'] ?? 0) ?></div>
                                <div class="stat-label">Lượt xem</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-database"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number"><?= number_format($product['record_count'] ?? 0) ?></div>
                                <div class="stat-label">Records</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-download"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number"><?= $product['quota'] ?? 100 ?></div>
                                <div class="stat-label">Lần tải</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="product-details-tabs">
        <div class="tabs-header">
            <button class="tab-btn active" data-tab="tab-data-info">
                <i class="fas fa-database"></i>
                Thông Tin Data
            </button>
            <button class="tab-btn" data-tab="tab-supplier">
                <i class="fas fa-building"></i>
                Nhà Cung Cấp
            </button>
            <button class="tab-btn" data-tab="tab-benefits">
                <i class="fas fa-gift"></i>
                Lợi Ích
            </button>
            <button class="tab-btn" data-tab="tab-structure">
                <i class="fas fa-sitemap"></i>
                Cấu Trúc
            </button>
            <button class="tab-btn" data-tab="tab-orders">
                <i class="fas fa-shopping-cart"></i>
                Đơn Hàng
            </button>
        </div>

        <div class="tabs-content">
            <!-- Tab: Thông Tin Data -->
            <div class="tab-pane active" id="tab-data-info">
                <div class="details-grid">
                    <div class="details-section">
                        <h4>Thông Tin Cơ Bản</h4>
                        <table class="details-table">
                            <tr>
                                <td><strong>Loại Data:</strong></td>
                                <td><?= getTypeLabel($product['type']) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Giá:</strong></td>
                                <td><?= formatPrice($product['price']) ?></td>
                            </tr>
                            <?php if (!empty($product['sale_price'])): ?>
                            <tr>
                                <td><strong>Giá KM:</strong></td>
                                <td><?= formatPrice($product['sale_price']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($product['expiry_days'])): ?>
                            <tr>
                                <td><strong>Hạn sử dụng:</strong></td>
                                <td><?= $product['expiry_days'] ?> ngày</td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($product['sku'])): ?>
                            <tr>
                                <td><strong>SKU:</strong></td>
                                <td><?= htmlspecialchars($product['sku']) ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>

                    <div class="details-section">
                        <h4>Thông Tin Data</h4>
                        <table class="details-table">
                            <tr>
                                <td><strong>Số record:</strong></td>
                                <td><?= number_format($product['record_count'] ?? 0) ?></td>
                            </tr>
                            <?php if (!empty($product['data_size'])): ?>
                            <tr>
                                <td><strong>Dung lượng:</strong></td>
                                <td><?= htmlspecialchars($product['data_size']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($product['data_format'])): ?>
                            <tr>
                                <td><strong>Định dạng:</strong></td>
                                <td><?= htmlspecialchars($product['data_format']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($product['data_source'])): ?>
                            <tr>
                                <td><strong>Nguồn gốc:</strong></td>
                                <td><?= htmlspecialchars($product['data_source']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($product['reliability'])): ?>
                            <tr>
                                <td><strong>Độ tin cậy:</strong></td>
                                <td><?= htmlspecialchars($product['reliability']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($product['quota'])): ?>
                            <tr>
                                <td><strong>Quota (lần tải):</strong></td>
                                <td><?= $product['quota'] ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($product['quota_per_usage'])): ?>
                            <tr>
                                <td><strong>Tải mỗi lần:</strong></td>
                                <td><?= $product['quota_per_usage'] ?> records</td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tab: Nhà Cung Cấp -->
            <div class="tab-pane" id="tab-supplier">
                <?php if (!empty($product['supplier_name'])): ?>
                <div class="details-grid">
                    <div class="details-section">
                        <h4>Thông Tin Nhà Cung Cấp</h4>
                        <table class="details-table">
                            <?php if (!empty($product['supplier_name'])): ?>
                            <tr>
                                <td><strong>Tên:</strong></td>
                                <td><?= htmlspecialchars($product['supplier_name']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($product['supplier_title'])): ?>
                            <tr>
                                <td><strong>Chức danh:</strong></td>
                                <td><?= htmlspecialchars($product['supplier_title']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($product['supplier_bio'])): ?>
                            <tr>
                                <td><strong>Giới thiệu:</strong></td>
                                <td><?= nl2br(htmlspecialchars($product['supplier_bio'])) ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>

                    <?php if (!empty($supplierSocial)): ?>
                    <div class="details-section">
                        <h4>Liên Hệ</h4>
                        <table class="details-table">
                            <?php if (!empty($supplierSocial['website'])): ?>
                            <tr>
                                <td><strong>Website:</strong></td>
                                <td><a href="<?= htmlspecialchars($supplierSocial['website']) ?>" target="_blank"><?= htmlspecialchars($supplierSocial['website']) ?></a></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($supplierSocial['hotline'])): ?>
                            <tr>
                                <td><strong>Hotline:</strong></td>
                                <td><a href="tel:<?= htmlspecialchars($supplierSocial['hotline']) ?>"><?= htmlspecialchars($supplierSocial['hotline']) ?></a></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($supplierSocial['zalo'])): ?>
                            <tr>
                                <td><strong>Zalo:</strong></td>
                                <td><?= htmlspecialchars($supplierSocial['zalo']) ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="empty-state" style="padding: 40px;">
                    <i class="fas fa-building"></i>
                    <h3>Chưa có thông tin nhà cung cấp</h3>
                </div>
                <?php endif; ?>
            </div>

            <!-- Tab: Lợi Ích -->
            <div class="tab-pane" id="tab-benefits">
                <?php if (!empty($benefits)): ?>
                <div class="benefits-list">
                    <?php foreach ($benefits as $benefit): ?>
                    <div class="benefit-item">
                        <i class="fas fa-check-circle"></i>
                        <span><?= htmlspecialchars($benefit) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="empty-state" style="padding: 40px;">
                    <i class="fas fa-gift"></i>
                    <h3>Chưa có lợi ích</h3>
                </div>
                <?php endif; ?>
            </div>

            <!-- Tab: Cấu Trúc -->
            <div class="tab-pane" id="tab-structure">
                <?php if (!empty($dataStructure)): ?>
                <div class="structure-list">
                    <?php foreach ($dataStructure as $index => $section): ?>
                    <div class="structure-section">
                        <h4><?= $index + 1 ?>. <?= htmlspecialchars($section['title'] ?? 'Nhóm thông tin') ?></h4>
                        <span class="structure-count"><?= count($section['items'] ?? []) ?> trường</span>
                        <?php if (!empty($section['items'])): ?>
                        <ul class="structure-items">
                            <?php foreach ($section['items'] as $item): ?>
                            <li><?= htmlspecialchars($item['title'] ?? '') ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="empty-state" style="padding: 40px;">
                    <i class="fas fa-sitemap"></i>
                    <h3>Chưa có cấu trúc data</h3>
                </div>
                <?php endif; ?>
            </div>

            <!-- Tab: Đơn Hàng -->
            <div class="tab-pane" id="tab-orders">
                <?php if (empty($product_orders)): ?>
                <div class="empty-state" style="padding: 40px;">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>Chưa có đơn hàng nào</h3>
                </div>
                <?php else: ?>
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID Đơn hàng</th>
                                <th>Khách hàng</th>
                                <th>Số lượng</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th>Ngày đặt</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($product_orders as $order): ?>
                            <tr>
                                <td>#<?= $order['id'] ?></td>
                                <td>Khách hàng #<?= $order['user_id'] ?></td>
                                <td><?= $order['quantity'] ?></td>
                                <td><?= formatPrice($order['item_total'] ?? $order['total']) ?></td>
                                <td>
                                    <span class="status-badge <?= $order['status'] ?>">
                                        <?= ucfirst($order['status']) ?>
                                    </span>
                                </td>
                                <td><?= formatDate($order['created_at']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
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
.benefits-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 16px;
}

.benefit-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    background: #f9fafb;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
}

.benefit-item i {
    color: #10b981;
    font-size: 20px;
}

.benefit-item span {
    color: #374151;
    font-size: 14px;
}

.structure-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.structure-section {
    padding: 20px;
    background: #f9fafb;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
}

.structure-section h4 {
    font-size: 16px;
    font-weight: 600;
    color: #111827;
    margin: 0 0 4px 0;
}

.structure-count {
    font-size: 12px;
    color: #6b7280;
}

.structure-items {
    margin: 12px 0 0 0;
    padding-left: 20px;
}

.structure-items li {
    color: #374151;
    font-size: 14px;
    margin-bottom: 4px;
}

.product-type-badge {
    display: inline-block;
    padding: 4px 12px;
    background: #356DF1;
    color: #fff;
    font-size: 12px;
    font-weight: 600;
    border-radius: 6px;
    text-transform: uppercase;
    margin-bottom: 8px;
}

.product-price-display {
    display: flex;
    align-items: baseline;
    gap: 12px;
    margin: 16px 0;
    padding: 16px;
    background: #f9fafb;
    border-radius: 8px;
}

.product-price-display .price-current {
    font-size: 28px;
    font-weight: 700;
    color: #dc2626;
}

.product-price-display .price-original {
    font-size: 16px;
    color: #9ca3af;
    text-decoration: line-through;
}

.product-price-display .price-discount {
    padding: 4px 8px;
    background: #dc2626;
    color: #fff;
    font-size: 12px;
    font-weight: 600;
    border-radius: 4px;
}
</style>
