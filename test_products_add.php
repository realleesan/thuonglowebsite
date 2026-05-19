<?php
/**
 * Test file to diagnose WSOD on admin products add page
 * URL: http://test1.web3b.com/test_products_add.php
 */

// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h1>Testing Admin Products Add Page Dependencies</h1>";
echo "<hr>";

// Test 1: Check if session is started
echo "<h2>Test 1: Session Status</h2>";
session_start();
echo "✓ Session started successfully<br>";
echo "Session ID: " . session_id() . "<br><br>";

// Test 2: Load config
echo "<h2>Test 2: Loading Config</h2>";
try {
    $config = require_once __DIR__ . '/config.php';
    echo "✓ Config loaded successfully<br><br>";
} catch (Exception $e) {
    echo "✗ Error loading config: " . $e->getMessage() . "<br><br>";
    die();
}

// Test 3: Load view_init.php
echo "<h2>Test 3: Loading view_init.php</h2>";
try {
    require_once __DIR__ . '/core/view_init.php';
    echo "✓ view_init.php loaded successfully<br>";
    echo "✓ ServiceManager initialized: " . (isset($serviceManager) ? 'YES' : 'NO') . "<br>";
    echo "✓ AdminService available: " . (isset($adminService) ? 'YES' : 'NO') . "<br>";
    echo "✓ ErrorHandler available: " . (isset($errorHandler) ? 'YES' : 'NO') . "<br><br>";
} catch (Exception $e) {
    echo "✗ Error loading view_init.php: " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre><br><br>";
    die();
}

// Test 4: Check if BrandsModel file exists
echo "<h2>Test 4: BrandsModel File Check</h2>";
$brandsModelPath = __DIR__ . '/app/models/BrandsModel.php';
if (file_exists($brandsModelPath)) {
    echo "✓ BrandsModel.php exists at: $brandsModelPath<br>";
} else {
    echo "✗ BrandsModel.php NOT FOUND at: $brandsModelPath<br>";
}
echo "<br>";

// Test 5: Try to load BrandsModel
echo "<h2>Test 5: Loading BrandsModel</h2>";
try {
    require_once __DIR__ . '/app/models/BrandsModel.php';
    echo "✓ BrandsModel.php required successfully<br>";
    
    $brandsModel = new BrandsModel();
    echo "✓ BrandsModel instantiated successfully<br>";
    
    $brands = $brandsModel->getForDropdown();
    echo "✓ getForDropdown() called successfully<br>";
    echo "✓ Brands count: " . count($brands) . "<br>";
    echo "<pre>" . print_r($brands, true) . "</pre><br>";
} catch (Exception $e) {
    echo "✗ Error with BrandsModel: " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre><br>";
}
echo "<br>";

// Test 6: Check ProductsModel
echo "<h2>Test 6: Loading ProductsModel</h2>";
try {
    require_once __DIR__ . '/app/models/ProductsModel.php';
    echo "✓ ProductsModel.php required successfully<br>";
    
    $productsModel = new ProductsModel();
    echo "✓ ProductsModel instantiated successfully<br><br>";
} catch (Exception $e) {
    echo "✗ Error with ProductsModel: " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre><br><br>";
}

// Test 7: Test AdminService getActiveCategoriesForDropdown
echo "<h2>Test 7: AdminService - Get Categories</h2>";
try {
    if (isset($adminService)) {
        $categoriesData = $adminService->getActiveCategoriesForDropdown();
        echo "✓ getActiveCategoriesForDropdown() called successfully<br>";
        $categories = $categoriesData['categories'] ?? [];
        echo "✓ Categories count: " . count($categories) . "<br>";
        echo "<pre>" . print_r($categories, true) . "</pre><br>";
    } else {
        echo "✗ AdminService not available<br><br>";
    }
} catch (Exception $e) {
    echo "✗ Error getting categories: " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre><br><br>";
}

// Test 8: Simulate the exact code from add.php
echo "<h2>Test 8: Simulating add.php Code</h2>";
try {
    // This is the exact code from add.php lines 11-27
    $service = isset($currentService) ? $currentService : ($adminService ?? null);
    
    echo "✓ Service variable set<br>";
    
    // Get categories for dropdown using AdminService
    $categoriesData = $service->getActiveCategoriesForDropdown();
    $categories = $categoriesData['categories'] ?? [];
    echo "✓ Categories loaded: " . count($categories) . " items<br>";

    // Get brands for dropdown using AdminService
    try {
        $brandsModel = new BrandsModel();
        $brands = $brandsModel->getForDropdown();
        echo "✓ Brands loaded: " . count($brands) . " items<br>";
    } catch (Exception $e) {
        $brands = [];
        echo "✗ Error getting brands: " . $e->getMessage() . "<br>";
        error_log('Error getting brands: ' . $e->getMessage());
    }
    
    echo "<br>✓ <strong>ALL TESTS PASSED - No errors found in dependencies!</strong><br>";
    
} catch (Exception $e) {
    echo "✗ Error in simulation: " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre><br>";
}

echo "<hr>";
echo "<h2>Conclusion</h2>";
echo "If all tests passed above, the issue might be:<br>";
echo "1. Authentication/session issue preventing access to admin page<br>";
echo "2. Admin layout file (admin_master.php) has an error<br>";
echo "3. JavaScript error preventing page render<br>";
echo "4. CSS issue making content invisible<br>";
echo "<br>";
echo "<a href='?page=admin&module=products&action=add'>Try accessing the actual add page</a>";
?>
