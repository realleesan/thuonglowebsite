<?php
// Add username column directly
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Add Username Column</h1>";

try {
    require_once 'config.php';
    require_once 'core/database.php';
    
    $db = new Database();
    
    // Check current table structure
    echo "<h2>Current table structure:</h2>";
    $structure = $db->query("DESCRIBE users");
    $hasUsername = false;
    
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    foreach ($structure as $col) {
        echo "<tr>";
        echo "<td>" . $col['Field'] . "</td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . $col['Key'] . "</td>";
        echo "</tr>";
        
        if ($col['Field'] === 'username') {
            $hasUsername = true;
        }
    }
    echo "</table>";
    
    if (!$hasUsername) {
        echo "<h2>Adding username column...</h2>";
        
        // Add username column
        $result1 = $db->query("ALTER TABLE users ADD COLUMN username VARCHAR(50) NULL AFTER name");
        if ($result1 !== false) {
            echo "✓ Username column added<br>";
        } else {
            echo "✗ Failed to add username column<br>";
        }
        
        // Add unique index
        $result2 = $db->query("ALTER TABLE users ADD UNIQUE KEY idx_username (username)");
        if ($result2 !== false) {
            echo "✓ Username index added<br>";
        } else {
            echo "✗ Failed to add username index<br>";
        }
        
    } else {
        echo "<p>✓ Username column already exists</p>";
    }
    
    // Create test user
    echo "<h2>Creating test user...</h2>";
    
    // Check if test user exists
    $existing = $db->query("SELECT * FROM users WHERE username = ? OR email = ?", ['realleesan', 'realleesan@example.com']);
    
    if ($existing) {
        echo "<p>Test user already exists:</p>";
        echo "<pre>" . print_r($existing[0], true) . "</pre>";
    } else {
        $hashedPassword = password_hash('21042005nhatT@', PASSWORD_DEFAULT);
        
        $insertResult = $db->query(
            "INSERT INTO users (name, username, email, password, role, status, points, level, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())",
            [
                'Real Lee San',
                'realleesan',
                'realleesan@example.com', 
                $hashedPassword,
                'user',
                'active',
                0,
                'Bronze'
            ]
        );
        
        if ($insertResult !== false) {
            echo "<p style='color: green;'>✓ Test user created successfully!</p>";
            echo "<p><strong>Credentials:</strong></p>";
            echo "<ul>";
            echo "<li>Username: realleesan</li>";
            echo "<li>Email: realleesan@example.com</li>";
            echo "<li>Password: 21042005nhatT@</li>";
            echo "</ul>";
        } else {
            echo "<p style='color: red;'>✗ Failed to create test user</p>";
        }
    }
    
    // Show all users
    echo "<h2>All users:</h2>";
    $users = $db->query("SELECT id, name, username, email, role, status FROM users");
    if ($users) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Name</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . htmlspecialchars($user['name']) . "</td>";
            echo "<td>" . htmlspecialchars($user['username'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . $user['role'] . "</td>";
            echo "<td>" . $user['status'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>