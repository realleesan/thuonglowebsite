<?php
/**
 * Debug File Upload - Simple test
 */

echo "<h1>Debug: Upload Test</h1>";

$uploadDir = __DIR__ . '/assets/uploads/categories/';
echo "Upload dir: $uploadDir<br>";
echo "Exists: " . (is_dir($uploadDir) ? "YES" : "NO") . "<br>";
echo "Writable: " . (is_writable($uploadDir) ? "YES" : "NO") . "<br>";

// List all files with details
echo "<h3>All files in uploads/categories:</h3>";
if (is_dir($uploadDir)) {
    $files = scandir($uploadDir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $fullPath = $uploadDir . $file;
            $size = filesize($fullPath);
            $modified = date('Y-m-d H:i:s', filemtime($fullPath));
            echo "$file - Size: $size bytes - Modified: $modified<br>";
        }
    }
} else {
    echo "Directory does not exist!<br>";
}

// Try to create a test file
echo "<h3>Test creating file:</h3>";
$testFile = $uploadDir . 'test_' . time() . '.txt';
$testResult = file_put_contents($testFile, 'test');
echo "Test file created: " . ($testResult !== false ? "YES at $testFile" : "NO") . "<br>";

if ($testResult !== false) {
    unlink($testFile);
    echo "Test file deleted: YES<br>";
}
?>

<!DOCTYPE html>
<html>
<head><title>Debug Upload</title></head>
<body>
    <h2>Simple Upload Test</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="image">
        <button type="submit">Upload</button>
    </form>
    <?php
    if (!empty($_FILES['image']['name'])) {
        echo "<h3>Upload Result:</h3>";
        echo "Name: " . $_FILES['image']['name'] . "<br>";
        echo "Temp: " . $_FILES['image']['tmp_name'] . "<br>";
        echo "Error: " . $_FILES['image']['error'] . "<br>";
        
        $target = $uploadDir . time() . '_' . $_FILES['image']['name'];
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            echo "SUCCESS: File saved to $target<br>";
        } else {
            echo "FAILED to move file<br>";
        }
    }
    ?>
    <p><a href="?">Refresh</a></p>
</body>
</html>
