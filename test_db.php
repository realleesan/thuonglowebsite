<?php
/**
 * Test Database Tables
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Tables Test</h1>";

try {
    // Load config
    $config = require __DIR__ . '/config.php';
    
    // Connect directly
    $dsn = "mysql:host={$config['database']['host']};dbname={$config['database']['name']};charset={$config['database']['charset']}";
    $pdo = new PDO($dsn, $config['database']['username'], $config['database']['password'], $config['database']['options']);
    
    echo "✅ Connected to database: {$config['database']['name']}<br>";
    
    // Show all tables
    echo "<h2>All Tables in Database:</h2>";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        echo "- {$table}<br>";
    }
    
    // Check for hero tables
    echo "<h2>Hero Section Tables Check:</h2>";
    
    $heroTables = ['hero_sections', 'hero_buttons'];
    
    foreach ($heroTables as $table) {
        if (in_array($table, $tables)) {
            echo "✅ Table '{$table}' EXISTS<br>";
            
            // Show table structure
            $columns = $pdo->query("DESCRIBE {$table}")->fetchAll();
            echo "<details><summary>Structure of {$table}</summary>";
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
            
            foreach ($columns as $column) {
                echo "<tr>";
                echo "<td>{$column['Field']}</td>";
                echo "<td>{$column['Type']}</td>";
                echo "<td>{$column['Null']}</td>";
                echo "<td>{$column['Key']}</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "</details>";
            
            // Show sample data
            $count = $pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
            echo "Records: {$count}<br>";
            
            if ($count > 0) {
                $sample = $pdo->query("SELECT * FROM {$table} LIMIT 3")->fetchAll();
                echo "<details><summary>Sample Data</summary>";
                echo "<pre>" . print_r($sample, true) . "</pre>";
                echo "</details>";
            }
        } else {
            echo "❌ Table '{$table}' MISSING<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Database Error: " . $e->getMessage() . "<br>";
    echo "Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
}
?>
