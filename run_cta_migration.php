<?php
require_once __DIR__ . '/core/database.php';

echo "<h2>Running CTA Sections Migrations</h2>";

try {
    $db = Database::getInstance();
    
    // 1. Create table and insert default data
    echo "<p>Creating cta_sections table and seeding default data...</p>";
    $createSQL = file_get_contents(__DIR__ . '/database/migrations/048_create_cta_sections_table.sql');
    $statements = array_filter(array_map('trim', explode(';', $createSQL)));
    foreach ($statements as $stmt) {
        if (!empty($stmt)) {
            $db->getPdo()->exec($stmt);
        }
    }
    echo "<p style='color: green;'>✓ Created cta_sections table and seeded data successfully.</p>";
    echo "<h3 style='color: green;'>Success!</h3>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
