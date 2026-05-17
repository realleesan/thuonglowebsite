<?php
/**
 * Debug script for category deletion
 */

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include required files
require_once __DIR__ . '/app/services/AdminService.php';

// Test deletion
if (isset($_GET['id'])) {
    $categoryId = (int)$_GET['id'];
    
    echo "<h2>Testing Category Deletion for ID: $categoryId</h2>";
    
    try {
        $adminService = new AdminService(null, 'admin');
        
        // Test deleteCategory
        echo "<h3>Testing deleteCategory():</h3>";
        $result = $adminService->deleteCategory($categoryId);
        
        echo "<pre>";
        echo "Result: " . print_r($result, true);
        echo "</pre>";
        
        // Test forceDeleteCategory
        echo "<h3>Testing forceDeleteCategory():</h3>";
        $forceResult = $adminService->forceDeleteCategory($categoryId);
        
        echo "<pre>";
        echo "Force Result: " . print_r($forceResult, true);
        echo "</pre>";
        
    } catch (Exception $e) {
        echo "<h3>Error:</h3>";
        echo "<pre>";
        echo "Exception: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . "\n";
        echo "Line: " . $e->getLine() . "\n";
        echo "Trace:\n" . $e->getTraceAsString();
        echo "</pre>";
    }
} else {
    echo "<h2>Category Deletion Debug</h2>";
    echo "<p>Please provide an ID parameter: ?id=1</p>";
    
    // Show some categories
    try {
        $adminService = new AdminService(null, 'admin');
        $categoriesModel = $adminService->getModel('CategoriesModel');
        
        if ($categoriesModel) {
            $categories = $categoriesModel->query("SELECT id, name FROM categories LIMIT 5");
            
            echo "<h3>Available Categories:</h3>";
            echo "<ul>";
            foreach ($categories as $cat) {
                echo "<li><a href='?id={$cat['id']}'>ID: {$cat['id']} - {$cat['name']}</a></li>";
            }
            echo "</ul>";
        }
    } catch (Exception $e) {
        echo "<p>Error loading categories: " . $e->getMessage() . "</p>";
    }
}
?>
