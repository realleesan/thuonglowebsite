<?php
/**
 * Clean up filter_settings table - remove old JSON entries
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Cleanup Filter Settings</h1>";

try {
    require_once __DIR__ . '/core/database.php';
    $db = Database::getInstance();
    
    echo "<h2>1. Current filter_settings</h2>";
    $result = $db->query("SELECT setting_key, setting_value FROM filter_settings ORDER BY setting_key");
    
    echo "<table border='1'>";
    echo "<tr><th>Setting Key</th><th>Setting Value</th></tr>";
    foreach ($result as $row) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['setting_key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['setting_value']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>2. Remove old JSON entries</h2>";
    
    // Delete old JSON entries
    $db->query("DELETE FROM filter_settings WHERE setting_key IN ('criteria_order', 'criteria_enabled')");
    echo "Deleted old JSON entries<br>";
    
    echo "<h2>3. Filter settings after cleanup</h2>";
    $result = $db->query("SELECT setting_key, setting_value FROM filter_settings ORDER BY setting_key");
    
    echo "<table border='1'>";
    echo "<tr><th>Setting Key</th><th>Setting Value</th></tr>";
    foreach ($result as $row) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['setting_key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['setting_value']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>✅ Cleanup completed!</h2>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error: " . $e->getMessage() . "</h2>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
