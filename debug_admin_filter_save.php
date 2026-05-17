<?php
/**
 * Debug Admin Filter Save Process
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Admin Filter Save Debug</h1>";

echo "<h2>1. Current Filter Settings in Database</h2>";
try {
    require_once __DIR__ . '/core/database.php';
    $db = Database::getInstance();
    
    $result = $db->query("SELECT setting_key, setting_value FROM filter_settings WHERE setting_key LIKE 'criteria_%' ORDER BY setting_key");
    
    echo "<table border='1'>";
    echo "<tr><th>Setting Key</th><th>Setting Value</th></tr>";
    foreach ($result as $row) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['setting_key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['setting_value']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

echo "<h2>2. Test FilterConfigService Save</h2>";

try {
    require_once __DIR__ . '/app/services/FilterConfigService.php';
    $filterService = new FilterConfigService();
    
    // Simulate the exact data structure from frontend
    $testConfig = [
        'criteria' => [
            ['name' => 'categories', 'order' => 1, 'enabled' => true],
            ['name' => 'brands', 'order' => 2, 'enabled' => true],
            ['name' => 'price_ranges', 'order' => 3, 'enabled' => true]
        ],
        'items' => [
            'categories' => [
                ['id' => 1, 'enabled' => true, 'order' => 1, 'parent_id' => 0],
                ['id' => 14, 'enabled' => true, 'order' => 2, 'parent_id' => 0]
            ]
        ]
    ];
    
    echo "Test config structure:<br>";
    echo "<pre>" . print_r($testConfig, true) . "</pre>";
    
    echo "<h3>Calling saveFilterConfig...</h3>";
    $result = $filterService->saveFilterConfig($testConfig);
    
    echo "Save result:<br>";
    echo "<pre>" . print_r($result, true) . "</pre>";
    
    if ($result['success']) {
        echo "✅ Save successful!<br>";
    } else {
        echo "❌ Save failed: " . $result['message'] . "<br>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h2>3. Check Filter Settings After Save</h2>";

try {
    $db = Database::getInstance();
    
    $result = $db->query("SELECT setting_key, setting_value FROM filter_settings WHERE setting_key LIKE 'criteria_%' ORDER BY setting_key");
    
    echo "<table border='1'>";
    echo "<tr><th>Setting Key</th><th>Setting Value</th></tr>";
    foreach ($result as $row) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['setting_key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['setting_value']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

echo "<h2>4. Test getFilterConfig After Save</h2>";

try {
    require_once __DIR__ . '/app/services/FilterConfigService.php';
    $filterService = new FilterConfigService();
    
    $config = $filterService->getFilterConfig();
    
    echo "getFilterConfig result:<br>";
    echo "<pre>" . print_r($config, true) . "</pre>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

echo "<h2>5. Instructions for Manual Testing</h2>";
echo "<ol>";
echo "<li>Open browser developer tools (F12)</li>";
echo "<li>Go to Network tab</li>";
echo "<li>Go to admin filter config page</li>";
echo "<li>Drag and drop to reorder criteria</li>";
echo "<li>Click 'Lưu Cấu Hình'</li>";
echo "<li>Check the Network tab for the api.php?action=saveFilterConfig request</li>";
echo "<li>Click on the request and check:</li>";
echo "<ul>";
echo "<li>Request payload (what data was sent)</li>";
echo "<li>Response (what server returned)</li>";
echo "</ul>";
echo "</ol>";
?>
