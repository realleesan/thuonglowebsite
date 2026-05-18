<?php
// Test avatar display - final version
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Final Avatar Test</h1>";

// Test 1: Direct URL like sidebar
echo "<h2>Test 1: Direct URL (like sidebar)</h2>";
echo '<img src="https://test1.web3b.com/assets/images/home/home-banner-final.png" alt="Direct URL test" style="width:80px;height:80px;border:1px solid #ccc;"><br>';
echo "URL: https://test1.web3b.com/assets/images/home/home-banner-final.png<br>";

echo "<hr>";

// Test 2: Check if file exists
echo "<h2>Test 2: File exists check</h2>";
$imagePath = __DIR__ . '/assets/images/home/home-banner-final.png';
if (file_exists($imagePath)) {
    echo "✅ File exists: " . htmlspecialchars($imagePath) . "<br>";
    echo "File size: " . filesize($imagePath) . " bytes<br>";
    echo "File modified: " . date('Y-m-d H:i:s', filemtime($imagePath)) . "<br>";
} else {
    echo "❌ File NOT exists: " . htmlspecialchars($imagePath) . "<br>";
}

echo "<hr>";

// Test 3: Check default-avatar.jpg
echo "<h2>Test 3: Check default-avatar.jpg</h2>";
$defaultPath = __DIR__ . '/assets/images/default-avatar.jpg';
if (file_exists($defaultPath)) {
    echo "✅ default-avatar.jpg exists<br>";
    echo '<img src="/assets/images/default-avatar.jpg" alt="Default avatar" style="width:80px;height:80px;border:1px solid #ccc;"><br>';
} else {
    echo "❌ default-avatar.jpg NOT exists<br>";
}

echo "<hr>";

// Test 4: Simulate user data
echo "<h2>Test 4: Simulate user data</h2>";
$user = [
    'avatar' => null, // Simulate no avatar
    'name' => 'Test User'
];

echo "User avatar: " . ($user['avatar'] ?? 'null') . "<br>";
echo "User name: " . $user['name'] . "<br>";

// Test avatar logic
echo "<h3>Avatar logic test:</h3>";
if (!empty($user['avatar'])) {
    echo "Using user avatar: " . htmlspecialchars($user['avatar']) . "<br>";
    echo '<img src="' . htmlspecialchars($user['avatar']) . '" alt="User avatar" style="width:80px;height:80px;border:1px solid #ccc;"><br>';
} else {
    echo "Using placeholder<br>";
    echo '<img src="https://test1.web3b.com/assets/images/home/home-banner-final.png" alt="Placeholder" style="width:80px;height:80px;border:1px solid #ccc;"><br>';
}

echo "<hr>";

// Test 5: Check current session user
echo "<h2>Test 5: Current session user</h2>";
if (isset($_SESSION['user_id'])) {
    echo "✅ User logged in: " . $_SESSION['user_id'] . "<br>";
    echo "User name: " . ($_SESSION['user_name'] ?? 'Not set') . "<br>";
    echo "User avatar: " . ($_SESSION['avatar'] ?? 'Not set') . "<br>";
} else {
    echo "❌ No user logged in<br>";
}

echo "<hr>";

// Test 6: Environment info
echo "<h2>Test 6: Environment</h2>";
echo "Current URL: " . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "<br>";
echo "Document root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Script filename: " . __FILE__ . "<br>";

?>
