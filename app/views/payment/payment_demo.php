<?php
/**
 * Payment Processing Demo Page
 * Trang xử lý thanh toán demo với SePay thật
 */

require_once __DIR__ . '/../../../core/view_init.php';
require_once __DIR__ . '/../../controllers/PaymentDemoController.php';

// Process payment
$controller = new PaymentDemoController();
$result = $controller->processPayment();

if (!$result['success']) {
    echo '<script>alert("' . htmlspecialchars($result['message']) . '"); window.location.href = "?page=products_demo";</script>';
    exit;
}

$order = $result['order'];
$qrData = $result['qr_data'];

// Extract QR info
$qrUrl = $qrData['qr_url'] ?? '';
$content = $qrData['content'] ?? '';
$amount = $qrData['amount'] ?? 0;
$accountNumber = $qrData['account_number'] ?? '';
$bankCode = $qrData['bank_code'] ?? 'MB';
$timeout = $qrData['timeout'] ?? 300;

// Fallback: Nếu accountNumber vẫn là placeholder, lấy từ .env trực tiếp
if (empty($accountNumber) || $accountNumber === 'YOUR_ACCOUNT_NUMBER_HERE') {
    require_once __DIR__ . '/../../../core/env.php';
    $accountNumber = Env::get('SEPAY_ACCOUNT_NUMBER', '0389654785');
}

// Generate QR URL using SePay public API
$qrSource = "https://qr.sepay.vn/img?bank={$bankCode}&acc={$accountNumber}&template=compact&amount={$amount}&des={$content}";
?>

