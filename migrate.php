<?php
require_once __DIR__ . '/../../../../../../xampp/htdocs/thuonglowebsite/core/database.php';

try {
    $db = Database::getInstance();
    
    // Create product_categories table
    $sql = "CREATE TABLE IF NOT EXISTS product_categories (
        product_id INT NOT NULL,
        category_id INT NOT NULL,
        PRIMARY KEY (product_id, category_id),
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $db->exec($sql);
    echo "Table product_categories created successfully!\n";
    
    // Migrate existing data
    $migrateSql = "INSERT IGNORE INTO product_categories (product_id, category_id)
                   SELECT id, category_id FROM products 
                   WHERE category_id IS NOT NULL AND category_id > 0";
    $db->exec($migrateSql);
    echo "Migrated existing product categories to product_categories table!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

