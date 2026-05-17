<?php
/**
 * Debug Items Save Process
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debug Items Save Process</h1>";

echo "<h2>1. Simulate Frontend Data Structure</h2>";

// Simulate exact data structure from frontend after drag & drop
$testConfig = [
    'criteria' => [
        ['name' => 'categories', 'order' => 1, 'enabled' => true],
        ['name' => 'brands', 'order' => 2, 'enabled' => true],
        ['name' => 'price_ranges', 'order' => 3, 'enabled' => true]
    ],
    'items' => [
        'categories' => [
            ['id' => 25, 'parent_id' => 0, 'order' => 1, 'enabled' => true], // GIÀY DÉP NỮ
            ['id' => 1, 'parent_id' => 0, 'order' => 2, 'enabled' => true],  // THỜI TRANG NAM
            ['id' => 14, 'parent_id' => 0, 'order' => 3, 'enabled' => true], // THỜI TRANG NỮ
            ['id' => 39, 'parent_id' => 0, 'order' => 4, 'enabled' => true], // THỜI TRANG TRẺ EM
            ['id' => 26, 'parent_id' => 0, 'order' => 5, 'enabled' => true]  // PHỤ KIỆN NAM
        ],
        'brands' => [
            ['id' => 1, 'parent_id' => 0, 'order' => 1, 'enabled' => true],
            ['id' => 19, 'parent_id' => 0, 'order' => 2, 'enabled' => true],
            ['id' => 20, 'parent_id' => 0, 'order' => 3, 'enabled' => true]
        ]
    ]
];

echo "<h3>Test config structure:</h3>";
echo "<pre>" . print_r($testConfig, true) . "</pre>";

echo "<h2>2. Test saveFilterConfig with items</h2>";

try {
    require_once __DIR__ . '/app/services/FilterConfigService.php';
    $filterService = new FilterConfigService();
    
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

echo "<h2>3. Check filter_config table after save</h2>";

try {
    require_once __DIR__ . '/core/database.php';
    $db = Database::getInstance();
    
    $result = $db->query("SELECT criteria_type, item_id, parent_id, sort_order, is_enabled 
                          FROM filter_config 
                          ORDER BY criteria_type, sort_order");
    
    echo "<table border='1'>";
    echo "<tr><th>Criteria Type</th><th>Item ID</th><th>Parent ID</th><th>Sort Order</th><th>Enabled</th></tr>";
    foreach ($result as $row) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['criteria_type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['item_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['parent_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['sort_order']) . "</td>";
        echo "<td>" . htmlspecialchars($row['is_enabled']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

echo "<h2>4. Test getFilterConfig to verify items are loaded</h2>";

try {
    require_once __DIR__ . '/app/services/FilterConfigService.php';
    $filterService = new FilterConfigService();
    
    $config = $filterService->getFilterConfig();
    
    echo "Categories items count: " . count($config['data']['items']['categories'] ?? []) . "<br>";
    echo "Brands items count: " . count($config['data']['items']['brands'] ?? []) . "<br>";
    
    echo "<h3>Categories items:</h3>";
    echo "<pre>" . print_r($config['data']['items']['categories'] ?? [], true) . "</pre>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

echo "<h2>5. Instructions for Manual Testing</h2>";
echo "<ol>";
echo "<li>Open browser developer tools (F12)</li>";
echo "<li>Go to Network tab</li>";
echo "<li>Go to admin filter config page</li>";
echo "<li>Drag and drop sub-items within categories or brands</li>";
echo "<li>Click 'Lưu Cấu Hình'</li>";
echo "<li>Check the Network tab for the api.php?action=saveFilterConfig request</li>";
echo "<li>Click on the request and check Request Payload - should have items data</li>";
echo "</ol>";
?>
