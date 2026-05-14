<?php
// Test HeroSectionController index method directly
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>HeroSectionController Index Method Test</h1>";

// Mock session for admin
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';
$_SESSION['username'] = 'admin';

// Mock GET parameters
$_GET['page'] = 'admin';
$_GET['module'] = 'hero-section';

try {
    // Load required files
    require_once 'config.php';
    require_once 'core/view_init.php';
    require_once 'app/services/AuthService.php';
    require_once 'app/models/BaseModel.php';
    require_once 'app/models/HeroSectionModel.php';
    require_once 'app/models/HeroButtonModel.php';
    require_once 'app/controllers/HeroSectionController.php';
    
    echo "<h2>✓ All files loaded</h2>";
    
    // Create controller
    $controller = new HeroSectionController();
    echo "<h2>✓ Controller created</h2>";
    
    echo "<h2>Testing index() method:</h2>";
    
    // Capture output of index method
    ob_start();
    $controller->index();
    $output = ob_get_clean();
    
    if (!empty($output)) {
        echo "<p>✓ index() method produced output</p>";
        echo "<p>Output length: " . strlen($output) . " characters</p>";
        
        echo "<h3>Output preview:</h3>";
        echo "<div style='border: 1px solid #ccc; padding: 10px; background: #f9f9f9; max-height: 400px; overflow-y: scroll;'>";
        echo "<pre>" . htmlspecialchars(substr($output, 0, 1000)) . "</pre>";
        if (strlen($output) > 1000) {
            echo "<p>... (truncated)</p>";
        }
        echo "</div>";
    } else {
        echo "<p>✗ index() method produced no output</p>";
    }
    
} catch (Exception $e) {
    echo "<h2>✗ Error in index() method:</h2>";
    echo "<p style='color: red;'><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<h3>Stack Trace:</h3>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Also test the exact URL routing scenario
echo "<h1>URL Routing Simulation Test</h1>";

try {
    // Simulate exact same conditions as index.php
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['HTTP_HOST'] = 'test1.web3b.com';
    $_SERVER['REQUEST_URI'] = '/?page=admin&module=hero-section';
    
    $module = $_GET['module'] ?? 'dashboard';
    $action = $_GET['action'] ?? 'index';
    $id = $_GET['id'] ?? null;
    
    echo "<p>Module: $module</p>";
    echo "<p>Action: $action</p>";
    echo "<p>ID: " . ($id ?? 'null') . "</p>";
    
    // Recreate the exact routing logic
    require_once __DIR__ . '/app/controllers/HeroSectionController.php';
    $heroSectionController = new HeroSectionController();
    
    switch($action) {
        case 'create':
            echo "<p>Would call create()</p>";
            break;
        case 'edit':
            echo "<p>Would call edit($id)</p>";
            break;
        case 'update':
            echo "<p>Would call update($id)</p>";
            break;
        case 'delete':
            echo "<p>Would call delete($id)</p>";
            break;
        case 'index':
        default:
            echo "<p>Calling index() method...</p>";
            ob_start();
            $heroSectionController->index();
            $output = ob_get_clean();
            
            if (!empty($output)) {
                echo "<p>✓ Routing successful - output produced</p>";
                echo "<p>Output length: " . strlen($output) . " characters</p>";
            } else {
                echo "<p>✗ Routing failed - no output</p>";
            }
            break;
    }
    
} catch (Exception $e) {
    echo "<h2>✗ Routing error:</h2>";
    echo "<p style='color: red;'><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
