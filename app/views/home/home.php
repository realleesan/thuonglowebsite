<?php
/**
 * Home Page - Dynamic Version
 * Converted from hardcoded data to dynamic database data
 */

// Load required services and models
require_once __DIR__ . '/../../services/ViewDataService.php';
require_once __DIR__ . '/../../services/ErrorHandler.php';

// Initialize services
$viewDataService = new ViewDataService();
$errorHandler = new ErrorHandler();

// Initialize data variables with empty states
$homeData = [];
$featuredProducts = [];
$latestProducts = [];
$featuredCategories = [];
$latestNews = [];
$showErrorMessage = false;
$errorMessage = '';

try {
    // Get home page data
    $homeData = $viewDataService->getHomePageData();
    
    // Extract data arrays
    $featuredProducts = $homeData['featured_products'] ?? [];
    $latestProducts = $homeData['latest_products'] ?? [];
    $featuredCategories = $homeData['featured_categories'] ?? [];
    $latestNews = $homeData['latest_news'] ?? [];
    
} catch (Exception $e) {
    // Handle errors gracefully
    $result = $errorHandler->handleViewError($e, 'home', []);
    $showErrorMessage = true;
    $errorMessage = $result['message'];
    
    // Use empty state data
    $emptyState = $viewDataService->handleEmptyState('home');
    $featuredProducts = $emptyState['featured_products'];
    $latestProducts = $emptyState['latest_products'];
    $featuredCategories = $emptyState['featured_categories'];
    $latestNews = $emptyState['latest_news'];
}

// Helper function to safely get image URL
function getProductImage($product) {
    if (!empty($product['image']) && $product['image'] !== '/assets/images/default-product.jpg') {
        return $product['image'];
    }
    return img_url('home/home-banner-top.png'); // Fallback to existing image
}

// Helper function to safely get category image
function getCategoryImage($category) {
    if (!empty($category['image']) && $category['image'] !== '/assets/images/default-category.jpg') {
        return $category['image'];
    }
    return img_url('home/cta-final.png'); // Fallback to existing image
}

// Ensure img_url function exists
if (!function_exists('img_url')) {
    function img_url($path) {
        return '/assets/images/' . $path;
    }
}
?>

