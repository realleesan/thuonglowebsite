<?php
/**
 * Simple Session Test
 * Basic test for SessionManager functionality
 */

require_once __DIR__ . '/../app/services/SessionManager.php';

echo "Testing SessionManager basic functionality...\n";

try {
    // Test 1: Create SessionManager
    $sessionManager = new SessionManager();
    echo "✓ SessionManager created successfully\n";
    
    // Test 2: Check session status
    echo "Session status: " . session_status() . "\n";
    
    // Test 3: Try to start session
    $result = $sessionManager->start();
    echo "Session start result: " . ($result ? 'true' : 'false') . "\n";
    echo "Session status after start: " . session_status() . "\n";
    
    if (session_status() === PHP_SESSION_ACTIVE) {
        echo "✓ Session is active\n";
        
        // Test CSRF token
        $token = $sessionManager->getCsrfToken();
        echo "CSRF token length: " . strlen($token) . "\n";
        
        if (!empty($token)) {
            echo "✓ CSRF token generated successfully\n";
        } else {
            echo "✗ CSRF token generation failed\n";
        }
        
        // Test session data
        $sessionManager->set('test', 'value');
        $retrieved = $sessionManager->get('test');
        
        if ($retrieved === 'value') {
            echo "✓ Session data storage works\n";
        } else {
            echo "✗ Session data storage failed\n";
        }
        
    } else {
        echo "✗ Session is not active\n";
    }
    
    echo "\nSessionManager basic functionality test completed.\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}