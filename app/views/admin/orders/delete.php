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

// Handle form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $confirm_delete = isset($_POST['confirm_delete']);
    $delete_reason = trim($_POST['delete_reason'] ?? '');
    
    if (!$confirm_delete) {
        $error_message = 'Vui lòng xác nhận việc xóa đơn hàng';
    } elseif (empty($delete_reason)) {
        $error_message = 'Vui lòng nhập lý do xóa đơn hàng';
    } else {
        // Delete from database
        if ($ordersModel->delete($order_id)) {
            $success_message = 'Đơn hàng đã được xóa thành công!';
            header('Location: ?page=admin&module=orders&deleted=1');
            exit;
        } else {
            $error_message = 'Có lỗi xảy ra khi xóa đơn hàng';
        }
    }
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
        'bank_transfer' => 'Chuyển khoản ngân hàng',
        'momo' => 'Ví MoMo',
        'vnpay' => 'VNPay',
        'cod' => 'Thanh toán khi nhận hàng'
    ];
    return $labels[$method] ?? $method;
}

// Check if order can be safely deleted
$can_delete_safely = in_array($order['status'], ['cancelled', 'pending']);
$related_issues = [];

if ($order['status'] == 'completed') {
    $related_issues[] = 'Đơn hàng đã hoàn thành - có thể ảnh hưởng đến báo cáo doanh thu';
}

if ($order['status'] == 'processing') {
    $related_issues[] = 'Đơn hàng đang được xử lý - có thể gây nhầm lẫn cho khách hàng';
}

if ($order['payment_method'] != 'cod' && $order['status'] != 'cancelled') {
    $related_issues[] = 'Đơn hàng đã thanh toán - cần hoàn tiền cho khách hàng';
}
?>

