<?php
define('THUONGLO_INIT', true);
$config = require __DIR__ . '/config.php';
require_once __DIR__ . '/core/database.php';

$db = Database::getInstance($config['database']);

echo "=== DIAGNOSTIC: 10K - 15K PRICE RANGE FILTER ===\n\n";

// 1. Old logic count
$sql_old = "SELECT COUNT(*) as count FROM products p 
            WHERE p.status = 'active' 
            AND (CASE WHEN p.sale_price > 0 THEN p.sale_price ELSE p.price END) >= 10000 
            AND (CASE WHEN p.sale_price > 0 THEN p.sale_price ELSE p.price END) <= 15000";
$old_count = $db->query($sql_old)[0]['count'];
echo "Old logic count (10k-15k): {$old_count}\n";

// 2. New logic count
$sql_new = "SELECT COUNT(*) as count FROM products p 
            WHERE p.status = 'active' 
            AND (CASE WHEN p.sale_price > 0 AND p.sale_price < p.price THEN p.sale_price ELSE p.price END) >= 10000 
            AND (CASE WHEN p.sale_price > 0 AND p.sale_price < p.price THEN p.sale_price ELSE p.price END) <= 15000";
$new_count = $db->query($sql_new)[0]['count'];
echo "New logic count (10k-15k): {$new_count}\n";

// 3. List products under new logic
$sql_list = "SELECT p.id, p.name, p.price, p.sale_price, 
             (CASE WHEN p.sale_price > 0 AND p.sale_price < p.price THEN p.sale_price ELSE p.price END) as effective_price 
             FROM products p 
             WHERE p.status = 'active' 
             AND (CASE WHEN p.sale_price > 0 AND p.sale_price < p.price THEN p.sale_price ELSE p.price END) >= 10000 
             AND (CASE WHEN p.sale_price > 0 AND p.sale_price < p.price THEN p.sale_price ELSE p.price END) <= 15000";
$products = $db->query($sql_list);
echo "\nProducts found in range [10000, 15000] with new logic:\n";
foreach ($products as $p) {
    echo "ID: {$p['id']} | Name: {$p['name']} | Price: {$p['price']} | Sale Price: " . ($p['sale_price'] ?? 'NULL') . " | Effective: {$p['effective_price']}\n";
}

