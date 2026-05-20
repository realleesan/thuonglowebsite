<?php
// Test file to debug home page sections issue
define('ROOT_PATH', __DIR__);

// Load the framework like index.php does
require_once 'config.php';
require_once 'core/view_init.php';

echo "<h1>DEBUG: Home Page Sections</h1>";

try {
    // 1. Check if PublicService is available
    if (!isset($publicService)) {
        echo "<p style='color: red;'>❌ PublicService not found</p>";
        exit;
    } else {
        echo "<p style='color: green;'>✅ PublicService found</p>";
    }

    // 2. Test PublicService getHomeData
    echo "<h2>🔧 PublicService Test</h2>";
    
    // Check if getHomeData method exists
    if (method_exists($publicService, 'getHomeData')) {
        $homeData = $publicService->getHomeData();
        
        echo "<h3>Home Data Keys:</h3>";
        echo "<ul>";
        foreach ($homeData as $key => $value) {
            if (is_array($value)) {
                echo "<li><strong>$key</strong>: " . count($value) . " items</li>";
            } else {
                echo "<li><strong>$key</strong>: " . (is_scalar($value) ? $value : gettype($value)) . "</li>";
            }
        }
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>❌ getHomeData method not found in PublicService</p>";
        $homeData = [];
    }

    // 3. Check specific product arrays
    echo "<h2>📦 Product Arrays</h2>";
    
    $productTypes = ['featuredProducts', 'latestProducts', 'budgetProducts', 'saleProducts'];
    foreach ($productTypes as $type) {
        $products = $homeData[$type] ?? [];
        echo "<h3>$type (" . count($products) . " items)</h3>";
        
        if (!empty($products)) {
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>ID</th><th>Name</th><th>Price</th><th>Sale Price</th><th>Category</th></tr>";
            $count = 0;
            foreach ($products as $product) {
                if ($count >= 3) break; // Show only first 3 items
                echo "<tr>";
                echo "<td>{$product['id']}</td>";
                echo "<td>" . htmlspecialchars($product['name']) . "</td>";
                echo "<td>{$product['formatted_price']}</td>";
                echo "<td>" . ($product['formatted_sale_price'] ?? 'N/A') . "</td>";
                echo "<td>" . ($product['category_name'] ?? 'N/A') . "</td>";
                echo "</tr>";
                $count++;
            }
            if (count($products) > 3) {
                echo "<tr><td colspan='5'>... and " . (count($products) - 3) . " more items</td></tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: red;'>❌ Empty</p>";
        }
    }

    // 4. Test section visibility logic (simulate home view logic)
    echo "<h2>👁️ Section Visibility Logic</h2>";
    
    // Get section settings from homeData (if available)
    $latestProductsSection = $homeData['latestProductsSection'] ?? ['is_active' => true];
    $budgetProductsSection = $homeData['budgetProductsSection'] ?? ['is_active' => true];
    $saleProductsSection = $homeData['saleProductsSection'] ?? ['is_active' => true];
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Section</th><th>Is Active</th><th>Has Data</th><th>Will Show</th></tr>";
    
    $sectionChecks = [
        'latest_products' => ['setting' => $latestProductsSection, 'data' => 'latestProducts'],
        'budget_products' => ['setting' => $budgetProductsSection, 'data' => 'budgetProducts'],
        'sale_products' => ['setting' => $saleProductsSection, 'data' => 'saleProducts']
    ];
    
    foreach ($sectionChecks as $sectionName => $check) {
        $isActive = $check['setting']['is_active'] ?? true;
        $hasData = !empty($homeData[$check['data']]);
        $willShow = $isActive && $hasData;
        
        $color = $willShow ? 'green' : 'red';
        echo "<tr style='color: $color;'>";
        echo "<td>$sectionName</td>";
        echo "<td>" . ($isActive ? '✅ Yes' : '❌ No') . "</td>";
        echo "<td>" . ($hasData ? '✅ Yes' : '❌ No') . "</td>";
        echo "<td>" . ($willShow ? '✅ Yes' : '❌ No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    // 5. Test individual model methods through service
    echo "<h2>🔍 ProductsModel Methods Test</h2>";
    
    $methods = [
        'getLatest' => 'Latest Products',
        'getBudget' => 'Budget Products', 
        'getSale' => 'Sale Products'
    ];
    
    foreach ($methods as $method => $label) {
        echo "<h3>$label (Service Method: $method)</h3>";
        try {
            // Try to get model from service
            $productsModel = $publicService->getModel('ProductsModel');
            if ($productsModel && method_exists($productsModel, $method)) {
                $result = $productsModel->$method(8);
                echo "<p style='color: green;'>✅ Method returned " . count($result) . " items</p>";
                if (!empty($result)) {
                    $first = $result[0];
                    echo "<p>First item: " . htmlspecialchars($first['name']) . " - {$first['formatted_price']}</p>";
                }
            } else {
                echo "<p style='color: red;'>❌ Method $method not found in ProductsModel</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
        }
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ ERROR: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>Test completed!</strong></p>";
echo "<p><a href='?'>← Back to Home</a></p>";
?>
