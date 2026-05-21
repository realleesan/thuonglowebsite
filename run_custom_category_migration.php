<?php
require_once __DIR__ . '/core/database.php';

echo "<h2>Running Custom Category Sections Migrations</h2>";

try {
    $db = Database::getInstance();
    
    // 1. Create table
    echo "<p>Creating custom_category_sections table...</p>";
    $createSQL = file_get_contents(__DIR__ . '/database/migrations/047_create_custom_category_sections_table.sql');
    $statements = array_filter(array_map('trim', explode(';', $createSQL)));
    foreach ($statements as $stmt) {
        if (!empty($stmt)) {
            $db->getPdo()->exec($stmt);
        }
    }
    echo "<p style='color: green;'>✓ Created custom_category_sections table successfully.</p>";
    echo "<h3 style='color: green;'>Success!</h3>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
