<?php
/**
 * Test session state and authentication
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Session Debug Test</h1>";

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h2>1. Session Information</h2>";
echo "<p>Session status: " . (session_status() === PHP_SESSION_ACTIVE ? "ACTIVE" : "INACTIVE") . "</p>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session name: " . session_name() . "</p>";

echo "<h2>2. Session Data</h2>";
if (empty($_SESSION)) {
    echo "<p>❌ Session is EMPTY</p>";
} else {
    echo "<p>✅ Session has data:</p>";
    echo "<pre>" . print_r($_SESSION, true) . "</pre>";
}

echo "<h2>3. Required Session Variables</h2>";
$required_vars = ['user_id', 'user_role', 'user_email', 'user_name'];
foreach ($required_vars as $var) {
    $value = $_SESSION[$var] ?? 'NOT SET';
    $status = isset($_SESSION[$var]) ? "✅" : "❌";
    echo "<p>$status $var: $value</p>";
}

echo "<h2>4. Test Login Status</h2>";
try {
    require_once 'app/services/AuthService.php';
    $auth = new AuthService();
    
    $isLoggedIn = $auth->isLoggedIn();
    echo "<p>isLoggedIn(): " . ($isLoggedIn ? "✅ TRUE" : "❌ FALSE") . "</p>";
    
    if ($isLoggedIn) {
        $user = $auth->getCurrentUser();
        echo "<p>Current user: <pre>" . print_r($user, true) . "</pre></p>";
        echo "<p>User role: " . ($user['role'] ?? 'N/A') . "</p>";
        echo "<p>Is admin: " . ($user['role'] === 'admin' ? "✅ YES" : "❌ NO") . "</p>";
    } else {
        echo "<p>❌ User not logged in</p>";
        
        // Try to get user anyway to see what happens
        try {
            $user = $auth->getCurrentUser();
            echo "<p>Current user (forced): <pre>" . print_r($user, true) . "</pre></p>";
        } catch (Exception $e) {
            echo "<p>Error getting current user: " . $e->getMessage() . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ AuthService error: " . $e->getMessage() . "</p>";
}

echo "<h2>5. Session Manager Test</h2>";
try {
    require_once 'app/services/SessionManager.php';
    $sessionManager = new SessionManager();
    
    $isValid = $sessionManager->isValid();
    echo "<p>SessionManager::isValid(): " . ($isValid ? "✅ TRUE" : "❌ FALSE") . "</p>";
    
} catch (Exception $e) {
    echo "<p>❌ SessionManager error: " . $e->getMessage() . "</p>";
}

echo "<h2>6. Device Session Check</h2>";
if (isset($_SESSION['user_id'])) {
    try {
        require_once 'app/services/DeviceAccessService.php';
        $deviceService = new DeviceAccessService();
        $userId = $_SESSION['user_id'];
        
        $deviceValid = $deviceService->checkCurrentDeviceSession($userId);
        echo "<p>Device session valid for user $userId: " . ($deviceValid ? "✅ TRUE" : "❌ FALSE") . "</p>";
        
        // Show current device info
        require_once 'app/models/DeviceAccessModel.php';
        $deviceModel = new DeviceAccessModel();
        $currentDevice = $deviceModel->findByUserAndSession($userId, session_id());
        
        if ($currentDevice) {
            echo "<p>Current device info:</p>";
            echo "<pre>" . print_r($currentDevice, true) . "</pre>";
        } else {
            echo "<p>❌ No current device found</p>";
            
            // Show all devices for this user
            $allDevices = $deviceModel->getActiveDevices($userId);
            echo "<p>All active devices for user $userId:</p>";
            echo "<pre>" . print_r($allDevices, true) . "</pre>";
        }
        
    } catch (Exception $e) {
        echo "<p>❌ Device check error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>❌ No user_id in session</p>";
}

echo "<h2>7. Test Manual Login</h2>";
echo "<p>If you're not logged in, try logging in first:</p>";
echo "<p><a href='?page=login'>Go to Login Page</a></p>";

echo "<h2>8. Fix Session</h2>";
echo "<p>If session is empty, you may need to:</p>";
echo "<ul>";
echo "<li>Clear browser cookies and login again</li>";
echo "<li>Check if session.save_path is writable</li>";
echo "<li>Verify session timeout settings</li>";
echo "</ul>";

echo "<h2>Test Complete</h2>";
?>
