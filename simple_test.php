<?php
// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Simple Test - Check what's happening</h2>";

// Test basic includes
try {
    echo "1. Testing include AdminService...<br>";
    require_once __DIR__ . '/app/services/AdminService.php';
    echo "✓ AdminService included<br>";
    
    echo "2. Testing create AdminService...<br>";
    $adminService = new AdminService(null, 'admin');
    echo "✓ AdminService created<br>";
    
    echo "3. Testing getModel CategoriesModel...<br>";
    $categoriesModel = $adminService->getModel('CategoriesModel');
    if ($categoriesModel) {
        echo "✓ CategoriesModel loaded<br>";
    } else {
        echo "✗ CategoriesModel failed to load<br>";
    }
    
    echo "4. Testing getModel ProductsModel...<br>";
    $productsModel = $adminService->getModel('ProductsModel');
    if ($productsModel) {
        echo "✓ ProductsModel loaded<br>";
    } else {
        echo "✗ ProductsModel failed to load<br>";
    }
    
    echo "5. Testing deleteCategory(66)...<br>";
    $result = $adminService->deleteCategory(66);
    echo "Result: <pre>" . print_r($result, true) . "</pre>";
    
    echo "6. Testing forceDeleteCategory(66)...<br>";
    $forceResult = $adminService->forceDeleteCategory(66);
    echo "Force Result: <pre>" . print_r($forceResult, true) . "</pre>";
    
} catch (Exception $e) {
    echo "<h3>Error caught:</h3>";
    echo "<pre>";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString();
    echo "</pre>";
}

// Test JSON output
echo "<h3>7. Testing JSON output:</h3>";
$testArray = ['success' => true, 'message' => 'Test message'];
$jsonOutput = json_encode($testArray);
echo "JSON: " . $jsonOutput . "<br>";
echo "JSON length: " . strlen($jsonOutput) . "<br>";

?>
