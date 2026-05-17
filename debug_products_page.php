<?php
/**
 * Debug Products Page Step by Step
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', '512M');

echo "<h1>Products Page Debug</h1>";

$step = 1;

function debug_step($description, $function = null) {
    global $step;
    echo "<h2>Step $step: $description</h2>";
    
    try {
        if ($function) {
            $result = $function();
            echo "✅ Success<br>";
            if ($result !== null) {
                echo "<pre>" . print_r($result, true) . "</pre>";
            }
        } else {
            echo "✅ Completed<br>";
        }
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "<br>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
        die("Debug stopped at step $step");
    } catch (Error $e) {
        echo "❌ Fatal Error: " . $e->getMessage() . "<br>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
        die("Debug stopped at step $step");
    }
    
    $step++;
    echo "<hr>";
}

// Step 1: Basic PHP setup
debug_step("Basic PHP Setup", function() {
    return [
        'php_version' => PHP_VERSION,
        'memory_limit' => ini_get('memory_limit'),
        'max_execution_time' => ini_get('max_execution_time')
    ];
});

// Step 2: Check files exist
debug_step("Check Required Files", function() {
    $files = [
        '/core/view_init.php',
        '/app/services/FilterConfigService.php',
        '/app/views/products/products.php'
    ];
    
    $result = [];
    foreach ($files as $file) {
        $result[$file] = file_exists(__DIR__ . $file);
    }
    return $result;
});

// Step 3: Load view_init.php
debug_step("Load view_init.php", function() {
    require_once __DIR__ . '/core/view_init.php';
    return "view_init.php loaded";
});

// Step 4: Check service injection
debug_step("Check Service Injection", function() {
    global $publicService, $currentService;
    return [
        'publicService_exists' => isset($publicService),
        'currentService_exists' => isset($currentService),
        'publicService_type' => $publicService ? get_class($publicService) : 'null',
        'currentService_type' => $currentService ? get_class($currentService) : 'null'
    ];
});

// Step 5: Load FilterConfigService
debug_step("Load FilterConfigService", function() {
    require_once __DIR__ . '/app/services/FilterConfigService.php';
    $filterService = new FilterConfigService();
    return "FilterConfigService loaded";
});

// Step 6: Test FilterConfigService methods
debug_step("Test FilterConfigService Methods", function() {
    $filterService = new FilterConfigService();
    
    $config = $filterService->getFilterConfig();
    $categories = $filterService->getCategoriesForFilter();
    $brands = $filterService->getBrandsForFilter();
    $price_ranges = $filterService->getPriceRangesForFilter();
    
    return [
        'config_success' => $config['success'] ?? false,
        'categories_count' => count($categories),
        'brands_count' => count($brands),
        'price_ranges_count' => count($price_ranges)
    ];
});

// Step 7: Test GET parameters
debug_step("Test GET Parameters", function() {
    return [
        'page' => $_GET['page'] ?? 'not_set',
        'category' => $_GET['category'] ?? 'not_set',
        'brand' => $_GET['brand'] ?? 'not_set',
        'min_price' => $_GET['min_price'] ?? 'not_set',
        'max_price' => $_GET['max_price'] ?? 'not_set',
        'order_by' => $_GET['order_by'] ?? 'not_set',
        'search' => $_GET['search'] ?? 'not_set'
    ];
});

// Step 8: Test parameter processing
debug_step("Test Parameter Processing", function() {
    $page = isset($_GET['p']) ? (int) $_GET['p'] : 1;
    $page = max(1, $page);
    
    $categoryId = $_GET['category'] ?? [];
    if (!is_array($categoryId)) {
        if ($categoryId === '' || $categoryId === 'null' || $categoryId === null) {
            $categoryId = [];
        } else {
            $categoryId = [(int) $categoryId];
        }
    } else {
        $categoryId = array_map('intval', array_filter($categoryId));
    }
    
    $brandId = $_GET['brand'] ?? [];
    if (!is_array($brandId)) {
        if ($brandId === '' || $brandId === 'null' || $brandId === null) {
            $brandId = [];
        } else {
            $brandId = [(int) $brandId];
        }
    } else {
        $brandId = array_map('intval', array_filter($brandId));
    }
    
    return [
        'page' => $page,
        'category_id' => $categoryId,
        'brand_id' => $brandId
    ];
});

// Step 9: Test service selection
debug_step("Test Service Selection", function() {
    $service = isset($currentService) ? $currentService : ($publicService ?? null);
    return [
        'service_selected' => $service ? get_class($service) : 'null',
        'service_methods' => $service ? get_class_methods($service) : []
    ];
});

// Step 10: Test filter data loading
debug_step("Test Filter Data Loading", function() {
    $service = isset($currentService) ? $currentService : ($publicService ?? null);
    
    require_once __DIR__ . '/app/services/FilterConfigService.php';
    $filterService = new FilterConfigService();
    
    // Get filter data with saved configuration
    $categories = $filterService->getCategoriesForFilter();
    $brands = $filterService->getBrandsForFilter();
    $price_ranges = $filterService->getPriceRangesForFilter();
    
    // Get criteria order and enabled status
    $config_result = $filterService->getFilterConfig();
    $filter_config = $config_result['success'] ? $config_result['data'] : [];
    
    // Get criteria order from filter config for sidebar display
    $criteria_order = [
        'categories' => $filter_config['criteria']['categories']['order'] ?? 1,
        'brands' => $filter_config['criteria']['brands']['order'] ?? 2, 
        'price_ranges' => $filter_config['criteria']['price_ranges']['order'] ?? 3
    ];
    
    // Get criteria enabled status
    $criteria_enabled = [
        'categories' => $filter_config['criteria']['categories']['enabled'] ?? true,
        'brands' => $filter_config['criteria']['brands']['enabled'] ?? true,
        'price_ranges' => $filter_config['criteria']['price_ranges']['enabled'] ?? true
    ];
    
    return [
        'categories_count' => count($categories),
        'brands_count' => count($brands),
        'price_ranges_count' => count($price_ranges),
        'filter_config_loaded' => !empty($filter_config),
        'criteria_order' => $criteria_order,
        'criteria_enabled' => $criteria_enabled
    ];
});

echo "<h2>✅ All Steps Completed Successfully!</h2>";
echo "<p>If this debug works, the issue might be in the products.php template rendering.</p>";
?>
