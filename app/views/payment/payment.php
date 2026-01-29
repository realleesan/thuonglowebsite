<?php
// Tạo dữ liệu giả lập để hiển thị
$orderId = "DEMO_" . rand(1000, 9999); // Mã đơn hàng ngẫu nhiên
$amount = 250000;
$bankAcc = "0389654785"; // Số tài khoản của bạn (để hiện lên QR cho giống thật)
$bankName = "MBBank";    // Tên ngân hàng
$content = "THANHTOAN " . $orderId; 

// Link tạo ảnh QR của SePay (Dùng để hiển thị cho đẹp, quét được thật nhưng ta không xử lý tiền)
$qrSource = "https://qr.sepay.vn/img?bank={$bankName}&acc={$bankAcc}&template=compact&amount={$amount}&des={$content}";
?>

<section class="payment-section">
    <div class="container">
        <h1 class="checkout-title" style="text-align: center;">Quét mã để thanh toán</h1>

        <div class="qr-container">
            <p class="payment-instructions">
                Mở ứng dụng ngân hàng quét mã QR bên dưới.<br>
                (Lưu ý: Đây là chế độ <strong>DEMO</strong>, hệ thống sẽ tự động xác nhận sau 5 giây mà không cần chuyển khoản)
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
            
            <div style="width: 100%; background: #eee; height: 5px; margin-top: 15px; border-radius: 3px; overflow: hidden;">
                <div id="progress-bar" style="width: 0%; height: 100%; background: #2563EB; transition: width 5s linear;"></div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const progressBar = document.getElementById('progress-bar');
    const statusText = document.getElementById('status-text');
    const orderId = '<?php echo $orderId; ?>';

    // 1. Bắt đầu chạy thanh tiến trình (giả vờ đang kết nối)
    setTimeout(() => {
        progressBar.style.width = '100%';
    }, 100);

    // 2. Sau 2 giây: Đổi thông báo
    setTimeout(() => {
        statusText.textContent = "Đã nhận được tín hiệu! Đang xử lý đơn hàng...";
        statusText.style.color = "#2563EB";
    }, 2500);

    // 3. Sau 5 giây: Chuyển hướng sang trang Success (Thành công)
    setTimeout(() => {
        window.location.href = 'index.php?page=payment_success&order_id=' + orderId;
    }, 5000);
});
</script>