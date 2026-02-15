<?php
// Simple login test
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>Processing Login...</h2>";
    
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';
    
    echo "Login: " . htmlspecialchars($login) . "<br>";
    echo "Password: " . str_repeat('*', strlen($password)) . "<br>";
    
    // Test database connection
    try {
        require_once 'config.php';
        require_once 'core/database.php';
        
        $db = new Database();
        
        // Check if user exists
        $user = $db->query("SELECT * FROM users WHERE email = ? OR phone = ? OR username = ?", [$login, $login, $login]);
        
        if ($user) {
            $user = $user[0];
            echo "<p>User found: " . htmlspecialchars($user['name']) . "</p>";
            
            // Check password
            if (password_verify($password, $user['password'])) {
                echo "<p style='color: green;'>Password correct!</p>";
                
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['username'] = $user['username'] ?? '';
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['is_authenticated'] = true;
                $_SESSION['login_time'] = time();
                $_SESSION['last_activity'] = time();
                
                echo "<p style='color: green;'>Session created successfully!</p>";
                echo "<p><a href='?page=users'>Go to Dashboard</a></p>";
                
            } else {
                echo "<p style='color: red;'>Password incorrect!</p>";
            }
        } else {
            echo "<p style='color: red;'>User not found!</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>Database error: " . $e->getMessage() . "</p>";
    }
    
} else {
    // Show form
    ?>
    <h2>Simple Login Test</h2>
    <form method="POST">
        <p>
            <label>Email/Username/Phone:</label><br>
            <input type="text" name="login" required>
        </p>
        <p>
            <label>Password:</label><br>
            <input type="password" name="password" required>
        </p>
        <p>
            <button type="submit">Login</button>
        </p>
    </form>
    <?php
}

// Show current session
echo "<h3>Current Session:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
?>