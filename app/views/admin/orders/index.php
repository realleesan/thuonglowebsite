<?php
/**
 * Admin Orders Index
 * Sử dụng AdminService thông qua ServiceManager
 */

// Xử lý delete request (trước khi hiển thị layout)
$action = $_GET['action'] ?? '';
if ($action === 'delete' && isset($_GET['id'])) {
    $delete_id = (int)$_GET['id'];
    if ($delete_id > 0) {
        // Debug: log what's available
        error_log("Delete action triggered. delete_id=$delete_id");
        error_log("currentService set: " . (isset($currentService) ? 'YES' : 'NO'));
        error_log("GLOBALS[adminService] set: " . (isset($GLOBALS['adminService']) ? 'YES' : 'NO'));
        error_log("serviceManager set: " . (isset($serviceManager) ? 'YES' : 'NO'));
        
        // Sử dụng service để xóa - thử nhiều cách để lấy service
        $service = null;
        
        // Cách 1: từ $currentService (được set trong index.php chính)
        if (isset($currentService)) {
            $service = $currentService;
        }
        // Cách 2: từ global $adminService (được set trong view_init.php)
        elseif (isset($GLOBALS['adminService'])) {
            $service = $GLOBALS['adminService'];
        }
        // Cách 3: gọi ServiceManager trực tiếp
        else {
            global $serviceManager;
            if ($serviceManager) {
                $service = $serviceManager->getService('admin');
            }
        }
        
        error_log("Service obtained: " . ($service ? 'YES' : 'NO'));
        
        if ($service) {
            try {
                $result = $service->deleteOrder($delete_id);
                error_log("Delete result: " . ($result ? 'SUCCESS' : 'FAILED'));
            } catch (Exception $e) {
                error_log("Delete exception: " . $e->getMessage());
            }
        } else {
            error_log("Delete failed - no service available!");
        }
        // Redirect về danh sách
        header('Location: ?page=admin&module=orders');
        exit;
    }
}

// Chọn service admin - thử nhiều cách
$service = null;
if (isset($currentService)) {
    $service = $currentService;
} elseif (isset($GLOBALS['adminService'])) {
    $service = $GLOBALS['adminService'];
} else {
    global $serviceManager;
    if ($serviceManager) {
        $service = $serviceManager->getService('admin');
    }
}

if (!$service) {
    die('Service not available');
}

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
        'status' => $_GET['status'] ?? '',
        'payment_method' => $_GET['payment'] ?? '',
        'date_from' => $_GET['date_from'] ?? '',
        'date_to' => $_GET['date_to'] ?? ''
    ];
    
    $current_page = max(1, (int)($_GET['page'] ?? 1));
    $per_page = 10;
    
    // Get orders data using AdminService
    $ordersData = $service->getOrdersData($current_page, $per_page, $filters);
    $orders = $ordersData['orders'];
    $pagination = $ordersData['pagination'];
    $stats = $ordersData['stats'];
    $total_orders = $ordersData['total'];
    
    // Extract filter values for form
    $search = $filters['search'];
    $status_filter = $filters['status'];
    $payment_filter = $filters['payment_method'];
    $date_from = $filters['date_from'];
    $date_to = $filters['date_to'];
    
    // Pagination values
    $total_pages = $pagination['last_page'];
    $current_page = $pagination['current_page'];
    
} catch (Exception $e) {
    $errorHandler->logError('Admin Orders Index View Error', $e);
    $orders = [];
    $stats = ['total' => 0, 'pending' => 0];
    $total_orders = 0;
    $total_pages = 1;
    $current_page = 1;
    $pagination = ['current_page' => 1, 'total' => 0];
}

// Format price function
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' VNĐ';
}

// Format date function
function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}

// Get status label
function getStatusLabel($status) {
    $labels = [
        'pending' => 'Chờ xử lý',
        'processing' => 'Đang xử lý',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã hủy'
    ];
    return $labels[$status] ?? $status;
}

// Get payment method label
function getPaymentMethodLabel($method) {
    $labels = [
        'bank_transfer' => 'Chuyển khoản',
        'momo' => 'MoMo',
        'vnpay' => 'VNPay',
        'cod' => 'Thanh toán khi nhận hàng'
    ];
    return $labels[$method] ?? $method;
}
?>

