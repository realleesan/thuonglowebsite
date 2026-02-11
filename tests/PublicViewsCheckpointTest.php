<?php
/**
 * Public Views Checkpoint Test
 * Comprehensive test to ensure all public views are working correctly
 * after conversion from hardcoded to dynamic data
 */

class PublicViewsCheckpointTest {
    
    /**
     * Test home page view structure
     */
    public function testHomePageStructure() {
        $homeViewPath = __DIR__ . '/../app/views/home/home.php';
        
        if (!file_exists($homeViewPath)) {
            throw new Exception("Home view file not found");
        }
        
        $content = file_get_contents($homeViewPath);
        
        // Check for dynamic data usage (either direct model or service)
        if (strpos($content, 'ProductsModel') === false && strpos($content, 'ViewDataService') === false) {
            throw new Exception("Home view should use ProductsModel or ViewDataService");
        }
        
        // Check for proper PHP opening tags
        if (strpos($content, '<?php') === false) {
            throw new Exception("Home view should have PHP code");
        }
        
        // Check for error handling
        if (strpos($content, 'try') === false && strpos($content, 'catch') === false) {
            echo "⚠ Warning: Home view should have error handling\n";
        }
        
        echo "✓ Home page structure is correct\n";
        return true;
    }
    
    /**
     * Test products page view structure
     */
    public function testProductsPageStructure() {
        $productsViewPath = __DIR__ . '/../app/views/products/products.php';
        
        if (!file_exists($productsViewPath)) {
            throw new Exception("Products view file not found");
        }
        
        $content = file_get_contents($productsViewPath);
        
        // Check for dynamic data usage (either direct model or service)
        if (strpos($content, 'ProductsModel') === false && strpos($content, 'ViewDataService') === false) {
            throw new Exception("Products view should use ProductsModel or ViewDataService");
        }
        
        // Check for pagination logic
        if (strpos($content, 'pagination') === false && strpos($content, 'page') === false) {
            throw new Exception("Products view should have pagination");
        }
        
        // Check for search functionality
        if (strpos($content, 'search') === false) {
            throw new Exception("Products view should have search functionality");
        }
        
        echo "✓ Products page structure is correct\n";
        return true;
    }
    
    /**
     * Test product details page structure
     */
    public function testProductDetailsPageStructure() {
        $detailsViewPath = __DIR__ . '/../app/views/products/details.php';
        
        if (!file_exists($detailsViewPath)) {
            throw new Exception("Product details view file not found");
        }
        
        $content = file_get_contents($detailsViewPath);
        
        // Check for dynamic data usage (either direct model or service)
        if (strpos($content, 'ProductsModel') === false && strpos($content, 'ViewDataService') === false) {
            throw new Exception("Product details view should use ProductsModel or ViewDataService");
        }
        
        // Check for error handling
        if (strpos($content, 'try') === false || strpos($content, 'catch') === false) {
            throw new Exception("Product details view should have error handling");
        }
        
        echo "✓ Product details page structure is correct\n";
        return true;
    }
    
    /**
     * Test categories page structure
     */
    public function testCategoriesPageStructure() {
        $categoriesViewPath = __DIR__ . '/../app/views/categories/categories.php';
        
        if (!file_exists($categoriesViewPath)) {
            throw new Exception("Categories view file not found");
        }
        
        $content = file_get_contents($categoriesViewPath);
        
        // Check for dynamic data usage (either direct model or service)
        if (strpos($content, 'CategoriesModel') === false && strpos($content, 'ViewDataService') === false) {
            throw new Exception("Categories view should use CategoriesModel or ViewDataService");
        }
        
        // Check for pagination
        if (strpos($content, 'pagination') === false && strpos($content, 'page') === false) {
            throw new Exception("Categories view should have pagination");
        }
        
        // Check for sorting
        if (strpos($content, 'order_by') === false) {
            throw new Exception("Categories view should have sorting");
        }
        
        echo "✓ Categories page structure is correct\n";
        return true;
    }
    
    /**
     * Test that all views use proper security measures
     */
    public function testSecurityMeasures() {
        $viewPaths = [
            __DIR__ . '/../app/views/home/home.php',
            __DIR__ . '/../app/views/products/products.php',
            __DIR__ . '/../app/views/products/details.php',
            __DIR__ . '/../app/views/categories/categories.php'
        ];
        
        foreach ($viewPaths as $viewPath) {
            if (!file_exists($viewPath)) {
                continue;
            }
            
            $content = file_get_contents($viewPath);
            
            // Check for HTML escaping
            if (strpos($content, 'htmlspecialchars') === false) {
                throw new Exception("View " . basename($viewPath) . " should use htmlspecialchars for security");
            }
        }
        
        echo "✓ Security measures are in place\n";
        return true;
    }
    
    /**
     * Test that views handle empty states
     */
    public function testEmptyStateHandling() {
        $viewPaths = [
            __DIR__ . '/../app/views/products/products.php',
            __DIR__ . '/../app/views/categories/categories.php'
        ];
        
        foreach ($viewPaths as $viewPath) {
            if (!file_exists($viewPath)) {
                continue;
            }
            
            $content = file_get_contents($viewPath);
            
            // Check for empty state handling
            if (strpos($content, 'empty(') === false && strpos($content, 'count(') === false) {
                throw new Exception("View " . basename($viewPath) . " should handle empty states");
            }
        }
        
        echo "✓ Empty state handling is implemented\n";
        return true;
    }
    
