<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/database.php';

$db = Database::getInstance();
$pdo = $db->getPdo();

// Check affiliates table structure
$stmt = $pdo->query("SHOW COLUMNS FROM affiliates WHERE Field = 'status'");
$column = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<h1>Affiliates Table - Status Column</h1>";
echo "<pre>";
print_r($column);
echo "</pre>";

// Check what values exist
$stmt2 = $pdo->query("SELECT DISTINCT status FROM affiliates");
$values = $stmt2->fetchAll(PDO::FETCH_COLUMN);

echo "<h2>Existing status values:</h2>";
echo "<pre>";
print_r($values);
echo "</pre>";
?>

