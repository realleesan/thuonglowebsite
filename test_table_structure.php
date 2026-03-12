<?php
/**
 * Test to view products table structure
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/database.php';

echo "<h1>Products Table Structure</h1>";
echo "<pre style='background:#f5f5f5;padding:15px;overflow:auto;max-height:600px'>";

$db = Database::getInstance();
$pdo = $db->getPdo();

// Get table structure
$stmt = $pdo->query("DESCRIBE products");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Total columns: " . count($columns) . "\n\n";
echo "=== COLUMNS ===\n";
foreach ($columns as $col) {
    echo sprintf("%-30s %-20s %-10s %s\n", 
        $col['Field'], 
        $col['Type'], 
        $col['Null'], 
        $col['Default'] ?? 'NULL'
    );
}

echo "\n\n=== SAMPLE DATA (Product ID 1) ===\n";
$stmt = $pdo->query("SELECT * FROM products WHERE id = 1");
$product = $stmt->fetch(PDO::FETCH_ASSOC);
if ($product) {
    foreach ($product as $key => $value) {
        echo "$key: ";
        if (strlen($value) > 100) {
            echo substr($value, 0, 100) . "...\n";
        } else {
            echo $value . "\n";
        }
    }
}

echo "\n\n=== ALL PRODUCTS ===\n";
$stmt = $pdo->query("SELECT id, name, type, price, stock, status FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($products as $p) {
    echo "ID: {$p['id']} | {$p['name']} | Type: {$p['type']} | Price: {$p['price']} | Stock: {$p['stock']} | Status: {$p['status']}\n";
}
echo "</pre>";
