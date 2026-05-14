<?php
/**
 * Migration Runner for Hero Section Tables
 * Run this script to create hero_sections and hero_buttons tables
 */

// Prevent direct access
if (!defined('THUONGLO_INIT')) {
    define('THUONGLO_INIT', true);
}

// Load configuration
require_once __DIR__ . '/../config.php';

try {
    // Connect to database
    $dsn = "mysql:host={$config['database']['host']};dbname={$config['database']['name']};charset={$config['database']['charset']}";
    $pdo = new PDO($dsn, $config['database']['username'], $config['database']['password'], $config['database']['options']);
    
    echo "Connected to database successfully!\n";
    
    // List of migration files to run
    $migrations = [
        '028_create_hero_sections_table.sql',
        '029_create_hero_buttons_table.sql', 
        '030_insert_hero_section_data.sql'
    ];
    
    // Create migrations table if not exists
    $createMigrationsTable = "
        CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL UNIQUE,
            batch INT NOT NULL DEFAULT 1,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $pdo->exec($createMigrationsTable);
    echo "Migrations table ready!\n";
    
    // Run each migration
    foreach ($migrations as $migrationFile) {
        echo "Processing migration: $migrationFile\n";
        
        // Check if migration already ran
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM migrations WHERE migration = ?");
        $checkStmt->execute([$migrationFile]);
        
        if ($checkStmt->fetchColumn() > 0) {
            echo "  - Already executed, skipping...\n";
            continue;
        }
        
        // Read and execute migration
        $migrationPath = __DIR__ . '/migrations/' . $migrationFile;
        if (!file_exists($migrationPath)) {
            echo "  - ERROR: Migration file not found: $migrationPath\n";
            continue;
        }
        
        $sql = file_get_contents($migrationPath);
        
        // Split SQL by semicolon and execute each statement
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        $pdo->beginTransaction();
        try {
            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    $pdo->exec($statement);
                }
            }
            
            // Record migration
            $recordStmt = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
            $recordStmt->execute([$migrationFile, 1]);
            
            $pdo->commit();
            echo "  - SUCCESS: Migration executed successfully!\n";
            
        } catch (Exception $e) {
            $pdo->rollback();
            echo "  - ERROR: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\nMigration process completed!\n";
    
    // Verify tables were created
    echo "\nVerifying tables...\n";
    $tables = ['hero_sections', 'hero_buttons'];
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        if ($stmt->rowCount() > 0) {
            echo "  ✓ Table '$table' exists\n";
            
            // Show record count
            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM $table");
            $countStmt->execute();
            $count = $countStmt->fetchColumn();
            echo "    - Records: $count\n";
        } else {
            echo "  ✗ Table '$table' NOT found\n";
        }
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>
