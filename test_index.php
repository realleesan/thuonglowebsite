<?php
/**
 * Test file mô phỏng chính xác index.php
 * Truy cập: https://test1.web3b.com/test_index.php?page=home
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h1>Test Index Flow</h1><pre>";

$step = 0;

try {
    // Step 1: Define constant
    echo ++$step . ". Defining THUONGLO_INIT... ";
    define('THUONGLO_INIT', true);
    echo "OK\n";

    // Step 2: Session
    echo ++$step . ". Starting session... ";
    session_start();
    echo "OK\n";

    // Step 3: Load config
    echo ++$step . ". Loading config.php... ";
    $base_dir = __DIR__;
    $config = require_once $base_dir . '/config.php';
    echo "OK (environment: " . $config['app']['environment'] . ")\n";

    // Step 4: Include security.php
    echo ++$step . ". Loading security.php... ";
    require_once $base_dir . '/core/security.php';
    echo "OK\n";

    // Step 5: Include functions.php
    echo ++$step . ". Loading functions.php... ";
    require_once $base_dir . '/core/functions.php';
    echo "OK\n";

    // Step 6: Include AuthMiddleware
    echo ++$step . ". Loading AuthMiddleware.php... ";
    require_once $base_dir . '/app/middleware/AuthMiddleware.php';
    echo "OK\n";

    // Step 7: Include view_init.php
    echo ++$step . ". Loading view_init.php... ";
    require_once $base_dir . '/core/view_init.php';
    echo "OK (services loaded)\n";

    // Step 8: Initialize URL Builder
    echo ++$step . ". Initializing URL builder... ";
    init_url_builder();
    echo "OK\n";

    // Step 9: Output buffering
    echo ++$step . ". Starting output buffer... ";
    if (ob_get_level() === 0) {
        ob_start();
    }
    echo "OK\n";

    // Step 10: Get page from URL
    echo ++$step . ". Getting page parameter... ";
    $page = $_GET['page'] ?? 'home';
    echo "OK (page: $page)\n";

    // Step 11: Initialize variables
    echo ++$step . ". Initializing page variables... ";
    $currentService = $publicService ?? null;
    $title = '';
    $content = '';
    $showPageHeader = false;
    $showCTA = false;
    $showBreadcrumb = false;
    $breadcrumbs = [];
    echo "OK\n";

    // Step 12: Route to page
    echo ++$step . ". Routing to page '$page'... ";
    switch($page) {
        case 'home':
            $title = 'Trang chủ - Thuong Lo';
            $content = 'app/views/home/home.php';
            $showPageHeader = false;
            $showCTA = true;
            $showBreadcrumb = false;
            $currentService = $publicService ?? $currentService;
            break;
            
        case 'products':
            $title = 'Sản phẩm - Thuong Lo';
            $content = 'app/views/products/products.php';
            $showPageHeader = true;
            $showCTA = false;
            $showBreadcrumb = true;
            $breadcrumbs = generate_breadcrumb('products');
            $currentService = $publicService ?? $currentService;
            break;
            
        case 'details':
        case 'course-details':
            $title = 'Chi tiết sản phẩm - Thuong Lo';
            $content = 'app/views/products/details.php';
            $showPageHeader = false;
            $showCTA = false;
            $showBreadcrumb = true;
            if (isset($_GET['id']) && is_numeric($_GET['id'])) {
                $breadcrumbs = get_product_breadcrumb_from_db($_GET['id']);
            } else {
                $product_name = $_GET['product'] ?? 'Sản phẩm';
                $breadcrumbs = [
                    ['title' => 'Trang chủ', 'url' => './'],
                    ['title' => 'Sản phẩm', 'url' => '?page=products'],
                    ['title' => $product_name]
                ];
            }
            $currentService = $publicService ?? $currentService;
            break;
            
        default:
            $title = 'Không tìm thấy trang - Thuong Lo';
            $content = 'errors/404.php';
            $showPageHeader = false;
            $showCTA = false;
            $showBreadcrumb = false;
            break;
    }
    echo "OK (content: $content)\n";

    // Step 13: Check if content file exists
    echo ++$step . ". Checking content file... ";
    if (!file_exists($content)) {
        echo "MISSING ($content)\n";
    } else {
        echo "OK\n";
    }

    // Step 14: Check layout file
    echo ++$step . ". Checking layout file... ";
    $layout = 'app/views/_layout/master.php';
    if (!file_exists($layout)) {
        echo "MISSING ($layout)\n";
    } else {
        echo "OK\n";
    }

    echo "\n=== All steps passed! ===\n";
    echo "If index.php still shows 500, the error might be in:\n";
    echo "- One of the included view files\n";
    echo "- A syntax error not caught by test\n";
    echo "- Server configuration (.htaccess)\n";
    
} catch (Exception $e) {
    echo "\n\n❌ EXCEPTION at step $step:\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "\n\n❌ FATAL ERROR at step $step:\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "</pre>";
