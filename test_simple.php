<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Step 1: Starting...<br>";

echo "Step 2: Loading config...<br>";
require_once __DIR__ . '/config.php';
echo "Step 3: Config loaded!<br>";

echo "Step 4: About to load Database.php...<br>";
try {
    require_once __DIR__ . '/core/Database.php';
    echo "Step 5: Database.php loaded!<br>";
} catch (Throwable $e) {
    echo "ERROR loading Database.php: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
}

echo "Step 6: Done!<br>";
?>
