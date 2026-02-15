<?php
// Test authentication flow step by step
session_start();

echo "<h1>Authentication Flow Test</h1>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>Step 1: POST Data Received</h2>";
    echo "<pre>" . print_r($_POST, true) . "</pre>";
    
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($login) || empty($password)) {
        echo "<p style='color: red;'>Login or password is empty!</p>";
        exit;
    }
    
    echo "<h2>Step 2: Loading AuthService</h2>";
    try {
        require_once 'config.php';
        require_once 'app/services/AuthService.php';
        
        $authService = new AuthService();
        echo "<p style='color: green;'>AuthService loaded successfully</p>";
        
        echo "<h2>Step 3: Calling authenticate method</h2>";
        $result = $authService->authenticate($login, $password);
        
        echo "<h3>Authentication Result:</h3>";
        echo "<pre>" . print_r($result, true) . "</pre>";
        
        if ($result['success']) {
            echo "<h2>Step 4: Authentication Successful</h2>";
            echo "<p style='color: green;'>Login successful!</p>";
            
            echo "<h3>Checking session after authentication:</h3>";
            echo "<pre>" . print_r($_SESSION, true) . "</pre>";
            
            echo "<h3>Testing isAuthenticated:</h3>";
            $isAuth = $authService->isAuthenticated();
            echo "<p>Is Authenticated: " . ($isAuth ? 'Yes' : 'No') . "</p>";
            
            echo "<h3>Testing getCurrentUser:</h3>";
            $currentUser = $authService->getCurrentUser();
            echo "<pre>" . print_r($currentUser, true) . "</pre>";
            
            echo "<p><a href='?page=users' style='color: blue; font-size: 18px;'>Go to User Dashboard</a></p>";
            
        } else {
            echo "<h2>Step 4: Authentication Failed</h2>";
            echo "<p style='color: red;'>Login failed: " . ($result['message'] ?? 'Unknown error') . "</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>Exception in authentication: " . $e->getMessage() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
    
} else {
    // Show form
    ?>
    <h2>Login Test Form</h2>
    <form method="POST" style="border: 1px solid #ccc; padding: 20px; max-width: 400px;">
        <p>
            <label><strong>Email/Username/Phone:</strong></label><br>
            <input type="text" name="login" required style="width: 100%; padding: 8px; margin-top: 5px;">
        </p>
        <p>
            <label><strong>Password:</strong></label><br>
            <input type="password" name="password" required style="width: 100%; padding: 8px; margin-top: 5px;">
        </p>
        <p>
            <button type="submit" style="background: #007cba; color: white; padding: 10px 20px; border: none; cursor: pointer;">Test Login</button>
        </p>
    </form>
    
    <h3>Current Session Data:</h3>
    <pre><?php print_r($_SESSION); ?></pre>
    
    <h3>Quick Links:</h3>
    <ul>
        <li><a href="check_users.php">Check Users in Database</a></li>
        <li><a href="test_login_simple.php">Simple Login Test</a></li>
        <li><a href="?page=login">Go to Login Page</a></li>
    </ul>
    <?php
}
?>