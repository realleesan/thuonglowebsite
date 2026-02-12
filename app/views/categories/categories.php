<?php
/**
 * Categories Page - Dynamic Version
 */

// 1. Khởi tạo View an toàn
require_once __DIR__ . '/../../../core/view_init.php';

// Get pagination and sorting parameters
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
$orderBy = $_GET['order_by'] ?? 'name';

// Initialize data variables
$categories = [];
$pagination = [];
$categoryStats = [];
$totalCategories = 0;
$displayedCount = 0;
$showErrorMessage = false;
$errorMessage = '';

try {
    // Debug: Before calling getCategoriesPageData
    echo "<!-- Debug: About to call getCategoriesPageData -->";
    error_log("Categories page: About to call getCategoriesPageData");
    
    // Get categories page data
    $categoriesData = $viewDataService->getCategoriesPageData($page, $perPage, $orderBy);
    
    // Debug: After calling getCategoriesPageData
    echo "<!-- Debug: getCategoriesPageData returned " . count($categoriesData) . " keys -->";
    error_log("Categories page: getCategoriesPageData returned " . count($categoriesData) . " keys");
    
    // Extract data
    $categories = $categoriesData['categories'] ?? [];
    $pagination = $categoriesData['pagination'] ?? [];
    $categoryStats = $categoriesData['stats'] ?? [];
    $totalCategories = $categoriesData['total_categories'] ?? 0;
    $displayedCount = $categoriesData['displayed_count'] ?? 0;
    
    // Debug: Data extracted
    echo "<!-- Debug: Extracted " . count($categories) . " categories, total: $totalCategories -->";
    error_log("Categories page: Extracted " . count($categories) . " categories, total: $totalCategories");
    
} catch (Exception $e) {
    error_log("Categories page FATAL ERROR: " . $e->getMessage() . " in " . $e->getFile() . " line " . $e->getLine());
    // Handle errors gracefully
    $result = $errorHandler->handleViewError($e, 'categories', []);
    $showErrorMessage = true;
    $errorMessage = $result['message'];
    
    // Use empty state data
    $emptyState = $viewDataService->handleEmptyState('categories');
    $categories = $emptyState['categories'];
    $pagination = $emptyState['pagination'];
    $categoryStats = $emptyState['stats'];
    $totalCategories = $emptyState['total_categories'];
    $displayedCount = $emptyState['displayed_count'];
}

