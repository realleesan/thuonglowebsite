<?php
/**
 * Debug Template Variables Directly
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Template Variables Debug</h1>";

try {
    // Simulate exact products.php environment
    require_once __DIR__ . '/core/view_init.php';
    require_once __DIR__ . '/app/services/FilterConfigService.php';
    
    $filterService = new FilterConfigService();
    
    // Get exact same data as products.php
    $categories = $filterService->getCategoriesForFilter();
    $brands = $filterService->getBrandsForFilter();
    $price_ranges = $filterService->getPriceRangesForFilter();
    
    $config_result = $filterService->getFilterConfig();
    $filter_config = $config_result['success'] ? $config_result['data'] : [];
    
    // Build category tree (exact same logic)
    $hierarchicalCategories = [];
    $categoryTree = [];
    if (!empty($categories)) {
        $categoryByParent = [];
        foreach ($categories as $category) {
            $parentKey = $category['parent_id'] ?? null;
            $categoryByParent[$parentKey][] = $category;
        }
        
        echo "<h2>CategoryByParent Debug</h2>";
        echo "<pre>" . print_r($categoryByParent, true) . "</pre>";
        echo "Looking for parent_id = null: " . (isset($categoryByParent[null]) ? "FOUND" : "NOT FOUND") . "<br>";
        echo "Looking for parent_id = 0: " . (isset($categoryByParent[0]) ? "FOUND" : "NOT FOUND") . "<br>";
        
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
    
    // Get criteria order and enabled status (exact same logic)
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
    
    echo "<h2>Template Variables Check</h2>";
    echo "criteria_order: <pre>" . print_r($criteria_order, true) . "</pre>";
    echo "criteria_enabled: <pre>" . print_r($criteria_enabled, true) . "</pre>";
    
    echo "<h2>Category Tree Check</h2>";
    echo "CategoryTree items: " . count($categoryTree) . "<br>";
    if (!empty($categoryTree)) {
        echo "<pre>" . print_r($categoryTree, true) . "</pre>";
    }
    
    echo "<h2>Template Logic Simulation</h2>";
    
    // Simulate exact template logic
    asort($criteria_order);
    echo "Sorted criteria order: <pre>" . print_r($criteria_order, true) . "</pre>";
    
    foreach ($criteria_order as $criteria_name => $order) {
        echo "<h3>Checking: $criteria_name</h3>";
        echo "Enabled: " . ($criteria_enabled[$criteria_name] ?? false ? "YES" : "NO") . "<br>";
        
        if ($criteria_name === 'categories' && ($criteria_enabled['categories'] ?? true)) {
            echo "✅ Categories section should render<br>";
            echo "CategoryTree empty: " . (empty($categoryTree) ? "YES - PROBLEM!" : "NO") . "<br>";
            
            if (!empty($categoryTree)) {
                echo "✅ Categories will be rendered<br>";
                // Test renderCategoryTree function
                if (function_exists('renderCategoryTree')) {
                    $html = renderCategoryTree($categoryTree, []);
                    echo "HTML length: " . strlen($html) . "<br>";
                    echo "HTML preview: " . substr($html, 0, 200) . "...<br>";
                } else {
                    echo "❌ renderCategoryTree function not found<br>";
                }
            } else {
                echo "❌ CategoryTree is empty - this is the problem!<br>";
            }
        }
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Error: " . $e->getMessage() . "</h2>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
