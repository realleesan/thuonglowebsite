<?php
/**
 * Ultra Simple Migration Script
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Ultra Simple Migration</h2>";

try {
    // Load config
    require_once __DIR__ . '/config.php';
    
    // Connect to database
    $dsn = "mysql:host={$config['database']['host']};dbname={$config['database']['name']};charset={$config['database']['charset']}";
    $pdo = new PDO($dsn, $config['database']['username'], $config['database']['password'], $config['database']['options']);
    
    echo "<p>✓ Database connected</p>";
    
    // Step 1: Create migrations table manually
    echo "<p>Step 1: Creating migrations table...</p>";
    $sql = "CREATE TABLE IF NOT EXISTS migrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(255) NOT NULL UNIQUE,
        batch INT NOT NULL DEFAULT 1,
        executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "<p>✓ Migrations table created</p>";
    
    // Step 2: Record the migrations table creation
    echo "<p>Step 2: Recording migrations table creation...</p>";
    $stmt = $pdo->prepare("INSERT IGNORE INTO migrations (migration, batch) VALUES (?, ?)");
    $stmt->execute(['000_create_migrations_table.sql', 1]);
    echo "<p>✓ Migration recorded</p>";
    
    // Step 3: Run other migrations
    echo "<p>Step 3: Running other migrations...</p>";
    $migrationsDir = __DIR__ . '/database/migrations';
    $files = glob($migrationsDir . '/*.sql');
    sort($files);
    
    $executed = 0;
    foreach ($files as $file) {
        $filename = basename($file);
        
        // Skip the migrations table file (already done)
        if ($filename === '000_create_migrations_table.sql') {
            echo "<p>- Skipped: $filename (already executed)</p>";
            continue;
        }
        
        // Check if already executed
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM migrations WHERE migration = ?");
        $stmt->execute([$filename]);
        
        if ($stmt->fetchColumn() == 0) {
            echo "<p>🔄 Executing: $filename</p>";
            
            // Read and execute migration
            $sql = file_get_contents($file);
            
            // Split by semicolons and execute each statement
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            
            foreach ($statements as $statement) {
                if (!empty($statement) && !preg_match('/^\s*--/', $statement)) {
                    $pdo->exec($statement);
                }
            }
            
            // Record execution
            $stmt = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
            $stmt->execute([$filename, 1]);
            
            echo "<p style='color: green;'>✓ Completed: $filename</p>";
            $executed++;
        } else {
            echo "<p>- Skipped: $filename (already executed)</p>";
        }
    }
    
    echo "<h3 style='color: green;'>Migration completed successfully!</h3>";
    echo "<p>Total new migrations executed: $executed</p>";
    
    // Show tables
    echo "<h3>Database Tables:</h3>";
    echo "<ul>";
    $tables = $pdo->query("SHOW TABLES");
    foreach ($tables as $table) {
        $tableName = array_values($table)[0];
        echo "<li>$tableName</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>Error:</h3>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
}
?>