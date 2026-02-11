<?php
/**
 * Product Details Page - Dynamic Version
 * Converted from hardcoded data to dynamic database data
 */

// Load required services and models
require_once __DIR__ . '/../../services/ViewDataService.php';
require_once __DIR__ . '/../../services/ErrorHandler.php';

// Initialize services
$viewDataService = new ViewDataService();
$errorHandler = new ErrorHandler();

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
    
    // Use fallback data
    $product = [
        'id' => $productId ?: 1,
        'name' => 'Sản phẩm không tồn tại',
        'description' => 'Sản phẩm bạn đang tìm kiếm không tồn tại hoặc đã bị xóa.',
        'formatted_price' => '0₫',
        'image' => '/assets/images/default-product.jpg',
        'status' => 'inactive',
        'created_at' => date('Y-m-d H:i:s'),
        'category_name' => 'Không xác định'
    ];
}

// Helper function to get product image
function getProductImage($product) {
    if (!empty($product['image']) && $product['image'] !== '/assets/images/default-product.jpg') {
        return $product['image'];
    }
    return 'https://eduma.thimpress.com/demo-marketplace/wp-content/uploads/sites/99/2024/10/course-offline-01-675x450.jpg';
}

// Product features based on category or default
$productFeatures = [
    'Sản phẩm chất lượng cao được kiểm định',
    'Thông tin chi tiết và đầy đủ về sản phẩm',
    'Hỗ trợ tư vấn từ đội ngũ chuyên gia',
    'Dịch vụ giao hàng nhanh chóng và an toàn',
    'Chính sách bảo hành và đổi trả linh hoạt',
    'Giá cả cạnh tranh trên thị trường',
    'Hỗ trợ khách hàng 24/7',
    'Cập nhật thông tin sản phẩm thường xuyên'
];

// Package contents - can be customized based on product type
$packageContents = [
    [
        'title' => 'Thông tin sản phẩm',
        'items' => ['Mô tả chi tiết sản phẩm', 'Hình ảnh chất lượng cao', 'Thông số kỹ thuật đầy đủ']
    ],
    [
        'title' => 'Dịch vụ hỗ trợ',
        'items' => ['Tư vấn trước khi mua', 'Hướng dẫn sử dụng', 'Hỗ trợ sau bán hàng']
    ],
    [
        'title' => 'Chính sách',
        'items' => ['Bảo hành chính hãng', 'Đổi trả trong 7 ngày', 'Giao hàng miễn phí']
    ],
    [
        'title' => 'Ưu đãi đặc biệt',
        'items' => ['Giảm giá cho khách hàng thân thiết', 'Tích điểm mỗi lần mua hàng', 'Quà tặng kèm theo']
    ]
];

// Provider info
$providerInfo = [
    'name' => 'ThuongLo.com',
    'description' => 'Chuyên gia hàng đầu về thương mại điện tử và cung cấp sản phẩm chất lượng',
    'experience' => '5+ năm kinh nghiệm',
    'customers' => '10,000+ khách hàng tin tưởng',
    'rating' => 4.8,
    'specialties' => ['Sản phẩm chất lượng', 'Thương mại điện tử', 'Dịch vụ khách hàng', 'Logistics chuyên nghiệp']
];
?>

