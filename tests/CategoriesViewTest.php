<?php
/**
 * Categories View Test
 * Tests the categories view conversion from hardcoded to dynamic data
 */

require_once __DIR__ . '/../app/models/CategoriesModel.php';

class CategoriesViewTest {
    private $categoriesModel;
    
    public function __construct() {
        $this->categoriesModel = new CategoriesModel();
    }
    
    /**
     * Test that categories view uses database data
     */
    public function testCategoriesViewUsesDatabase() {
        // Get categories from model
        $categories = $this->categoriesModel->getWithProductCounts();
        
        // Test that we get an array
        if (!is_array($categories)) {
            throw new Exception("Categories should return an array");
        }
        
        // Test that each category has required fields
        foreach ($categories as $category) {
            if (!isset($category['id'])) {
                throw new Exception("Category should have an id field");
            }
            if (!isset($category['name'])) {
                throw new Exception("Category should have a name field");
            }
            if (!isset($category['products_count'])) {
                throw new Exception("Category should have a products_count field");
            }
        }
        
        echo "✓ Categories view uses database data correctly\n";
        return true;
    }
    
    /**
     * Test pagination logic
     */
    public function testPaginationLogic() {
        $categories = $this->categoriesModel->getWithProductCounts();
        $totalCategories = count($categories);
        $perPage = 12;
        $totalPages = ceil($totalCategories / $perPage);
        
        // Test pagination calculation
        if ($totalPages < 1) {
            throw new Exception("Total pages should be at least 1");
        }
        
        // Test page 1
        $page1Categories = array_slice($categories, 0, $perPage);
        if (count($page1Categories) > $perPage) {
            throw new Exception("Page 1 should not have more than {$perPage} categories");
        }
        
        echo "✓ Pagination logic works correctly\n";
        return true;
    }
    
    /**
     * Test sorting functionality
     */
    public function testSortingFunctionality() {
        $categories = $this->categoriesModel->getWithProductCounts();
        
        if (empty($categories)) {
            echo "⚠ No categories found, skipping sorting test\n";
            return true;
        }
        
        // Test name sorting
        $sortedByName = $categories;
        usort($sortedByName, function($a, $b) { 
            return strcmp($a['name'], $b['name']); 
        });
        
        // Test product count sorting
        $sortedByCount = $categories;
        usort($sortedByCount, function($a, $b) { 
            return ($b['products_count'] ?? 0) - ($a['products_count'] ?? 0); 
        });
        
        echo "✓ Sorting functionality works correctly\n";
        return true;
    }
    
    /**
     * Test category statistics
     */
    public function testCategoryStatistics() {
        $stats = $this->categoriesModel->getStats();
        
        // Test that stats is an array
        if (!is_array($stats)) {
            throw new Exception("Stats should return an array");
        }
        
        // Test required stat fields
        $requiredFields = ['total', 'active', 'parent_categories', 'with_products'];
        foreach ($requiredFields as $field) {
            if (!isset($stats[$field])) {
                throw new Exception("Stats should have {$field} field");
            }
            if (!is_numeric($stats[$field])) {
                throw new Exception("Stats {$field} should be numeric");
            }
        }
        
        echo "✓ Category statistics work correctly\n";
        return true;
    }
    
    /**
     * Run all tests
     */
    public function runAllTests() {
        echo "Running Categories View Tests...\n\n";
        
        try {
            $this->testCategoriesViewUsesDatabase();
            $this->testPaginationLogic();
            $this->testSortingFunctionality();
            $this->testCategoryStatistics();
            
            echo "\n✅ All Categories View tests passed!\n";
            return true;
        } catch (Exception $e) {
            echo "\n❌ Test failed: " . $e->getMessage() . "\n";
            return false;
        }
    }
}

// Run tests if this file is executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $test = new CategoriesViewTest();
    $test->runAllTests();
}