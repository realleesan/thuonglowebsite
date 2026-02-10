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
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate form data
    $new_status = trim($_POST['status'] ?? '');
    $admin_note = trim($_POST['admin_note'] ?? '');
    $notify_customer = isset($_POST['notify_customer']);
    
    // Validation
    if (empty($new_status)) {
        $errors[] = 'Vui lòng chọn trạng thái';
    } elseif (!in_array($new_status, ['pending', 'processing', 'completed', 'cancelled'])) {
        $errors[] = 'Trạng thái không hợp lệ';
    }
    
    if (empty($errors)) {
        // Update order in database
        $updateData = [
            'status' => $new_status,
            'admin_note' => $admin_note
        ];
        
        if ($ordersModel->update($order_id, $updateData)) {
            $success_message = 'Cập nhật trạng thái đơn hàng thành công!';
            
            // Update the order status for display purposes
            $order['status'] = $new_status;
            
            // TODO: Send email notification if requested
            if ($notify_customer) {
                // Send email notification logic here
            }
            
            header('Location: ?page=admin&module=orders&action=view&id=' . $order_id . '&updated=1');
            exit;
        } else {
            $error_message = 'Có lỗi xảy ra khi cập nhật đơn hàng';
        }
    } else {
        $error_message = 'Có lỗi xảy ra khi cập nhật đơn hàng';
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

$status_info = getStatusInfo($order['status']);
?>

<div class="orders-edit-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-edit"></i>
                Cập Nhật Đơn Hàng #<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?>
            </h1>
            <p class="page-description">Cập nhật trạng thái và thông tin đơn hàng</p>
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
            </div>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                <strong>Lỗi!</strong>
                <p><?= htmlspecialchars($error_message) ?></p>
                <?php if (!empty($errors)): ?>
                    <ul class="error-list">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Order Summary -->
    <div class="order-summary">
        <div class="order-summary-grid">
            <!-- Order Info -->
            <div class="order-info-card">
                <h3>Thông Tin Đơn Hàng</h3>
                <div class="order-meta">
                    <div class="meta-item">
                        <span class="meta-label">Mã đơn hàng:</span>
                        <span class="meta-value">#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Khách hàng:</span>
                        <span class="meta-value"><?= htmlspecialchars($user['name'] ?? 'N/A') ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Sản phẩm:</span>
                        <span class="meta-value"><?= htmlspecialchars($product['name'] ?? 'Sản phẩm đã xóa') ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Tổng tiền:</span>
                        <span class="meta-value price-highlight"><?= formatPrice($order['total']) ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Trạng thái hiện tại:</span>
                        <span class="meta-value">
                            <span class="status-badge status-<?= $order['status'] ?> status-<?= $status_info['color'] ?>">
                                <?= $status_info['label'] ?>
                            </span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Customer Info -->
            <div class="customer-info-card">
                <h3>Thông Tin Khách Hàng</h3>
                <?php if ($user): ?>
                    <div class="customer-details">
                        <div class="customer-avatar">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div class="customer-meta">
                            <h4><?= htmlspecialchars($user['name']) ?></h4>
                            <p><?= htmlspecialchars($user['email']) ?></p>
                            <p><?= htmlspecialchars($user['phone'] ?? 'Chưa cập nhật') ?></p>
                            <span class="user-role role-<?= $user['role'] ?>">
                                <?= ucfirst($user['role']) ?>
                            </span>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="no-customer">Không tìm thấy thông tin khách hàng</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Update Form -->
    <div class="form-container">
        <form method="POST" class="admin-form">
            <div class="form-grid">
                <!-- Status Update Section -->
                <div class="form-column">
                    <div class="form-section">
                        <h3 class="section-title">Cập Nhật Trạng Thái</h3>
                        
                        <div class="form-group">
                            <label for="status" class="required">Trạng thái mới:</label>
                            <select id="status" name="status" class="<?= in_array('status', array_keys($errors ?? [])) ? 'error' : '' ?>" required>
                                <option value="">Chọn trạng thái</option>
                                <option value="pending" <?= ($order['status'] == 'pending') ? 'selected' : '' ?>>
                                    Chờ xử lý
                                </option>
                                <option value="processing" <?= ($order['status'] == 'processing') ? 'selected' : '' ?>>
                                    Đang xử lý
                                </option>
                                <option value="completed" <?= ($order['status'] == 'completed') ? 'selected' : '' ?>>
                                    Hoàn thành
                                </option>
                                <option value="cancelled" <?= ($order['status'] == 'cancelled') ? 'selected' : '' ?>>
                                    Đã hủy
                                </option>
                            </select>
                            <small>Chọn trạng thái mới cho đơn hàng</small>
                        </div>

                        <div class="form-group">
                            <label for="admin_note">Ghi chú của admin:</label>
                            <textarea id="admin_note" name="admin_note" rows="4" 
                                      placeholder="Nhập ghi chú về việc cập nhật trạng thái (tùy chọn)..."><?= htmlspecialchars($_POST['admin_note'] ?? '') ?></textarea>
                            <small>Ghi chú này sẽ được lưu trong lịch sử đơn hàng</small>
                        </div>

                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="notify_customer" value="1" 
                                       <?= isset($_POST['notify_customer']) ? 'checked' : '' ?>>
                                <span class="checkmark"></span>
                                Gửi email thông báo cho khách hàng
                            </label>
                            <small>Khách hàng sẽ nhận được email thông báo về việc thay đổi trạng thái</small>
                        </div>
                    </div>
                </div>

                <!-- Status Guide Section -->
                <div class="form-column">
                    <div class="form-section">
                        <h3 class="section-title">Hướng Dẫn Trạng Thái</h3>
                        
                        <div class="status-guide">
                            <div class="guide-item">
                                <div class="guide-status">
                                    <span class="status-badge status-pending">Chờ xử lý</span>
                                </div>
                                <div class="guide-description">
                                    <p>Đơn hàng mới được tạo, chưa được xử lý</p>
                                </div>
                            </div>
                            
                            <div class="guide-item">
                                <div class="guide-status">
                                    <span class="status-badge status-processing">Đang xử lý</span>
                                </div>
                                <div class="guide-description">
                                    <p>Đơn hàng đang được chuẩn bị và xử lý</p>
                                </div>
                            </div>
                            
                            <div class="guide-item">
                                <div class="guide-status">
                                    <span class="status-badge status-completed">Hoàn thành</span>
                                </div>
                                <div class="guide-description">
                                    <p>Đơn hàng đã được giao thành công</p>
                                </div>
                            </div>
                            
                            <div class="guide-item">
                                <div class="guide-status">
                                    <span class="status-badge status-cancelled">Đã hủy</span>
                                </div>
                                <div class="guide-description">
                                    <p>Đơn hàng đã bị hủy bởi khách hàng hoặc admin</p>
                                </div>
                            </div>
                        </div>

                        <div class="status-warning">
                            <div class="warning-header">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Lưu ý quan trọng</strong>
                            </div>
                            <ul>
                                <li>Không thể thay đổi trạng thái từ "Hoàn thành" về "Chờ xử lý"</li>
                                <li>Đơn hàng "Đã hủy" không thể chuyển sang trạng thái khác</li>
                                <li>Khách hàng sẽ nhận được email thông báo nếu bạn chọn tùy chọn gửi email</li>
                                <li>Mọi thay đổi sẽ được ghi lại trong lịch sử đơn hàng</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Cập nhật trạng thái
                </button>
                <a href="?page=admin&module=orders&action=view&id=<?= $order['id'] ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Hủy
                </a>
                <button type="button" class="btn btn-info" id="preview-email">
                    <i class="fas fa-eye"></i>
                    Xem trước email
                </button>
            </div>
        </form>
    </div>

    <!-- Order Timeline -->
    <div class="order-timeline-section">
        <h3>Lịch Sử Đơn Hàng</h3>
        <div class="timeline">
            <div class="timeline-item">
                <div class="timeline-marker completed"></div>
                <div class="timeline-content">
                    <div class="timeline-header">
                        <strong>Đơn hàng được tạo</strong>
                        <span class="timeline-date"><?= formatDate($order['created_at']) ?></span>
                    </div>
                    <p>Khách hàng đã đặt hàng thành công</p>
                </div>
            </div>
            
            <?php if ($order['payment_method'] != 'cod'): ?>
                <div class="timeline-item">
                    <div class="timeline-marker completed"></div>
                    <div class="timeline-content">
                        <div class="timeline-header">
                            <strong>Thanh toán thành công</strong>
                            <span class="timeline-date"><?= date('d/m/Y H:i', strtotime($order['created_at']) + 1800) ?></span>
                        </div>
                        <p>Đã nhận được thanh toán qua <?= getPaymentMethodLabel($order['payment_method']) ?></p>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (in_array($order['status'], ['processing', 'completed'])): ?>
                <div class="timeline-item">
                    <div class="timeline-marker completed"></div>
                    <div class="timeline-content">
                        <div class="timeline-header">
                            <strong>Đang xử lý</strong>
                            <span class="timeline-date"><?= date('d/m/Y H:i', strtotime($order['created_at']) + 3600) ?></span>
                        </div>
                        <p>Đơn hàng đang được chuẩn bị</p>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($order['status'] == 'completed'): ?>
                <div class="timeline-item">
                    <div class="timeline-marker completed"></div>
                    <div class="timeline-content">
                        <div class="timeline-header">
                            <strong>Hoàn thành</strong>
                            <span class="timeline-date"><?= date('d/m/Y H:i', strtotime($order['created_at']) + 86400) ?></span>
                        </div>
                        <p>Đơn hàng đã được giao thành công</p>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($order['status'] == 'cancelled'): ?>
                <div class="timeline-item">
                    <div class="timeline-marker cancelled"></div>
                    <div class="timeline-content">
                        <div class="timeline-header">
                            <strong>Đã hủy</strong>
                            <span class="timeline-date"><?= date('d/m/Y H:i', strtotime($order['created_at']) + 7200) ?></span>
                        </div>
                        <p>Đơn hàng đã được hủy</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Email Preview Modal -->
    <div id="emailPreviewModal" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h3>Xem trước email thông báo</h3>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="email-preview">
                    <div class="email-header">
                        <h4>Thông báo cập nhật trạng thái đơn hàng #<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></h4>
                    </div>
                    <div class="email-content">
                        <p>Xin chào <?= htmlspecialchars($user['name'] ?? 'Khách hàng') ?>,</p>
                        <p>Chúng tôi xin thông báo trạng thái đơn hàng của bạn đã được cập nhật:</p>
                        
                        <div class="email-order-info">
                            <table>
                                <tr>
                                    <td><strong>Mã đơn hàng:</strong></td>
                                    <td>#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Sản phẩm:</strong></td>
                                    <td><?= htmlspecialchars($product['name'] ?? 'N/A') ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Trạng thái mới:</strong></td>
                                    <td><span id="email-status-preview">Chọn trạng thái để xem</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Tổng tiền:</strong></td>
                                    <td><?= formatPrice($order['total']) ?></td>
                                </tr>
                            </table>
                        </div>
                        
                        <div id="email-note-preview" style="display: none;">
                            <p><strong>Ghi chú từ admin:</strong></p>
                            <p id="email-note-content"></p>
                        </div>
                        
                        <p>Cảm ơn bạn đã tin tưởng và sử dụng dịch vụ của chúng tôi!</p>
                        <p>Trân trọng,<br>Đội ngũ ThuongLo</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="closeEmailPreview">Đóng</button>
            </div>
        </div>
    </div>
</div>