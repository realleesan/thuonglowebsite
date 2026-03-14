<?php
/**
 * Direct test - simulates what happens when clicking save in edit.php
 * This bypasses the form and directly tests the processing logic
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get product ID from URL or use default
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 34;

echo "<!DOCTYPE html>";
echo "<html><head><title>Direct Edit Test</title>";
echo "<style>
    body { font-family: monospace; padding: 20px; }
    .result { padding: 10px; margin: 10px 0; background: #f0f0f0; }
    .success { color: green; }
    .error { color: red; }
</style>";
echo "</head><body>";
echo "<h1>Direct Edit Test - Product ID: $productId</h1>";

// ============================================
// Simulate what edit.php does
// ============================================

// First, load the product to get current data
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/database.php';
require_once __DIR__ . '/app/models/ProductsModel.php';

$productsModel = new ProductsModel();
$product = $productsModel->find($productId);

if (!$product) {
    echo "<p class='error'>Product not found!</p>";
    exit;
}

echo "<div class='result'>";
echo "<h3>Current Product Data</h3>";
echo "Name: " . $product['name'] . "<br>";
echo "Price: " . number_format($product['price']) . "<br>";
echo "Category ID: " . $product['category_id'] . "<br>";
echo "</div>";

// ============================================
// Simulate form submission
// ============================================

if (isset($_GET['action']) && $_GET['action'] === 'save') {
    echo "<div class='result'>";
    echo "<h3>Processing Save Action</h3>";
    
    // Simulate POST data from form
    $postData = [
        'name' => 'Direct Test Edit ' . date('Y-m-d H:i:s'),
        'category_id' => $product['category_id'], // Keep same category
        'price' => $product['price'] + 10000, // Increase price by 10000
        'description' => $product['description'] ?? 'Test description',
        'status' => 'active',
        'type' => $product['type'] ?? 'data_nguon_hang',
        'data_action' => '', // Empty for main form
    ];
    
    echo "Simulated POST data:<br>";
    echo "<pre>" . print_r($postData, true) . "</pre>";
    
    // ============================================
    // Run validation (same as edit.php)
    // ============================================
    echo "<h4>Validation:</h4>";
    $errors = [];
    
    $name = trim($postData['name'] ?? '');
    $category_id = (int)($postData['category_id'] ?? 0);
    $price = (float)($postData['price'] ?? 0);
    $description = trim($postData['description'] ?? '');
    
    echo "name: '$name' (empty: " . (empty($name) ? 'true' : 'false') . ")<br>";
    echo "category_id: $category_id<br>";
    echo "price: $price<br>";
    echo "description length: " . strlen($description) . "<br>";
    
    if (empty($name)) {
        $errors[] = 'Tên data không được để trống';
        echo "<span class='error'>ERROR: name is empty</span><br>";
    }
    
    if ($category_id <= 0) {
        $errors[] = 'Vui lòng chọn danh mục';
        echo "<span class='error'>ERROR: category_id is invalid ($category_id)</span><br>";
    }
    
    if ($price <= 0) {
        $errors[] = 'Giá data phải lớn hơn 0';
        echo "<span class='error'>ERROR: price is invalid ($price)</span><br>";
    }
    
    if (empty($description)) {
        $errors[] = 'Mô tả data không được để trống';
        echo "<span class='error'>ERROR: description is empty</span><br>";
    }
    
    if (!empty($errors)) {
        echo "<span class='error'>VALIDATION FAILED!</span><br>";
    } else {
        echo "<span class='success'>VALIDATION PASSED!</span><br>";
        
        // ============================================
        // Try to update
        // ============================================
        echo "<h4>Database Update:</h4>";
        
        $updateData = [
            'name' => $name,
            'category_id' => $category_id,
            'price' => $price,
            'description' => $description,
            'status' => $postData['status'],
            'type' => $postData['type'],
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        try {
            $updated = $productsModel->update($productId, $updateData);
            
            if ($updated) {
                echo "<span class='success'>✓ UPDATE SUCCESSFUL!</span><br>";
                
                // Verify
                $updatedProduct = $productsModel->find($productId);
                echo "<h4>Updated Product:</h4>";
                echo "Name: " . $updatedProduct['name'] . "<br>";
                echo "Price: " . number_format($updatedProduct['price']) . "<br>";
                echo "Category ID: " . $updatedProduct['category_id'] . "<br>";
                echo "Updated at: " . $updatedProduct['updated_at'] . "<br>";
            } else {
                echo "<span class='error'>✗ UPDATE FAILED! (returned false)</span><br>";
            }
        } catch (Exception $e) {
            echo "<span class='error'>✗ EXCEPTION: " . $e->getMessage() . "</span><br>";
        }
    }
    
    echo "</div>";
}

echo "<div class='result'>";
echo "<h3>Test Links</h3>";
echo "<p><a href='?id=$productId&action=save'>Click here to simulate save</a></p>";
echo "<p><a href='?id=$productId'>Reset</a></p>";
echo "</div>";

echo "</body></html>";
