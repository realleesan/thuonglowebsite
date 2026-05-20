<?php
/**
 * Simple test to catch the exact error
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

echo "<h1>Simple Homepage Test</h1>";

try {
    // Load HomepageController
    require_once 'app/controllers/HomepageController.php';
    $homepageController = new HomepageController();
    
    echo "<p>✅ Controller loaded</p>";
    
    // Test index method directly
    echo "<p>Testing index() method...</p>";
    
    $homepageController->index();
    
    echo "<p>✅ index() completed</p>";
    
} catch (Exception $e) {
    echo "<h2>🔥 ERROR FOUND!</h2>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<p><strong>Stack trace:</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    
    // Check if this affects session
    echo "<h2>Session Check</h2>";
    echo "<p>Session ID: " . session_id() . "</p>";
    echo "<p>User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "</p>";
} catch (Error $e) {
    echo "<h2>🔥 FATAL ERROR FOUND!</h2>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<p><strong>Stack trace:</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
