<?php
/**
 * Simple test to check if the issue is with redirect
 * This just simulates what happens when form is submitted
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Simple Save Test</h1>";

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simulate form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<p>POST request received!</p>";
    
    // Simulate save
    $updated = true;
    
    if ($updated) {
        $_SESSION['test_saved'] = time();
        echo "<p style='color:green'>Saved! Session set.</p>";
    }
}

echo "<p>Session test_saved: " . ($_SESSION['test_saved'] ?? 'not set') . "</p>";

echo "<hr>";
echo "<h2>Test Form</h2>";
echo "<form method='POST'>";
echo "<button type='submit'>Test Save</button>";
echo "</form>";

echo "<hr>";
echo "<p><a href='?page=admin&module=products&action=edit&id=34'>Go to Edit Page</a></p>";
?>
