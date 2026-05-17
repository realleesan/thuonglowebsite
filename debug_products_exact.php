<?php
/**
 * Debug Products Page by including the exact products.php file
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', '512M');

echo "<h1>Exact Products Page Debug</h1>";

try {
    echo "<h2>Step 1: Including products.php directly</h2>";
    
    // Set up the environment exactly like the routing system
    $_GET['page'] = 'products';
    
    // Include the products.php file directly
    ob_start();
    include __DIR__ . '/app/views/products/products.php';
    $output = ob_get_clean();
    
    echo "✅ products.php included successfully<br>";
    echo "Output length: " . strlen($output) . " characters<br>";
    
    // Show first 1000 characters of output
    echo "<h2>First 1000 characters of output:</h2>";
    echo "<pre>" . htmlspecialchars(substr($output, 0, 1000)) . "</pre>";
    
    if (strlen($output) > 1000) {
        echo "<p>... (output truncated)</p>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Exception: " . $e->getMessage() . "</h2>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} catch (Error $e) {
    echo "<h2>❌ Fatal Error: " . $e->getMessage() . "</h2>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} catch (ParseError $e) {
    echo "<h2>❌ Parse Error: " . $e->getMessage() . "</h2>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
