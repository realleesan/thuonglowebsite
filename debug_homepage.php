<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Homepage Debug</h1>";

try {
    // Test required files
    echo "<h2>Checking required files...</h2>";
    
    $requiredFiles = [
        __DIR__ . '/app/services/AuthService.php',
        __DIR__ . '/app/models/HeroSectionModel.php',
        __DIR__ . '/app/models/HeroButtonModel.php',
        __DIR__ . '/app/models/FeaturedProductsSectionModel.php',
        __DIR__ . '/app/models/LatestProductsSectionModel.php',
        __DIR__ . '/app/models/BudgetProductsSectionModel.php',
        __DIR__ . '/app/models/SaleProductsSectionModel.php',
        __DIR__ . '/core/view_init.php'
    ];
    
    foreach ($requiredFiles as $file) {
        if (file_exists($file)) {
            echo "<span style='color: green;'>✓ $file</span><br>";
        } else {
            echo "<span style='color: red;'>✗ $file - NOT FOUND</span><br>";
        }
    }
    
    echo "<h2>Testing database connection...</h2>";
    
    // Test database connection
    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/core/database.php';
    $conn = getConnection();
    
    if ($conn) {
        echo "<span style='color: green;'>✓ Database connection successful</span><br>";
        echo "<strong>Database Info:</strong><br>";
        echo "- Host: " . $config['database']['host'] . "<br>";
        echo "- Name: " . $config['database']['name'] . "<br>";
        echo "- User: " . $config['database']['username'] . "<br>";
        
        // Check if tables exist using PDO directly
        $tables = ['hero_sections', 'featured_products_section', 'latest_products_section', 'budget_products_section', 'sale_products_section'];
        
        echo "<br><strong>Table Status:</strong><br>";
        $stmt = $conn->query("SHOW TABLES");
        $tableList = [];
        if ($stmt) {
            while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                $tableList[] = $row[0];
            }
        }
        
        foreach ($tables as $table) {
            if (in_array($table, $tableList)) {
                echo "<span style='color: green;'>✓ Table '$table' exists</span><br>";
            } else {
                echo "<span style='color: red;'>✗ Table '$table' missing</span><br>";
            }
        }
        
        // Show all tables in database
        echo "<br><strong>All tables in database:</strong><br>";
        $stmt = $conn->query("SHOW TABLES");
        if ($stmt) {
            while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                echo "- " . $row[0] . "<br>";
            }
        }
        
        // PDO doesn't have close() method
        $conn = null;
    } else {
        echo "<span style='color: red;'>✗ Database connection failed</span><br>";
    }
    
    echo "<h2>Testing HomepageController...</h2>";
    
    // Try to load HomepageController
    require_once __DIR__ . '/app/controllers/HomepageController.php';
    echo "<span style='color: green;'>✓ HomepageController loaded successfully</span><br>";
    
    // Try to create instance
    $controller = new HomepageController();
    echo "<span style='color: green;'>✓ HomepageController instantiated successfully</span><br>";
    
} catch (Exception $e) {
    echo "<span style='color: red;'>Error: " . $e->getMessage() . "</span><br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} catch (Error $e) {
    echo "<span style='color: red;'>Fatal Error: " . $e->getMessage() . "</span><br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h2>PHP Info</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Memory Limit: " . ini_get('memory_limit') . "<br>";
echo "Max Execution Time: " . ini_get('max_execution_time') . "<br>";
?>
