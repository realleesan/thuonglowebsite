<?php
/**
 * Test script to debug product creation
 * Run this file directly to test product creation
 */

echo "<h1>Test Product Creation</h1>";

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/database.php';
require_once __DIR__ . '/app/models/ProductsModel.php';

echo "<h2>Step 1: Check Database Connection</h2>";
try {
    $db = Database::getInstance();
    echo "<p style='color:green'>✓ Database connected successfully</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

echo "<h2>Step 2: Check ProductsModel</h2>";
try {
    $productsModel = new ProductsModel();
    echo "<p style='color:green'>✓ ProductsModel instantiated</p>";
    $reflection = new ReflectionClass($productsModel);
    $property = $reflection->getProperty('fillable');
    $property->setAccessible(true);
    $fillable = $property->getValue($productsModel);
    echo "<pre>Fillable fields: " . print_r($fillable, true) . "</pre>";
} catch (Exception $e) {
    echo "<p style='color:red'>✗ ProductsModel failed: " . $e->getMessage() . "</p>";
    exit;
}

echo "<h2>Step 3: Test Product Creation</h2>";

// Sample data matching the form in add.php
$testData = [
    'name'             => 'Test Product ' . date('Y-m-d H:i:s'),
    'slug'             => 'test-product-' . time(),
    'category_id'      => 1,
    'price'            => 1500000,
    'stock'            => 100,
    'description'      => 'Test description',
    'status'           => 'active',
    'type'             => 'data_nguon_hang',
    'sale_price'       => 990000,
    'expiry_days'      => 30,
    'sku'              => 'TEST-' . time(),
    'short_description'=> 'Test short description',
    'image'            => '',
    'record_count'     => 100,
    'data_size'        => '15 KB',
    'data_format'      => 'Excel, CSV',
    'data_source'      => 'Việt Nam',
    'reliability'      => '90%',
    'quota'            => 100,
    'quota_per_usage'  => 10,
    'supplier_name'    => 'Test Supplier',
    'supplier_title'   => 'Test Title',
    'supplier_bio'     => 'Test Bio',
    'supplier_avatar'  => '',
    'supplier_social' => '{"website":"https://test.com","hotline":"19001234"}',
    'benefits'         => '["Test benefit 1","Test benefit 2"]',
    'data_structure'   => '[{"title":"Test Group","items":[{"title":"Field 1"}]}]',
    'digital'          => 1,
    'featured'         => 0,
    'downloadable'     => 0,
    'created_at'       => date('Y-m-d H:i:s')
];

echo "<p>Testing with data:</p>";
echo "<pre>" . print_r($testData, true) . "</pre>";

echo "<h2>Step 4: Create Product</h2>";
try {
    $id = $productsModel->create($testData);
    echo "<p>Returned ID: " . var_export($id, true) . "</p>";
    
    if ($id) {
        echo "<p style='color:green'>✓ Product created successfully with ID: " . $id . "</p>";
        
        // Verify the product was created
        $product = $productsModel->find($id);
        echo "<p>Product details:</p>";
        echo "<pre>" . print_r($product, true) . "</pre>";
    } else {
        echo "<p style='color:red'>✗ Product creation returned false</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Exception: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace:</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h2>Step 5: Check products table structure</h2>";
try {
    $sql = "DESCRIBE products";
    $result = $db->query($sql);
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($result as $row) {
        echo "<tr>";
        echo "<td>" . ($row['Field'] ?? '') . "</td>";
        echo "<td>" . ($row['Type'] ?? '') . "</td>";
        echo "<td>" . ($row['Null'] ?? '') . "</td>";
        echo "<td>" . ($row['Key'] ?? '') . "</td>";
        echo "<td>" . ($row['Default'] ?? '') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Error getting table structure: " . $e->getMessage() . "</p>";
}

echo "<h2>Test Complete</h2>";
?>
