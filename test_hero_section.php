<?php
// Test file for Hero Section Admin Page Debugging
// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h1>Hero Section Admin Page Debug Test</h1>";

// Test 1: Check if required files exist
echo "<h2>1. File Existence Check</h2>";

$requiredFiles = [
    'app/controllers/HeroSectionController.php',
    'app/models/HeroSectionModel.php', 
    'app/models/HeroButtonModel.php',
    'app/services/AuthService.php',
    'app/views/admin/homepage/hero_section/index.php',
    'app/views/_layout/admin_master.php',
    'core/view_init.php'
];

foreach ($requiredFiles as $file) {
    $exists = file_exists($file) ? '<span style="color: green;">✓ EXISTS</span>' : '<span style="color: red;">✗ MISSING</span>';
    echo "<p>$file: $exists</p>";
}

// Test 2: Check database connection
echo "<h2>2. Database Connection Test</h2>";
try {
    require_once 'config.php';
    $config = require_once 'config.php';
    
    // Test database connection using same method as core
    $host = $config['database']['host'];
    $dbname = $config['database']['dbname']; 
    $username = $config['database']['username'];
    $password = $config['database']['password'];
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo '<p><span style="color: green;">✓ Database connection successful</span></p>';
    
    // Check if hero_sections table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'hero_sections'");
    $tableExists = $stmt->rowCount() > 0;
    $tableStatus = $tableExists ? '<span style="color: green;">✓ EXISTS</span>' : '<span style="color: red;">✗ MISSING</span>';
    echo "<p>hero_sections table: $tableStatus</p>";
    
    // Check if hero_buttons table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'hero_buttons'");
    $tableExists = $stmt->rowCount() > 0;
    $tableStatus = $tableExists ? '<span style="color: green;">✓ EXISTS</span>' : '<span style="color: red;">✗ MISSING</span>';
    echo "<p>hero_buttons table: $tableStatus</p>";
    
} catch (Exception $e) {
    echo '<p><span style="color: red;">✗ Database connection failed: ' . $e->getMessage() . '</span></p>';
}

// Test 3: Try to load HeroSectionController
echo "<h2>3. Controller Loading Test</h2>";
try {
    require_once 'app/controllers/HeroSectionController.php';
    echo '<p><span style="color: green;">✓ HeroSectionController loaded successfully</span></p>';
    
    // Try to instantiate controller
    $controller = new HeroSectionController();
    echo '<p><span style="color: green;">✓ HeroSectionController instantiated successfully</span></p>';
    
} catch (Exception $e) {
    echo '<p><span style="color: red;">✗ Controller loading failed: ' . $e->getMessage() . '</span></p>';
    echo '<p><strong>Stack trace:</strong><br><pre>' . $e->getTraceAsString() . '</pre></p>';
}

// Test 4: Check session and authentication
echo "<h2>4. Session & Authentication Test</h2>";
session_start();
echo '<p>Session status: ' . (session_status() === PHP_SESSION_ACTIVE ? '<span style="color: green;">ACTIVE</span>' : '<span style="color: red;">INACTIVE</span>') . '</p>';
echo '<p>Session ID: ' . session_id() . '</p>';
echo '<p>User logged in: ' . (isset($_SESSION['user_id']) ? '<span style="color: green;">YES (ID: ' . $_SESSION['user_id'] . ')</span>' : '<span style="color: red;">NO</span>') . '</p>';
echo '<p>User role: ' . (isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '<span style="color: red;">NOT SET</span>') . '</p>';

