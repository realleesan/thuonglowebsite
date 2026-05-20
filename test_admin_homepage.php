<?php
/**
 * Test file for diagnosing admin homepage 500 error
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h1>Admin Homepage Debug Test</h1>";

// Test 1: Check if basic files exist
echo "<h2>1. File Existence Check</h2>";
$files_to_check = [
    'app/controllers/HomepageController.php',
    'app/models/HeroSectionModel.php',
    'app/models/FeaturedProductsSectionModel.php',
    'app/models/LatestProductsSectionModel.php',
    'app/models/BudgetProductsSectionModel.php',
    'app/models/SaleProductsSectionModel.php',
    'app/services/AuthService.php',
    'app/views/admin/homepage/index.php',
    'core/view_init.php'
];

foreach ($files_to_check as $file) {
    $exists = file_exists($file) ? "✅ EXISTS" : "❌ MISSING";
    echo "<p>$file: $exists</p>";
}

// Test 2: Check database connection
echo "<h2>2. Database Connection Test</h2>";
try {
    require_once 'config.php';
    require_once 'core/database.php';
    
    $db = Database::getInstance();
    $pdo = $db->getPdo();
    
    echo "<p>✅ Database connection successful</p>";
    
    // Test if required tables exist
    $tables_to_check = [
        'hero_sections',
        'featured_products_section',
        'latest_products_section',
        'budget_products_section',
        'sale_products_section'
    ];
    
    foreach ($tables_to_check as $table) {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            $exists = $stmt->rowCount() > 0 ? "✅ EXISTS" : "❌ MISSING";
            echo "<p>Table '$table': $exists</p>";
        } catch (Exception $e) {
            echo "<p>Table '$table': ❌ ERROR - " . $e->getMessage() . "</p>";
        }
    }
} catch (Exception $e) {
    echo "<p>❌ Database connection failed: " . $e->getMessage() . "</p>";
}

// Test 3: Try to load AuthService
echo "<h2>3. AuthService Test</h2>";
try {
    require_once 'app/services/AuthService.php';
    $authService = new AuthService();
    echo "<p>✅ AuthService loaded successfully</p>";
    
    // Check if user is authenticated
    $isLoggedIn = $authService->isLoggedIn();
    echo "<p>User logged in: " . ($isLoggedIn ? "✅ YES" : "❌ NO") . "</p>";
    
    if ($isLoggedIn) {
        $user = $authService->getCurrentUser();
        echo "<p>User role: " . ($user['role'] ?? 'N/A') . "</p>";
        echo "<p>Is admin: " . ($user['role'] === 'admin' ? "✅ YES" : "❌ NO") . "</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ AuthService error: " . $e->getMessage() . "</p>";
}

// Test 4: Try to load models
echo "<h2>4. Models Loading Test</h2>";
$models_to_test = [
    'HeroSectionModel' => 'app/models/HeroSectionModel.php',
    'FeaturedProductsSectionModel' => 'app/models/FeaturedProductsSectionModel.php',
    'LatestProductsSectionModel' => 'app/models/LatestProductsSectionModel.php',
    'BudgetProductsSectionModel' => 'app/models/BudgetProductsSectionModel.php',
    'SaleProductsSectionModel' => 'app/models/SaleProductsSectionModel.php'
];

foreach ($models_to_test as $model_class => $model_file) {
    try {
        require_once $model_file;
        $model = new $model_class();
        echo "<p>✅ $model_class loaded successfully</p>";
        
        // Test getFirst method if it exists
        if (method_exists($model, 'getFirst')) {
            try {
                $result = $model->getFirst();
                echo "<p>   - getFirst() method works: " . ($result !== null ? "✅ Returns data" : "⚠️ Returns null") . "</p>";
            } catch (Exception $e) {
                echo "<p>   - getFirst() method error: " . $e->getMessage() . "</p>";
            }
        }
        
        // Test getAllForAdmin method for HeroSectionModel
        if ($model_class === 'HeroSectionModel' && method_exists($model, 'getAllForAdmin')) {
            try {
                $result = $model->getAllForAdmin();
                echo "<p>   - getAllForAdmin() method works: " . (is_array($result) ? "✅ Returns array" : "⚠️ Unexpected result") . "</p>";
            } catch (Exception $e) {
                echo "<p>   - getAllForAdmin() method error: " . $e->getMessage() . "</p>";
            }
        }
        
    } catch (Exception $e) {
        echo "<p>❌ $model_class error: " . $e->getMessage() . "</p>";
    }
}

// Test 5: Try to load HomepageController
echo "<h2>5. HomepageController Test</h2>";
try {
    require_once 'app/controllers/HomepageController.php';
    $homepageController = new HomepageController();
    echo "<p>✅ HomepageController loaded successfully</p>";
    
    // Check if index method exists
    if (method_exists($homepageController, 'index')) {
        echo "<p>✅ index() method exists</p>";
        
        // Try to call index method (but don't output, just catch errors)
        try {
            ob_start();
            $homepageController->index();
            $output = ob_get_clean();
            echo "<p>✅ index() method executed without fatal errors</p>";
            echo "<p>Output length: " . strlen($output) . " characters</p>";
        } catch (Exception $e) {
            echo "<p>❌ index() method error: " . $e->getMessage() . "</p>";
            echo "<p>Stack trace: <pre>" . $e->getTraceAsString() . "</pre></p>";
        }
    } else {
        echo "<p>❌ index() method missing</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ HomepageController error: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace: <pre>" . $e->getTraceAsString() . "</pre></p>";
}

// Test 6: Check session state
echo "<h2>6. Session State</h2>";
if (session_status() === PHP_SESSION_NONE) {
    echo "<p>Session not started</p>";
} else {
    echo "<p>Session active</p>";
    echo "<p>Session ID: " . session_id() . "</p>";
    echo "<p>Session data: <pre>" . print_r($_SESSION, true) . "</pre></p>";
}

// Test 7: Memory and performance
echo "<h2>7. System Info</h2>";
echo "<p>Memory usage: " . memory_get_usage(true) . " bytes</p>";
echo "<p>Memory limit: " . ini_get('memory_limit') . "</p>";
echo "<p>Max execution time: " . ini_get('max_execution_time') . "</p>";

echo "<h2>Test Complete</h2>";
echo "<p><a href='?page=admin&module=homepage'>Try accessing admin homepage again</a></p>";
?>
