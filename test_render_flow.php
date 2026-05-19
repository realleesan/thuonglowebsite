<?php
/**
 * Test file to check rendering flow for admin products add
 * URL: http://test1.web3b.com/test_render_flow.php
 */

// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h1>Testing Admin Products Add Rendering Flow</h1>";
echo "<hr>";

// Simulate the exact flow from index.php
session_start();

// Test 1: Load config
echo "<h2>Step 1: Loading Config</h2>";
$config = require_once __DIR__ . '/config.php';
echo "✓ Config loaded<br><br>";

// Test 2: Load view_init
echo "<h2>Step 2: Loading view_init.php</h2>";
require_once __DIR__ . '/core/view_init.php';
echo "✓ view_init loaded<br>";
echo "✓ AdminService: " . (isset($adminService) ? 'YES' : 'NO') . "<br><br>";

// Test 3: Set admin variables (simulating index.php lines 617-620)
echo "<h2>Step 3: Setting Admin Variables</h2>";
$useAdminLayout = true;
$currentService = $adminService;
$page_title = 'Quản lý Sản phẩm';
echo "✓ useAdminLayout = true<br>";
echo "✓ currentService set to adminService<br>";
echo "✓ page_title = '$page_title'<br><br>";

// Test 4: Set content path (simulating index.php line 784)
echo "<h2>Step 4: Setting Content Path</h2>";
$content = 'app/views/admin/products/add.php';
echo "✓ content = '$content'<br>";
echo "✓ File exists: " . (file_exists($content) ? 'YES' : 'NO') . "<br><br>";

// Test 5: Try to include the content file directly
echo "<h2>Step 5: Testing Direct Include of Content</h2>";
echo "<div style='border:2px solid #3B82F6; padding:20px; margin:20px 0; background:#EFF6FF;'>";
echo "<strong>Content from add.php:</strong><br><br>";

ob_start();
try {
    include $content;
    $output = ob_get_clean();
    
    if (empty(trim($output))) {
        echo "<span style='color:red;'>⚠️ WARNING: Content file included but produced NO OUTPUT!</span><br>";
        echo "This means the file is executing but not echoing anything.<br>";
    } else {
        echo "✓ Content file produced output (" . strlen($output) . " bytes)<br>";
        echo "<details><summary>Show first 500 characters</summary>";
        echo "<pre>" . htmlspecialchars(substr($output, 0, 500)) . "...</pre>";
        echo "</details>";
    }
} catch (Exception $e) {
    ob_end_clean();
    echo "<span style='color:red;'>✗ Error including content: " . $e->getMessage() . "</span><br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
echo "</div><br>";

// Test 6: Check if admin_master.php exists and can be included
echo "<h2>Step 6: Testing Admin Master Layout</h2>";
$layoutPath = 'app/views/_layout/admin_master.php';
echo "✓ Layout path: $layoutPath<br>";
echo "✓ File exists: " . (file_exists($layoutPath) ? 'YES' : 'NO') . "<br><br>";

// Test 7: Simulate the exact rendering from index.php (lines 2195-2196)
echo "<h2>Step 7: Simulating Exact Rendering Flow</h2>";
echo "<div style='border:2px solid #10B981; padding:20px; margin:20px 0; background:#D1FAE5;'>";
echo "<strong>Checking conditions:</strong><br>";
echo "• isset(\$useAdminLayout) = " . (isset($useAdminLayout) ? 'true' : 'false') . "<br>";
echo "• \$useAdminLayout value = " . ($useAdminLayout ? 'true' : 'false') . "<br>";
echo "• isset(\$content) = " . (isset($content) ? 'true' : 'false') . "<br>";
echo "• \$content value = " . ($content ?? 'NOT SET') . "<br><br>";

if (isset($useAdminLayout) && $useAdminLayout) {
    echo "✓ Condition met: Will include admin_master.php<br>";
    echo "<strong>Now including admin_master.php...</strong><br>";
} else {
    echo "✗ Condition NOT met - admin_master.php will NOT be included!<br>";
}
echo "</div><br>";

echo "<hr>";
echo "<h2>FINAL TEST: Full Render</h2>";
echo "<p>Below should show the full admin page with sidebar, header, and content:</p>";
echo "<div style='border:3px solid #EF4444; padding:10px; background:#FEE2E2;'>";

// Reset content variable
$content = 'app/views/admin/products/add.php';
$useAdminLayout = true;

// Include the layout (this should render everything)
if (isset($useAdminLayout) && $useAdminLayout) {
    include_once 'app/views/_layout/admin_master.php';
} else {
    echo "ERROR: useAdminLayout not set properly!";
}

echo "</div>";
?>
