<?php
/**
 * Debug the categories edit output
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Set error handler to catch all
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    echo "<p style='color:red'><b>PHP ERROR [$errno]:</b> $errstr<br>File: $errfile:$errline</p>";
    return true;
});

// Capture all output
ob_start();

try {
    // Set up required variables like index.php does
    $page_title = 'Chỉnh sửa Danh mục';
    $_GET['id'] = 20; // The ID from your screenshot
    $_GET['page'] = 'admin';
    $_GET['module'] = 'categories';
    $_GET['action'] = 'edit';
    
    // Include view_init.php
    require_once __DIR__ . '/core/view_init.php';
    
    // Set up service
    require_once __DIR__ . '/app/services/AdminService.php';
    $currentService = new AdminService(null, 'admin');
    
    echo "<h1>Debug Output</h1>";
    echo "<p>Before including edit.php</p>";
    
    // Try to include the edit.php
    include __DIR__ . '/app/views/admin/categories/edit.php';
    
    echo "<p style='color:green'><b>After including edit.php - SUCCESS!</b></p>";
    
} catch (Throwable $e) {
    echo "<p style='color:red'><b>EXCEPTION:</b> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

$output = ob_get_contents();
ob_end_clean();

echo "<hr><h2>Output Length: " . strlen($output) . " bytes</h2>";

// Check if form-actions is in output
if (strpos($output, 'form-actions') !== false) {
    echo "<p style='color:green'>✓ form-actions FOUND in output</p>";
} else {
    echo "<p style='color:red'>✗ form-actions NOT found in output</p>";
}

// Check for debug style
if (strpos($output, 'border: 3px solid red') !== false) {
    echo "<p style='color:green'>✓ Debug CSS (red border) FOUND</p>";
} else {
    echo "<p style='color:red'>✗ Debug CSS (red border) NOT found</p>";
}
