<?php
// Táº¡o dá»¯ liá»‡u giáº£ láº­p Ä‘á»ƒ hiá»ƒn thá»‹
$orderId = "DEMO_" . rand(1000, 9999); // MÃ£ Ä‘Æ¡n hÃ ng ngáº«u nhiÃªn
$amount = 250000;
$bankAcc = "0389654785"; // Sá»‘ tÃ i khoáº£n cá»§a báº¡n (Ä‘á»ƒ hiá»‡n lÃªn QR cho giá»‘ng tháº­t)
$bankName = "MBBank";    // TÃªn ngÃ¢n hÃ ng
$content = "THANHTOAN " . $orderId; 

// Link táº¡o áº£nh QR cá»§a SePay (DÃ¹ng Ä‘á»ƒ hiá»ƒn thá»‹ cho Ä‘áº¹p, quÃ©t Ä‘Æ°á»£c tháº­t nhÆ°ng ta khÃ´ng xá»­ lÃ½ tiá»n)
$qrSource = "https://qr.sepay.vn/img?bank={$bankName}&acc={$bankAcc}&template=compact&amount={$amount}&des={$content}";
?>

<section class="payment-section">
    <div class="container">
        <h1 class="checkout-title" style="text-align: center;">QuÃ©t mÃ£ Ä‘á»ƒ thanh toÃ¡n</h1>

        <div class="qr-container">
            <p class="payment-instructions">
                Má»Ÿ á»©ng dá»¥ng ngÃ¢n hÃ ng quÃ©t mÃ£ QR bÃªn dÆ°á»›i.<br>
                (LÆ°u Ã½: ÄÃ¢y lÃ  cháº¿ Ä‘á»™ <strong>DEMO</strong>, há»‡ thá»‘ng sáº½ tá»± Ä‘á»™ng xÃ¡c nháº­n sau 5 giÃ¢y mÃ  khÃ´ng cáº§n chuyá»ƒn khoáº£n)
            </p>

            <img src="<?php echo $qrSource; ?>" alt="SePay QR Code" class="qr-image">

            <div class="order-info">
                <p><strong>NgÃ¢n hÃ ng:</strong> <?php echo $bankName; ?></p>
                <p><strong>Sá»‘ tÃ i khoáº£n:</strong> <?php echo $bankAcc; ?></p>
                <p><strong>Chá»§ tÃ i khoáº£n:</strong> NGUYEN VAN A</p>
                <p><strong>Sá»‘ tiá»n:</strong> <span style="color: #2563EB; font-size: 18px; font-weight: bold;"><?php echo number_format($amount); ?>Ä‘</span></p>
                <p><strong>Ná»™i dung:</strong> <span style="color: #d32f2f; font-weight: bold;"><?php echo $content; ?></span></p>
            </div>

            <div class="payment-waiting">
                <div class="spinner"></div>
                <span id="status-text">Äang chá» tÃ­n hiá»‡u tá»« ngÃ¢n hÃ ng...</span>
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

    // 1. Báº¯t Ä‘áº§u cháº¡y thanh tiáº¿n trÃ¬nh (giáº£ vá» Ä‘ang káº¿t ná»‘i)
    setTimeout(() => {
        progressBar.style.width = '100%';
    }, 100);

    // 2. Sau 2 giÃ¢y: Äá»•i thÃ´ng bÃ¡o
    setTimeout(() => {
        statusText.textContent = "ÄÃ£ nháº­n Ä‘Æ°á»£c tÃ­n hiá»‡u! Äang xá»­ lÃ½ Ä‘Æ¡n hÃ ng...";
        statusText.style.color = "#2563EB";
    }, 2500);

    // 3. Sau 5 giÃ¢y: Chuyá»ƒn hÆ°á»›ng sang trang Success (ThÃ nh cÃ´ng)
    setTimeout(() => {
        window.location.href = '<?php echo page_url('payment_success', ['order_id' => '']); ?>' + orderId;
    }, 5000);
});
</script>