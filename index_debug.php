<?php
/**
 * Debug version của index.php
 * Truy cập: https://test1.web3b.com/index_debug.php?page=home
 */

// Bật tất cả error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Log lỗi vào file
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/index_debug_errors.log');

echo "<h1>Index Debug Mode</h1><pre>";
echo "Starting index_debug.php...\n\n";

$step = 0;

try {
    echo ++$step . ". Defining THUONGLO_INIT... ";
    define('THUONGLO_INIT', true);
    echo "OK\n";

    echo ++$step . ". Starting session... ";
    session_start();
    echo "OK\n";

    echo ++$step . ". Loading config... ";
    $base_dir = __DIR__;
    $config = require_once $base_dir . '/config.php';
    echo "OK (env: " . $config['app']['environment'] . ")\n";

    // Set error reporting based on config
    if ($config['app']['debug']) {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
    } else {
        error_reporting(0);
        ini_set('display_errors', 0);
    }

    echo ++$step . ". Loading security.php... ";
    require_once $base_dir . '/core/security.php';
    echo "OK\n";

    echo ++$step . ". Loading functions.php... ";
    require_once $base_dir . '/core/functions.php';
    echo "OK\n";

    echo ++$step . ". Loading AuthMiddleware... ";
    require_once $base_dir . '/app/middleware/AuthMiddleware.php';
    echo "OK\n";

    echo ++$step . ". Loading view_init.php... ";
    require_once $base_dir . '/core/view_init.php';
    echo "OK\n";

    echo ++$step . ". Initializing URL builder... ";
    init_url_builder();
    echo "OK\n";

    echo ++$step . ". Starting output buffer... ";
    if (ob_get_level() === 0) {
        ob_start();
    }
    echo "OK\n";

    echo ++$step . ". Getting page parameter... ";
    $page = $_GET['page'] ?? 'home';
    echo "OK (page: $page)\n";

    echo ++$step . ". Setting up variables... ";
    $currentService = $publicService ?? null;
    $title = '';
    $content = '';
    $showPageHeader = false;
    $showCTA = false;
    $showBreadcrumb = false;
    $breadcrumbs = [];
    $useAdminLayout = false;
    $useAffiliateLayout = false;
    echo "OK\n";

    echo ++$step . ". Routing (switch)... ";
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
            
        default:
            $title = 'Không tìm thấy trang - Thuong Lo';
            $content = 'errors/404.php';
            $showPageHeader = false;
            $showCTA = false;
            $showBreadcrumb = false;
            break;
    }
    echo "OK (content: $content)\n";

    echo ++$step . ". Checking file exists... ";
    if (!file_exists($content)) {
        echo "MISSING! ($content)\n";
        $content = 'errors/404.php';
    } else {
        echo "OK\n";
    }

    echo ++$step . ". Including layout... ";
    echo "\n   - About to include: app/views/_layout/master.php\n";
    echo "   - Content file: $content\n";
    
    // Actually include the layout
    $useAffiliateLayout = false;
    $useAdminLayout = false;
    
    if (isset($useAdminLayout) && $useAdminLayout) {
        echo "   - Using admin layout\n";
        include_once 'app/views/_layout/admin_master.php';
    } elseif (isset($useAffiliateLayout) && $useAffiliateLayout) {
        echo "   - Using affiliate layout\n";
        include_once $content;
    } else {
        echo "   - Using master layout\n";
        include_once 'app/views/_layout/master.php';
    }
    echo "   - Layout included successfully!\n";

    echo ++$step . ". Flushing output buffer... ";
    if (ob_get_level() > 0) {
        ob_end_flush();
    }
    echo "OK\n";

    echo "\n=== Index loaded successfully! ===\n";
    
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
