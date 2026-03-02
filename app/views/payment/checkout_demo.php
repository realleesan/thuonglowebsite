<?php
/**
 * Checkout Demo Page
 * Trang thanh toán demo
 */

require_once __DIR__ . '/../../../core/view_init.php';
require_once __DIR__ . '/../../controllers/PaymentDemoController.php';

$controller = new PaymentDemoController();
$result = $controller->checkout();

$product = $result['product'] ?? null;
$total = $result['total'] ?? 0;
$showErrorMessage = !$result['success'];
$errorMessage = $result['message'] ?? '';

if ($showErrorMessage) {
    header('Location: ?page=products_demo');
    exit;
}
?>

<div id="wrapper-container" class="wrapper-container">
    <div class="content-pusher">
        <div id="main-content">
            
            <!-- Demo Notice -->
            <div class="demo-notice" style="background: #fff3cd; border: 2px solid #ffc107; padding: 15px; margin: 20px auto; max-width: 900px; border-radius: 8px; text-align: center;">
                <p style="color: #856404; margin: 0; font-size: 14px;">
                    <i class="fas fa-exclamation-triangle"></i> <strong>THANH TOÁN DEMO</strong> - Đây là giao dịch test với SePay thật
                </p>
            </div>
            
            <section class="checkout-section" style="padding: 40px 20px;">
                <div class="container" style="max-width: 900px; margin: 0 auto;">
                    <h1 class="checkout-title" style="text-align: center; margin-bottom: 40px; font-size: 32px; color: #333;">
                        Thanh toán đơn hàng
                    </h1>

                    <div class="checkout-wrap" style="background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 30px;">
                        
                        <!-- Order Summary -->
                        <h3 style="margin: 0 0 20px 0; font-size: 20px; color: #333; padding-bottom: 15px; border-bottom: 2px solid #2563EB;">
                            Đơn hàng của bạn
                        </h3>

                        <table class="order-table" style="width: 100%; margin-bottom: 30px; border-collapse: collapse;">
                            <thead>
                                <tr style="background: #f9f9f9;">
                                    <th style="padding: 15px; text-align: left; border-bottom: 2px solid #e0e0e0; font-weight: 600;">Sản phẩm</th>
                                    <th style="padding: 15px; text-align: right; border-bottom: 2px solid #e0e0e0; font-weight: 600;">Tạm tính</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="padding: 20px 15px; border-bottom: 1px solid #f0f0f0;">
                                        <div style="display: flex; align-items: center; gap: 15px;">
                                            <img src="<?php echo $product['image'] ?: 'https://via.placeholder.com/80x80'; ?>" 
                                                 style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">
                                            <div>
                                                <div style="font-weight: 600; color: #333; margin-bottom: 5px;">
                                                    <?php echo htmlspecialchars($product['name']); ?>
                                                </div>
                                                <div style="font-size: 13px; color: #999;">
                                                    SKU: <?php echo htmlspecialchars($product['sku']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="padding: 20px 15px; text-align: right; border-bottom: 1px solid #f0f0f0; font-size: 18px; color: #333;">
                                        <?php echo number_format($product['price'], 0, ',', '.'); ?>đ
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 20px 15px; font-weight: 600; font-size: 18px; color: #333;">
                                        Tổng cộng
                                    </td>
                                    <td style="padding: 20px 15px; text-align: right; font-size: 24px; font-weight: bold; color: #2563EB;">
                                        <?php echo number_format($total, 0, ',', '.'); ?>đ
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Customer Information Form -->
                        <h3 style="margin: 30px 0 20px 0; font-size: 20px; color: #333; padding-bottom: 15px; border-bottom: 2px solid #2563EB;">
                            Thông tin khách hàng
                        </h3>
                        
                        <form action="?page=payment_demo" method="POST" id="checkoutForm">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            
                            <div class="form-group" style="margin-bottom: 20px;">
                                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                                    Họ và tên <span style="color: #d32f2f;">*</span>
                                </label>
                                <input type="text" 
                                       name="customer_name" 
                                       required 
                                       placeholder="Nhập họ và tên"
                                       value="<?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?>"
                                       style="width: 100%; padding: 12px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 15px;">
                            </div>
                            
                            <div class="form-group" style="margin-bottom: 20px;">
                                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                                    Email
                                </label>
                                <input type="email" 
                                       name="customer_email" 
                                       placeholder="Nhập email (không bắt buộc)"
                                       value="<?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>"
                                       style="width: 100%; padding: 12px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 15px;">
                            </div>
                            
                            <div class="form-group" style="margin-bottom: 20px;">
                                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                                    Số điện thoại
                                </label>
                                <input type="tel" 
                                       name="customer_phone" 
                                       placeholder="Nhập số điện thoại (không bắt buộc)"
                                       value="<?php echo htmlspecialchars($_SESSION['user_phone'] ?? ''); ?>"
                                       style="width: 100%; padding: 12px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 15px;">
                            </div>

                            <!-- Payment Method -->
                            <h3 style="margin: 30px 0 20px 0; font-size: 20px; color: #333; padding-bottom: 15px; border-bottom: 2px solid #2563EB;">
                                Phương thức thanh toán
                            </h3>
                            
                            <div class="payment-method-box" style="border: 2px solid #2563EB; border-radius: 8px; padding: 20px; margin-bottom: 20px; background: #f0f7ff;">
                                <label style="display: flex; align-items: center; gap: 15px; cursor: pointer;">
                                    <input type="radio" name="payment_method" value="sepay" checked style="width: 20px; height: 20px;">
                                    <div>
                                        <div style="font-weight: 600; font-size: 16px; color: #333; margin-bottom: 5px;">
                                            Chuyển khoản QR (SePay)
                                        </div>
                                        <div style="font-size: 14px; color: #666;">
                                            Quét mã QR để thanh toán nhanh chóng và an toàn
                                        </div>
                                    </div>
                                </label>
                            </div>
                            
                            <div class="payment-note" style="background: #e3f2fd; border-left: 4px solid #2196f3; padding: 15px; margin-bottom: 30px; border-radius: 4px;">
                                <div style="display: flex; gap: 10px; align-items: start;">
                                    <i class="fas fa-info-circle" style="color: #2196f3; margin-top: 2px;"></i>
                                    <div style="font-size: 14px; color: #1565c0; line-height: 1.6;">
                                        <strong>Lưu ý:</strong> Đây là giao dịch demo với SePay thật. Bạn sẽ được chuyển đến trang thanh toán với mã QR. 
                                        Giá trị giao dịch: <strong>10,000đ</strong>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" 
                                    class="btn-place-order" 
                                    style="width: 100%; padding: 18px; background: #2563EB; color: #fff; border: none; border-radius: 8px; font-size: 18px; font-weight: bold; cursor: pointer; transition: background 0.3s;">
                                <i class="fas fa-lock"></i> Đặt hàng ngay
                            </button>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<style>
.btn-place-order:hover {
    background: #1d4ed8;
}

input:focus {
    outline: none;
    border-color: #2563EB;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}
</style>
