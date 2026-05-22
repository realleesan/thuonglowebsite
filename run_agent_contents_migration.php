<?php
// Define security constant
define('THUONGLO_INIT', true);

require_once __DIR__ . '/core/database.php';

echo "<h2>Running Agent Contents Table Migrations</h2>";

try {
    $db = Database::getInstance();
    
    // Create table and insert default data
    echo "<p>Creating agent_contents table and seeding default data...</p>";
    $createSQL = file_get_contents(__DIR__ . '/database/migrations/050_create_agent_contents_table.sql');
    
    // Split by semicolons but be careful about semicolons inside text/strings if any. 
    // The default sql is simple enough to be split by ';'.
    $statements = array_filter(array_map('trim', explode(';', $createSQL)));
    foreach ($statements as $stmt) {
        if (!empty($stmt)) {
            $db->getPdo()->exec($stmt);
        }
    }
    echo "<p style='color: green;'>✓ Created agent_contents table and seeded data successfully.</p>";
    echo "<h3 style='color: green;'>Success!</h3>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
