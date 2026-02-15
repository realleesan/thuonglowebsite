<?php
// Cleanup debug files
echo "<h1>Cleanup Debug Files</h1>";

$debugFiles = [
    'basic_debug.php',
    'add_username_column.php', 
    'bypass_auth_test.php',
    'minimal_auth_test.php',
    'create_test_user.php',
    'debug_500_error.php',
    'test_auth_flow.php',
    'debug_login.php',
    'test_login_simple.php',
    'simple_login_controller.php',
    'final_login_test.php',
    'check_users.php',
    'cleanup_debug_files.php'
];

echo "<h2>Debug files to remove:</h2>";
echo "<ul>";
foreach ($debugFiles as $file) {
    if (file_exists($file)) {
        echo "<li>✓ $file (exists)</li>";
    } else {
        echo "<li>✗ $file (not found)</li>";
    }
}
echo "</ul>";

if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    echo "<h2>Removing files...</h2>";
    $removed = 0;
    foreach ($debugFiles as $file) {
        if (file_exists($file)) {
            if (unlink($file)) {
                echo "<p style='color: green;'>✓ Removed $file</p>";
                $removed++;
            } else {
                echo "<p style='color: red;'>✗ Failed to remove $file</p>";
            }
        }
    }
    echo "<p><strong>Removed $removed files.</strong></p>";
    echo "<p><a href='/'>Go to homepage</a></p>";
} else {
    echo "<p><a href='?confirm=yes' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>Confirm Removal</a></p>";
    echo "<p><a href='final_login_test.php'>Back to Final Test</a></p>";
}
?>