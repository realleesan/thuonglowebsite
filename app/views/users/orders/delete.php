<?php
// User Orders Delete - Cancel Order
// Load fake data
$dataFile = __DIR__ . '/../data/user_fake_data.json';
$data = [];

if (file_exists($dataFile)) {
    $jsonContent = file_get_contents($dataFile);
    $data = json_decode($jsonContent, true) ?: [];
}

// Get order ID from URL
$orderId = $_GET['id'] ?? '';

// Find the specific order
$order = null;
$orderIndex = null;
foreach ($data['orders'] ?? [] as $index => $orderItem) {
    if ($orderItem['id'] === $orderId) {
        $order = $orderItem;
        $orderIndex = $index;
        break;
    }
}

// Redirect if order not found or cannot be cancelled
if (!$order || !in_array($order['status'], ['processing', 'pending'])) {
    header('Location: ?page=users&module=orders');
    exit;
}

// Handle cancellation confirmation
$message = '';
$messageType = '';
$cancelSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_cancel'])) {
    // Update order status to cancelled (in a real app, this would update the database)
    $data['orders'][$orderIndex]['status'] = 'cancelled';
    $order['status'] = 'cancelled';
    
    $message = 'Đơn hàng #' . htmlspecialchars($order['id']) . ' đã được hủy thành công!';
    $messageType = 'success';
    $cancelSuccess = true;
}

// Status and type mappings
$statusLabels = [
    'completed' => 'Hoàn thành',
    'processing' => 'Đang xử lý',
    'pending' => 'Chờ xử lý',
    'cancelled' => 'Đã hủy'
];

$typeLabels = [
    'data_nguon_hang' => 'Data Nguồn Hàng',
    'van_chuyen' => 'Vận Chuyển',
    'dich_vu_tt' => 'Dịch Vụ Thanh Toán',
    'danh_hang' => 'Đánh Hàng',
    'khoa_hoc' => 'Khóa Học',
    'tool' => 'Tool'
];

$paymentLabels = [
    'bank_transfer' => 'Chuyển khoản ngân hàng',
    'momo' => 'Ví MoMo',
    'zalopay' => 'ZaloPay',
    'vnpay' => 'VNPay'
];
?>

