<?php
/**
 * Filter Comparison Test - Compare User vs Admin data
 */

// Initialize View & ServiceManager
require_once __DIR__ . '/core/view_init.php';

// Get services
$publicService = $publicService ?? null;
require_once __DIR__ . '/app/services/FilterConfigService.php';
$filterConfigService = new FilterConfigService();

echo "<!DOCTYPE html>
<html lang='vi'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>🔍 Filter Comparison Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; }
        .comparison-section { background: white; margin: 20px 0; padding: 20px; border-radius: 8px; }
        .section-title { font-size: 24px; font-weight: bold; margin-bottom: 15px; color: #333; }
        .side-by-side { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .user-side, .admin-side { padding: 15px; }
        .user-side { background: #e8f5e8; border-left: 4px solid #4caf50; }
        .admin-side { background: #e3f2fd; border-left: 4px solid #2196f3; }
        .side-title { font-weight: bold; margin-bottom: 10px; font-size: 18px; }
        .category-list { list-style: none; padding: 0; }
        .category-item { padding: 8px 12px; margin: 3px 0; background: white; border-radius: 4px; display: flex; justify-content: space-between; align-items: center; }
        .category-item.sub-item { margin-left: 20px; font-size: 14px; background: #f9f9f9; }
        .category-name { font-weight: 500; }
        .category-count { background: #2196f3; color: white; padding: 2px 8px; border-radius: 12px; font-size: 12px; }
        .brand-item { padding: 8px 12px; margin: 3px 0; background: white; border-radius: 4px; display: flex; justify-content: space-between; align-items: center; }
        .diff { background: #fff3cd !important; border-left: 4px solid #ffc107; }
        .missing { background: #f8d7da !important; border-left: 4px solid #dc3545; }
        .stats { background: #f8f9fa; padding: 10px; border-radius: 4px; margin-bottom: 10px; }
        .console { background: #263238; color: #aed581; padding: 15px; border-radius: 8px; font-family: monospace; font-size: 12px; max-height: 300px; overflow-y: auto; margin-top: 20px; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔍 Filter Data Comparison Test</h1>
        <p>So sánh data giữa trang User (sidebar) và trang Admin (filter config)</p>";

// Get User Data (same as products page)
echo "<div class='comparison-section'>
    <h2 class='section-title'>📦 Categories Comparison</h2>
    <div class='side-by-side'>
        <div class='user-side'>
            <div class='side-title'>👥 User Sidebar (Products Page)</div>
            <div class='stats'>";

// User Categories - exact same logic as products.php
$categoriesData = [];
if ($publicService && method_exists($publicService, 'getCategoriesWithProductCounts')) {
    $categoriesData = $publicService->getCategoriesWithProductCounts();
} else {
    echo "❌ Cannot get user categories data<br>";
}
$categories = $categoriesData['categories'] ?? [];

echo "DEBUG: Service exists: " . ($publicService ? 'YES' : 'NO') . "<br>";
echo "DEBUG: Method exists: " . (method_exists($publicService, 'getCategoriesWithProductCounts') ? 'YES' : 'NO') . "<br>";
echo "DEBUG: CategoriesData count: " . count($categoriesData) . "<br>";
echo "DEBUG: Categories count: " . count($categories) . "<br>";

if (empty($categories)) {
    echo "❌ No categories found - checking fallback<br>";
    // Fallback: get directly from model like products.php does
    require_once __DIR__ . '/app/models/CategoriesModel.php';
    $categoriesModel = new CategoriesModel();
    $categories = $categoriesModel->getWithProductCounts();
    echo "DEBUG: Fallback categories count: " . count($categories) . "<br>";
}

$categoryByParent = [];
foreach ($categories as $category) {
    $parentKey = $category['parent_id'] ?? null;
    if (!isset($categoryByParent[$parentKey])) {
        $categoryByParent[$parentKey] = [];
    }
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

$userCategoryTree = $buildCategoryTree(null, 0);

echo "Total: " . count($userCategoryTree) . " top-level categories</div>";

function renderUserCategoryTree($nodes, $level = 0) {
    echo "<ul class='category-list'>";
    foreach ($nodes as $node) {
        $indent = $level > 0 ? 'sub-item' : '';
        echo "<li class='category-item $indent'>
                <span class='category-name'>" . htmlspecialchars($node['name']) . "</span>
                <span class='category-count'>" . ($node['product_count'] ?? 0) . "</span>
              </li>";
        if (!empty($node['children'])) {
            renderUserCategoryTree($node['children'], $level + 1);
        }
    }
    echo "</ul>";
}

renderUserCategoryTree($userCategoryTree);

echo "</div>";

// Admin Categories
echo "<div class='admin-side'>
    <div class='side-title'>⚙️ Admin Filter Config</div>
    <div class='stats'>";

$adminCategories = $filterConfigService->getCategoriesForFilter();

echo "Total: " . count($adminCategories) . " top-level categories</div>";

function renderAdminCategoryTree($nodes, $level = 0) {
    echo "<ul class='category-list'>";
    foreach ($nodes as $node) {
        $indent = $level > 0 ? 'sub-item' : '';
        echo "<li class='category-item $indent'>
                <span class='category-name'>" . htmlspecialchars($node['name']) . "</span>
                <span class='category-count'>" . ($node['count'] ?? 0) . "</span>
              </li>";
        if (!empty($node['children'])) {
            renderAdminCategoryTree($node['children'], $level + 1);
        }
    }
    echo "</ul>";
}

renderAdminCategoryTree($adminCategories);

echo "</div></div></div>";

// Brands Comparison
echo "<div class='comparison-section'>
    <h2 class='section-title'>🏷️ Brands Comparison</h2>
    <div class='side-by-side'>
        <div class='user-side'>
            <div class='side-title'>👥 User Sidebar (Products Page)</div>
            <div class='stats'>";

// User Brands - exact same logic as products.php
$brandsData = [];
if ($publicService && method_exists($publicService, 'getBrandsForFilter')) {
    $brandsData = $publicService->getBrandsForFilter();
} else {
    echo "❌ Cannot get user brands data<br>";
}
$userBrands = $brandsData['brands'] ?? [];

echo "Total: " . count($userBrands) . " brands</div>";

echo "<div class='category-list'>";
foreach ($userBrands as $brand) {
    echo "<li class='brand-item'>
            <span class='category-name'>" . htmlspecialchars($brand['name']) . "</span>
            <span class='category-count'>" . ($brand['product_count'] ?? 0) . "</span>
          </li>";
}
echo "</div>";

echo "</div>";

// Admin Brands
echo "<div class='admin-side'>
    <div class='side-title'>⚙️ Admin Filter Config</div>
    <div class='stats'>";

$adminBrands = $filterConfigService->getBrandsForFilter();

echo "Total: " . count($adminBrands) . " brands</div>";

echo "<div class='category-list'>";
foreach ($adminBrands as $brand) {
    echo "<li class='brand-item'>
            <span class='category-name'>" . htmlspecialchars($brand['name']) . "</span>
            <span class='category-count'>" . ($brand['count'] ?? 0) . "</span>
          </li>";
}
echo "</div>";

echo "</div></div></div>";

// Detailed Comparison
echo "<div class='comparison-section'>
    <h2 class='section-title'>🔍 Detailed Analysis</h2>
    
    <h3>Categories Analysis</h3>
    <div class='console'>";

// Compare categories
$userCats = [];
$adminCats = [];

function flattenCategories($nodes, &$result, $prefix = '') {
    foreach ($nodes as $node) {
        $key = $prefix . $node['name'];
        $result[$key] = [
            'name' => $node['name'],
            'count' => $node['product_count'] ?? 0,
            'sort_order' => $node['sort_order'] ?? 0
        ];
        if (!empty($node['children'])) {
            flattenCategories($node['children'], $result, $prefix . '  ');
        }
    }
}

flattenCategories($userCategoryTree, $userCats);
flattenCategories($adminCategories, $adminCats);

echo "=== CATEGORIES COMPARISON ===\n";
echo "User categories: " . count($userCats) . "\n";
echo "Admin categories: " . count($adminCats) . "\n\n";

echo "User category order:\n";
foreach ($userCats as $key => $cat) {
    echo sprintf("  %-40s Count:%-3d Sort:%d\n", $key, $cat['count'], $cat['sort_order']);
}

echo "\nAdmin category order:\n";
foreach ($adminCats as $key => $cat) {
    echo sprintf("  %-40s Count:%-3d Sort:%d\n", $key, $cat['count'], $cat['sort_order']);
}

echo "\n=== BRANDS COMPARISON ===\n";
echo "User brands: " . count($userBrands) . "\n";
echo "Admin brands: " . count($adminBrands) . "\n\n";

echo "User brand order:\n";
foreach ($userBrands as $brand) {
    echo sprintf("  %-30s Count:%-3d Sort:%d\n", $brand['name'], $brand['product_count'] ?? 0, $brand['sort_order'] ?? 0);
}

echo "\nAdmin brand order:\n";
foreach ($adminBrands as $brand) {
    echo sprintf("  %-30s Count:%-3d Sort:%d\n", $brand['name'], $brand['count'] ?? 0, $brand['sort_order'] ?? 0);
}

echo "</div></div>";

echo "</div></body></html>";
?>
