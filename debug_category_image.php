<?php
/**
 * Debug Category Image - Direct database access
 */

// Define constant to allow config access
define('THUONGLO_INIT', true);

// Load core env
require_once __DIR__ . '/core/env.php';

echo "<h1>Debug: Category Image</h1><hr>";

// Get database connection from Env
$db = null;
try {
    $host = getenv('DB_HOST') ?: 'localhost';
    $dbname = getenv('DB_NAME') ?: 'test1_thuonglowebsite';
    $user = getenv('DB_USER') ?: 'test1_thuonglowebsite';
    $pass = getenv('DB_PASS') ?: '21042005nhat';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db = $pdo;
    echo "Database connected!<br>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

if ($db) {
    // Get categories
    $stmt = $db->query("SELECT id, name, image FROM categories ORDER BY id DESC LIMIT 10");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Name</th><th>Image in DB</th><th>File exists?</th><th>Image Preview</th></tr>";
    
    foreach ($categories as $cat) {
        $img = $cat['image'];
        $fileExists = file_exists(__DIR__ . $img);
        
        echo "<tr>";
        echo "<td>" . $cat['id'] . "</td>";
        echo "<td>" . htmlspecialchars($cat['name']) . "</td>";
        echo "<td>" . htmlspecialchars($img) . "</td>";
        echo "<td>" . ($fileExists ? "YES" : "NO") . "</td>";
        
        if ($img && $fileExists) {
            echo "<td><img src='$img' style='height:50px'></td>";
        } else {
            echo "<td>No image</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}

// Show uploaded files
echo "<hr><h3>Files in uploads folder:</h3>";
$dir = __DIR__ . '/assets/uploads/categories/';
if (is_dir($dir)) {
    foreach (scandir($dir) as $f) {
        if ($f != '.' && $f != '..') echo "$f<br>";
    }
}
?>
<p><a href="?">Refresh</a></p>
