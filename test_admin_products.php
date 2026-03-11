<?php
/**
 * Test file to debug admin products pages
 */

// Load config
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/database.php';

echo "<h1>Test: Admin Products Data</h1>";

$db = Database::getInstance();
$pdo = $db->getPdo();

// Test 1: Check if products table exists
echo "<h2>1. Check products table:</h2>";
try {
    $result = $pdo->query("SELECT COUNT(*) as count FROM products");
    $count = $result->fetch(PDO::FETCH_ASSOC);
    echo "Products count: " . $count['count'] . "<br>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

// Test 2: Get first product
echo "<h2>2. First product:</h2>";
try {
    $stmt = $pdo->query("SELECT * FROM products LIMIT 1");
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($product) {
        echo "<pre>";
        print_r($product);
        echo "</pre>";
    } else {
        echo "No products found<br>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

// Test 3: Test ProductsModel
echo "<h2>3. Test ProductsModel:</h2>";
try {
    require_once __DIR__ . '/app/models/ProductsModel.php';
    $model = new ProductsModel();
    
    $products = $model->query("SELECT * FROM products LIMIT 1");
    if (!empty($products)) {
        echo "ProductsModel query works!<br>";
        echo "<pre>";
        print_r($products[0]);
        echo "</pre>";
    } else {
        echo "No products found via model<br>";
    }
} catch (Exception $e) {
    echo "ProductsModel Error: " . $e->getMessage() . "<br>";
}

// Test 4: Test with product ID 1
echo "<h2>4. Test with product ID 1:</h2>";
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([1]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($product) {
        echo "Product ID 1 found:<br>";
        echo "Name: " . $product['name'] . "<br>";
        echo "Price: " . $product['price'] . "<br>";
        echo "Stock: " . $product['stock'] . "<br>";
    } else {
        echo "Product ID 1 not found<br>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

// Test 5: Check view_init
echo "<h2>5. Test view_init:</h2>";
try {
    require_once __DIR__ . '/core/view_init.php';
    echo "view_init loaded successfully<br>";
    
    global $adminService;
    echo "adminService: " . (isset($adminService) ? "EXISTS" : "NULL") . "<br>";
} catch (Exception $e) {
    echo "view_init Error: " . $e->getMessage() . "<br>";
}

// Test 6: Test current URL params
echo "<h2>6. Current URL params:</h2>";
echo "page: " . ($_GET['page'] ?? 'not set') . "<br>";
echo "module: " . ($_GET['module'] ?? 'not set') . "<br>";
echo "action: " . ($_GET['action'] ?? 'not set') . "<br>";
echo "id: " . ($_GET['id'] ?? 'not set') . "<br>";
