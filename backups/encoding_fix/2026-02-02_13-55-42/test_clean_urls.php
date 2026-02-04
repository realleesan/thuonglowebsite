<?php
/**
 * Clean URLs Test Script
 * Tests .htaccess URL rewriting functionality
 */

// Load configuration
require_once 'config.php';
require_once 'core/functions.php';

// Initialize URL builder
$config = get_config();
$urlBuilder = new UrlBuilder($config);

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clean URLs Test - Thuong Lo</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .container { max-width: 800px; margin: 0 auto; }
        h1 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        h2 { color: #666; border-bottom: 1px solid #ccc; padding-bottom: 5px; }
        .test-section { margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 5px; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .info { color: #17a2b8; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #e9ecef; }
        .test-links a { display: inline-block; margin: 5px 10px 5px 0; padding: 8px 15px; 
                       background: #007bff; color: white; text-decoration: none; border-radius: 4px; }
        .test-links a:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>ðŸ”— Clean URLs & .htaccess Test Results</h1>
        
        <div class="test-section">
            <h2>1. Environment Information</h2>
            <table>
                <tr><th>Property</th><th>Value</th></tr>
                <tr><td>Environment</td><td class="info"><?php echo detect_environment(); ?></td></tr>
                <tr><td>Base URL</td><td class="info"><?php echo $urlBuilder->getBaseUrl(); ?></td></tr>
                <tr><td>Current URL</td><td class="info"><?php echo $_SERVER['REQUEST_URI']; ?></td></tr>
                <tr><td>HTTP Host</td><td class="info"><?php echo $_SERVER['HTTP_HOST']; ?></td></tr>
                <tr><td>HTTPS</td><td class="info"><?php echo isset($_SERVER['HTTPS']) ? 'Yes' : 'No'; ?></td></tr>
            </table>
        </div>

        <div class="test-section">
            <h2>2. URL Generation Test</h2>
            <table>
                <tr><th>Type</th><th>Generated URL</th><th>Status</th></tr>
                <?php
                $testUrls = [
                    'Home Page' => $urlBuilder->page('home'),
                    'Products' => $urlBuilder->page('products'),
                    'News' => $urlBuilder->page('news'),
                    'Contact' => $urlBuilder->page('contact'),
                    'Login' => $urlBuilder->page('login')
                ];
                
                foreach ($testUrls as $name => $url) {
                    $hasIndexPhp = strpos($url, 'index.php') !== false;
                    $status = $hasIndexPhp ? '<span class="error">Contains index.php</span>' : '<span class="success">Clean URL</span>';
                    echo "<tr><td>$name</td><td>$url</td><td>$status</td></tr>";
                }
                ?>
            </table>
        </div>

        <div class="test-section">
            <h2>3. Asset URLs Test</h2>
            <table>
                <tr><th>Asset Type</th><th>Generated URL</th><th>Accessible</th></tr>
                <?php
                $testAssets = [
                    'CSS File' => css_url('home.css'),
                    'JS File' => js_url('home.js'),
                    'Logo Image' => img_url('logo/logo.png'),
                    'Font File' => asset_url('fonts/awesome-5x/fa-solid-900.woff2')
                ];
                
                foreach ($testAssets as $type => $url) {
                    // Simple check if URL is properly formatted
                    $isValid = filter_var($url, FILTER_VALIDATE_URL) !== false;
                    $status = $isValid ? '<span class="success">Valid URL</span>' : '<span class="error">Invalid URL</span>';
                    echo "<tr><td>$type</td><td>$url</td><td>$status</td></tr>";
                }
                ?>
            </table>
        </div>

        <div class="test-section">
            <h2>4. Navigation Links Test</h2>
            <p>Click these links to test clean URL navigation:</p>
            <div class="test-links">
                <a href="<?php echo nav_url('home'); ?>">Home</a>
                <a href="<?php echo nav_url('products'); ?>">Products</a>
                <a href="<?php echo nav_url('news'); ?>">News</a>
                <a href="<?php echo nav_url('contact'); ?>">Contact</a>
                <a href="<?php echo nav_url('login'); ?>">Login</a>
            </div>
        </div>

        <div class="test-section">
            <h2>5. .htaccess Rules Test</h2>
            <?php
            // Check if .htaccess file exists
            $htaccessExists = file_exists('.htaccess');
            echo "<p><strong>.htaccess file exists:</strong> " . ($htaccessExists ? '<span class="success">Yes</span>' : '<span class="error">No</span>') . "</p>";
            
            if ($htaccessExists) {
                $htaccessContent = file_get_contents('.htaccess');
                $hasRewriteEngine = strpos($htaccessContent, 'RewriteEngine On') !== false;
                $hasIndexPhpRemoval = strpos($htaccessContent, 'index\.php') !== false;
                $hasSecurityRules = strpos($htaccessContent, 'config.php') !== false;
                
                echo "<p><strong>RewriteEngine enabled:</strong> " . ($hasRewriteEngine ? '<span class="success">Yes</span>' : '<span class="error">No</span>') . "</p>";
                echo "<p><strong>Index.php removal rules:</strong> " . ($hasIndexPhpRemoval ? '<span class="success">Yes</span>' : '<span class="error">No</span>') . "</p>";
                echo "<p><strong>Security rules:</strong> " . ($hasSecurityRules ? '<span class="success">Yes</span>' : '<span class="error">No</span>') . "</p>";
            }
            ?>
        </div>

        <div class="test-section">
            <h2>6. Security Test</h2>
            <p>These links should be blocked by .htaccess rules:</p>
            <div class="test-links">
                <a href="config.php" target="_blank">config.php</a>
                <a href="logs/error.log" target="_blank">error.log</a>
                <a href="README.md" target="_blank">README.md</a>
                <a href="core/functions.php" target="_blank">functions.php</a>
            </div>
            <p><em>If these links show content instead of 403/404 errors, security rules need adjustment.</em></p>
        </div>

        <div class="test-section">
            <h2>7. Form Action URLs</h2>
            <form method="GET" action="<?php echo nav_url('contact'); ?>">
                <p><strong>Contact form action:</strong> <?php echo nav_url('contact'); ?></p>
                <input type="hidden" name="test" value="form_submission">
                <button type="submit" style="padding: 8px 15px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer;">Test Form Submit</button>
            </form>
        </div>

        <div class="test-section">
            <h2>8. URL Rewriting Status</h2>
            <?php
            $currentUrl = $_SERVER['REQUEST_URI'];
            $hasCleanUrl = strpos($currentUrl, 'index.php') === false;
            $hasQueryParams = strpos($currentUrl, '?') !== false;
            
            echo "<p><strong>Current URL is clean:</strong> " . ($hasCleanUrl ? '<span class="success">Yes</span>' : '<span class="error">No</span>') . "</p>";
            echo "<p><strong>Using query parameters:</strong> " . ($hasQueryParams ? '<span class="info">Yes</span>' : '<span class="info">No</span>') . "</p>";
            
            // Test if mod_rewrite is working
            if (function_exists('apache_get_modules')) {
                $modules = apache_get_modules();
                $rewriteEnabled = in_array('mod_rewrite', $modules);
                echo "<p><strong>mod_rewrite enabled:</strong> " . ($rewriteEnabled ? '<span class="success">Yes</span>' : '<span class="error">No</span>') . "</p>";
            } else {
                echo "<p><strong>mod_rewrite status:</strong> <span class="info">Cannot determine (function not available)</span></p>";
            }
            ?>
        </div>

        <hr>
        <p><em>Test completed at: <?php echo date('Y-m-d H:i:s'); ?></em></p>
        <p><em>Test file: <?php echo basename(__FILE__); ?></em></p>
    </div>
</body>
</html>