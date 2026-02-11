<?php
/**
 * Categories ViewDataService Integration Test
 * Tests that categories.php properly uses ViewDataService
 */

class CategoriesViewDataServiceTest {
    
    /**
     * Test that categories.php uses ViewDataService
     */
    public function testCategoriesUsesViewDataService() {
        $categoriesPath = __DIR__ . '/../app/views/categories/categories.php';
        
        if (!file_exists($categoriesPath)) {
            throw new Exception("Categories view file not found");
        }
        
        $content = file_get_contents($categoriesPath);
        
        // Check for ViewDataService usage
        if (strpos($content, 'ViewDataService') === false) {
            throw new Exception("Categories view should use ViewDataService");
        }
        
        // Check for service instantiation
        if (strpos($content, 'new ViewDataService()') === false) {
            throw new Exception("Categories view should instantiate ViewDataService");
        }
        
        // Check for getCategoriesPageData method call
        if (strpos($content, 'getCategoriesPageData') === false) {
            throw new Exception("Categories view should call getCategoriesPageData method");
        }
        
        echo "✓ Categories view uses ViewDataService correctly\n";
        return true;
    }
    
    /**
     * Test that ViewDataService has getCategoriesPageData method
     */
    public function testViewDataServiceHasCategoriesMethod() {
        $servicePath = __DIR__ . '/../app/services/ViewDataService.php';
        
        if (!file_exists($servicePath)) {
            throw new Exception("ViewDataService file not found");
        }
        
        $content = file_get_contents($servicePath);
        
        // Check for getCategoriesPageData method
        if (strpos($content, 'function getCategoriesPageData') === false) {
            throw new Exception("ViewDataService should have getCategoriesPageData method");
        }
        
        // Check for sortCategories method
        if (strpos($content, 'function sortCategories') === false) {
            throw new Exception("ViewDataService should have sortCategories method");
        }
        
        echo "✓ ViewDataService has required categories methods\n";
        return true;
    }
    
    /**
     * Test error handling in categories view
     */
    public function testCategoriesErrorHandling() {
        $categoriesPath = __DIR__ . '/../app/views/categories/categories.php';
        $content = file_get_contents($categoriesPath);
        
        // Check for try-catch blocks
        if (strpos($content, 'try') === false || strpos($content, 'catch') === false) {
            throw new Exception("Categories view should have error handling");
        }
        
        // Check for ErrorHandler usage
        if (strpos($content, 'ErrorHandler') === false) {
            throw new Exception("Categories view should use ErrorHandler");
        }
        
        // Check for empty state handling
        if (strpos($content, 'handleEmptyState') === false) {
            throw new Exception("Categories view should handle empty states");
        }
        
        echo "✓ Categories view has proper error handling\n";
        return true;
    }
    
    /**
     * Test service layer architecture consistency
     */
    public function testArchitectureConsistency() {
        $viewPaths = [
            'home' => __DIR__ . '/../app/views/home/home.php',
            'products' => __DIR__ . '/../app/views/products/products.php',
            'categories' => __DIR__ . '/../app/views/categories/categories.php'
        ];
        
        foreach ($viewPaths as $viewName => $viewPath) {
            if (!file_exists($viewPath)) {
                continue;
            }
            
            $content = file_get_contents($viewPath);
            
            // All views should use ViewDataService
            if (strpos($content, 'ViewDataService') === false) {
                throw new Exception("{$viewName} view should use ViewDataService for consistency");
            }
            
            // All views should use ErrorHandler
            if (strpos($content, 'ErrorHandler') === false) {
                throw new Exception("{$viewName} view should use ErrorHandler for consistency");
            }
        }
        
        echo "✓ All views follow consistent service layer architecture\n";
        return true;
    }
    
    /**
     * Test that categories view doesn't directly use models
     */
    public function testNoDirectModelUsage() {
        $categoriesPath = __DIR__ . '/../app/views/categories/categories.php';
        $content = file_get_contents($categoriesPath);
        
        // Should not directly instantiate CategoriesModel
        if (strpos($content, 'new CategoriesModel()') !== false) {
            throw new Exception("Categories view should not directly instantiate CategoriesModel - use ViewDataService instead");
        }
        
        // Should not directly call model methods
        if (strpos($content, '$categoriesModel->') !== false) {
            throw new Exception("Categories view should not directly call model methods - use ViewDataService instead");
        }
        
        echo "✓ Categories view follows service layer pattern (no direct model usage)\n";
        return true;
    }
    
    /**
     * Run all tests
     */
    public function runAllTests() {
        echo "Running Categories ViewDataService Integration Tests...\n\n";
        
        try {
            $this->testCategoriesUsesViewDataService();
            $this->testViewDataServiceHasCategoriesMethod();
            $this->testCategoriesErrorHandling();
            $this->testArchitectureConsistency();
            $this->testNoDirectModelUsage();
            
            echo "\n✅ All Categories ViewDataService tests passed!\n";
            echo "\n📋 Architecture Summary:\n";
            echo "- Categories view: ✓ Uses ViewDataService (consistent with home & products)\n";
            echo "- Service layer: ✓ getCategoriesPageData() method implemented\n";
            echo "- Error handling: ✓ Proper try-catch and ErrorHandler usage\n";
            echo "- Architecture: ✓ Consistent service layer pattern across all views\n";
            echo "- Separation: ✓ No direct model usage in views\n";
            
            return true;
        } catch (Exception $e) {
            echo "\n❌ Test failed: " . $e->getMessage() . "\n";
            return false;
        }
    }
}

// Run tests if this file is executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $test = new CategoriesViewDataServiceTest();
    $test->runAllTests();
}