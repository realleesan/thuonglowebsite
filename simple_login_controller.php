<?php
// Simple login controller to test
session_start();

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>Processing Login (Simple Version)</h2>";
    
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';
    
    echo "Login: " . htmlspecialchars($login) . "<br>";
    echo "Password: " . str_repeat('*', strlen($password)) . "<br>";
    
    try {
        // Load required files
        require_once 'config.php';
        require_once 'app/services/AuthService.php';
        
        $authService = new AuthService();
        
        echo "AuthService loaded successfully<br>";
        
        $result = $authService->authenticate($login, $password);
        
        echo "<h3>Authentication Result:</h3>";
        echo "<pre>" . print_r($result, true) . "</pre>";
        
        if ($result['success']) {
            echo "<p style='color: green;'>Authentication successful!</p>";
            
            // Set flash message
            $_SESSION['flash_success'] = $result['message'];
            
            // Simple redirect
            echo "<script>window.location.href = '?page=users';</script>";
            echo "<p><a href='?page=users'>Click here if not redirected</a></p>";
            
        } else {
            echo "<p style='color: red;'>Authentication failed!</p>";
            echo "<p>Error: " . ($result['message'] ?? 'Unknown error') . "</p>";
            
            // Set flash message
            $_SESSION['flash_error'] = $result['message'];
            
            echo "<p><a href='?page=login'>Back to login</a></p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>Exception: " . $e->getMessage() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
    
} else {
    // Show simple form
    ?>
    <h2>Simple Login Form</h2>
    <form method="POST">
        <p>
            <label>Username/Email/Phone:</label><br>
            <input type="text" name="login" required>
        </p>
        <p>
            <label>Password:</label><br>
            <input type="password" name="password" required>
        </p>
        <p>
            <button type="submit">Login</button>
        </p>
    </form>
    <?php
}
?>