<!-- Home Page Content -->
<?php if ($showErrorMessage): ?>
<div class="error-message" style="background: #f8d7da; color: #721c24; padding: 15px; margin: 20px 0; border-radius: 5px;">
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
                    <!-- Products Grid -->
                    <div class="courses-grid">
                        <?php if (!empty($featuredProducts)): ?>
                            <?php foreach ($featuredProducts as $product): ?>
                                <!-- Featured Product Item -->
                                <div class="course-item">
                                    <div class="course-category">
                                        <a href="?page=categories&id=<?php echo $product['category_id'] ?? ''; ?>" class="category-tag">
                                            <?php echo $product['category_name'] ?: 'Sản phẩm'; ?>
                                        </a>
                                    </div>
                                    <div class="course-image">
                                        <a href="?page=details&id=<?php echo $product['id']; ?>">
                                            <img src="<?php echo getProductImage($product); ?>" 
                                                 alt="<?php echo $product['name']; ?>" loading="lazy">
                                        </a>
                                    </div>
                                    <div class="course-content">
                                        <h4 class="course-title">
                                            <a href="?page=details&id=<?php echo $product['id']; ?>">
                                                <?php echo $product['name']; ?>
                                            </a>
                                        </h4>
                                        <div class="course-excerpt">
                                            <?php echo $product['short_description'] ?: 'Sản phẩm chất lượng cao từ ThuongLo.com'; ?>
                                        </div>
                                        <div class="course-instructor">
                                            <a href="#" class="instructor-name">ThuongLo.com</a>
                                        </div>
                                        <div class="course-meta">
                                            <div class="course-lessons">
                                                <svg width="16" height="17" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M5.33333 6.49992H8M5.33333 9.16659H10.6667M5.33333 11.8333H10.6667M10.6663 1.83325V3.83325M5.33301 1.83325V3.83325M4.66667 2.83325H11.3333C12.8061 2.83325 14 4.02716 14 5.49992V12.4999C14 13.9727 12.8061 15.1666 11.3333 15.1666H4.66667C3.19391 15.1666 2 13.9727 2 12.4999V5.49992C2 4.02716 3.19391 2.83325 4.66667 2.83325Z" stroke="#444444" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                <span><?php echo $product['in_stock'] ? 'Còn hàng' : 'Hết hàng'; ?></span>
                                            </div>
                                        </div>
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
                                        <div class="course-button">
                                            <a href="?page=details&id=<?php echo $product['id']; ?>" class="btn-start-learning">
                                                <i class="fas fa-play"></i>
                                                <span>Xem chi tiết</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Empty State -->
                            <div class="empty-state">
                                <p>Chưa có sản phẩm nổi bật nào. Vui lòng quay lại sau.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Slider Pagination -->
                    <div class="slider-pagination">
                        <?php 
                        $productCount = count($featuredProducts);
                        $maxBullets = min(5, max(1, ceil($productCount / 4))); // Assuming 4 products per slide
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
                                     alt="<?php echo $category['name']; ?>" 
                                     width="380" height="126"> 
                                <span class="category-title"><?php echo $category['name']; ?></span>
                                <p class="count-course">
                                    <?php echo $category['product_count']; ?> sản phẩm
                                </p>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Fallback categories if no dynamic data -->
                    <li>
                        <a href="?page=categories">
                            <img loading="lazy" decoding="async" src="<?php echo img_url('home/cta-final.png'); ?>" alt="Data nguồn hàng" width="380" height="126"> 
                            <span class="category-title">Data nguồn hàng</span>
                            <p class="count-course">1000+ Nhà cung cấp</p>
                        </a>
                    </li>
                    <li>
                        <a href="?page=categories">
                            <img loading="lazy" decoding="async" src="<?php echo img_url('home/cta-final.png'); ?>" alt="Vận chuyển" width="380" height="126"> 
                            <span class="category-title">Vận chuyển chính ngạch</span>
                            <p class="count-course">An toàn - Nhanh chóng</p>
                        </a>
                    </li>
                    <li>
                        <a href="?page=categories">
                            <img loading="lazy" decoding="async" src="<?php echo img_url('home/cta-final.png'); ?>" alt="Mua hàng" width="380" height="126"> 
                            <span class="category-title">Mua hàng trọn gói</span>
                            <p class="count-course">Từ A đến Z</p>
                        </a>
                    </li>
                    <li>
                        <a href="?page=categories">
                            <img loading="lazy" decoding="async" src="<?php echo img_url('home/cta-final.png'); ?>" alt="Thanh toán" width="380" height="126"> 
                            <span class="category-title">Thanh toán quốc tế</span>
                            <p class="count-course">Tỷ giá ưu đãi</p>
                        </a>
                    </li>
                    <li>
                        <a href="?page=categories">
                            <img loading="lazy" decoding="async" src="<?php echo img_url('home/cta-final.png'); ?>" alt="Đánh hàng" width="380" height="126"> 
                            <span class="category-title">Dịch vụ đánh hàng</span>
                            <p class="count-course">Chuyên nghiệp</p>
                        </a>
                    </li>
                    <li>
                        <a href="?page=categories">
                            <img loading="lazy" decoding="async" src="<?php echo img_url('home/cta-final.png'); ?>" alt="Phiên dịch" width="380" height="126"> 
                            <span class="category-title">Phiên dịch</span>
                            <p class="count-course">Trung - Việt</p>
                        </a>
                    </li>
                    <li>
                        <a href="?page=categories">
                            <img loading="lazy" decoding="async" src="<?php echo img_url('home/cta-final.png'); ?>" alt="Hỗ trợ" width="380" height="126"> 
                            <span class="category-title">Hỗ trợ đi lại</span>
                            <p class="count-course">Ăn ở - Di chuyển</p>
                        </a>
                    </li>
                    <li>
                        <a href="?page=categories">
                            <img loading="lazy" decoding="async" src="<?php echo img_url('home/cta-final.png'); ?>" alt="Tư vấn" width="380" height="126"> 
                            <span class="category-title">Tư vấn kinh doanh</span>
                            <p class="count-course">Chuyên gia</p>
                        </a>
                    </li>
                    <li>
                        <a href="?page=categories">
                            <img loading="lazy" decoding="async" src="<?php echo img_url('home/cta-final.png'); ?>" alt="Khác" width="380" height="126"> 
                            <span class="category-title">Sản phẩm khác</span>
                            <p class="count-course">Linh hoạt</p>
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
                    <!-- Latest Products Grid -->
                    <div class="courses-grid">
                        <?php if (!empty($latestProducts)): ?>
                            <?php foreach ($latestProducts as $product): ?>
                                <!-- Latest Product Item -->
                                <div class="course-item">
                                    <div class="course-category">
                                        <a href="?page=categories&id=<?php echo $product['category_id'] ?? ''; ?>" class="category-tag">
                                            <?php echo $product['category_name'] ?: 'Sản phẩm'; ?>
                                        </a>
                                    </div>
                                    <div class="course-image">
                                        <a href="?page=details&id=<?php echo $product['id']; ?>">
                                            <img src="<?php echo getProductImage($product); ?>" 
                                                 alt="<?php echo $product['name']; ?>" loading="lazy">
                                        </a>
                                    </div>
                                    <div class="course-content">
                                        <h4 class="course-title">
                                            <a href="?page=details&id=<?php echo $product['id']; ?>">
                                                <?php echo $product['name']; ?>
                                            </a>
                                        </h4>
                                        <div class="course-excerpt">
                                            <?php echo $product['short_description'] ?: 'Sản phẩm mới nhất từ ThuongLo.com'; ?>
                                        </div>
                                        <div class="course-instructor">
                                            <a href="#" class="instructor-name">ThuongLo.com</a>
                                        </div>
                                        <div class="course-meta">
                                            <div class="course-lessons">
                                                <svg width="16" height="17" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M5.33333 6.49992H8M5.33333 9.16659H10.6667M5.33333 11.8333H10.6667M10.6663 1.83325V3.83325M5.33301 1.83325V3.83325M4.66667 2.83325H11.3333C12.8061 2.83325 14 4.02716 14 5.49992V12.4999C14 13.9727 12.8061 15.1666 11.3333 15.1666H4.66667C3.19391 15.1666 2 13.9727 2 12.4999V5.49992C2 4.02716 3.19391 2.83325 4.66667 2.83325Z" stroke="#444444" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                <span><?php echo $product['in_stock'] ? 'Còn hàng' : 'Hết hàng'; ?></span>
                                            </div>
                                        </div>
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
                                        <div class="course-button">
                                            <a href="?page=details&id=<?php echo $product['id']; ?>" class="btn-start-learning">
                                                <i class="fas fa-play"></i>
                                                <span>Xem chi tiết</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Empty State -->
                            <div class="empty-state">
                                <p>Chưa có sản phẩm mới nào. Vui lòng quay lại sau.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Slider Pagination -->
                    <div class="slider-pagination">
                        <?php 
                        $latestCount = count($latestProducts);
                        $maxBullets = min(5, max(1, ceil($latestCount / 4))); // Assuming 4 products per slide
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
<!-- Why Choose ThuongLo -->
<section class="mission-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Tại sao chọn <span class="highlight">ThuongLo?</span></h2>
        </div>
        
        <div class="mission-grid">
            <!-- Row 1 -->
            <div class="mission-item">
                <div class="mission-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="39.3878701px" height="39.0749985px" viewBox="0 0 39.3878701 39.0749985">
                        <g stroke-width="1" fill="none" fill-rule="evenodd">
                            <path d="M38.399995,17.0749985 L37.199995,15.9749985 C36.799995,15.4749985 36.5,14.4749985 36.699995,13.7749985 L37.099995,12.1749985 C37.299995,11.2749985 37.199995,10.3749985 36.799995,9.6749985 C36.299995,8.8749985 35.599995,8.3749985 34.799995,8.0749985 L33.199995,7.6749985 C32.599995,7.4749985 31.799995,6.7749985 31.599995,6.0749985 L31.199995,4.4749985 C30.699995,2.7749985 28.799995,1.6749985 27.099995,2.0749985 L25.5,2.4749985 C24.799995,2.7749985 23.699995,2.4749985 23.199995,2.0749985 L22,0.9749985 C20.699995,-0.3249995 18.5,-0.3249995 17.199995,0.9749985 L16,2.0749985 C15.699995,2.4749985 14.599995,2.7749985 14,2.6749985 L12.399995,2.2749985 C10.599995,1.7749985 8.699995,2.8749985 8.299995,4.5749985 L7.899995,6.1749985 C7.699995,6.7749985 6.899995,7.5749985 6.299995,7.6749985 L4.699995,8.1749985 C3.799995,8.3749985 3.099995,8.9749985 2.699995,9.7749985 C2.199995,10.5749985 2.099995,11.4749985 2.399995,12.2749985 L2.699995,13.7749985 C2.899995,14.4749985 2.599995,15.4749985 2.099995,15.9749985 L1,17.0749985 C0.4,17.6749985 0,18.5749985 0,19.4749985 C0,20.3749985 0.3,21.1749985 1,21.8749985 L2.099995,22.9749985 C2.599995,23.4749985 2.899995,24.4749985 2.699995,25.0749985 L2.299995,26.6749985 C2.099995,27.5749985 2.199995,28.4749985 2.599995,29.1749985 C3.099995,29.9749985 3.799995,30.4749985 4.599995,30.7749985 L6.199995,31.1749985 C6.799995,31.3749985 7.600005,32.0749985 7.799995,32.7749985 L8.199995,34.3749985 C8.699995,36.0749985 10.599995,37.1749985 12.299995,36.7749985 L13.899995,36.3749985 C14.5,36.1749985 15.599995,36.4749985 16.099995,36.9749985 L17.299995,38.0749985 C17.899995,38.6749985 18.799995,39.0749985 19.699995,39.0749985 C20.599995,39.0749985 21.5,38.7749985 22.099995,38.0749985 L23.299995,36.9749985 C23.799995,36.5749985 24.899995,36.2749985 25.5,36.3749985 L27.099995,36.7749985 C27.399995,36.8749985 27.699995,36.8749985 28,36.8749985 C29.5,36.8749985 30.899995,35.8749985 31.299995,34.3749985 L31.699995,32.7749985 C31.899995,32.1749985 32.699995,31.3749985 33.299995,31.1749985 L34.899995,30.7749985 C36.699995,30.2749985 37.799995,28.4749985 37.299995,26.6749985 L36.699995,25.0749985 C36.5,24.4749985 36.799995,23.3749985 37.299995,22.8749985 L38.5,21.7749985 C39.699995,20.4749985 39.699995,18.3749985 38.399995,17.0749985 Z" fill="#356DF1"></path>
                            <path class="nochange" d="M28.499895,15.1749985 L17.999895,25.5749985 C17.699895,25.8749985 17.399895,25.9749985 17.099895,25.9749985 C16.799895,25.9749985 16.399895,25.8749985 16.199895,25.5749985 L10.899895,20.3749985 C10.399895,19.8749985 10.399895,19.0749985 10.899895,18.5749985 C11.399895,18.0749985 12.199895,18.0749985 12.799895,18.5749985 L17.099895,22.8749985 L26.699895,13.3749985 C27.199895,12.8749985 27.999895,12.8749985 28.599895,13.3749985 C29.199895,13.8749985 28.999895,14.6749985 28.499895,15.1749985 Z" fill="#FFFFFF"></path>
                        </g>
                    </svg>
                </div>
                <h3 class="mission-title">Kinh nghiệm dày dặn</h3>
                <p class="mission-description">Hơn 10 năm kinh nghiệm trong lĩnh vực thương mại xuyên biên giới, hiểu rõ thị trường và quy trình</p>
            </div>
            
            <div class="mission-item">
                <div class="mission-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="39.3878701px" height="39.0749985px" viewBox="0 0 39.3878701 39.0749985">
                        <g stroke-width="1" fill="none" fill-rule="evenodd">
                            <path d="M38.399995,17.0749985 L37.199995,15.9749985 C36.799995,15.4749985 36.5,14.4749985 36.699995,13.7749985 L37.099995,12.1749985 C37.299995,11.2749985 37.199995,10.3749985 36.799995,9.6749985 C36.299995,8.8749985 35.599995,8.3749985 34.799995,8.0749985 L33.199995,7.6749985 C32.599995,7.4749985 31.799995,6.7749985 31.599995,6.0749985 L31.199995,4.4749985 C30.699995,2.7749985 28.799995,1.6749985 27.099995,2.0749985 L25.5,2.4749985 C24.799995,2.7749985 23.699995,2.4749985 23.199995,2.0749985 L22,0.9749985 C20.699995,-0.3249995 18.5,-0.3249995 17.199995,0.9749985 L16,2.0749985 C15.699995,2.4749985 14.599995,2.7749985 14,2.6749985 L12.399995,2.2749985 C10.599995,1.7749985 8.699995,2.8749985 8.299995,4.5749985 L7.899995,6.1749985 C7.699995,6.7749985 6.899995,7.5749985 6.299995,7.6749985 L4.699995,8.1749985 C3.799995,8.3749985 3.099995,8.9749985 2.699995,9.7749985 C2.199995,10.5749985 2.099995,11.4749985 2.399995,12.2749985 L2.699995,13.7749985 C2.899995,14.4749985 2.599995,15.4749985 2.099995,15.9749985 L1,17.0749985 C0.4,17.6749985 0,18.5749985 0,19.4749985 C0,20.3749985 0.3,21.1749985 1,21.8749985 L2.099995,22.9749985 C2.599995,23.4749985 2.899995,24.4749985 2.699995,25.0749985 L2.299995,26.6749985 C2.099995,27.5749985 2.199995,28.4749985 2.599995,29.1749985 C3.099995,29.9749985 3.799995,30.4749985 4.599995,30.7749985 L6.199995,31.1749985 C6.799995,31.3749985 7.600005,32.0749985 7.799995,32.7749985 L8.199995,34.3749985 C8.699995,36.0749985 10.599995,37.1749985 12.299995,36.7749985 L13.899995,36.3749985 C14.5,36.1749985 15.599995,36.4749985 16.099995,36.9749985 L17.299995,38.0749985 C17.899995,38.6749985 18.799995,39.0749985 19.699995,39.0749985 C20.599995,39.0749985 21.5,38.7749985 22.099995,38.0749985 L23.299995,36.9749985 C23.799995,36.5749985 24.899995,36.2749985 25.5,36.3749985 L27.099995,36.7749985 C27.399995,36.8749985 27.699995,36.8749985 28,36.8749985 C29.5,36.8749985 30.899995,35.8749985 31.299995,34.3749985 L31.699995,32.7749985 C31.899995,32.1749985 32.699995,31.3749985 33.299995,31.1749985 L34.899995,30.7749985 C36.699995,30.2749985 37.799995,28.4749985 37.299995,26.6749985 L36.699995,25.0749985 C36.5,24.4749985 36.799995,23.3749985 37.299995,22.8749985 L38.5,21.7749985 C39.699995,20.4749985 39.699995,18.3749985 38.399995,17.0749985 Z" fill="#356DF1"></path>
                            <path class="nochange" d="M28.499895,15.1749985 L17.999895,25.5749985 C17.699895,25.8749985 17.399895,25.9749985 17.099895,25.9749985 C16.799895,25.9749985 16.399895,25.8749985 16.199895,25.5749985 L10.899895,20.3749985 C10.399895,19.8749985 10.399895,19.0749985 10.899895,18.5749985 C11.399895,18.0749985 12.199895,18.0749985 12.799895,18.5749985 L17.099895,22.8749985 L26.699895,13.3749985 C27.199895,12.8749985 27.999895,12.8749985 28.599895,13.3749985 C29.199895,13.8749985 28.999895,14.6749985 28.499895,15.1749985 Z" fill="#FFFFFF"></path>
                        </g>
                    </svg>
                </div>
                <h3 class="mission-title">Dịch vụ toàn diện</h3>
                <p class="mission-description">Cung cấp giải pháp từ A-Z cho thương mại xuyên biên giới, từ tìm nguồn hàng đến vận chuyển</p>
            </div>
            
            <div class="mission-item">
                <div class="mission-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="39.3878701px" height="39.0749985px" viewBox="0 0 39.3878701 39.0749985">
                        <g stroke-width="1" fill="none" fill-rule="evenodd">
                            <path d="M38.399995,17.0749985 L37.199995,15.9749985 C36.799995,15.4749985 36.5,14.4749985 36.699995,13.7749985 L37.099995,12.1749985 C37.299995,11.2749985 37.199995,10.3749985 36.799995,9.6749985 C36.299995,8.8749985 35.599995,8.3749985 34.799995,8.0749985 L33.199995,7.6749985 C32.599995,7.4749985 31.799995,6.7749985 31.599995,6.0749985 L31.199995,4.4749985 C30.699995,2.7749985 28.799995,1.6749985 27.099995,2.0749985 L25.5,2.4749985 C24.799995,2.7749985 23.699995,2.4749985 23.199995,2.0749985 L22,0.9749985 C20.699995,-0.3249995 18.5,-0.3249995 17.199995,0.9749985 L16,2.0749985 C15.699995,2.4749985 14.599995,2.7749985 14,2.6749985 L12.399995,2.2749985 C10.599995,1.7749985 8.699995,2.8749985 8.299995,4.5749985 L7.899995,6.1749985 C7.699995,6.7749985 6.899995,7.5749985 6.299995,7.6749985 L4.699995,8.1749985 C3.799995,8.3749985 3.099995,8.9749985 2.699995,9.7749985 C2.199995,10.5749985 2.099995,11.4749985 2.399995,12.2749985 L2.699995,13.7749985 C2.899995,14.4749985 2.599995,15.4749985 2.099995,15.9749985 L1,17.0749985 C0.4,17.6749985 0,18.5749985 0,19.4749985 C0,20.3749985 0.3,21.1749985 1,21.8749985 L2.099995,22.9749985 C2.599995,23.4749985 2.899995,24.4749985 2.699995,25.0749985 L2.299995,26.6749985 C2.099995,27.5749985 2.199995,28.4749985 2.599995,29.1749985 C3.099995,29.9749985 3.799995,30.4749985 4.599995,30.7749985 L6.199995,31.1749985 C6.799995,31.3749985 7.600005,32.0749985 7.799995,32.7749985 L8.199995,34.3749985 C8.699995,36.0749985 10.599995,37.1749985 12.299995,36.7749985 L13.899995,36.3749985 C14.5,36.1749985 15.599995,36.4749985 16.099995,36.9749985 L17.299995,38.0749985 C17.899995,38.6749985 18.799995,39.0749985 19.699995,39.0749985 C20.599995,39.0749985 21.5,38.7749985 22.099995,38.0749985 L23.299995,36.9749985 C23.799995,36.5749985 24.899995,36.2749985 25.5,36.3749985 L27.099995,36.7749985 C27.399995,36.8749985 27.699995,36.8749985 28,36.8749985 C29.5,36.8749985 30.899995,35.8749985 31.299995,34.3749985 L31.699995,32.7749985 C31.899995,32.1749985 32.699995,31.3749985 33.299995,31.1749985 L34.899995,30.7749985 C36.699995,30.2749985 37.799995,28.4749985 37.299995,26.6749985 L36.699995,25.0749985 C36.5,24.4749985 36.799995,23.3749985 37.299995,22.8749985 L38.5,21.7749985 C39.699995,20.4749985 39.699995,18.3749985 38.399995,17.0749985 Z" fill="#356DF1"></path>
                            <path class="nochange" d="M28.499895,15.1749985 L17.999895,25.5749985 C17.699895,25.8749985 17.399895,25.9749985 17.099895,25.9749985 C16.799895,25.9749985 16.399895,25.8749985 16.199895,25.5749985 L10.899895,20.3749985 C10.399895,19.8749985 10.399895,19.0749985 10.899895,18.5749985 C11.399895,18.0749985 12.199895,18.0749985 12.799895,18.5749985 L17.099895,22.8749985 L26.699895,13.3749985 C27.199895,12.8749985 27.999895,12.8749985 28.599895,13.3749985 C29.199895,13.8749985 28.999895,14.6749985 28.499895,15.1749985 Z" fill="#FFFFFF"></path>
                        </g>
                    </svg>
                </div>
                <h3 class="mission-title">Hỗ trợ 24/7</h3>
                <p class="mission-description">Đội ngũ hỗ trợ chuyên nghiệp sẵn sàng giải đáp mọi thắc mắc và hỗ trợ khách hàng mọi lúc</p>
            </div>
            
            <div class="mission-item">
                <div class="mission-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="39.3878701px" height="39.0749985px" viewBox="0 0 39.3878701 39.0749985">
                        <g stroke-width="1" fill="none" fill-rule="evenodd">
                            <path d="M38.399995,17.0749985 L37.199995,15.9749985 C36.799995,15.4749985 36.5,14.4749985 36.699995,13.7749985 L37.099995,12.1749985 C37.299995,11.2749985 37.199995,10.3749985 36.799995,9.6749985 C36.299995,8.8749985 35.599995,8.3749985 34.799995,8.0749985 L33.199995,7.6749985 C32.599995,7.4749985 31.799995,6.7749985 31.599995,6.0749985 L31.199995,4.4749985 C30.699995,2.7749985 28.799995,1.6749985 27.099995,2.0749985 L25.5,2.4749985 C24.799995,2.7749985 23.699995,2.4749985 23.199995,2.0749985 L22,0.9749985 C20.699995,-0.3249995 18.5,-0.3249995 17.199995,0.9749985 L16,2.0749985 C15.699995,2.4749985 14.599995,2.7749985 14,2.6749985 L12.399995,2.2749985 C10.599995,1.7749985 8.699995,2.8749985 8.299995,4.5749985 L7.899995,6.1749985 C7.699995,6.7749985 6.899995,7.5749985 6.299995,7.6749985 L4.699995,8.1749985 C3.799995,8.3749985 3.099995,8.9749985 2.699995,9.7749985 C2.199995,10.5749985 2.099995,11.4749985 2.399995,12.2749985 L2.699995,13.7749985 C2.899995,14.4749985 2.599995,15.4749985 2.099995,15.9749985 L1,17.0749985 C0.4,17.6749985 0,18.5749985 0,19.4749985 C0,20.3749985 0.3,21.1749985 1,21.8749985 L2.099995,22.9749985 C2.599995,23.4749985 2.899995,24.4749985 2.699995,25.0749985 L2.299995,26.6749985 C2.099995,27.5749985 2.199995,28.4749985 2.599995,29.1749985 C3.099995,29.9749985 3.799995,30.4749985 4.599995,30.7749985 L6.199995,31.1749985 C6.799995,31.3749985 7.600005,32.0749985 7.799995,32.7749985 L8.199995,34.3749985 C8.699995,36.0749985 10.599995,37.1749985 12.299995,36.7749985 L13.899995,36.3749985 C14.5,36.1749985 15.599995,36.4749985 16.099995,36.9749985 L17.299995,38.0749985 C17.899995,38.6749985 18.799995,39.0749985 19.699995,39.0749985 C20.599995,39.0749985 21.5,38.7749985 22.099995,38.0749985 L23.299995,36.9749985 C23.799995,36.5749985 24.899995,36.2749985 25.5,36.3749985 L27.099995,36.7749985 C27.399995,36.8749985 27.699995,36.8749985 28,36.8749985 C29.5,36.8749985 30.899995,35.8749985 31.299995,34.3749985 L31.699995,32.7749985 C31.899995,32.1749985 32.699995,31.3749985 33.299995,31.1749985 L34.899995,30.7749985 C36.699995,30.2749985 37.799995,28.4749985 37.299995,26.6749985 L36.699995,25.0749985 C36.5,24.4749985 36.799995,23.3749985 37.299995,22.8749985 L38.5,21.7749985 C39.699995,20.4749985 39.699995,18.3749985 38.399995,17.0749985 Z" fill="#356DF1"></path>
                            <path class="nochange" d="M28.499895,15.1749985 L17.999895,25.5749985 C17.699895,25.8749985 17.399895,25.9749985 17.099895,25.9749985 C16.799895,25.9749985 16.399895,25.8749985 16.199895,25.5749985 L10.899895,20.3749985 C10.399895,19.8749985 10.399895,19.0749985 10.899895,18.5749985 C11.399895,18.0749985 12.199895,18.0749985 12.799895,18.5749985 L17.099895,22.8749985 L26.699895,13.3749985 C27.199895,12.8749985 27.999895,12.8749985 28.599895,13.3749985 C29.199895,13.8749985 28.999895,14.6749985 28.499895,15.1749985 Z" fill="#FFFFFF"></path>
                        </g>
                    </svg>
                </div>
                <h3 class="mission-title">Giá cả cạnh tranh</h3>
                <p class="mission-description">Cam kết mang đến giá cả tốt nhất thị trường với chất lượng dịch vụ cao nhất</p>
            </div>
        </div>
    </div>
</section>

<!-- End Home Page Content -->