<!-- Main Content -->
<div id="wrapper-container" class="wrapper-container">
    <div class="content-pusher">
        <div id="main-content">
            <!-- Error Message -->
            <?php if ($showErrorMessage): ?>
            <div class="error-message" style="background: #f8d7da; color: #721c24; padding: 15px; margin: 20px 0; border-radius: 5px;">
                <strong>Thông báo:</strong> <?php echo htmlspecialchars($errorMessage); ?>
            </div>
            <?php endif; ?>
            
            <!-- Course Details Section -->
            <section class="course-details-section">
                <div class="container">
                    <div class="course-details-layout">
                        <!-- Left Column - Course Content -->
                        <div class="course-details-main">
                            <!-- Course Header -->
                            <div class="course-header">
                                <h1 class="course-title"><?php echo $product['name']; ?></h1>
                                <div class="course-instructor">
                                    <span class="instructor-label">Được cung cấp bởi</span>
                                    <a href="#" class="instructor-name"><?php echo htmlspecialchars($providerInfo['name']); ?></a>
                                </div>
                                <div class="course-meta">
                                    <div class="meta-item">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M8 1V8L12 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/>
                                        </svg>
                                        <span>Cập nhật <?php echo date('m/Y', strtotime($product['created_at'])); ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M5.33333 6.49992H8M5.33333 9.16659H10.6667M5.33333 11.8333H10.6667M10.6663 1.83325V3.83325M5.33301 1.83325V3.83325M4.66667 2.83325H11.3333C12.8061 2.83325 14 4.02716 14 5.49992V12.4999C14 13.9727 12.8061 15.1666 11.3333 15.1666H4.66667C3.19391 15.1666 2 13.9727 2 12.4999V5.49992C2 4.02716 3.19391 2.83325 4.66667 2.83325Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        <span><?php echo $product['category_name'] ?: 'Sản phẩm'; ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M8 2L10.09 6.26L15 7L11 10.74L12.18 15.74L8 13.27L3.82 15.74L5 10.74L1 7L5.91 6.26L8 2Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        <span><?php echo $product['in_stock'] ? 'Còn hàng' : 'Hết hàng'; ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Course Tabs -->
                            <div class="course-tabs">
                                <div class="tabs-nav">
                                    <button class="tab-item active" data-tab="description">Mô tả</button>
                                    <button class="tab-item" data-tab="curriculum">Chi tiết</button>
                                    <button class="tab-item" data-tab="instructor">Nhà cung cấp</button>
                                </div>

                                <div class="tabs-content">
                                    <!-- Description Tab -->
                                    <div class="tab-panel active" id="description">
                                        <div class="course-description">
                                            <h4>Bạn sẽ nhận được gì</h4>
                                            <div class="learning-objectives">
                                                <div class="objectives-grid">
                                                    <?php foreach ($productFeatures as $feature): ?>
                                                    <div class="objective-item">
                                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M16.6667 5L7.50004 14.1667L3.33337 10" stroke="#356DF1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                        <span><?php echo htmlspecialchars($feature); ?></span>
                                                    </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>

                                            <div class="course-content-description">
                                                <h4>Mô tả sản phẩm</h4>
                                                <p><?php echo nl2br($product['description'] ?: 'Thông tin chi tiết về sản phẩm sẽ được cập nhật sớm.'); ?></p>
                                                
                                                <?php if ($product['short_description']): ?>
                                                <p><?php echo nl2br($product['short_description']); ?></p>
                                                <?php endif; ?>
                                                
                                                <h5>Yêu cầu</h5>
                                                <ul>
                                                    <li>Đọc kỹ thông tin sản phẩm trước khi đặt hàng</li>
                                                    <li>Liên hệ tư vấn nếu có thắc mắc</li>
                                                    <li>Kiểm tra chính sách đổi trả</li>
                                                    <li>Cung cấp thông tin giao hàng chính xác</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Details Tab -->
                                    <div class="tab-panel" id="curriculum">
                                        <div class="course-curriculum">
                                            <?php foreach ($packageContents as $index => $section): ?>
                                            <div class="curriculum-section">
                                                <div class="section-header">
                                                    <h5>Phần <?php echo $index + 1; ?>: <?php echo htmlspecialchars($section['title']); ?></h5>
                                                    <span class="section-info"><?php echo count($section['items']); ?> mục</span>
                                                </div>
                                                <div class="section-lessons">
                                                    <?php foreach ($section['items'] as $item): ?>
                                                    <div class="lesson-item">
                                                        <div class="lesson-icon">
                                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path d="M3 8L6 11L13 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                            </svg>
                                                        </div>
                                                        <span class="lesson-title"><?php echo htmlspecialchars($item); ?></span>
                                                    </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>

                                    <!-- Instructor Tab -->
                                    <div class="tab-panel" id="instructor">
                                        <div class="instructor-info">
                                            <div class="instructor-header">
                                                <div class="instructor-avatar">
                                                    <img src="https://via.placeholder.com/80x80" alt="<?php echo htmlspecialchars($providerInfo['name']); ?>">
                                                </div>
                                                <div class="instructor-details">
                                                    <h4><?php echo htmlspecialchars($providerInfo['name']); ?></h4>
                                                    <p><?php echo htmlspecialchars($providerInfo['description']); ?></p>
                                                    <div class="instructor-stats">
                                                        <div class="stat-item">
                                                            <span class="stat-value"><?php echo $providerInfo['rating']; ?></span>
                                                            <span class="stat-label">Đánh giá</span>
                                                        </div>
                                                        <div class="stat-item">
                                                            <span class="stat-value"><?php echo $providerInfo['customers']; ?></span>
                                                            <span class="stat-label">Khách hàng</span>
                                                        </div>
                                                        <div class="stat-item">
                                                            <span class="stat-value"><?php echo $providerInfo['experience']; ?></span>
                                                            <span class="stat-label">Kinh nghiệm</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="instructor-specialties">
                                                <h5>Chuyên môn</h5>
                                                <div class="specialties-list">
                                                    <?php foreach ($providerInfo['specialties'] as $specialty): ?>
                                                        <span class="specialty-tag"><?php echo htmlspecialchars($specialty); ?></span>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column - Course Sidebar -->
                        <div class="course-sidebar">
                            <div class="course-card">
                                <div class="course-badge">
                                    <img src="<?php echo getProductImage($product); ?>" 
                                         alt="<?php echo $product['name']; ?>" 
                                         class="course-thumbnail">
                                </div>
                                <div class="course-card-content">
                                    <div class="course-price">
                                        <?php if ($product['sale_price']): ?>
                                            <span class="price"><?php echo $product['formatted_sale_price']; ?></span>
                                            <span class="old-price"><?php echo $product['formatted_price']; ?></span>
                                            <?php if ($product['discount_percent']): ?>
                                                <span class="discount">-<?php echo $product['discount_percent']; ?>%</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="price"><?php echo $product['formatted_price']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if ($product['status'] === 'active' && $product['in_stock']): ?>
                                        <button class="btn-enroll">Đặt hàng ngay</button>
                                        <button class="btn-cart">Thêm vào giỏ hàng</button>
                                    <?php else: ?>
                                        <button class="btn-enroll disabled" disabled>Hết hàng</button>
                                    <?php endif; ?>
                                    
                                    <div class="course-includes">
                                        <h5>Sản phẩm bao gồm:</h5>
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
                                                Bảo hành chính hãng
                                            </li>
                                            <li>
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M3 8L6 11L13 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                Giao hàng miễn phí
                                            </li>
                                            <li>
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M3 8L6 11L13 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                Hỗ trợ 24/7
                                            </li>
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
                        <div class="course-item">
                            <div class="course-category">
                                <a href="?page=products&category=<?php echo $relatedProduct['category_id'] ?? ''; ?>" class="category-tag">
                                    <?php echo $relatedProduct['category_name'] ?: 'Sản phẩm'; ?>
                                </a>
                            </div>
                            <div class="course-image">
                                <a href="?page=details&id=<?php echo $relatedProduct['id']; ?>">
                                    <img src="<?php echo getProductImage($relatedProduct); ?>" 
                                         alt="<?php echo $relatedProduct['name']; ?>" loading="lazy">
                                </a>
                            </div>
                            <div class="course-content">
                                <h4 class="course-title">
                                    <a href="?page=details&id=<?php echo $relatedProduct['id']; ?>">
                                        <?php echo $relatedProduct['name']; ?>
                                    </a>
                                </h4>
                                <div class="course-excerpt">
                                    <?php echo $relatedProduct['short_description'] ?: 'Sản phẩm chất lượng cao từ ThuongLo.com'; ?>
                                </div>
                                <div class="course-price">
                                    <?php if ($relatedProduct['sale_price']): ?>
                                        <span class="price"><?php echo $relatedProduct['formatted_sale_price']; ?></span>
                                        <span class="old-price"><?php echo $relatedProduct['formatted_price']; ?></span>
                                    <?php else: ?>
                                        <span class="price"><?php echo $relatedProduct['formatted_price']; ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="course-button">
                                    <a href="?page=details&id=<?php echo $relatedProduct['id']; ?>" class="btn-start-learning">
                                        <i class="fas fa-play"></i>
                                        <span>Xem chi tiết</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
            <?php endif; ?>
        </div>
    </div>
</div>