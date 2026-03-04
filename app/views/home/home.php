<?php
/**
 * Home Page - Dynamic Version
 */

// 1. Khởi tạo View an toàn & ServiceManager
require_once __DIR__ . '/../../../core/view_init.php';

// Helper function for formatting record count
if (!function_exists('formatRecordCount')) {
    function formatRecordCount($count) {
        if (!$count || $count == 0) {
            return 'Liên hệ';
        }
        if ($count >= 1000) {
            return number_format($count, 0, ',', '.') . ' records';
        }
        return number_format($count, 0, ',', '.') . ' records';
    }
}

// 2. Chọn service phù hợp (ưu tiên biến được inject từ routing)
$service = isset($currentService) ? $currentService : ($publicService ?? null);

// 3. Khởi tạo biến dữ liệu
$homeData = [];
$showErrorMessage = false;
$errorMessage = '';

try {
    // Sử dụng PublicService thông qua entry-point getHomePageData()
    $homeData = ($service !== null && method_exists($service, 'getHomePageData'))
        ? $service->getHomePageData()
        : [];
    
    $featuredProducts = $homeData['featured_products'] ?? [];
    $latestProducts = $homeData['latest_products'] ?? [];
    $featuredCategories = $homeData['featured_categories'] ?? [];
    $latestNews = $homeData['latest_news'] ?? [];
    
} catch (Exception $e) {
    if (isset($errorHandler)) {
        $result = $errorHandler->handleViewError($e, 'home', []);
        $showErrorMessage = true;
        $errorMessage = $result['message'];
    }
}
?>

<!-- Home Page Content -->
<?php if ($showErrorMessage): ?>
<div class="error-message" style="background: #f8d7da; color: #721c24; padding: 15px; margin: 20px 0; border-radius: 5px; text-align: center;">
    <strong>Thông báo:</strong> <?php echo htmlspecialchars($errorMessage); ?>
