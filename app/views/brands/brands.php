<?php
/**
 * Brands Page - Public View
 * Hiển thị danh sách thương hiệu cho người dùng - Giao diện đồng bộ với products
 */

// Khởi tạo View & ServiceManager
require_once __DIR__ . '/../../../core/view_init.php';

// Chọn service phù hợp
$service = isset($currentService) ? $currentService : ($publicService ?? null);

// Get pagination and sorting parameters
$page = isset($_GET['p']) ? (int) $_GET['p'] : 1;
$page = max(1, $page);
$perPage = 12;
$orderBy = $_GET['order_by'] ?? 'name';
$minProducts = $_GET['min_products'] ?? '';

// Initialize data variables
$brands = [];
$pagination = [];
$totalBrands = 0;
$showErrorMessage = false;
$errorMessage = '';

try {
    // Get brands data
    if ($service && method_exists($service, 'getBrandsPageData')) {
        $brandsData = $service->getBrandsPageData($page, $perPage, $orderBy, $minProducts);
    } else {
        // Fallback: get from BrandsModel directly
        require_once __DIR__ . '/../../models/BrandsModel.php';
        $brandsModel = new BrandsModel();
        $allBrands = $brandsModel->getActive();
        
        // Get product counts
        require_once __DIR__ . '/../../models/ProductsModel.php';
        $productsModel = new ProductsModel();
        
        foreach ($allBrands as &$brand) {
            $productCountSql = "SELECT COUNT(*) as count FROM products WHERE brand_id = ? AND status = 'active'";
            $countResult = $productsModel->query($productCountSql, [$brand['id']]);
            $brand['product_count'] = $countResult[0]['count'] ?? 0;
        }
        unset($brand);
        
        // Filter by min_products if set
        if ($minProducts) {
            $allBrands = array_filter($allBrands, function($b) use ($minProducts) {
                return ($b['product_count'] ?? 0) >= (int)$minProducts;
            });
            $allBrands = array_values($allBrands);
        }
        
        // Sort
        if ($orderBy === 'name_desc') {
            usort($allBrands, fn($a, $b) => strcmp($b['name'], $a['name']));
        } elseif ($orderBy === 'product_count') {
            usort($allBrands, fn($a, $b) => ($b['product_count'] ?? 0) <=> ($a['product_count'] ?? 0));
        } elseif ($orderBy === 'popular') {
            usort($allBrands, fn($a, $b) => ($b['product_count'] ?? 0) <=> ($a['product_count'] ?? 0));
        } else {
            usort($allBrands, fn($a, $b) => strcmp($a['name'], $b['name']));
        }
        
        // Pagination
        $totalBrands = count($allBrands);
        $totalPages = ceil($totalBrands / $perPage);
        $offset = ($page - 1) * $perPage;
        $brands = array_slice($allBrands, $offset, $perPage);
        
        $brandsData = [
            'brands' => $brands,
            'pagination' => [
                'current_page' => $page,
                'last_page' => $totalPages,
                'total' => $totalBrands,
                'from' => $totalBrands > 0 ? $offset + 1 : 0,
                'to' => min($offset + $perPage, $totalBrands)
            ]
        ];
    }
    
    // Extract data
    $brands = $brandsData['brands'] ?? [];
    $pagination = $brandsData['pagination'] ?? [];
    $totalBrands = $pagination['total'] ?? count($brands);
    
} catch (Exception $e) {
    $result = $errorHandler->handleViewError($e, 'brands', []);
    $showErrorMessage = true;
    $errorMessage = $result['message'];
    
    $brands = [];
    $pagination = ['current_page' => 1, 'total' => 0, 'last_page' => 1, 'from' => 0, 'to' => 0];
    $totalBrands = 0;
}

// Calculate display values
$totalPages = $pagination['last_page'] ?? 1;
$fromCount = $pagination['from'] ?? 0;
$toCount = $pagination['to'] ?? 0;

// getBrandImage is defined globally in core/functions.php

