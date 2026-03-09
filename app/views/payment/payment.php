<?php
/**
 * Payment Processing Page - Dynamic Version
 */

// 1. Khởi tạo View an toàn & ServiceManager
require_once __DIR__ . '/../../../core/view_init.php';

// Debug: Bật hiển thị lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Kiểm tra đăng nhập - dùng JavaScript redirect thay vì header vì header đã được gửi
$userId = $_SESSION['user_id'] ?? null;
$authRedirect = "";
if (!$userId) {
    $authRedirect = "<script>window.location.href = '?page=login';</script>";
}

// 2. Khởi tạo biến dữ liệu
$paymentData = [];
$orderId = "ORD_" . bin2hex(random_bytes(4));
$amount = 0;
$orderItems = [];
$showErrorMessage = false;
$errorMessage = '';

// Xử lý dữ liệu từ form checkout (POST)
$items = $_POST['items'] ?? [];
$totalAmount = $_POST['total_amount'] ?? 0;

// Lấy payment_method từ form, mặc định là bank_transfer cho sepay
$paymentMethod = $_POST['payment_method'] ?? 'bank_transfer';

// Map sepay sang bank_transfer vì database chưa có sepay trong ENUM
if ($paymentMethod === 'sepay') {
    $paymentMethod = 'bank_transfer';
}

// Debug: log what we're about to insert
error_log('Order data payment_method: ' . $paymentMethod . ' length: ' . strlen($paymentMethod));

// Nếu không có dữ liệu từ POST, thử lấy từ session (nếu user quay lại sau khi thanh toán thất bại)
if (empty($items) && isset($_SESSION['checkout_items'])) {
    $items = $_SESSION['checkout_items'];
    $totalAmount = $_SESSION['checkout_total'] ?? 0;
    // Map sepay to bank_transfer for database compatibility
    $paymentMethod = isset($_SESSION['checkout_payment_method']) ? $_SESSION['checkout_payment_method'] : 'bank_transfer';
    if ($paymentMethod === 'sepay') {
        $paymentMethod = 'bank_transfer';
    }
}

// Kiểm tra dữ liệu hợp lệ - dùng JavaScript redirect thay vì header
$emptyItemsRedirect = "";
if (empty($items)) {
    $emptyItemsRedirect = "<script>window.location.href = '?page=users&module=cart';</script>";
}

