<?php
/**
 * Test file to debug pagination issue
 * This file will help diagnose the pagination problem on the products page
 */

echo "<h1>Debug Pagination Issue</h1>";

// Simulate the current GET parameters from products page
$_GET['page'] = 'products';  // This is the routing parameter
$_GET['category'] = '1';     // Example category filter
$_GET['order_by'] = 'post_date'; // Example sort

echo "<h2>Current \$_GET parameters:</h2>";
echo "<pre>";
print_r($_GET);
echo "</pre>";

// Current problematic pagination URL generation (from products.php line 420, 434, 442)
echo "<h2>Current (PROBLEMATIC) pagination URL generation:</h2>";
for ($i = 1; $i <= 3; $i++) {
    $url = "?".http_build_query(array_merge($_GET, ['page' => $i]));
    echo "Page $i: <a href='$url'>$url</a><br>";
}

// Show what happens when we try to get page parameter
echo "<h2>Page parameter extraction:</h2>";
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
echo "Extracted page: $page (should be pagination number, but gets 'products' string)<br>";

// CORRECT approach - use different parameter name for pagination
echo "<h2>CORRECT pagination URL generation:</h2>";
for ($i = 1; $i <= 3; $i++) {
    // Remove any existing pagination parameter and add the correct one
    $getParams = $_GET;
    unset($getParams['p']); // Remove old pagination param if exists
    $getParams['p'] = $i;   // Add new pagination param
    
    $url = "?".http_build_query($getParams);
    echo "Page $i: <a href='$url'>$url</a><br>";
}

// Show correct page parameter extraction
echo "<h2>Correct page parameter extraction:</h2>";
$paginationPage = isset($_GET['p']) ? (int) $_GET['p'] : 1;
echo "Extracted pagination page: $paginationPage (correct!)<br>";

echo "<h2>Summary of the problem:</h2>";
echo "<ul>";
echo "<li><strong>Issue:</strong> The pagination uses 'page' parameter which conflicts with routing</li>";
echo "<li><strong>Current behavior:</strong> ?page=products&page=2 becomes ?page=2 (loses routing)</li>";
echo "<li><strong>Solution:</strong> Use 'p' parameter for pagination instead of 'page'</li>";
echo "</ul>";

echo "<h2>Required changes:</h2>";
echo "<ol>";
echo "<li>In products.php: Change pagination links to use 'p' parameter</li>";
echo "<li>In products.php: Change page extraction to use \$_GET['p'] instead of \$_GET['page']</li>";
echo "<li>Update all URL building logic to preserve 'page=products' for routing</li>";
echo "</ol>";

// Test the fix
echo "<h2>Testing the fix:</h2>";
$_GET['page'] = 'products';
$_GET['p'] = 2;
$_GET['category'] = '1';

$currentPage = isset($_GET['p']) ? (int)$_GET['p'] : 1;
echo "Current page from 'p' parameter: $currentPage<br>";

// Build URL for next page
$nextPage = $currentPage + 1;
$getParams = $_GET;
$getParams['p'] = $nextPage;
$nextUrl = "?".http_build_query($getParams);
echo "Next page URL: <a href='$nextUrl'>$nextUrl</a><br>";

echo "<h2>Conclusion:</h2>";
echo "<p>The pagination issue is caused by parameter name conflict. The 'page' parameter is used for routing (?page=products) but pagination also tries to use 'page' for page numbers, causing the routing to break.</p>";
?>
