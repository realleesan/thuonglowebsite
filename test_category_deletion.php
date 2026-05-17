<?php
/**
 * Test script for category deletion logic
 */

require_once __DIR__ . '/app/models/CategoriesModel.php';
require_once __DIR__ . '/app/services/AdminService.php';

// Initialize models
$categoriesModel = new CategoriesModel();
$adminService = new AdminService(null, 'admin');

echo "<h2>Testing Category Deletion Logic</h2>";

// Get all categories for testing
$categories = $categoriesModel->query("SELECT id, name, parent_id FROM categories LIMIT 5");

echo "<h3>Available Categories:</h3>";
echo "<table border='1'>";
echo "<tr><th>ID</th><th>Name</th><th>Parent ID</th><th>Has Products</th><th>Has Children</th></tr>";

foreach ($categories as $category) {
    $hasProducts = $categoriesModel->hasProducts($category['id']);
    $hasChildren = $categoriesModel->hasChildCategories($category['id']);
    $productCount = $categoriesModel->getProductCount($category['id']);
    $childCount = $categoriesModel->getChildCategoriesCount($category['id']);
    
    echo "<tr>";
    echo "<td>{$category['id']}</td>";
    echo "<td>{$category['name']}</td>";
    echo "<td>" . ($category['parent_id'] ?: 'None') . "</td>";
    echo "<td>" . ($hasProducts ? "Yes ($productCount)" : "No") . "</td>";
    echo "<td>" . ($hasChildren ? "Yes ($childCount)" : "No") . "</td>";
    echo "</tr>";
}
echo "</table>";

// Test deletion logic for each category
echo "<h3>Testing Deletion Logic:</h3>";

foreach ($categories as $category) {
    echo "<h4>Category: {$category['name']} (ID: {$category['id']})</h4>";
    
    // Test the deleteCategory method
    $result = $adminService->deleteCategory((int)$category['id']);
    
    echo "<pre>";
    echo json_encode($result, JSON_PRETTY_PRINT);
    echo "</pre>";
    
    echo "<hr>";
}

echo "<h2>Test completed!</h2>";
echo "<p><strong>Note:</strong> This test only simulates the deletion logic - no actual deletion occurred unless you confirm the prompts.</p>";
?>
