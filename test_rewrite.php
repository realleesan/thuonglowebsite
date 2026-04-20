<?php
/**
 * Test URL rewrite và $_GET parameters
 * Truy cập: https://test1.web3b.com/test_rewrite.php?page=home
 * Hoặc: https://test1.web3b.com/test_rewrite.php?page=products
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test URL Rewrite</h1><pre>";

echo "=== Server Variables ===\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
echo "QUERY_STRING: " . ($_SERVER['QUERY_STRING'] ?? 'N/A') . "\n";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "\n";
echo "PHP_SELF: " . ($_SERVER['PHP_SELF'] ?? 'N/A') . "\n";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'N/A') . "\n";
echo "HTTPS: " . (isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : 'not set') . "\n";
echo "SERVER_PORT: " . ($_SERVER['SERVER_PORT'] ?? 'N/A') . "\n\n";

echo "=== GET Parameters ===\n";
print_r($_GET);
echo "\n";

echo "=== Testing page parameter ===\n";
$page = $_GET['page'] ?? 'home';
echo "Page: $page\n\n";

echo "=== Testing Rewrite Simulation ===\n";
// Simulate what index.php does
$_GET['page'] = $page;
echo "Simulating index.php with page=$page...\n";

// Check if this is causing redirect loop or error
if ($page === 'admin' || strpos($page, 'admin/') === 0) {
    echo "WARNING: Admin route detected - may require authentication\n";
}

echo "\n=== Test Complete ===\n";
echo "If index.php still shows 500, check:\n";
echo "1. .htaccess rewrite rules\n";
echo "2. File permissions on index.php\n";
echo "3. Try accessing index.php directly: /index.php?page=home\n";
echo "4. Check hosting error logs\n";

echo "</pre>";
