<?php
// Most basic debug
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Basic Debug</h1>";

// Test 1: PHP basics
echo "<h2>1. PHP Info</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Current directory: " . getcwd() . "<br>";
echo "Script name: " . $_SERVER['SCRIPT_NAME'] . "<br>";

// Test 2: File existence
echo "<h2>2. File Existence Check</h2>";
$files = [
    'config.php',
    'core/database.php',
    'app/services/AuthService.php',
    'app/controllers/AuthController.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✓ $file exists<br>";
    } else {
        echo "✗ $file NOT FOUND<br>";
    }
}

// Test 3: Database connection
echo "<h2>3. Database Connection</h2>";
try {
    require_once 'config.php';
    echo "Config loaded<br>";
    
    require_once 'core/database.php';
    echo "Database class loaded<br>";
    
    $db = new Database();
    echo "Database instance created<br>";
    
    $result = $db->query("SELECT 1 as test");
    if ($result) {
        echo "✓ Database connection successful<br>";
    } else {
        echo "✗ Database query failed<br>";
    }
    
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "<br>";
}

// Test 4: Check users table
echo "<h2>4. Users Table Check</h2>";
try {
    $structure = $db->query("DESCRIBE users");
    echo "Users table columns:<br>";
    foreach ($structure as $col) {
        echo "- " . $col['Field'] . " (" . $col['Type'] . ")<br>";
    }
    
    $count = $db->query("SELECT COUNT(*) as count FROM users");
    echo "Total users: " . $count[0]['count'] . "<br>";
    
} catch (Exception $e) {
    echo "✗ Users table error: " . $e->getMessage() . "<br>";
}

echo "<h2>5. Session Info</h2>";
session_start();
echo "Session ID: " . session_id() . "<br>";
echo "Session data: <pre>" . print_r($_SESSION, true) . "</pre>";
?>