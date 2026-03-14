<?php
/**
 * Test file to simulate edit.php form submission
 * This test mimics exactly what edit.php does
 * to find the difference between working test and non-working edit.php
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/database.php';
require_once __DIR__ . '/app/models/ProductsModel.php';

echo "<!DOCTYPE html>";
echo "<html><head><title>Test: Edit.php Simulation</title>";
echo "<style>
    body { font-family: monospace; padding: 20px; background: #f5f5f5; }
    .test-section { background: white; padding: 15px; margin: 10px 0; border: 1px solid #ddd; }
    .success { color: green; }
    .error { color: red; }
    .info { color: blue; }
    pre { background: #f0f0f0; padding: 10px; overflow: auto; }
</style>";
echo "</head><body>";
echo "<h1>Test: Edit.php Form Submission Simulation</h1>";

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simulate POST data as if coming from edit.php form
// This is exactly what edit.php receives when user clicks save
$simulatedPost = [
    'name' => 'Test Product Edit ' . date('Y-m-d H:i:s'),
    'category_id' => '1',  // This is REQUIRED - must be > 0
    'price' => '150000',   // This is REQUIRED - must be > 0
    'description' => 'Test description for the product',  // This is REQUIRED - cannot be empty
    'status' => 'active',
    'type' => 'data_nguon_hang',
    'sale_price' => '',
    'expiry_days' => '30',
    'sku' => 'TEST-001',
    'short_description' => 'Test short description',
    'meta_title' => 'Test Meta Title',
    'meta_description' => 'Test Meta Description',
    'data_action' => '',  // Empty for main form save
    'record_count' => '100',
    'data_size' => '10 KB',
    'data_format' => 'Excel',
    'data_source' => 'Vietnam',
    'reliability' => '90%',
    'quota' => '100',
    'quota_per_usage' => '10',
    'featured' => '1',
    'downloadable' => '1'
];

// Simulate GET parameter (product ID)
$productId = 34; // Use the same product as before

echo "<div class='test-section'>";
echo "<h3>1. Simulated POST Data</h3>";
echo "<pre>" . print_r($simulatedPost, true) . "</pre>";
echo "</div>";

// Now simulate what edit.php does
echo "<div class='test-section'>";
echo "<h3>2. Processing as edit.php does</h3>";

// ========================================
// STEP 1: Check if POST and data_action is empty
// ========================================
echo "<h4>Step 1: Check condition</h4>";
$isPost = ($_SERVER['REQUEST_METHOD'] === 'POST');
echo "Is POST request: " . ($isPost ? 'YES' : 'NO (using simulated data)');
echo "<br>";

$dataAction = $simulatedPost['data_action'] ?? '';
echo "data_action value: '$dataAction'";
echo "<br>";
echo "empty(data_action): " . (empty($dataAction) ? 'true' : 'false');
echo "<br>";

// ========================================
// STEP 2: Validation
// ========================================
echo "<h4>Step 2: Validation</h4>";
$errors = [];

$name = trim($simulatedPost['name'] ?? '');
$category_id = (int)($simulatedPost['category_id'] ?? 0);
$price = (float)($simulatedPost['price'] ?? 0);
$description = trim($simulatedPost['description'] ?? '');

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
    echo "<pre>Errors: " . print_r($errors, true) . "</pre>";
} else {
    echo "<span class='success'>VALIDATION PASSED!</span><br>";
}

// ========================================
// STEP 3: Try to update if no errors
// ========================================
echo "<h4>Step 3: Database Update</h4>";

if (empty($errors)) {
    try {
        $productsModel = new ProductsModel();
        
        $updateData = [
            'name'             => $name,
            'category_id'      => $category_id,
            'price'            => $price,
            'description'      => $description,
            'status'           => $simulatedPost['status'] ?? 'active',
            'type'             => $simulatedPost['type'] ?? 'data_nguon_hang',
            'sale_price'       => isset($simulatedPost['sale_price']) && $simulatedPost['sale_price'] !== '' ? (float)$simulatedPost['sale_price'] : null,
            'expiry_days'      => isset($simulatedPost['expiry_days']) && $simulatedPost['expiry_days'] !== '' ? (int)$simulatedPost['expiry_days'] : 30,
            'sku'              => !empty($simulatedPost['sku']) ? $simulatedPost['sku'] : null,
            'short_description'=> $simulatedPost['short_description'] ?? '',
            'meta_title'       => $simulatedPost['meta_title'] ?? '',
            'meta_description' => $simulatedPost['meta_description'] ?? '',
            'image'            => '',
            'record_count'     => isset($simulatedPost['record_count']) && $simulatedPost['record_count'] !== '' ? (int)$simulatedPost['record_count'] : 0,
            'stock'            => isset($simulatedPost['record_count']) && $simulatedPost['record_count'] !== '' ? (int)$simulatedPost['record_count'] : 0,
            'data_size'        => $simulatedPost['data_size'] ?? '',
            'data_format'      => $simulatedPost['data_format'] ?? '',
            'data_source'      => $simulatedPost['data_source'] ?? '',
            'reliability'      => $simulatedPost['reliability'] ?? '',
            'quota'            => isset($simulatedPost['quota']) && $simulatedPost['quota'] !== '' ? (int)$simulatedPost['quota'] : 100,
            'quota_per_usage'  => isset($simulatedPost['quota_per_usage']) && $simulatedPost['quota_per_usage'] !== '' ? (int)$simulatedPost['quota_per_usage'] : 10,
            'supplier_name'    => null,
            'supplier_title'   => null,
            'supplier_bio'     => null,
            'supplier_avatar'  => null,
            'supplier_social'  => null,
            'benefits'         => null,
            'data_structure'   => null,
            'featured'         => isset($simulatedPost['featured']) ? 1 : 0,
            'downloadable'     => isset($simulatedPost['downloadable']) ? 1 : 0,
            'updated_at'       => date('Y-m-d H:i:s')
        ];
        
        echo "Update data prepared:<br>";
        echo "<pre>" . print_r($updateData, true) . "</pre>";
        
        $updated = $productsModel->update($productId, $updateData);
        
        if ($updated) {
            echo "<span class='success'>✓ UPDATE SUCCESSFUL!</span><br>";
            
            // Verify
            $product = $productsModel->find($productId);
            echo "Updated product:<br>";
            echo "Name: " . $product['name'] . "<br>";
            echo "Price: " . number_format($product['price']) . "<br>";
            echo "Category ID: " . $product['category_id'] . "<br>";
        } else {
            echo "<span class='error'>✗ UPDATE FAILED!</span><br>";
        }
        
    } catch (Exception $e) {
        echo "<span class='error'>EXCEPTION: " . $e->getMessage() . "</span><br>";
    }
}

echo "</div>";

// ========================================
// Analysis
// ========================================
echo "<div class='test-section'>";
echo "<h3>3. Analysis</h3>";

echo "<p><strong>Key Difference Between Working Test and Edit.php:</strong></p>";
echo "<ul>";
echo "<li><strong>In test_product_edit_debug.php:</strong> We only sent name and price - validation is simple</li>";
echo "<li><strong>In edit.php:</strong> Many fields are REQUIRED:";
echo "<ul>";
echo "<li>name - cannot be empty</li>";
echo "<li>category_id - MUST be > 0</li>";
echo "<li>price - MUST be > 0</li>";
echo "<li>description - cannot be empty</li>";
echo "</ul>";
echo "</li>";
echo "<li>If any of these fail validation, the update will NOT happen!</li>";
echo "</ul>";

echo "<p><strong>Most Likely Cause:</strong></p>";
echo "<ul>";
echo "<li>category_id is 0 or empty in the form submission</li>";
echo "<li>OR description is empty</li>";
echo "<li>OR price is 0 or empty</li>";
echo "</ul>";

echo "<p><strong>Solution:</strong></p>";
echo "<ul>";
echo "<li>Check the form to make sure category_id is always selected</li>";
echo "<li>Make sure description field has content</li>";
echo "<li>Make sure price has a valid value</li>";
echo "</ul>";
echo "</div>";

echo "</body></html>";
