<?php
// Test Register with Duplicate Data
session_start();

echo "<h1>Test Register with Duplicate Data</h1>";

// Simulate POST data with existing email
$_POST = [
    'name' => 'Test User New',
    'username' => 'newuser',
    'email' => 'realleesan@example.com', // This should exist
    'phone' => '0987654321',
    'password' => 'TestPassword123!',
    'confirm_password' => 'TestPassword123!',
    'csrf_token' => 'test_token',
    'terms' => '1'
];

$_SERVER['REQUEST_METHOD'] = 'POST';

// Include and test
require_once 'app/controllers/AuthController.php';

try {
    $authController = new AuthController();
    $authController->processRegister();
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

// Check flash messages
echo "<h2>Flash Messages:</h2>";
if (isset($_SESSION['flash_errors'])) {
    echo "<h3>Field Errors:</h3>";
    foreach ($_SESSION['flash_errors'] as $field => $error) {
        echo "<p><strong>$field:</strong> $error</p>";
    }
}

if (isset($_SESSION['flash_error'])) {
    echo "<p><strong>General Error:</strong> " . $_SESSION['flash_error'] . "</p>";
}

if (isset($_SESSION['flash_success'])) {
    echo "<p><strong>Success:</strong> " . $_SESSION['flash_success'] . "</p>";
}
?>