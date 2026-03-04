<?php
/**
 * Products Page - Dynamic Version
 */

// 1. Khởi tạo View an toàn & ServiceManager
require_once __DIR__ . '/../../../core/view_init.php';

// 2. Chọn service phù hợp (ưu tiên biến được inject từ routing)
$service = isset($currentService) ? $currentService : ($publicService ?? null);

// Get pagination parameters
// Get pagination parameters
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
// Ensure page is at least 1
$page = max(1, $page);
$limit = 12; // Products per page
$categoryId = $_GET['category'] ?? null;
// Handle empty string from form and convert to integer for DB query
if ($categoryId === '' || $categoryId === 'null') {
    $categoryId = null;
} else {
    $categoryId = (int) $categoryId;
}
$orderBy = $_GET['order_by'] ?? 'post_date';
$search = $_GET['search'] ?? '';

// Get filter parameters from sidebar
$priceType = $_GET['price_type'] ?? ''; // Single value: 'free', 'paid', or empty

// Store current filters for UI highlighting
$currentFilters = [
    'category' => $categoryId,
    'price_type' => $priceType
];

// Initialize data variables
$products = [];
$categories = [];
$pagination = [];
$totalProducts = 0;
$showErrorMessage = false;
$errorMessage = '';

try {
    // Prepare filters for PublicService
    $filters = [
        'page' => $page,
        'limit' => $limit,
        'category_id' => $categoryId,
        'order_by' => $orderBy,
        'search' => $search,
        'price_type' => $priceType
    ];
    
    // Get product listing data từ PublicService
    $productData = [];
    if ($service && method_exists($service, 'getProductListingData')) {
        $productData = $service->getProductListingData($filters);
    }
    $products = $productData['products'] ?? [];
    $pagination = $productData['pagination'] ?? [];
    $totalProducts = $pagination['total'] ?? 0;
    
    // Get categories for sidebar
    $categoriesData = [];
    if ($service && method_exists($service, 'getCategoriesWithProductCounts')) {
        $categoriesData = $service->getCategoriesWithProductCounts();
    }
    $categories = $categoriesData['categories'] ?? [];
    
} catch (Exception $e) {
    // Handle errors gracefully
    $result = $errorHandler->handleViewError($e, 'products', []);
    $showErrorMessage = true;
    $errorMessage = $result['message'];
    
    // Use empty state data an toàn
    $products = [];
    $pagination = ['current_page' => 1, 'total' => 0, 'last_page' => 1];
}

// Helper function to get product image
if (!function_exists('getProductImage')) {
    function getProductImage($product) {
        if (!empty($product['image']) && $product['image'] !== '/assets/images/default-product.jpg') {
            return $product['image'];
        }
        return 'https://eduma.thimpress.com/demo-marketplace/wp-content/uploads/sites/99/2024/10/course-offline-01-675x450.jpg';
    }
}

// Helper function to get sort options
if (!function_exists('getSortOptions')) {
    function getSortOptions() {
        return [
            'post_date' => 'Mới nhất',
            'post_title' => 'Tên A-Z',
            'post_title_desc' => 'Tên Z-A',
            'price' => 'Giá cao đến thấp',
            'price_low' => 'Giá thấp đến cao',
            'popular' => 'Phổ biến',
            'rating' => 'Đánh giá trung bình'
        ];
    }
}

// Helper function to format record count
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

// Calculate display counts based on filtered products
$totalFiltered = count($products);
// Ensure page is at least 1
$page = max(1, $page);
$fromCount = $totalFiltered > 0 ? ($page - 1) * $limit + 1 : 0;
$toCount = min($page * $limit, $totalFiltered);
// Adjust if we're on a page beyond available products
if ($fromCount > $totalFiltered) {
    $fromCount = 0;
    $toCount = 0;
}
?>

