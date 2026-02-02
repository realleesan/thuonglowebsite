<?php
/**
 * URL Rewriting Test Script
 * Tests clean URLs and .htaccess functionality
 */

// Load configuration
require_once 'config.php';
require_once 'core/functions.php';

// Initialize URL builder
$config = get_config();
$urlBuilder = new UrlBuilder($config);

echo "<h1>URL Rewriting Test Results</h1>";

// Test 1: Clean URL generation
echo "<h2>1. Clean URL Generation</h2>";
$testUrls = [
    'home' => '',
    'products' => '?page=products',
    'news' => '?page=news',
    'contact' => '?page=contact',
    'auth' => '?page=auth'
];

foreach ($testUrls as $name => $path) {
    $url = $urlBuilder->url($path);
    echo "<p><strong>$name:</strong> $url</p>";
}

// Test 2: Asset URL generation
echo "<h2>2. Asset URL Generation</h2>";
$testAssets = [
    'CSS' => css_url('home.css'),
    'JS' => js_url('home.js'),
    'Image' => img_url('logo/logo.png'),
    'Font' => asset_url('fonts/awesome-5x/fa-solid-900.woff2')
];

foreach ($testAssets as $type => $url) {
    echo "<p><strong>$type:</strong> $url</p>";
}

// Test 3: Environment detection
echo "<h2>3. Environment Detection</h2>";
$environment = detect_environment();
echo "<p><strong>Current Environment:</strong> $environment</p>";
echo "<p><strong>Base URL:</strong> " . $urlBuilder->getBaseUrl() . "</p>";

// Test 4: Check if .htaccess rules are working
echo "<h2>4. .htaccess Rules Test</h2>";

// Check if mod_rewrite is enabled
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    $rewrite_enabled = in_array('mod_rewrite', $modules);
    echo "<p><strong>mod_rewrite enabled:</strong> " . ($rewrite_enabled ? 'Yes' : 'No') . "</p>";
} else {
    echo "<p><strong>mod_rewrite status:</strong> Cannot determine (apache_get_modules not available)</p>";
}

// Test current URL structure
$current_url = $_SERVER['REQUEST_URI'];
echo "<p><strong>Current URL:</strong> $current_url</p>";

// Check if index.php is in URL
$has_index_php = strpos($current_url, 'index.php') !== false;
echo "<p><strong>Contains index.php:</strong> " . ($has_index_php ? 'Yes (needs redirect)' : 'No (clean URL)') . "</p>";

// Test 5: Security file access
echo "<h2>5. Security Test</h2>";
echo "<p>Try accessing these URLs to test security:</p>";
echo "<ul>";
echo "<li><a href='" . $urlBuilder->url('config.php') . "' target='_blank'>config.php</a> (should be blocked)</li>";
echo "<li><a href='" . $urlBuilder->url('logs/error.log') . "' target='_blank'>logs/error.log</a> (should be blocked)</li>";
echo "<li><a href='" . $urlBuilder->url('README.md') . "' target='_blank'>README.md</a> (should be blocked)</li>";
echo "</ul>";

// Test 6: Navigation links
echo "<h2>6. Navigation Links Test</h2>";
$nav_links = [
    'Home' => nav_url('home'),
    'Products' => nav_url('products'),
    'News' => nav_url('news'),
    'Contact' => nav_url('contact'),
    'Login' => nav_url('auth')
];

echo "<ul>";
foreach ($nav_links as $name => $url) {
    echo "<li><a href='$url'>$name</a></li>";
}
echo "</ul>";

// Test 7: Form action URLs
echo "<h2>7. Form Action URLs</h2>";
echo "<form method='POST' action='" . nav_url('auth') . "'>";
echo "<p>Login form action: " . nav_url('auth') . "</p>";
echo "<input type='hidden' name='test' value='1'>";
echo "<button type='submit'>Test Form Submit</button>";
echo "</form>";

echo "<hr>";
echo "<p><em>Test completed at: " . date('Y-m-d H:i:s') . "</em></p>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1 { color: #333; }
h2 { color: #666; border-bottom: 1px solid #ccc; }
p { margin: 5px 0; }
ul { margin: 10px 0; }
form { background: #f5f5f5; padding: 10px; border-radius: 5px; }
</style>