// Calculate pagination display values
$offset = ($page - 1) * $perPage;
$totalPages = $pagination['last_page'] ?? 1;
$displayedCategories = $categories; // Already paginated by service
?>
<!-- Main Content -->
<div id="wrapper-container" class="wrapper-container">
    <div class="content-pusher">
        <div id="main-content">
            <div class="elementor elementor-15130">
                <?php 
                // Breadcrumb sẽ được hiển thị từ master layout
                ?>

                <!-- Main Categories Section -->
                <section class="categories-section">
                    <div class="container">
                        <div class="categories-layout">
                            <!-- Left Column - Categories -->
                            <div class="categories-main">
                                <!-- Header with Title and Filter Button -->
                                <div class="categories-header">
                                    <h1 class="page-title">Danh Mục</h1>
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
                                <div class="categories-topbar">
                                    <div class="results-count">
                                        <span>Hiển thị <?php echo ($pagination['from'] ?? 1); ?>-<?php echo ($pagination['to'] ?? $displayedCount); ?> trong <?php echo $totalCategories; ?> danh mục</span>
                                    </div>
                                    <div class="sort-dropdown">
                                        <form method="get">
                                            <?php if (isset($_GET['page'])): ?>
                                                <input type="hidden" name="page" value="<?php echo $page; ?>">
                                            <?php endif; ?>
                                            <select name="order_by" class="sort-select" onchange="this.form.submit()">
                                                <option value="name" <?php echo $orderBy === 'name' ? 'selected' : ''; ?>>Tên A-Z</option>
                                                <option value="name_desc" <?php echo $orderBy === 'name_desc' ? 'selected' : ''; ?>>Tên Z-A</option>
                                                <option value="course_count" <?php echo $orderBy === 'course_count' ? 'selected' : ''; ?>>Nhiều sản phẩm nhất</option>
                                                <option value="course_count_desc" <?php echo $orderBy === 'course_count_desc' ? 'selected' : ''; ?>>Ít sản phẩm nhất</option>
                                                <option value="popular" <?php echo $orderBy === 'popular' ? 'selected' : ''; ?>>Phổ biến nhất</option>
                                            </select>
                                        </form>
                                    </div>
                                </div>

                                <!-- Categories Grid -->
                                <div class="categories-grid">
                                    <?php if ($showErrorMessage): ?>
                                        <div class="error-message">
                                            <p><?php echo htmlspecialchars($errorMessage); ?></p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (empty($displayedCategories)): ?>
                                        <div class="no-categories">
                                            <p>Chưa có danh mục nào được tạo.</p>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($displayedCategories as $category): ?>
                                        <!-- Category Item -->
                                        <div class="category-item">
                                            <div class="category-tag-wrapper">
                                                <a href="#" class="category-tag"><?php echo htmlspecialchars($category['status'] ?? 'Phổ biến'); ?></a>
                                            </div>
                                            <div class="category-image">
                                                <a href="?page=products&category=<?php echo $category['id']; ?>">
                                                    <img src="<?php echo !empty($category['image']) ? htmlspecialchars($category['image']) : 'https://eduma.thimpress.com/demo-marketplace/wp-content/uploads/sites/99/2022/11/create-an-lms-website-with-learnpress-4-675x450.png'; ?>" 
                                                         alt="<?php echo htmlspecialchars($category['name']); ?>" loading="lazy">
                                                </a>
                                            </div>
                                            <div class="category-content">
                                                <h3 class="category-title">
                                                    <a href="?page=products&category=<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></a>
                                                </h3>
                                                <div class="category-description">
                                                    <?php echo htmlspecialchars($category['description'] ?? 'Mô tả danh mục sẽ được cập nhật sớm.'); ?>
                                                </div>
                                                <div class="category-meta">
                                                    <div class="course-count">
                                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M2 4H14M2 8H14M2 12H10" stroke="#6c757d" stroke-width="1.5" stroke-linecap="round"/>
                                                        </svg>
                                                        <span><?php echo ($category['products_count'] ?? 0); ?> Sản phẩm</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>

                                <!-- Pagination -->
                                <?php if ($totalPages > 1): ?>
                                <div class="pagination-wrapper">
                                    <nav class="pagination">
                                        <?php if ($page > 1): ?>
                                            <a href="?page=<?php echo $page - 1; ?><?php echo $orderBy !== 'name' ? '&order_by=' . $orderBy : ''; ?>" class="page-link prev">
                                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M12.5 15L7.5 10L12.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php
                                        // Show page numbers
                                        $startPage = max(1, $page - 2);
                                        $endPage = min($totalPages, $page + 2);
                                        
                                        for ($i = $startPage; $i <= $endPage; $i++):
                                        ?>
                                            <a href="?page=<?php echo $i; ?><?php echo $orderBy !== 'name' ? '&order_by=' . $orderBy : ''; ?>" 
                                               class="page-link <?php echo $i === $page ? 'active' : ''; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $totalPages): ?>
                                            <a href="?page=<?php echo $page + 1; ?><?php echo $orderBy !== 'name' ? '&order_by=' . $orderBy : ''; ?>" class="page-link next">
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
                            <div class="categories-sidebar" id="categoriesSidebar">
                                <div class="sidebar-header">
                                    <h3>Bộ Lọc</h3>
                                    <button class="sidebar-close" id="sidebarClose">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div class="sidebar-content">
                                    <!-- Category Type Filter -->
                                    <div class="filter-section">
                                        <h3 class="filter-title">Loại Danh Mục</h3>
                                        <div class="filter-content">
                                            <ul class="category-type-list">
                                                <li><a href="?page=categories">Tất Cả Danh Mục</a> <span class="count">(<?php echo $categoryStats['total']; ?>)</span></li>
                                                <li><a href="?page=categories&order_by=popular">Phổ Biến Nhất</a> <span class="count">(<?php echo $categoryStats['with_products']; ?>)</span></li>
                                                <li><a href="?page=categories&order_by=course_count">Nhiều Sản Phẩm</a> <span class="count">(<?php echo $categoryStats['with_products']; ?>)</span></li>
                                                <li><a href="?page=categories&status=active">Danh Mục Hoạt Động</a> <span class="count">(<?php echo $categoryStats['active']; ?>)</span></li>
                                                <li><a href="?page=categories&parent_only=1">Danh Mục Chính</a> <span class="count">(<?php echo $categoryStats['parent_categories']; ?>)</span></li>
                                            </ul>
                                        </div>
                                    </div>

                                    <!-- Reset Button -->
                                    <div class="filter-section">
                                        <button class="reset-filters-btn">Đặt Lại</button>
                                    </div>

                                    <!-- Course Count Filter -->
                                    <div class="filter-section">
                                        <h3 class="filter-title">Số Lượng Sản Phẩm</h3>
                                        <div class="filter-content">
                                            <ul class="course-count-list">
                                                <?php
                                                // Count categories by product ranges using service data
                                                $ranges = [
                                                    '10+' => 0,
                                                    '20+' => 0,
                                                    '30+' => 0
                                                ];
                                                
                                                foreach ($categories as $cat) {
                                                    $count = $cat['products_count'] ?? 0;
                                                    if ($count >= 30) $ranges['30+']++;
                                                    if ($count >= 20) $ranges['20+']++;
                                                    if ($count >= 10) $ranges['10+']++;
                                                }
                                                ?>
                                                <li><a href="#">10+ Sản Phẩm</a> <span class="count">(<?php echo $ranges['10+']; ?>)</span></li>
                                                <li><a href="#">20+ Sản Phẩm</a> <span class="count">(<?php echo $ranges['20+']; ?>)</span></li>
                                                <li><a href="#">30+ Sản Phẩm</a> <span class="count">(<?php echo $ranges['30+']; ?>)</span></li>
                                            </ul>
                                        </div>
                                    </div>

                                    <!-- Apply Button -->
                                    <div class="filter-section">
                                        <button class="apply-filters-btn">Áp Dụng</button>
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