<div class="orders-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-shopping-cart"></i>
                Quản Lý Đơn Hàng
            </h1>
            <p class="page-description">Quản lý danh sách đơn hàng của hệ thống</p>
        </div>
        <div class="page-header-right">
            <div class="header-stats">
                <div class="stat-item">
                    <span class="stat-label">Tổng đơn hàng:</span>
                    <span class="stat-value"><?= $stats['total'] ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Chờ xử lý:</span>
                    <span class="stat-value pending"><?= $stats['pending'] ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-section">
        <form method="GET" class="filters-form">
            <input type="hidden" name="page" value="admin">
            <input type="hidden" name="module" value="orders">
            
            <div class="filter-group">
                <div class="filter-item">
                    <label for="search">Tìm kiếm:</label>
                    <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" 
                           placeholder="ID đơn hàng, tên khách hàng, sản phẩm...">
                </div>
                
                <div class="filter-item">
                    <label for="status">Trạng thái:</label>
                    <select id="status" name="status">
                        <option value="">Tất cả trạng thái</option>
                        <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>Chờ xử lý</option>
                        <option value="processing" <?= $status_filter == 'processing' ? 'selected' : '' ?>>Đang xử lý</option>
                        <option value="completed" <?= $status_filter == 'completed' ? 'selected' : '' ?>>Hoàn thành</option>
                        <option value="cancelled" <?= $status_filter == 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                    </select>
                </div>
                
                <div class="filter-item">
                    <label for="payment">Thanh toán:</label>
                    <select id="payment" name="payment">
                        <option value="">Tất cả phương thức</option>
                        <option value="bank_transfer" <?= $payment_filter == 'bank_transfer' ? 'selected' : '' ?>>Chuyển khoản</option>
                        <option value="momo" <?= $payment_filter == 'momo' ? 'selected' : '' ?>>MoMo</option>
                        <option value="vnpay" <?= $payment_filter == 'vnpay' ? 'selected' : '' ?>>VNPay</option>
                        <option value="cod" <?= $payment_filter == 'cod' ? 'selected' : '' ?>>COD</option>
                    </select>
                </div>
                
                <div class="filter-item">
                    <label for="date_from">Từ ngày:</label>
                    <input type="date" id="date_from" name="date_from" value="<?= htmlspecialchars($date_from) ?>">
                </div>
                
                <div class="filter-item">
                    <label for="date_to">Đến ngày:</label>
                    <input type="date" id="date_to" name="date_to" value="<?= htmlspecialchars($date_to) ?>">
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-search"></i>
                        Lọc
                    </button>
                    <a href="?page=admin&module=orders" class="btn btn-outline">
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
            Hiển thị <?= count($orders) ?> trong tổng số <?= $total_orders ?> đơn hàng
        </span>
    </div>

    <!-- Orders Table -->
    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th width="40">
                        <input type="checkbox" id="select-all">
                    </th>
                    <th width="80">ID</th>
                    <th width="150">Khách hàng</th>
                    <th width="100">Ảnh</th>
                    <th>Sản phẩm</th>
                    <th width="120">Tổng tiền</th>
                    <th width="120">Thanh toán</th>
                    <th width="100">Trạng thái</th>
                    <th width="120">Ngày đặt</th>
                    <th width="120">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="10" class="no-data">
                            <i class="fas fa-inbox"></i>
                            <p>Không tìm thấy đơn hàng nào</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="order-checkbox" value="<?= $order['id'] ?>">
                            </td>
                            <td>
                                <strong>#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></strong>
                            </td>
                            <td>
                                <div class="customer-info">
                                    <h4 class="customer-name"><?= htmlspecialchars($order['user_name'] ?? 'N/A') ?></h4>
                                    <p class="customer-email"><?= htmlspecialchars($order['user_email'] ?? 'N/A') ?></p>
                                </div>
                            </td>
                            <td>
                                <?php $productImage = $order['product_image'] ?? ''; ?>
                                <?php if ($productImage): ?>
                                    <img src="<?= htmlspecialchars($productImage) ?>" alt="Product" class="product-thumbnail" onerror="this.src='<?= asset_url('images/placeholder.jpg') ?>'">
                                <?php else: ?>
                                    <span class="no-image"><i class="fas fa-image"></i></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="product-info">
                                    <h4 class="product-name"><?= htmlspecialchars($order['product_name'] ?? 'Sản phẩm đã xóa') ?></h4>
                                    <p class="product-price"><?= formatPrice($order['product_price'] ?? 0) ?></p>
                                    <?php if (!empty($order['category_name'])): ?>
                                        <p class="product-category"><?= htmlspecialchars($order['category_name']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="price-cell">
                                <?= formatPrice($order['total']) ?>
                            </td>
                            <td>
                                <span class="payment-badge payment-<?= $order['payment_method'] ?>">
                                    <?= getPaymentMethodLabel($order['payment_method']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-<?= $order['status'] ?>">
                                    <?= getStatusLabel($order['status']) ?>
                                </span>
                            </td>
                            <td><?= formatDate($order['created_at']) ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="?page=admin&module=orders&action=view&id=<?= $order['id'] ?>" 
                                       class="btn btn-sm btn-info" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="?page=admin&module=orders&action=edit&id=<?= $order['id'] ?>" 
                                       class="btn btn-sm btn-warning" title="Cập nhật trạng thái">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger delete-btn" 
                                            data-id="<?= $order['id'] ?>" data-name="<?= htmlspecialchars($order['product_name'] ?? 'đơn hàng #' . $order['id']) ?>"
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
                    <a href="?page=admin&module=orders&<?= http_build_query(array_merge($_GET, ['page' => $current_page - 1])) ?>" 
                       class="pagination-btn">
                        <i class="fas fa-chevron-left"></i>
                        Trước
                    </a>
                <?php endif; ?>

                <?php
                $start_page = max(1, $current_page - 2);
                $end_page = min($total_pages, $current_page + 2);
                
                if ($start_page > 1): ?>
                    <a href="?page=admin&module=orders&<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>" 
                       class="pagination-number">1</a>
                    <?php if ($start_page > 2): ?>
                        <span class="pagination-dots">...</span>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <a href="?page=admin&module=orders&<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
                       class="pagination-number <?= $i == $current_page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($end_page < $total_pages): ?>
                    <?php if ($end_page < $total_pages - 1): ?>
                        <span class="pagination-dots">...</span>
                    <?php endif; ?>
                    <a href="?page=admin&module=orders&<?= http_build_query(array_merge($_GET, ['page' => $total_pages])) ?>" 
                       class="pagination-number"><?= $total_pages ?></a>
                <?php endif; ?>

                <?php if ($current_page < $total_pages): ?>
                    <a href="?page=admin&module=orders&<?= http_build_query(array_merge($_GET, ['page' => $current_page + 1])) ?>" 
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