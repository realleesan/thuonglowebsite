<?php
// Quick database connection test
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Connection Test</h1>";

try {
    require_once 'config.php';
    $config = require_once 'config.php';
    
    echo "<h2>Current Config:</h2>";
    echo "<pre>";
    echo "Host: " . $config['database']['host'] . "\n";
    echo "Database: " . $config['database']['dbname'] . "\n";
    echo "Username: " . $config['database']['username'] . "\n";
    echo "Password: " . (empty($config['database']['password']) ? "(empty)" : "(set)") . "\n";
    echo "</pre>";
    
    // Try different connection methods
    echo "<h2>Testing Connection Methods:</h2>";
    
    // Method 1: Standard MySQL connection
    try {
        $dsn = "mysql:host=" . $config['database']['host'] . ";dbname=" . $config['database']['dbname'] . ";charset=utf8mb4";
        $pdo = new PDO($dsn, $config['database']['username'], $config['database']['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "<p>✓ Standard MySQL connection: SUCCESS</p>";
    } catch (Exception $e) {
        echo "<p>✗ Standard MySQL connection: " . $e->getMessage() . "</p>";
    }
    
    // Method 2: Try localhost if host is 127.0.0.1
    if ($config['database']['host'] === '127.0.0.1') {
        try {
            $dsn = "mysql:host=localhost;dbname=" . $config['database']['dbname'] . ";charset=utf8mb4";
            $pdo = new PDO($dsn, $config['database']['username'], $config['database']['password']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "<p>✓ Localhost connection: SUCCESS</p>";
        } catch (Exception $e) {
            echo "<p>✗ Localhost connection: " . $e->getMessage() . "</p>";
        }
    }
    
    // Method 3: Try with socket
    try {
        $dsn = "mysql:host=" . $config['database']['host'] . ";dbname=" . $config['database']['dbname'] . ";charset=utf8mb4;unix_socket=/tmp/mysql.sock";
        $pdo = new PDO($dsn, $config['database']['username'], $config['database']['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "<p>✓ Socket connection: SUCCESS</p>";
    } catch (Exception $e) {
        echo "<p>✗ Socket connection: " . $e->getMessage() . "</p>";
    }
    
    // Check if MySQL service is running (Linux/Unix)
    if (function_exists('exec')) {
        echo "<h2>MySQL Service Status:</h2>";
        $output = [];
        exec('ps aux | grep mysql', $output);
        if (!empty($output)) {
            echo "<p>✓ MySQL processes found:</p>";
            echo "<pre>" . implode("\n", array_slice($output, 0, 5)) . "</pre>";
        } else {
            echo "<p>✗ No MySQL processes found</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p>Config file error: " . $e->getMessage() . "</p>";
}
?>
