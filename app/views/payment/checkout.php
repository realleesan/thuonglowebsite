<section class="payment-section">
    <div class="container">
        <h1 class="checkout-title">Thanh toán khóa học</h1>

        <div class="checkout-wrap">
            <h3 class="mb-3">Đơn hàng của bạn</h3>

            <form action="index.php?page=payment" method="POST">
                <table class="order-table">
                    <thead>
                        <tr>
                            <th style="width: 70%;">Sản phẩm</th>
                            <th style="width: 30%;">Tạm tính</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="product-name">
                                <img src="assets/images/course-thumb.jpg" style="width: 30px; margin-right: 10px; vertical-align: middle;">
                                Khóa học: Lập trình Web Fullstack (Demo)
                            </td>
                            <td class="amount">250,000đ</td>
                        </tr>
                        <tr>
                            <td><strong>Tổng cộng</strong></td>
                            <td class="amount" style="font-size: 18px; color: #d32f2f;">250,000đ</td>
                        </tr>
                    </tbody>
                </table>

                <h3 class="mb-3">Phương thức thanh toán</h3>
                <div class="payment-method-box">
                    <input type="radio" name="payment_method" value="sepay" checked id="pm_sepay">
                    <label for="pm_sepay">Chuyển khoản QR (SePay) </label>
                </div>
                <div class="payment-note">
                    <i class="fas fa-info-circle"></i> Đây là đơn hàng mô phỏng. Không cần thanh toán thật.
                </div>
                <button type="submit" class="btn-place-order">Đặt hàng ngay</button>
            </form>
        </div>
    </div>
</section>