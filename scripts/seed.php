<?php
/**
 * Database Seeder Script
 * Runs all seeders to populate database with initial data
 */

// Include the Database class
require_once __DIR__ . '/../core/Database.php';

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

echo "=== Database Seeder Script ===\n\n";

try {
    // Test database connection
    $db = Database::getInstance();
    if (!$db->testConnection()) {
        throw new Exception("Database connection failed!");
    }
    
    echo "✓ Database connection successful\n\n";
    
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
    
    echo "✓ All required tables exist\n\n";
    
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
    
    // Run each seeder
    foreach ($seeders as $seederClass) {
        echo "🚀 Running {$seederClass}...\n";
        
        try {
            $seeder = new $seederClass();
            $seeder->run();
            $totalSeeded++;
            echo "✅ {$seederClass} completed successfully\n\n";
            
        } catch (Exception $e) {
            echo "❌ {$seederClass} failed: " . $e->getMessage() . "\n";
            echo "Stopping seeder execution.\n";
            exit(1);
        }
    }
    
    $endTime = microtime(true);
    $executionTime = round($endTime - $startTime, 2);
    
    echo "=== Seeding Summary ===\n";
    echo "Total seeders run: {$totalSeeded}/" . count($seeders) . "\n";
    echo "Execution time: {$executionTime} seconds\n";
    echo "\n✅ All seeders completed successfully!\n";
    
    // Show final statistics
    echo "\n=== Database Statistics ===\n";
    foreach ($requiredTables as $table) {
        try {
            $count = $db->query("SELECT COUNT(*) as count FROM {$table}");
            $recordCount = $count[0]['count'] ?? 0;
            echo "- {$table}: {$recordCount} records\n";
        } catch (Exception $e) {
            echo "- {$table}: Error counting records\n";
        }
    }
    
    echo "\n🎉 Database seeding completed! Your application is ready to use.\n";
    
} catch (Exception $e) {
    echo "❌ Seeding failed: " . $e->getMessage() . "\n";
    echo "\nPlease check:\n";
    echo "1. Database connection settings\n";
    echo "2. All migrations have been run\n";
    echo "3. JSON data files exist and are valid\n";
    echo "4. Database permissions\n";
    exit(1);
}