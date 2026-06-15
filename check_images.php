<?php
define('THUONGLO_INIT', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/db.php';
require_once __DIR__ . '/core/functions.php';

$db = DB::getInstance();

echo "=== CATEGORIES ===\n";
$categories = $db->query("SELECT id, name, image FROM categories");
foreach ($categories as $cat) {
    echo "ID: {$cat['id']} | Name: {$cat['name']} | Image: '" . ($cat['image'] ?? 'NULL') . "'\n";
}

echo "\n=== BRANDS ===\n";
$brands = $db->query("SELECT id, name, image FROM brands");
foreach ($brands as $brand) {
    echo "ID: {$brand['id']} | Name: {$brand['name']} | Image: '" . ($brand['image'] ?? 'NULL') . "'\n";
}

echo "\n=== NEWS ===\n";
$news = $db->query("SELECT id, title, image FROM news LIMIT 20");
foreach ($news as $n) {
    echo "ID: {$n['id']} | Title: {$n['title']} | Image: '" . ($n['image'] ?? 'NULL') . "'\n";
}
