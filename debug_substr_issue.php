<?php
/**
 * Debug substr issue in getCriteriaSettings
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debug Substr Issue</h1>";

// Test substr calculations
$testKeys = [
    'criteria_order_brands',
    'criteria_enabled_brands',
    'criteria_order_categories',
    'criteria_enabled_categories',
    'criteria_order_price_ranges',
    'criteria_enabled_price_ranges'
];

echo "<h2>Testing substr calculations</h2>";
echo "<table border='1'>";
echo "<tr><th>Original Key</th><th>Expected Result</th><th>substr(17)</th><th>substr(19)</th></tr>";

foreach ($testKeys as $key) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($key) . "</td>";
    
    // Expected result
    $expected = '';
    if (strpos($key, 'criteria_order_') === 0) {
        $expected = substr($key, 17);
    } elseif (strpos($key, 'criteria_enabled_') === 0) {
        $expected = substr($key, 19);
    }
    echo "<td>" . htmlspecialchars($expected) . "</td>";
    
    // Test substr(17)
    $substr17 = substr($key, 17);
    echo "<td>" . htmlspecialchars($substr17) . "</td>";
    
    // Test substr(19)
    $substr19 = substr($key, 19);
    echo "<td>" . htmlspecialchars($substr19) . "</td>";
    
    echo "</tr>";
}
echo "</table>";

echo "<h2>Manual test of getCriteriaSettings logic</h2>";

try {
    require_once __DIR__ . '/core/database.php';
    $db = Database::getInstance();
    
    $result = $db->query("SELECT setting_key, setting_value FROM filter_settings WHERE setting_key LIKE 'criteria_%' ORDER BY setting_key");
    
    $criteria = [];
    
    foreach ($result as $row) {
        $key = $row['setting_key'];
        $value = $row['setting_value'];
        
        echo "<h3>Processing: $key = $value</h3>";
        
        if (strpos($key, 'criteria_order_') === 0) {
            $criteriaName = substr($key, 15); // Fixed: 15 chars
            echo "  - criteria_order_ detected, criteriaName: '$criteriaName'<br>";
            
            if (!isset($criteria[$criteriaName])) {
                $criteria[$criteriaName] = [];
            }
            $criteria[$criteriaName]['order'] = (int)$value;
            
        } elseif (strpos($key, 'criteria_enabled_') === 0) {
            $criteriaName = substr($key, 17); // Fixed: 17 chars
            echo "  - criteria_enabled_ detected, criteriaName: '$criteriaName'<br>";
            
            if (!isset($criteria[$criteriaName])) {
                $criteria[$criteriaName] = [];
            }
            $criteria[$criteriaName]['enabled'] = (bool)$value;
        }
    }
    
    echo "<h2>Final criteria array:</h2>";
    echo "<pre>" . print_r($criteria, true) . "</pre>";
    
} catch (Exception $e) {
    echo "<h2>Error: " . $e->getMessage() . "</h2>";
}
?>
