<?php
/**
 * Test script to verify authentication system
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

echo "<h2>Authentication System Test</h2>";

// Test 1: Check if user is logged in
echo "<h3>Test 1: Current Session Status</h3>";
echo "User ID: " . ($_SESSION['user_id'] ?? 'Not set') . "<br>";
echo "User Email: " . ($_SESSION['user_email'] ?? 'Not set') . "<br>";
echo "User Role: " . ($_SESSION['user_role'] ?? 'Not set') . "<br>";

// Test 2: Test AuthMiddleware
echo "<h3>Test 2: AuthMiddleware Tests</h3>";
$authMiddleware = new AuthMiddleware();

echo "Is authenticated: " . ($authMiddleware->requireAuth() ? 'Yes' : 'No') . "<br>";
echo "Is admin: " . ($authMiddleware->requireAdmin() ? 'Yes' : 'No') . "<br>";
echo "Is affiliate: " . ($authMiddleware->requireAffiliate() ? 'Yes' : 'No') . "<br>";

// Test 3: Current user info
echo "<h3>Test 3: Current User Info</h3>";
$currentUser = $authMiddleware->getCurrentUser();
if ($currentUser) {
    echo "User data: <pre>" . print_r($currentUser, true) . "</pre>";
} else {
    echo "No user data available<br>";
}

// Test 4: Test direct access simulation
echo "<h3>Test 4: Access Control Simulation</h3>";
echo "Admin access would be: " . ($authMiddleware->requireAdmin() ? 'ALLOWED' : 'DENIED') . "<br>";
echo "Affiliate access would be: " . ($authMiddleware->requireAffiliate() ? 'ALLOWED' : 'DENIED') . "<br>";

echo "<br><a href='./'>Back to Home</a>";
?>