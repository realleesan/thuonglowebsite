<?php
/**
 * Test file to check edit.php loading
 * Run this at: https://test1.web3b.com/test_edit_loading.php?id=34
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Edit Page Loading Test</h1>";

// Simulate the edit page loading
$product_id = (int)($_GET['id'] ?? 0);

echo "<p>Product ID: $product_id</p>";

if (!$product_id) {
    echo "<p style='color:red'>Error: No product ID</p>";
    exit;
}

// Include the required files
echo "<p>Including config...</p>";
require_once __DIR__ . '/config.php';

echo "<p>Including database...</p>";
require_once __DIR__ . '/core/database.php';

echo "<p>Including ProductsModel...</p>";
require_once __DIR__ . '/app/models/ProductsModel.php';

echo "<p>Creating ProductsModel...</p>";
$productsModel = new ProductsModel();

echo "<p>Finding product...</p>";
$products = $productsModel->query("SELECT * FROM products WHERE id = ?", [$product_id]);

if (empty($products)) {
    echo "<p style='color:red'>Error: Product not found</p>";
    exit;
}

$product = $products[0];

echo "<p>Product found: " . htmlspecialchars($product['name']) . "</p>";
echo "<p style='color:green'>All includes successful!</p>";

// Now try to include the edit.php file
echo "<hr>";
echo "<p>Attempting to include edit.php...</p>";

// Start output buffering to catch any output
ob_start();

try {
    include __DIR__ . '/app/views/admin/products/edit.php';
    $output = ob_get_clean();
    echo "<p>edit.php included successfully!</p>";
    echo "<p>Output length: " . strlen($output) . " bytes</p>";
} catch (Exception $e) {
    ob_end_clean();
    echo "<p style='color:red'>Error including edit.php: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
}

echo "<hr>";
echo "<p>Test completed</p>";
