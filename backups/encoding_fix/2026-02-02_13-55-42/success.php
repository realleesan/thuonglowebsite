<?php
$orderId = $_GET['order_id'] ?? 'Unknown';
?>
<section class="payment-section">
    <div class="container">
        <div class="success-box" style="text-align: center; max-width: 600px; margin: 0 auto;">
            <div class="success-animation-icon-container">
                <div class="success-checkmark">
                    <div class="check-icon">
                        <span class="icon-line line-tip"></span>
                        <span class="icon-line line-long"></span>
                        <div class="icon-circle"></div>
                        <div class="icon-fix"></div>
                    </div>
                </div>
            </div>

            <h1 class="checkout-title" style="margin-bottom: 10px;">Thanh toÃ¡n thÃ nh cÃ´ng!</h1>
            <p style="color: #666; margin-bottom: 30px;">Cáº£m Æ¡n báº¡n. ÄÆ¡n hÃ ng <strong>#<?php echo htmlspecialchars($orderId); ?></strong> Ä‘Ã£ Ä‘Æ°á»£c kÃ­ch hoáº¡t.</p>

            <table class="order-table">
                <tbody>
                    <tr>
                        <td><strong>MÃ£ Ä‘Æ¡n hÃ ng:</strong></td>
                        <td><?php echo htmlspecialchars($orderId); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Tráº¡ng thÃ¡i:</strong></td>
                        <td><span style="color: #28a745; font-weight: bold; background: #d4edda; padding: 5px 10px; border-radius: 15px;">ÄÃ£ thanh toÃ¡n</span></td>
                    </tr>
                    <tr>
                        <td><strong>PhÆ°Æ¡ng thá»©c:</strong></td>
                        <td>SePay QR (Demo)</td>
                    </tr>
                    <tr>
                        <td><strong>Tá»•ng tiá»n:</strong></td>
                        <td>250,000Ä‘</td>
                    </tr>
                </tbody>
            </table>

            <div style="margin-top: 30px; display: flex; gap: 10px; justify-content: center;">
                <a href="<?php echo page_url('products'); ?>" class="btn-place-order" style="text-decoration: none; background: #333;">Vá» trang chá»§</a>
                <a href="#" class="btn-place-order" style="text-decoration: none;">VÃ o há»c ngay</a>
            </div>
        </div>
    </div>
</section>