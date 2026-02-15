<?php
// Test CSRF token functionality
session_start();

echo "<h1>CSRF Token Test</h1>";

try {
    require_once 'config.php';
    require_once 'app/services/AuthService.php';
    
    $authService = new AuthService();
    
    echo "<h2>1. Current Session</h2>";
    echo "<pre>" . print_r($_SESSION, true) . "</pre>";
    
    echo "<h2>2. Get CSRF Token</h2>";
    $token1 = $authService->getCsrfToken();
    echo "Token 1: " . htmlspecialchars($token1) . "<br>";
    
    $token2 = $authService->getCsrfToken();
    echo "Token 2: " . htmlspecialchars($token2) . "<br>";
    
    if ($token1 === $token2) {
        echo "✓ Tokens are consistent<br>";
    } else {
        echo "✗ Tokens are different!<br>";
    }
    
    echo "<h2>3. Verify Token</h2>";
    $isValid = $authService->verifyCsrfToken($token1);
    echo "Token validation: " . ($isValid ? '✓ Valid' : '✗ Invalid') . "<br>";
    
    echo "<h2>4. Test Invalid Token</h2>";
    $invalidToken = 'invalid_token_123';
    $isInvalid = $authService->verifyCsrfToken($invalidToken);
    echo "Invalid token validation: " . ($isInvalid ? '✗ Incorrectly valid' : '✓ Correctly invalid') . "<br>";
    
    echo "<h2>5. Test Login Form Token</h2>";
    echo "<form method='POST' action='?page=login&action=process'>";
    echo "<input type='hidden' name='csrf_token' value='" . htmlspecialchars($token1) . "'>";
    echo "<input type='text' name='login' placeholder='Username' required>";
    echo "<input type='password' name='password' placeholder='Password' required>";
    echo "<button type='submit'>Test Login</button>";
    echo "</form>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h3>Links:</h3>";
echo "<ul>";
echo "<li><a href='?page=login'>Login Page</a></li>";
echo "<li><a href='?page=logout'>Logout</a></li>";
echo "<li><a href='test_csrf_token.php'>Refresh This Page</a></li>";
echo "</ul>";
?>