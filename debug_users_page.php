<?php
// Debug users page
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Debug Users Page</h1>";

// Start session
session_start();

echo "<h2>1. Session Status</h2>";
echo "Session ID: " . session_id() . "<br>";
echo "Session status: " . session_status() . "<br>";

echo "<h2>2. Session Data</h2>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";

echo "<h2>3. Authentication Check</h2>";
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    echo "✓ User ID: " . $_SESSION['user_id'] . "<br>";
    echo "✓ User authenticated<br>";
} else {
    echo "✗ User not authenticated<br>";
    echo "Redirecting to login...<br>";
    echo "<script>setTimeout(function() { window.location.href = '?page=login'; }, 3000);</script>";
}

echo "<h2>4. Test Loading Users Dashboard</h2>";
try {
    // Test if we can load the dashboard file
    $dashboardFile = 'app/views/users/dashboard.php';
    if (file_exists($dashboardFile)) {
        echo "✓ Dashboard file exists: $dashboardFile<br>";
        
        // Test loading core files
        echo "Loading core files...<br>";
        require_once 'config.php';
        echo "✓ Config loaded<br>";
        
        require_once 'core/view_init.php';
        echo "✓ View init loaded<br>";
        
        // Test loading the dashboard
        echo "Loading dashboard content...<br>";
        ob_start();
        include $dashboardFile;
        $dashboardContent = ob_get_clean();
        
        if (strlen($dashboardContent) > 0) {
            echo "✓ Dashboard loaded successfully (" . strlen($dashboardContent) . " bytes)<br>";
        } else {
            echo "✗ Dashboard loaded but empty<br>";
        }
        
    } else {
        echo "✗ Dashboard file not found: $dashboardFile<br>";
    }
    
} catch (Exception $e) {
    echo "✗ Error loading dashboard: " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} catch (Error $e) {
    echo "✗ Fatal error loading dashboard: " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h2>5. Test Direct Access</h2>";
echo "<p><a href='?page=users' target='_blank'>Test ?page=users (new tab)</a></p>";
echo "<p><a href='app/views/users/dashboard.php' target='_blank'>Test dashboard.php directly (new tab)</a></p>";

echo "<h2>6. File Existence Check</h2>";
$requiredFiles = [
    'app/views/users/dashboard.php',
    'app/views/_layout/master.php',
    'core/view_init.php',
    'assets/css/users_dashboard.css',
    'assets/js/users_dashboard.js'
];

foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        echo "✓ $file<br>";
    } else {
        echo "✗ $file (missing)<br>";
    }
}
?>