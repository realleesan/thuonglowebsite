<?php
/**
 * Payment Success Demo Page
 * Trang thông báo thanh toán thành công
 */

require_once __DIR__ . '/../../../core/view_init.php';
require_once __DIR__ . '/../../controllers/PaymentDemoController.php';

$controller = new PaymentDemoController();
$result = $controller->success();

if (!$result['success']) {
    header('Location: ?page=products_demo');
    exit;
}

$order = $result['order'];
?>

<div id="wrapper-container" class="wrapper-container">
    <div class="content-pusher">
        <div id="main-content">
            
            <section class="success-section" style="padding: 60px 20px;">
                <div class="container" style="max-width: 700px; margin: 0 auto; text-align: center;">
                    
                    <!-- Success Icon -->
                    <div class="success-icon" style="margin-bottom: 30px;">
                        <div style="display: inline-block; width: 100px; height: 100px; background: #4caf50; border-radius: 50%; position: relative; animation: scaleIn 0.5s ease-out;">
                            <i class="fas fa-check" style="color: #fff; font-size: 50px; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);"></i>
                        </div>
                    </div>
                    
                    <h1 style="font-size: 36px; color: #4caf50; margin: 0 0 15px 0;">
                        Thanh toán thành công!
                    </h1>
                    
                    <p style="font-size: 18px; color: #666; margin-bottom: 40px;">
                        Cảm ơn bạn đã đặt hàng. Đơn hàng của bạn đã được xác nhận.
                    </p>
                    
                    <!-- Order Details Card -->
                    <div class="order-details-card" style="background: #fff; border: 2px solid #4caf50; border-radius: 12px; padding: 30px; text-align: left; margin-bottom: 30px;">
                        <h3 style="margin: 0 0 20px 0; font-size: 20px; color: #333; text-align: center; padding-bottom: 15px; border-bottom: 2px solid #4caf50;">
                            Thông tin đơn hàng
                        </h3>
                        
                        <div class="order-info-grid" style="display: grid; gap: 20px;">
                            <div class="info-row" style="display: flex; justify-content: space-between; padding: 15px; background: #f9f9f9; border-radius: 6px;">
                                <span style="color: #666; font-weight: 500;">Mã đơn hàng:</span>
                                <span style="color: #333; font-weight: 700; font-size: 16px;"><?php echo htmlspecialchars($order['order_number']); ?></span>
                            </div>
                            
                            <div class="info-row" style="display: flex; justify-content: space-between; padding: 15px; background: #f9f9f9; border-radius: 6px;">
                                <span style="color: #666; font-weight: 500;">Trạng thái:</span>
                                <span style="color: #4caf50; font-weight: 700;">
                                    <i class="fas fa-check-circle"></i> 
                                    <?php echo $order['payment_status'] === 'paid' ? 'Đã thanh toán' : 'Đang xử lý'; ?>
                                </span>
                            </div>
                            
                            <div class="info-row" style="display: flex; justify-content: space-between; padding: 15px; background: #f9f9f9; border-radius: 6px;">
                                <span style="color: #666; font-weight: 500;">Tổng tiền:</span>
                                <span style="color: #2563EB; font-weight: 700; font-size: 20px;">
                                    <?php echo number_format($order['total'], 0, ',', '.'); ?>đ
                                </span>
                            </div>
                            
                            <div class="info-row" style="display: flex; justify-content: space-between; padding: 15px; background: #f9f9f9; border-radius: 6px;">
                                <span style="color: #666; font-weight: 500;">Phương thức:</span>
                                <span style="color: #333; font-weight: 600;">Chuyển khoản QR (SePay)</span>
                            </div>
                            
                            <?php if (!empty($order['customer_name'])): ?>
                            <div class="info-row" style="display: flex; justify-content: space-between; padding: 15px; background: #f9f9f9; border-radius: 6px;">
                                <span style="color: #666; font-weight: 500;">Khách hàng:</span>
                                <span style="color: #333; font-weight: 600;"><?php echo htmlspecialchars($order['customer_name']); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <div class="info-row" style="display: flex; justify-content: space-between; padding: 15px; background: #f9f9f9; border-radius: 6px;">
                                <span style="color: #666; font-weight: 500;">Thời gian:</span>
                                <span style="color: #333; font-weight: 600;">
                                    <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Items -->
                    <?php if (!empty($order['items'])): ?>
                    <div class="order-items-card" style="background: #fff; border: 1px solid #e0e0e0; border-radius: 12px; padding: 30px; text-align: left; margin-bottom: 30px;">
                        <h3 style="margin: 0 0 20px 0; font-size: 18px; color: #333; text-align: center; padding-bottom: 15px; border-bottom: 1px solid #e0e0e0;">
                            Sản phẩm đã mua
                        </h3>
                        
                        <?php foreach ($order['items'] as $item): ?>
                        <div class="order-item" style="display: flex; justify-content: space-between; align-items: center; padding: 15px; background: #f9f9f9; border-radius: 6px; margin-bottom: 10px;">
                            <div>
                                <div style="font-weight: 600; color: #333; margin-bottom: 5px;">
                                    <?php echo htmlspecialchars($item['product_name']); ?>
                                </div>
                                <div style="font-size: 13px; color: #999;">
                                    Số lượng: <?php echo $item['quantity']; ?>
                                </div>
                            </div>
                            <div style="font-weight: 700; color: #2563EB; font-size: 16px;">
                                <?php echo number_format($item['total'], 0, ',', '.'); ?>đ
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Success Message -->
                    <div class="success-message" style="background: #e8f5e9; border-left: 4px solid #4caf50; padding: 20px; border-radius: 6px; margin-bottom: 30px; text-align: left;">
                        <div style="display: flex; gap: 15px; align-items: start;">
                            <i class="fas fa-info-circle" style="color: #4caf50; font-size: 20px; margin-top: 2px;"></i>
                            <div style="color: #2e7d32; line-height: 1.6;">
                                <strong>Đơn hàng demo đã được xử lý thành công!</strong><br>
                                Đây là đơn hàng demo để test chức năng thanh toán với SePay. 
                                Webhook đã được gọi và đơn hàng đã được cập nhật tự động.
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="action-buttons" style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                        <a href="?page=products_demo" 
                           style="display: inline-block; padding: 15px 30px; background: #2563EB; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600; transition: background 0.3s;">
                            <i class="fas fa-shopping-bag"></i> Tiếp tục mua hàng
                        </a>
                        
                        <a href="?page=home" 
                           style="display: inline-block; padding: 15px 30px; background: #fff; color: #2563EB; text-decoration: none; border-radius: 8px; font-weight: 600; border: 2px solid #2563EB; transition: all 0.3s;">
                            <i class="fas fa-home"></i> Về trang chủ
                        </a>
                    </div>
                    
                    <!-- Demo Notice -->
                    <div style="margin-top: 40px; padding: 20px; background: #fff3cd; border-radius: 8px; border: 1px solid #ffc107;">
                        <p style="margin: 0; color: #856404; font-size: 14px;">
                            <i class="fas fa-exclamation-triangle"></i> 
                            <strong>Lưu ý:</strong> Đây là đơn hàng demo. Sau khi hoàn thiện trang sản phẩm thật, 
                            các file demo sẽ được xóa và logic thanh toán sẽ được tích hợp vào trang thật.
                        </p>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<style>
@keyframes scaleIn {
    0% {
        transform: scale(0);
        opacity: 0;
    }
    50% {
        transform: scale(1.1);
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

.action-buttons a:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
</style>
