<?php
/**
 * Check if section product tables exist and have data
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Check Section Products Tables</h1>";

try {
    require_once 'config.php';
    require_once 'core/database.php';
    
    $db = Database::getInstance();
    $pdo = $db->getPdo();
    
    echo "<h2>1. Check Section Product Tables</h2>";
    
    $section_product_tables = [
        'latest_products_section_products',
        'budget_products_section_products', 
        'sale_products_section_products',
        'featured_products_section_products'
    ];
    
    foreach ($section_product_tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<p>✅ $table EXISTS</p>";
            
            $count = $pdo->query("SELECT COUNT(*) as count FROM `$table`")->fetch(PDO::FETCH_ASSOC);
            echo "<p>   - Products: {$count['count']}</p>";
            
            if ($count['count'] > 0) {
                $products = $pdo->query("SELECT sp.*, p.name, p.price FROM `$table` sp JOIN products p ON sp.product_id = p.id LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
                echo "<p>   - Sample:</p>";
                echo "<table border='1' style='font-size: 12px;'><tr><th>Product ID</th><th>Name</th><th>Price</th><th>Sort Order</th></tr>";
                foreach ($products as $product) {
                    echo "<tr><td>{$product['product_id']}</td><td>" . substr($product['name'], 0, 30) . "...</td><td>{$product['price']}</td><td>{$product['sort_order']}</td></tr>";
                }
                echo "</table>";
            } else {
                echo "<p>   - ⚠️ EMPTY - Need to add products</p>";
            }
        } else {
            echo "<p>❌ $table MISSING</p>";
        }
    }
    
    echo "<h2>2. Add Products to Empty Sections</h2>";
    
    // Get active products
    $products = $pdo->query("SELECT id, name, price FROM products WHERE status = 'active' ORDER BY id DESC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($products)) {
        echo "<p>Found " . count($products) . " active products</p>";
        
        foreach ($section_product_tables as $table) {
            $count = $pdo->query("SELECT COUNT(*) as count FROM `$table`")->fetch(PDO::FETCH_ASSOC);
            
            if ($count['count'] == 0) {
                echo "<p>Adding products to $table...</p>";
                
                // Clear existing just in case
                $pdo->exec("DELETE FROM `$table`");
                
                // Add 4 products to each section
                for ($i = 0; $i < min(4, count($products)); $i++) {
                    $product_id = $products[$i]['id'];
                    $sort_order = $i + 1;
                    
                    try {
                        $insert_sql = "INSERT INTO `$table` (section_id, product_id, sort_order) VALUES (1, ?, ?)";
                        $stmt = $pdo->prepare($insert_sql);
                        $stmt->execute([$product_id, $sort_order]);
                        echo "<p>   ✅ Added: {$products[$i]['name']}</p>";
                    } catch (Exception $e) {
                        echo "<p>   ❌ Failed to add product {$product_id}: " . $e->getMessage() . "</p>";
                    }
                }
            } else {
                echo "<p>✅ $table already has {$count['count']} products</p>";
            }
        }
    } else {
        echo "<p>❌ No active products found</p>";
    }
    
    echo "<h2>3. Test PublicService Methods</h2>";
    
    require_once 'app/services/PublicService.php';
    $publicService = new PublicService();
    
    $methods = ['getLatestProducts', 'getBudgetProducts', 'getSaleProducts'];
    
    foreach ($methods as $method) {
        try {
            $result = $publicService->$method(4);
            echo "<p>✅ $method() returned " . count($result) . " products</p>";
            
            if (!empty($result)) {
                echo "<p>   - First product: " . $result[0]['name'] . " (Price: " . $result[0]['price'] . ")</p>";
            }
        } catch (Exception $e) {
            echo "<p>❌ $method() error: " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<h2>✅ Done!</h2>";
    echo "<p><a href='/'>View Homepage</a> | <a href='test_homepage_products.php'>Test Again</a></p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>
