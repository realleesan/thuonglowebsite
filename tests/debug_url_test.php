<?php
/**
 * Debug URL Test
 * Check actual vs expected URL generation
 */

require_once 'config.php';
require_once 'core/UrlBuilder.php';

// Test config
$testConfig = [
    'app' => [
        'environment' => 'hosting',
        'debug' => false,
    ],
    'url' => [
        'force_https' => true,
        'www_redirect' => 'non-www',
        'remove_index_php' => true,
    ],
    'paths' => [
        'assets' => 'assets/',
        'uploads' => 'uploads/',
    ],
    'performance' => [
        'cache_assets' => true,
    ]
];

echo "<h1>Debug URL Generation</h1>";

echo "<h2>Current Server Info</h2>";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'Not set') . "<br>";
echo "SERVER_PORT: " . ($_SERVER['SERVER_PORT'] ?? 'Not set') . "<br>";
echo "HTTPS: " . ($_SERVER['HTTPS'] ?? 'Not set') . "<br>";

$urlBuilder = new UrlBuilder($testConfig);

echo "<h2>URL Generation Results</h2>";
echo "Base URL: " . $urlBuilder->getBaseUrl() . "<br>";
echo "Asset URL (css/main.css): " . $urlBuilder->asset('css/main.css') . "<br>";

$pageUrl = $urlBuilder->page('products');
$pageUrlWithParams = $urlBuilder->page('products', ['category' => 'electronics']);

echo "Page URL (products): " . $pageUrl . "<br>";
echo "Page URL with params: " . $pageUrlWithParams . "<br>";

echo "<h2>Expected vs Actual</h2>";
$expectedPageUrl = 'https://test1.web3b.com/?page=products';
$expectedPageUrlWithParams = 'https://test1.web3b.com/?page=products&category=electronics';

echo "Expected page URL: " . $expectedPageUrl . "<br>";
echo "Actual page URL: " . $pageUrl . "<br>";
echo "Match: " . ($pageUrl === $expectedPageUrl ? 'YES' : 'NO') . "<br><br>";

echo "Expected page URL with params: " . $expectedPageUrlWithParams . "<br>";
echo "Actual page URL with params: " . $pageUrlWithParams . "<br>";
echo "Match: " . ($pageUrlWithParams === $expectedPageUrlWithParams ? 'YES' : 'NO') . "<br>";

// Test with different environments
echo "<h2>Environment Test</h2>";
$localConfig = $testConfig;
$localConfig['app']['environment'] = 'local';
$localConfig['url']['force_https'] = false;

$localUrlBuilder = new UrlBuilder($localConfig);
echo "Local Base URL: " . $localUrlBuilder->getBaseUrl() . "<br>";
echo "Local Page URL: " . $localUrlBuilder->page('products') . "<br>";
?>