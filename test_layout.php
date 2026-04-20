<?php
/**
 * Test render layout để tìm lỗi 500
 * Truy cập: https://test1.web3b.com/test_layout.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h1>Test Layout Render</h1><pre>";

$step = 0;

try {
    // Setup
    echo ++$step . ". Setup... ";
    define('THUONGLO_INIT', true);
    session_start();
    $base_dir = __DIR__;
    $config = require_once $base_dir . '/config.php';
    require_once $base_dir . '/core/security.php';
    require_once $base_dir . '/core/functions.php';
    require_once $base_dir . '/app/middleware/AuthMiddleware.php';
    require_once $base_dir . '/core/view_init.php';
    init_url_builder();
    echo "OK\n";

    // Set page variables
    echo ++$step . ". Setting page variables... ";
    $page = 'home';
    $title = 'Trang chủ - Thuong Lo';
    $content = 'app/views/home/home.php';
    $showPageHeader = false;
    $showCTA = true;
    $showBreadcrumb = false;
    $breadcrumbs = [];
    $currentService = $publicService ?? null;
    echo "OK\n";

    // Check if home.php has syntax errors
    echo ++$step . ". Checking home.php syntax... ";
    $output = [];
    $return = 0;
    exec("php -l " . escapeshellarg(__DIR__ . '/app/views/home/home.php') . " 2>&1", $output, $return);
    if ($return !== 0) {
        echo "SYNTAX ERROR:\n" . implode("\n", $output) . "\n";
    } else {
        echo "OK\n";
    }

    // Check if master.php has syntax errors
    echo ++$step . ". Checking master.php syntax... ";
    $output = [];
    $return = 0;
    exec("php -l " . escapeshellarg(__DIR__ . '/app/views/_layout/master.php') . " 2>&1", $output, $return);
    if ($return !== 0) {
        echo "SYNTAX ERROR:\n" . implode("\n", $output) . "\n";
    } else {
        echo "OK\n";
    }

    // Try to include master.php
    echo ++$step . ". Including master.php...\n";
    
    // Capture any output/errors
    ob_start();
    try {
        include_once 'app/views/_layout/master.php';
        $output_content = ob_get_clean();
        echo "   OK - Layout included successfully\n";
        echo "   Output length: " . strlen($output_content) . " chars\n";
    } catch (Exception $e) {
        ob_end_clean();
        throw $e;
    } catch (Error $e) {
        ob_end_clean();
        throw $e;
    }

    echo "\n=== Layout render successful! ===\n";
    
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
