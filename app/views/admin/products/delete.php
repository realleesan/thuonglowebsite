<?php
// Load fake data
$fake_data = json_decode(file_get_contents(__DIR__ . '/../data/fake_data.json'), true);
$products = $fake_data['products'];
$orders = $fake_data['orders'];

// Get product ID
$product_id = (int)($_GET['id'] ?? 0);

// Find product
$product = null;
foreach ($products as $p) {
    if ($p['id'] == $product_id) {
        $product = $p;
        break;
    }
}

// If product not found, redirect
if (!$product) {
    header('Location: ?page=admin&module=products&error=not_found');
    exit;
}

// Check if product has orders
$product_orders = array_filter($orders, function($order) use ($product_id) {
    return $order['product_id'] == $product_id;
});

$has_orders = !empty($product_orders);
$can_delete = !$has_orders; // In real app, you might allow deletion with cascade

// Handle deletion
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    if (!$can_delete) {
        $error = 'Không thể xóa sản phẩm này vì đã có đơn hàng liên quan.';
    } else {
        // Demo: simulate deletion
        $success = true;
        // In real app: delete from database
        // header('Location: ?page=admin&module=products&success=deleted');
        // exit;
    }
}

function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' VNĐ';
}

function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}
?>

<div class="products-page products-delete-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-trash"></i>
                Xóa Sản Phẩm
            </h1>
            <p class="page-description">Xác nhận xóa sản phẩm khỏi hệ thống</p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=products&action=view&id=<?= $product['id'] ?>" class="btn btn-info">
                <i class="fas fa-eye"></i>
                Xem chi tiết
            </a>
            <a href="?page=admin&module=products" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Quay lại danh sách
            </a>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <div>
                <h4>Xóa sản phẩm thành công!</h4>
                <p>Sản phẩm "<?= htmlspecialchars($product['name']) ?>" đã được xóa khỏi hệ thống. (Demo - dữ liệu không được xóa thật)</p>
                <div class="alert-actions">
                    <a href="?page=admin&module=products" class="btn btn-primary">
                        <i class="fas fa-list"></i>
                        Quay lại danh sách sản phẩm
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Product Information -->
        <div class="delete-confirmation-container">
            <div class="product-summary">
                <div class="product-summary-image">
                    <img src="<?= $product['image'] ?>" alt="<?= htmlspecialchars($product['name']) ?>" 
                         onerror="this.src='<?php echo asset_url('images/placeholder.jpg'); ?>'"">
                </div>
                <div class="product-summary-info">
                    <h3><?= htmlspecialchars($product['name']) ?></h3>
                    <div class="product-meta">
                        <p><strong>ID:</strong> #<?= $product['id'] ?></p>
                        <p><strong>Giá:</strong> <?= formatPrice($product['price']) ?></p>
                        <p><strong>Tồn kho:</strong> <?= $product['stock'] ?> sản phẩm</p>
                        <p><strong>Trạng thái:</strong> 
                            <span class="status-badge status-<?= $product['status'] ?>">
                                <?= $product['status'] == 'active' ? 'Hoạt động' : 'Không hoạt động' ?>
                            </span>
                        </p>
                        <p><strong>Ngày tạo:</strong> <?= formatDate($product['created_at']) ?></p>
                    </div>
                </div>
            </div>

            <!-- Warning Section -->
            <div class="warning-section">
                <div class="warning-header">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h4>Cảnh báo quan trọng</h4>
                </div>
                
                <?php if ($has_orders): ?>
                    <div class="warning-content danger">
                        <h5>Không thể xóa sản phẩm này!</h5>
                        <p>Sản phẩm này đã có <strong><?= count($product_orders) ?> đơn hàng</strong> liên quan. 
                           Để bảo toàn tính toàn vẹn dữ liệu, bạn không thể xóa sản phẩm này.</p>
                        
                        <div class="related-orders">
                            <h6>Đơn hàng liên quan:</h6>
                            <ul>
                                <?php foreach (array_slice($product_orders, 0, 5) as $order): ?>
                                    <li>
                                        Đơn hàng #<?= $order['id'] ?> - 
                                        <?= formatPrice($order['total']) ?> - 
                                        <?= formatDate($order['created_at']) ?>
                                    </li>
                                <?php endforeach; ?>
                                <?php if (count($product_orders) > 5): ?>
                                    <li>... và <?= count($product_orders) - 5 ?> đơn hàng khác</li>
                                <?php endif; ?>
                            </ul>
                        </div>

                        <div class="alternative-actions">
                            <h6>Thay vào đó, bạn có thể:</h6>
                            <ul>
                                <li>Đặt sản phẩm ở trạng thái "Không hoạt động" để ẩn khỏi khách hàng</li>
                                <li>Đặt tồn kho về 0 để ngừng bán</li>
                                <li>Chuyển sản phẩm vào danh mục "Lưu trữ"</li>
                            </ul>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="warning-content">
                        <h5>Bạn sắp xóa sản phẩm này!</h5>
                        <p>Hành động này sẽ:</p>
                        <ul>
                            <li><strong>Xóa vĩnh viễn</strong> sản phẩm khỏi hệ thống</li>
                            <li><strong>Xóa tất cả</strong> thông tin liên quan (hình ảnh, mô tả, SEO...)</li>
                            <li><strong>Không thể hoàn tác</strong> sau khi thực hiện</li>
                        </ul>
                        <p class="text-danger"><strong>Lưu ý:</strong> Sản phẩm này chưa có đơn hàng nào, nên có thể xóa an toàn.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Action Buttons -->
            <div class="delete-actions">
                <?php if ($can_delete): ?>
                    <form method="POST" class="delete-form">
                        <div class="confirmation-checkbox">
                            <label>
                                <input type="checkbox" id="confirm-checkbox" required>
                                Tôi hiểu rằng hành động này không thể hoàn tác và đồng ý xóa sản phẩm này
                            </label>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" name="confirm_delete" class="btn btn-danger" id="delete-btn" disabled>
                                <i class="fas fa-trash"></i>
                                Xác nhận xóa sản phẩm
                            </button>
                            <a href="?page=admin&module=products&action=edit&id=<?= $product['id'] ?>" class="btn btn-warning">
                                <i class="fas fa-edit"></i>
                                Chỉnh sửa thay vì xóa
                            </a>
                            <a href="?page=admin&module=products" class="btn btn-secondary">
                                <i class="fas fa-times"></i>
                                Hủy bỏ
                            </a>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="form-actions">
                        <a href="?page=admin&module=products&action=edit&id=<?= $product['id'] ?>" class="btn btn-warning">
                            <i class="fas fa-edit"></i>
                            Chỉnh sửa sản phẩm
                        </a>
                        <button type="button" class="btn btn-info" onclick="deactivateProduct()">
                            <i class="fas fa-eye-slash"></i>
                            Vô hiệu hóa sản phẩm
                        </button>
                        <a href="?page=admin&module=products" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Quay lại danh sách
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    <?php endif; ?>
</div>

<!-- Deactivate Confirmation Modal -->
<div id="deactivateModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Vô hiệu hóa sản phẩm</h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <p>Bạn có muốn vô hiệu hóa sản phẩm <strong><?= htmlspecialchars($product['name']) ?></strong> thay vì xóa?</p>
            <p>Sản phẩm sẽ được ẩn khỏi khách hàng nhưng vẫn giữ nguyên dữ liệu và đơn hàng.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="cancelDeactivate">Hủy</button>
            <button type="button" class="btn btn-warning" id="confirmDeactivate">Vô hiệu hóa</button>
        </div>
    </div>
</div>