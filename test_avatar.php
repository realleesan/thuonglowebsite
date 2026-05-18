<?php
// Test file to debug avatar display issue
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test Avatar Display</h1>";

// Test 1: Check if functions.php exists and img_url works
echo "<h2>Test 1: Check functions.php</h2>";
if (file_exists(__DIR__ . '/core/functions.php')) {
    echo "✅ functions.php exists<br>";
    require_once __DIR__ . '/core/functions.php';
    
    if (function_exists('img_url')) {
        echo "✅ img_url function exists<br>";
        $url = img_url('home/home-banner-final.png');
        echo "img_url result: " . htmlspecialchars($url) . "<br>";
        echo '<img src="' . htmlspecialchars($url) . '" alt="Test from functions.php" style="width:100px;height:100px;"><br>';
    } else {
        echo "❌ img_url function NOT exists<br>";
    }
} else {
    echo "❌ functions.php NOT exists<br>";
}

echo "<hr>";

// Test 2: Check if image file exists
echo "<h2>Test 2: Check image file</h2>";
$imagePath = __DIR__ . '/assets/images/home/home-banner-final.png';
if (file_exists($imagePath)) {
    echo "✅ Image file exists: " . htmlspecialchars($imagePath) . "<br>";
    echo "File size: " . filesize($imagePath) . " bytes<br>";
} else {
    echo "❌ Image file NOT exists: " . htmlspecialchars($imagePath) . "<br>";
}

echo "<hr>";

// Test 3: Test direct path
echo "<h2>Test 3: Direct path</h2>";
$directPath = 'assets/images/home/home-banner-final.png';
echo "Direct path: " . htmlspecialchars($directPath) . "<br>";
echo '<img src="' . htmlspecialchars($directPath) . '" alt="Direct path test" style="width:100px;height:100px;"><br>';

echo "<hr>";

// Test 4: Test local_img_url function
echo "<h2>Test 4: Local img_url function</h2>";
function local_img_url($file) {
    return 'assets/images/' . ltrim($file, '/');
}

$localUrl = local_img_url('home/home-banner-final.png');
echo "local_img_url result: " . htmlspecialchars($localUrl) . "<br>";
echo '<img src="' . htmlspecialchars($localUrl) . '" alt="Local function test" style="width:100px;height:100px;"><br>';

echo "<hr>";

// Test 5: Simulate sidebar environment
echo "<h2>Test 5: Simulate sidebar</h2>";
$_SESSION['user_id'] = 1;
$_SESSION['user_name'] = 'Test User';

// Load sidebar
echo "<h3>Loading sidebar...</h3>";
if (file_exists(__DIR__ . '/app/views/_layout/user_sidebar.php')) {
    echo "✅ sidebar file exists<br>";
    include __DIR__ . '/app/views/_layout/user_sidebar.php';
} else {
    echo "❌ sidebar file NOT exists<br>";
}

echo "<hr>";

// Test 6: Check current working directory
echo "<h2>Test 6: Environment info</h2>";
echo "Current working directory: " . getcwd() . "<br>";
echo "Document root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Not set') . "<br>";
echo "Script name: " . ($_SERVER['SCRIPT_NAME'] ?? 'Not set') . "<br>";

?>
