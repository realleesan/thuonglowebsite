<?php
/**
 * Complete authentication system test
 */

// Define security constant
define('THUONGLO_INIT', true);

// Start session
session_start();

// Load configuration
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/security.php';
require_once __DIR__ . '/core/functions.php';
require_once __DIR__ . '/app/middleware/AuthMiddleware.php';

echo "<h2>Complete Authentication System Test</h2>";

// Test current session
echo "<h3>Current Session Status:</h3>";
echo "User ID: " . ($_SESSION['user_id'] ?? 'Not set') . "<br>";
echo "User Email: " . ($_SESSION['user_email'] ?? 'Not set') . "<br>";
echo "User Role: " . ($_SESSION['user_role'] ?? 'Not set') . "<br>";

// Test AuthMiddleware
echo "<h3>AuthMiddleware Test Results:</h3>";
$authMiddleware = new AuthMiddleware();

// Capture output to prevent redirects during testing
ob_start();
$isAuth = $authMiddleware->requireAuth();
$authOutput = ob_get_clean();

ob_start();
$isAdmin = $authMiddleware->requireAdmin();
$adminOutput = ob_get_clean();

ob_start();
$isAffiliate = $authMiddleware->requireAffiliate();
$affiliateOutput = ob_get_clean();

echo "Authentication check: " . ($isAuth ? 'PASS' : 'FAIL') . "<br>";
echo "Admin check: " . ($isAdmin ? 'PASS' : 'FAIL') . "<br>";
echo "Affiliate check: " . ($isAffiliate ? 'PASS' : 'FAIL') . "<br>";

// Test access control simulation
echo "<h3>Access Control Simulation:</h3>";
echo "<strong>Based on current session:</strong><br>";

if (!isset($_SESSION['user_id'])) {
    echo "❌ Not logged in - Admin/Affiliate access should be DENIED<br>";
    echo "✅ Expected behavior: Redirect to login page<br>";
} else {
    $role = $_SESSION['user_role'] ?? 'user';
    echo "✅ Logged in as: $role<br>";
    
    switch($role) {
        case 'admin':
            echo "✅ Admin access: ALLOWED<br>";
            echo "✅ Affiliate access: ALLOWED (admin can access affiliate)<br>";
            break;
        case 'affiliate':
            echo "❌ Admin access: DENIED<br>";
            echo "✅ Affiliate access: ALLOWED<br>";
            break;
        case 'user':
        default:
            echo "❌ Admin access: DENIED<br>";
            echo "❌ Affiliate access: DENIED<br>";
            break;
    }
}

// Test direct access links
echo "<h3>Test Direct Access (Click to test):</h3>";
echo "<ul>";
echo "<li><a href='?page=admin' target='_blank'>🔗 Direct Admin Access</a></li>";
echo "<li><a href='?page=affiliate' target='_blank'>🔗 Direct Affiliate Access</a></li>";
echo "<li><a href='?page=users' target='_blank'>🔗 Direct User Dashboard Access</a></li>";
echo "</ul>";

echo "<h3>Expected Results:</h3>";
echo "<ul>";
echo "<li>If not logged in: All should redirect to login page</li>";
echo "<li>If logged in as user: Admin/Affiliate should redirect to user dashboard</li>";
echo "<li>If logged in as affiliate: Admin should redirect to affiliate dashboard</li>";
echo "<li>If logged in as admin: All should work</li>";
echo "</ul>";

echo "<br><a href='./'>🏠 Back to Home</a> | ";
echo "<a href='?page=login'>🔐 Login Page</a> | ";
echo "<a href='test_auth.php'>🔄 Refresh Test</a>";
?>