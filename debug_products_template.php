<?php
/**
 * Debug Products Template Rendering
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', '512M');

echo "<h1>Products Template Debug</h1>";

try {
    // Load all data like products.php
    require_once __DIR__ . '/core/view_init.php';
    
    $service = isset($currentService) ? $currentService : ($publicService ?? null);
    if (!$service) {
        require_once __DIR__ . '/app/services/PublicService.php';
        $service = new PublicService();
    }
    
    // Process parameters
    $page = isset($_GET['p']) ? (int) $_GET['p'] : 1;
    $page = max(1, $page);
    $limit = 12;
    
    $categoryId = $_GET['category'] ?? [];
    if (!is_array($categoryId)) {
        if ($categoryId === '' || $categoryId === 'null' || $categoryId === null) {
            $categoryId = [];
        } else {
            $categoryId = [(int) $categoryId];
        }
    } else {
        $categoryId = array_map('intval', array_filter($categoryId));
    }
    
    $brandId = $_GET['brand'] ?? [];
    if (!is_array($brandId)) {
        if ($brandId === '' || $brandId === 'null' || $brandId === null) {
            $brandId = [];
        } else {
            $brandId = [(int) $brandId];
        }
    } else {
        $brandId = array_map('intval', array_filter($brandId));
    }
    
    $orderBy = $_GET['order_by'] ?? 'post_date';
    $search = $_GET['search'] ?? '';
    $minPriceInput = trim((string)($_GET['min_price'] ?? ''));
    $maxPriceInput = trim((string)($_GET['max_price'] ?? ''));
    $minPrice = ($minPriceInput !== '' && is_numeric($minPriceInput)) ? (float)$minPriceInput : null;
    $maxPrice = ($maxPriceInput !== '' && is_numeric($maxPriceInput)) ? (float)$maxPriceInput : null;
    
    $filters = [
        'category_id' => $categoryId,
        'brand_id' => $brandId,
        'page' => $page,
        'per_page' => $limit,
        'order_by' => $orderBy,
        'search' => $search,
        'min_price' => $minPrice,
        'max_price' => $maxPrice
    ];
    
    // Get product data
    $productData = $service->getProductListingData($filters);
    $products = $productData['products'] ?? [];
    $pagination = $productData['pagination'] ?? [];
    $totalProducts = $pagination['total'] ?? 0;
    
    // Get filter data
    require_once __DIR__ . '/app/services/FilterConfigService.php';
    $filterService = new FilterConfigService();
    
    $categories = $filterService->getCategoriesForFilter();
    $brands = $filterService->getBrandsForFilter();
    $price_ranges = $filterService->getPriceRangesForFilter();
    
    $config_result = $filterService->getFilterConfig();
    $filter_config = $config_result['success'] ? $config_result['data'] : [];
    
    // Build category tree
    $hierarchicalCategories = [];
    $categoryTree = [];
    if (!empty($categories)) {
        $categoryByParent = [];
        foreach ($categories as $category) {
            $parentKey = $category['parent_id'] ?? null;
            $categoryByParent[$parentKey][] = $category;
        }
        
        $buildCategoryTree = function ($parentId = null, $depth = 0) use (&$buildCategoryTree, $categoryByParent) {
            $nodes = $categoryByParent[$parentId] ?? [];
            usort($nodes, function ($a, $b) {
                $sortA = (int)($a['sort_order'] ?? 0);
                $sortB = (int)($b['sort_order'] ?? 0);
                if ($sortA === $sortB) {
                    return strcmp((string)($a['name'] ?? ''), (string)($b['name'] ?? ''));
                }
                return $sortA <=> $sortB;
            });
            
            foreach ($nodes as &$node) {
                $node['depth'] = $depth;
                $node['children'] = $buildCategoryTree($node['id'], $depth + 1);
            }
            return $nodes;
        };
        
        $categoryTree = $buildCategoryTree(null, 0);
    }
    
    // Get criteria order and enabled status
    $criteria_order = [
        'categories' => $filter_config['criteria']['categories']['order'] ?? 1,
        'brands' => $filter_config['criteria']['brands']['order'] ?? 2, 
        'price_ranges' => $filter_config['criteria']['price_ranges']['order'] ?? 3
    ];
    
    $criteria_enabled = [
        'categories' => $filter_config['criteria']['categories']['enabled'] ?? true,
        'brands' => $filter_config['criteria']['brands']['enabled'] ?? true,
        'price_ranges' => $filter_config['criteria']['price_ranges']['enabled'] ?? true
    ];
    
    echo "<h2>✅ All Data Loaded Successfully</h2>";
    echo "Products: " . count($products) . "<br>";
    echo "Categories: " . count($categories) . "<br>";
    echo "Brands: " . count($brands) . "<br>";
    
    echo "<h2>Testing Template Functions</h2>";
    
    // Test renderCategoryTree function
    if (!function_exists('renderCategoryTree')) {
        echo "❌ renderCategoryTree function not defined<br>";
        
        // Define it here for testing
        function renderCategoryTree($nodes, $activeIds, $isSub = false) {
            $activeIds = is_array($activeIds) ? $activeIds : ($activeIds ? [$activeIds] : []);
            $html = '<ul class="' . ($isSub ? 'sub-categories' : 'category-list') . '">';
            foreach ($nodes as $node) {
                // Skip disabled categories
                if (!($node['enabled'] ?? true)) {
                    continue;
                }
                $hasChildren = !empty($node['children']);
                $isActive = in_array((int)$node['id'], $activeIds);
                
                $itemClass = 'category-item';
                if ($hasChildren) $itemClass .= ' has-children';
                if ($isActive) $itemClass .= ' active';
                
                $html .= '<li class="' . $itemClass . '" data-id="' . $node['id'] . '">';
                $html .= '<div class="category-header-wrapper">';
                $html .= '<div class="category-item-content">';
                $html .= '<label class="filter-item-label">';
                $html .= '<input type="checkbox" name="category[]" value="' . $node['id'] . '" ' . ($isActive ? 'checked' : '') . '>';
                $html .= '<span class="custom-checkbox"></span>';
                $html .= '<span class="category-label">' . htmlspecialchars($node['name']) . '</span>';
                $html .= '</label>';
                $html .= '<span class="category-count">' . ($node['product_count'] ?? 0) . '</span>';
                $html .= '</div>';
                
                if ($hasChildren) {
                    $html .= '<span class="toggle-sub"><i class="fas fa-chevron-down"></i></span>';
                }
                
                $html .= '</div>';
                
                if ($hasChildren) {
                    $html .= '<div class="sub-categories-wrapper">';
                    $html .= renderCategoryTree($node['children'], $activeIds, true);
                    $html .= '</div>';
                }
                
                $html .= '</li>';
            }
            $html .= '</ul>';
            return $html;
        }
        echo "✅ renderCategoryTree function defined<br>";
    } else {
        echo "✅ renderCategoryTree function exists<br>";
    }
    
    echo "<h2>Testing Template Variables</h2>";
    echo "criteria_order defined: " . (isset($criteria_order) ? 'yes' : 'no') . "<br>";
    echo "criteria_enabled defined: " . (isset($criteria_enabled) ? 'yes' : 'no') . "<br>";
    echo "categoryTree defined: " . (isset($categoryTree) ? 'yes' : 'no') . "<br>";
    echo "brands defined: " . (isset($brands) ? 'yes' : 'no') . "<br>";
    
    echo "<h2>Testing Small Template Section</h2>";
    
    // Test a small section of the template
    ob_start();
    ?>
    <div class="test-section">
        <h3>Test Template</h3>
        <?php if (!empty($categoryTree)): ?>
            <p>Category tree has items</p>
            <?php echo renderCategoryTree($categoryTree, $categoryId); ?>
        <?php else: ?>
            <p>No categories</p>
        <?php endif; ?>
    </div>
    <?php
    $template_output = ob_get_clean();
    echo "✅ Template section rendered successfully<br>";
    echo "Output length: " . strlen($template_output) . " characters<br>";
    
    echo "<h2>✅ Template Test Completed Successfully!</h2>";
    echo "<p>The issue might be in the full products.php template or a specific section.</p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Template Exception: " . $e->getMessage() . "</h2>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} catch (Error $e) {
    echo "<h2>❌ Template Fatal Error: " . $e->getMessage() . "</h2>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
