<?php
/**
 * Product Details Demo Page
 * Trang chi tiết sản phẩm demo
 */

require_once __DIR__ . '/../../../core/view_init.php';
require_once __DIR__ . '/../../controllers/ProductsDemoController.php';

$productId = $_GET['id'] ?? null;

if (!$productId) {
    header('Location: ?page=products_demo');
    exit;
}

$controller = new ProductsDemoController();
$result = $controller->details($productId);

$product = $result['product'] ?? null;
$relatedProducts = $result['related_products'] ?? [];
$showErrorMessage = !$result['success'];
$errorMessage = $result['message'] ?? '';
?>

<div id="wrapper-container" class="wrapper-container">
    <div class="content-pusher">
        <div id="main-content">
            
            <?php if ($showErrorMessage): ?>
            <!-- Error Message -->
            <section class="error-section" style="padding: 60px 20px; text-align: center;">
                <div class="container" style="max-width: 800px; margin: 0 auto;">
                    <h1 style="color: #d32f2f; margin-bottom: 20px;">Không tìm thấy sản phẩm</h1>
                    <p style="color: #666; margin-bottom: 30px;"><?php echo htmlspecialchars($errorMessage); ?></p>
                    <a href="?page=products_demo" style="display: inline-block; padding: 12px 30px; background: #2563EB; color: #fff; text-decoration: none; border-radius: 4px; font-weight: bold;">
                        ← Quay lại danh sách
                    </a>
                </div>
            </section>
            
            <?php else: ?>
            
            <!-- Demo Notice -->
            <div class="demo-notice" style="background: #fff3cd; border: 2px solid #ffc107; padding: 15px; margin: 20px auto; max-width: 1200px; border-radius: 8px; text-align: center;">
                <p style="color: #856404; margin: 0; font-size: 14px;">
                    <i class="fas fa-exclamation-triangle"></i> <strong>TRANG DEMO</strong> - Sản phẩm giả lập để test thanh toán
                </p>
            </div>
            
            <!-- Product Details Section -->
            <section class="product-details-section" style="padding: 40px 20px;">
                <div class="container" style="max-width: 1200px; margin: 0 auto;">
                    <div class="product-details-layout" style="display: grid; grid-template-columns: 1fr 400px; gap: 40px;">
                        
                        <!-- Left Column - Product Info -->
                        <div class="product-main">
                            <div class="product-image-large" style="background: #f5f5f5; border-radius: 8px; overflow: hidden; margin-bottom: 30px;">
                                <img src="<?php echo $product['image'] ?: 'https://via.placeholder.com/800x600?text=Demo+Product'; ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                     style="width: 100%; height: auto; display: block;">
                            </div>
                            
                            <h1 class="product-title" style="font-size: 32px; margin: 0 0 20px 0; color: #333;">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </h1>
                            
                            <div class="product-meta" style="display: flex; gap: 20px; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #e0e0e0; font-size: 14px; color: #666;">
                                <span><i class="fas fa-tag"></i> SKU: <?php echo htmlspecialchars($product['sku']); ?></span>
                                <span><i class="fas fa-box"></i> <?php echo $product['in_stock'] ? 'Còn hàng' : 'Hết hàng'; ?></span>
                                <span><i class="fas fa-eye"></i> <?php echo $product['views']; ?> lượt xem</span>
                            </div>
                            
                            <div class="product-description" style="margin-bottom: 30px;">
                                <h3 style="font-size: 20px; margin: 0 0 15px 0; color: #333;">Mô tả sản phẩm</h3>
                                <div style="color: #666; line-height: 1.8;">
                                    <?php echo nl2br(htmlspecialchars($product['description'] ?: 'Sản phẩm demo để test chức năng thanh toán với SePay. Giá 10,000đ để dễ dàng kiểm tra giao dịch.')); ?>
                                </div>
                            </div>
                            
                            <div class="product-features" style="background: #f9f9f9; padding: 20px; border-radius: 8px;">
                                <h3 style="font-size: 18px; margin: 0 0 15px 0; color: #333;">Đặc điểm nổi bật</h3>
                                <ul style="margin: 0; padding-left: 20px; color: #666; line-height: 2;">
                                    <li>Sản phẩm demo để test thanh toán</li>
                                    <li>Tích hợp với SePay payment gateway</li>
                                    <li>Giá cố định 10,000đ cho mục đích test</li>
                                    <li>Hỗ trợ thanh toán qua QR code</li>
                                    <li>Webhook tự động xác nhận thanh toán</li>
                                </ul>
                            </div>
                        </div>
                        
                        <!-- Right Column - Purchase Card -->
                        <div class="product-sidebar">
                            <div class="purchase-card" style="background: #fff; border: 2px solid #2563EB; border-radius: 8px; padding: 30px; position: sticky; top: 20px;">
                                <div class="product-price" style="margin-bottom: 20px; text-align: center;">
                                    <div style="font-size: 14px; color: #666; margin-bottom: 5px;">Giá demo</div>
                                    <div class="price" style="font-size: 36px; font-weight: bold; color: #2563EB;">
                                        <?php echo $product['formatted_price']; ?>
                                    </div>
                                </div>
                                
                                <div class="stock-status" style="text-align: center; margin-bottom: 20px; padding: 10px; background: #e8f5e9; border-radius: 4px; color: #2e7d32; font-weight: bold;">
                                    <i class="fas fa-check-circle"></i> Còn hàng (<?php echo $product['stock']; ?>)
                                </div>
                                
                                <?php if ($product['in_stock']): ?>
                                <div class="purchase-actions" style="display: flex; flex-direction: column; gap: 15px;">
                                    <a href="?page=checkout_demo&product_id=<?php echo $product['id']; ?>" 
                                       class="btn-buy-now" 
                                       style="display: block; padding: 15px; text-align: center; background: #2563EB; color: #fff; text-decoration: none; border-radius: 4px; font-size: 16px; font-weight: bold; transition: background 0.3s;">
                                        <i class="fas fa-shopping-cart"></i> Thanh toán ngay
                                    </a>
                                    
                                    <button onclick="alert('Chức năng giỏ hàng đang được phát triển')" 
                                            class="btn-add-cart" 
                                            style="padding: 15px; background: #fff; color: #2563EB; border: 2px solid #2563EB; border-radius: 4px; font-size: 16px; font-weight: bold; cursor: pointer; transition: all 0.3s;">
                                        <i class="fas fa-cart-plus"></i> Thêm vào giỏ hàng
                                    </button>
                                </div>
                                <?php else: ?>
                                <button disabled class="btn-out-of-stock" style="width: 100%; padding: 15px; background: #ccc; color: #666; border: none; border-radius: 4px; font-size: 16px; font-weight: bold; cursor: not-allowed;">
                                    Hết hàng
                                </button>
                                <?php endif; ?>
                                
                                <div class="product-info" style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0;">
                                    <h4 style="font-size: 16px; margin: 0 0 15px 0; color: #333;">Thông tin sản phẩm</h4>
                                    <ul style="margin: 0; padding: 0; list-style: none; font-size: 14px; color: #666;">
                                        <li style="padding: 8px 0; border-bottom: 1px solid #f0f0f0;">
                                            <i class="fas fa-check" style="color: #4caf50; margin-right: 8px;"></i>
                                            Sản phẩm demo
                                        </li>
                                        <li style="padding: 8px 0; border-bottom: 1px solid #f0f0f0;">
                                            <i class="fas fa-check" style="color: #4caf50; margin-right: 8px;"></i>
                                            Thanh toán qua SePay
                                        </li>
                                        <li style="padding: 8px 0; border-bottom: 1px solid #f0f0f0;">
                                            <i class="fas fa-check" style="color: #4caf50; margin-right: 8px;"></i>
                                            Hỗ trợ QR code
                                        </li>
                                        <li style="padding: 8px 0;">
                                            <i class="fas fa-check" style="color: #4caf50; margin-right: 8px;"></i>
                                            Webhook tự động
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Related Products -->
            <?php if (!empty($relatedProducts)): ?>
            <section class="related-products-section" style="padding: 40px 20px; background: #f9f9f9;">
                <div class="container" style="max-width: 1200px; margin: 0 auto;">
                    <h3 style="text-align: center; margin-bottom: 30px; font-size: 28px; color: #333;">Sản phẩm demo khác</h3>
                    <div class="products-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
                        <?php foreach ($relatedProducts as $related): ?>
                        <div class="product-card" style="background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; transition: transform 0.3s;">
                            <div class="product-image" style="position: relative; padding-top: 66.67%; overflow: hidden;">
                                <a href="?page=details_demo&id=<?php echo $related['id']; ?>" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;">
                                    <img src="<?php echo $related['image'] ?: 'https://via.placeholder.com/400x300'; ?>" 
                                         alt="<?php echo htmlspecialchars($related['name']); ?>" 
                                         style="width: 100%; height: 100%; object-fit: cover;">
                                </a>
                            </div>
                            <div class="product-content" style="padding: 15px;">
                                <h4 style="margin: 0 0 10px 0; font-size: 16px;">
                                    <a href="?page=details_demo&id=<?php echo $related['id']; ?>" style="color: #333; text-decoration: none;">
                                        <?php echo htmlspecialchars($related['name']); ?>
                                    </a>
                                </h4>
                                <div class="price" style="font-size: 20px; font-weight: bold; color: #2563EB; margin-bottom: 10px;">
                                    <?php echo $related['formatted_price']; ?>
                                </div>
                                <a href="?page=details_demo&id=<?php echo $related['id']; ?>" 
                                   style="display: block; padding: 8px; text-align: center; background: #f0f0f0; color: #333; text-decoration: none; border-radius: 4px; font-size: 14px;">
                                    Xem chi tiết
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
            <?php endif; ?>
            
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.btn-buy-now:hover {
    background: #1d4ed8;
}

.btn-add-cart:hover {
    background: #2563EB;
    color: #fff;
}

.product-card:hover {
    transform: translateY(-5px);
}

@media (max-width: 768px) {
    .product-details-layout {
        grid-template-columns: 1fr !important;
    }
    
    .purchase-card {
        position: static !important;
    }
}
</style>
