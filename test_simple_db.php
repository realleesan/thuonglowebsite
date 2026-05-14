<?php
// Simple database test
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Simple Database Test</h1>";

// Load config the same way as index.php
try {
    require_once 'config.php';
    echo "<h2>Config loaded successfully</h2>";
    
    echo "<h3>Database Config:</h3>";
    echo "<pre>";
    echo "Host: " . $config['database']['host'] . "\n";
    echo "Database: " . $config['database']['name'] . "\n";
    echo "Username: " . $config['database']['username'] . "\n";
    echo "Password: " . (empty($config['database']['password']) ? "(empty)" : "(set)") . "\n";
    echo "</pre>";
    
    // Test connection
    echo "<h3>Testing Connection:</h3>";
    
    $dsn = "mysql:host=" . $config['database']['host'] . ";dbname=" . $config['database']['name'] . ";charset=utf8mb4";
    echo "<p>DSN: $dsn</p>";
    
    $pdo = new PDO($dsn, $config['database']['username'], $config['database']['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green;'>✓ Database connection successful!</p>";
    
    // Test query
    $stmt = $pdo->query("SELECT VERSION() as version");
    $result = $stmt->fetch();
    echo "<p>MySQL Version: " . $result['version'] . "</p>";
    
    // Check tables
    echo "<h3>Checking Required Tables:</h3>";
    
    $tables = ['hero_sections', 'hero_buttons'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        $exists = $stmt->rowCount() > 0;
        $status = $exists ? '<span style="color: green;">✓ EXISTS</span>' : '<span style="color: red;">✗ MISSING</span>';
        echo "<p>$table: $status</p>";
    }
    
    // Check users table for admin login
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "<h3>Admin Users:</h3>";
        $stmt = $pdo->query("SELECT id, username, email, role FROM users WHERE role = 'admin' LIMIT 5");
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($admins)) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th></tr>";
            foreach ($admins as $admin) {
                echo "<tr>";
                echo "<td>" . $admin['id'] . "</td>";
                echo "<td>" . htmlspecialchars($admin['username']) . "</td>";
                echo "<td>" . htmlspecialchars($admin['email']) . "</td>";
                echo "<td>" . htmlspecialchars($admin['role']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: orange;'>⚠ No admin users found</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace:</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
