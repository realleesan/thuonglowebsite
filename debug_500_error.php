<?php
// Debug 500 error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Debug 500 Error</h1>";

// Test if we can load basic files
echo "<h2>Step 1: Testing basic file loading</h2>";

try {
    echo "Loading config.php... ";
    require_once 'config.php';
    echo "✓ OK<br>";
    
    echo "Loading core/database.php... ";
    require_once 'core/database.php';
    echo "✓ OK<br>";
    
    echo "Loading app/services/AuthService.php... ";
    require_once 'app/services/AuthService.php';
    echo "✓ OK<br>";
    
    echo "Loading app/controllers/AuthController.php... ";
    require_once 'app/controllers/AuthController.php';
    echo "✓ OK<br>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error loading files: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Test AuthController instantiation
echo "<h2>Step 2: Testing AuthController instantiation</h2>";
try {
    $authController = new AuthController();
    echo "✓ AuthController created successfully<br>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Error creating AuthController: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Test form processing simulation
echo "<h2>Step 3: Testing form processing simulation</h2>";
try {
    // Simulate POST data
    $_POST = [
        'login' => 'realleesan',
        'password' => '21042005nhatT@',
        'csrf_token' => 'test_token'
    ];
    $_SERVER['REQUEST_METHOD'] = 'POST';
    
    echo "POST data set<br>";
    echo "Calling processLogin method...<br>";
    
    // This might cause the 500 error
    $authController->processLogin();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error in processLogin: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} catch (Error $e) {
    echo "<p style='color: red;'>Fatal Error in processLogin: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h2>Step 4: Check error logs</h2>";
$errorLog = ini_get('error_log');
if ($errorLog && file_exists($errorLog)) {
    echo "Error log location: " . $errorLog . "<br>";
    $errors = file_get_contents($errorLog);
    $recentErrors = array_slice(explode("\n", $errors), -20);
    echo "<pre>" . implode("\n", $recentErrors) . "</pre>";
} else {
    echo "No error log found or configured<br>";
}
?>