<?php
/**
 * Admin Orders View
 * Sử dụng AdminService thông qua ServiceManager
 */

// Khởi tạo View & ServiceManager nếu chưa được khởi tạo
if (!defined('VIEW_INIT_LOADED')) {
    require_once __DIR__ . '/../../../../core/view_init.php';
}

// Chọn service admin - thử nhiều cách
$service = null;
if (isset($currentService)) {
    $service = $currentService;
} elseif (isset($GLOBALS['adminService'])) {
    $service = $GLOBALS['adminService'];
} elseif (isset($adminService)) {
    $service = $adminService;
} else {
    global $serviceManager;
    if ($serviceManager) {
        $service = $serviceManager->getService('admin');
    }
}

if (!$service) {
    die('Service not available. Please ensure you are accessing this page through the admin panel.');
}

// Get error handler if available
$errorHandler = null;
if (isset($GLOBALS['errorHandler'])) {
    $errorHandler = $GLOBALS['errorHandler'];
} elseif (class_exists('ErrorHandler')) {
    $errorHandler = new ErrorHandler();
}

try {
    // Get order ID from URL
    $order_id = (int)($_GET['id'] ?? 0);
    
    if (!$order_id) {
        header('Location: ?page=admin&module=orders&error=invalid_id');
        exit;
    }
    
    // Get order data using AdminService
    $orderData = $service->getOrderDetailsData($order_id);
    $order = $orderData['order'];
    $user = $orderData['user'];
    $orderItems = $orderData['order_items'];
    
    // Redirect if order not found
    if (!$order) {
        header('Location: ?page=admin&module=orders&error=not_found');
        exit;
    }
    
} catch (Exception $e) {
    $errorHandler->logError('Admin Orders View Error', $e);
    header('Location: ?page=admin&module=orders&error=system_error');
    exit;
}

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
                    
                    <!-- Products in Order -->
                    <?php if (!empty($orderItems)): ?>
                    <div class="order-products-inline">
                        <h4 class="products-inline-title">Sản phẩm trong đơn (<?= count($orderItems) ?>)</h4>
                        <div class="order-products-list">
                            <?php foreach ($orderItems as $item): ?>
                            <div class="order-product-row">
                                <div class="product-thumb">
                                    <?php if (!empty($item['product_image'])): ?>
                                        <?php $imgUrl = strpos($item['product_image'], 'http') === 0 ? $item['product_image'] : '/uploads/products/' . $item['product_image']; ?>
                                        <img src="<?= $imgUrl ?>" alt="<?= htmlspecialchars($item['product_name'] ?? 'Product') ?>">
                                    <?php else: ?>
                                        <div class="no-thumb"><i class="fas fa-image"></i></div>
                                    <?php endif; ?>
                                </div>
                                <div class="product-details">
                                    <span class="product-name"><?= htmlspecialchars($item['product_name'] ?? 'Sản phẩm #' . $item['product_id']) ?></span>
                                    <span class="product-meta">
                                        <?= formatPrice($item['product_price'] ?? $item['price'] ?? 0) ?> × <?= $item['quantity'] ?? 1 ?>
                                    </span>
                                </div>
                                <div class="product-subtotal">
                                    <?= formatPrice(($item['product_price'] ?? $item['price'] ?? 0) * ($item['quantity'] ?? 1)) ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="summary-item total-item">
                        <span class="item-label">Tổng tiền:</span>
                        <span class="item-value price-highlight"><?= formatPrice($order['total']) ?></span>
                    </div>
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
                <button type="button" class="btn btn-danger delete-btn" 
                        data-id="<?= $order['id'] ?>" data-customer="<?= htmlspecialchars($user['name'] ?? 'N/A') ?>"
                        onclick="showProductDeleteModal(<?= $order['id'] ?>, '<?= htmlspecialchars($order['order_number'] ?? 'đơn hàng này') ?>')">
                    <i class="fas fa-trash"></i>
                    Xóa đơn hàng
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal - New Implementation -->
    <div id="productDeleteModal" style="display: none;">
        <div class="product-modal-overlay"></div>
        <div class="product-modal-container">
            <div class="product-modal-header">
                <h3>Xác nhận xóa</h3>
                <button class="product-modal-close" onclick="closeProductDeleteModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa data "<strong id="productDeleteName"></strong>"?</p>
                <p class="product-modal-warning">Hành động này không thể hoàn tác!</p>
            </div>
            <div class="product-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeProductDeleteModal()">Hủy</button>
                <button type="button" class="btn btn-danger" id="prConfirmDeleteBtn">Xóa</button>
            </div>
        </div>
    </div>

    <style>
    .product-thumbnail {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid #e5e7eb;
    }
    
    .no-image {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 50px;
        height: 50px;
        background: #f3f4f6;
        border-radius: 4px;
        color: #9ca3af;
    }
    
    .product-category {
        font-size: 11px;
        color: #6b7280;
        margin-top: 2px;
    }
    
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
    window.showProductDeleteModal = function(id, name) {
        const modal = document.getElementById('productDeleteModal');
        const nameElement = document.getElementById('productDeleteName');
    
        if (modal) {
            if (nameElement) {
                nameElement.textContent = name || 'đơn hàng này';
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

    // Handle confirm delete
    document.addEventListener('click', function(e) {
        if (e.target.id === 'prConfirmDeleteBtn') {
            const modal = document.getElementById('productDeleteModal');
            const deleteId = modal ? modal.dataset.deleteId : null;
            if (deleteId) {
                window.location.href = '?page=admin&module=orders&action=delete&id=' + deleteId;
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