<div id="wrapper-container" class="wrapper-container">
    <div class="content-pusher">
        <div id="main-content">
            
            <!-- Demo Notice -->
            <div class="demo-notice" style="background: #fff3cd; border: 2px solid #ffc107; padding: 15px; margin: 20px auto; max-width: 800px; border-radius: 8px; text-align: center;">
                <p style="color: #856404; margin: 0; font-size: 14px;">
                    <i class="fas fa-exclamation-triangle"></i> <strong>THANH TOÁN THẬT</strong> - Đây là giao dịch thật với SePay
                </p>
            </div>
            
            <section class="payment-section" style="padding: 40px 20px;">
                <div class="container" style="max-width: 800px; margin: 0 auto;">
                    <h1 class="checkout-title" style="text-align: center; margin-bottom: 20px; font-size: 32px; color: #333;">
                        Quét mã để thanh toán
                    </h1>
                    
                    <div class="order-number" style="text-align: center; margin-bottom: 30px; font-size: 16px; color: #666;">
                        Mã đơn hàng: <strong style="color: #2563EB;"><?php echo htmlspecialchars($order['order_number']); ?></strong>
                    </div>

                    <div class="qr-container" style="background: #fff; border: 2px solid #2563EB; border-radius: 12px; padding: 40px; text-align: center;">
                        
                        <p class="payment-instructions" style="font-size: 16px; color: #666; margin-bottom: 30px; line-height: 1.6;">
                            Mở ứng dụng ngân hàng và quét mã QR bên dưới để thanh toán.<br>
                            <strong style="color: #d32f2f;">Lưu ý: Đây là giao dịch THẬT, vui lòng chuyển khoản đúng số tiền.</strong>
                        </p>

                        <div class="qr-code-wrapper" style="display: inline-block; padding: 20px; background: #fff; border: 3px solid #2563EB; border-radius: 12px; margin-bottom: 30px;">
                            <img src="<?php echo $qrSource; ?>" 
                                 alt="SePay QR Code" 
                                 class="qr-image" 
                                 style="width: 300px; height: 300px; display: block;">
                        </div>

                        <div class="order-info" style="background: #f9f9f9; border-radius: 8px; padding: 25px; text-align: left; margin-bottom: 30px;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                <div>
                                    <div style="font-size: 13px; color: #999; margin-bottom: 5px;">Ngân hàng</div>
                                    <div style="font-weight: 600; color: #333;"><?php echo $bankCode; ?> Bank</div>
                                </div>
                                <div>
                                    <div style="font-size: 13px; color: #999; margin-bottom: 5px;">Số tài khoản</div>
                                    <div style="font-weight: 600; color: #333;"><?php echo $accountNumber; ?></div>
                                </div>
                                <div>
                                    <div style="font-size: 13px; color: #999; margin-bottom: 5px;">Chủ tài khoản</div>
                                    <div style="font-weight: 600; color: #333;">NGUYEN VAN A</div>
                                </div>
                                <div>
                                    <div style="font-size: 13px; color: #999; margin-bottom: 5px;">Số tiền</div>
                                    <div style="font-weight: bold; font-size: 20px; color: #2563EB;">
                                        <?php echo number_format($amount, 0, ',', '.'); ?>đ
                                    </div>
                                </div>
                            </div>
                            <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e0e0e0;">
                                <div style="font-size: 13px; color: #999; margin-bottom: 5px;">Nội dung chuyển khoản</div>
                                <div style="font-weight: bold; font-size: 18px; color: #d32f2f; background: #fff; padding: 12px; border-radius: 4px; border: 2px dashed #d32f2f;">
                                    <?php echo htmlspecialchars($content); ?>
                                </div>
                                <div style="font-size: 12px; color: #999; margin-top: 8px;">
                                    <i class="fas fa-exclamation-circle"></i> Vui lòng nhập chính xác nội dung này
                                </div>
                            </div>
                        </div>
                                    <div style="font-size: 13px; color: #999; margin-bottom: 5px;">Chủ tài khoản</div>
                                    <div style="font-weight: 600; color: #333;">NGUYEN VAN A</div>
                                </div>
                                <div>
                                    <div style="font-size: 13px; color: #999; margin-bottom: 5px;">Số tiền</div>
                                    <div style="font-weight: bold; font-size: 20px; color: #2563EB;">
                                        <?php echo number_format($amount, 0, ',', '.'); ?>đ
                                    </div>
                                </div>
                            </div>
                            <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e0e0e0;">
                                <div style="font-size: 13px; color: #999; margin-bottom: 5px;">Nội dung chuyển khoản</div>
                                <div style="font-weight: bold; font-size: 18px; color: #d32f2f; background: #fff; padding: 12px; border-radius: 4px; border: 2px dashed #d32f2f;">
                                    <?php echo htmlspecialchars($content); ?>
                                </div>
                                <div style="font-size: 12px; color: #999; margin-top: 8px;">
                                    <i class="fas fa-exclamation-circle"></i> Vui lòng nhập chính xác nội dung này
                                </div>
                            </div>
                        </div>

                        <div class="payment-waiting" style="margin-bottom: 20px;">
                            <div class="spinner" style="display: inline-block; width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #2563EB; border-radius: 50%; animation: spin 1s linear infinite; margin-bottom: 15px;"></div>
                            <div id="status-text" style="font-size: 16px; color: #666; font-weight: 600;">
                                Đang chờ xác nhận thanh toán từ ngân hàng...
                            </div>
                            <div id="countdown" style="font-size: 14px; color: #999; margin-top: 10px;">
                                Thời gian còn lại: <span id="time-left"><?php echo $timeout; ?></span> giây
                            </div>
                        </div>
                        
                        <div style="width: 100%; background: #eee; height: 8px; border-radius: 4px; overflow: hidden;">
                            <div id="progress-bar" style="width: 0%; height: 100%; background: linear-gradient(90deg, #2563EB, #1d4ed8); transition: width 0.5s linear;"></div>
                        </div>
                    </div>
                    
                    <div style="text-align: center; margin-top: 30px;">
                        <a href="?page=products_demo" style="color: #666; text-decoration: none; font-size: 14px;">
                            <i class="fas fa-arrow-left"></i> Quay lại trang sản phẩm
                        </a>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<style>
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const orderId = <?php echo $order['id']; ?>;
    const timeout = <?php echo $timeout; ?>;
    const progressBar = document.getElementById('progress-bar');
    const statusText = document.getElementById('status-text');
    const timeLeftSpan = document.getElementById('time-left');
    
    let timeLeft = timeout;
    let checkInterval;
    let countdownInterval;
    
    // Start progress bar
    setTimeout(() => {
        progressBar.style.width = '100%';
        progressBar.style.transition = `width ${timeout}s linear`;
    }, 100);
    
    // Countdown timer
    countdownInterval = setInterval(() => {
        timeLeft--;
        timeLeftSpan.textContent = timeLeft;
        
        if (timeLeft <= 0) {
            clearInterval(countdownInterval);
            statusText.textContent = 'Hết thời gian chờ. Vui lòng thử lại.';
            statusText.style.color = '#d32f2f';
        }
    }, 1000);
    
    // Check payment status every 3 seconds
    checkInterval = setInterval(() => {
        fetch('?page=check_payment_demo&order_id=' + orderId)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.payment_status === 'paid') {
                    clearInterval(checkInterval);
                    clearInterval(countdownInterval);
                    
                    statusText.textContent = 'Thanh toán thành công! Đang chuyển hướng...';
                    statusText.style.color = '#4caf50';
                    
                    setTimeout(() => {
                        window.location.href = '?page=payment_success_demo&order_number=<?php echo $order['order_number']; ?>';
                    }, 2000);
                }
            })
            .catch(error => {
                console.error('Error checking payment:', error);
            });
    }, 3000);
    
    // Stop checking after timeout
    setTimeout(() => {
        clearInterval(checkInterval);
        clearInterval(countdownInterval);
    }, timeout * 1000);
});
</script>