try {
    // Lưu dữ liệu vào session để sử dụng khi cần
    $_SESSION['checkout_items'] = $items;
    $_SESSION['checkout_total'] = $totalAmount;
    $_SESSION['checkout_payment_method'] = $paymentMethod;
    
    // Chuyển đổi items sang định dạng phù hợp cho database
    $orderItems = [];
    foreach ($items as $itemData) {
        if (isset($itemData['product_id'])) {
            $orderItems[] = [
                'product_id' => $itemData['product_id'],
                'quantity' => $itemData['quantity'] ?? 1,
                'price' => $itemData['price'] ?? 0,
            ];
        }
    }
    
    // Tạo đơn hàng trong database
    require_once __DIR__ . '/../../models/OrdersModel.php';
    require_once __DIR__ . '/../../services/UserService.php';
    require_once __DIR__ . '/../../models/ProductsModel.php';
    
    $ordersModel = new OrdersModel();
    $userService = new UserService();
    $productsModel = new ProductsModel();
    
    // Lấy thông tin user
    $userData = $userService->getAccountData($userId);
    $userEmail = $userData['email'] ?? '';
    $userName = $userData['name'] ?? $userData['username'] ?? 'Khách hàng';
    
    // Chuẩn bị dữ liệu đơn hàng
    $orderData = [
        'user_id' => $userId,
        'order_number' => $orderId,
        'status' => 'pending',
        'payment_status' => 'pending',
        'payment_method' => $paymentMethod,
        'subtotal' => $totalAmount,
        'total' => $totalAmount,
        'shipping_name' => substr($userName, 0, 100),
        'shipping_email' => substr($userEmail, 0, 100),
        'shipping_phone' => substr($userData['phone'] ?? '', 0, 20),
    ];
    
    // Chu�n bị items với đầy đủ thông tin sản phẩm
    $items = [];
    $isRenewal = false; // Check if this is a renewal purchase
    
    foreach ($orderItems as $itemData) {
        $product = $productsModel->find($itemData['product_id']);
        $items[] = [
            'product_id' => $itemData['product_id'],
            'product_name' => $product['name'] ?? 'Sản phẩm',
            'product_sku' => $product['sku'] ?? null,
            'product_type' => $product['type'] ?? 'data_nguon_hang',
            'quantity' => $itemData['quantity'] ?? 1,
            'price' => $itemData['price'] ?? 0,
            'product_data' => $product ?? []
        ];
        
        // Check if user has already purchased this product for renewal
        if (!$isRenewal && isset($itemData['product_id']) && $userId) {
            $hasPurchased = $ordersModel->hasUserPurchasedProduct($userId, $itemData['product_id']);
            if ($hasPurchased) {
                $isRenewal = true;
            }
        }
    }
    
    // Lưu đơn hàng vào database (bao gồm cả order_items)
    // Pass $isRenewal to extend expiry date for existing purchases
    $order = $ordersModel->createOrder($orderData, $items, $isRenewal);
    
    if ($order) {
        $amount = (float) $totalAmount;
    } else {
        $showErrorMessage = true;
        $errorMessage = 'Không thể tạo đơn hàng. Vui lòng thử lại.';
    }
    
} catch (Exception $e) {
    // Log error chi tiết
    error_log('Payment Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' line ' . $e->getLine());
    
    if (isset($errorHandler)) {
        $result = $errorHandler->handleViewError($e, 'payment_processing', []);
        $showErrorMessage = true;
        $errorMessage = $result['message'] . ' (' . $e->getMessage() . ')';
    } else {
        $showErrorMessage = true;
        $errorMessage = 'Lỗi: ' . $e->getMessage();
    }
    // Sử dụng dữ liệu từ form khi có lỗi
    $amount = (float) $totalAmount;
}

// Payment configuration - lấy từ settings nếu có
$bankAcc = "0389654785"; // Có thể lấy từ SettingsModel
$bankName = "MBBank";
$content = "THANHTOAN " . $orderId;
$qrSource = "https://qr.sepay.vn/img?bank={$bankName}&acc={$bankAcc}&template=compact&amount={$amount}&des=" . urlencode($content);
?>

<!-- Redirect messages if needed -->
<?php if (!empty($authRedirect)): ?>
<?php echo $authRedirect; ?>
<?php endif; ?>
<?php if (!empty($emptyItemsRedirect)): ?>
<?php echo $emptyItemsRedirect; ?>
<?php endif; ?>

<!-- Error Message -->
<?php if ($showErrorMessage): ?>
<div class="error-message" style="background: #f8d7da; color: #721c24; padding: 15px; margin: 20px 0; border-radius: 5px; text-align: center;">
    <strong>Thông báo:</strong> <?php echo htmlspecialchars($errorMessage); ?>
</div>
<?php endif; ?>

<section class="payment-section">
    <div class="container">
        <h1 class="checkout-title" style="text-align: center;">Quét mã để thanh toán</h1>

        <div class="qr-container">
            <p class="payment-instructions">
                Mở ứng dụng ngân hàng quét mã QR bên dưới để thanh toán.<br>
                <strong style="color: #d32f2f;">Lưu ý: Vui lòng chuyển khoản đúng số tiền để hệ thống tự động xác nhận.</strong>
            </p>

            <img src="<?php echo $qrSource; ?>" alt="SePay QR Code" class="qr-image">

            <div class="order-info">
                <p><strong>Ngân hàng:</strong> <?php echo $bankName; ?></p>
                <p><strong>Số tài khoản:</strong> <?php echo $bankAcc; ?></p>
                <p><strong>Chủ tài khoản:</strong> NGUYEN VAN A</p>
                <p><strong>Số tiền:</strong> <span style="color: #2563EB; font-size: 18px; font-weight: bold;"><?php echo number_format($amount); ?>đ</span></p>
                <p><strong>Nội dung:</strong> <span style="color: #d32f2f; font-weight: bold;"><?php echo $content; ?></span></p>
            </div>

            <div class="payment-waiting">
                <div class="spinner"></div>
                <span id="status-text">Đang chờ tín hiệu từ ngân hàng...</span>
            </div>
            
            <!-- Demo: Button to bypass payment -->
            <div style="margin-top: 20px; text-align: center;">
                <p style="color: #666; font-size: 12px;">Chưa có tiền? Nhấn nút bên dưới để test:</p>
                <a href="?page=payment_success&order_id=<?php echo $orderId; ?>" 
                   style="display: inline-block; padding: 12px 24px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">
                   ✓ Xác nhận đã thanh toán (Demo)
                </a>
            </div>
            
            <div style="width: 100%; background: #eee; height: 5px; margin-top: 15px; border-radius: 3px; overflow: hidden;">
                <div id="progress-bar" style="width: 0%; height: 100%; background: #2563EB; transition: width 5s linear;"></div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const orderId = '<?php echo $orderId; ?>';
    const statusText = document.getElementById('status-text');
    const progressBar = document.getElementById('progress-bar');
    let checkCount = 0;
    const maxChecks = 60; // Tối đa 60 lần kiểm tra (5 phút)
    
    // Hàm kiểm tra trạng thái thanh toán
    function checkPaymentStatus() {
        fetch('api.php?action=check_payment_status&order_id=' + orderId)
            .then(response => response.json())
            .then(data => {
                checkCount++;
                
                if (data.success && data.payment_status === 'paid') {
                    // Thanh toán thành công
                    statusText.textContent = '✓ Đã xác nhận thanh toán! Đang chuyển hướng...';
                    statusText.style.color = '#28a745';
                    
                    if (progressBar) {
                        progressBar.style.background = '#28a745';
                        progressBar.style.width = '100%';
                    }
                    
                    // Chuyển đến trang thành công
                    setTimeout(() => {
                        window.location.href = '?page=payment_success&order_id=' + orderId;
                    }, 1500);
                    
                    return;
                }
                
                // Tiếp tục kiểm tra nếu chưa quá số lần tối đa
                if (checkCount < maxChecks) {
                    // Hiển thị trạng thái
                    if (checkCount < 10) {
                        statusText.textContent = 'Đang chờ thanh toán... (' + checkCount + ')';
                    } else {
                        statusText.textContent = 'Vui lòng hoàn tất thanh toán... Đang kiểm tra...';
                    }
                    
                    // Tiếp tục kiểm tra sau 5 giây
                    setTimeout(checkPaymentStatus, 5000);
                } else {
                    // Quá thời gian chờ
                    statusText.textContent = '⏳ Chờ quá lâu. Bạn có thể kiểm tra lại sau.';
                    statusText.style.color = '#ffc107';
                    
                    // Hiển nút thử lại
                    const retryBtn = document.createElement('button');
                    retryBtn.className = 'btn btn-primary mt-2';
                    retryBtn.textContent = 'Kiểm tra lại';
                    retryBtn.onclick = function() {
                        checkCount = 0;
                        checkPaymentStatus();
                    };
                    statusText.parentNode.appendChild(retryBtn);
                }
            })
            .catch(error => {
                console.error('Lỗi kiểm tra thanh toán:', error);
                checkCount++;
                
                if (checkCount < maxChecks) {
                    setTimeout(checkPaymentStatus, 5000);
                }
            });
    }
    
    // Bắt đầu kiểm tra sau 3 giây
    setTimeout(checkPaymentStatus, 3000);
    
    // Cập nhật progress bar
    if (progressBar) {
        setTimeout(() => {
            progressBar.style.width = '30%';
        }, 500);
    }
});
</script>