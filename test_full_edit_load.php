<?php
/**
 * Test that loads edit page through index.php and captures any errors
 * Run: https://test1.web3b.com/test_full_edit_load.php?id=34
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set a flag to indicate this is a test
$_GET['page'] = 'admin';
$_GET['module'] = 'products';
$_GET['action'] = 'edit';
$_GET['id'] = isset($_GET['id']) ? (int)$_GET['id'] : 34;

echo "<h1>Testing Edit Page Load</h1>";
echo "<p>Loading edit page for product ID: {$_GET['id']}</p>";

// Capture output
ob_start();

try {
    // Simulate what index.php does
    require_once __DIR__ . '/config.php';
    
    echo "<p>Config loaded</p>";
    
    // Include index.php to process the request
    // But we need to be careful about what we include
    
    // Let's just test the admin routing part
    $page = 'admin';
    $module = 'products';
    $action = 'edit';
    $product_id = (int)$_GET['id'];
    
    echo "<p>page=$page, module=$module, action=$action, id=$product_id</p>";
    
    // Check if the file exists
    $editFile = __DIR__ . '/app/views/admin/products/edit.php';
    echo "<p>Edit file exists: " . (file_exists($editFile) ? 'YES' : 'NO') . "</p>";
    
    if (file_exists($editFile)) {
        echo "<p style='color:green'>Edit file found!</p>";
    } else {
        echo "<p style='color:red'>Edit file NOT found!</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}

$output = ob_get_clean();
echo $output;

echo "<hr>";
echo "<h2>Manual Test</h2>";
echo "<p><a href='?page=admin&module=products&action=edit&id=34'>Go to Edit Page</a></p>";
