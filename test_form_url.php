<?php
/**
 * Test file to check form action URL behavior
 * This tests if the relative URL in edit.php form action works correctly
 */

echo "<!DOCTYPE html>";
echo "<html><head><title>Test: Form URL</title>";
echo "<style>
    body { font-family: monospace; padding: 20px; }
    .result { padding: 10px; margin: 10px 0; background: #f0f0f0; }
    .success { color: green; }
    .error { color: red; }
</style>";
echo "</head><body>";
echo "<h1>Test: Form Action URL Behavior</h1>";

// Current URL info
echo "<div class='result'>";
echo "<h3>Current URL Information</h3>";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'NOT SET') . "<br>";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'NOT SET') . "<br>";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'NOT SET') . "<br>";
echo "QUERY_STRING: " . ($_SERVER['QUERY_STRING'] ?? 'NOT SET') . "<br>";
echo "</div>";

// Simulate what happens when form is submitted
echo "<div class='result'>";
echo "<h3>Form Submission Test</h3>";

// Current URL: https://test1.web3b.com/?page=admin&module=products&action=edit&id=34
// The form action is: ?page=admin&module=products&action=edit&id=34&tab=tab-basic
// This is RELATIVE - it just appends to current URL

$currentUrl = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
echo "Current full URL: $currentUrl<br><br>";

// If user is at: https://test1.web3b.com/?page=admin&module=products&action=edit&id=34
// And form action is: ?page=admin&module=products&action=edit&id=34&tab=tab-basic
// Browser will submit to: https://test1.web3b.com/?page=admin&module=products&action=edit&id=34&tab=tab-basic

// This SHOULD work... unless there's a redirect happening

// Let's check if there's any redirect happening
if (isset($_GET['test_redirect'])) {
    echo "<span class='success'>✓ Redirect was successful! Got test_redirect parameter.</span><br>";
    echo "tab parameter: " . ($_GET['tab'] ?? 'NOT SET') . "<br>";
} else {
    echo "No redirect detected (test_redirect not in URL)<br>";
}

echo "</div>";

// Test redirect behavior
echo "<div class='result'>";
echo "<h3>Test Links</h3>";

echo "<p><a href='?test_redirect=1'>Test redirect with relative URL (this page)</a></p>";
echo "<p><a href='?test_redirect=1&tab=tab-data'>Test redirect with tab parameter</a></p>";

echo "<form method='GET'>";
echo "<button type='submit' name='test_redirect' value='1'>Test GET form</button>";
echo "</form>";
echo "</div>";

echo "</body></html>";
