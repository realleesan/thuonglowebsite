<?php
// Simple test to debug featured brands issue
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing Featured Brands Section...<br>";

try {
    // Test database connection
    require_once 'core/database.php';
    $db = Database::getInstance();
    echo "Database connection: OK<br>";
    
    // Test table existence
    $result = $db->query("SHOW TABLES LIKE 'featured_brands_section'");
    if ($result) {
        echo "Table exists: OK<br>";
    } else {
        echo "Table does NOT exist<br>";
    }
    
    // Test data
    $result = $db->query("SELECT * FROM featured_brands_section LIMIT 1");
    if ($result) {
        echo "Data found: " . print_r($result[0], true) . "<br>";
    } else {
        echo "No data found<br>";
    }
    
    // Test model
    require_once 'app/models/FeaturedBrandsSectionModel.php';
    $model = new FeaturedBrandsSectionModel();
    echo "Model creation: OK<br>";
    
    $section = $model->getFirst();
    if ($section) {
        echo "Model getFirst: OK<br>";
    } else {
        echo "Model getFirst: No data<br>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    echo "Trace: " . $e->getTraceAsString() . "<br>";
}
?>
