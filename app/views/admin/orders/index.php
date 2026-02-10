<?php
// Load Models
require_once __DIR__ . '/../../../models/OrdersModel.php';
require_once __DIR__ . '/../../../models/UsersModel.php';

$ordersModel = new OrdersModel();
$usersModel = new UsersModel();

// Search and filter parameters
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$payment_filter = $_GET['payment'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$current_page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;

// Build query conditions
$conditions = [];
$bindings = [];

if (!empty($search)) {
    $conditions[] = "(o.order_number LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
    $searchTerm = "%{$search}%";
    $bindings = array_merge($bindings, [$searchTerm, $searchTerm, $searchTerm]);
}

if (!empty($status_filter)) {
    $conditions[] = "o.status = ?";
    $bindings[] = $status_filter;
}

if (!empty($payment_filter)) {
    $conditions[] = "o.payment_status = ?";
    $bindings[] = $payment_filter;
}

if (!empty($date_from)) {
    $conditions[] = "DATE(o.created_at) >= ?";
    $bindings[] = $date_from;
}

if (!empty($date_to)) {
    $conditions[] = "DATE(o.created_at) <= ?";
    $bindings[] = $date_to;
}

// Get total count for pagination
$countSql = "SELECT COUNT(*) as total FROM orders o LEFT JOIN users u ON o.user_id = u.id";
if (!empty($conditions)) {
    $countSql .= " WHERE " . implode(' AND ', $conditions);
}
$totalResult = $ordersModel->db->query($countSql, $bindings);
$total_orders = $totalResult[0]['total'] ?? 0;

// Calculate pagination
$total_pages = ceil($total_orders / $per_page);
$current_page = max(1, min($total_pages, $current_page));
$offset = ($current_page - 1) * $per_page;

// Get orders with user info
$sql = "
    SELECT o.*, u.name as user_name, u.email as user_email,
           COUNT(oi.id) as items_count
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    LEFT JOIN order_items oi ON o.id = oi.order_id
";
if (!empty($conditions)) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}
$sql .= " GROUP BY o.id ORDER BY o.created_at DESC LIMIT {$per_page} OFFSET {$offset}";

$filtered_orders = $ordersModel->db->query($sql, $bindings);
        $user_name = $user_lookup[$order['user_id']]['name'] ?? '';
        $product_name = $product_lookup[$order['product_id']]['name'] ?? '';
        return stripos($user_name, $search) !== false || 
               stripos($product_name, $search) !== false ||
               stripos($order['shipping_address'], $search) !== false ||
               stripos((string)$order['id'], $search) !== false;
    });
}

if (!empty($status_filter)) {
    $filtered_orders = array_filter($filtered_orders, function($order) use ($status_filter) {
        return $order['status'] == $status_filter;
    });
}

if (!empty($payment_filter)) {
    $filtered_orders = array_filter($filtered_orders, function($order) use ($payment_filter) {
        return $order['payment_method'] == $payment_filter;
    });
}

if (!empty($date_from)) {
    $filtered_orders = array_filter($filtered_orders, function($order) use ($date_from) {
        return strtotime($order['created_at']) >= strtotime($date_from);
    });
}

if (!empty($date_to)) {
    $filtered_orders = array_filter($filtered_orders, function($order) use ($date_to) {
        return strtotime($order['created_at']) <= strtotime($date_to . ' 23:59:59');
    });
}

// Sort by created_at desc
usort($filtered_orders, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

// Pagination
$per_page = 10;
$total_orders = count($filtered_orders);
$total_pages = ceil($total_orders / $per_page);
$current_page = max(1, min($total_pages, (int)($_GET['page'] ?? 1)));
$offset = ($current_page - 1) * $per_page;
$paged_orders = array_slice($filtered_orders, $offset, $per_page);

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
                    <span class="stat-value"><?= count($orders) ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Chờ xử lý:</span>
                    <span class="stat-value pending"><?= count(array_filter($orders, fn($o) => $o['status'] == 'pending')) ?></span>
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
            Hiển thị <?= count($paged_orders) ?> trong tổng số <?= $total_orders ?> đơn hàng
        </span>
        
        <!-- Quick Actions -->
        <div class="quick-actions">
            <button type="button" class="btn btn-info" id="export-orders">
                <i class="fas fa-download"></i>
                Xuất Excel
            </button>
            <button type="button" class="btn btn-warning" id="bulk-update-status">
                <i class="fas fa-edit"></i>
                Cập nhật hàng loạt
            </button>
        </div>
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
                    <th>Sản phẩm</th>
                    <th width="80">SL</th>
                    <th width="120">Tổng tiền</th>
                    <th width="120">Thanh toán</th>
                    <th width="100">Trạng thái</th>
                    <th width="120">Ngày đặt</th>
                    <th width="120">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($paged_orders)): ?>
                    <tr>
                        <td colspan="10" class="no-data">
                            <i class="fas fa-inbox"></i>
                            <p>Không tìm thấy đơn hàng nào</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($paged_orders as $order): ?>
                        <?php 
                        $user = $user_lookup[$order['user_id']] ?? null;
                        $product = $product_lookup[$order['product_id']] ?? null;
                        ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="order-checkbox" value="<?= $order['id'] ?>">
                            </td>
                            <td>
                                <strong>#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></strong>
                            </td>
                            <td>
                                <div class="customer-info">
                                    <h4 class="customer-name"><?= htmlspecialchars($user['name'] ?? 'N/A') ?></h4>
                                    <p class="customer-email"><?= htmlspecialchars($user['email'] ?? 'N/A') ?></p>
                                </div>
                            </td>
                            <td>
                                <div class="product-info">
                                    <h4 class="product-name"><?= htmlspecialchars($product['name'] ?? 'Sản phẩm đã xóa') ?></h4>
                                    <p class="product-price"><?= formatPrice($product['price'] ?? 0) ?></p>
                                </div>
                            </td>
                            <td class="quantity-cell">
                                <span class="quantity-badge"><?= $order['quantity'] ?></span>
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
                                            data-id="<?= $order['id'] ?>" data-customer="<?= htmlspecialchars($user['name'] ?? 'N/A') ?>" 
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

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Xác nhận xóa đơn hàng</h3>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa đơn hàng của khách hàng <strong id="deleteCustomerName"></strong>?</p>
                <p class="text-danger">Hành động này không thể hoàn tác!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelDelete">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Xóa</button>
            </div>
        </div>
    </div>

    <!-- Bulk Update Status Modal -->
    <div id="bulkUpdateModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Cập nhật trạng thái hàng loạt</h3>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="bulk-status">Trạng thái mới:</label>
                    <select id="bulk-status" class="form-control">
                        <option value="">Chọn trạng thái</option>
                        <option value="pending">Chờ xử lý</option>
                        <option value="processing">Đang xử lý</option>
                        <option value="completed">Hoàn thành</option>
                        <option value="cancelled">Đã hủy</option>
                    </select>
                </div>
                <p class="selected-count">Đã chọn <span id="selectedCount">0</span> đơn hàng</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelBulkUpdate">Hủy</button>
                <button type="button" class="btn btn-primary" id="confirmBulkUpdate">Cập nhật</button>
            </div>
        </div>
    </div>
</div>