<?php
/**
 * Test HomepageController specifically to find the 500 error
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h1>HomepageController Debug Test</h1>";

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h2>1. Session Before Test</h2>";
echo "<p>User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "</p>";
echo "<p>User Role: " . ($_SESSION['user_role'] ?? 'NOT SET') . "</p>";

echo "<h2>2. Load Required Files</h2>";

$required_files = [
    'app/controllers/HomepageController.php',
    'app/services/AuthService.php',
    'app/models/HeroSectionModel.php',
    'app/models/FeaturedProductsSectionModel.php',
    'app/models/LatestProductsSectionModel.php',
    'app/models/BudgetProductsSectionModel.php',
    'app/models/SaleProductsSectionModel.php',
    'core/view_init.php'
];

foreach ($required_files as $file) {
    try {
        require_once $file;
        echo "<p>✅ $file loaded</p>";
    } catch (Exception $e) {
        echo "<p>❌ $file failed: " . $e->getMessage() . "</p>";
    }
}

echo "<h2>3. Test HomepageController Instantiation</h2>";
try {
    $homepageController = new HomepageController();
    echo "<p>✅ HomepageController created</p>";
} catch (Exception $e) {
    echo "<p>❌ HomepageController failed: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace: <pre>" . $e->getTraceAsString() . "</pre></p>";
    exit;
}

echo "<h2>4. Test requireAdmin Method</h2>";
try {
    // Use reflection to access private method
    $reflection = new ReflectionClass($homepageController);
    $requireAdmin = $reflection->getMethod('requireAdmin');
    $requireAdmin->setAccessible(true);
    
    $result = $requireAdmin->invoke($homepageController);
    echo "<p>✅ requireAdmin() returned: " . ($result ? "TRUE" : "FALSE") . "</p>";
} catch (Exception $e) {
    echo "<p>❌ requireAdmin() failed: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace: <pre>" . $e->getTraceAsString() . "</pre></p>";
}

echo "<h2>5. Test Model Loading Step by Step</h2>";

// Test each model method that's called in index()
$tests = [
    'HeroSectionModel::getAllForAdmin()' => function() {
        $model = new HeroSectionModel();
        return $model->getAllForAdmin();
    },
    'FeaturedProductsSectionModel::getFirst()' => function() {
        $model = new FeaturedProductsSectionModel();
        return $model->getFirst();
    },
    'LatestProductsSectionModel::getFirst()' => function() {
        $model = new LatestProductsSectionModel();
        return $model->getFirst();
    },
    'BudgetProductsSectionModel::getFirst()' => function() {
        $model = new BudgetProductsSectionModel();
        return $model->getFirst();
    },
    'SaleProductsSectionModel::getFirst()' => function() {
        $model = new SaleProductsSectionModel();
        return $model->getFirst();
    }
];

foreach ($tests as $test_name => $test_func) {
    try {
        $result = $test_func();
        echo "<p>✅ $test_name - Success</p>";
    } catch (Exception $e) {
        echo "<p>❌ $test_name - Error: " . $e->getMessage() . "</p>";
        echo "<p>Stack trace: <pre>" . $e->getTraceAsString() . "</pre></p>";
    }
}

echo "<h2>6. Test Index Method with Output Buffering</h2>";
try {
    echo "<p>Testing index() method...</p>";
    
    // Capture output to prevent actual rendering
    ob_start();
    $homepageController->index();
    $output = ob_get_clean();
    
    echo "<p>✅ index() executed without fatal error</p>";
    echo "<p>Output length: " . strlen($output) . " characters</p>";
    
    // Check if output contains error indicators
    if (strpos($output, 'error') !== false || strpos($output, 'Error') !== false) {
        echo "<p>⚠️ Output contains potential errors</p>";
        echo "<p>Output preview: <pre>" . htmlspecialchars(substr($output, 0, 1000)) . "</pre></p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ index() failed: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace: <pre>" . $e->getTraceAsString() . "</pre></p>";
    
    // Check if this is the error that causes logout
    if (strpos($e->getMessage(), 'session') !== false) {
        echo "<p>🔥 SESSION-RELATED ERROR DETECTED!</p>";
    }
}

echo "<h2>7. Session After Test</h2>";
echo "<p>User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "</p>";
echo "<p>User Role: " . ($_SESSION['user_role'] ?? 'NOT SET') . "</p>";

// Check if session changed
if (session_id() !== 'aptqverlsga09l358fje3v5168') {
    echo "<p>🔥 SESSION ID CHANGED! This could cause logout!</p>";
    echo "<p>Old: aptqverlsga09l358fje3v5168</p>";
    echo "<p>New: " . session_id() . "</p>";
} else {
    echo "<p>✅ Session ID unchanged</p>";
}

echo "<h2>8. Memory Usage</h2>";
echo "<p>Memory before: " . memory_get_usage(true) . "</p>";
echo "<p>Peak memory: " . memory_get_peak_usage(true) . "</p>";

echo "<h2>Test Complete</h2>";
echo "<p><strong>If you see any red errors above, that's what's causing the 500 error!</strong></p>";
?>
