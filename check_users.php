<?php
// Check users in database
try {
    require_once 'config.php';
    require_once 'core/database.php';
    
    $db = new Database();
    
    // Check users table structure
    echo "<h2>Users Table Structure:</h2>";
    $structure = $db->query("DESCRIBE users");
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($structure as $row) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check existing users
    echo "<h2>Existing Users:</h2>";
    $users = $db->query("SELECT id, name, username, email, phone, role, status, created_at FROM users");
    
    if ($users) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Name</th><th>Username</th><th>Email</th><th>Phone</th><th>Role</th><th>Status</th><th>Created</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . htmlspecialchars($user['name']) . "</td>";
            echo "<td>" . htmlspecialchars($user['username'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . htmlspecialchars($user['phone'] ?? 'N/A') . "</td>";
            echo "<td>" . $user['role'] . "</td>";
            echo "<td>" . $user['status'] . "</td>";
            echo "<td>" . $user['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No users found in database.</p>";
        
        // Create a test user
        echo "<h3>Creating test user...</h3>";
        $hashedPassword = password_hash('123456', PASSWORD_DEFAULT);
        
        $result = $db->query(
            "INSERT INTO users (name, username, email, password, role, status) VALUES (?, ?, ?, ?, ?, ?)",
            ['Test User', 'testuser', 'test@example.com', $hashedPassword, 'user', 'active']
        );
        
        if ($result) {
            echo "<p style='color: green;'>Test user created successfully!</p>";
            echo "<p>Email: test@example.com</p>";
            echo "<p>Username: testuser</p>";
            echo "<p>Password: 123456</p>";
        } else {
            echo "<p style='color: red;'>Failed to create test user.</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>