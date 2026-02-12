<?php
/**
 * Product Details Page - Real Data Only
 */

// 1. Khởi tạo View an toàn
require_once __DIR__ . '/../../../core/view_init.php';

// Get product ID from URL
$productId = $_GET['id'] ?? null;

// Initialize data variables
$product = null;
$category = null;
$relatedProducts = [];
$showErrorMessage = false;
$errorMessage = '';

try {
    if (!$productId) {
        throw new Exception('ID sản phẩm không hợp lệ');
    }
    
    // Get product details
    $productData = $viewDataService->getProductDetailsData($productId);
    $product = $productData['product'] ?? null;
    $category = $productData['category'] ?? null;
    $relatedProducts = $productData['related_products'] ?? [];
    
    if (!$product) {
        throw new Exception('Không tìm thấy sản phẩm');
    }
    
} catch (Exception $e) {
    // Handle errors gracefully
    $result = $errorHandler->handleViewError($e, 'product_details', ['id' => $productId]);
    $showErrorMessage = true;
    $errorMessage = $result['message'];
}

// Helper function to get product image
if (!function_exists('getProductImage')) {
    function getProductImage($product) {
        if (!empty($product['image']) && file_exists($_SERVER['DOCUMENT_ROOT'] . $product['image'])) {
            return $product['image'];
        }
        return '/assets/images/default-product.jpg';
    }
}
?>

