<?php
// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Test Force Delete Category 66</h2>";

try {
    require_once __DIR__ . '/app/services/AdminService.php';
    $adminService = new AdminService(null, 'admin');
    
    echo "1. Getting models...<br>";
    $categoriesModel = $adminService->getModel('CategoriesModel');
    $productsModel = $adminService->getModel('ProductsModel');
    
    if (!$categoriesModel) {
        die("CategoriesModel failed to load");
    }
    
    if (!$productsModel) {
        die("ProductsModel failed to load");
    }
    
    echo "✓ Both models loaded<br>";
    
    echo "2. Testing hasChildCategories...<br>";
    $hasChildren = $categoriesModel->hasChildCategories(66);
    echo "Has children: " . ($hasChildren ? 'true' : 'false') . "<br>";
    
    echo "3. Testing database access...<br>";
    // Skip direct db access test
    
    echo "4. Testing query via CategoriesModel...<br>";
    try {
        $testQuery = $categoriesModel->query("SELECT COUNT(*) as count FROM products WHERE category_id = ?", [66]);
        echo "Products in category 66: " . ($testQuery[0]['count'] ?? 0) . "<br>";
    } catch (Exception $e) {
        echo "Query via CategoriesModel failed: " . $e->getMessage() . "<br>";
    }
    
    echo "5. Testing simple query...<br>";
    try {
        $simpleQuery = $categoriesModel->query("SELECT COUNT(*) as count FROM categories");
        echo "Total categories: " . ($simpleQuery[0]['count'] ?? 0) . "<br>";
    } catch (Exception $e) {
        echo "Simple query failed: " . $e->getMessage() . "<br>";
    }
    
    echo "6. Testing forceDeleteCategory...<br>";
    $result = $adminService->forceDeleteCategory(66);
    echo "Result: <pre>" . print_r($result, true) . "</pre>";
    
} catch (Exception $e) {
    echo "<h3>Error:</h3>";
    echo "<pre>";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString();
    echo "</pre>";
}
?>
