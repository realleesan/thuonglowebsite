<?php
// Debug login process
session_start();

echo "<h2>Debug Login Process</h2>";

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>POST Data Received:</h3>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    // Check if we have login data
    if (isset($_POST['login']) && isset($_POST['password'])) {
        echo "<h3>Attempting Authentication...</h3>";
        
        try {
            // Load required files
            require_once 'config.php';
            require_once 'app/services/AuthService.php';
            
            $authService = new AuthService();
            
            $login = $_POST['login'];
            $password = $_POST['password'];
            
            echo "Login: " . htmlspecialchars($login) . "<br>";
            echo "Password length: " . strlen($password) . "<br>";
            
            $result = $authService->authenticate($login, $password);
            
            echo "<h3>Authentication Result:</h3>";
            echo "<pre>";
            print_r($result);
            echo "</pre>";
            
            if ($result['success']) {
                echo "<p style='color: green;'>Authentication successful!</p>";
                echo "<p>Redirecting to: " . ($result['redirect'] ?? 'default') . "</p>";
            } else {
                echo "<p style='color: red;'>Authentication failed!</p>";
                echo "<p>Error: " . ($result['message'] ?? 'Unknown error') . "</p>";
            }
            
        } catch (Exception $e) {
            echo "<p style='color: red;'>Exception: " . $e->getMessage() . "</p>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
        }
    }
} else {
    echo "<p>No POST data received. This should be called from the login form.</p>";
}

// Check session data
echo "<h3>Current Session Data:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Check if user is authenticated
echo "<h3>Authentication Check:</h3>";
try {
    require_once 'config.php';
    require_once 'app/services/AuthService.php';
    
    $authService = new AuthService();
    $isAuth = $authService->isAuthenticated();
    $currentUser = $authService->getCurrentUser();
    
    echo "Is Authenticated: " . ($isAuth ? 'Yes' : 'No') . "<br>";
    if ($currentUser) {
        echo "Current User: <pre>" . print_r($currentUser, true) . "</pre>";
    }
} catch (Exception $e) {
    echo "Error checking auth: " . $e->getMessage();
}
?>