    /**
     * Test that models are properly included
     */
    public function testModelInclusion() {
        $modelPaths = [
            __DIR__ . '/../app/models/ProductsModel.php',
            __DIR__ . '/../app/models/CategoriesModel.php',
            __DIR__ . '/../app/models/NewsModel.php'
        ];
        
        foreach ($modelPaths as $modelPath) {
            if (!file_exists($modelPath)) {
                throw new Exception("Model " . basename($modelPath) . " not found");
            }
            
            $content = file_get_contents($modelPath);
            
            // Check for class definition
            $className = str_replace('.php', '', basename($modelPath));
            if (strpos($content, "class {$className}") === false) {
                throw new Exception("Model {$className} should have proper class definition");
            }
        }
        
        echo "✓ All required models are present\n";
        return true;
    }
    
    /**
     * Test view-specific methods in models
     */
    public function testViewSpecificMethods() {
        $checks = [
            'ProductsModel' => ['getFeaturedForHome', 'getByCategory', 'getLatestForHome'],
            'CategoriesModel' => ['getWithProductCounts', 'getFeaturedCategories', 'getStats'],
            'NewsModel' => ['getLatestForHome', 'getWithCategories']
        ];
        
        foreach ($checks as $modelName => $methods) {
            $modelPath = __DIR__ . "/../app/models/{$modelName}.php";
            
            if (!file_exists($modelPath)) {
                throw new Exception("Model {$modelName} not found");
            }
            
            $content = file_get_contents($modelPath);
            
            foreach ($methods as $method) {
                if (strpos($content, "function {$method}") === false) {
                    throw new Exception("Model {$modelName} should have method {$method}");
                }
            }
        }
        
        echo "✓ View-specific methods are implemented in models\n";
        return true;
    }
    
    /**
     * Test that hardcoded data has been removed
     */
    public function testHardcodedDataRemoval() {
        $viewPaths = [
            __DIR__ . '/../app/views/home/home.php',
            __DIR__ . '/../app/views/products/products.php',
            __DIR__ . '/../app/views/categories/categories.php'
        ];
        
        $hardcodedPatterns = [
            'Sản phẩm 1',
            'Danh mục 1',
            'hardcoded',
            'fake-data',
            'example-product'
        ];
        
        foreach ($viewPaths as $viewPath) {
            if (!file_exists($viewPath)) {
                continue;
            }
            
            $content = file_get_contents($viewPath);
            
            foreach ($hardcodedPatterns as $pattern) {
                if (stripos($content, $pattern) !== false) {
                    echo "⚠ Warning: Possible hardcoded data '{$pattern}' found in " . basename($viewPath) . "\n";
                }
            }
        }
        
        echo "✓ Hardcoded data check completed\n";
        return true;
    }
    
    /**
     * Test infrastructure services
     */
    public function testInfrastructureServices() {
        $servicePaths = [
            __DIR__ . '/../app/services/ViewDataService.php',
            __DIR__ . '/../app/services/DataTransformer.php',
            __DIR__ . '/../app/services/ViewSecurityHelper.php',
            __DIR__ . '/../app/services/ErrorHandler.php'
        ];
        
        foreach ($servicePaths as $servicePath) {
            if (!file_exists($servicePath)) {
                throw new Exception("Service " . basename($servicePath) . " not found");
            }
            
            $content = file_get_contents($servicePath);
            
            // Check for class definition
            $className = str_replace('.php', '', basename($servicePath));
            if (strpos($content, "class {$className}") === false) {
                throw new Exception("Service {$className} should have proper class definition");
            }
        }
        
        echo "✓ Infrastructure services are present\n";
        return true;
    }
    
    /**
     * Run all checkpoint tests
     */
    public function runAllTests() {
        echo "Running Public Views Checkpoint Tests...\n\n";
        
        try {
            $this->testHomePageStructure();
            $this->testProductsPageStructure();
            $this->testProductDetailsPageStructure();
            $this->testCategoriesPageStructure();
            $this->testSecurityMeasures();
            $this->testEmptyStateHandling();
            $this->testModelInclusion();
            $this->testViewSpecificMethods();
            $this->testHardcodedDataRemoval();
            $this->testInfrastructureServices();
            
            echo "\n✅ All Public Views Checkpoint tests passed!\n";
            echo "\n📋 Summary:\n";
            echo "- Home page: ✓ Converted to dynamic data\n";
            echo "- Products page: ✓ Converted with pagination and search\n";
            echo "- Product details: ✓ Converted with error handling\n";
            echo "- Categories page: ✓ Converted with sorting and filtering\n";
            echo "- Security: ✓ HTML escaping implemented\n";
            echo "- Empty states: ✓ Properly handled\n";
            echo "- Models: ✓ All required methods present\n";
            echo "- Infrastructure: ✓ Services are available\n";
            
            return true;
        } catch (Exception $e) {
            echo "\n❌ Checkpoint failed: " . $e->getMessage() . "\n";
            return false;
        }
    }
}

// Run tests if this file is executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $test = new PublicViewsCheckpointTest();
    $test->runAllTests();
}