<?php
/**
 * Debug Category Tree Building
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Category Tree Building Debug</h1>";

try {
    require_once __DIR__ . '/core/view_init.php';
    require_once __DIR__ . '/app/services/FilterConfigService.php';
    
    $filterService = new FilterConfigService();
    $categories = $filterService->getCategoriesForFilter();
    
    echo "<h2>Raw Categories Data</h2>";
    echo "Total categories: " . count($categories) . "<br>";
    
    // Group by parent
    $categoryByParent = [];
    foreach ($categories as $category) {
        $parentKey = $category['parent_id'] ?? 0;
        $categoryByParent[$parentKey][] = $category;
    }
    
    echo "<h2>CategoryByParent Structure</h2>";
    echo "<pre>" . print_r($categoryByParent, true) . "</pre>";
    
    // Build tree
    $buildCategoryTree = function ($parentId = 0, $depth = 0) use (&$buildCategoryTree, $categoryByParent) {
        $nodes = $categoryByParent[$parentId] ?? [];
        
        echo "<h3>Building tree for parentId: $parentId</h3>";
        echo "Found " . count($nodes) . " nodes<br>";
        
        usort($nodes, function ($a, $b) {
            $sortA = (int)($a['sort_order'] ?? 0);
            $sortB = (int)($b['sort_order'] ?? 0);
            if ($sortA === $sortB) {
                return strcmp((string)($a['name'] ?? ''), (string)($b['name'] ?? ''));
            }
            return $sortA <=> $sortB;
        });

        foreach ($nodes as &$node) {
            echo "<h4>Processing node: {$node['name']} (ID: {$node['id']})</h4>";
            $node['depth'] = $depth;
            $node['children'] = $buildCategoryTree($node['id'], $depth + 1);
            echo "Children count for {$node['name']}: " . count($node['children']) . "<br>";
        }
        return $nodes;
    };
    
    $categoryTree = $buildCategoryTree(0, 0);
    
    echo "<h2>Final Category Tree</h2>";
    echo "<pre>" . print_r($categoryTree, true) . "</pre>";
    
    echo "<h2>Test renderCategoryTree Function</h2>";
    if (function_exists('renderCategoryTree')) {
        $html = renderCategoryTree($categoryTree, []);
        echo "HTML length: " . strlen($html) . "<br>";
        echo "HTML preview:<br>";
        echo "<pre>" . htmlspecialchars(substr($html, 0, 1000)) . "</pre>";
    } else {
        echo "❌ renderCategoryTree function not found<br>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Error: " . $e->getMessage() . "</h2>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
