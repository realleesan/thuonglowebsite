<?php
// Load Models
require_once __DIR__ . '/../../models/OrdersModel.php';
require_once __DIR__ . '/../../models/UsersModel.php';
require_once __DIR__ . '/../../models/ProductsModel.php';

$ordersModel = new OrdersModel();
$usersModel = new UsersModel();
$productsModel = new ProductsModel();

// Get order ID from URL
$order_id = (int)($_GET['id'] ?? 0);

// Get order from database
$order = $ordersModel->getById($order_id);

// Redirect if order not found
if (!$order) {
    header('Location: ?page=admin&module=orders');
    exit;
}

// Get related data
$user = $usersModel->getById($order['user_id']);
$product = $productsModel->getById($order['product_id']);

// Format price function
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' VNĐ';
}

// Format date function
function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}

// Get status label and color
function getStatusInfo($status) {
    $info = [
        'pending' => ['label' => 'Chờ xử lý', 'color' => 'warning'],
        'processing' => ['label' => 'Đang xử lý', 'color' => 'info'],
        'completed' => ['label' => 'Hoàn thành', 'color' => 'success'],
        'cancelled' => ['label' => 'Đã hủy', 'color' => 'danger']
    ];
    return $info[$status] ?? ['label' => $status, 'color' => 'secondary'];
}

// Get payment method label
function getPaymentMethodLabel($method) {
    $labels = [
        'bank_transfer' => 'Chuyển khoản ngân hàng',
        'momo' => 'Ví MoMo',
        'vnpay' => 'VNPay',
        'cod' => 'Thanh toán khi nhận hàng'
    ];
    return $labels[$method] ?? $method;
}

// Generate fake order timeline
function generateOrderTimeline($order) {
    $timeline = [];
    $created_time = strtotime($order['created_at']);
    
    // Order created
    $timeline[] = [
        'title' => 'Đơn hàng được tạo',
        'description' => 'Khách hàng đã đặt hàng thành công',
        'time' => $order['created_at'],
        'status' => 'completed'
    ];
    
    // Payment received (if not COD)
    if ($order['payment_method'] != 'cod') {
        $timeline[] = [
            'title' => 'Thanh toán thành công',
            'description' => 'Đã nhận được thanh toán qua ' . getPaymentMethodLabel($order['payment_method']),
            'time' => date('Y-m-d H:i:s', $created_time + 1800), // +30 minutes
            'status' => 'completed'
        ];
    }
    
    // Processing
    if (in_array($order['status'], ['processing', 'completed'])) {
        $timeline[] = [
            'title' => 'Đang xử lý',
            'description' => 'Đơn hàng đang được chuẩn bị',
            'time' => date('Y-m-d H:i:s', $created_time + 3600), // +1 hour
            'status' => 'completed'
        ];
    }
    
    // Completed
    if ($order['status'] == 'completed') {
        $timeline[] = [
            'title' => 'Hoàn thành',
            'description' => 'Đơn hàng đã được giao thành công',
            'time' => date('Y-m-d H:i:s', $created_time + 86400), // +1 day
            'status' => 'completed'
        ];
    }
    
    // Cancelled
    if ($order['status'] == 'cancelled') {
        $timeline[] = [
            'title' => 'Đã hủy',
            'description' => 'Đơn hàng đã được hủy',
            'time' => date('Y-m-d H:i:s', $created_time + 7200), // +2 hours
            'status' => 'cancelled'
        ];
    }
    
    return $timeline;
}

$status_info = getStatusInfo($order['status']);
$timeline = generateOrderTimeline($order);
?>

