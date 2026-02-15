<?php
// Bypass AuthService completely
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

echo "<h1>Bypass Auth Test</h1>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';
    
    echo "<h2>Processing: " . htmlspecialchars($login) . "</h2>";
    
    try {
        require_once 'config.php';
        require_once 'core/database.php';
        
        $db = new Database();
        
        // Find user
        $user = $db->query(
            "SELECT * FROM users WHERE (email = ? OR phone = ? OR username = ?) AND status = 'active'",
            [$login, $login, $login]
        );
        
        if ($user && password_verify($password, $user[0]['password'])) {
            $user = $user[0];
            
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['username'] = $user['username'] ?? '';
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['is_authenticated'] = true;
            $_SESSION['login_time'] = time();
            $_SESSION['last_activity'] = time();
            $_SESSION['flash_success'] = 'Đăng nhập thành công!';
            
            echo "<p style='color: green; font-size: 18px;'>✓ Login successful!</p>";
            echo "<p>Redirecting to dashboard...</p>";
            
            // JavaScript redirect
            echo "<script>";
            echo "setTimeout(function() {";
            echo "  window.location.href = '?page=users';";
            echo "}, 2000);";
            echo "</script>";
            
            echo "<p><a href='?page=users' style='font-size: 16px;'>Click here if not redirected</a></p>";
            
        } else {
            echo "<p style='color: red;'>Login failed!</p>";
            $_SESSION['flash_error'] = 'Tên đăng nhập hoặc mật khẩu không đúng';
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    }
    
} else {
    ?>
    <form method="POST" style="border: 2px solid #007cba; padding: 20px; max-width: 400px; margin: 20px 0;">
        <h2 style="color: #007cba;">Direct Login (Bypass AuthService)</h2>
        <p>
            <label><strong>Username/Email:</strong></label><br>
            <input type="text" name="login" value="realleesan" style="width: 100%; padding: 10px; margin: 5px 0;">
        </p>
        <p>
            <label><strong>Password:</strong></label><br>
            <input type="password" name="password" value="21042005nhatT@" style="width: 100%; padding: 10px; margin: 5px 0;">
        </p>
        <p>
            <button type="submit" style="background: #007cba; color: white; padding: 12px 24px; border: none; cursor: pointer; font-size: 16px;">Login Now</button>
        </p>
    </form>
    
    <div style="background: #f0f0f0; padding: 15px; margin: 20px 0;">
        <h3>Current Session:</h3>
        <pre><?php print_r($_SESSION); ?></pre>
    </div>
    
    <div style="background: #e8f4fd; padding: 15px; margin: 20px 0;">
        <h3>Test Steps:</h3>
        <ol>
            <li><a href="basic_debug.php">Run Basic Debug</a> - Check if files exist and database works</li>
            <li><a href="add_username_column.php">Add Username Column</a> - Ensure username column exists and create test user</li>
            <li><strong>Use this form</strong> - Test direct login bypass</li>
            <li><a href="index.php?page=login">Try Real Login</a> - Test actual login page</li>
        </ol>
    </div>
    <?php
}
?>