<!-- Main Content -->
<div id="wrapper-container" class="wrapper-container">
    <div class="content-pusher">
        <div id="main-content">
            <div class="elementor elementor-15130">
                <!-- Error Message -->
                <?php if ($showErrorMessage): ?>
                <div class="error-message" style="background: #f8d7da; color: #721c24; padding: 15px; margin: 20px 0; border-radius: 5px;">
                    <strong>Thông báo:</strong> <?php echo htmlspecialchars($errorMessage); ?>
                </div>
                <?php endif; ?>
                
                <!-- Main Products Section -->
                <section class="products-section">
                    <div class="container">
                        <div class="products-layout">
                            <!-- Left Column - Products -->
                            <div class="products-main">
                                <!-- Header with Title and Filter Button -->
                                <div class="products-header">
                                    <h1 class="page-title">Sản phẩm</h1>
                                    <button class="filter-toggle-btn" id="filterToggle">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <path d="M8.37013 7.79006C8.42013 8.22006 8.73013 8.55006 9.17013 8.63006C9.24013 8.64006 9.31013 8.65006 9.38013 8.65006C9.74013 8.65006 10.0701 8.46006 10.2401 8.15006C10.2401 8.15006 10.3701 7.93006 10.3701 7.86006V6.83006H21.3701C21.4801 6.83006 21.8501 6.61006 21.9301 6.52006C22.1301 6.31006 22.2301 5.99006 22.1801 5.68006C22.1401 5.36006 21.9601 5.10006 21.7001 4.95006C21.6801 4.94006 21.3401 4.81006 21.2801 4.81006H10.3701V3.77006C10.3701 3.64006 10.1101 3.30006 10.0601 3.25006C9.80013 3.01006 9.39013 2.94006 9.03013 3.07006C8.68013 3.19006 8.44013 3.47006 8.39013 3.81006C8.34013 4.16006 8.36013 4.61006 8.37013 5.05006C8.37013 5.25006 8.39013 5.44006 8.39013 5.61006C8.39013 5.78006 8.39013 5.96006 8.37013 6.16006C8.35013 6.71006 8.33013 7.34006 8.37013 7.80006V7.79006Z" fill="#098CE9"></path>
                                            <path d="M21.3701 17.5401H10.3701V16.5101C10.3701 16.4501 10.2201 16.1701 10.2201 16.1601C9.99013 15.8101 9.57013 15.6601 9.14013 15.7501C8.72013 15.8501 8.42013 16.1701 8.37013 16.5801C8.34013 16.9301 8.35013 17.3401 8.37013 17.7401C8.37013 17.9501 8.38013 18.1501 8.38013 18.3401C8.38013 18.5001 8.38013 18.6801 8.36013 18.8801C8.34013 19.4601 8.31013 20.1201 8.38013 20.5701C8.42013 20.8601 8.61013 21.1101 8.89013 21.2601C9.05013 21.3401 9.22013 21.3801 9.39013 21.3801C9.56013 21.3801 9.71013 21.3401 9.85013 21.2701C10.0201 21.1901 10.3701 20.8201 10.3701 20.6101V19.5801H21.2801C21.3401 19.5801 21.6801 19.4501 21.7001 19.4401C21.9601 19.2901 22.1301 19.0201 22.1701 18.7101C22.2101 18.4001 22.1201 18.0801 21.9101 17.8601C21.8601 17.8101 21.4801 17.5501 21.3501 17.5501L21.3701 17.5401Z" fill="#098CE9"></path>
                                            <path d="M14.3401 9.45006C14.0301 9.31006 13.7001 9.32006 13.4301 9.49006C13.1301 9.67006 12.9201 10.0201 12.8901 10.4201C12.8101 11.4001 12.8301 13.0001 12.8901 13.9301C12.9301 14.3901 13.1701 14.7801 13.5201 14.9501C13.6401 15.0101 13.7701 15.0301 13.9001 15.0301C14.1101 15.0301 14.3101 14.9601 14.5101 14.8201C14.6601 14.7201 14.9201 14.3701 14.9201 14.1701V13.1901H21.4001C21.4001 13.1901 21.4501 13.1901 21.6901 13.0701C21.9901 12.9001 22.1801 12.5901 22.1901 12.2301C22.2001 11.8701 22.0301 11.5401 21.7401 11.3601C21.7201 11.3501 21.4601 11.2101 21.3901 11.2101H14.9101V10.2201C14.9101 9.96006 14.5501 9.56006 14.3301 9.46006L14.3401 9.45006Z" fill="#098CE9"></path>
                                            <path d="M2.84013 13.1801H11.3801C11.8701 13.0601 12.2001 12.6701 12.2101 12.2001C12.2101 11.7301 11.9201 11.3401 11.4301 11.2001H2.77013C2.23013 11.3501 2.00013 11.8201 2.00013 12.2201C2.01013 12.7001 2.33013 13.0801 2.83013 13.1901L2.84013 13.1801Z" fill="#098CE9"></path>
                                            <path d="M2.84013 6.82006H6.82013C7.40013 6.69006 7.66013 6.22006 7.65013 5.80006C7.65013 5.39006 7.38013 4.92006 6.77013 4.81006C6.25013 4.84006 5.66013 4.81006 5.09013 4.78006C4.35013 4.74006 3.58013 4.70006 2.92013 4.79006C2.31013 4.90006 2.02013 5.36006 2.00013 5.79006C1.98013 6.21006 2.23013 6.69006 2.83013 6.83006L2.84013 6.82006Z" fill="#098CE9"></path>
                                            <path d="M6.86013 17.5501H2.82013C2.23013 17.6901 1.98013 18.1801 2.00013 18.5901C2.02013 19.0101 2.31013 19.4701 2.92013 19.5601C3.22013 19.6001 3.54013 19.6201 3.87013 19.6201C4.23013 19.6201 4.60013 19.6001 4.96013 19.5901C5.55013 19.5601 6.15013 19.5401 6.69013 19.5901H6.71013C7.31013 19.5101 7.60013 19.0601 7.63013 18.6401C7.66013 18.2201 7.43013 17.7201 6.84013 17.5701L6.86013 17.5501Z" fill="#098CE9"></path>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Top Bar with Results and Sort -->
                                <div class="products-topbar">
                                    <div class="results-count">
                                        <?php if ($totalFiltered > 0): ?>
                                            <span>Hiển thị <?php echo $fromCount; ?>-<?php echo $toCount; ?> trong tổng số <?php echo $totalFiltered; ?> kết quả</span>
                                        <?php else: ?>
                                            <span>Không tìm thấy sản phẩm nào</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="sort-dropdown">
                                        <form method="get">
                                            <input type="hidden" name="page" value="products">
                                            <?php if ($categoryId): ?>
                                                <input type="hidden" name="category" value="<?php echo htmlspecialchars($categoryId); ?>">
                                            <?php endif; ?>
                                            <?php if ($search): ?>
                                                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                                            <?php endif; ?>
                                            <select name="order_by" class="sort-select" onchange="this.form.submit()">
                                                <?php foreach (getSortOptions() as $value => $label): ?>
                                                    <option value="<?php echo $value; ?>" <?php echo $orderBy === $value ? 'selected' : ''; ?>>
                                                        <?php echo $label; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </form>
                                    </div>
                                </div>

                                <!-- Products Grid -->
                                <div class="products-grid">
                                    <?php if (empty($products)): ?>
                                        <div class="no-products">
                                            <p>Không tìm thấy sản phẩm nào.</p>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($products as $product): ?>
                                        <!-- Product Item -->
                                        <div class="course-item">
                                            <div class="course-category">
                                                <a href="?page=products&category=<?php echo $product['category_id'] ?? ''; ?>" class="category-tag">
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
                                                    <?php if ($product['sale_price']): ?>
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
                                    <?php endif; ?>
                                </div>

                                <!-- Pagination -->
                                <?php if ($pagination['last_page'] > 1): ?>
                                <div class="pagination-wrapper">
                                    <nav class="pagination">
                                        <!-- Previous Page -->
                                        <?php if ($pagination['current_page'] > 1): ?>
                                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] - 1])); ?>" class="page-link prev">
                                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M12.5 15L7.5 10L12.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <!-- Page Numbers -->
                                        <?php
                                        $startPage = max(1, $pagination['current_page'] - 2);
                                        $endPage = min($pagination['last_page'], $pagination['current_page'] + 2);
                                        
                                        for ($i = $startPage; $i <= $endPage; $i++):
                                        ?>
                                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                                               class="page-link <?php echo $i === $pagination['current_page'] ? 'active' : ''; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        <?php endfor; ?>
                                        
                                        <!-- Next Page -->
                                        <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
                                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] + 1])); ?>" class="page-link next">
                                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M7.5 15L12.5 10L7.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </a>
                                        <?php endif; ?>
                                    </nav>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Right Column - Sidebar -->
                            <div class="products-sidebar" id="productsSidebar">
                                <form method="get" action="" class="filter-form">
                                    <input type="hidden" name="page" value="products">
                                    <?php if ($search): ?>
                                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                                    <?php endif; ?>
                                    <?php if ($orderBy): ?>
                                        <input type="hidden" name="order_by" value="<?php echo htmlspecialchars($orderBy); ?>">
                                    <?php endif; ?>
                                    <div class="sidebar-header">
                                        <h3>Bộ lọc</h3>
                                        <button type="button" class="sidebar-close" id="sidebarClose">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <div class="sidebar-content">
                                        <!-- Categories Filter (radio buttons, submit on Apply) -->
                                        <div class="filter-section">
                                            <h3 class="filter-title">Danh mục</h3>
                                            <div class="filter-content">
                                                <ul class="category-list">
                                                    <li>
                                                        <label>
                                                            <input type="radio" name="category" value="" 
                                                                   <?php echo empty($categoryId) ? 'checked' : ''; ?>>
                                                            <span>Tìm kiếm nhiều nhất</span>
                                                        </label>
                                                        <span class="count">(<?php echo $totalProducts; ?>)</span>
                                                    </li>
                                                    <?php if (!empty($categories)): ?>
                                                        <?php foreach ($categories as $category): ?>
                                                            <li>
                                                                <label>
                                                                    <input type="radio" name="category" value="<?php echo $category['id']; ?>" 
                                                                           <?php echo $categoryId == $category['id'] ? 'checked' : ''; ?>>
                                                                        <span><?php echo htmlspecialchars($category['name']); ?></span>
                                                                </label>
                                                                <span class="count">(<?php echo $category['product_count']; ?>)</span>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        </div>

                                        <!-- Price Filter (radio buttons: Tất cả/Miễn phí/Có phí) -->
                                        <div class="filter-section">
                                            <h3 class="filter-title">Giá</h3>
                                            <div class="filter-content">
                                                <ul class="price-list">
                                                    <li>
                                                        <label>
                                                            <input type="radio" name="price_type" value="" 
                                                                   <?php echo empty($priceType) ? 'checked' : ''; ?>>
                                                            <span>Tất cả</span>
                                                        </label>
                                                    </li>
                                                    <li>
                                                        <label>
                                                            <input type="radio" name="price_type" value="free" 
                                                                   <?php echo $priceType === 'free' ? 'checked' : ''; ?>>
                                                            <span>Miễn phí</span>
                                                        </label>
                                                    </li>
                                                    <li>
                                                        <label>
                                                            <input type="radio" name="price_type" value="paid" 
                                                                   <?php echo $priceType === 'paid' ? 'checked' : ''; ?>>
                                                            <span>Có phí</span>
                                                        </label>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>

                                        <!-- Apply and Reset Buttons -->
                                        <div class="filter-section">
                                            <button type="submit" class="apply-filters-btn">Áp dụng</button>
                                        </div>
                                        <div class="filter-section">
                                            <button type="button" class="reset-filters-btn" onclick="window.location.href='?page=products'">Đặt lại</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
