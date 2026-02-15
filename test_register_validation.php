<?php
// Test Register Validation
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test Register Validation</h1>";

// Start session
session_start();

// Load core files
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/functions.php';
require_once __DIR__ . '/core/view_init.php';
require_once __DIR__ . '/app/services/AuthService.php';

echo "<h2>Testing duplicate registration...</h2>";

// Create AuthService
$authService = new AuthService();

// Test data with existing email
$testData = [
    'name' => 'Test User 2',
    'username' => 'testuser2',
    'email' => 'realleesan@example.com', // This should exist from seeder
    'phone' => '0987654321',
    'password' => 'TestPassword123!',
    'password_confirmation' => 'TestPassword123!'
];

echo "<h3>Test Data:</h3>";
echo "<pre>" . print_r($testData, true) . "</pre>";

try {
    $result = $authService->register($testData);
    
    echo "<h3>Registration Result:</h3>";
    echo "<pre>" . print_r($result, true) . "</pre>";
    
    if (!$result['success']) {
        echo "<h3>Errors:</h3>";
        if (isset($result['errors'])) {
            foreach ($result['errors'] as $field => $error) {
                echo "<p><strong>{$field}:</strong> {$error}</p>";
            }
        } else {
            echo "<p>{$result['message']}</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Exception: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Test with existing username
echo "<hr><h2>Testing with existing username...</h2>";

$testData2 = [
    'name' => 'Test User 3',
    'username' => 'realleesan', // This should exist
    'email' => 'newuser@example.com',
    'phone' => '0123456789',
    'password' => 'TestPassword123!',
    'password_confirmation' => 'TestPassword123!'
];

try {
    $result2 = $authService->register($testData2);
    
    echo "<h3>Registration Result 2:</h3>";
    echo "<pre>" . print_r($result2, true) . "</pre>";
    
    if (!$result2['success']) {
        echo "<h3>Errors:</h3>";
        if (isset($result2['errors'])) {
            foreach ($result2['errors'] as $field => $error) {
                echo "<p><strong>{$field}:</strong> {$error}</p>";
            }
        } else {
            echo "<p>{$result2['message']}</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Exception: " . $e->getMessage() . "</p>";
}
?>