<?php
/**
 * Test file for checking device session issues
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Device Session Debug Test</h1>";

// Test database connection and device_sessions table
try {
    require_once 'config.php';
    require_once 'core/database.php';
    
    $db = Database::getInstance();
    $pdo = $db->getPdo();
    
    echo "<h2>1. Database Connection</h2>";
    echo "<p>✅ Database connected</p>";
    
    // Check if device_sessions table exists
    echo "<h2>2. Device Sessions Table</h2>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'device_sessions'");
    if ($stmt->rowCount() > 0) {
        echo "<p>✅ device_sessions table exists</p>";
        
        // Check table structure
        $columns = $pdo->query("DESCRIBE device_sessions")->fetchAll(PDO::FETCH_ASSOC);
        echo "<p>Table structure:</p>";
        echo "<table border='1'><tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th></tr>";
        foreach ($columns as $col) {
            echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Null']}</td><td>{$col['Key']}</td></tr>";
        }
        echo "</table>";
        
        // Check if there are any records
        $count = $pdo->query("SELECT COUNT(*) as count FROM device_sessions")->fetch(PDO::FETCH_ASSOC);
        echo "<p>Total records: {$count['count']}</p>";
        
        // Show some sample records
        if ($count['count'] > 0) {
            $records = $pdo->query("SELECT * FROM device_sessions LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
            echo "<p>Sample records:</p>";
            echo "<table border='1'><tr><th>ID</th><th>User ID</th><th>Session ID</th><th>Status</th><th>Device Name</th></tr>";
            foreach ($records as $record) {
                $deviceName = isset($record['device_name']) ? $record['device_name'] : 'N/A';
                echo "<tr><td>{$record['id']}</td><td>{$record['user_id']}</td><td>{$record['session_id']}</td><td>{$record['status']}</td><td>{$deviceName}</td></tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<p>❌ device_sessions table does NOT exist</p>";
        
        // Try to create the table
        echo "<h3>Creating device_sessions table...</h3>";
        $create_sql = "
        CREATE TABLE `device_sessions` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `session_id` varchar(255) NOT NULL,
            `device_name` varchar(255) DEFAULT NULL,
            `device_type` varchar(50) DEFAULT NULL,
            `browser` varchar(100) DEFAULT NULL,
            `os` varchar(100) DEFAULT NULL,
            `ip_address` varchar(45) DEFAULT NULL,
            `location` varchar(255) DEFAULT NULL,
            `status` enum('active','pending','blocked','expired') DEFAULT 'pending',
            `is_current` tinyint(1) DEFAULT 0,
            `last_activity` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_user_session` (`user_id`,`session_id`),
            KEY `idx_user_id` (`user_id`),
            KEY `idx_session_id` (`session_id`),
            KEY `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        
        try {
            $pdo->exec($create_sql);
            echo "<p>✅ device_sessions table created successfully</p>";
        } catch (Exception $e) {
            echo "<p>❌ Failed to create table: " . $e->getMessage() . "</p>";
        }
    }
    
    // Test DeviceAccessModel
    echo "<h2>3. DeviceAccessModel Test</h2>";
    try {
        require_once 'app/models/DeviceAccessModel.php';
        $model = new DeviceAccessModel();
        echo "<p>✅ DeviceAccessModel loaded</p>";
        
        // Test getActiveDevices
        try {
            $activeDevices = $model->getActiveDevices(1); // Test with user ID 1
            echo "<p>✅ getActiveDevices() works, returned " . count($activeDevices) . " devices</p>";
        } catch (Exception $e) {
            echo "<p>❌ getActiveDevices() error: " . $e->getMessage() . "</p>";
        }
        
        // Test findByUserAndSession
        try {
            $device = $model->findByUserAndSession(1, session_id());
            echo "<p>✅ findByUserAndSession() works, " . ($device ? "found device" : "no device found") . "</p>";
        } catch (Exception $e) {
            echo "<p>❌ findByUserAndSession() error: " . $e->getMessage() . "</p>";
        }
        
    } catch (Exception $e) {
        echo "<p>❌ DeviceAccessModel error: " . $e->getMessage() . "</p>";
    }
    
    // Test DeviceAccessService
    echo "<h2>4. DeviceAccessService Test</h2>";
    try {
        require_once 'app/services/DeviceAccessService.php';
        $service = new DeviceAccessService();
        echo "<p>✅ DeviceAccessService loaded</p>";
        
        // Test checkCurrentDeviceSession
        try {
            $result = $service->checkCurrentDeviceSession(1); // Test with user ID 1
            echo "<p>✅ checkCurrentDeviceSession() works, returned: " . ($result ? "true" : "false") . "</p>";
        } catch (Exception $e) {
            echo "<p>❌ checkCurrentDeviceSession() error: " . $e->getMessage() . "</p>";
            echo "<p>Stack trace: <pre>" . $e->getTraceAsString() . "</pre></p>";
        }
        
    } catch (Exception $e) {
        echo "<p>❌ DeviceAccessService error: " . $e->getMessage() . "</p>";
    }
    
    // Test AuthService device validation
    echo "<h2>5. AuthService Device Validation Test</h2>";
    try {
        require_once 'app/services/AuthService.php';
        $auth = new AuthService();
        echo "<p>✅ AuthService loaded</p>";
        
        // Test isAuthenticated (this calls device validation)
        try {
            $isAuth = $auth->isAuthenticated();
            echo "<p>✅ isAuthenticated() works, returned: " . ($isAuth ? "true" : "false") . "</p>";
        } catch (Exception $e) {
            echo "<p>❌ isAuthenticated() error: " . $e->getMessage() . "</p>";
            echo "<p>Stack trace: <pre>" . $e->getTraceAsString() . "</pre></p>";
        }
        
    } catch (Exception $e) {
        echo "<p>❌ AuthService error: " . $e->getMessage() . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Database connection failed: " . $e->getMessage() . "</p>";
}

echo "<h2>Test Complete</h2>";
?>
