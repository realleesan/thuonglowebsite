<?php
$service = isset($currentService) ? $currentService : ($adminService ?? null);

/**
 * Admin Products View - Dynamic Version
 * Converted from direct model usage to AdminService
 */

// Load required services
// Initialize services
// Get product ID from URL
$product_id = (int)($_GET['id'] ?? 0);

// Initialize data variables
$product = null;
$categories = [];
$showErrorMessage = false;
$errorMessage = '';

try {
    if (!$product_id) {
        throw new Exception('ID sản phẩm không hợp lệ');
    }
    
    // Get admin product details data
    $productData = $service->getProductDetailsData($product_id);
    
    // Extract data
    $product = $productData['product'] ?? null;
    $categories = $productData['categories'] ?? [];
    
    if (!$product) {
        throw new Exception('Không tìm thấy sản phẩm');
    }
    
} catch (Exception $e) {
    // Handle errors gracefully
    $result = $errorHandler->handleViewError($e, 'admin_product_details', []);
    $showErrorMessage = true;
    $errorMessage = $result['message'];
    
    // Redirect if product not found
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

function getStatusBadge($status) {
    $badges = [
        'active' => '<span class="status-badge status-active">Đang hoạt động</span>',
        'inactive' => '<span class="status-badge status-inactive">Không hoạt động</span>',
        'pending' => '<span class="status-badge status-pending">Chờ duyệt</span>',
        'out_of_stock' => '<span class="status-badge status-out">Hết hàng</span>',
    ];
    return $badges[$status] ?? '<span class="status-badge">' . htmlspecialchars($status) . '</span>';
}
?>
if (!$product) {
    header('Location: ?page=admin&module=products&error=not_found');
    exit;
}

// Find category
$category = null;
foreach ($categories as $c) {
    if ($c['id'] == $product['category_id']) {
        $category = $c;
        break;
    }
}

// Get orders for this product
$product_orders = array_filter($orders, function($order) use ($product_id) {
    return $order['product_id'] == $product_id;
});

// Calculate stats
$total_sold = array_sum(array_column($product_orders, 'quantity'));
$total_revenue = array_sum(array_column($product_orders, 'total'));
$avg_rating = 0;
$total_reviews = 0;

// Get real rating from service if available
if ($service && method_exists($service, 'getProductRating')) {
    $ratingData = $service->getProductRating($productId ?? 0);
    $avg_rating = $ratingData['avg_rating'] ?? 0;
    $total_reviews = $ratingData['total_reviews'] ?? 0;
}

// Format functions
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' VNĐ';
}

function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}

function getStatusBadge($status) {
    $badges = [
        'active' => '<span class="status-badge status-active">Hoạt động</span>',
        'inactive' => '<span class="status-badge status-inactive">Không hoạt động</span>',
        'draft' => '<span class="status-badge status-draft">Nháp</span>',
        'archived' => '<span class="status-badge status-archived">Lưu trữ</span>'
    ];
    return $badges[$status] ?? '<span class="status-badge status-unknown">Không xác định</span>';
}
?>