<div class="orders-delete-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-trash"></i>
                Xóa Đơn Hàng #<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?>
            </h1>
            <p class="page-description">Xác nhận xóa đơn hàng khỏi hệ thống</p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=orders&action=view&id=<?= $order['id'] ?>" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i>
                Quay lại
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if ($success_message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <div>
                <strong>Thành công!</strong>
                <p><?= htmlspecialchars($success_message) ?></p>
                <p><a href="?page=admin&module=orders">Quay lại danh sách đơn hàng</a></p>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                <strong>Lỗi!</strong>
                <p><?= htmlspecialchars($error_message) ?></p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Order Summary -->
    <div class="delete-confirmation-container">
        <div class="order-summary">
            <div class="order-summary-image">
                <?php if ($product && $product['image']): ?>
                    <img src="<?= $product['image'] ?>" alt="<?= htmlspecialchars($product['name'] ?? '') ?>" 
                         onerror="this.src='<?php echo asset_url('images/placeholder.jpg'); ?>'"">
                <?php else: ?>
                    <div class="no-image">
                        <i class="fas fa-box"></i>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="order-summary-info">
                <h3>Đơn hàng #<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></h3>
                <div class="order-meta">
                    <p><i class="fas fa-user"></i> <strong>Khách hàng:</strong> <?= htmlspecialchars($user['name'] ?? 'N/A') ?></p>
                    <p><i class="fas fa-box"></i> <strong>Sản phẩm:</strong> <?= htmlspecialchars($product['name'] ?? 'Sản phẩm đã xóa') ?></p>
                    <p><i class="fas fa-calendar"></i> <strong>Ngày đặt:</strong> <?= formatDate($order['created_at']) ?></p>
                    <p><i class="fas fa-money-bill-wave"></i> <strong>Tổng tiền:</strong> <?= formatPrice($order['total']) ?></p>
                    <p><i class="fas fa-info-circle"></i> <strong>Trạng thái:</strong> 
                        <span class="status-badge status-<?= $order['status'] ?>">
                            <?= getStatusLabel($order['status']) ?>
                        </span>
                    </p>
                    <p><i class="fas fa-credit-card"></i> <strong>Thanh toán:</strong> <?= getPaymentMethodLabel($order['payment_method']) ?></p>
                </div>
            </div>
        </div>

        <!-- Warning Section -->
        <div class="warning-section">
            <div class="warning-header">
                <i class="fas fa-exclamation-triangle"></i>
                <h4>Cảnh báo quan trọng</h4>
            </div>
            
            <?php if ($can_delete_safely): ?>
                <div class="warning-content">
                    <h5>Đơn hàng có thể xóa an toàn</h5>
                    <p>Đơn hàng này có trạng thái "<?= getStatusLabel($order['status']) ?>" và có thể được xóa mà không gây ảnh hưởng nghiêm trọng đến hệ thống.</p>
                    
                    <div class="safe-delete-info">
                        <h6>Điều gì sẽ xảy ra khi xóa:</h6>
                        <ul>
                            <li>Đơn hàng sẽ bị xóa vĩnh viễn khỏi hệ thống</li>
                            <li>Thông tin đơn hàng sẽ không thể khôi phục</li>
                            <li>Lịch sử giao dịch sẽ bị mất</li>
                            <li>Báo cáo thống kê có thể bị ảnh hưởng</li>
                        </ul>
                    </div>
                </div>
            <?php else: ?>
                <div class="warning-content danger">
                    <h5>Cảnh báo: Xóa đơn hàng này có thể gây rủi ro</h5>
                    <p>Đơn hàng này có trạng thái "<?= getStatusLabel($order['status']) ?>" và việc xóa có thể gây ra các vấn đề sau:</p>
                    
                    <?php if (!empty($related_issues)): ?>
                        <div class="related-issues">
                            <h6>Các vấn đề có thể xảy ra:</h6>
                            <ul>
                                <?php foreach ($related_issues as $issue): ?>
                                    <li><?= htmlspecialchars($issue) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <div class="alternative-actions">
                        <h6>Các hành động thay thế được khuyến nghị:</h6>
                        <ul>
                            <li>Thay đổi trạng thái đơn hàng thành "Đã hủy" thay vì xóa</li>
                            <li>Liên hệ khách hàng để xác nhận trước khi xóa</li>
                            <li>Đảm bảo đã hoàn tiền (nếu có) trước khi xóa</li>
                            <li>Lưu trữ thông tin đơn hàng ở nơi khác trước khi xóa</li>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Delete Form -->
        <?php if (!$success_message): ?>
            <div class="delete-actions">
                <form method="POST" class="delete-form">
                    <div class="form-group">
                        <label for="delete_reason" class="required">Lý do xóa đơn hàng:</label>
                        <textarea id="delete_reason" name="delete_reason" rows="4" required
                                  placeholder="Vui lòng nhập lý do chi tiết tại sao cần xóa đơn hàng này..."><?= htmlspecialchars($_POST['delete_reason'] ?? '') ?></textarea>
                        <small>Lý do này sẽ được ghi lại trong log hệ thống</small>
                    </div>

                    <div class="confirmation-checkbox">
                        <label>
                            <input type="checkbox" name="confirm_delete" value="1" required>
                            Tôi hiểu rằng việc xóa đơn hàng này không thể hoàn tác và chấp nhận mọi rủi ro có thể xảy ra
                        </label>
                    </div>

                    <?php if (!$can_delete_safely): ?>
                        <div class="warning-box">
                            <i class="fas fa-exclamation-triangle"></i>
                            <div>
                                <p><strong>Lưu ý đặc biệt:</strong></p>
                                <p>Đơn hàng này có trạng thái "<?= getStatusLabel($order['status']) ?>". Vui lòng đảm bảo bạn đã:</p>
                                <ul>
                                    <li>Liên hệ và thông báo với khách hàng</li>
                                    <li>Xử lý việc hoàn tiền (nếu cần thiết)</li>
                                    <li>Cập nhật các báo cáo liên quan</li>
                                    <li>Có sự đồng ý từ cấp trên (nếu cần)</li>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i>
                            Xác nhận xóa đơn hàng
                        </button>
                        <a href="?page=admin&module=orders&action=view&id=<?= $order['id'] ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            Hủy bỏ
                        </a>
                        <a href="?page=admin&module=orders&action=edit&id=<?= $order['id'] ?>" class="btn btn-warning">
                            <i class="fas fa-edit"></i>
                            Thay đổi trạng thái thay vì xóa
                        </a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <!-- Additional Information -->
    <div class="additional-info">
        <div class="info-grid">
            <!-- Customer Impact -->
            <div class="info-section">
                <h4><i class="fas fa-user-friends"></i> Tác động đến khách hàng</h4>
                <div class="info-content">
                    <?php if ($user): ?>
                        <p><strong>Khách hàng:</strong> <?= htmlspecialchars($user['name']) ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                        <p><strong>Tổng đơn hàng của khách:</strong> 
                            <?php 
                            $userOrders = $ordersModel->getByUser($user['id']);
                            echo count($userOrders);
                            ?> đơn
                        </p>
                        <p><strong>Tổng chi tiêu:</strong> 
                            <?php 
                            $totalSpent = array_sum(array_map(fn($o) => $o['total'], $userOrders));
                            echo formatPrice($totalSpent);
                            ?>
                        </p>
                        
                        <?php if ($order['status'] != 'cancelled'): ?>
                            <div class="customer-notification">
                                <p><strong>Khuyến nghị:</strong> Nên thông báo cho khách hàng về việc xóa đơn hàng này.</p>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p>Không tìm thấy thông tin khách hàng</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- System Impact -->
            <div class="info-section">
                <h4><i class="fas fa-cogs"></i> Tác động đến hệ thống</h4>
                <div class="info-content">
                    <ul>
                        <li><strong>Báo cáo doanh thu:</strong> Số liệu thống kê sẽ thay đổi</li>
                        <li><strong>Lịch sử giao dịch:</strong> Thông tin sẽ bị mất vĩnh viễn</li>
                        <li><strong>Tồn kho sản phẩm:</strong> 
                            <?php if ($product): ?>
                                Không ảnh hưởng (sản phẩm vẫn tồn tại)
                            <?php else: ?>
                                Sản phẩm đã bị xóa trước đó
                            <?php endif; ?>
                        </li>
                        <li><strong>Log hệ thống:</strong> Hành động xóa sẽ được ghi lại</li>
                    </ul>
                    
                    <div class="backup-recommendation">
                        <p><strong>Khuyến nghị:</strong> Xuất thông tin đơn hàng ra file trước khi xóa để lưu trữ.</p>
                        <button type="button" class="btn btn-sm btn-info" onclick="window.print()">
                            <i class="fas fa-download"></i>
                            Xuất thông tin đơn hàng
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>