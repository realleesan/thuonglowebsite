<?php
/**
 * Categories Page - Dynamic Version
 */

// 1. Khởi tạo View an toàn & ServiceManager
require_once __DIR__ . '/../../../core/view_init.php';

// Chọn service phù hợp cho categories (ưu tiên inject từ routing)
$service = isset($currentService) ? $currentService : ($publicService ?? null);

// Get pagination and sorting parameters
$page = isset($_GET['p']) ? (int) $_GET['p'] : 1;
$page = max(1, $page);
$perPage = 1000;
$orderBy = $_GET['order_by'] ?? 'name';

// Get filter parameters
$minProducts = $_GET['min_products'] ?? null;
$filters = [];
if ($minProducts !== null && is_numeric($minProducts)) {
    $filters['min_products'] = (int)$minProducts;
}

// Initialize data variables
$categories = [];
$pagination = [];
$categoryStats = [];
$totalCategories = 0;
$displayedCount = 0;
$showErrorMessage = false;
$errorMessage = '';

try {
    // Get categories page data (Always request page 1 to load all items for hierarchy grouping)
    if ($service && method_exists($service, 'getCategoriesPageData')) {
        $categoriesData = $service->getCategoriesPageData(1, $perPage, $orderBy, $filters);
    } else {
        $categoriesData = [];
    }
    
    // Extract data
    $categories = $categoriesData['categories'] ?? [];
    $pagination = $categoriesData['pagination'] ?? [];
    $categoryStats = $categoriesData['stats'] ?? [];
    $totalCategories = $categoriesData['total_categories'] ?? 0;
    $displayedCount = $categoriesData['displayed_count'] ?? 0;
    
    // Debug: Data extracted
    echo "<!-- Debug: Extracted " . count($categories) . " categories, total: $totalCategories -->";
    foreach ($categories as $cat) {
        echo "<!-- DEBUG cat: {$cat['name']} = {$cat['product_count']} -->";
    }
    error_log("Categories page: Extracted " . count($categories) . " categories, total: $totalCategories");
    
} catch (Exception $e) {
    // Handle errors gracefully
    $result = $errorHandler->handleViewError($e, 'categories', []);
    $showErrorMessage = true;
    $errorMessage = $result['message'];
    
    // Use empty state data an toàn
    $categories = [];
    $pagination = ['current_page' => 1, 'total' => 0, 'last_page' => 1, 'from' => 0, 'to' => 0];
    $categoryStats = ['total' => 0, 'active' => 0, 'parent_categories' => 0, 'with_products' => 0];
    $totalCategories = 0;
    $displayedCount = 0;
}

// Calculate pagination display values
$offset = ($page - 1) * $perPage;
$totalPages = $pagination['last_page'] ?? 1;
$displayedCategories = $categories; // Already paginated by service

// Group child categories under parent categories using recursive ultimate-root tracing
$allCategoriesMap = [];
foreach ($displayedCategories as $cat) {
    $allCategoriesMap[$cat['id']] = $cat;
}

// Function to find the ultimate root category ID of a category
if (!function_exists('getUltimateRootParentId')) {
    function getUltimateRootParentId($catId, $allCategoriesMap) {
        $current = $allCategoriesMap[$catId] ?? null;
        if (!$current) {
            return $catId;
        }
        
        $parentId = $current['parent_id'] ?? null;
        if (empty($parentId)) {
            return $catId;
        }
        
        $visited = [$catId];
        while (!empty($parentId)) {
            if (in_array($parentId, $visited)) {
                break; // prevent circular reference loops
            }
            $visited[] = $parentId;
            if (!isset($allCategoriesMap[$parentId])) {
                return $parentId;
            }
            $current = $allCategoriesMap[$parentId];
            $nextParentId = $current['parent_id'] ?? null;
            if (empty($nextParentId)) {
                return $parentId;
            }
            $parentId = $nextParentId;
        }
        return $parentId;
    }
}

