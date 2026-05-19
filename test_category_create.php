<?php
/**
 * Test category creation
 */

// Bật error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test Category Creation</h1>";

// Load necessary files
require_once __DIR__ . '/core/view_init.php';
require_once __DIR__ . '/app/models/CategoriesModel.php';

// Test 1: Kiểm tra CategoriesModel
echo "<h2>Test 1: CategoriesModel</h2>";
try {
    $categoriesModel = new \CategoriesModel();
    echo "✅ CategoriesModel created successfully<br>";
} catch (Exception $e) {
    echo "❌ Error creating CategoriesModel: " . $e->getMessage() . "<br>";
    exit;
}

// Test 2: Kiểm tra AdminService
echo "<h2>Test 2: AdminService</h2>";
try {
    global $adminService;
    if ($adminService) {
        echo "✅ AdminService available<br>";
    } else {
        echo "❌ AdminService not available<br>";
        exit;
    }
} catch (Exception $e) {
    echo "❌ Error with AdminService: " . $e->getMessage() . "<br>";
    exit;
}

// Test 3: Kiểm tra tạo category trực tiếp qua model
echo "<h2>Test 3: Direct Category Creation via Model</h2>";
try {
    $testData = [
        'name' => 'Test Category ' . date('Y-m-d H:i:s'),
        'slug' => 'test-category-' . time(),
        'type' => 'news',
        'status' => 'active',
        'description' => 'Test category created via direct model'
    ];
    
    echo "Test data: <pre>" . print_r($testData, true) . "</pre>";
    
    $categoryId = $categoriesModel->create($testData);
    
    if ($categoryId) {
        echo "✅ Category created successfully with ID: " . $categoryId . "<br>";
        
        // Verify it was saved
        $savedCategory = $categoriesModel->find($categoryId);
        if ($savedCategory) {
            echo "✅ Category verified in database:<br>";
            echo "<pre>" . print_r($savedCategory, true) . "</pre>";
            
            // Check if type field was saved
            if (isset($savedCategory['type']) && $savedCategory['type'] === 'news') {
                echo "✅ Type field saved correctly: " . $savedCategory['type'] . "<br>";
            } else {
                echo "❌ Type field not saved correctly<br>";
                echo "Type field value: " . (isset($savedCategory['type']) ? $savedCategory['type'] : 'NOT SET') . "<br>";
            }
        } else {
            echo "❌ Category not found in database after creation<br>";
        }
    } else {
        echo "❌ Failed to create category<br>";
    }
} catch (Exception $e) {
    echo "❌ Error creating category: " . $e->getMessage() . "<br>";
    echo "Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
}

// Test 4: Kiểm tra tạo category qua AdminService
echo "<h2>Test 4: Category Creation via AdminService</h2>";
try {
    $testData2 = [
        'name' => 'Test Service Category ' . date('Y-m-d H:i:s'),
        'slug' => 'test-service-category-' . time(),
        'type' => 'news',
        'status' => 'active',
        'description' => 'Test category created via AdminService'
    ];
    
    echo "Test data: <pre>" . print_r($testData2, true) . "</pre>";
    
    $categoryId2 = $adminService->createCategory($testData2);
    
    if ($categoryId2) {
        echo "✅ Category created via AdminService with ID: " . $categoryId2 . "<br>";
        
        // Verify it was saved
        $savedCategory2 = $categoriesModel->find($categoryId2);
        if ($savedCategory2) {
            echo "✅ Category verified in database:<br>";
            echo "<pre>" . print_r($savedCategory2, true) . "</pre>";
        } else {
            echo "❌ Category not found in database after creation<br>";
        }
    } else {
        echo "❌ Failed to create category via AdminService<br>";
    }
} catch (Exception $e) {
    echo "❌ Error creating category via AdminService: " . $e->getMessage() . "<br>";
    echo "Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
}

// Test 5: Kiểm tra query categories với type = 'news'
echo "<h2>Test 5: Query Categories with type='news'</h2>";
try {
    $newsCategories = $categoriesModel->query("SELECT * FROM categories WHERE type = 'news' AND status = 'active' ORDER BY id DESC LIMIT 5") ?? [];
    echo "Found " . count($newsCategories) . " news categories:<br>";
    
    foreach ($newsCategories as $category) {
        echo "- ID: " . $category['id'] . ", Name: " . $category['name'] . ", Type: " . ($category['type'] ?? 'NULL') . "<br>";
    }
} catch (Exception $e) {
    echo "❌ Error querying news categories: " . $e->getMessage() . "<br>";
}

echo "<h2>Test Complete</h2>";
?>