</div>
<?php endif; ?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="hero-content">
            <div class="hero-left">
                <h1 class="hero-title">
                    Nền tảng data nguồn hàng và dịch vụ
                    <span class="highlight">Thương mại xuyên biên giới</span>
                </h1>
                <div class="hero-subtitle">
                    <p>ThuongLo là nền tảng hàng đầu cung cấp data nguồn hàng chất lượng, dịch vụ vận chuyển chính ngạch và hỗ trợ toàn diện cho các doanh nghiệp muốn phát triển thương mại xuyên biên giới.</p>
                </div>
                <div class="hero-buttons">
                    <a href="?page=register" class="btn-primary">Đăng ký miễn phí</a>
                    <a href="?page=products" class="btn-secondary">Xem sản phẩm</a>
                </div>
            </div>
            <div class="hero-right">
                <div class="hero-image">
                    <img fetchpriority="high" decoding="async" width="600" height="600" 
                         src="<?php echo img_url('home/home-banner-final.png'); ?>" 
                         alt="ThuongLo - Nền tảng thương mại xuyên biên giới" 
                         srcset="<?php echo img_url('home/home-banner-final.png'); ?> 600w, <?php echo img_url('home/home-banner-final.png'); ?> 360w, <?php echo img_url('home/home-banner-final.png'); ?> 150w, <?php echo img_url('home/home-banner-final.png'); ?> 100w"
                         sizes="(max-width: 600px) 100vw, 600px" />
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section class="popular-courses-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Sản phẩm <span class="highlight">Nổi bật</span></h2>
            <a href="?page=products" class="see-more-btn">
                Xem thêm
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3.33333 8H12.6667M12.6667 8L8 3.33333M12.6667 8L8 12.6667" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
        </div>
        
        <div class="courses-slider-wrapper">
            <div class="courses-slider">
                <!-- Slider Navigation -->
                <div class="slider-nav slider-nav-prev" title="Previous">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12.5 15L7.5 10L12.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="slider-nav slider-nav-next" title="Next">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M7.5 15L12.5 10L7.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                
                <!-- Products Container -->
                <div class="courses-container">
                    <div class="courses-grid">
                        <?php if (!empty($featuredProducts)): ?>
                            <?php foreach ($featuredProducts as $product): ?>
                                <div class="course-item">
                                    <div class="course-category">
                                        <a href="?page=categories&id=<?php echo $product['category_id'] ?? ''; ?>" class="category-tag">
                                            <?php echo $product['category_name'] ?: 'Sản phẩm'; ?>
                                        </a>
                                    </div>
                                    <div class="course-image">
                                        <a href="?page=details&id=<?php echo $product['id']; ?>">
                                            <img src="<?php echo getProductImage($product); ?>" 
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>" loading="lazy">
                                        </a>
                                    </div>
                                    <div class="course-content">
                                        <h4 class="course-title">
                                            <a href="?page=details&id=<?php echo $product['id']; ?>">
                                                <?php echo htmlspecialchars($product['name']); ?>
                                            </a>
                                        </h4>
                                        <div class="course-excerpt">
                                            <?php echo htmlspecialchars($product['short_description'] ?: 'Sản phẩm chất lượng cao từ ' . ($product['supplier_name'] ?? 'ThuongLo.com')); ?>
                                        </div>
                                        <div class="course-instructor">
                                            <a href="#" class="instructor-name"><?php echo $product['supplier_name'] ?? 'ThuongLo.com'; ?></a>
                                        </div>
                                        <div class="course-meta">
                                            <div class="course-lessons">
                                                <!-- Database icon for logistics data -->
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <ellipse cx="12" cy="6" rx="9" ry="3" stroke="#356DF1" stroke-width="2"/>
                                                    <path d="M3 6V18C3 19.6569 7.02944 21 12 21C16.9706 21 21 19.6569 21 18V6" stroke="#356DF1" stroke-width="2"/>
                                                    <path d="M3 12V18C3 19.6569 7.02944 21 12 21C16.9706 21 21 19.6569 21 18V12" stroke="#356DF1" stroke-width="2"/>
                                                    <path d="M21 6V18" stroke="#356DF1" stroke-width="2"/>
                                                    <ellipse cx="12" cy="12" rx="9" ry="3" stroke="#356DF1" stroke-width="2"/>
                                                </svg>
                                                <!-- Display record count for logistics data -->
                                                <span><?php echo formatRecordCount($product['record_count'] ?? $product['in_stock'] ?? 0); ?></span>
                                            </div>
                                        </div>
                                        <div class="course-price">
                                            <?php if (!empty($product['sale_price'])): ?>
                                                <span class="price"><?php echo $product['formatted_sale_price']; ?></span>
                                                <span class="old-price"><?php echo $product['formatted_price']; ?></span>
                                            <?php else: ?>
                                                <span class="price"><?php echo $product['formatted_price']; ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="course-button">
                                            <a href="?page=details&id=<?php echo $product['id']; ?>" class="btn-start-learning">
                                                <i class="fas fa-database"></i>
                                                <span>Xem chi tiết</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <p>Chưa có sản phẩm nổi bật nào. Vui lòng quay lại sau.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="slider-pagination">
                        <?php 
                        $productCount = count($featuredProducts);
                        $maxBullets = min(5, max(1, ceil($productCount / 4))); 
                        for ($i = 0; $i < $maxBullets; $i++): 
                        ?>
                            <span class="pagination-bullet <?php echo $i === 0 ? 'active' : ''; ?>"></span>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Categories Section -->
<section class="elementor-section elementor-top-section elementor-element elementor-element-2932ede elementor-section-stretched elementor-section-boxed elementor-section-height-default elementor-section-height-default" data-id="2932ede" data-element_type="section" data-settings="{&quot;stretch_section&quot;:&quot;section-stretched&quot;}">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Danh mục <span class="highlight">Nổi bật</span></h2>
            <a href="?page=categories" class="see-more-btn">
                Xem tất cả
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3.33333 8H12.6667M12.6667 8L8 3.33333M12.6667 8L8 12.6667" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
        </div>
        
        <div class="thim-widget-course-categories-grid layout-image-cats">
            <ul class="columns-3">
                <?php if (!empty($featuredCategories)): ?>
                    <?php foreach ($featuredCategories as $category): ?>
                        <li>
                            <a href="?page=categories&id=<?php echo $category['id']; ?>">
                                <img loading="lazy" decoding="async" 
                                     src="<?php echo getCategoryImage($category); ?>" 
                                     alt="<?php echo htmlspecialchars($category['name']); ?>" 
                                     width="380" height="126"> 
                                <span class="category-title"><?php echo htmlspecialchars($category['name']); ?></span>
                                <p class="count-course">
                                    <?php echo $category['product_count']; ?> sản phẩm
                                </p>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>
                        <a href="?page=categories">
                            <img loading="lazy" decoding="async" src="<?php echo img_url('home/cta-final.png'); ?>" alt="Data nguồn hàng" width="380" height="126"> 
                            <span class="category-title">Đang cập nhật danh mục...</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</section>

