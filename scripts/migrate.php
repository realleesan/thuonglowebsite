<?php
/**
 * Database Migration Script
 * Runs all pending migrations in order
 */

// Include the Database class
require_once __DIR__ . '/../core/Database.php';

echo "=== Database Migration Script ===\n\n";

try {
    // Get database instance
    $db = Database::getInstance();
    
    // Test connection first
    if (!$db->testConnection()) {
        throw new Exception("Database connection failed!");
    }
    
    echo "✓ Database connection successful\n\n";
    
    // Get migrations directory
    $migrationsDir = __DIR__ . '/../database/migrations';
    
    if (!is_dir($migrationsDir)) {
        throw new Exception("Migrations directory not found: $migrationsDir");
    }
    
    // Get all migration files
    $migrationFiles = glob($migrationsDir . '/*.sql');
    sort($migrationFiles);
    
    if (empty($migrationFiles)) {
        echo "No migration files found.\n";
        exit(0);
    }
    
    echo "Found " . count($migrationFiles) . " migration files\n\n";
    
    // Check if migrations table exists, if not create it first
    $tables = $db->query("SHOW TABLES LIKE 'migrations'");
    if (empty($tables)) {
        echo "Creating migrations table...\n";
        // Run the first migration (000_create_migrations_table.sql) manually
        $firstMigration = file_get_contents($migrationsDir . '/000_create_migrations_table.sql');
        if ($firstMigration) {
            $db->execute($firstMigration);
            echo "✓ Migrations table created\n\n";
        }
    }
    
    // Get already executed migrations
    $executedMigrations = [];
    try {
        $executed = $db->table('migrations')->select('migration')->get();
        foreach ($executed as $migration) {
            $executedMigrations[] = $migration['migration'];
        }
    } catch (Exception $e) {
        // Migrations table might not exist yet
        echo "Warning: Could not read migrations table: " . $e->getMessage() . "\n";
    }
    
    $newMigrations = 0;
    $currentBatch = 1;
    
    // Get next batch number
    try {
        $lastBatch = $db->query("SELECT MAX(batch) as max_batch FROM migrations");
        if (!empty($lastBatch) && $lastBatch[0]['max_batch']) {
            $currentBatch = $lastBatch[0]['max_batch'] + 1;
        }
    } catch (Exception $e) {
        // Use default batch 1
    }
    
    // Process each migration file
    foreach ($migrationFiles as $file) {
        $filename = basename($file, '.sql');
        
        // Skip if already executed
        if (in_array($filename, $executedMigrations)) {
            echo "⏭  Skipping (already executed): $filename\n";
            continue;
        }
        
        echo "🔄 Running migration: $filename\n";
        
        // Read migration file
        $sql = file_get_contents($file);
        if (!$sql) {
            echo "   ❌ Error: Could not read migration file\n";
            continue;
        }
        
        try {
            // Execute migration
            $db->execute($sql);
            
            // Record migration as executed (skip for 000_create_migrations_table as it records itself)
            if ($filename !== '000_create_migrations_table') {
                $db->table('migrations')->insert([
                    'migration' => $filename,
                    'batch' => $currentBatch
                ]);
            }
            
            echo "   ✅ Success: $filename\n";
            $newMigrations++;
            
        } catch (Exception $e) {
            echo "   ❌ Error: " . $e->getMessage() . "\n";
            echo "   Migration failed, stopping execution.\n";
            exit(1);
        }
    }
    
    echo "\n=== Migration Summary ===\n";
    echo "Total migrations found: " . count($migrationFiles) . "\n";
    echo "Already executed: " . count($executedMigrations) . "\n";
    echo "New migrations run: $newMigrations\n";
    
    if ($newMigrations > 0) {
        echo "Current batch: $currentBatch\n";
        echo "\n✅ All migrations completed successfully!\n";
    } else {
        echo "\n✅ Database is up to date!\n";
    }
    
    // Show final table list
    echo "\n=== Database Tables ===\n";
    $tables = $db->query("SHOW TABLES");
    foreach ($tables as $table) {
        $tableName = array_values($table)[0];
        echo "- $tableName\n";
    }
    
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    echo "\nPlease check:\n";
    echo "1. Database connection settings\n";
    echo "2. Database permissions\n";
    echo "3. Migration file syntax\n";
    exit(1);
}