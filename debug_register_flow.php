<?php
// Debug registration flow
define('THUONGLO_INIT', true);
session_start();

// Load configuration
$config = require_once __DIR__ . '/config.php';

// Include necessary files
require_once __DIR__ . '/core/functions.php';
require_once __DIR__ . '/core/view_init.php';

echo "<h2>Debug Registration Flow</h2>";

// Test 1: Check if services are loaded
echo "<h3>1. Services Status:</h3>";
echo "AuthService: " . (isset($authService) ? "✓ Loaded" : "✗ Not loaded") . "<br>";
echo "SessionManager: " . (isset($sessionManager) ? "✓ Loaded" : "✗ Not loaded") . "<br>";
echo "UsersModel: " . (isset($usersModel) ? "✓ Loaded" : "✗ Not loaded") . "<br>";

// Test 2: Check session
echo "<h3>2. Session Status:</h3>";
echo "Session ID: " . session_id() . "<br>";
echo "Session Status: " . session_status() . "<br>";
echo "Is Authenticated: " . (isset($_SESSION['is_authenticated']) ? "Yes" : "No") . "<br>";

// Test 3: Check flash messages
echo "<h3>3. Flash Messages:</h3>";
if (isset($_SESSION['flash_success'])) {
    echo "<div style='color: green; padding: 10px; border: 1px solid green;'>";
    echo "SUCCESS: " . $_SESSION['flash_success'];
    echo "</div>";
}
if (isset($_SESSION['flash_error'])) {
    echo "<div style='color: red; padding: 10px; border: 1px solid red;'>";
    echo "ERROR: " . $_SESSION['flash_error'];
    echo "</div>";
}

// Test 4: Simulate registration
if (isset($_POST['test_register'])) {
    echo "<h3>4. Testing Registration:</h3>";
    
    $userData = [
        'name' => 'Test User ' . time(),
        'username' => 'testuser' . time(),
        'email' => 'test' . time() . '@example.com',
        'phone' => '0123456789',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'address' => 'Test Address',
        'ref_code' => '',
    ];
    
    try {
        if (isset($authService)) {
            $result = $authService->register($userData);
            echo "<pre>";
            print_r($result);
            echo "</pre>";
            
            if ($result['success']) {
                echo "<div style='color: green; padding: 10px; border: 1px solid green;'>";
                echo "Registration successful!";
                echo "</div>";
                
                // Check if user is now logged in
                echo "Is now authenticated: " . ($authService->isAuthenticated() ? "Yes" : "No") . "<br>";
                if ($authService->isAuthenticated()) {
                    $currentUser = $authService->getCurrentUser();
                    echo "Current user: " . ($currentUser['name'] ?? 'Unknown') . "<br>";
                }
            } else {
                echo "<div style='color: red; padding: 10px; border: 1px solid red;'>";
                echo "Registration failed: " . $result['message'];
                echo "</div>";
            }
        } else {
            echo "<div style='color: red;'>AuthService not available</div>";
        }
    } catch (Exception $e) {
        echo "<div style='color: red; padding: 10px; border: 1px solid red;'>";
        echo "Exception: " . $e->getMessage();
        echo "</div>";
    }
}

// Test form
echo "<h3>Test Registration:</h3>";
echo '<form method="POST">';
echo '<input type="hidden" name="test_register" value="1">';
echo '<button type="submit">Test Register New User</button>';
echo '</form>';

echo "<hr>";
echo "<p><a href='/'>Go to Home</a></p>";
echo "<p><a href='?page=register'>Go to Register Page</a></p>";
?>