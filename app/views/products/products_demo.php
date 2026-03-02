<?php
/**
 * Products Demo Page
 * Trang sản phẩm demo để test thanh toán
 */

require_once __DIR__ . '/../../../core/view_init.php';

// Initialize variables
$products = [];
$showErrorMessage = false;
$errorMessage = '';

try {
    require_once __DIR__ . '/../../controllers/ProductsDemoController.php';
    
    $controller = new ProductsDemoController();
    $result = $controller->index();
    
    $products = $result['products'] ?? [];
    $showErrorMessage = !$result['success'];
    $errorMessage = $result['message'] ?? '';
    
} catch (Exception $e) {
    $showErrorMessage = true;
    $errorMessage = 'Lỗi: ' . $e->getMessage();
}
?>

<div id="wrapper-container" class="wrapper-container">
    <div class="content-pusher">
        <div id="main-content">
            <div class="elementor elementor-demo">
                
                <!-- Demo Notice -->
                <div class="demo-notice" style="background: #fff3cd; border: 2px solid #ffc107; padding: 20px; margin: 20px auto; max-width: 1200px; border-radius: 8px; text-align: center;">
                    <h3 style="color: #856404; margin: 0 0 10px 0;">
                        <i class="fas fa-exclamation-triangle"></i> TRANG DEMO - TEST THANH TOÁN
                    </h3>
                    <p style="color: #856404; margin: 0; font-size: 14px;">
                        Đây là trang demo để test chức năng thanh toán với SePay. Tất cả sản phẩm đều có giá 10,000đ.
                        <br>Sau khi hoàn thiện trang sản phẩm thật, trang này sẽ được xóa.
                    </p>
                </div>

                <?php if ($showErrorMessage): ?>
                <div class="error-message" style="background: #f8d7da; color: #721c24; padding: 15px; margin: 20px auto; max-width: 1200px; border-radius: 5px; text-align: center;">
                    <strong>Lỗi:</strong> <?php echo htmlspecialchars($errorMessage); ?>
                </div>
                <?php endif; ?>
                
                <!-- Products Section -->
                <section class="products-demo-section" style="padding: 40px 20px;">
                    <div class="container" style="max-width: 1200px; margin: 0 auto;">
                        <h1 class="page-title" style="text-align: center; margin-bottom: 30px; font-size: 32px; color: #333;">
                            Sản phẩm Demo
                        </h1>
                        
                        <div class="products-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 30px;">
                            <?php if (empty($products)): ?>
                                <div class="no-products" style="grid-column: 1/-1; text-align: center; padding: 40px;">
                                    <p style="font-size: 18px; color: #666;">Chưa có sản phẩm demo. Vui lòng chạy file database/demo_schema.sql</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($products as $product): ?>
                                <div class="product-card" style="background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; transition: transform 0.3s, box-shadow 0.3s;">
                                    <div class="product-image" style="position: relative; padding-top: 66.67%; overflow: hidden; background: #f5f5f5;">
                                        <a href="?page=details_demo&id=<?php echo $product['id']; ?>" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;">
                                            <img src="<?php echo $product['image'] ?: 'https://via.placeholder.com/400x300?text=Demo+Product'; ?>" 
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                                 style="width: 100%; height: 100%; object-fit: cover;">
                                        </a>
                                        <?php if ($product['featured']): ?>
                                        <span class="badge-featured" style="position: absolute; top: 10px; right: 10px; background: #ff5722; color: #fff; padding: 5px 10px; border-radius: 4px; font-size: 12px; font-weight: bold;">
                                            Nổi bật
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="product-content" style="padding: 20px;">
                                        <h3 class="product-title" style="margin: 0 0 10px 0; font-size: 18px; line-height: 1.4;">
                                            <a href="?page=details_demo&id=<?php echo $product['id']; ?>" style="color: #333; text-decoration: none;">
                                                <?php echo htmlspecialchars($product['name']); ?>
                                            </a>
                                        </h3>
                                        
                                        <div class="product-excerpt" style="color: #666; font-size: 14px; margin-bottom: 15px; line-height: 1.6;">
                                            <?php echo htmlspecialchars($product['short_description'] ?: 'Sản phẩm demo để test thanh toán'); ?>
                                        </div>
                                        
                                        <div class="product-meta" style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px; font-size: 13px; color: #999;">
                                            <span>
                                                <i class="fas fa-box"></i>
                                                <?php echo $product['in_stock'] ? 'Còn hàng' : 'Hết hàng'; ?>
                                            </span>
                                            <span>|</span>
                                            <span>
                                                <i class="fas fa-eye"></i>
                                                <?php echo $product['views']; ?> lượt xem
                                            </span>
                                        </div>
                                        
                                        <div class="product-price" style="margin-bottom: 15px;">
                                            <span class="price" style="font-size: 24px; font-weight: bold; color: #2563EB;">
                                                <?php echo $product['formatted_price']; ?>
                                            </span>
                                        </div>
                                        
                                        <div class="product-actions" style="display: flex; gap: 10px;">
                                            <a href="?page=details_demo&id=<?php echo $product['id']; ?>" 
                                               class="btn-view" 
                                               style="flex: 1; padding: 10px; text-align: center; background: #f0f0f0; color: #333; text-decoration: none; border-radius: 4px; font-size: 14px; transition: background 0.3s;">
                                                Xem chi tiết
                                            </a>
                                            <a href="?page=checkout_demo&product_id=<?php echo $product['id']; ?>" 
                                               class="btn-buy" 
                                               style="flex: 1; padding: 10px; text-align: center; background: #2563EB; color: #fff; text-decoration: none; border-radius: 4px; font-size: 14px; font-weight: bold; transition: background 0.3s;">
                                                Mua ngay
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>

<style>
.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.btn-view:hover {
    background: #e0e0e0;
}

.btn-buy:hover {
    background: #1d4ed8;
}
</style>