// Function to get the display path relative to the root parent
if (!function_exists('getCategoryDisplayPath')) {
    function getCategoryDisplayPath($catId, $allCategoriesMap, $rootId) {
        $pathNames = [];
        $currentId = $catId;
        $visited = [];
        
        while (!empty($currentId) && $currentId != $rootId) {
            if (in_array($currentId, $visited)) {
                break;
            }
            $visited[] = $currentId;
            
            if (isset($allCategoriesMap[$currentId])) {
                $cat = $allCategoriesMap[$currentId];
                array_unshift($pathNames, $cat['name']);
                $currentId = $cat['parent_id'] ?? null;
            } else {
                break;
            }
        }
        
        return implode(' - ', $pathNames);
    }
}

// Function to recursively build a category tree branch from a flat descendant list
if (!function_exists('buildCategoryTreeFromList')) {
    function buildCategoryTreeFromList(array $list, $parentId) {
        $branch = [];
        foreach ($list as $cat) {
            $pId = $cat['parent_id'] ?? null;
            if ($pId == $parentId) {
                $children = buildCategoryTreeFromList($list, $cat['id']);
                $cat['children'] = $children;
                $branch[] = $cat;
            }
        }
        return $branch;
    }
}

// Recursive function to render a tree node as a collapsible row
if (!function_exists('renderCategoryTreeNode')) {
    function renderCategoryTreeNode($node, $level = 2) {
        $children = $node['children'] ?? [];
        $hasChildren = !empty($children);
        $nodeId = $node['id'];
        $nodeName = $node['name'];
        $nodeDesc = $node['description'] ?? '';
        $nodeImage = $node['image'] ?? '';
        $nodeCount = $node['product_count'] ?? 0;
        
        $levelClass = "category-node-level-" . $level;
        ?>
        <div class="category-tree-node <?php echo $levelClass; ?> <?php echo $hasChildren ? 'has-children' : ''; ?>" id="cat-node-<?php echo $nodeId; ?>">
            <div class="category-node-header">
                <div class="node-left">
                    <div class="node-image">
                        <a href="?page=products&category[]=<?php echo $nodeId; ?>">
                            <img src="<?php echo getCategoryImage($node); ?>" 
                                 alt="<?php echo htmlspecialchars($nodeName); ?>" loading="lazy">
                        </a>
                    </div>
                    <div class="node-info">
                        <h4 class="node-title">
                            <a href="?page=products&category[]=<?php echo $nodeId; ?>"><?php echo htmlspecialchars($nodeName); ?></a>
                        </h4>
                        <?php if (!empty($nodeDesc)): ?>
                            <p class="node-desc"><?php echo htmlspecialchars($nodeDesc); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="node-right">
                    <span class="node-count"><?php echo $nodeCount; ?> sản phẩm</span>
                    <?php if ($hasChildren): ?>
                        <button class="node-toggle" aria-label="Toggle subcategories">
                            <svg width="12" height="12" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if ($hasChildren): ?>
                <div class="category-node-content">
                    <div class="node-children-list">
                        <?php foreach ($children as $child): ?>
                            <?php renderCategoryTreeNode($child, $level + 1); ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
}


$rootCategoriesMap = [];
$childrenByRootMap = [];

// Identify root categories and group children by their ultimate root
foreach ($displayedCategories as $cat) {
    $rootId = getUltimateRootParentId($cat['id'], $allCategoriesMap);
    
    if ($cat['id'] == $rootId) {
        $rootCategoriesMap[$cat['id']] = $cat;
        if (!isset($childrenByRootMap[$cat['id']])) {
            $childrenByRootMap[$cat['id']] = [];
        }
    } else {
        // This is a subcategory (level 2, 3, etc.)
        // Ensure the root parent is initialized in our maps
        if (!isset($rootCategoriesMap[$rootId])) {
            $rootCategoriesMap[$rootId] = [
                'id' => $rootId,
                'name' => isset($allCategoriesMap[$rootId]) ? $allCategoriesMap[$rootId]['name'] : 'Danh mục gốc',
                'description' => isset($allCategoriesMap[$rootId]) ? $allCategoriesMap[$rootId]['description'] : '',
                'image' => isset($allCategoriesMap[$rootId]) ? $allCategoriesMap[$rootId]['image'] : '',
                'product_count' => isset($allCategoriesMap[$rootId]) ? $allCategoriesMap[$rootId]['product_count'] : 0,
            ];
            $childrenByRootMap[$rootId] = [];
        }
        
        $cat['display_path'] = getCategoryDisplayPath($cat['id'], $allCategoriesMap, $rootId);
        $childrenByRootMap[$rootId][] = $cat;
    }
}

// Rebuild sorted parent categories list keeping original order
$sortedParentCategories = [];
foreach ($displayedCategories as $cat) {
    if (isset($rootCategoriesMap[$cat['id']])) {
        $sortedParentCategories[] = $rootCategoriesMap[$cat['id']];
        unset($rootCategoriesMap[$cat['id']]);
    }
}
foreach ($rootCategoriesMap as $cat) {
    $sortedParentCategories[] = $cat;
}

// Perform View-level pagination for the 10 root parent categories per page
$perPageRoot = 10;
$totalRoots = count($sortedParentCategories);
$totalPages = (int) ceil($totalRoots / $perPageRoot);
if ($totalPages < 1) {
    $totalPages = 1;
}
$page = max(1, min($page, $totalPages));
$offsetRoot = ($page - 1) * $perPageRoot;
$parentCategoriesToShow = array_slice($sortedParentCategories, $offsetRoot, $perPageRoot);

// Calculate root categories pagination count for display
$fromCount = $totalRoots > 0 ? $offsetRoot + 1 : 0;
$toCount = min($offsetRoot + $perPageRoot, $totalRoots);
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
                                        <span>Hiển thị <?php echo $fromCount; ?>-<?php echo $toCount; ?> trong <?php echo $totalRoots; ?> danh mục</span>
                                    </div>
                                    <div class="sort-dropdown">
                                        <form method="get">
                                            <input type="hidden" name="page" value="categories">
                                            <?php if ($page > 1): ?>
                                                <input type="hidden" name="p" value="<?php echo $page; ?>">
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

                                <!-- Categories Accordion List -->
                                <div class="category-accordion-list">
                                    <?php if ($showErrorMessage): ?>
                                        <div class="error-message">
                                            <p><?php echo htmlspecialchars($errorMessage); ?></p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (empty($sortedParentCategories)): ?>
                                        <div class="no-categories">
                                            <p>Chưa có danh mục nào được tạo.</p>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($parentCategoriesToShow as $parent): 
                                            $children = buildCategoryTreeFromList($childrenByRootMap[$parent['id']] ?? [], $parent['id']);
                                        ?>
                                        <!-- Accordion Item -->
                                        <div class="category-accordion-item" id="cat-accordion-<?php echo $parent['id']; ?>">
                                            <div class="category-accordion-header">
                                                <div class="header-left">
                                                    <div class="parent-image">
                                                        <a href="?page=products&category[]=<?php echo $parent['id']; ?>">
                                                            <img src="<?php echo getCategoryImage($parent); ?>" 
                                                                 alt="<?php echo htmlspecialchars($parent['name']); ?>" loading="lazy">
                                                        </a>
                                                     </div>
                                                     <div class="parent-info">
                                                         <h3 class="parent-title">
                                                             <a href="?page=products&category[]=<?php echo $parent['id']; ?>"><?php echo htmlspecialchars($parent['name']); ?></a>
                                                         </h3>
                                                         <p class="parent-desc"><?php echo htmlspecialchars($parent['description'] ?? 'Mô tả danh mục sẽ được cập nhật sớm.'); ?></p>
                                                     </div>
                                                </div>
                                                <div class="header-right">
                                                    <div class="parent-meta">
                                                        <span class="product-badge">
                                                            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-right: 4px;">
                                                                <path d="M2 4H14M2 8H14M2 12H10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                            </svg>
                                                            <?php echo ($parent['product_count'] ?? 0); ?> sản phẩm
                                                        </span>
                                                    </div>
                                                    <?php if (!empty($children)): ?>
                                                    <button class="accordion-toggle" aria-label="Toggle subcategories">
                                                        <svg width="14" height="14" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                    </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            
                                            <?php if (!empty($children)): ?>
                                            <div class="category-accordion-content">
                                                <div class="subcategories-wrapper">
                                                    <div class="node-children-list">
                                                        <?php foreach ($children as $child): ?>
                                                            <?php renderCategoryTreeNode($child, 2); ?>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endif; ?>
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
                                            $getParams['page'] = 'categories';
                                            $getParams['p'] = $page - 1;
                                            unset($getParams['view']); // Remove view parameter
                                            ?>
                                            <a href="?<?php echo http_build_query($getParams); ?>" class="page-link prev">
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
                                            <?php 
                                            $getParams = $_GET;
                                            $getParams['page'] = 'categories';
                                            $getParams['p'] = $i;
                                            unset($getParams['view']); // Remove view parameter
                                            ?>
                                            <a href="?<?php echo http_build_query($getParams); ?>" 
                                               class="page-link <?php echo $i === $page ? 'active' : ''; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $totalPages): ?>
                                            <?php 
                                            $getParams = $_GET;
                                            $getParams['page'] = 'categories';
                                            $getParams['p'] = $page + 1;
                                            unset($getParams['view']); // Remove view parameter
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
                            <div class="categories-sidebar" id="categoriesSidebar">
                                <form method="get" action="" class="filter-form">
                                    <input type="hidden" name="page" value="categories">
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
                                        <!-- Course Count Filter (Radio Buttons) -->
                                        <div class="filter-section">
                                            <h3 class="filter-title">
                                                <div class="title-icon-wrapper"><i class="fas fa-layer-group"></i> Số lượng sản phẩm</div>
                                                <i class="fas fa-chevron-down chevron-icon"></i>
                                            </h3>
                                            <div class="filter-content">
                                                <ul class="category-list">
                                                    <?php
                                                    // Get current min_products filter
                                                    $currentMinProducts = $_GET['min_products'] ?? '';
                                                    
                                                    // Count categories by product ranges using service data
                                                    $ranges = [
                                                        '10+' => 0,
                                                        '20+' => 0,
                                                        '30+' => 0
                                                    ];
                                                    
                                                    foreach ($categories as $cat) {
                                                        $count = $cat['product_count'] ?? 0;
                                                        if ($count >= 30) $ranges['30+']++;
                                                        if ($count >= 20) $ranges['20+']++;
                                                        if ($count >= 10) $ranges['10+']++;
                                                    }
                                                    ?>
                                                    <li class="category-item <?php echo $currentMinProducts === '10' ? 'active' : ''; ?>">
                                                        <div class="category-item-content">
                                                            <label class="filter-item-label">
                                                                <input type="checkbox" name="min_products" class="filter-checkbox-single" value="10" <?php echo $currentMinProducts === '10' ? 'checked' : ''; ?>>
                                                                <span class="custom-checkbox"></span>
                                                                <span class="category-label">10+ Sản Phẩm</span>
                                                            </label>
                                                        </div>
                                                    </li>
                                                    <li class="category-item <?php echo $currentMinProducts === '20' ? 'active' : ''; ?>">
                                                        <div class="category-item-content">
                                                            <label class="filter-item-label">
                                                                <input type="checkbox" name="min_products" class="filter-checkbox-single" value="20" <?php echo $currentMinProducts === '20' ? 'checked' : ''; ?>>
                                                                <span class="custom-checkbox"></span>
                                                                <span class="category-label">20+ Sản Phẩm</span>
                                                            </label>
                                                        </div>
                                                    </li>
                                                    <li class="category-item <?php echo $currentMinProducts === '30' ? 'active' : ''; ?>">
                                                        <div class="category-item-content">
                                                            <label class="filter-item-label">
                                                                <input type="checkbox" name="min_products" class="filter-checkbox-single" value="30" <?php echo $currentMinProducts === '30' ? 'checked' : ''; ?>>
                                                                <span class="custom-checkbox"></span>
                                                                <span class="category-label">30+ Sản Phẩm</span>
                                                            </label>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>

                                        <!-- Actions -->
                                        <div class="filter-actions">
                                            <button type="submit" class="apply-filters-btn">Áp dụng bộ lọc</button>
                                            <button type="button" class="reset-filters-btn" onclick="window.location.href='?page=categories'">Đặt lại</button>
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