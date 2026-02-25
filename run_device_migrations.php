<?php
/**
 * Run migrations for device access system
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Starting migrations...\n";

    // Migration 018
    $sql018 = file_get_contents(__DIR__ . '/database/migrations/018_create_device_sessions_table.sql');
    $db->exec($sql018);
    echo "Migration 018 applied successfully.\n";

    // Migration 019
    $sql019 = file_get_contents(__DIR__ . '/database/migrations/019_create_device_verification_codes_table.sql');
    $db->exec($sql019);
    echo "Migration 019 applied successfully.\n";

    echo "All migrations completed.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
