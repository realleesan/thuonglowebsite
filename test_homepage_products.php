<?php
/**
 * Test homepage products in sections
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Homepage Products Debug</h1>";

try {
    require_once 'config.php';
    require_once 'core/database.php';
    
    $db = Database::getInstance();
    $pdo = $db->getPdo();
    
    echo "<h2>1. Check Section Tables</h2>";
    
    // Check if section product tables exist
    $section_tables = [
        'latest_products_section_products',
        'budget_products_section_products', 
        'sale_products_section_products',
        'featured_products_section_products'
    ];
    
    foreach ($section_tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        $exists = $stmt->rowCount() > 0 ? "✅ EXISTS" : "❌ MISSING";
        echo "<p>$table: $exists</p>";
        
        if ($stmt->rowCount() > 0) {
            $count = $pdo->query("SELECT COUNT(*) as count FROM `$table`")->fetch(PDO::FETCH_ASSOC);
            echo "<p>   - Products: {$count['count']}</p>";
            
            if ($count['count'] > 0) {
                $products = $pdo->query("SELECT * FROM `$table` LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
                echo "<p>   - Sample products:</p>";
                echo "<pre>" . print_r($products, true) . "</pre>";
            }
        }
    }
    
    echo "<h2>2. Check Main Products Table</h2>";
    $products_count = $pdo->query("SELECT COUNT(*) as count FROM products")->fetch(PDO::FETCH_ASSOC);
    echo "<p>Total products in database: {$products_count['count']}</p>";
    
    if ($products_count['count'] > 0) {
        $sample_products = $pdo->query("SELECT id, name, price, status, type FROM products LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
        echo "<p>Sample products:</p>";
        echo "<table border='1'><tr><th>ID</th><th>Name</th><th>Price</th><th>Status</th><th>Type</th></tr>";
        foreach ($sample_products as $product) {
            echo "<tr><td>{$product['id']}</td><td>{$product['name']}</td><td>{$product['price']}</td><td>{$product['status']}</td><td>{$product['type']}</td></tr>";
        }
        echo "</table>";
    }
    
    echo "<h2>3. Check Homepage Display Logic</h2>";
    
    // Check how homepage gets products
    require_once 'app/services/PublicService.php';
    $publicService = new PublicService();
    
    echo "<p>✅ PublicService loaded</p>";
    
    // Test getLatestProducts
    if (method_exists($publicService, 'getLatestProducts')) {
        try {
            $latest = $publicService->getLatestProducts(5);
            echo "<p>✅ getLatestProducts() returned " . count($latest) . " products</p>";
            if (!empty($latest)) {
                echo "<pre>" . print_r($latest[0], true) . "</pre>";
            }
        } catch (Exception $e) {
            echo "<p>❌ getLatestProducts() error: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>❌ getLatestProducts() method not found</p>";
    }
    
    // Test getBudgetProducts
    if (method_exists($publicService, 'getBudgetProducts')) {
        try {
            $budget = $publicService->getBudgetProducts(5);
            echo "<p>✅ getBudgetProducts() returned " . count($budget) . " products</p>";
        } catch (Exception $e) {
            echo "<p>❌ getBudgetProducts() error: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>❌ getBudgetProducts() method not found</p>";
    }
    
    // Test getSaleProducts
    if (method_exists($publicService, 'getSaleProducts')) {
        try {
            $sale = $publicService->getSaleProducts(5);
            echo "<p>✅ getSaleProducts() returned " . count($sale) . " products</p>";
        } catch (Exception $e) {
            echo "<p>❌ getSaleProducts() error: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>❌ getSaleProducts() method not found</p>";
    }
    
    echo "<h2>4. Check Home View</h2>";
    $home_view = file_get_contents('app/views/home/home.php');
    if (strpos($home_view, 'getLatestProducts') !== false) {
        echo "<p>✅ Home view calls getLatestProducts()</p>";
    } else {
        echo "<p>❌ Home view doesn't call getLatestProducts()</p>";
    }
    
    if (strpos($home_view, 'getBudgetProducts') !== false) {
        echo "<p>✅ Home view calls getBudgetProducts()</p>";
    } else {
        echo "<p>❌ Home view doesn't call getBudgetProducts()</p>";
    }
    
    if (strpos($home_view, 'getSaleProducts') !== false) {
        echo "<p>✅ Home view calls getSaleProducts()</p>";
    } else {
        echo "<p>❌ Home view doesn't call getSaleProducts()</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<h2>Test Complete</h2>";
?>