// Helper function for sort options
if (!function_exists('getBrandSortOptions')) {
    function getBrandSortOptions() {
        return [
            'name' => 'Tên A-Z',
            'name_desc' => 'Tên Z-A',
            'product_count' => 'Nhiều sản phẩm nhất',
            'popular' => 'Phổ biến nhất'
        ];
    }
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
                
                <!-- Main Brands Section -->
                <section class="products-section">
                    <div class="container">
                        <div class="products-layout">
                            <!-- Left Column - Brands -->
                            <div class="products-main">
                                <!-- Header with Title and Filter Button -->
                                <div class="products-header">
                                    <h1 class="page-title">Thương hiệu</h1>
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
                                        <?php if ($totalBrands > 0): ?>
                                            <span>Hiển thị <?php echo $fromCount; ?>-<?php echo $toCount; ?> trong tổng số <?php echo $totalBrands; ?> thương hiệu</span>
                                        <?php else: ?>
                                            <span>Không tìm thấy thương hiệu nào</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="sort-dropdown">
                                        <form method="get">
                                            <input type="hidden" name="page" value="brands">
                                            <?php if ($minProducts): ?>
                                                <input type="hidden" name="min_products" value="<?php echo htmlspecialchars($minProducts); ?>">
                                            <?php endif; ?>
                                            <select name="order_by" class="sort-select" onchange="this.form.submit()">
                                                <?php foreach (getBrandSortOptions() as $value => $label): ?>
                                                    <option value="<?php echo $value; ?>" <?php echo $orderBy === $value ? 'selected' : ''; ?>>
                                                        <?php echo $label; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </form>
                                    </div>
                                </div>

                                <!-- Brands Grid -->
                                <div class="products-grid">
                                    <?php if (empty($brands)): ?>
                                        <div class="no-products">
                                            <p>Không tìm thấy thương hiệu nào.</p>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($brands as $brand): ?>
                                        <!-- Brand Item -->
                                        <div class="course-item">
                                            <div class="course-image">
                                                <a href="?page=products&brand=<?php echo $brand['id']; ?>">
                                                    <img src="<?php echo htmlspecialchars(getBrandImage($brand)); ?>" 
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
                                                    <?php echo !empty($brand['description']) ? htmlspecialchars(substr($brand['description'], 0, 100)) . (strlen($brand['description']) > 100 ? '...' : '') : 'Khám phá các sản phẩm từ thương hiệu này'; ?>
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
                                    <?php endif; ?>
                                </div>

                                <!-- Pagination -->
                                <?php if ($totalPages > 1): ?>
                                <div class="pagination-wrapper">
                                    <nav class="pagination">
                                        <?php if ($page > 1): ?>
                                            <?php 
                                            $getParams = $_GET;
                                            $getParams['page'] = 'brands';
                                            $getParams['p'] = $page - 1;
                                            unset($getParams['page_num']); // Remove old parameter
                                            ?>
                                            <a href="?<?php echo http_build_query($getParams); ?>" class="page-link prev">
                                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M12.5 15L7.5 10L12.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                            <?php 
                                            $getParams = $_GET;
                                            $getParams['page'] = 'brands';
                                            $getParams['p'] = $i;
                                            unset($getParams['page_num']); // Remove old parameter
                                            ?>
                                            <a href="?<?php echo http_build_query($getParams); ?>" 
                                               class="page-link <?php echo $i === $page ? 'active' : ''; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $totalPages): ?>
                                            <?php 
                                            $getParams = $_GET;
                                            $getParams['page'] = 'brands';
                                            $getParams['p'] = $page + 1;
                                            unset($getParams['page_num']); // Remove old parameter
                                            ?>
                                            <a href="?<?php echo http_build_query($getParams); ?>" class="page-link next">
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
                            <div class="products-sidebar" id="brandsSidebar">
                                <form method="get" action="" class="filter-form">
                                    <input type="hidden" name="page" value="brands">
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
                                        <!-- Product Count Filter -->
                                        <div class="filter-section">
                                            <h3 class="filter-title">
                                                <div class="title-icon-wrapper"><i class="fas fa-boxes"></i> Số lượng sản phẩm</div>
                                                <i class="fas fa-chevron-down chevron-icon"></i>
                                            </h3>
                                            <div class="filter-content">
                                                <ul class="category-list">
                                                     <li class="category-item <?php echo $minProducts === '5' ? 'active' : ''; ?>">
                                                         <div class="category-item-content">
                                                             <label class="filter-item-label">
                                                                 <input type="checkbox" name="min_products" class="filter-checkbox-single" value="5" 
                                                                        <?php echo $minProducts === '5' ? 'checked' : ''; ?>>
                                                                 <span class="custom-checkbox"></span>
                                                                 <span class="category-label">5+ sản phẩm</span>
                                                             </label>
                                                         </div>
                                                     </li>
                                                     <li class="category-item <?php echo $minProducts === '10' ? 'active' : ''; ?>">
                                                         <div class="category-item-content">
                                                             <label class="filter-item-label">
                                                                 <input type="checkbox" name="min_products" class="filter-checkbox-single" value="10" 
                                                                        <?php echo $minProducts === '10' ? 'checked' : ''; ?>>
                                                                 <span class="custom-checkbox"></span>
                                                                 <span class="category-label">10+ sản phẩm</span>
                                                             </label>
                                                         </div>
                                                     </li>
                                                     <li class="category-item <?php echo $minProducts === '20' ? 'active' : ''; ?>">
                                                         <div class="category-item-content">
                                                             <label class="filter-item-label">
                                                                 <input type="checkbox" name="min_products" class="filter-checkbox-single" value="20" 
                                                                        <?php echo $minProducts === '20' ? 'checked' : ''; ?>>
                                                                 <span class="custom-checkbox"></span>
                                                                 <span class="category-label">20+ sản phẩm</span>
                                                             </label>
                                                         </div>
                                                     </li>
                                                 </ul>
                                            </div>
                                        </div>

                                        <!-- Actions -->
                                        <div class="filter-actions">
                                            <button type="submit" class="apply-filters-btn">Áp dụng bộ lọc</button>
                                            <button type="button" class="reset-filters-btn" onclick="window.location.href='?page=brands'">Đặt lại</button>
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