// Test 5: Try to simulate the admin page request
echo "<h2>5. Admin Page Simulation Test</h2>";
try {
    // Mock the GET parameters
    $_GET['page'] = 'admin';
    $_GET['module'] = 'hero-section';
    
    // Try to load required dependencies
    require_once 'core/view_init.php';
    echo '<p><span style="color: green;">✓ view_init.php loaded</span></p>';
    
    require_once 'app/services/AuthService.php';
    echo '<p><span style="color: green;">✓ AuthService loaded</span></p>';
    
    $authService = new AuthService();
    echo '<p><span style="color: green;">✓ AuthService instantiated</span></p>';
    
    // Check admin authentication
    $isAdmin = $authService->isLoggedIn() && $authService->hasRole('admin');
    echo '<p>Admin access: ' . ($isAdmin ? '<span style="color: green;">GRANTED</span>' : '<span style="color: red;">DENIED</span>') . '</p>';
    
    if ($isAdmin) {
        // Try to load controller and call index method
        require_once 'app/controllers/HeroSectionController.php';
        $controller = new HeroSectionController();
        
        echo '<p><span style="color: green;">✓ Ready to call controller index method</span></p>';
        echo '<p><strong>Note:</strong> The actual index() method will try to render a view, which might cause the WSOD if there are view/template issues.</p>';
    }
    
} catch (Exception $e) {
    echo '<p><span style="color: red;">✗ Admin page simulation failed: ' . $e->getMessage() . '</span></p>';
    echo '<p><strong>Stack trace:</strong><br><pre>' . $e->getTraceAsString() . '</pre></p>';
}

// Test 6: Check view file contents
echo "<h2>6. View File Content Check</h2>";
$viewFile = 'app/views/admin/homepage/hero_section/index.php';
if (file_exists($viewFile)) {
    echo '<p><span style="color: green;">✓ View file exists</span></p>';
    
    // Check if file is readable
    $content = file_get_contents($viewFile);
    if ($content !== false) {
        echo '<p><span style="color: green;">✓ View file is readable</span></p>';
        echo '<p>File size: ' . number_format(strlen($content)) . ' bytes</p>';
        
        // Check for common issues
        if (strpos($content, '<?php') === 0) {
            echo '<p><span style="color: green;">✓ File starts with PHP tag</span></p>';
        } else {
            echo '<p><span style="color: orange;">⚠ File does not start with PHP tag</span></p>';
        }
        
        // Check for syntax errors
        $tempFile = tempnam(sys_get_temp_dir(), 'hero_test_');
        file_put_contents($tempFile, $content);
        
        $output = [];
        $returnCode = 0;
        exec("php -l \"$tempFile\" 2>&1", $output, $returnCode);
        
        if ($returnCode === 0) {
            echo '<p><span style="color: green;">✓ PHP syntax is valid</span></p>';
        } else {
            echo '<p><span style="color: red;">✗ PHP syntax error:</span></p>';
            echo '<pre>' . implode("\n", $output) . '</pre>';
        }
        
        unlink($tempFile);
    } else {
        echo '<p><span style="color: red;">✗ Cannot read view file</span></p>';
    }
} else {
    echo '<p><span style="color: red;">✗ View file does not exist</span></p>';
}

// Test 7: Check error logs
echo "<h2>7. Recent Error Log Check</h2>";
$errorLog = ini_get('error_log');
if ($errorLog && file_exists($errorLog)) {
    echo '<p>Error log file: ' . $errorLog . '</p>';
    
    // Read last 20 lines of error log
    $lines = file($errorLog);
    if ($lines) {
        $recentLines = array_slice($lines, -20);
        echo '<p><strong>Last 20 lines of error log:</strong></p>';
        echo '<pre style="background: #f5f5f5; padding: 10px; border: 1px solid #ccc; max-height: 300px; overflow-y: scroll;">';
        foreach ($recentLines as $line) {
            echo htmlspecialchars($line);
        }
        echo '</pre>';
    }
} else {
    echo '<p>No error log file found or error logging is disabled</p>';
}

echo "<h2>8. PHP Environment Info</h2>";
echo '<p>PHP Version: ' . phpversion() . '</p>';
echo '<p>Memory Limit: ' . ini_get('memory_limit') . '</p>';
echo '<p>Max Execution Time: ' . ini_get('max_execution_time') . '</p>';
echo '<p>Display Errors: ' . (ini_get('display_errors') ? 'ON' : 'OFF') . '</p>';
echo '<p>Error Reporting: ' . error_reporting() . '</p>';

echo "<h2>Test Complete</h2>";
echo "<p>Copy these results and send them back for further analysis.</p>";
?>