<!-- Latest Products Section -->
<section class="new-release-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Sản phẩm <span class="highlight">Mới nhất</span></h2>
            <a href="?page=products" class="see-more-btn">
                Xem thêm
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3.33333 8H12.6667M12.6667 8L8 3.33333M12.6667 8L8 12.6667" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
        </div>
        
        <div class="courses-slider-wrapper">
            <div class="courses-slider">
                <div class="slider-nav slider-nav-prev" title="Previous">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12.5 15L7.5 10L12.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="slider-nav slider-nav-next" title="Next">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M7.5 15L12.5 10L7.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                
                <div class="courses-container">
                    <div class="courses-grid">
                        <?php if (!empty($latestProducts)): ?>
                            <?php foreach ($latestProducts as $product): ?>
                                <div class="course-item">
                                    <div class="course-category">
                                        <a href="?page=categories&id=<?php echo $product['category_id'] ?? ''; ?>" class="category-tag">
                                            <?php echo $product['category_name'] ?: 'Sản phẩm'; ?>
                                        </a>
                                    </div>
                                    <div class="course-image">
                                        <a href="?page=details&id=<?php echo $product['id']; ?>">
                                            <img src="<?php echo getProductImage($product); ?>" 
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>" loading="lazy">
                                        </a>
                                    </div>
                                    <div class="course-content">
                                        <h4 class="course-title">
                                            <a href="?page=details&id=<?php echo $product['id']; ?>">
                                                <?php echo htmlspecialchars($product['name']); ?>
                                            </a>
                                        </h4>
                                        <div class="course-excerpt">
                                            <?php echo htmlspecialchars($product['short_description'] ?: 'Sản phẩm mới nhất từ ' . ($product['supplier_name'] ?? 'ThuongLo.com')); ?>
                                        </div>
                                        <div class="course-instructor">
                                            <a href="#" class="instructor-name"><?php echo $product['supplier_name'] ?? 'ThuongLo.com'; ?></a>
                                        </div>
                                        <div class="course-meta">
                                            <div class="course-lessons">
                                                <!-- Database icon for logistics data -->
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <ellipse cx="12" cy="6" rx="9" ry="3" stroke="#356DF1" stroke-width="2"/>
                                                    <path d="M3 6V18C3 19.6569 7.02944 21 12 21C16.9706 21 21 19.6569 21 18V6" stroke="#356DF1" stroke-width="2"/>
                                                    <path d="M3 12V18C3 19.6569 7.02944 21 12 21C16.9706 21 21 19.6569 21 18V12" stroke="#356DF1" stroke-width="2"/>
                                                    <path d="M21 6V18" stroke="#356DF1" stroke-width="2"/>
                                                    <ellipse cx="12" cy="12" rx="9" ry="3" stroke="#356DF1" stroke-width="2"/>
                                                </svg>
                                                <!-- Display record count for logistics data -->
                                                <span><?php echo formatRecordCount($product['record_count'] ?? $product['in_stock'] ?? 0); ?></span>
                                            </div>
                                        </div>
                                        <div class="course-price">
                                            <?php if (!empty($product['sale_price'])): ?>
                                                <span class="price"><?php echo $product['formatted_sale_price']; ?></span>
                                                <span class="old-price"><?php echo $product['formatted_price']; ?></span>
                                            <?php else: ?>
                                                <span class="price"><?php echo $product['formatted_price']; ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="course-button">
                                            <a href="?page=details&id=<?php echo $product['id']; ?>" class="btn-start-learning">
                                                <i class="fas fa-database"></i>
                                                <span>Xem chi tiết</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <p>Chưa có sản phẩm mới nào. Vui lòng quay lại sau.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose ThuongLo -->
