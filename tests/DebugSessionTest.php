<?php
/**
 * Debug Session Test
 */

echo "PHP Session Debug Test\n";
echo "======================\n";

echo "Initial session status: " . session_status() . "\n";
echo "Session save path: " . session_save_path() . "\n";
echo "Session name: " . session_name() . "\n";

// Try basic session start
echo "\nTrying basic session_start()...\n";
$result = @session_start();
echo "Result: " . ($result ? 'true' : 'false') . "\n";
echo "Session status after start: " . session_status() . "\n";

if (session_status() === PHP_SESSION_ACTIVE) {
    echo "✓ Session started successfully\n";
    echo "Session ID: " . session_id() . "\n";
    
    // Test basic session operations
    $_SESSION['test'] = 'value';
    echo "Set session test = 'value'\n";
    echo "Retrieved: " . ($_SESSION['test'] ?? 'null') . "\n";
    
} else {
    echo "✗ Session failed to start\n";
    echo "Last error: " . error_get_last()['message'] ?? 'No error' . "\n";
}

// Check if we can create temp directory
$tempDir = __DIR__ . '/../tmp/sessions';
echo "\nChecking temp directory: $tempDir\n";
if (!is_dir($tempDir)) {
    echo "Creating directory...\n";
    $created = mkdir($tempDir, 0755, true);
    echo "Created: " . ($created ? 'true' : 'false') . "\n";
}
echo "Directory exists: " . (is_dir($tempDir) ? 'true' : 'false') . "\n";
echo "Directory writable: " . (is_writable($tempDir) ? 'true' : 'false') . "\n";