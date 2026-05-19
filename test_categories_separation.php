<?php
/**
 * Test Categories Separation
 * Kiểm tra xem news categories có được phân tách đúng không
 */

// Bật error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test Categories Separation</h1>";

// Load necessary files
require_once __DIR__ . '/core/view_init.php';
require_once __DIR__ . '/app/models/CategoriesModel.php';
require_once __DIR__ . '/app/services/FilterConfigService.php';

// Test 1: Kiểm tra CategoriesModel methods
echo "<h2>Test 1: CategoriesModel Methods</h2>";

$categoriesModel = new \CategoriesModel();

// Test getNewsCategories()
$newsCategories = $categoriesModel->getNewsCategories();
echo "<h3>getNewsCategories() - Chỉ nên có type = 'news'</h3>";
echo "Count: " . count($newsCategories) . "<br>";
foreach ($newsCategories as $cat) {
    echo "- ID: {$cat['id']}, Name: {$cat['name']}, Type: " . ($cat['type'] ?? 'NULL') . "<br>";
}

// Test getActiveForFilter() 
$productCategories = $categoriesModel->getActiveForFilter();
echo "<h3>getActiveForFilter() - Chỉ nên có type != 'news'</h3>";
echo "Count: " . count($productCategories) . "<br>";
foreach ($productCategories as $cat) {
    echo "- ID: {$cat['id']}, Name: {$cat['name']}, Type: " . ($cat['type'] ?? 'NULL') . "<br>";
}

// Test getAllWithProductCounts()
$allCategories = $categoriesModel->getAllWithProductCounts();
echo "<h3>getAllWithProductCounts() - Chỉ nên có type != 'news'</h3>";
echo "Count: " . count($allCategories) . "<br>";
foreach ($allCategories as $cat) {
    echo "- ID: {$cat['id']}, Name: {$cat['name']}, Type: " . ($cat['type'] ?? 'NULL') . "<br>";
}

// Test 2: Kiểm tra FilterConfigService
echo "<h2>Test 2: FilterConfigService</h2>";

$filterConfigService = new \FilterConfigService();
$filterCategories = $filterConfigService->getCategoriesForFilter();

echo "<h3>getCategoriesForFilter() - Chỉ nên có type != 'news'</h3>";
echo "Count: " . count($filterCategories) . "<br>";
foreach ($filterCategories as $cat) {
    echo "- ID: {$cat['id']}, Name: {$cat['name']}, Type: " . ($cat['type'] ?? 'NULL') . "<br>";
}

// Test 3: Kiểm tra AdminService
echo "<h2>Test 3: AdminService</h2>";

global $adminService;
if ($adminService) {
    $adminCategoriesData = $adminService->getCategoriesData(1, 10);
    $adminCategories = $adminCategoriesData['categories'];
    
    echo "<h3>getCategoriesData() - Chỉ nên có type != 'news'</h3>";
    echo "Count: " . count($adminCategories) . "<br>";
    foreach ($adminCategories as $cat) {
        echo "- ID: {$cat['id']}, Name: {$cat['name']}, Type: " . ($cat['type'] ?? 'NULL') . "<br>";
    }
} else {
    echo "❌ AdminService not available<br>";
}

// Test 4: Kiểm tra PublicService
echo "<h2>Test 4: PublicService</h2>";

global $publicService;
if ($publicService) {
    $hierarchyCategories = $publicService->getCategoriesHierarchy();
    
    echo "<h3>getCategoriesHierarchy() - Chỉ nên có type != 'news'</h3>";
    function countCategories($categories) {
        $count = count($categories);
        foreach ($categories as $cat) {
            if (!empty($cat['children'])) {
                $count += countCategories($cat['children']);
            }
        }
        return $count;
    }
    
    echo "Count: " . countCategories($hierarchyCategories) . "<br>";
    
    function displayCategories($categories, $level = 0) {
        foreach ($categories as $cat) {
            $indent = str_repeat('--', $level);
            echo "{$indent} ID: {$cat['id']}, Name: {$cat['name']}, Type: " . ($cat['type'] ?? 'NULL') . "<br>";
            if (!empty($cat['children'])) {
                displayCategories($cat['children'], $level + 1);
            }
        }
    }
    
    displayCategories($hierarchyCategories);
} else {
    echo "❌ PublicService not available<br>";
}

// Test 5: Tổng kết
echo "<h2>Test 5: Summary</h2>";

// Count total categories in database
$allCategories = $categoriesModel->query("SELECT * FROM categories WHERE status = 'active'");
$newsCount = 0;
$productCount = 0;

foreach ($allCategories as $cat) {
    if (isset($cat['type']) && $cat['type'] === 'news') {
        $newsCount++;
    } else {
        $productCount++;
    }
}

echo "<strong>Database Summary:</strong><br>";
echo "- Total active categories: " . count($allCategories) . "<br>";
echo "- News categories (type = 'news'): " . $newsCount . "<br>";
echo "- Product categories (type != 'news'): " . $productCount . "<br>";

echo "<br><strong>Expected Results:</strong><br>";
echo "- News pages should show " . $newsCount . " categories<br>";
echo "- Product pages should show " . $productCount . " categories<br>";

echo "<h2>Test Complete</h2>";
echo "<p><strong>Nếu kết quả đúng:</strong></p>";
echo "<ul>";
echo "<li>✅ getNewsCategories() chỉ trả về news categories</li>";
echo "<li>✅ getActiveForFilter() chỉ trả về product categories</li>";
echo "<li>✅ getAllWithProductCounts() chỉ trả về product categories</li>";
echo "<li>✅ FilterConfigService chỉ trả về product categories</li>";
echo "<li>✅ AdminService chỉ trả về product categories</li>";
echo "<li>✅ PublicService chỉ trả về product categories</li>";
echo "</ul>";
?>
