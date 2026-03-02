<?php
// Test news page
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing news page...<br>";

try {
    // Load config
    require_once __DIR__ . '/config.php';
    echo "Config loaded<br>";
    
    // Load database
    require_once __DIR__ . '/core/Database.php';
    echo "Database class loaded<br>";
    
    // Test database connection
    $db = Database::getInstance();
    echo "Database connected<br>";
    
    // Load models
    require_once __DIR__ . '/app/models/BaseModel.php';
    echo "BaseModel loaded<br>";
    
    require_once __DIR__ . '/app/models/NewsModel.php';
    echo "NewsModel loaded<br>";
    
    $newsModel = new NewsModel();
    echo "NewsModel instantiated<br>";
    
    $news = $newsModel->all();
    echo "News count: " . count($news) . "<br>";
    
    if (!empty($news)) {
        echo "<h3>First news item:</h3>";
        echo "<pre>";
        print_r($news[0]);
        echo "</pre>";
    }
    
    echo "<br><strong>SUCCESS!</strong> News page should work now.";
} catch (Exception $e) {
    echo "<br><strong>ERROR:</strong> " . $e->getMessage();
    echo "<br><strong>File:</strong> " . $e->getFile();
    echo "<br><strong>Line:</strong> " . $e->getLine();
    echo "<br><pre>" . $e->getTraceAsString() . "</pre>";
}
?>