<div class="user-content-with-sidebar">
    <!-- User Sidebar -->
    <?php include 'app/views/_layout/user_sidebar.php'; ?>
    
    <!-- Cancel Order Content -->
    <div class="user-orders">
        <!-- Cancel Header -->
        <div class="order-cancel-header">
            <div class="order-cancel-header-left">
                <div class="order-breadcrumb">
                    <a href="?page=users&module=orders">Đơn hàng</a>
                    <i class="fas fa-chevron-right"></i>
                    <a href="?page=users&module=orders&action=view&id=<?php echo $order['id']; ?>">Chi tiết #<?php echo htmlspecialchars($order['id']); ?></a>
                    <i class="fas fa-chevron-right"></i>
                    <span>Hủy đơn hàng</span>
                </div>
                <h1>Hủy đơn hàng #<?php echo htmlspecialchars($order['id']); ?></h1>
                <p>Xác nhận hủy đơn hàng của bạn</p>
            </div>
        </div>

        <!-- Success/Error Message -->
        <?php if ($message): ?>
        <div class="orders-message orders-message-<?php echo $messageType; ?>">
            <div class="success-message-content">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="success-text">
                    <h3><?php echo htmlspecialchars($message); ?></h3>
                    <p>Bạn sẽ được chuyển hướng về danh sách đơn hàng trong <span id="countdown">3</span> giây...</p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($cancelSuccess): ?>
        <!-- Success State -->
        <div class="cancel-success-state">
            <div class="cancel-success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="cancel-success-content">
                <h2>Đơn hàng đã được hủy thành công!</h2>
                <p>Đơn hàng #<?php echo htmlspecialchars($order['id']); ?> đã được hủy và không thể khôi phục.</p>
                
                <div class="cancel-success-actions">
                    <a href="?page=users&module=orders" class="orders-btn orders-btn-primary">
                        <i class="fas fa-list"></i>
                        Xem danh sách đơn hàng
                    </a>
                    
                    <a href="?page=products" class="orders-btn orders-btn-secondary">
                        <i class="fas fa-shopping-bag"></i>
                        Tiếp tục mua sắm
                    </a>
                </div>
            </div>
        </div>
        
        <script>
        // Auto redirect after 3 seconds
        let countdown = 3;
        const countdownElement = document.getElementById('countdown');
        
        const timer = setInterval(function() {
            countdown--;
            if (countdownElement) {
                countdownElement.textContent = countdown;
            }
            
            if (countdown <= 0) {
                clearInterval(timer);
                window.location.href = '?page=users&module=orders';
            }
        }, 1000);
        </script>
        
        <?php elseif ($order['status'] !== 'cancelled'): ?>
        <!-- Warning Notice -->
        <div class="cancel-warning">
            <div class="cancel-warning-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="cancel-warning-content">
                <h3>Cảnh báo: Hành động không thể hoàn tác</h3>
                <p>Sau khi hủy đơn hàng, bạn sẽ không thể khôi phục lại. Vui lòng xem xét kỹ trước khi thực hiện.</p>
            </div>
        </div>

        <!-- Order Information -->
        <div class="cancel-order-info">
            <div class="order-card-header">
                <h3>Thông tin đơn hàng sẽ bị hủy</h3>
            </div>
            <div class="order-card-content">
                <div class="cancel-order-detail">
                    <div class="cancel-product-info">
                        <div class="cancel-product-image">
                            <div class="cancel-product-placeholder">
                                <i class="fas fa-<?php 
                                    echo $order['type'] === 'data_nguon_hang' ? 'database' : 
                                        ($order['type'] === 'van_chuyen' ? 'truck' : 
                                        ($order['type'] === 'dich_vu_tt' ? 'credit-card' : 
                                        ($order['type'] === 'khoa_hoc' ? 'graduation-cap' : 'cog'))); 
                                ?>"></i>
                            </div>
                        </div>
                        <div class="cancel-product-details">
                            <h4><?php echo htmlspecialchars($order['product_name']); ?></h4>
                            <p class="cancel-product-type">
                                <i class="fas fa-tag"></i>
                                <?php echo $typeLabels[$order['type']] ?? $order['type']; ?>
                            </p>
                            <div class="cancel-product-price">
                                <?php echo number_format($order['amount'], 0, ',', '.'); ?> VNĐ
                            </div>
                        </div>
                    </div>
                    
                    <div class="cancel-order-meta">
                        <div class="cancel-meta-item">
                            <span class="cancel-meta-label">Mã đơn hàng:</span>
                            <span class="cancel-meta-value">#<?php echo htmlspecialchars($order['id']); ?></span>
                        </div>
                        
                        <div class="cancel-meta-item">
                            <span class="cancel-meta-label">Ngày đặt:</span>
                            <span class="cancel-meta-value"><?php echo date('d/m/Y H:i', strtotime($order['date'])); ?></span>
                        </div>
                        
                        <div class="cancel-meta-item">
                            <span class="cancel-meta-label">Trạng thái hiện tại:</span>
                            <span class="cancel-meta-value">
                                <span class="orders-badge orders-badge-<?php 
                                    echo $order['status'] === 'processing' ? 'warning' : 'info'; 
                                ?>">
                                    <?php echo $statusLabels[$order['status']] ?? $order['status']; ?>
                                </span>
                            </span>
                        </div>
                        
                        <div class="cancel-meta-item">
                            <span class="cancel-meta-label">Phương thức thanh toán:</span>
                            <span class="cancel-meta-value"><?php echo $paymentLabels[$order['payment_method']] ?? $order['payment_method']; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cancellation Reasons -->
        <div class="cancel-reasons">
            <div class="order-card-header">
                <h3>Lý do hủy đơn hàng (tùy chọn)</h3>
                <p>Giúp chúng tôi cải thiện dịch vụ bằng cách cho biết lý do hủy</p>
            </div>
            <div class="order-card-content">
                <form method="POST" class="cancel-form" id="cancelForm">
                    <div class="cancel-reason-options">
                        <label class="cancel-reason-option">
                            <input type="radio" name="cancel_reason" value="changed_mind">
                            <span class="cancel-reason-text">Tôi đã thay đổi ý định</span>
                        </label>
                        
                        <label class="cancel-reason-option">
                            <input type="radio" name="cancel_reason" value="found_better_price">
                            <span class="cancel-reason-text">Tìm thấy giá tốt hơn ở nơi khác</span>
                        </label>
                        
                        <label class="cancel-reason-option">
                            <input type="radio" name="cancel_reason" value="payment_issues">
                            <span class="cancel-reason-text">Gặp vấn đề với thanh toán</span>
                        </label>
                        
                        <label class="cancel-reason-option">
                            <input type="radio" name="cancel_reason" value="delivery_time">
                            <span class="cancel-reason-text">Thời gian xử lý quá lâu</span>
                        </label>
                        
                        <label class="cancel-reason-option">
                            <input type="radio" name="cancel_reason" value="other">
                            <span class="cancel-reason-text">Lý do khác</span>
                        </label>
                    </div>
                    
                    <div class="cancel-reason-other" id="otherReasonDiv" style="display: none;">
                        <label for="other_reason" class="form-label">Vui lòng mô tả chi tiết:</label>
                        <textarea id="other_reason" 
                                  name="other_reason" 
                                  class="form-control" 
                                  rows="3" 
                                  placeholder="Nhập lý do hủy đơn hàng..."></textarea>
                    </div>
                    
                    <!-- Confirmation Checkbox -->
                    <div class="cancel-confirmation">
                        <label class="cancel-confirmation-checkbox">
                            <input type="checkbox" name="confirm_understanding" required>
                            <span class="cancel-confirmation-text">
                                Tôi hiểu rằng việc hủy đơn hàng này không thể hoàn tác và tôi sẽ cần đặt hàng mới nếu muốn mua sản phẩm này.
                            </span>
                        </label>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="cancel-form-actions">
                        <a href="?page=users&module=orders&action=view&id=<?php echo $order['id']; ?>" 
                           class="orders-btn orders-btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Quay lại
                        </a>
                        
                        <button type="submit" 
                                name="confirm_cancel" 
                                class="orders-btn orders-btn-danger"
                                id="confirmCancelBtn">
                            <i class="fas fa-times"></i>
                            Xác nhận hủy đơn hàng
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Include Orders JavaScript -->
<script src="assets/js/user_orders.js"></script>
<script>
// Handle other reason visibility
document.addEventListener('DOMContentLoaded', function() {
    const reasonOptions = document.querySelectorAll('input[name="cancel_reason"]');
    const otherReasonDiv = document.getElementById('otherReasonDiv');
    const otherReasonTextarea = document.getElementById('other_reason');
    
    // Show success message function
    function showMessage(message, type = 'info') {
        // Remove existing messages
        const existingMessages = document.querySelectorAll('.orders-message');
        existingMessages.forEach(msg => {
            if (!msg.querySelector('#countdown')) { // Don't remove countdown messages
                msg.remove();
            }
        });
        
        // Create new message
        const messageDiv = document.createElement('div');
        messageDiv.className = `orders-message orders-message-${type}`;
        messageDiv.innerHTML = `
            <div class="message-content">
                <div class="message-icon">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                </div>
                <div class="message-text">
                    <span>${message}</span>
                </div>
            </div>
        `;
        
        // Insert at the top of orders content
        const ordersContent = document.querySelector('.user-orders');
        const firstChild = ordersContent.querySelector('.order-cancel-header').nextElementSibling;
        ordersContent.insertBefore(messageDiv, firstChild);
        
        // Auto-hide after 5 seconds (except for success messages)
        if (type !== 'success') {
            setTimeout(() => {
                messageDiv.style.opacity = '0';
                setTimeout(() => {
                    messageDiv.remove();
                }, 300);
            }, 5000);
        }
    }
    
    // Make showMessage available globally
    window.showMessage = showMessage;
    
    reasonOptions.forEach(option => {
        option.addEventListener('change', function() {
            if (this.value === 'other') {
                otherReasonDiv.style.display = 'block';
                otherReasonTextarea.required = true;
            } else {
                otherReasonDiv.style.display = 'none';
                otherReasonTextarea.required = false;
                otherReasonTextarea.value = '';
            }
        });
    });
    
    // Form submission confirmation
    document.getElementById('cancelForm').addEventListener('submit', function(e) {
        const confirmCheckbox = document.querySelector('input[name="confirm_understanding"]');
        
        if (!confirmCheckbox.checked) {
            e.preventDefault();
            showMessage('Vui lòng xác nhận bạn đã hiểu về việc hủy đơn hàng', 'error');
            return;
        }
        
        // Final confirmation
        if (!confirm('Bạn có chắc chắn muốn hủy đơn hàng này? Hành động này không thể hoàn tác.')) {
            e.preventDefault();
            return;
        }
        
        // Show loading state
        const submitBtn = document.getElementById('confirmCancelBtn');
        const originalContent = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang hủy đơn hàng...';
        submitBtn.disabled = true;
        
        // Disable form to prevent double submission
        const form = document.getElementById('cancelForm');
        const formElements = form.querySelectorAll('input, textarea, button');
        formElements.forEach(element => {
            element.disabled = true;
        });
        
        // Show processing message
        showMessage('Đang xử lý yêu cầu hủy đơn hàng...', 'info');
    });
});
</script>