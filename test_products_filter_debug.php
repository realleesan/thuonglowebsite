<?php
/**
 * Debug file for products page filter configuration
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Products Filter Debug</h1>";

try {
    // Test 1: Check FilterConfigService
    echo "<h2>1. Testing FilterConfigService</h2>";
    
    require_once __DIR__ . '/app/services/FilterConfigService.php';
    $filterService = new FilterConfigService();
    echo "✅ FilterConfigService loaded successfully<br>";
    
    // Test getFilterConfig
    echo "<h3>getFilterConfig()</h3>";
    $config_result = $filterService->getFilterConfig();
    if ($config_result['success']) {
        echo "✅ getFilterConfig success<br>";
        echo "<pre>" . print_r($config_result['data'], true) . "</pre>";
    } else {
        echo "❌ getFilterConfig failed: " . $config_result['message'] . "<br>";
    }
    
    // Test getCategoriesForFilter
    echo "<h3>getCategoriesForFilter()</h3>";
    $categories = $filterService->getCategoriesForFilter();
    echo "✅ Categories loaded: " . count($categories) . " items<br>";
    if (!empty($categories)) {
        echo "<pre>" . print_r($categories[0], true) . "</pre>";
    }
    
    // Test getBrandsForFilter
    echo "<h3>getBrandsForFilter()</h3>";
    $brands = $filterService->getBrandsForFilter();
    echo "✅ Brands loaded: " . count($brands) . " items<br>";
    if (!empty($brands)) {
        echo "<pre>" . print_r($brands[0], true) . "</pre>";
    }
    
    // Test getPriceRangesForFilter
    echo "<h3>getPriceRangesForFilter()</h3>";
    $price_ranges = $filterService->getPriceRangesForFilter();
    echo "✅ Price ranges loaded: " . count($price_ranges) . " items<br>";
    if (!empty($price_ranges)) {
        echo "<pre>" . print_r($price_ranges[0], true) . "</pre>";
    }
    
    // Test 2: Check database connection
    echo "<h2>2. Testing Database Connection</h2>";
    require_once __DIR__ . '/core/database.php';
    $db = Database::getInstance();
    echo "✅ Database connection established<br>";
    
    // Test filter_config table
    echo "<h3>filter_config table</h3>";
    $result = $db->query("SELECT COUNT(*) as count FROM filter_config");
    echo "✅ filter_config has " . $result[0]['count'] . " records<br>";
    
    // Test filter_settings table
    echo "<h3>filter_settings table</h3>";
    $result = $db->query("SELECT COUNT(*) as count FROM filter_settings");
    echo "✅ filter_settings has " . $result[0]['count'] . " records<br>";
    
    // Test 3: Check required files
    echo "<h2>3. Checking Required Files</h2>";
    $required_files = [
        '/core/view_init.php',
        '/app/services/FilterConfigService.php',
        '/app/models/BaseModel.php'
    ];
    
    foreach ($required_files as $file) {
        if (file_exists(__DIR__ . $file)) {
            echo "✅ $file exists<br>";
        } else {
            echo "❌ $file missing<br>";
        }
    }
    
    // Test 4: Simulate products page initialization
    echo "<h2>4. Simulating Products Page Initialization</h2>";
    
    // Test view_init
    echo "<h3>Loading view_init.php</h3>";
    try {
        require_once __DIR__ . '/core/view_init.php';
        echo "✅ view_init.php loaded successfully<br>";
    } catch (Exception $e) {
        echo "❌ view_init.php error: " . $e->getMessage() . "<br>";
    }
    
    // Test service loading
    echo "<h3>Service loading</h3>";
    $service = null;
    if (isset($publicService)) {
        $service = $publicService;
        echo "✅ Using injected publicService<br>";
    } else {
        echo "⚠️ No service injected, will use fallback<br>";
    }
    
    echo "<h2>✅ Debug completed successfully!</h2>";
    
} catch (Exception $e) {
    echo "<h2>❌ ERROR: " . $e->getMessage() . "</h2>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} catch (Error $e) {
    echo "<h2>❌ FATAL ERROR: " . $e->getMessage() . "</h2>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<h3>PHP Info</h3>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "Memory Limit: " . ini_get('memory_limit') . "<br>";
echo "Max Execution Time: " . ini_get('max_execution_time') . "<br>";
echo "Error Reporting: " . error_reporting() . "<br>";
echo "Display Errors: " . (ini_get('display_errors') ? 'On' : 'Off') . "<br>";
?>
