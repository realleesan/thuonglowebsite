<?php
/**
 * Test script to simulate the exact form submission in add.php
 * This simulates what happens when user submits the form
 */

echo "<h1>Test Add Product Form Simulation</h1>";

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/database.php';
require_once __DIR__ . '/core/view_init.php';

echo "<h2>Step 1: Simulate POST data</h2>";

// Simulate form POST data
$_POST = [
    'name'             => 'Test Product Form ' . date('Y-m-d H:i:s'),
    'category_id'      => 1,
    'price'            => 1500000,
    'description'      => 'Test description from form',
    'status'           => 'active',
    'type'             => 'data_nguon_hang',
    'sale_price'       => 990000,
    'expiry_days'      => 30,
    'sku'              => 'FORM-TEST-' . time(),
    'short_description'=> 'Test short desc',
    'meta_title'       => '',
    'meta_description' => '',
    'image_url'        => '',
    'record_count'     => 100,
    'data_size'        => '15 KB',
    'data_format'      => 'Excel, CSV',
    'data_source'      => 'Việt Nam',
    'reliability'      => '90%',
    'quota'            => 100,
    'quota_per_usage'  => 10,
    'supplier_name'    => 'Test Supplier Form',
    'supplier_title'   => 'Test Title Form',
    'supplier_bio'     => 'Test Bio Form',
    'supplier_avatar'  => '',
    'supplier_social'  => '{"website":"https://test.com","hotline":"19001234"}',
    'benefits'         => '["Test benefit"]',
    'data_structure'   => '[{"title":"Test"}]',
    'featured'         => '1',
    'downloadable'     => '1'
];

$_FILES = [];

echo "<p>POST data:</p>";
echo "<pre>" . print_r($_POST, true) . "</pre>";

echo "<h2>Step 2: Validate data (same as add.php)</h2>";

$errors = [];
$name = trim($_POST['name'] ?? '');
$category_id = (int)($_POST['category_id'] ?? 0);
$price = (float)($_POST['price'] ?? 0);
$description = trim($_POST['description'] ?? '');
$status = $_POST['status'] ?? 'active';

if (empty($name)) {
    $errors[] = 'Tên data không được để trống';
}

if ($category_id <= 0) {
    $errors[] = 'Vui lòng chọn danh mục';
}

if ($price <= 0) {
    $errors[] = 'Giá data phải lớn hơn 0';
}

if (empty($description)) {
    $errors[] = 'Mô tả data không được để trống';
}

if (!empty($errors)) {
    echo "<p style='color:red'>Validation errors:</p>";
    echo "<ul>" . implode('', array_map(fn($e) => "<li>$e</li>", $errors)) . "</ul>";
} else {
    echo "<p style='color:green'>✓ Validation passed</p>";
}

echo "<h2>Step 3: Check if ProductsModel can be loaded</h2>";

require_once __DIR__ . '/app/models/ProductsModel.php';

try {
    $productsModel = new ProductsModel();
    echo "<p style='color:green'>✓ ProductsModel loaded successfully</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Error loading ProductsModel: " . $e->getMessage() . "</p>";
    exit;
}

echo "<h2>Step 4: Create product data array</h2>";

$image_path = '';
$record_count = isset($_POST['record_count']) && $_POST['record_count'] !== '' ? (int)$_POST['record_count'] : 0;

// Helper function (same as in add.php)
function createSlugProduct($str) {
    $str = strtolower($str);
    $str = preg_replace('/[^a-z0-9\s-]/', '', $str);
    $str = preg_replace('/\s+/', '-', $str);
    $str = trim($str, '-');
    return $str;
}

$insertData = [
    'name'             => $name,
    'slug'             => createSlugProduct($name),
    'category_id'      => $category_id,
    'price'            => $price,
    'stock'            => $record_count,
    'description'      => $description,
    'status'           => $status,
    'type'             => $_POST['type'] ?? 'data_nguon_hang',
    'sale_price'       => isset($_POST['sale_price']) && $_POST['sale_price'] !== '' ? (float)$_POST['sale_price'] : null,
    'expiry_days'      => isset($_POST['expiry_days']) && $_POST['expiry_days'] !== '' ? (int)$_POST['expiry_days'] : 30,
    'sku'              => $_POST['sku'] ?? '',
    'short_description'=> $_POST['short_description'] ?? '',
    'meta_title'       => $_POST['meta_title'] ?? '',
    'meta_description' => $_POST['meta_description'] ?? '',
    'image'            => $image_path,
    'record_count'     => $record_count,
    'data_size'        => $_POST['data_size'] ?? '',
    'data_format'      => $_POST['data_format'] ?? '',
    'data_source'      => $_POST['data_source'] ?? '',
    'reliability'      => $_POST['reliability'] ?? '',
    'quota'            => isset($_POST['quota']) && $_POST['quota'] !== '' ? (int)$_POST['quota'] : 100,
    'quota_per_usage'  => isset($_POST['quota_per_usage']) && $_POST['quota_per_usage'] !== '' ? (int)$_POST['quota_per_usage'] : 10,
    'supplier_name'    => $_POST['supplier_name'] ?? '',
    'supplier_title'   => $_POST['supplier_title'] ?? '',
    'supplier_bio'     => $_POST['supplier_bio'] ?? '',
    'supplier_avatar'  => $_POST['supplier_avatar'] ?? '',
    'supplier_social'  => $_POST['supplier_social'] ?? '',
    'benefits'         => $_POST['benefits'] ?? '',
    'data_structure'   => $_POST['data_structure'] ?? '',
    'digital'          => 1,
    'featured'         => isset($_POST['featured']) ? 1 : 0,
    'downloadable'     => isset($_POST['downloadable']) ? 1 : 0,
    'created_at'       => date('Y-m-d H:i:s')
];

echo "<p>Insert data prepared:</p>";
echo "<pre>" . print_r($insertData, true) . "</pre>";

echo "<h2>Step 5: Call productsModel->create()</h2>";

try {
    $id = $productsModel->create($insertData);
    
    if ($id) {
        echo "<p style='color:green'>✓ Product created successfully! ID: " . $id . "</p>";
        
        // Verify
        $product = $productsModel->find($id);
        echo "<p>Product saved with these fields:</p>";
        echo "<pre>" . print_r($product, true) . "</pre>";
    } else {
        echo "<p style='color:red'>✗ create() returned false</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Exception: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h2>Test Complete</h2>";
?>
