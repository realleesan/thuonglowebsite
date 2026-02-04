<?php
/**
 * Improved Configuration Test
 * More comprehensive testing
 */

// Load configuration
$config = require_once 'config.php';
require_once 'core/functions.php';

// Initialize URL Builder
init_url_builder();

echo "<h1>Improved Configuration Test</h1>";

echo "<h2>✅ Environment Detection</h2>";
$env = get_environment();
echo "Environment: <strong>$env</strong><br>";
echo "Is Local: " . (is_local() ? '✅ Yes' : '❌ No') . "<br>";
echo "Is Hosting: " . (is_hosting() ? '✅ Yes' : '❌ No') . "<br>";
echo "Debug Mode: " . (is_debug() ? '✅ Enabled' : '❌ Disabled') . "<br>";

echo "<h2>✅ URL Generation</h2>";
echo "Base URL: <strong>" . base_url() . "</strong><br>";

// Test asset URLs
$testAssets = [
    'CSS' => css_url('main.css'),
    'JS' => js_url('app.js'),
    'Image' => img_url('logo.png'),
    'Font' => font_url('awesome.woff'),
    'Icon' => icon_url('favicon.ico')
];

foreach ($testAssets as $type => $url) {
    $valid = filter_var($url, FILTER_VALIDATE_URL) ? '✅' : '❌';
    echo "$type URL: $valid $url<br>";
}

echo "<h2>✅ Page URLs</h2>";
$testPages = ['home', 'products', 'about', 'contact', 'news'];
foreach ($testPages as $page) {
    $url = page_url($page);
    $valid = filter_var($url, FILTER_VALIDATE_URL) ? '✅' : '❌';
    echo "Page '$page': $valid $url<br>";
}

echo "<h2>✅ Versioned Assets (Cache Busting)</h2>";
echo "Versioned CSS: " . versioned_css('main.css') . "<br>";
echo "Versioned JS: " . versioned_js('app.js') . "<br>";

echo "<h2>✅ Configuration Values</h2>";
$configTests = [
    'App Name' => config('app.name'),
    'Environment' => config('app.environment'),
    'Debug Mode' => config('app.debug') ? 'Enabled' : 'Disabled',
    'Force HTTPS' => config('url.force_https') ? 'Yes' : 'No',
    'WWW Redirect' => config('url.www_redirect'),
    'Assets Path' => config('paths.assets'),
    'Cache Assets' => config('performance.cache_assets') ? 'Yes' : 'No',
];

foreach ($configTests as $key => $value) {
    echo "$key: <strong>$value</strong><br>";
}

echo "<h2>✅ Server Environment</h2>";
$serverInfo = [
    'HTTP Host' => $_SERVER['HTTP_HOST'] ?? 'Not set',
    'Server Port' => $_SERVER['SERVER_PORT'] ?? 'Not set',
    'HTTPS' => $_SERVER['HTTPS'] ?? 'Not set',
    'Request URI' => $_SERVER['REQUEST_URI'] ?? 'Not set',
    'Document Root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Not set',
];

foreach ($serverInfo as $key => $value) {
    echo "$key: <strong>$value</strong><br>";
}

// Test URL validation
echo "<h2>✅ URL Validation Test</h2>";
$testUrls = [
    base_url(),
    css_url('test.css'),
    js_url('test.js'),
    img_url('test.png'),
    page_url('products'),
    page_url('products', ['category' => 'test', 'id' => 123])
];

$allValid = true;
foreach ($testUrls as $i => $url) {
    $valid = filter_var($url, FILTER_VALIDATE_URL);
    $status = $valid ? '✅ Valid' : '❌ Invalid';
    echo "URL " . ($i + 1) . ": $status - $url<br>";
    if (!$valid) $allValid = false;
}

echo "<hr>";
if ($allValid) {
    echo "<h2 style='color: green;'>🎉 All Tests Passed!</h2>";
    echo "<p>Configuration system is working perfectly on <strong>" . get_environment() . "</strong> environment.</p>";
} else {
    echo "<h2 style='color: red;'>❌ Some Issues Found</h2>";
    echo "<p>Please check the invalid URLs above.</p>";
}

echo "<h3>Next Steps:</h3>";
echo "<ul>";
echo "<li><a href='tests/run_tests.php'>Run Unit Tests</a></li>";
echo "<li><a href='debug_url_test.php'>Debug URL Generation</a></li>";
echo "<li><a href='index.php'>Test Main Website</a></li>";
echo "</ul>";
?>