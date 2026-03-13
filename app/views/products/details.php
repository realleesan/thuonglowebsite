<?php
/**
 * Product Details Page - Logistics/Data Source Version
 */

// 1. Khởi tạo View an toàn
require_once __DIR__ . '/../../../core/view_init.php';

// 2. Chọn service phù hợp (ưu tiên biến được inject từ routing)
$service = isset($currentService) ? $currentService : ($publicService ?? null);
?>

<?php
$productId = $_GET['id'] ?? null;

// Initialize data variables
$product = null;
$category = null;
$relatedProducts = [];
$benefits = [];
$dataStructure = [];
$supplier = null;
$reviews = [];
$productMeta = [];
$showErrorMessage = false;
$errorMessage = '';

try {
    if (!$productId) {
        throw new Exception('ID sản phẩm không hợp lệ');
    }
    
    if (!$service) {
        throw new Exception('Service không khả dụng');
    }
    
    // Get product details
    $productData = $service->getProductDetailsData($productId);
    $product = $productData['product'] ?? null;
    $category = $productData['category'] ?? null;
    $relatedProducts = $productData['related_products'] ?? [];
    $benefits = $productData['benefits'] ?? [];
    $dataStructure = $productData['data_structure'] ?? [];
    $supplier = $productData['supplier'] ?? null;
    $reviews = $productData['reviews'] ?? [];
    $productMeta = $productData['product_meta'] ?? [];
    
    if (!$product) {
        throw new Exception('Không tìm thấy sản phẩm');
    }
    
    // Check if user has purchased this product
    $hasPurchased = false;
    $productExpiryDate = null;
    $productQuotaInfo = null;
    $isUserLoggedIn = isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
    if ($isUserLoggedIn) {
        require_once __DIR__ . '/../../models/OrdersModel.php';
        $ordersModel = new OrdersModel();
        $hasPurchased = $ordersModel->hasUserPurchasedProduct($_SESSION['user_id'], $productId);
        $productExpiryDate = $ordersModel->getProductExpiryDate($_SESSION['user_id'], $productId);
        $productQuotaInfo = $ordersModel->getProductQuotaInfo($_SESSION['user_id'], $productId);
    }
    
    // Add purchased flag to product array for use in template
    $product['has_purchased'] = $hasPurchased;
    $product['expiry_date'] = $productExpiryDate;
    $product['quota_info'] = $productQuotaInfo;
    
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

// Calculate rating distribution
function calculateRatingDistribution($reviews) {
    $distribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
    $total = count($reviews);
    
    if ($total === 0) {
        return $distribution;
    }
    
    foreach ($reviews as $review) {
        $rating = intval($review['rating']);
        if (isset($distribution[$rating])) {
            $distribution[$rating]++;
        }
    }
    
    // Convert to percentage
    foreach ($distribution as $rating => $count) {
        $distribution[$rating] = round(($count / $total) * 100);
    }
    
    return $distribution;
}

$ratingDistribution = calculateRatingDistribution($reviews);
$averageRating = !empty($reviews) ? array_sum(array_column($reviews, 'rating')) / count($reviews) : 0;
$averageRating = round($averageRating, 1);
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
            
            <!-- Product Header Section -->
            <section class="product-header-section">
                <div class="container">
                    <div class="header-content">
                        <div class="header-main">
                            <!-- Category Tag -->
                            <?php if ($category): ?>
                            <span class="category-tag">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </span>
                            <?php endif; ?>
                            
                            <!-- Product Title -->
                            <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                            
                            <!-- Supplier Info -->
                            <?php if ($supplier): ?>
                            <div class="instructor-info">
                                <span class="instructor-label">Nhà cung cấp:</span>
                                <a href="#" class="instructor-link">
                                    <?php if (!empty($supplier['avatar'])): ?>
                                    <img src="<?php echo htmlspecialchars($supplier['avatar']); ?>" alt="<?php echo htmlspecialchars($supplier['name']); ?>" class="instructor-avatar">
                                    <?php endif; ?>
                                    <span class="instructor-name"><?php echo htmlspecialchars($supplier['name']); ?></span>
                                </a>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Header Meta Info -->
                            <div class="header-meta">
                                <div class="meta-item">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M8 1V8L12 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/>
                                    </svg>
                                    <span>Cập nhật: <?php echo date('d/m/Y', strtotime($product['created_at'])); ?></span>
                                </div>
                                <div class="meta-item">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M8 2L10.09 6.26L15 7L11 10.74L12.18 15.74L8 13.27L3.82 15.74L5 10.74L1 7L5.91 6.26L8 2Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <span><?php echo count($reviews); ?> đánh giá</span>
                                </div>
                                <div class="meta-item">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M2 4H14M2 8H14M2 12H14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                    <span><?php echo number_format($productMeta['record_count'] ?? 0); ?> records</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Product Details Section -->
            <section class="product-details-section">
                <div class="container">
                    <div class="product-details-layout">
                        <!-- Left Column - Product Content -->
                        <div class="product-details-main">
                            
                            <!-- Product Content with Tabs -->
                            <div class="product-content">
                                <!-- Tab Navigation -->
                                <div class="product-tabs">
                                    <button class="tab-button active" data-tab="overview">Tổng quan</button>
                                    <button class="tab-button" data-tab="curriculum">Cấu trúc dữ liệu</button>
                                    <button class="tab-button" data-tab="instructor">Nhà cung cấp</button>
                                    <button class="tab-button" data-tab="reviews">Đánh giá</button>
                                </div>
                                
                                <!-- Tab Content -->
                                <div class="tab-content">
                                    
                                    <!-- Overview Tab -->
                                    <div id="overview" class="tab-panel active">
                                        <!-- Benefits Section -->
                                        <?php if (!empty($benefits)): ?>
                                        <div class="what-youll-learn">
                                            <h3 class="section-title">Lợi ích sử dụng</h3>
                                            <div class="learn-grid">
                                                <?php foreach ($benefits as $item): ?>
                                                <div class="learn-item">
                                                    <svg class="check-icon" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M7 10L9 12L13 8" stroke="#10B981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <circle cx="10" cy="10" r="8" stroke="#10B981" stroke-width="2"/>
                                                    </svg>
                                                    <span><?php echo htmlspecialchars($item); ?></span>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <!-- Product Description -->
                                        <?php if (!empty($product['description'])): ?>
                                        <div class="product-description">
                                            <h3 class="section-title">Mô tả sản phẩm</h3>
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
                                    </div>
                                    
                                    <!-- Data Structure Tab -->
                                    <div id="curriculum" class="tab-panel">
                                        <div class="curriculum-section">
                                            <div class="curriculum-header">
                                                <h3 class="section-title">Cấu trúc dữ liệu</h3>
                                                <div class="curriculum-meta">
                                                    <span><?php echo count($dataStructure); ?> nhóm thông tin</span>
                                                    <span>•</span>
                                                    <span><?php echo number_format($productMeta['record_count'] ?? 0); ?> records</span>
                                                    <span>•</span>
                                                    <span><?php echo $productMeta['data_size'] ?? '0 MB'; ?></span>
                                                </div>
                                            </div>
                                            
                                            <div class="curriculum-content">
                                                <?php foreach ($dataStructure as $sectionIndex => $section): ?>
                                                <div class="curriculum-section-item">
                                                    <div class="section-header">
                                                        <span class="section-number"><?php echo $sectionIndex + 1; ?></span>
                                                        <h4 class="section-title"><?php echo htmlspecialchars($section['title']); ?></h4>
                                                        <span class="section-lessons-count"><?php echo count($section['items']); ?> trường</span>
                                                    </div>
                                                    <div class="section-content">
                                                        <?php foreach ($section['items'] as $item): ?>
                                                        <div class="lesson-item">
                                                            <div class="lesson-icon">
                                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <path d="M4 3L12 8L4 13V3Z" fill="currentColor"/>
                                                                </svg>
                                                            </div>
                                                            <span class="lesson-title"><?php echo htmlspecialchars($item['title']); ?></span>
                                                            <span class="lesson-duration"><?php echo htmlspecialchars($item['type']); ?></span>
                                                        </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Supplier Tab -->
                                    <div id="instructor" class="tab-panel">
                                        <div class="instructor-section">
                                            <h3 class="section-title">Về nhà cung cấp</h3>
                                            <?php if ($supplier): ?>
                                            <div class="instructor-profile">
                                                <div class="instructor-avatar-large">
                                                    <img src="<?php echo htmlspecialchars($supplier['avatar'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($supplier['name']) . '&background=356DF1&color=fff&size=150'); ?>" alt="<?php echo htmlspecialchars($supplier['name']); ?>">
                                                </div>
                                                <div class="instructor-info-details">
                                                    <h4 class="instructor-name"><?php echo htmlspecialchars($supplier['name']); ?></h4>
                                                    <p class="instructor-title"><?php echo htmlspecialchars($supplier['title'] ?? 'Nhà cung cấp'); ?></p>
                                                    
                                                    <?php if (!empty($supplier['bio'])): ?>
                                                    <p class="instructor-bio"><?php echo nl2br(htmlspecialchars($supplier['bio'])); ?></p>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($supplier['social'])): ?>
                                                    <div class="instructor-social">
                                                        <?php if (!empty($supplier['social']['website'])): ?>
                                                        <a href="<?php echo htmlspecialchars($supplier['social']['website']); ?>" class="social-link" aria-label="Website">
                                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                                                            </svg>
                                                        </a>
                                                        <?php endif; ?>
                                                        <?php if (!empty($supplier['social']['hotline'])): ?>
                                                        <a href="tel:<?php echo htmlspecialchars($supplier['social']['hotline']); ?>" class="social-link" aria-label="Hotline">
                                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                                                <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/>
                                                            </svg>
                                                        </a>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Reviews Tab -->
                                    <div id="reviews" class="tab-panel">
                                        <div class="reviews-section">
                                            <h3 class="section-title">Đánh giá từ khách hàng</h3>
                                            
                                            <?php if (!empty($reviews)): ?>
                                            <div class="reviews-layout">
                                                <!-- Rating Overview -->
                                                <div class="rating-overview">
                                                    <div class="rating-big">
                                                        <span class="rating-value"><?php echo $averageRating; ?></span>
                                                        <div class="rating-stars">
                                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="<?php echo $i <= round($averageRating) ? '#FFB800' : '#E0E0E0'; ?>">
                                                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                                            </svg>
                                                            <?php endfor; ?>
                                                        </div>
                                                        <span class="rating-count">Dựa trên <?php echo count($reviews); ?> đánh giá</span>
                                                    </div>
                                                    
                                                    <!-- Rating Distribution -->
                                                    <div class="rating-distribution">
                                                        <?php for ($i = 5; $i >= 1; $i--): ?>
                                                        <div class="distribution-row">
                                                            <span class="star-label"><?php echo $i; ?> sao</span>
                                                            <div class="progress-bar">
                                                                <div class="progress-fill" style="width: <?php echo $ratingDistribution[$i]; ?>%;"></div>
                                                            </div>
                                                            <span class="percentage"><?php echo $ratingDistribution[$i]; ?>%</span>
                                                        </div>
                                                        <?php endfor; ?>
                                                    </div>
                                                </div>
                                                
                                                <!-- Reviews List -->
                                                <div class="reviews-list">
                                                    <?php foreach ($reviews as $review): ?>
                                                    <div class="review-item">
                                                        <div class="review-header">
                                                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($review['reviewer_name'] ?? 'U'); ?>&background=random&color=fff&size=50" alt="User" class="reviewer-avatar">
                                                            <div class="reviewer-info">
                                                                <h4 class="reviewer-name"><?php echo htmlspecialchars($review['reviewer_name'] ?? 'Ẩn danh'); ?></h4>
                                                                <div class="review-rating">
                                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="<?php echo $i <= $review['rating'] ? '#FFB800' : '#E0E0E0'; ?>">
                                                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                                                    </svg>
                                                                    <?php endfor; ?>
                                                                </div>
                                                            </div>
                                                            <span class="review-date"><?php echo date('d/m/Y', strtotime($review['created_at'])); ?></span>
                                                        </div>
                                                        <?php if (!empty($review['title'])): ?>
                                                        <div class="review-title">
                                                            <strong><?php echo htmlspecialchars($review['title']); ?></strong>
                                                        </div>
                                                        <?php endif; ?>
                                                        <?php if (!empty($review['content'])): ?>
                                                        <div class="review-content">
                                                            <p><?php echo nl2br(htmlspecialchars($review['content'])); ?></p>
                                                        </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                            <?php else: ?>
                                            <div class="no-reviews">
                                                <p>Chưa có đánh giá nào cho sản phẩm này.</p>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column - Product Sidebar (Sticky) -->
                        <div class="product-sidebar">
                            <div class="product-card" style="position: relative;">
                                <button class="wishlist-icon-btn" onclick="toggleWishlist(<?php echo $product['id']; ?>, this)" title="Thêm vào yêu thích" style="position: absolute; top: 12px; right: 12px; z-index: 100;">
                                    <i class="far fa-heart"></i>
                                </button>
                                <div class="product-image">
                                    <img src="<?php echo getProductImage($product); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                         class="product-thumbnail">
                                </div>
                                
                                <div class="product-card-content">
                                    <!-- Price Section -->
                                    <div class="product-price">
                                        <?php if (!empty($product['sale_price']) && $product['sale_price'] < $product['price']): ?>
                                            <span class="price"><?php echo $product['formatted_sale_price']; ?></span>
                                            <span class="old-price"><?php echo $product['formatted_price']; ?></span>
                                        <?php else: ?>
                                            <span class="price"><?php echo $product['formatted_price']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Action Buttons -->
                                    <?php if ($product['status'] === 'active' && $product['stock'] > 0): ?>
                                        <?php if (!empty($product['has_purchased'])): ?>
                                            <!-- Purchased product buttons -->
                                            <button class="btn-order" onclick="viewMyOrder(<?php echo $product['id']; ?>)">
                                                <i class="fas fa-eye"></i> Xem ngay
                                            </button>
                                            <button class="btn-cart" onclick="renewProduct(<?php echo $product['id']; ?>)">
                                                <i class="fas fa-sync-alt"></i> Gia hạn
                                            </button>
                                        <?php else: ?>
                                            <!-- Normal purchase buttons -->
                                            <button class="btn-order" onclick="buyNow(<?php echo $product['id']; ?>)">
                                                Mua ngay
                                            </button>
                                            <button class="btn-cart" onclick="addToCart(<?php echo $product['id']; ?>)">
                                                Thêm vào giỏ hàng
                                            </button>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <button class="btn-order disabled" disabled>
                                            <?php echo ($product['stock'] <= 0) ? 'Hết hàng' : 'Ngừng bán'; ?>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <!-- Expiry Countdown for purchased products -->
                                    <?php if (!empty($product['has_purchased']) && !empty($product['expiry_date'])): ?>
                                        <?php 
                                            $expiry = strtotime($product['expiry_date']);
                                            $now = time();
                                            $daysLeft = floor(($expiry - $now) / (60 * 60 * 24));
                                            $hoursLeft = floor((($expiry - $now) % (60 * 60 * 24)) / (60 * 60));
                                            
                                            $countdownStyle = 'background: #28a745;';
                                            $countdownText = 'Còn ' . $daysLeft . ' ngày';
                                            
                                            if ($daysLeft <= 0) {
                                                if ($hoursLeft > 0) {
                                                    $countdownText = 'Còn ' . $hoursLeft . ' giờ';
                                                    $countdownStyle = 'background: #ff971a;';
                                                } else {
                                                    $countdownText = 'Hết hạn';
                                                    $countdownStyle = 'background: #dc3545;';
                                                }
                                            } elseif ($daysLeft <= 7) {
                                                $countdownStyle = 'background: #ff971a;';
                                            }
                                        ?>
                                        <div class="expiry-countdown" style="margin-top: 12px; padding: 10px 15px; border-radius: 8px; color: white; font-size: 14px; font-weight: 600; text-align: center; <?php echo $countdownStyle; ?>">
                                            <i class="fas fa-clock"></i> 
                                            <?php if ($daysLeft > 0 || $hoursLeft > 0): ?>
                                                Sản phẩm của bạn: <?php echo $countdownText; ?>
                                            <?php else: ?>
                                                Sản phẩm đã hết hạn - Vui lòng gia hạn để tiếp tục sử dụng
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Quota display for purchased products -->
                                        <?php if (!empty($product['quota_info'])): ?>
                                            <?php 
                                                $qi = $product['quota_info'];
                                                $quotaPercent = $qi['total'] > 0 ? round(($qi['remaining'] / $qi['total']) * 100) : 0;
                                                $quotaStyle = 'background: #17a2b8;';
                                                if ($quotaPercent <= 20) {
                                                    $quotaStyle = 'background: #dc3545;';
                                                } elseif ($quotaPercent <= 50) {
                                                    $quotaStyle = 'background: #ff971a;';
                                                }
                                            ?>
                                            <div class="quota-display" style="margin-top: 8px; padding: 10px 15px; border-radius: 8px; color: white; font-size: 14px; font-weight: 600; text-align: center; <?php echo $quotaStyle; ?>">
                                                <i class="fas fa-bolt"></i> 
                                                Quota của bạn còn: <?php echo $qi['remaining']; ?>/<?php echo $qi['total']; ?>
                                                <?php if ($qi['remaining'] <= 0): ?>
                                                    - Hết quota! Vui lòng gia hạn
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    
                                    <!-- Meta Info - Logistics Specific -->
                                    <div class="product-meta-info">
                                        <div class="meta-row">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M8 2L10.09 6.26L15 7L11 10.74L12.18 15.74L8 13.27L3.82 15.74L5 10.74L1 7L5.91 6.26L8 2Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <span class="label">Đánh giá:</span>
                                            <span class="value"><?php echo $averageRating; ?>/5.0</span>
                                        </div>
                                        <div class="meta-row">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M2 4H14M2 8H14M2 12H14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                            </svg>
                                            <span class="label">Số record:</span>
                                            <span class="value"><?php echo number_format($productMeta['record_count'] ?? 0); ?></span>
                                        </div>
                                        <div class="meta-row">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M8 1V8L12 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/>
                                            </svg>
                                            <span class="label">Dung lượng:</span>
                                            <span class="value"><?php echo $productMeta['data_size'] ?? '0 MB'; ?></span>
                                        </div>
                                        <div class="meta-row">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M12 5L8 9L4 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M2 11V14H14V11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <span class="label">Định dạng:</span>
                                            <span class="value"><?php echo $productMeta['data_format'] ?? 'Excel'; ?></span>
                                        </div>
                                        <div class="meta-row">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="1.5"/>
                                                <path d="M8 5V8L10 10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                            </svg>
                                            <span class="label">Nguồn gốc:</span>
                                            <span class="value"><?php echo $productMeta['data_source'] ?? 'Việt Nam'; ?></span>
                                        </div>
                                        <?php 
                                            $expiryDays = $product['expiry_days'] ?? 30;
                                            if ($expiryDays > 0): 
                                        ?>
                                        <div class="meta-row">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                                <path d="M16 2V6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                <path d="M8 2V6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                <path d="M3 10H21" stroke="currentColor" stroke-width="1.5"/>
                                                <path d="M8 14H8.01" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                <path d="M12 14H12.01" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                <path d="M16 14H16.01" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                <path d="M8 18H8.01" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                            </svg>
                                            <span class="label">Hạn sử dụng:</span>
                                            <span class="value"><?php echo $expiryDays; ?> ngày</span>
                                        </div>
                                        <?php endif; ?>
                                        <?php 
                                            $quota = $product['quota'] ?? 100;
                                            if ($quota > 0): 
                                        ?>
                                        <div class="meta-row">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M13 2L3 14H12L11 22L21 10H12L13 2Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <span class="label">Quota:</span>
                                            <span class="value"><?php echo $quota; ?></span>
                                        </div>
                                        <?php endif; ?>
                                        <div class="meta-row">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M8 2L10.09 6.26L15 7L11 10.74L12.18 15.74L8 13.27L3.82 15.74L5 10.74L1 7L5.91 6.26L8 2Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <span class="label">Độ tin cậy:</span>
                                            <span class="value"><?php echo $productMeta['reliability'] ?? 'N/A'; ?></span>
                                        </div>
                                    </div>
                                    
                                    <!-- Product Info List -->
                                    <div class="product-info">
                                        <h5>Thông tin sản phẩm:</h5>
                                        <ul>
                                            <li>
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M3 8L6 11L13 4" stroke="#10B981" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                Dữ liệu chính xác, verified
                                            </li>
                                            <li>
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M3 8L6 11L13 4" stroke="#10B981" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                Cập nhật định kỳ
                                            </li>
                                            <li>
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M3 8L6 11L13 4" stroke="#10B981" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                Hỗ trợ kỹ thuật 24/7
                                            </li>
                                            <?php if ($product['digital']): ?>
                                            <li>
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M3 8L6 11L13 4" stroke="#10B981" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                Giao file ngay sau thanh toán
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
                    <h3 class="related-title">Sản phẩm liên quan</h3>
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


<!-- Product Details Styles moved to product_details.css -->


<!-- Product Details JS -->
<script src="<?php echo base_url(); ?>assets/js/product_details.js"></script>
