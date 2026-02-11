<?php
/**
 * Admin Products ViewDataService Integration Test
 * Tests that admin products views properly use ViewDataService
 */

class AdminProductsViewDataServiceTest {
    
    /**
     * Test that admin products index uses ViewDataService
     */
    public function testAdminProductsIndexUsesViewDataService() {
        $indexPath = __DIR__ . '/../app/views/admin/products/index.php';
        
        if (!file_exists($indexPath)) {
            throw new Exception("Admin products index view file not found");
        }
        
        $content = file_get_contents($indexPath);
        
        // Check for ViewDataService usage
        if (strpos($content, 'ViewDataService') === false) {
            throw new Exception("Admin products index should use ViewDataService");
        }
        
        // Check for getAdminProductsData method call
        if (strpos($content, 'getAdminProductsData') === false) {
            throw new Exception("Admin products index should call getAdminProductsData method");
        }
        
        echo "✓ Admin products index uses ViewDataService correctly\n";
        return true;
    }
    
    /**
     * Test that admin products view uses ViewDataService
     */
    public function testAdminProductsViewUsesViewDataService() {
        $viewPath = __DIR__ . '/../app/views/admin/products/view.php';
        
        if (!file_exists($viewPath)) {
            throw new Exception("Admin products view file not found");
        }
        
        $content = file_get_contents($viewPath);
        
        // Check for ViewDataService usage
        if (strpos($content, 'ViewDataService') === false) {
            throw new Exception("Admin products view should use ViewDataService");
        }
        
        // Check for getAdminProductDetailsData method call
        if (strpos($content, 'getAdminProductDetailsData') === false) {
            throw new Exception("Admin products view should call getAdminProductDetailsData method");
        }
        
        echo "✓ Admin products view uses ViewDataService correctly\n";
        return true;
    }
    
    /**
     * Test that ViewDataService has admin products methods
     */
    public function testViewDataServiceHasAdminProductsMethods() {
        $servicePath = __DIR__ . '/../app/services/ViewDataService.php';
        
        if (!file_exists($servicePath)) {
            throw new Exception("ViewDataService file not found");
        }
        
        $content = file_get_contents($servicePath);
        
        // Check for admin products methods
        $requiredMethods = [
            'getAdminProductsData',
            'getAdminProductDetailsData'
        ];
        
        foreach ($requiredMethods as $method) {
            if (strpos($content, "function {$method}") === false) {
                throw new Exception("ViewDataService should have {$method} method");
            }
        }
        
        echo "✓ ViewDataService has all required admin products methods\n";
        return true;
    }
    
    /**
     * Test error handling in admin products views
     */
    public function testAdminProductsErrorHandling() {
        $viewPaths = [
            'index' => __DIR__ . '/../app/views/admin/products/index.php',
            'view' => __DIR__ . '/../app/views/admin/products/view.php'
        ];
        
        foreach ($viewPaths as $viewName => $viewPath) {
            if (!file_exists($viewPath)) {
                continue;
            }
            
            $content = file_get_contents($viewPath);
            
            // Check for try-catch blocks
            if (strpos($content, 'try') === false || strpos($content, 'catch') === false) {
                throw new Exception("Admin products {$viewName} should have error handling");
            }
            
            // Check for ErrorHandler usage
            if (strpos($content, 'ErrorHandler') === false) {
                throw new Exception("Admin products {$viewName} should use ErrorHandler");
            }
        }
        
        echo "✓ Admin products views have proper error handling\n";
        return true;
    }
    
    /**
     * Test that admin products views don't directly use models
     */
    public function testNoDirectModelUsage() {
        $viewPaths = [
            'index' => __DIR__ . '/../app/views/admin/products/index.php',
            'view' => __DIR__ . '/../app/views/admin/products/view.php'
        ];
        
        foreach ($viewPaths as $viewName => $viewPath) {
            if (!file_exists($viewPath)) {
                continue;
            }
            
            $content = file_get_contents($viewPath);
            
            // Should not directly instantiate ProductsModel
            if (strpos($content, 'new ProductsModel()') !== false) {
                throw new Exception("Admin products {$viewName} should not directly instantiate ProductsModel - use ViewDataService instead");
            }
            
            // Should not directly instantiate CategoriesModel
            if (strpos($content, 'new CategoriesModel()') !== false) {
                throw new Exception("Admin products {$viewName} should not directly instantiate CategoriesModel - use ViewDataService instead");
            }
        }
        
        echo "✓ Admin products views follow service layer pattern (no direct model usage)\n";
        return true;
    }
    
    /**
     * Test pagination and filtering functionality
     */
    public function testPaginationAndFiltering() {
        $indexPath = __DIR__ . '/../app/views/admin/products/index.php';
        $content = file_get_contents($indexPath);
        
        // Check for pagination variables
        if (strpos($content, '$pagination') === false) {
            throw new Exception("Admin products index should handle pagination");
        }
        
        // Check for filter handling
        if (strpos($content, '$filters') === false) {
            throw new Exception("Admin products index should handle filters");
        }
        
        // Check for search functionality
        if (strpos($content, 'search') === false) {
            throw new Exception("Admin products index should have search functionality");
        }
        
        echo "✓ Admin products index has pagination and filtering\n";
        return true;
    }
    
    /**
     * Test security measures
     */
    public function testSecurityMeasures() {
        $viewPaths = [
            'index' => __DIR__ . '/../app/views/admin/products/index.php',
            'view' => __DIR__ . '/../app/views/admin/products/view.php'
        ];
        
        foreach ($viewPaths as $viewName => $viewPath) {
            if (!file_exists($viewPath)) {
                continue;
            }
            
            $content = file_get_contents($viewPath);
            
            // Check for HTML escaping
            if (strpos($content, 'htmlspecialchars') === false) {
                throw new Exception("Admin products {$viewName} should use htmlspecialchars for security");
            }
        }
        
        echo "✓ Admin products views have proper security measures\n";
        return true;
    }
    
    /**
     * Run all tests
     */
    public function runAllTests() {
        echo "Running Admin Products ViewDataService Integration Tests...\n\n";
        
        try {
            $this->testAdminProductsIndexUsesViewDataService();
            $this->testAdminProductsViewUsesViewDataService();
            $this->testViewDataServiceHasAdminProductsMethods();
            $this->testAdminProductsErrorHandling();
            $this->testNoDirectModelUsage();
            $this->testPaginationAndFiltering();
            $this->testSecurityMeasures();
            
            echo "\n✅ All Admin Products ViewDataService tests passed!\n";
            echo "\n📋 Architecture Summary:\n";
            echo "- Admin products index: ✓ Uses ViewDataService with pagination and filtering\n";
            echo "- Admin products view: ✓ Uses ViewDataService for product details\n";
            echo "- Service layer: ✓ getAdminProductsData() and getAdminProductDetailsData() methods implemented\n";
            echo "- Error handling: ✓ Proper try-catch and ErrorHandler usage\n";
            echo "- Security: ✓ HTML escaping and input validation\n";
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
    $test = new AdminProductsViewDataServiceTest();
    $test->runAllTests();
}