<div class="products-page products-view-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-eye"></i>
                Chi Tiết Sản Phẩm
            </h1>
            <p class="page-description">Thông tin chi tiết sản phẩm #<?= $product['id'] ?></p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=products&action=edit&id=<?= $product['id'] ?>" class="btn btn-warning">
                <i class="fas fa-edit"></i>
                Chỉnh sửa
            </a>
            <button type="button" class="btn btn-danger" onclick="deleteProduct(<?= $product['id'] ?>, '<?= htmlspecialchars($product['name']) ?>')">
                <i class="fas fa-trash"></i>
                Xóa
            </button>
            <a href="?page=admin&module=products" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Quay lại danh sách
            </a>
        </div>
    </div>

    <!-- Product Overview -->
    <div class="product-overview">
        <div class="product-overview-grid">
            <!-- Product Image -->
            <div class="product-image-section">
                <div class="product-image-main">
                    <img src="<?= $product['image'] ?>" alt="<?= htmlspecialchars($product['name']) ?>" 
                         onerror="this.src='<?php echo asset_url('images/placeholder.jpg'); ?>'"">
                </div>
                <div class="product-image-info">
                    <p><strong>Hình ảnh:</strong> <?= basename($product['image']) ?></p>
                    <p><strong>Kích thước:</strong> <?= $product['width'] ?? 0 ?>x<?= $product['height'] ?? 0 ?>px</p>
                </div>
            </div>

            <!-- Product Info -->
            <div class="product-info-section">
                <div class="product-header">
                    <h2 class="product-name"><?= htmlspecialchars($product['name']) ?></h2>
                    <?= getStatusBadge($product['status']) ?>
                </div>

                <div class="product-meta">
                    <div class="meta-item">
                        <span class="meta-label">ID:</span>
                        <span class="meta-value">#<?= $product['id'] ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Danh mục:</span>
                        <span class="meta-value">
                            <span class="category-badge"><?= htmlspecialchars($category['name'] ?? 'N/A') ?></span>
                        </span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Giá:</span>
                        <span class="meta-value price-highlight"><?= formatPrice($product['price']) ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Tồn kho:</span>
                        <span class="meta-value">
                            <span class="stock-badge <?= $product['stock'] < 10 ? 'low-stock' : '' ?>">
                                <?= $product['stock'] ?> sản phẩm
                            </span>
                        </span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Ngày tạo:</span>
                        <span class="meta-value"><?= formatDate($product['created_at']) ?></span>
                    </div>
                </div>

                <div class="product-description">
                    <h4>Mô tả sản phẩm:</h4>
                    <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                </div>

                <!-- Product Stats - Moved here -->
                <div class="product-stats">
                    <h4>Thống Kê Bán Hàng:</h4>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number" title="<?= $total_sold ?> sản phẩm đã bán"><?= $total_sold ?></div>
                                <div class="stat-label">Đã bán</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number" title="Tổng doanh thu: <?= formatPrice($total_revenue) ?>"><?= formatPrice($total_revenue) ?></div>
                                <div class="stat-label">Doanh thu</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number" title="Đánh giá trung bình: <?= $avg_rating ?>/5 sao"><?= $avg_rating ?>/5</div>
                                <div class="stat-label">Đánh giá TB</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-eye"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number" title="<?= number_format($product['view_count'] ?? 0, 0, ',', '.') ?> lượt xem"><?= number_format($product['view_count'] ?? 0, 0, ',', '.') ?></div>
                                <div class="stat-label">Lượt xem</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Information Tabs -->
    <div class="product-details-tabs">
        <div class="tabs-header">
            <button class="tab-btn active" data-tab="details">Chi Tiết</button>
            <button class="tab-btn" data-tab="orders">Đơn Hàng</button>
            <button class="tab-btn" data-tab="reviews">Đánh Giá</button>
            <button class="tab-btn" data-tab="seo">SEO</button>
            <button class="tab-btn" data-tab="history">Lịch Sử</button>
        </div>

        <div class="tabs-content">
            <!-- Details Tab -->
            <div class="tab-content active" id="details">
                <div class="details-grid">
                    <div class="details-section">
                        <h4>Thông Tin Sản Phẩm</h4>
                        <table class="details-table">
                            <tr>
                                <td><strong>Mã SKU:</strong></td>
                                <td>SKU-<?= $product['id'] ?></td>
                            </tr>
                            <tr>
                                <td><strong>Trọng lượng:</strong></td>
                                <td><?= $product['weight'] ?? 0 ?>g</td>
                            </tr>
                            <tr>
                                <td><strong>Kích thước:</strong></td>
                                <td><?= $product['width'] ?? 0 ?> x <?= $product['height'] ?? 0 ?> x <?= $product['depth'] ?? 0 ?> cm</td>
                            </tr>
                            <tr>
                                <td><strong>Màu sắc:</strong></td>
                                <td>Đa màu</td>
                            </tr>
                            <tr>
                                <td><strong>Chất liệu:</strong></td>
                                <td>Digital</td>
                            </tr>
                        </table>
                    </div>

                    <div class="details-section">
                        <h4>Thông Tin Kho</h4>
                        <table class="details-table">
                            <tr>
                                <td><strong>Tồn kho hiện tại:</strong></td>
                                <td>
                                    <span class="stock-badge <?= $product['stock'] < 10 ? 'low-stock' : '' ?>">
                                        <?= $product['stock'] ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Tồn kho tối thiểu:</strong></td>
                                <td>10</td>
                            </tr>
                            <tr>
                                <td><strong>Đã bán:</strong></td>
                                <td><?= $total_sold ?></td>
                            </tr>
                            <tr>
                                <td><strong>Đang chờ xử lý:</strong></td>
                                <td><?= count(array_filter($product_orders, function($o) { return $o['status'] == 'pending'; })) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Orders Tab -->
            <div class="tab-content" id="orders">
                <div class="orders-section">
                    <h4>Đơn Hàng Liên Quan (<?= count($product_orders) ?>)</h4>
                    <?php if (empty($product_orders)): ?>
                        <div class="no-data">
                            <i class="fas fa-shopping-cart"></i>
                            <p>Chưa có đơn hàng nào cho sản phẩm này</p>
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
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($product_orders as $order): ?>
                                        <tr>
                                            <td>#<?= $order['id'] ?></td>
                                            <td>Khách hàng #<?= $order['user_id'] ?></td>
                                            <td><?= $order['quantity'] ?></td>
                                            <td><?= formatPrice($order['total']) ?></td>
                                            <td>
                                                <span class="status-badge status-<?= $order['status'] ?>">
                                                    <?= ucfirst($order['status']) ?>
                                                </span>
                                            </td>
                                            <td><?= formatDate($order['created_at']) ?></td>
                                            <td>
                                                <a href="?page=admin&module=orders&action=view&id=<?= $order['id'] ?>" 
                                                   class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
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

            <!-- Reviews Tab -->
            <div class="tab-content" id="reviews">
                <div class="reviews-section">
                    <div class="reviews-summary">
                        <div class="rating-overview">
                            <div class="rating-score">
                                <span class="score"><?= $avg_rating ?></span>
                                <div class="stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?= $i <= floor($avg_rating) ? 'active' : '' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="total-reviews">(<?= $total_reviews ?> đánh giá)</span>
                            </div>
                        </div>
                    </div>

                    <h4>Đánh Giá Gần Đây</h4>
                    <div class="reviews-list">
                        <?php
                        // Get real reviews from service if available
                        $reviews = [];
                        if ($service && method_exists($service, 'getProductReviews')) {
                            $reviews = $service->getProductReviews($productId ?? 0, 3);
                        }
                        foreach ($reviews as $review): ?>
                            <div class="review-item">
                                <div class="review-header">
                                    <div class="reviewer-info">
                                        <strong><?= htmlspecialchars($review['customer_name'] ?? 'Khách hàng') ?></strong>
                                        <div class="review-stars">
                                            <?php for ($j = 1; $j <= 5; $j++): ?>
                                                <i class="fas fa-star <?= $j <= ($review['rating'] ?? 0) ? 'active' : '' ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <div class="review-date"><?= date('d/m/Y', strtotime($review['created_at'] ?? 'now')) ?></div>
                                </div>
                                <div class="review-content">
                                    <p><?= htmlspecialchars($review['content'] ?? '') ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($reviews)): ?>
                            <div class="empty-state">
                                <p>Chưa có đánh giá nào.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- SEO Tab -->
            <div class="tab-content" id="seo">
                <div class="seo-section">
                    <h4>Thông Tin SEO</h4>
                    <table class="details-table">
                        <tr>
                            <td><strong>Tiêu đề SEO:</strong></td>
                            <td><?= htmlspecialchars($product['name']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Mô tả SEO:</strong></td>
                            <td><?= htmlspecialchars(substr($product['description'], 0, 160)) ?>...</td>
                        </tr>
                        <tr>
                            <td><strong>URL:</strong></td>
                            <td>/san-pham/<?= strtolower(str_replace(' ', '-', $product['name'])) ?>-<?= $product['id'] ?></td>
                        </tr>
                        <tr>
                            <td><strong>Tags:</strong></td>
                            <td>
                                <span class="tag">dropshipping</span>
                                <span class="tag">data</span>
                                <span class="tag">kinh doanh</span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- History Tab -->
            <div class="tab-content" id="history">
                <div class="history-section">
                    <h4>Lịch Sử Thay Đổi</h4>
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <strong>Tạo sản phẩm</strong>
                                    <span class="timeline-date"><?= formatDate($product['created_at']) ?></span>
                                </div>
                                <p>Sản phẩm được tạo bởi Admin</p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <strong>Cập nhật giá</strong>
                                    <span class="timeline-date"><?= date('d/m/Y H:i', strtotime('-5 days')) ?></span>
                                </div>
                                <p>Giá được cập nhật từ <?= formatPrice($product['price'] - 500000) ?> thành <?= formatPrice($product['price']) ?></p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <strong>Cập nhật tồn kho</strong>
                                    <span class="timeline-date"><?= date('d/m/Y H:i', strtotime('-2 days')) ?></span>
                                </div>
                                <p>Tồn kho được cập nhật từ <?= $product['stock'] + 20 ?> thành <?= $product['stock'] ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Xác nhận xóa sản phẩm</h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <p>Bạn có chắc chắn muốn xóa sản phẩm <strong id="deleteProductName"></strong>?</p>
            <div class="warning-box">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <p><strong>Cảnh báo:</strong> Hành động này không thể hoàn tác!</p>
                    <p>Sản phẩm sẽ bị xóa khỏi hệ thống và tất cả dữ liệu liên quan sẽ bị mất.</p>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="cancelDelete">Hủy</button>
            <button type="button" class="btn btn-danger" id="confirmDelete">Xóa sản phẩm</button>
        </div>
    </div>
</div>