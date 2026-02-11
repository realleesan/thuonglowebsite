<?php
/**
 * Products Page - Dynamic Version
 * Converted from hardcoded data to dynamic database data
 */

// Load required services and models
require_once __DIR__ . '/../../services/ViewDataService.php';
require_once __DIR__ . '/../../services/ErrorHandler.php';

// Initialize services
$viewDataService = new ViewDataService();
$errorHandler = new ErrorHandler();

// Get pagination parameters
$page = (int) ($_GET['page'] ?? 1);
$limit = 12; // Products per page
$categoryId = $_GET['category'] ?? null;
$orderBy = $_GET['order_by'] ?? 'post_date';
$search = $_GET['search'] ?? '';

// Initialize data variables
$products = [];
$categories = [];
$pagination = [];
$totalProducts = 0;
$showErrorMessage = false;
$errorMessage = '';

try {
    // Prepare filters for ViewDataService
    $filters = [
        'page' => $page,
        'limit' => $limit,
        'category_id' => $categoryId,
        'order_by' => $orderBy,
        'search' => $search
    ];
    
    // Get product listing data
    $productData = $viewDataService->getProductListingData($filters);
    $products = $productData['products'] ?? [];
    $pagination = $productData['pagination'] ?? [];
    $totalProducts = $pagination['total'] ?? 0;
    
    // Get categories for sidebar
    $categoriesData = $viewDataService->getCategoriesWithProductCounts();
    $categories = $categoriesData['categories'] ?? [];
    
} catch (Exception $e) {
    // Handle errors gracefully
    $result = $errorHandler->handleViewError($e, 'products', []);
    $showErrorMessage = true;
    $errorMessage = $result['message'];
    
    // Use empty state data
    $emptyState = $viewDataService->handleEmptyState('products');
    $products = $emptyState['products'];
    $pagination = $emptyState['pagination'];
}

// Helper function to get product image
function getProductImage($product) {
    if (!empty($product['image']) && $product['image'] !== '/assets/images/default-product.jpg') {
        return $product['image'];
    }
    return 'https://eduma.thimpress.com/demo-marketplace/wp-content/uploads/sites/99/2024/10/course-offline-01-675x450.jpg';
}

// Helper function to get sort options
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

// Calculate display counts
$displayedCount = count($products);
$fromCount = ($page - 1) * $limit + 1;
$toCount = min($page * $limit, $totalProducts);
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
                                        <?php if ($totalProducts > 0): ?>
                                            <span>Hiển thị <?php echo $fromCount; ?>-<?php echo $toCount; ?> trong tổng số <?php echo $totalProducts; ?> kết quả</span>
                                        <?php else: ?>
                                            <span>Không tìm thấy sản phẩm nào</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="sort-dropdown">
                                        <form method="get">
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
                                <div class="sidebar-header">
                                    <h3>Bộ lọc</h3>
                                    <button class="sidebar-close" id="sidebarClose">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div class="sidebar-content">
                                    <!-- Search Filter -->
                                    <div class="filter-section">
                                        <h3 class="filter-title">Tìm kiếm</h3>
                                        <div class="filter-content">
                                            <form method="get">
                                                <?php if ($categoryId): ?>
                                                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($categoryId); ?>">
                                                <?php endif; ?>
                                                <?php if ($orderBy !== 'post_date'): ?>
                                                    <input type="hidden" name="order_by" value="<?php echo htmlspecialchars($orderBy); ?>">
                                                <?php endif; ?>
                                                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                                                       placeholder="Tìm kiếm sản phẩm..." class="search-input">
                                                <button type="submit" class="search-btn">Tìm kiếm</button>
                                            </form>
                                        </div>
                                    </div>
                                    
                                    <!-- Categories Filter -->
                                    <div class="filter-section">
                                        <h3 class="filter-title">Danh mục</h3>
                                        <div class="filter-content">
                                            <ul class="category-list">
                                                <li>
                                                    <a href="?<?php echo http_build_query(array_merge($_GET, ['category' => null])); ?>" 
                                                       class="<?php echo !$categoryId ? 'active' : ''; ?>">
                                                        Tất cả
                                                    </a>
                                                    <span class="count">(<?php echo $totalProducts; ?>)</span>
                                                </li>
                                                <?php if (!empty($categories)): ?>
                                                    <?php foreach ($categories as $category): ?>
                                                        <li>
                                                            <a href="?<?php echo http_build_query(array_merge($_GET, ['category' => $category['id']])); ?>" 
                                                               class="<?php echo $categoryId == $category['id'] ? 'active' : ''; ?>">
                                                                <?php echo $category['name']; ?>
                                                            </a>
                                                            <span class="count">(<?php echo $category['product_count']; ?>)</span>
                                                        </li>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <!-- Fallback categories -->
                                                    <li><a href="#">Data nguồn hàng</a> <span class="count">(8)</span></li>
                                                    <li><a href="#">Vận chuyển</a> <span class="count">(6)</span></li>
                                                    <li><a href="#">Mua hàng trọn gói</a> <span class="count">(4)</span></li>
                                                    <li><a href="#">Thanh toán quốc tế</a> <span class="count">(3)</span></li>
                                                    <li><a href="#">Dịch vụ đánh hàng</a> <span class="count">(2)</span></li>
                                                    <li><a href="#">Sản phẩm khác</a> <span class="count">(5)</span></li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </div>

                                    <!-- Reset Button -->
                                    <div class="filter-section">
                                        <a href="?page=products" class="reset-filters-btn">Đặt lại</a>
                                    </div>

                                    <!-- Author Filter -->
                                    <div class="filter-section">
                                        <h3 class="filter-title">Nhà cung cấp</h3>
                                        <div class="filter-content">
                                            <ul class="author-list">
                                                <li><a href="#">ThuongLo.com</a></li>
                                            </ul>
                                        </div>
                                    </div>

                                    <!-- Price Filter -->
                                    <div class="filter-section">
                                        <h3 class="filter-title">Giá</h3>
                                        <div class="filter-content">
                                            <ul class="price-list">
                                                <li><a href="#">Miễn phí</a></li>
                                                <li><a href="#">Có phí</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>