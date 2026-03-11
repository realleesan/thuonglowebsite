<?php
/**
 * Test file to debug admin products view
 * Run this by accessing: ?page=test_debug
 */

// Prevent direct access
if (!defined('THUONGLO_INIT')) {
    define('THUONGLO_INIT', true);
}

echo "<h1>Test: Admin Products View Debug</h1>";
echo "<pre style='background:#f5f5f5;padding:10px;overflow:auto;max-height:500px'>";

// Test 1: Basic PHP info
echo "<h2>1. PHP Version: " . phpversion() . "</h2>\n";

// Test 2: Check database connection
echo "<h2>2. Database Connection Test</h2>\n";
try {
    require_once __DIR__ . '/core/database.php';
    $db = Database::getInstance();
    $pdo = $db->getPdo();
    echo "Database connected: OK\n";
    
    // Check tables exist
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables: " . implode(', ', $tables) . "\n";
    
} catch (Exception $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
}

// Test 3: Check products table
echo "\n<h2>3. Products Table Test</h2>\n";
try {
    $result = $pdo->query("SELECT COUNT(*) as cnt FROM products");
    $count = $result->fetch(PDO::FETCH_ASSOC);
    echo "Products count: " . $count['cnt'] . "\n";
    
    // Get first product
    $stmt = $pdo->query("SELECT * FROM products LIMIT 1");
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($product) {
        echo "\nFirst product:\n";
        print_r($product);
    } else {
        echo "No products found!\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Test 4: Test ProductsModel
echo "\n<h2>4. ProductsModel Test</h2>\n";
try {
    require_once __DIR__ . '/app/models/ProductsModel.php';
    $model = new ProductsModel();
    
    // Test query method
    $products = $model->query("SELECT * FROM products WHERE id = 1");
    echo "Model query result: " . (empty($products) ? "EMPTY" : "HAS DATA") . "\n";
    
    if (!empty($products)) {
        echo "Product name: " . ($products[0]['name'] ?? 'N/A') . "\n";
        echo "Product price: " . ($products[0]['price'] ?? 'N/A') . "\n";
    }
} catch (Exception $e) {
    echo "ProductsModel Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

// Test 5: Test view_init
echo "\n<h2>5. view_init Test</h2>\n";
try {
    require_once __DIR__ . '/core/view_init.php';
    echo "view_init loaded: OK\n";
    
    global $adminService, $publicService, $serviceManager;
    echo "publicService: " . (isset($publicService) ? "EXISTS" : "NULL") . "\n";
    echo "adminService: " . (isset($adminService) ? "EXISTS" : "NULL") . "\n";
    echo "serviceManager: " . (isset($serviceManager) ? "EXISTS" : "NULL") . "\n";
    
    // Test adminService
    if (isset($adminService)) {
        echo "adminService class: " . get_class($adminService) . "\n";
    }
} catch (Exception $e) {
    echo "view_init Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

// Test 6: Test get product from service
echo "\n<h2>6. AdminService Test</h2>\n";
try {
    global $adminService;
    
    if (isset($adminService) && method_exists($adminService, 'getProductById')) {
        $productData = $adminService->getProductById(1);
        echo "Service getProductById(1): ";
        if ($productData) {
            echo "OK\n";
            echo "Name: " . ($productData['name'] ?? 'N/A') . "\n";
            echo "Price: " . ($productData['price'] ?? 'N/A') . "\n";
        } else {
            echo "NULL/EMPTY\n";
        }
    } else {
        echo "adminService not available or method missing\n";
    }
} catch (Exception $e) {
    echo "AdminService Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

// Test 7: Check categories table
echo "\n<h2>7. Categories Test</h2>\n";
try {
    require_once __DIR__ . '/app/models/CategoriesModel.php';
    $catModel = new CategoriesModel();
    $categories = $catModel->getActive();
    echo "Categories count: " . count($categories) . "\n";
} catch (Exception $e) {
    echo "Categories Error: " . $e->getMessage() . "\n";
}

echo "</pre>";

// Test 8: Try to load the actual view
echo "<h2>8. Actual View Test</h2>\n";
echo "<p>Click <a href='?page=admin&module=products&action=view&id=1'>here</a> to test actual view</p>\n";