<div class="orders-view-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-shopping-cart"></i>
                Chi Tiết Đơn Hàng #<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?>
            </h1>
            <p class="page-description">Thông tin chi tiết đơn hàng</p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=orders" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i>
                Quay lại
            </a>
            <a href="?page=admin&module=orders&action=edit&id=<?= $order['id'] ?>" class="btn btn-warning">
                <i class="fas fa-edit"></i>
                Cập nhật trạng thái
            </a>
        </div>
    </div>

    <!-- Order Overview -->
    <div class="order-overview">
        <!-- Order Summary -->
        <div class="order-summary-section">
            <div class="order-summary-card">
                <div class="summary-header">
                    <h3>Thông Tin Đơn Hàng</h3>
                    <span class="status-badge status-<?= $order['status'] ?> status-<?= $status_info['color'] ?>">
                        <?= $status_info['label'] ?>
                    </span>
                </div>
                
                <div class="summary-content">
                    <div class="summary-item">
                        <span class="item-label">Mã đơn hàng:</span>
                        <span class="item-value">#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></span>
                    </div>
                    
                    <div class="summary-item">
                        <span class="item-label">Ngày đặt:</span>
                        <span class="item-value"><?= formatDate($order['created_at']) ?></span>
                    </div>
                    
                    <div class="summary-item">
                        <span class="item-label">Phương thức thanh toán:</span>
                        <span class="item-value">
                            <span class="payment-badge payment-<?= $order['payment_method'] ?>">
                                <?= getPaymentMethodLabel($order['payment_method']) ?>
                            </span>
                        </span>
                    </div>
                    
                    <div class="summary-item">
                        <span class="item-label">Tổng tiền:</span>
                        <span class="item-value price-highlight"><?= formatPrice($order['total']) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Stats - Moved below and horizontal layout -->
        <div class="order-stats-section">
            <h4>Thông Tin Nhanh</h4>
            <div class="stats-horizontal">
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-label">Số lượng</div>
                        <div class="stat-value"><?= $order['quantity'] ?> sản phẩm</div>
                    </div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-label">Tổng tiền</div>
                        <div class="stat-value price-highlight"><?= formatPrice($order['total']) ?></div>
                    </div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-label">Ngày đặt</div>
                        <div class="stat-value"><?= formatDate($order['created_at']) ?></div>
                    </div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-label">Thanh toán</div>
                        <div class="stat-value"><?= getPaymentMethodLabel($order['payment_method']) ?></div>
                    </div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-label">Trạng thái</div>
                        <div class="stat-value">
                            <span class="status-badge status-<?= $order['status'] ?>">
                                <?= $status_info['label'] ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Details Tabs -->
    <div class="order-details-tabs">
        <div class="tabs-header">
            <button class="tab-btn active" data-tab="details">Chi Tiết</button>
            <button class="tab-btn" data-tab="customer">Khách Hàng</button>
            <button class="tab-btn" data-tab="product">Sản Phẩm</button>
            <button class="tab-btn" data-tab="timeline">Lịch Sử</button>
        </div>
        
        <div class="tabs-content">
            <!-- Details Tab -->
            <div class="tab-content active" id="details-tab">
                <div class="details-grid">
                    <!-- Order Information -->
                    <div class="details-section">
                        <h4>Thông Tin Đơn Hàng</h4>
                        <table class="details-table">
                            <tr>
                                <td>Mã đơn hàng:</td>
                                <td><strong>#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></strong></td>
                            </tr>
                            <tr>
                                <td>Trạng thái:</td>
                                <td>
                                    <span class="status-badge status-<?= $order['status'] ?>">
                                        <?= $status_info['label'] ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td>Ngày đặt hàng:</td>
                                <td><?= formatDate($order['created_at']) ?></td>
                            </tr>
                            <tr>
                                <td>Phương thức thanh toán:</td>
                                <td><?= getPaymentMethodLabel($order['payment_method']) ?></td>
                            </tr>
                            <tr>
                                <td>Số lượng:</td>
                                <td><?= $order['quantity'] ?></td>
                            </tr>
                            <tr>
                                <td>Tổng tiền:</td>
                                <td class="price-highlight"><?= formatPrice($order['total']) ?></td>
                            </tr>
                        </table>
                    </div>

                    <!-- Shipping Information -->
                    <div class="details-section">
                        <h4>Thông Tin Giao Hàng</h4>
                        <table class="details-table">
                            <tr>
                                <td>Địa chỉ giao hàng:</td>
                                <td><?= htmlspecialchars($order['shipping_address']) ?></td>
                            </tr>
                            <tr>
                                <td>Người nhận:</td>
                                <td><?= htmlspecialchars($user['name'] ?? 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td>Số điện thoại:</td>
                                <td><?= htmlspecialchars($user['phone'] ?? 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td>Email:</td>
                                <td><?= htmlspecialchars($user['email'] ?? 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td>Ghi chú:</td>
                                <td><em>Không có ghi chú</em></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Customer Tab -->
            <div class="tab-content" id="customer-tab">
                <?php if ($user): ?>
                    <div class="customer-details">
                        <div class="customer-header">
                            <div class="customer-avatar">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <div class="customer-info">
                                <h3><?= htmlspecialchars($user['name']) ?></h3>
                                <p><?= htmlspecialchars($user['email']) ?></p>
                                <span class="user-role role-<?= $user['role'] ?>">
                                    <?= ucfirst($user['role']) ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="customer-stats">
                            <div class="stat-item">
                                <span class="stat-label">Tổng đơn hàng:</span>
                                <span class="stat-value">
                                    <?php 
                                    $userOrders = $ordersModel->getByUser($user['id']);
                                    echo count($userOrders);
                                    ?>
                                </span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Tổng chi tiêu:</span>
                                <span class="stat-value">
                                    <?php 
                                    $totalSpent = array_sum(array_map(fn($o) => $o['total'], $userOrders));
                                    echo formatPrice($totalSpent);
                                    ?>
                                </span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Ngày tham gia:</span>
                                <span class="stat-value"><?= formatDate($user['created_at']) ?></span>
                            </div>
                        </div>
                        
                        <div class="customer-contact">
                            <h4>Thông Tin Liên Hệ</h4>
                            <table class="details-table">
                                <tr>
                                    <td>Họ tên:</td>
                                    <td><?= htmlspecialchars($user['name']) ?></td>
                                </tr>
                                <tr>
                                    <td>Email:</td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                </tr>
                                <tr>
                                    <td>Số điện thoại:</td>
                                    <td><?= htmlspecialchars($user['phone'] ?? 'Chưa cập nhật') ?></td>
                                </tr>
                                <tr>
                                    <td>Địa chỉ:</td>
                                    <td><?= htmlspecialchars($user['address'] ?? 'Chưa cập nhật') ?></td>
                                </tr>
                                <tr>
                                    <td>Trạng thái:</td>
                                    <td>
                                        <span class="status-badge status-<?= $user['status'] ?>">
                                            <?= $user['status'] == 'active' ? 'Hoạt động' : 'Không hoạt động' ?>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="no-data">
                        <i class="fas fa-user-slash"></i>
                        <p>Không tìm thấy thông tin khách hàng</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Product Tab -->
            <div class="tab-content" id="product-tab">
                <?php if ($product): ?>
                    <div class="product-details">
                        <div class="product-overview-grid">
                            <div class="product-image-section">
                                <div class="product-image-main">
                                    <img src="<?= $product['image'] ?>" alt="<?= htmlspecialchars($product['name']) ?>" 
                                         onerror="this.src='<?php echo asset_url('images/placeholder.jpg'); ?>'"">
                                </div>
                                <div class="product-image-info">
                                    <small>Click để phóng to</small>
                                </div>
                            </div>
                            
                            <div class="product-info-section">
                                <div class="product-header">
                                    <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                                    <span class="status-badge status-<?= $product['status'] ?>">
                                        <?= $product['status'] == 'active' ? 'Hoạt động' : 'Không hoạt động' ?>
                                    </span>
                                </div>
                                
                                <div class="product-meta">
                                    <div class="meta-item">
                                        <span class="meta-label">Giá:</span>
                                        <span class="meta-value price-highlight"><?= formatPrice($product['price']) ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <span class="meta-label">Tồn kho:</span>
                                        <span class="meta-value">
                                            <span class="stock-badge <?= $product['stock'] < 10 ? 'low-stock' : '' ?>">
                                                <?= $product['stock'] ?>
                                            </span>
                                        </span>
                                    </div>
                                    <div class="meta-item">
                                        <span class="meta-label">Ngày tạo:</span>
                                        <span class="meta-value"><?= formatDate($product['created_at']) ?></span>
                                    </div>
                                </div>
                                
                                <div class="product-description">
                                    <h4>Mô tả sản phẩm</h4>
                                    <p><?= htmlspecialchars($product['description']) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="no-data">
                        <i class="fas fa-box-open"></i>
                        <p>Sản phẩm đã bị xóa hoặc không tồn tại</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Timeline Tab -->
            <div class="tab-content" id="timeline-tab">
                <div class="timeline">
                    <?php foreach ($timeline as $index => $event): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker <?= $event['status'] ?>"></div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <strong><?= $event['title'] ?></strong>
                                    <span class="timeline-date"><?= formatDate($event['time']) ?></span>
                                </div>
                                <p><?= $event['description'] ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="order-actions">
        <div class="action-group">
            <h4>Hành Động</h4>
            <div class="action-buttons">
                <a href="?page=admin&module=orders&action=edit&id=<?= $order['id'] ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i>
                    Cập nhật trạng thái
                </a>
                <button type="button" class="btn btn-info" onclick="window.print()">
                    <i class="fas fa-print"></i>
                    In đơn hàng
                </button>
                <button type="button" class="btn btn-success" id="send-email">
                    <i class="fas fa-envelope"></i>
                    Gửi email khách hàng
                </button>
                <button type="button" class="btn btn-danger delete-btn" 
                        data-id="<?= $order['id'] ?>" data-customer="<?= htmlspecialchars($user['name'] ?? 'N/A') ?>">
                    <i class="fas fa-trash"></i>
                    Xóa đơn hàng
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Xác nhận xóa đơn hàng</h3>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa đơn hàng #<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?> của khách hàng <strong id="deleteCustomerName"></strong>?</p>
                <p class="text-danger">Hành động này không thể hoàn tác!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelDelete">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Xóa</button>
            </div>
        </div>
    </div>
</div>