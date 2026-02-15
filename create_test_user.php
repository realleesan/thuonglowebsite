<?php
// Create test user with username
try {
    require_once 'config.php';
    require_once 'core/database.php';
    
    $db = new Database();
    
    // Check if username column exists
    $columns = $db->query("DESCRIBE users");
    $hasUsername = false;
    foreach ($columns as $column) {
        if ($column['Field'] === 'username') {
            $hasUsername = true;
            break;
        }
    }
    
    if (!$hasUsername) {
        echo "<h2>Adding username column...</h2>";
        $db->query("ALTER TABLE users ADD COLUMN username VARCHAR(50) UNIQUE NULL AFTER name");
        $db->query("ALTER TABLE users ADD INDEX idx_username (username)");
        echo "Username column added successfully!<br>";
    }
    
    // Check if test user exists
    $existingUser = $db->query("SELECT * FROM users WHERE username = ? OR email = ?", ['realleesan', 'realleesan@example.com']);
    
    if ($existingUser) {
        echo "<h2>Test user already exists:</h2>";
        echo "<pre>" . print_r($existingUser[0], true) . "</pre>";
    } else {
        echo "<h2>Creating test user...</h2>";
        
        $hashedPassword = password_hash('21042005nhatT@', PASSWORD_DEFAULT);
        
        $result = $db->query(
            "INSERT INTO users (name, username, email, password, role, status, points, level) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
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
        
        if ($result) {
            echo "<p style='color: green;'>Test user created successfully!</p>";
            echo "<p><strong>Login credentials:</strong></p>";
            echo "<ul>";
            echo "<li>Username: realleesan</li>";
            echo "<li>Email: realleesan@example.com</li>";
            echo "<li>Password: 21042005nhatT@</li>";
            echo "</ul>";
        } else {
            echo "<p style='color: red;'>Failed to create test user.</p>";
        }
    }
    
    // Show all users
    echo "<h2>All users in database:</h2>";
    $users = $db->query("SELECT id, name, username, email, role, status FROM users");
    if ($users) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . htmlspecialchars($user['name']) . "</td>";
            echo "<td>" . htmlspecialchars($user['username'] ?? 'N/A') . "</td>";
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

<h3>Test Links:</h3>
<ul>
    <li><a href="test_auth_flow.php">Test Authentication Flow</a></li>
    <li><a href="simple_login_controller.php">Simple Login Controller</a></li>
    <li><a href="?page=login">Go to Login Page</a></li>
</ul>