<section class="mission-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Tại sao chọn <span class="highlight">ThuongLo?</span></h2>
        </div>
        
        <div class="mission-grid">
            <div class="mission-item">
                <div class="mission-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="39" height="39" viewBox="0 0 39 39"><path d="M38.4,17.1l-1.2-1.1c-0.4-0.5-0.7-1.5-0.5-2.2l0.4-1.6c0.2-0.9,0.1-1.8-0.3-2.5c-0.5-0.8-1.2-1.3-2-1.6L33.2,7.7 c-0.6-0.2-1.4-0.9-1.6-1.6l-0.4-1.6c-0.5-1.7-2.4-2.8-4.1-2.4L25.5,2.5c-0.7,0.3-1.8,0-2.3-0.4l-1.2-1.1c-1.3-1.3-3.5-1.3-4.8,0 L16,2.1c-0.3,0.4-1.4,0.7-2,0.6l-1.6-0.4c-1.8-0.5-3.7,0.6-4.1,2.3l-0.4,1.6c-0.2,0.6-1,1.4-1.6,1.5l-1.6,0.5 c-0.9,0.2-1.6,0.8-2,1.6c-0.5,0.8-0.6,1.7-0.3,2.5L2.7,13.8c0.2,0.7-0.1,1.7-0.6,2.2l-1.1,1.1c-0.6,0.6-1,1.5-1,2.4 c0,0.9,0.3,1.8,1,2.4l1.1,1.1c0.5,0.5,0.8,1.5,0.6,2.1l-0.4,1.6c-0.2,0.9-0.1,1.8,0.3,2.5c0.5,0.8,1.2,1.3,2,1.6l1.6,0.4 c0.6,0.2,1.4,0.9,1.6,1.6l0.4,1.6c0.5,1.7,2.4,2.8,4.1,2.4l1.6-0.4c0.6-0.2,1.7,0.1,2.2,0.6l1.2,1.1c0.6,0.6,1.5,1,2.4,1 s1.8-0.3,2.4-1l1.2-1.1c0.5-0.4,1.6-0.7,2.2-0.6l1.6,0.4c0.3,0.1,0.6,0.1,0.9,0.1c1.5,0,2.9-1,3.3-2.5l0.4-1.6 c0.2-0.6,1-1.4,1.6-1.6l1.6-0.4c1.8-0.5,2.9-2.3,2.4-4.1l-0.6-1.6c-0.2-0.6,0.1-1.7,0.6-2.2l1.2-1.1C39.7,20.5,39.7,18.4,38.4,17.1z" fill="#356DF1"></path><path d="M28.5,15.2L18,25.6c-0.3,0.3-0.6,0.4-0.9,0.4s-0.7-0.1-0.9-0.4l-5.3-5.2c-0.5-0.5-0.5-1.3,0-1.8s1.3-0.5,1.9,0l4.3,4.3 l9.6-9.5c0.5-0.5,1.3-0.5,1.9,0C29.1,13.9,28.9,14.7,28.5,15.2z" fill="#FFFFFF"></path></svg>
                </div>
                <h3 class="mission-title">Kinh nghiệm dày dặn</h3>
                <p class="mission-description">Hơn 10 năm kinh nghiệm trong lĩnh vực thương mại xuyên biên giới, hiểu rõ thị trường và quy trình</p>
            </div>
            <div class="mission-item">
                <div class="mission-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="39" height="39" viewBox="0 0 39 39"><path d="M38.4,17.1l-1.2-1.1c-0.4-0.5-0.7-1.5-0.5-2.2l0.4-1.6c0.2-0.9,0.1-1.8-0.3-2.5c-0.5-0.8-1.2-1.3-2-1.6L33.2,7.7 c-0.6-0.2-1.4-0.9-1.6-1.6l-0.4-1.6c-0.5-1.7-2.4-2.8-4.1-2.4L25.5,2.5c-0.7,0.3-1.8,0-2.3-0.4l-1.2-1.1c-1.3-1.3-3.5-1.3-4.8,0 L16,2.1c-0.3,0.4-1.4,0.7-2,0.6l-1.6-0.4c-1.8-0.5-3.7,0.6-4.1,2.3l-0.4,1.6c-0.2,0.6-1,1.4-1.6,1.5l-1.6,0.5 c-0.9,0.2-1.6,0.8-2,1.6c-0.5,0.8-0.6,1.7-0.3,2.5L2.7,13.8c0.2,0.7-0.1,1.7-0.6,2.2l-1.1,1.1c-0.6,0.6-1,1.5-1,2.4 c0,0.9,0.3,1.8,1,2.4l1.1,1.1c0.5,0.5,0.8,1.5,0.6,2.1l-0.4,1.6c-0.2,0.9-0.1,1.8,0.3,2.5c0.5,0.8,1.2,1.3,2,1.6l1.6,0.4 c0.6,0.2,1.4,0.9,1.6,1.6l0.4,1.6c0.5,1.7,2.4,2.8,4.1,2.4l1.6-0.4c0.6-0.2,1.7,0.1,2.2,0.6l1.2,1.1c0.6,0.6,1.5,1,2.4,1 s1.8-0.3,2.4-1l1.2-1.1c0.5-0.4,1.6-0.7,2.2-0.6l1.6,0.4c0.3,0.1,0.6,0.1,0.9,0.1c1.5,0,2.9-1,3.3-2.5l0.4-1.6 c0.2-0.6,1-1.4,1.6-1.6l1.6-0.4c1.8-0.5,2.9-2.3,2.4-4.1l-0.6-1.6c-0.2-0.6,0.1-1.7,0.6-2.2l1.2-1.1C39.7,20.5,39.7,18.4,38.4,17.1z" fill="#356DF1"></path><path d="M28.5,15.2L18,25.6c-0.3,0.3-0.6,0.4-0.9,0.4s-0.7-0.1-0.9-0.4l-5.3-5.2c-0.5-0.5-0.5-1.3,0-1.8s1.3-0.5,1.9,0l4.3,4.3 l9.6-9.5c0.5-0.5,1.3-0.5,1.9,0C29.1,13.9,28.9,14.7,28.5,15.2z" fill="#FFFFFF"></path></svg>
                </div>
                <h3 class="mission-title">Dịch vụ toàn diện</h3>
                <p class="mission-description">Cung cấp giải pháp từ A-Z cho thương mại xuyên biên giới, từ tìm nguồn hàng đến vận chuyển</p>
            </div>
            <div class="mission-item">
                <div class="mission-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="39" height="39" viewBox="0 0 39 39"><path d="M38.4,17.1l-1.2-1.1c-0.4-0.5-0.7-1.5-0.5-2.2l0.4-1.6c0.2-0.9,0.1-1.8-0.3-2.5c-0.5-0.8-1.2-1.3-2-1.6L33.2,7.7 c-0.6-0.2-1.4-0.9-1.6-1.6l-0.4-1.6c-0.5-1.7-2.4-2.8-4.1-2.4L25.5,2.5c-0.7,0.3-1.8,0-2.3-0.4l-1.2-1.1c-1.3-1.3-3.5-1.3-4.8,0 L16,2.1c-0.3,0.4-1.4,0.7-2,0.6l-1.6-0.4c-1.8-0.5-3.7,0.6-4.1,2.3l-0.4,1.6c-0.2,0.6-1,1.4-1.6,1.5l-1.6,0.5 c-0.9,0.2-1.6,0.8-2,1.6c-0.5,0.8-0.6,1.7-0.3,2.5L2.7,13.8c0.2,0.7-0.1,1.7-0.6,2.2l-1.1,1.1c-0.6,0.6-1,1.5-1,2.4 c0,0.9,0.3,1.8,1,2.4l1.1,1.1c0.5,0.5,0.8,1.5,0.6,2.1l-0.4,1.6c-0.2,0.9-0.1,1.8,0.3,2.5c0.5,0.8,1.2,1.3,2,1.6l1.6,0.4 c0.6,0.2,1.4,0.9,1.6,1.6l0.4,1.6c0.5,1.7,2.4,2.8,4.1,2.4l1.6-0.4c0.6-0.2,1.7,0.1,2.2,0.6l1.2,1.1c0.6,0.6,1.5,1,2.4,1 s1.8-0.3,2.4-1l1.2-1.1c0.5-0.4,1.6-0.7,2.2-0.6l1.6,0.4c0.3,0.1,0.6,0.1,0.9,0.1c1.5,0,2.9-1,3.3-2.5l0.4-1.6 c0.2-0.6,1-1.4,1.6-1.6l1.6-0.4c1.8-0.5,2.9-2.3,2.4-4.1l-0.6-1.6c-0.2-0.6,0.1-1.7,0.6-2.2l1.2-1.1C39.7,20.5,39.7,18.4,38.4,17.1z" fill="#356DF1"></path><path d="M28.5,15.2L18,25.6c-0.3,0.3-0.6,0.4-0.9,0.4s-0.7-0.1-0.9-0.4l-5.3-5.2c-0.5-0.5-0.5-1.3,0-1.8s1.3-0.5,1.9,0l4.3,4.3 l9.6-9.5c0.5-0.5,1.3-0.5,1.9,0C29.1,13.9,28.9,14.7,28.5,15.2z" fill="#FFFFFF"></path></svg>
                </div>
                <h3 class="mission-title">Hỗ trợ 24/7</h3>
                <p class="mission-description">Đội ngũ hỗ trợ chuyên nghiệp sẵn sàng giải đáp mọi thắc mắc và hỗ trợ khách hàng mọi lúc</p>
            </div>
            <div class="mission-item">
                <div class="mission-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="39" height="39" viewBox="0 0 39 39"><path d="M38.4,17.1l-1.2-1.1c-0.4-0.5-0.7-1.5-0.5-2.2l0.4-1.6c0.2-0.9,0.1-1.8-0.3-2.5c-0.5-0.8-1.2-1.3-2-1.6L33.2,7.7 c-0.6-0.2-1.4-0.9-1.6-1.6l-0.4-1.6c-0.5-1.7-2.4-2.8-4.1-2.4L25.5,2.5c-0.7,0.3-1.8,0-2.3-0.4l-1.2-1.1c-1.3-1.3-3.5-1.3-4.8,0 L16,2.1c-0.3,0.4-1.4,0.7-2,0.6l-1.6-0.4c-1.8-0.5-3.7,0.6-4.1,2.3l-0.4,1.6c-0.2,0.6-1,1.4-1.6,1.5l-1.6,0.5 c-0.9,0.2-1.6,0.8-2,1.6c-0.5,0.8-0.6,1.7-0.3,2.5L2.7,13.8c0.2,0.7-0.1,1.7-0.6,2.2l-1.1,1.1c-0.6,0.6-1,1.5-1,2.4 c0,0.9,0.3,1.8,1,2.4l1.1,1.1c0.5,0.5,0.8,1.5,0.6,2.1l-0.4,1.6c-0.2,0.9-0.1,1.8,0.3,2.5c0.5,0.8,1.2,1.3,2,1.6l1.6,0.4 c0.6,0.2,1.4,0.9,1.6,1.6l0.4,1.6c0.5,1.7,2.4,2.8,4.1,2.4l1.6-0.4c0.6-0.2,1.7,0.1,2.2,0.6l1.2,1.1c0.6,0.6,1.5,1,2.4,1 s1.8-0.3,2.4-1l1.2-1.1c0.5-0.4,1.6-0.7,2.2-0.6l1.6,0.4c0.3,0.1,0.6,0.1,0.9,0.1c1.5,0,2.9-1,3.3-2.5l0.4-1.6 c0.2-0.6,1-1.4,1.6-1.6l1.6-0.4c1.8-0.5,2.9-2.3,2.4-4.1l-0.6-1.6c-0.2-0.6,0.1-1.7,0.6-2.2l1.2-1.1C39.7,20.5,39.7,18.4,38.4,17.1z" fill="#356DF1"></path><path d="M28.5,15.2L18,25.6c-0.3,0.3-0.6,0.4-0.9,0.4s-0.7-0.1-0.9-0.4l-5.3-5.2c-0.5-0.5-0.5-1.3,0-1.8s1.3-0.5,1.9,0l4.3,4.3 l9.6-9.5c0.5-0.5,1.3-0.5,1.9,0C29.1,13.9,28.9,14.7,28.5,15.2z" fill="#FFFFFF"></path></svg>
                </div>
                <h3 class="mission-title">Giá cả cạnh tranh</h3>
                <p class="mission-description">Cam kết mang đến giá cả tốt nhất thị trường với chất lượng dịch vụ cao nhất</p>
            </div>
        </div>
    </div>
</section>