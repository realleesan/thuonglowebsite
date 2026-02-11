<?php
/**
 * Database Migration Script
 * Runs all pending migrations in order
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the Database class
require_once __DIR__ . '/../core/database.php';

// Check if running in browser
$isBrowser = isset($_SERVER['HTTP_HOST']);

if ($isBrowser) {
    echo "<h2>Database Migration Script</h2>\n";
} else {
    echo "=== Database Migration Script ===\n\n";
}

try {
    // Get database instance
    $db = Database::getInstance();
    
    // Test connection first
    if (!$db->testConnection()) {
        throw new Exception("Database connection failed!");
    }
    
    if ($isBrowser) {
        echo "<p>✓ Database connection successful</p>\n";
    } else {
        echo "✓ Database connection successful\n\n";
    }
    
    // Get migrations directory
    $migrationsDir = __DIR__ . '/../database/migrations';
    
    if (!is_dir($migrationsDir)) {
        throw new Exception("Migrations directory not found: $migrationsDir");
    }
    
    // Get all migration files
    $migrationFiles = glob($migrationsDir . '/*.sql');
    sort($migrationFiles);
    
    if (empty($migrationFiles)) {
        $msg = "No migration files found.";
        echo $isBrowser ? "<p>$msg</p>" : "$msg\n";
        exit(0);
    }
    
    $msg = "Found " . count($migrationFiles) . " migration files";
    echo $isBrowser ? "<p>$msg</p>" : "$msg\n\n";
    
    // Check if migrations table exists, if not create it first
    $tables = $db->query("SHOW TABLES LIKE 'migrations'");
    if (empty($tables)) {
        $msg = "Creating migrations table...";
        echo $isBrowser ? "<p>$msg</p>" : "$msg\n";
        
        // Run the first migration (000_create_migrations_table.sql) manually
        $firstMigration = file_get_contents($migrationsDir . '/000_create_migrations_table.sql');
        if ($firstMigration) {
            // Execute migration - handle multiple statements
            $statements = array_filter(array_map('trim', explode(';', $firstMigration)));
            
            foreach ($statements as $statement) {
                if (!empty($statement) && !preg_match('/^\s*--/', $statement)) {
                    $db->execute($statement);
                }
            }
            
            // Wait a moment for table creation to complete
            usleep(100000); // 0.1 second
            
            // Verify table was created before trying to insert
            $checkTable = $db->query("SHOW TABLES LIKE 'migrations'");
            if (!empty($checkTable)) {
                // Record this migration as executed
                try {
                    $db->table('migrations')->insert([
                        'migration' => '000_create_migrations_table',
                        'batch' => 1
                    ]);
                } catch (Exception $e) {
                    // If insert fails, try direct SQL
                    $db->execute("INSERT INTO migrations (migration, batch) VALUES ('000_create_migrations_table', 1)");
                }
            }
            
            $msg = "✓ Migrations table created";
            echo $isBrowser ? "<p>$msg</p>" : "$msg\n\n";
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
        $msg = "Warning: Could not read migrations table: " . $e->getMessage();
        echo $isBrowser ? "<p style='color: orange;'>$msg</p>" : "$msg\n";
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
    
    if ($isBrowser) {
        echo "<h3>Migration Progress</h3>";
    }
    
    // Process each migration file
    foreach ($migrationFiles as $file) {
        $filename = basename($file, '.sql');
        
        // Skip if already executed
        if (in_array($filename, $executedMigrations)) {
            $msg = "⏭  Skipping (already executed): $filename";
            echo $isBrowser ? "<p>$msg</p>" : "$msg\n";
            continue;
        }
        
        $msg = "🔄 Running migration: $filename";
        echo $isBrowser ? "<p>$msg</p>" : "$msg\n";
        
        // Read migration file
        $sql = file_get_contents($file);
        if (!$sql) {
            $msg = "   ❌ Error: Could not read migration file";
            echo $isBrowser ? "<p style='color: red;'>$msg</p>" : "$msg\n";
            continue;
        }
        
        try {
            // Execute migration - handle multiple statements
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            
            foreach ($statements as $statement) {
                if (!empty($statement) && !preg_match('/^\s*--/', $statement)) {
                    $db->execute($statement);
                }
            }
            
            // Record migration as executed (skip for 000_create_migrations_table as it records itself)
            if ($filename !== '000_create_migrations_table') {
                $db->table('migrations')->insert([
                    'migration' => $filename,
                    'batch' => $currentBatch
                ]);
            }
            
            $msg = "   ✅ Success: $filename";
            echo $isBrowser ? "<p style='color: green;'>$msg</p>" : "$msg\n";
            $newMigrations++;
            
        } catch (Exception $e) {
            $msg = "   ❌ Error: " . $e->getMessage();
            echo $isBrowser ? "<p style='color: red;'>$msg</p>" : "$msg\n";
            $msg = "   Migration failed, stopping execution.";
            echo $isBrowser ? "<p style='color: red;'>$msg</p>" : "$msg\n";
            exit(1);
        }
    }
    
    if ($isBrowser) {
        echo "<h3>Migration Summary</h3>";
    } else {
        echo "\n=== Migration Summary ===\n";
    }
    
    $totalMsg = "Total migrations found: " . count($migrationFiles);
    $executedMsg = "Already executed: " . count($executedMigrations);
    $newMsg = "New migrations run: $newMigrations";
    
    if ($isBrowser) {
        echo "<p>$totalMsg</p>";
        echo "<p>$executedMsg</p>";
        echo "<p>$newMsg</p>";
    } else {
        echo "$totalMsg\n";
        echo "$executedMsg\n";
        echo "$newMsg\n";
    }
    
    if ($newMigrations > 0) {
        $batchMsg = "Current batch: $currentBatch";
        $successMsg = "✅ All migrations completed successfully!";
        
        if ($isBrowser) {
            echo "<p>$batchMsg</p>";
            echo "<h3 style='color: green;'>$successMsg</h3>";
        } else {
            echo "$batchMsg\n";
            echo "\n$successMsg\n";
        }
    } else {
        $upToDateMsg = "✅ Database is up to date!";
        echo $isBrowser ? "<h3 style='color: green;'>$upToDateMsg</h3>" : "\n$upToDateMsg\n";
    }
    
    // Show final table list
    if ($isBrowser) {
        echo "<h3>Database Tables</h3>";
        echo "<ul>";
    } else {
        echo "\n=== Database Tables ===\n";
    }
    
    $tables = $db->query("SHOW TABLES");
    foreach ($tables as $table) {
        $tableName = array_values($table)[0];
        if ($isBrowser) {
            echo "<li>$tableName</li>";
        } else {
            echo "- $tableName\n";
        }
    }
    
    if ($isBrowser) {
        echo "</ul>";
    }
    
} catch (Exception $e) {
    $errorMsg = "❌ Migration failed: " . $e->getMessage();
    $checkMsg = "Please check:\n1. Database connection settings\n2. Database permissions\n3. Migration file syntax";
    
    if ($isBrowser) {
        echo "<h3 style='color: red;'>$errorMsg</h3>";
        echo "<p>Please check:</p>";
        echo "<ol>";
        echo "<li>Database connection settings</li>";
        echo "<li>Database permissions</li>";
        echo "<li>Migration file syntax</li>";
        echo "</ol>";
    } else {
        echo "$errorMsg\n";
        echo "\n$checkMsg\n";
    }
    exit(1);
}