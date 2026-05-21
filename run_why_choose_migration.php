<?php
require_once __DIR__ . '/core/database.php';

echo "<h2>Running Why Choose Us Migrations</h2>";

try {
    $db = Database::getInstance();
    
    // 1. Create tables
    echo "<p>Creating tables...</p>";
    $createSQL = file_get_contents(__DIR__ . '/database/migrations/045_create_why_choose_section_table.sql');
    // PHP/PDO query might not support multiple statements in a single query unless specified,
    // so let's split by semicolon or execute them sequentially if needed. But let's check.
    // Actually, split by double newline or execute raw. To be safe, we can run them as separate statements.
    $statements = array_filter(array_map('trim', explode(';', $createSQL)));
    foreach ($statements as $stmt) {
        if (!empty($stmt)) {
            $db->getPdo()->exec($stmt);
        }
    }
    echo "<p style='color: green;'>✓ Created why_choose_section and why_choose_items tables.</p>";
    
    // 2. Seed default data
    echo "<p>Seeding data...</p>";
    $insertSQL = file_get_contents(__DIR__ . '/database/migrations/046_insert_why_choose_section_data.sql');
    $statements2 = array_filter(array_map('trim', explode(';', $insertSQL)));
    foreach ($statements2 as $stmt) {
        if (!empty($stmt)) {
            $db->getPdo()->exec($stmt);
        }
    }
    echo "<p style='color: green;'>✓ Seeded default Why Choose Us data.</p>";
    
    echo "<h3 style='color: green;'>Success!</h3>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
