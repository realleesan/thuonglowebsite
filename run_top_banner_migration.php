<?php
require_once __DIR__ . '/core/database.php';

echo "<h2>Running Top Banner Table Migrations</h2>";

try {
    $db = Database::getInstance();
    
    // 1. Create table and insert default data
    echo "<p>Creating top_banners table and seeding default data...</p>";
    $createSQL = file_get_contents(__DIR__ . '/database/migrations/049_create_top_banners_table.sql');
    $statements = array_filter(array_map('trim', explode(';', $createSQL)));
    foreach ($statements as $stmt) {
        if (!empty($stmt)) {
            $db->getPdo()->exec($stmt);
        }
    }
    echo "<p style='color: green;'>✓ Created top_banners table and seeded data successfully.</p>";
    echo "<h3 style='color: green;'>Success!</h3>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
