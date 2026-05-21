<?php
define('THUONGLO_INIT', true);
require_once 'config.php';
require_once 'core/database.php';

try {
    $db = Database::getInstance();
    
    // Check if column 'icon' already exists
    $columns = $db->query("SHOW COLUMNS FROM categories LIKE 'icon'");
    if (empty($columns)) {
        $db->exec("ALTER TABLE categories ADD COLUMN icon VARCHAR(255) DEFAULT NULL");
        echo "Column 'icon' added successfully!\n";
    } else {
        echo "Column 'icon' already exists!\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
