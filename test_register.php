<?php
// Test registration flow
session_start();

echo "<h2>Test Registration Flow</h2>";

// Test flash message display
if (isset($_SESSION['flash_success'])) {
    echo "<div style='color: green; padding: 10px; border: 1px solid green; margin: 10px 0;'>";
    echo "SUCCESS: " . $_SESSION['flash_success'];
    echo "</div>";
    unset($_SESSION['flash_success']);
}

if (isset($_SESSION['flash_error'])) {
    echo "<div style='color: red; padding: 10px; border: 1px solid red; margin: 10px 0;'>";
    echo "ERROR: " . $_SESSION['flash_error'];
    echo "</div>";
    unset($_SESSION['flash_error']);
}

// Test session data
echo "<h3>Current Session Data:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Test form
echo "<h3>Test Registration Form:</h3>";
echo '<form method="POST" action="?page=register&action=process">';
echo '<input type="hidden" name="csrf_token" value="test_token">';
echo '<p>Name: <input type="text" name="name" value="Test User" required></p>';
echo '<p>Username: <input type="text" name="username" value="testuser' . time() . '" required></p>';
echo '<p>Email: <input type="email" name="email" value="test' . time() . '@example.com" required></p>';
echo '<p>Phone: <input type="text" name="phone" value="0123456789" required></p>';
echo '<p>Password: <input type="password" name="password" value="password123" required></p>';
echo '<p>Confirm Password: <input type="password" name="confirm_password" value="password123" required></p>';
echo '<p>Address: <input type="text" name="address" value="Test Address"></p>';
echo '<p>Ref Code: <input type="text" name="ref_code" value=""></p>';
echo '<p><button type="submit">Test Register</button></p>';
echo '</form>';

echo "<p><a href='/'>Go to Home</a></p>";
echo "<p><a href='?page=register'>Go to Register Page</a></p>";
?>