<?php
/**
 * Create section product tables
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Create Section Product Tables</h1>";

try {
    require_once 'config.php';
    require_once 'core/database.php';
    
    $db = Database::getInstance();
    $pdo = $db->getPdo();
    
    echo "<h2>Creating section product tables...</h2>";
    
    // Create latest_products_section_products table
    $sql1 = "
    CREATE TABLE IF NOT EXISTS `latest_products_section_products` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `section_id` int(11) NOT NULL DEFAULT 1,
        `product_id` int(11) NOT NULL,
        `sort_order` int(11) NOT NULL DEFAULT 0,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_section_product` (`section_id`,`product_id`),
        KEY `idx_section_id` (`section_id`),
        KEY `idx_product_id` (`product_id`),
        FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    // Create budget_products_section_products table  
    $sql2 = "
    CREATE TABLE IF NOT EXISTS `budget_products_section_products` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `section_id` int(11) NOT NULL DEFAULT 1,
        `product_id` int(11) NOT NULL,
        `sort_order` int(11) NOT NULL DEFAULT 0,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_section_product` (`section_id`,`product_id`),
        KEY `idx_section_id` (`section_id`),
        KEY `idx_product_id` (`product_id`),
        FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    // Create sale_products_section_products table
    $sql3 = "
    CREATE TABLE IF NOT EXISTS `sale_products_section_products` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `section_id` int(11) NOT NULL DEFAULT 1,
        `product_id` int(11) NOT NULL,
        `sort_order` int(11) NOT NULL DEFAULT 0,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_section_product` (`section_id`,`product_id`),
        KEY `idx_section_id` (`section_id`),
        KEY `idx_product_id` (`product_id`),
        FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    // Create featured_products_section_products table
    $sql4 = "
    CREATE TABLE IF NOT EXISTS `featured_products_section_products` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `section_id` int(11) NOT NULL DEFAULT 1,
        `product_id` int(11) NOT NULL,
        `sort_order` int(11) NOT NULL DEFAULT 0,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_section_product` (`section_id`,`product_id`),
        KEY `idx_section_id` (`section_id`),
        KEY `idx_product_id` (`product_id`),
        FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    $tables = [
        'latest_products_section_products' => $sql1,
        'budget_products_section_products' => $sql2,
        'sale_products_section_products' => $sql3,
        'featured_products_section_products' => $sql4
    ];
    
    foreach ($tables as $table_name => $sql) {
        try {
            $pdo->exec($sql);
            echo "<p>✅ $table_name created successfully</p>";
        } catch (Exception $e) {
            echo "<p>❌ $table_name failed: " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<h2>Adding sample products to sections...</h2>";
    
    // Get some active products
    $products = $pdo->query("SELECT id FROM products WHERE status = 'active' ORDER BY id DESC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($products)) {
        $section_tables = ['latest_products_section_products', 'budget_products_section_products', 'sale_products_section_products', 'featured_products_section_products'];
        
        foreach ($section_tables as $table) {
            // Clear existing
            $pdo->exec("DELETE FROM `$table`");
            
            // Add 4 products to each section
            for ($i = 0; $i < min(4, count($products)); $i++) {
                $product_id = $products[$i]['id'];
                $sort_order = $i + 1;
                
                $insert_sql = "INSERT INTO `$table` (section_id, product_id, sort_order) VALUES (1, ?, ?)";
                $stmt = $pdo->prepare($insert_sql);
                $stmt->execute([$product_id, $sort_order]);
            }
            
            echo "<p>✅ Added 4 products to $table</p>";
        }
    } else {
        echo "<p>❌ No active products found</p>";
    }
    
    echo "<h2>✅ All done!</h2>";
    echo "<p><a href='test_homepage_products.php'>Check results</a></p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>
