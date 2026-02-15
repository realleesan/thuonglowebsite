<?php
// Final login test with proper URL
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

echo "<h1>Final Login Test</h1>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';
    
    echo "<h2>Testing AuthService with: " . htmlspecialchars($login) . "</h2>";
    
    try {
        require_once 'config.php';
        require_once 'app/services/AuthService.php';
        
        $authService = new AuthService();
        
        echo "<p>✓ AuthService loaded</p>";
        
        $result = $authService->authenticate($login, $password);
        
        echo "<h3>Authentication Result:</h3>";
        echo "<pre>" . print_r($result, true) . "</pre>";
        
        if ($result['success']) {
            echo "<p style='color: green; font-size: 18px;'>✓ Authentication successful!</p>";
            echo "<p>Redirect path: " . ($result['redirect'] ?? 'Not set') . "</p>";
            
            $_SESSION['flash_success'] = $result['message'];
            
            // Test redirect
            $redirectUrl = $result['redirect'] ?? '?page=users';
            echo "<p>Redirecting to: <strong>" . htmlspecialchars($redirectUrl) . "</strong></p>";
            
            echo "<script>";
            echo "setTimeout(function() {";
            echo "  window.location.href = '" . addslashes($redirectUrl) . "';";
            echo "}, 3000);";
            echo "</script>";
            
            echo "<p><a href='" . htmlspecialchars($redirectUrl) . "' style='font-size: 16px; color: blue;'>Click here if not redirected</a></p>";
            
        } else {
            echo "<p style='color: red; font-size: 18px;'>✗ Authentication failed!</p>";
            echo "<p>Error: " . ($result['message'] ?? 'Unknown error') . "</p>";
            if (isset($result['errors'])) {
                echo "<p>Validation errors:</p>";
                echo "<pre>" . print_r($result['errors'], true) . "</pre>";
            }
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>Exception: " . $e->getMessage() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
    
} else {
    ?>
    <div style="background: #f8f9fa; padding: 20px; margin: 20px 0; border-left: 4px solid #007cba;">
        <h2 style="color: #007cba; margin-top: 0;">Test AuthService Login</h2>
        <p>This will test the complete AuthService authentication flow with proper URL routing.</p>
    </div>
    
    <form method="POST" style="border: 2px solid #007cba; padding: 20px; max-width: 400px; margin: 20px 0;">
        <h3 style="color: #007cba; margin-top: 0;">Login Form</h3>
        <p>
            <label><strong>Username/Email:</strong></label><br>
            <input type="text" name="login" value="realleesan" style="width: 100%; padding: 10px; margin: 5px 0; border: 1px solid #ddd;">
        </p>
        <p>
            <label><strong>Password:</strong></label><br>
            <input type="password" name="password" value="21042005nhatT@" style="width: 100%; padding: 10px; margin: 5px 0; border: 1px solid #ddd;">
        </p>
        <p>
            <button type="submit" style="background: #007cba; color: white; padding: 12px 24px; border: none; cursor: pointer; font-size: 16px; border-radius: 4px;">Test Login</button>
        </p>
    </form>
    
    <div style="background: #e8f4fd; padding: 15px; margin: 20px 0; border-radius: 4px;">
        <h3>Current Session:</h3>
        <pre><?php print_r($_SESSION); ?></pre>
    </div>
    
    <div style="background: #fff3cd; padding: 15px; margin: 20px 0; border-radius: 4px; border: 1px solid #ffeaa7;">
        <h3>Next Steps:</h3>
        <ol>
            <li>Click "Test Login" to test AuthService</li>
            <li>If successful, try the <a href="?page=login">real login page</a></li>
            <li>Check if redirect works to <a href="?page=users">user dashboard</a></li>
        </ol>
    </div>
    <?php
}
?>