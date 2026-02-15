<?php
// Minimal authentication test
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

echo "<h1>Minimal Auth Test</h1>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';
    
    echo "<h2>Testing login: " . htmlspecialchars($login) . "</h2>";
    
    try {
        require_once 'config.php';
        require_once 'core/database.php';
        
        $db = new Database();
        
        // Direct database query
        $user = $db->query(
            "SELECT * FROM users WHERE (email = ? OR phone = ? OR username = ?) AND status = 'active'",
            [$login, $login, $login]
        );
        
        if ($user) {
            $user = $user[0];
            echo "<p>✓ User found: " . htmlspecialchars($user['name']) . "</p>";
            
            if (password_verify($password, $user['password'])) {
                echo "<p style='color: green;'>✓ Password correct!</p>";
                
                // Set session manually
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['username'] = $user['username'] ?? '';
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['is_authenticated'] = true;
                $_SESSION['login_time'] = time();
                $_SESSION['last_activity'] = time();
                
                echo "<p style='color: green;'>✓ Session created!</p>";
                echo "<p><a href='?page=users' style='font-size: 18px; color: blue;'>Go to Dashboard</a></p>";
                
                // Show session data
                echo "<h3>Session Data:</h3>";
                echo "<pre>" . print_r($_SESSION, true) . "</pre>";
                
            } else {
                echo "<p style='color: red;'>✗ Password incorrect!</p>";
            }
        } else {
            echo "<p style='color: red;'>✗ User not found!</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
    
} else {
    ?>
    <form method="POST" style="border: 1px solid #ccc; padding: 20px; max-width: 400px;">
        <h2>Login Test</h2>
        <p>
            <label><strong>Username/Email:</strong></label><br>
            <input type="text" name="login" value="realleesan" style="width: 100%; padding: 8px;">
        </p>
        <p>
            <label><strong>Password:</strong></label><br>
            <input type="password" name="password" value="21042005nhatT@" style="width: 100%; padding: 8px;">
        </p>
        <p>
            <button type="submit" style="background: #007cba; color: white; padding: 10px 20px; border: none;">Login</button>
        </p>
    </form>
    
    <h3>Current Session:</h3>
    <pre><?php print_r($_SESSION); ?></pre>
    
    <h3>Links:</h3>
    <ul>
        <li><a href="basic_debug.php">Basic Debug</a></li>
        <li><a href="add_username_column.php">Add Username Column</a></li>
    </ul>
    <?php
}
?>