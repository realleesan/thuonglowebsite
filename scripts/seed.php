<?php
/**
 * Database Seeder Script
 * Runs all seeders to populate database with initial data
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the Database class
require_once __DIR__ . '/../core/database.php';

// Include all seeder classes
require_once __DIR__ . '/../database/seeders/BaseSeeder.php';
require_once __DIR__ . '/../database/seeders/UsersSeeder.php';
require_once __DIR__ . '/../database/seeders/CategoriesSeeder.php';
require_once __DIR__ . '/../database/seeders/ProductsSeeder.php';
require_once __DIR__ . '/../database/seeders/OrdersSeeder.php';
require_once __DIR__ . '/../database/seeders/NewsSeeder.php';
require_once __DIR__ . '/../database/seeders/EventsSeeder.php';
require_once __DIR__ . '/../database/seeders/ContactsSeeder.php';
require_once __DIR__ . '/../database/seeders/SettingsSeeder.php';
require_once __DIR__ . '/../database/seeders/AffiliatesSeeder.php';

// Check if running in browser
$isBrowser = isset($_SERVER['HTTP_HOST']);

if ($isBrowser) {
    echo "<h2>Database Seeder Script</h2>\n";
} else {
    echo "=== Database Seeder Script ===\n\n";
}

try {
    // Test database connection
    $db = Database::getInstance();
    if (!$db->testConnection()) {
        throw new Exception("Database connection failed!");
    }
    
    $msg = "✓ Database connection successful";
    echo $isBrowser ? "<p>$msg</p>" : "$msg\n\n";
    
    // Check if tables exist
    $requiredTables = [
        'users', 'categories', 'products', 'orders', 'order_items',
        'news', 'events', 'contacts', 'settings', 'affiliates'
    ];
    
    foreach ($requiredTables as $table) {
        $exists = $db->query("SHOW TABLES LIKE '{$table}'");
        if (empty($exists)) {
            throw new Exception("Table '{$table}' does not exist. Please run migrations first.");
        }
    }
    
    $msg = "✓ All required tables exist";
    echo $isBrowser ? "<p>$msg</p>" : "$msg\n\n";
    
    // Define seeder execution order (important for foreign key constraints)
    $seeders = [
        'UsersSeeder',
        'CategoriesSeeder', 
        'ProductsSeeder',
        'OrdersSeeder',
        'NewsSeeder',
        'EventsSeeder',
        'ContactsSeeder',
        'SettingsSeeder',
        'AffiliatesSeeder'
    ];
    
    $startTime = microtime(true);
    $totalSeeded = 0;
    
    if ($isBrowser) {
        echo "<h3>Seeding Progress</h3>";
    }
    
    // Run each seeder
    foreach ($seeders as $seederClass) {
        $msg = "🚀 Running {$seederClass}...";
        echo $isBrowser ? "<p>$msg</p>" : "$msg\n";
        
        try {
            $seeder = new $seederClass();
            $seeder->run();
            $totalSeeded++;
            $msg = "✅ {$seederClass} completed successfully";
            echo $isBrowser ? "<p style='color: green;'>$msg</p>" : "$msg\n\n";
            
        } catch (Exception $e) {
            $msg = "❌ {$seederClass} failed: " . $e->getMessage();
            echo $isBrowser ? "<p style='color: red;'>$msg</p>" : "$msg\n";
            $msg = "Stopping seeder execution.";
            echo $isBrowser ? "<p style='color: red;'>$msg</p>" : "$msg\n";
            exit(1);
        }
    }
    
    $endTime = microtime(true);
    $executionTime = round($endTime - $startTime, 2);
    
    if ($isBrowser) {
        echo "<h3>Seeding Summary</h3>";
    } else {
        echo "=== Seeding Summary ===\n";
    }
    
    $totalMsg = "Total seeders run: {$totalSeeded}/" . count($seeders);
    $timeMsg = "Execution time: {$executionTime} seconds";
    $successMsg = "✅ All seeders completed successfully!";
    
    if ($isBrowser) {
        echo "<p>$totalMsg</p>";
        echo "<p>$timeMsg</p>";
        echo "<h3 style='color: green;'>$successMsg</h3>";
    } else {
        echo "$totalMsg\n";
        echo "$timeMsg\n";
        echo "\n$successMsg\n";
    }
    
    // Show final statistics
    if ($isBrowser) {
        echo "<h3>Database Statistics</h3>";
        echo "<ul>";
    } else {
        echo "\n=== Database Statistics ===\n";
    }
    
    foreach ($requiredTables as $table) {
        try {
            $count = $db->query("SELECT COUNT(*) as count FROM {$table}");
            $recordCount = $count[0]['count'] ?? 0;
            if ($isBrowser) {
                echo "<li>{$table}: {$recordCount} records</li>";
            } else {
                echo "- {$table}: {$recordCount} records\n";
            }
        } catch (Exception $e) {
            $msg = "{$table}: Error counting records";
            if ($isBrowser) {
                echo "<li style='color: orange;'>$msg</li>";
            } else {
                echo "- $msg\n";
            }
        }
    }
    
    if ($isBrowser) {
        echo "</ul>";
        echo "<h3 style='color: green;'>🎉 Database seeding completed! Your application is ready to use.</h3>";
    } else {
        echo "\n🎉 Database seeding completed! Your application is ready to use.\n";
    }
    
} catch (Exception $e) {
    $errorMsg = "❌ Seeding failed: " . $e->getMessage();
    
    if ($isBrowser) {
        echo "<h3 style='color: red;'>$errorMsg</h3>";
        echo "<p>Please check:</p>";
        echo "<ol>";
        echo "<li>Database connection settings</li>";
        echo "<li>All migrations have been run</li>";
        echo "<li>JSON data files exist and are valid</li>";
        echo "<li>Database permissions</li>";
        echo "</ol>";
    } else {
        echo "$errorMsg\n";
        echo "\nPlease check:\n";
        echo "1. Database connection settings\n";
        echo "2. All migrations have been run\n";
        echo "3. JSON data files exist and are valid\n";
        echo "4. Database permissions\n";
    }
    exit(1);
}