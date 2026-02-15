<?php
// Simple users page test
session_start();

// Set fake session for testing
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['user_name'] = 'Test User';
    $_SESSION['user_email'] = 'test@example.com';
    $_SESSION['username'] = 'testuser';
    $_SESSION['user_role'] = 'user';
    $_SESSION['is_authenticated'] = true;
}

echo "<h1>Simple Users Page Test</h1>";

echo "<h2>Session Data:</h2>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";

echo "<h2>Loading Dashboard...</h2>";

try {
    // Simulate the users page loading
    $page = 'users';
    $module = $_GET['module'] ?? 'dashboard';
    $title = 'Tài khoản - Thuong Lo';
    $showPageHeader = false;
    $showCTA = false;
    $showBreadcrumb = true;
    $breadcrumbs = [
        ['title' => 'Trang chủ', 'url' => './'],
        ['title' => 'Tài khoản']
    ];
    
    // Check authentication
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        echo "Not authenticated, would redirect to login<br>";
        exit;
    }
    
    echo "✓ Authentication check passed<br>";
    
    switch($module) {
        case 'dashboard':
        default:
            $content = 'app/views/users/dashboard.php';
            $title = 'Tài khoản của tôi - Thuong Lo';
            break;
    }
    
    echo "✓ Content file: $content<br>";
    
    if (file_exists($content)) {
        echo "✓ Content file exists<br>";
        
        // Load required files
        require_once 'config.php';
        echo "✓ Config loaded<br>";
        
        // Try to include the dashboard
        echo "<h3>Dashboard Content:</h3>";
        echo "<div style='border: 1px solid #ccc; padding: 10px; max-height: 400px; overflow: auto;'>";
        
        ob_start();
        include $content;
        $dashboardHTML = ob_get_clean();
        
        if (strlen($dashboardHTML) > 0) {
            echo $dashboardHTML;
        } else {
            echo "Dashboard loaded but no content generated";
        }
        
        echo "</div>";
        
    } else {
        echo "✗ Content file not found: $content<br>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Exception: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} catch (Error $e) {
    echo "<p style='color: red;'>Fatal Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h3>Links:</h3>";
echo "<ul>";
echo "<li><a href='debug_users_page.php'>Debug Users Page</a></li>";
echo "<li><a href='?page=users'>Try Real Users Page</a></li>";
echo "<li><a href='?page=login'>Login Page</a></li>";
echo "</ul>";
?>