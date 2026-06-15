<?php
if (!defined('THUONGLO_INIT')) {
    define('THUONGLO_INIT', true);
}
require_once __DIR__ . '/core/database.php';

$db = Database::getInstance();

echo "=== CATEGORIES IMAGE DUMP ===\n";
try {
    $categories = $db->query("SELECT id, name, image FROM categories");
    foreach ($categories as $cat) {
        echo "Category ID: {$cat['id']} | Name: {$cat['name']} | Image: '" . ($cat['image'] ?? 'NULL') . "'\n";
    }
} catch (Exception $e) {
    echo "Error querying categories: " . $e->getMessage() . "\n";
}

echo "\n=== NEWS IMAGE DUMP ===\n";
try {
    $news = $db->query("SELECT id, title, image FROM news LIMIT 30");
    foreach ($news as $n) {
        echo "News ID: {$n['id']} | Title: {$n['title']} | Image: '" . ($n['image'] ?? 'NULL') . "'\n";
    }
} catch (Exception $e) {
    echo "Error querying news: " . $e->getMessage() . "\n";
}
exit;