<!-- Main Content -->
<div id="wrapper-container" class="wrapper-container">
    <div class="content-pusher">
        <div id="main-content">
            
            <?php if ($showErrorMessage): ?>
            <!-- Error Message -->
            <section class="error-section">
                <div class="container">
                    <div class="error-content">
                        <h1>Sản phẩm không tồn tại</h1>
                        <p><?php echo htmlspecialchars($errorMessage); ?></p>
                        <a href="?page=products" class="btn-back">← Quay lại danh sách sản phẩm</a>
                    </div>
                </div>
            </section>
            
            <?php else: ?>
            <!-- Product Details Section -->
            <section class="product-details-section">
                <div class="container">
                    <div class="product-details-layout">
                        <!-- Left Column - Product Content -->
                        <div class="product-details-main">
                            <!-- Product Header -->
                            <div class="product-header">
                                <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                                
                                <?php if ($category): ?>
                                <div class="product-category">
                                    <span class="category-label">Danh mục:</span>
                                    <a href="?page=products&category=<?php echo $category['id']; ?>" class="category-name">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </a>
                                </div>
                                <?php endif; ?>
                                
                                <div class="product-meta">
                                    <div class="meta-item">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M8 1V8L12 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/>
                                        </svg>
                                        <span>Cập nhật <?php echo date('d/m/Y', strtotime($product['created_at'])); ?></span>
                                    </div>
                                    
                                    <?php if (!empty($product['sku'])): ?>
                                    <div class="meta-item">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M5.33333 6.49992H8M5.33333 9.16659H10.6667M5.33333 11.8333H10.6667M10.6663 1.83325V3.83325M5.33301 1.83325V3.83325M4.66667 2.83325H11.3333C12.8061 2.83325 14 4.02716 14 5.49992V12.4999C14 13.9727 12.8061 15.1666 11.3333 15.1666H4.66667C3.19391 15.1666 2 13.9727 2 12.4999V5.49992C2 4.02716 3.19391 2.83325 4.66667 2.83325Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        <span>SKU: <?php echo htmlspecialchars($product['sku']); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="meta-item">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M8 2L10.09 6.26L15 7L11 10.74L12.18 15.74L8 13.27L3.82 15.74L5 10.74L1 7L5.91 6.26L8 2Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        <span><?php echo ($product['stock'] > 0) ? 'Còn hàng (' . $product['stock'] . ')' : 'Hết hàng'; ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Product Description -->
                            <div class="product-content">
                                <?php if (!empty($product['description'])): ?>
                                <div class="product-description">
                                    <h4>Mô tả sản phẩm</h4>
                                    <div class="description-content">
                                        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($product['short_description'])): ?>
                                <div class="product-short-description">
                                    <h4>Thông tin ngắn gọn</h4>
                                    <div class="short-description-content">
                                        <?php echo nl2br(htmlspecialchars($product['short_description'])); ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <!-- Product Specifications -->
                                <div class="product-specifications">
                                    <h4>Thông số sản phẩm</h4>
                                    <table class="specs-table">
                                        <tr>
                                            <td>Loại sản phẩm:</td>
                                            <td><?php echo htmlspecialchars($product['type'] ?? 'Không xác định'); ?></td>
                                        </tr>
                                        <?php if (!empty($product['weight'])): ?>
                                        <tr>
                                            <td>Trọng lượng:</td>
                                            <td><?php echo htmlspecialchars($product['weight']); ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <?php if (!empty($product['dimensions'])): ?>
                                        <tr>
                                            <td>Kích thước:</td>
                                            <td><?php echo htmlspecialchars($product['dimensions']); ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <tr>
                                            <td>Trạng thái:</td>
                                            <td><?php echo ($product['status'] === 'active') ? 'Đang bán' : 'Ngừng bán'; ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column - Product Sidebar -->
                        <div class="product-sidebar">
                            <div class="product-card">
                                <div class="product-image">
                                    <img src="<?php echo getProductImage($product); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                         class="product-thumbnail">
                                </div>
                                
                                <div class="product-card-content">
                                    <div class="product-price">
                                        <?php if (!empty($product['sale_price']) && $product['sale_price'] < $product['price']): ?>
                                            <span class="price"><?php echo $product['formatted_sale_price']; ?></span>
                                            <span class="old-price"><?php echo $product['formatted_price']; ?></span>
                                            <?php if (!empty($product['discount_percent'])): ?>
                                                <span class="discount">-<?php echo $product['discount_percent']; ?>%</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="price"><?php echo $product['formatted_price']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if ($product['status'] === 'active' && $product['stock'] > 0): ?>
                                        <button class="btn-order" onclick="addToCart(<?php echo $product['id']; ?>)">
                                            Đặt hàng ngay
                                        </button>
                                        <button class="btn-cart" onclick="addToCart(<?php echo $product['id']; ?>)">
                                            Thêm vào giỏ hàng
                                        </button>
                                    <?php else: ?>
                                        <button class="btn-order disabled" disabled>
                                            <?php echo ($product['stock'] <= 0) ? 'Hết hàng' : 'Ngừng bán'; ?>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <div class="product-info">
                                        <h5>Thông tin sản phẩm:</h5>
                                        <ul>
                                            <li>
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M3 8L6 11L13 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                Sản phẩm chính hãng
                                            </li>
                                            <li>
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M3 8L6 11L13 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                Bảo hành theo quy định
                                            </li>
                                            <li>
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M3 8L6 11L13 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                Hỗ trợ khách hàng
                                            </li>
                                            <?php if ($product['digital']): ?>
                                            <li>
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M3 8L6 11L13 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                Sản phẩm số
                                            </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Related Products Section -->
            <?php if (!empty($relatedProducts)): ?>
            <section class="related-products-section">
                <div class="container">
                    <h3>Sản phẩm liên quan</h3>
                    <div class="products-grid">
                        <?php foreach ($relatedProducts as $relatedProduct): ?>
                        <div class="product-item">
                            <div class="product-image">
                                <a href="?page=details&id=<?php echo $relatedProduct['id']; ?>">
                                    <img src="<?php echo getProductImage($relatedProduct); ?>" 
                                         alt="<?php echo htmlspecialchars($relatedProduct['name']); ?>" loading="lazy">
                                </a>
                            </div>
                            <div class="product-content">
                                <h4 class="product-title">
                                    <a href="?page=details&id=<?php echo $relatedProduct['id']; ?>">
                                        <?php echo htmlspecialchars($relatedProduct['name']); ?>
                                    </a>
                                </h4>
                                <?php if (!empty($relatedProduct['short_description'])): ?>
                                <div class="product-excerpt">
                                    <?php echo htmlspecialchars(substr($relatedProduct['short_description'], 0, 100)) . '...'; ?>
                                </div>
                                <?php endif; ?>
                                <div class="product-price">
                                    <?php if (!empty($relatedProduct['sale_price']) && $relatedProduct['sale_price'] < $relatedProduct['price']): ?>
                                        <span class="price"><?php echo $relatedProduct['formatted_sale_price']; ?></span>
                                        <span class="old-price"><?php echo $relatedProduct['formatted_price']; ?></span>
                                    <?php else: ?>
                                        <span class="price"><?php echo $relatedProduct['formatted_price']; ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="product-button">
                                    <a href="?page=details&id=<?php echo $relatedProduct['id']; ?>" class="btn-view-details">
                                        Xem chi tiết
                                    </a>
                                </div>
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

<script>
function addToCart(productId) {
    // Add to cart functionality
    alert('Thêm sản phẩm vào giỏ hàng: ' + productId);
}
</script>