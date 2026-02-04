<section class="payment-section">
    <div class="container">
        <h1 class="checkout-title">Thanh toÃ¡n khÃ³a há»c</h1>

        <div class="checkout-wrap">
            <h3 class="mb-3">ÄÆ¡n hÃ ng cá»§a báº¡n</h3>

            <form action="<?php echo form_url('payment'); ?>" method="POST">
                <table class="order-table">
                    <thead>
                        <tr>
                            <th style="width: 70%;">Sáº£n pháº©m</th>
                            <th style="width: 30%;">Táº¡m tÃ­nh</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="product-name">
                                <img src="<?php echo img_url('home/home-banner-top.png'); ?>" style="width: 30px; margin-right: 10px; vertical-align: middle;">
                                KhÃ³a há»c: Láº­p trÃ¬nh Web Fullstack (Demo)
                            </td>
                            <td class="amount">250,000Ä‘</td>
                        </tr>
                        <tr>
                            <td><strong>Tá»•ng cá»™ng</strong></td>
                            <td class="amount" style="font-size: 18px; color: #d32f2f;">250,000Ä‘</td>
                        </tr>
                    </tbody>
                </table>

                <h3 class="mb-3">PhÆ°Æ¡ng thá»©c thanh toÃ¡n</h3>
                <div class="payment-method-box">
                    <input type="radio" name="payment_method" value="sepay" checked id="pm_sepay">
                    <label for="pm_sepay">Chuyá»ƒn khoáº£n QR (SePay) </label>
                </div>
                <div class="payment-note">
                    <i class="fas fa-info-circle"></i> ÄÃ¢y lÃ  Ä‘Æ¡n hÃ ng mÃ´ phá»ng. KhÃ´ng cáº§n thanh toÃ¡n tháº­t.
                </div>
                <button type="submit" class="btn-place-order">Äáº·t hÃ ng ngay</button>
            </form>
        </div>
    </div>
</section>