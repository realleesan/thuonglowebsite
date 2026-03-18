<?php
/**
 * Admin Orders Edit
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
        header('Location: ?page=admin&module=orders');
        exit;
    }
    
    // Get order data from service
    $orderData = $service->getOrderDetailsData($order_id);
    $order = $orderData['order'];
    $user = $orderData['user'];
    $order_items = $orderData['order_items'];
    
    // Redirect if order not found
    if (!$order) {
        header('Location: ?page=admin&module=orders');
        exit;
    }
    
    // Get product from first order item (simplified)
    $product = null;
    if (!empty($order_items) && !empty($order_items[0]['product'])) {
        $product = $order_items[0]['product'];
    }
    
} catch (Exception $e) {
    $errorHandler->logError('Admin Orders Edit Error', $e);
    header('Location: ?page=admin&module=orders&error=1');
    exit;
}

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
        
        $updateResult = $service->updateOrder($order_id, $updateData);
        
        if ($updateResult) {
            // Use PRG pattern - redirect after successful POST
            // Check if headers not sent yet
            if (!headers_sent($filename, $linenum)) {
                header('Location: ?page=admin&module=orders&action=view&id=' . $order_id . '&updated=1');
                exit;
            } else {
                // Fallback: if headers sent, use JavaScript redirect
                ?>
                <script>
                console.log('Redirecting via JS...');
                window.location.href = "?page=admin&module=orders&action=view&id=<?= $order_id ?>&updated=1";
                </script>
                <div style="padding:20px;text-align:center;">
                    <p>Đang chuyển hướng...</p>
                    <a href="?page=admin&module=orders&action=view&id=<?= $order_id ?>&updated=1">Nhấn vào đây nếu không tự chuyển</a>
                </div>
                <?php
                exit;
            }
        } else {
            $error_message = 'Có lỗi xảy ra khi cập nhật đơn hàng';
        }
    } else {
        $error_message = implode(', ', $errors);
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
            </div>
        </form>
    </div>
</div>