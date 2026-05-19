<?php
/**
 * Debug Hero Section Active Status
 */

define('THUONGLO_INIT', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/database.php';

echo "<h1>Debug Hero Section Active Status</h1>";

try {
    // Initialize database
    $database = Database::getInstance();
    $connection = $database->getPdo();
    
    if (!$connection) {
        throw new Exception("Database connection failed");
    }
    
    echo "<h2>1. Check hero_sections table</h2>";
    
    // Count all hero sections
    $countAll = $connection->query("SELECT COUNT(*) as count FROM hero_sections")->fetchColumn();
    echo "<p>Total hero sections: $countAll</p>";
    
    // Count active hero sections
    $countActive = $connection->query("SELECT COUNT(*) as count FROM hero_sections WHERE is_active = 1")->fetchColumn();
    echo "<p>Active hero sections: $countActive</p>";
    
    // Count inactive hero sections  
    $countInactive = $connection->query("SELECT COUNT(*) as count FROM hero_sections WHERE is_active = 0")->fetchColumn();
    echo "<p>Inactive hero sections: $countInactive</p>";
    
    echo "<h2>2. Show all hero sections</h2>";
    
    $sections = $connection->query("SELECT id, title_main, is_active, created_at FROM hero_sections ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($sections)) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Title</th><th>is_active</th><th>Created</th></tr>";
        foreach ($sections as $section) {
            $status = $section['is_active'] ? "<span style='color: green;'>Active ({$section['is_active']})</span>" : "<span style='color: red;'>Inactive ({$section['is_active']})</span>";
            echo "<tr>
                <td>{$section['id']}</td>
                <td>" . htmlspecialchars(strip_tags($section['title_main'])) . "</td>
                <td>$status</td>
                <td>{$section['created_at']}</td>
            </tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>No hero sections found in database!</p>";
    }
    
    echo "<h2>3. Test getActive() method directly</h2>";
    
    require_once __DIR__ . '/app/models/HeroSectionModel.php';
    $heroModel = new HeroSectionModel();
    
    // Test the SQL query directly
    echo "<h3>Direct SQL query:</h3>";
    $sql = "SELECT * FROM hero_sections WHERE is_active = 1 ORDER BY id DESC LIMIT 1";
    echo "<p>SQL: <code>$sql</code></p>";
    
    $result = $connection->query($sql)->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        echo "<p style='color: green;'>✓ Direct SQL found hero section:</p>";
        echo "<table border='1' cellpadding='3'>";
        foreach ($result as $key => $value) {
            echo "<tr><td>$key</td><td>" . htmlspecialchars($value ?? 'NULL') . "</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>✗ Direct SQL found nothing</p>";
    }
    
    // Test getActive() method
    echo "<h3>getActive() method:</h3>";
    $active = $heroModel->getActive();
    if ($active) {
        echo "<p style='color: green;'>✓ getActive() found hero section</p>";
    } else {
        echo "<p style='color: red;'>✗ getActive() returned null</p>";
    }
    
    echo "<h2>4. Test getWithButtons() method</h2>";
    $withButtons = $heroModel->getWithButtons();
    if ($withButtons) {
        echo "<p style='color: green;'>✓ getWithButtons() found hero section</p>";
        echo "<p>Buttons count: " . count($withButtons['buttons'] ?? []) . "</p>";
    } else {
        echo "<p style='color: red;'>✗ getWithButtons() returned null</p>";
    }
    
    echo "<h2>5. Check if there's an issue with is_active values</h2>";
    
    // Check is_active values
    $activeValues = $connection->query("SELECT DISTINCT is_active, COUNT(*) as count FROM hero_sections GROUP BY is_active")->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>is_active value</th><th>Count</th></tr>";
    foreach ($activeValues as $row) {
        echo "<tr><td>{$row['is_active']}</td><td>{$row['count']}</td></tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<div style='background: #ffe6e6; padding: 15px; border-left: 4px solid #ff0000;'>";
    echo "<h3>Error:</h3>";
    echo "<p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>File: " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p><small>Debug completed at: " . date('Y-m-d H:i:s') . "</small></p>";
?>
