<?php
/**
 * Home Page - Dynamic Version
 */

// 1. Khởi tạo View an toàn & ServiceManager
require_once __DIR__ . '/../../../core/view_init.php';

// Load Hero Section Model
require_once __DIR__ . '/../../models/HeroSectionModel.php';

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

// Helper function to get brand image on home page
if (!function_exists('getBrandImage')) {
    function getBrandImage($brand) {
        if (!empty($brand['image'])) {
            return $brand['image'];
        }
        return 'https://eduma.thimpress.com/demo-marketplace/wp-content/uploads/sites/99/2024/10/course-offline-01-675x450.jpg';
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
    $featuredBrands = $homeData['featured_brands'] ?? [];
    $latestNews = $homeData['latest_news'] ?? [];
    
    // Fallback: fetch featured brands directly if service doesn't provide them
    if (empty($featuredBrands) && isset($service) && method_exists($service, 'getFeaturedBrands')) {
        $featuredBrands = $service->getFeaturedBrands(6);
    } elseif (empty($featuredBrands) && $service !== null) {
        // Fallback to direct model access
        try {
            $brandsModel = $service->getModel('BrandsModel') ?? null;
            if ($brandsModel && method_exists($brandsModel, 'getFeatured')) {
                $featuredBrands = $brandsModel->getFeatured(6);
            }
        } catch (Exception $e) {
            $featuredBrands = [];
        }
    }
    
    // Use latest products as fallback if no featured products
    if (empty($featuredProducts)) {
        $featuredProducts = $latestProducts;
    }
    
    // Get Hero Section from database
    $heroSectionModel = new HeroSectionModel();
    $heroSection = $heroSectionModel->getWithButtons();
    
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
<?php if ($heroSection && $heroSection['is_active']): ?>
<section class="hero-section" style="<?php echo 'background-color: ' . htmlspecialchars($heroSection['background_color'] ?? '#ffffff') . ';'; ?>">
    <div class="container">
        <div class="hero-content">
            <div class="hero-left">
                <h1 class="hero-title" style="<?php echo 'color: ' . htmlspecialchars($heroSection['text_color'] ?? '#333333') . '; font-family: ' . htmlspecialchars($heroSection['font_family'] ?? 'Arial, sans-serif') . '; font-size: ' . htmlspecialchars($heroSection['title_font_size'] ?? '48px') . ';'; ?>">
                    <?php echo $heroSection['title_main'] ?? ''; ?>
                </h1>
                <?php if (!empty($heroSection['subtitle'])): ?>
                <div class="hero-subtitle" style="<?php echo 'color: ' . htmlspecialchars($heroSection['text_color'] ?? '#333333') . '; font-family: ' . htmlspecialchars($heroSection['font_family'] ?? 'Arial, sans-serif') . '; font-size: ' . htmlspecialchars($heroSection['subtitle_font_size'] ?? '18px') . ';'; ?>">
                    <p><?php echo $heroSection['subtitle'] ?? ''; ?></p>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($heroSection['buttons'])): ?>
                <div class="hero-buttons">
                    <?php foreach ($heroSection['buttons'] as $button): ?>
                        <?php if ($button['is_active']): ?>
                        <a href="<?php echo htmlspecialchars($button['button_url']); ?>" 
                           class="btn-<?php echo htmlspecialchars($button['button_style'] ?? 'primary'); ?>"
                           <?php 
                           // Only add inline styles for custom colors that differ from defaults
                           // Default values from home.css (desktop)
                           $defaultPrimaryBg = '#356DF1';
                           $defaultPrimaryColor = 'white';
                           $defaultSecondaryBg = 'transparent';
                           $defaultSecondaryColor = '#356DF1';
                           $defaultFontSize = '16px';
                           $defaultFontWeight = '600';
                           $defaultPadding = '16px 32px';
                           $defaultBorderRadius = '12px';
                           $defaultBorder = '2px solid transparent';
                           
                           $style = '';
                           $buttonStyle = $button['button_style'] ?? 'primary';
                           
                           // Custom background color
                           if ($buttonStyle === 'primary') {
                               if ($button['background_color'] && $button['background_color'] !== $defaultPrimaryBg) {
                                   $style .= 'background-color: ' . htmlspecialchars($button['background_color']) . '; ';
                                   $style .= 'border-color: ' . htmlspecialchars($button['background_color']) . '; ';
                               }
                           } else { // secondary
                               if ($button['background_color'] && $button['background_color'] !== $defaultSecondaryBg) {
                                   $style .= 'background-color: ' . htmlspecialchars($button['background_color']) . '; ';
                               }
                               if ($button['text_color'] && $button['text_color'] !== $defaultSecondaryColor) {
                                   $style .= 'border-color: ' . htmlspecialchars($button['text_color']) . '; ';
                               }
                           }
                           
                           // Custom text color
                           if ($button['text_color']) {
                               if ($buttonStyle === 'primary' && $button['text_color'] !== $defaultPrimaryColor) {
                                   $style .= 'color: ' . htmlspecialchars($button['text_color']) . '; ';
                               } elseif ($buttonStyle === 'secondary' && $button['text_color'] !== $defaultSecondaryColor) {
                                   $style .= 'color: ' . htmlspecialchars($button['text_color']) . '; ';
                               }
                           }
                           
                           // Custom font size
                           if ($button['font_size'] && $button['font_size'] !== $defaultFontSize) {
                               $style .= 'font-size: ' . htmlspecialchars($button['font_size']) . '; ';
                           }
                           
                           // Custom padding
                           if ($button['padding'] && $button['padding'] !== $defaultPadding) {
                               $style .= 'padding: ' . htmlspecialchars($button['padding']) . '; ';
                           }
                           
                           // Custom border radius
                           if ($button['border_radius'] && $button['border_radius'] !== $defaultBorderRadius) {
                               $style .= 'border-radius: ' . htmlspecialchars($button['border_radius']) . '; ';
                           }
                           
                           // Add font weight if not default
                           if ($button['font_weight'] && $button['font_weight'] !== $defaultFontWeight) {
                               $style .= 'font-weight: ' . htmlspecialchars($button['font_weight']) . '; ';
                           }
                           
                           if (!empty($style)) {
                               echo 'style="' . trim($style) . '"';
                           }
                           ?>>
                            <?php echo htmlspecialchars($button['button_text']); ?>
                        </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <div class="hero-right">
                <?php if (!empty($heroSection['image_url'])): ?>
                <div class="hero-image">
                    <img fetchpriority="high" decoding="async" width="600" height="600" 
                         src="<?php echo img_url($heroSection['image_url']); ?>" 
                         alt="<?php echo htmlspecialchars($heroSection['image_alt'] ?? 'Hero Section Image'); ?>" 
                         srcset="<?php echo img_url($heroSection['image_url']); ?> 600w, <?php echo img_url($heroSection['image_url']); ?> 360w, <?php echo img_url($heroSection['image_url']); ?> 150w, <?php echo img_url($heroSection['image_url']); ?> 100w"
                         sizes="(max-width: 600px) 100vw, 600px" />
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php else: ?>
<!-- Fallback Hero Section if no active hero section -->
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
<?php endif; ?>

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
                Xem thêm
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
                            <a href="?page=products&category=<?php echo $category['id']; ?>">
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
                            <p class="count-course">
                                Đang cập nhật...
                            </p>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</section>

<!-- Featured Brands Section -->
<section class="featured-brands-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Thương hiệu <span class="highlight">Nổi bật</span></h2>
            <a href="?page=brands" class="see-more-btn">
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
                
                <!-- Brands Container -->
                <div class="courses-container">
                    <div class="courses-grid">
                        <?php if (!empty($featuredBrands)): ?>
                            <?php foreach ($featuredBrands as $brand): ?>
                                <div class="course-item">
                                    <div class="course-image">
                                        <a href="?page=products&brand=<?php echo $brand['id']; ?>">
                                            <img src="<?php echo getBrandImage($brand); ?>" 
                                                 alt="<?php echo htmlspecialchars($brand['name']); ?>" loading="lazy">
                                        </a>
                                    </div>
                                    <div class="course-content">
                                        <h4 class="course-title">
                                            <a href="?page=products&brand=<?php echo $brand['id']; ?>">
                                                <?php echo htmlspecialchars($brand['name']); ?>
                                            </a>
                                        </h4>
                                        <div class="course-excerpt">
                                            <?php echo !empty($brand['description']) ? htmlspecialchars(substr($brand['description'], 0, 80)) . (strlen($brand['description']) > 80 ? '...' : '') : 'Khám phá các sản phẩm từ thương hiệu này'; ?>
                                        </div>
                                        <div class="course-instructor">
                                            <span class="instructor-name"><?php echo ($brand['product_count'] ?? 0); ?> sản phẩm</span>
                                        </div>
                                        <div class="course-button">
                                            <a href="?page=products&brand=<?php echo $brand['id']; ?>" class="btn-start-learning">
                                                <i class="fas fa-eye"></i>
                                                <span>Xem sản phẩm</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <p>Chưa có thương hiệu nổi bật nào. Vui lòng quay lại sau.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="slider-pagination">
                        <?php 
                        $brandCount = count($featuredBrands);
                        $maxBullets = min(5, max(1, ceil($brandCount / 4))); 
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
            <div class="mission-item">
                <div class="mission-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="39" height="39" viewBox="0 0 39 39"><path d="M38.4,17.1l-1.2-1.1c-0.4-0.5-0.7-1.5-0.5-2.2l0.4-1.6c0.2-0.9,0.1-1.8-0.3-2.5c-0.5-0.8-1.2-1.3-2-1.6L33.2,7.7 c-0.6-0.2-1.4-0.9-1.6-1.6l-0.4-1.6c-0.5-1.7-2.4-2.8-4.1-2.4L25.5,2.5c-0.7,0.3-1.8,0-2.3-0.4l-1.2-1.1c-1.3-1.3-3.5-1.3-4.8,0 L16,2.1c-0.3,0.4-1.4,0.7-2,0.6l-1.6-0.4c-1.8-0.5-3.7,0.6-4.1,2.3l-0.4,1.6c-0.2,0.6-1,1.4-1.6,1.5l-1.6,0.5 c-0.9,0.2-1.6,0.8-2,1.6c-0.5,0.8-0.6,1.7-0.3,2.5L2.7,13.8c0.2,0.7-0.1,1.7-0.6,2.2l-1.1,1.1c-0.6,0.6-1,1.5-1,2.4 c0,0.9,0.3,1.8,1,2.4l1.1,1.1c0.5,0.5,0.8,1.5,0.6,2.1l-0.4,1.6c-0.2,0.9-0.1,1.8,0.3,2.5c0.5,0.8,1.2,1.3,2,1.6l1.6,0.4 c0.6,0.2,1.4,0.9,1.6,1.6l0.4,1.6c0.5,1.7,2.4,2.8,4.1,2.4l1.6-0.4c0.6-0.2,1.7,0.1,2.2,0.6l1.2,1.1c0.6,0.6,1.5,1,2.4,1 s1.8-0.3,2.4-1l1.2-1.1c0.5-0.4,1.6-0.7,2.2-0.6l1.6,0.4c0.3,0.1,0.6,0.1,0.9,0.1c1.5,0,2.9-1,3.3-2.5l0.4-1.6 c0.2-0.6,1-1.4,1.6-1.6l1.6-0.4c1.8-0.5,2.9-2.3,2.4-4.1l-0.6-1.6c-0.2-0.6,0.1-1.7,0.6-2.2l1.2-1.1C39.7,20.5,39.7,18.4,38.4,17.1z" fill="#356DF1"></path><path d="M28.5,15.2L18,25.6c-0.3,0.3-0.6,0.4-0.9,0.4s-0.7-0.1-0.9-0.4l-5.3-5.2c-0.5-0.5-0.5-1.3,0-1.8s1.3-0.5,1.9,0l4.3,4.3 l9.6-9.5c0.5-0.5,1.3-0.5,1.9,0C29.1,13.9,28.9,14.7,28.5,15.2z" fill="#FFFFFF"></path></svg>
                </div>
                <h3 class="mission-title">Đội ngũ chuyên nghiệp</h3>
                <p class="mission-description">Đội ngũ nhân viên giàu kinh nghiệm, nhiệt tình và tận tâm với khách hàng</p>
            </div>
            <div class="mission-item">
                <div class="mission-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="39" height="39" viewBox="0 0 39 39"><path d="M38.4,17.1l-1.2-1.1c-0.4-0.5-0.7-1.5-0.5-2.2l0.4-1.6c0.2-0.9,0.1-1.8-0.3-2.5c-0.5-0.8-1.2-1.3-2-1.6L33.2,7.7 c-0.6-0.2-1.4-0.9-1.6-1.6l-0.4-1.6c-0.5-1.7-2.4-2.8-4.1-2.4L25.5,2.5c-0.7,0.3-1.8,0-2.3-0.4l-1.2-1.1c-1.3-1.3-3.5-1.3-4.8,0 L16,2.1c-0.3,0.4-1.4,0.7-2,0.6l-1.6-0.4c-1.8-0.5-3.7,0.6-4.1,2.3l-0.4,1.6c-0.2,0.6-1,1.4-1.6,1.5l-1.6,0.5 c-0.9,0.2-1.6,0.8-2,1.6c-0.5,0.8-0.6,1.7-0.3,2.5L2.7,13.8c0.2,0.7-0.1,1.7-0.6,2.2l-1.1,1.1c-0.6,0.6-1,1.5-1,2.4 c0,0.9,0.3,1.8,1,2.4l1.1,1.1c0.5,0.5,0.8,1.5,0.6,2.1l-0.4,1.6c-0.2,0.9-0.1,1.8,0.3,2.5c0.5,0.8,1.2,1.3,2,1.6l1.6,0.4 c0.6,0.2,1.4,0.9,1.6,1.6l0.4,1.6c0.5,1.7,2.4,2.8,4.1,2.4l1.6-0.4c0.6-0.2,1.7,0.1,2.2,0.6l1.2,1.1c0.6,0.6,1.5,1,2.4,1 s1.8-0.3,2.4-1l1.2-1.1c0.5-0.4,1.6-0.7,2.2-0.6l1.6,0.4c0.3,0.1,0.6,0.1,0.9,0.1c1.5,0,2.9-1,3.3-2.5l0.4-1.6 c0.2-0.6,1-1.4,1.6-1.6l1.6-0.4c1.8-0.5,2.9-2.3,2.4-4.1l-0.6-1.6c-0.2-0.6,0.1-1.7,0.6-2.2l1.2-1.1C39.7,20.5,39.7,18.4,38.4,17.1z" fill="#356DF1"></path><path d="M28.5,15.2L18,25.6c-0.3,0.3-0.6,0.4-0.9,0.4s-0.7-0.1-0.9-0.4l-5.3-5.2c-0.5-0.5-0.5-1.3,0-1.8s1.3-0.5,1.9,0l4.3,4.3 l9.6-9.5c0.5-0.5,1.3-0.5,1.9,0C29.1,13.9,28.9,14.7,28.5,15.2z" fill="#FFFFFF"></path></svg>
                </div>
                <h3 class="mission-title"> Uy tín và đáng tin cậy</h3>
                <p class="mission-description">Được hàng ngàn khách hàng tin tưởng và lựa chọn trong nhiều năm</p>
            </div>
        </div>
    </div>
</section>