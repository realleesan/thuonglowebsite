<?php
/**
 * Test Configuration Setup
 * Simple test to verify configuration is working
 */

// Load configuration
$config = require_once 'config.php';
require_once 'core/functions.php';

// Initialize URL Builder
init_url_builder();

echo "<h1>Configuration Test</h1>";

echo "<h2>Environment Detection</h2>";
echo "Environment: " . get_environment() . "<br>";
echo "Is Local: " . (is_local() ? 'Yes' : 'No') . "<br>";
echo "Is Hosting: " . (is_hosting() ? 'Yes' : 'No') . "<br>";
echo "Debug Mode: " . (is_debug() ? 'Enabled' : 'Disabled') . "<br>";

echo "<h2>URL Generation</h2>";
echo "Base URL: " . base_url() . "<br>";
echo "Asset URL (test.css): " . css_url('test.css') . "<br>";
echo "JS URL (test.js): " . js_url('test.js') . "<br>";
echo "Image URL (test.png): " . img_url('test.png') . "<br>";
echo "Page URL (products): " . page_url('products') . "<br>";
echo "Nav URL (about): " . nav_url('about') . "<br>";

echo "<h2>Configuration Values</h2>";
echo "App Name: " . config('app.name') . "<br>";
echo "Force HTTPS: " . (config('url.force_https') ? 'Yes' : 'No') . "<br>";
echo "WWW Redirect: " . config('url.www_redirect') . "<br>";
echo "Assets Path: " . config('paths.assets') . "<br>";

echo "<h2>Server Information</h2>";
echo "HTTP Host: " . ($_SERVER['HTTP_HOST'] ?? 'Not set') . "<br>";
echo "Server Port: " . ($_SERVER['SERVER_PORT'] ?? 'Not set') . "<br>";
echo "HTTPS: " . (isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : 'Not set') . "<br>";
echo "Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'Not set') . "<br>";

echo "<h2>Versioned Assets Test</h2>";
echo "Versioned CSS: " . versioned_css('main.css') . "<br>";
echo "Versioned JS: " . versioned_js('main.js') . "<br>";

echo "<p><strong>Test completed successfully!</strong></p>";
?>