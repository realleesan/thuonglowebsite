<?php
/**
 * Test full flow through index.php
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simulate the full request
$_GET['page'] = 'admin';
$_GET['module'] = 'categories';
$_GET['action'] = 'edit';
$_GET['id'] = 20;

// Start output buffering to capture everything
ob_start();

try {
    include __DIR__ . '/index.php';
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage();
}

$output = ob_get_clean();

// Show results
echo "<h1>Full Flow Debug</h1>";
echo "<p>Output length: " . strlen($output) . " bytes</p>";

// Check for form-actions
if (strpos($output, 'form-actions') !== false) {
    echo "<p style='color:green'>✓ form-actions FOUND</p>";
} else {
    echo "<p style='color:red'>✗ form-actions NOT found</p>";
}

// Check for debug red border
if (strpos($output, 'border: 3px solid red') !== false) {
    echo "<p style='color:green'>✓ Debug CSS FOUND</p>";
} else {
    echo "<p style='color:red'>✗ Debug CSS NOT found</p>";
}

// Show last 500 chars of output
echo "<h2>Last 500 chars of output:</h2>";
echo "<pre>" . htmlspecialchars(substr($output, -500)) . "</pre>";
