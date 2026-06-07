<?php
define('THUONGLO_INIT', true);
$config = require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/database.php';
require_once __DIR__ . '/app/models/BaseModel.php';
require_once __DIR__ . '/app/models/NewsModel.php';

try {
    $newsModel = new \NewsModel();
    $tagsData = $newsModel->query("SELECT tags FROM news WHERE tags IS NOT NULL AND tags != ''");
    $all_tags = [];
    foreach ($tagsData as $item) {
        $itemTags = array_map('trim', explode(',', $item['tags']));
        $all_tags = array_merge($all_tags, $itemTags);
    }
    $all_tags = array_unique(array_filter($all_tags));
    sort($all_tags);
    echo "Found tags:\n";
    print_r($all_tags);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
