<?php
// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "<h1>Homepage Error Debug</h1>";

try {
    echo "Loading config...<br>";
    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/core/database.php';
    
    echo "Creating HomepageController...<br>";
    require_once __DIR__ . '/app/controllers/HomepageController.php';
    
    echo "Instantiating controller...<br>";
    $controller = new HomepageController();
    
    echo "Calling index method...<br>";
    $controller->index();
    
    echo "✓ All operations completed successfully!<br>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>Exception caught:</h2>";
    echo "<strong>Error:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>File:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Line:</strong> " . $e->getLine() . "<br>";
    echo "<h3>Stack Trace:</h3>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} catch (Error $e) {
    echo "<h2 style='color: red;'>Fatal Error caught:</h2>";
    echo "<strong>Error:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>File:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Line:</strong> " . $e->getLine() . "<br>";
    echo "<h3>Stack Trace:</h3>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h2>PHP Error Log:</h2>";
$errorLog = ini_get('error_log');
if ($errorLog && file_exists($errorLog)) {
    echo "<pre>" . htmlspecialchars(file_get_contents($errorLog)) . "</pre>";
} else {
    echo "No error log found or logging disabled.";
}
?>
