<?php
/**
 * Debug Products Page by running actual products.php code
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', '512M');

echo "<h1>Full Products Page Debug</h1>";

// Simulate the exact same environment as products.php
try {
    echo "<h2>Step 1: Loading view_init.php</h2>";
    require_once __DIR__ . '/core/view_init.php';
    echo "✅ view_init.php loaded<br>";
    
    echo "<h2>Step 2: Check Service Variables</h2>";
    echo "currentService: " . (isset($currentService) ? get_class($currentService) : 'null') . "<br>";
    echo "publicService: " . (isset($publicService) ? get_class($publicService) : 'null') . "<br>";
    
    echo "<h2>Step 3: Service Selection Logic</h2>";
    $service = isset($currentService) ? $currentService : ($publicService ?? null);
    echo "Selected service: " . ($service ? get_class($service) : 'null') . "<br>";
    
    // Debug: Check service availability
    if (!$service) {
        echo "❌ No service available - creating fallback<br>";
        try {
            require_once __DIR__ . '/app/services/PublicService.php';
            $service = new PublicService();
            echo "✅ Created fallback PublicService: " . get_class($service) . "<br>";
        } catch (Exception $e) {
            echo "❌ Failed to create fallback service: " . $e->getMessage() . "<br>";
            $service = null;
        }
    }
    
    if ($service) {
        echo "✅ Service available: " . get_class($service) . "<br>";
        echo "Available methods: " . implode(', ', get_class_methods($service)) . "<br>";
    }
    
    echo "<h2>Step 4: Parameter Processing</h2>";
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
    
    echo "Parameters processed successfully<br>";
    
    echo "<h2>Step 5: Building Filters Array</h2>";
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
    echo "✅ Filters array built<br>";
    
    echo "<h2>Step 6: Loading Product Data</h2>";
    $productData = [];
    if ($service && method_exists($service, 'getProductListingData')) {
        echo "Trying getProductListingData...<br>";
        $productData = $service->getProductListingData($filters);
        echo "✅ getProductListingData success<br>";
    } else {
        echo "❌ getProductListingData not found<br>";
        // Try alternative method
        if ($service && method_exists($service, 'getProducts')) {
            echo "Trying getProducts...<br>";
            $productData = $service->getProducts($filters);
            echo "✅ getProducts success<br>";
        } else {
            echo "❌ No suitable product loading method found<br>";
            $productData = ['products' => [], 'pagination' => ['total' => 0, 'current_page' => 1, 'per_page' => 12]];
        }
    }
    
    $products = $productData['products'] ?? [];
    $pagination = $productData['pagination'] ?? [];
    $totalProducts = $pagination['total'] ?? 0;
    
    echo "✅ Product data loaded: " . count($products) . " products<br>";
    
    echo "<h2>Step 7: Loading Filter Configuration</h2>";
    require_once __DIR__ . '/app/services/FilterConfigService.php';
    $filterService = new FilterConfigService();
    
    $categories = $filterService->getCategoriesForFilter();
    $brands = $filterService->getBrandsForFilter();
    $price_ranges = $filterService->getPriceRangesForFilter();
    
    $config_result = $filterService->getFilterConfig();
    $filter_config = $config_result['success'] ? $config_result['data'] : [];
    
    echo "✅ Filter config loaded<br>";
    echo "Categories: " . count($categories) . "<br>";
    echo "Brands: " . count($brands) . "<br>";
    echo "Price ranges: " . count($price_ranges) . "<br>";
    
    echo "<h2>Step 8: Building Category Tree</h2>";
    $hierarchicalCategories = [];
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
        echo "✅ Category tree built<br>";
    } else {
        $categoryTree = [];
        echo "⚠️ No categories found<br>";
    }
    
    echo "<h2>✅ All Steps Completed Successfully!</h2>";
    echo "<p>The issue might be in the HTML template rendering part.</p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Exception: " . $e->getMessage() . "</h2>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} catch (Error $e) {
    echo "<h2>❌ Fatal Error: " . $e->getMessage() . "</h2>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
