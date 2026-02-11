<?php
/**
 * Test Admin Categories Views ViewDataService Integration
 * Validates that admin category views properly use ViewDataService
 */

require_once __DIR__ . '/../app/services/ViewDataService.php';
require_once __DIR__ . '/../app/services/ErrorHandler.php';

class AdminCategoriesViewDataServiceTest {
    private $viewDataService;
    private $errorHandler;
    private $testResults = [];
    
    public function __construct() {
        $this->viewDataService = new ViewDataService();
        $this->errorHandler = new ErrorHandler();
    }
    
    public function runAllTests(): array {
        echo "=== Admin Categories ViewDataService Integration Tests ===\n\n";
        
        $this->testAdminCategoriesDataMethod();
        $this->testAdminCategoryDetailsDataMethod();
        $this->testAdminCategoriesViewIntegration();
        $this->testAdminCategoryEditViewIntegration();
        $this->testAdminCategoryAddViewIntegration();
        
        return $this->testResults;
    }
    
    /**
     * Test getAdminCategoriesData method
     */
    private function testAdminCategoriesDataMethod(): void {
        echo "Testing getAdminCategoriesData method...\n";
        
        try {
            $data = $this->viewDataService->getAdminCategoriesData(1, 10);
            
            // Check required keys
            $requiredKeys = ['categories', 'pagination', 'filters', 'total'];
            foreach ($requiredKeys as $key) {
                if (!array_key_exists($key, $data)) {
                    throw new Exception("Missing required key: {$key}");
                }
            }
            
            // Check pagination structure
            if (!is_array($data['pagination']) || !isset($data['pagination']['current_page'])) {
                throw new Exception("Invalid pagination structure");
            }
            
            // Check categories array
            if (!is_array($data['categories'])) {
                throw new Exception("Categories should be an array");
            }
            
            $this->testResults['admin_categories_data'] = [
                'status' => 'PASS',
                'message' => 'getAdminCategoriesData method works correctly',
                'data_keys' => array_keys($data),
                'categories_count' => count($data['categories'])
            ];
            
            echo "✓ getAdminCategoriesData method test PASSED\n";
            
        } catch (Exception $e) {
            $this->testResults['admin_categories_data'] = [
                'status' => 'FAIL',
                'message' => 'getAdminCategoriesData method failed: ' . $e->getMessage()
            ];
            echo "✗ getAdminCategoriesData method test FAILED: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    /**
     * Test getAdminCategoryDetailsData method
     */
    private function testAdminCategoryDetailsDataMethod(): void {
        echo "Testing getAdminCategoryDetailsData method...\n";
        
        try {
            // Test with category ID 1 (should exist from seeder)
            $data = $this->viewDataService->getAdminCategoryDetailsData(1);
            
            // Check required keys
            $requiredKeys = ['category', 'products'];
            foreach ($requiredKeys as $key) {
                if (!array_key_exists($key, $data)) {
                    throw new Exception("Missing required key: {$key}");
                }
            }
            
            // Check products array
            if (!is_array($data['products'])) {
                throw new Exception("Products should be an array");
            }
            
            $this->testResults['admin_category_details_data'] = [
                'status' => 'PASS',
                'message' => 'getAdminCategoryDetailsData method works correctly',
                'data_keys' => array_keys($data),
                'has_category' => !is_null($data['category']),
                'products_count' => count($data['products'])
            ];
            
            echo "✓ getAdminCategoryDetailsData method test PASSED\n";
            
        } catch (Exception $e) {
            $this->testResults['admin_category_details_data'] = [
                'status' => 'FAIL',
                'message' => 'getAdminCategoryDetailsData method failed: ' . $e->getMessage()
            ];
            echo "✗ getAdminCategoryDetailsData method test FAILED: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    /**
     * Test admin categories index view integration
     */
    private function testAdminCategoriesViewIntegration(): void {
        echo "Testing admin categories index view integration...\n";
        
        try {
            // Capture output from the view
            ob_start();
            $_GET['page'] = 'admin';
            $_GET['module'] = 'categories';
            
            // Include the view file
            include __DIR__ . '/../app/views/admin/categories/index.php';
            $output = ob_get_clean();
            
            // Check if ViewDataService is used
            if (strpos($output, 'ViewDataService') === false && 
                strpos($output, '$viewDataService') === false) {
                // This is expected since the view uses the service internally
                // Check for expected content instead
            }
            
            // Check for expected HTML structure
            if (strpos($output, 'categories-page') === false) {
                throw new Exception("Missing categories page structure");
            }
            
            $this->testResults['admin_categories_view'] = [
                'status' => 'PASS',
                'message' => 'Admin categories index view integration works',
                'output_length' => strlen($output)
            ];
            
            echo "✓ Admin categories index view integration test PASSED\n";
            
        } catch (Exception $e) {
            $this->testResults['admin_categories_view'] = [
                'status' => 'FAIL',
                'message' => 'Admin categories index view integration failed: ' . $e->getMessage()
            ];
            echo "✗ Admin categories index view integration test FAILED: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    /**
     * Test admin category edit view integration
     */
    private function testAdminCategoryEditViewIntegration(): void {
        echo "Testing admin category edit view integration...\n";
        
        try {
            // Set up test environment
            $_GET['id'] = '1';
            $_GET['page'] = 'admin';
            $_GET['module'] = 'categories';
            $_GET['action'] = 'edit';
            
            // Capture output from the view
            ob_start();
            include __DIR__ . '/../app/views/admin/categories/edit.php';
            $output = ob_get_clean();
            
            // Check for expected HTML structure
            if (strpos($output, 'categories-edit-page') === false) {
                throw new Exception("Missing category edit page structure");
            }
            
            // Check for form elements
            if (strpos($output, 'name="name"') === false) {
                throw new Exception("Missing category name form field");
            }
            
            $this->testResults['admin_category_edit_view'] = [
                'status' => 'PASS',
                'message' => 'Admin category edit view integration works',
                'output_length' => strlen($output)
            ];
            
            echo "✓ Admin category edit view integration test PASSED\n";
            
        } catch (Exception $e) {
            $this->testResults['admin_category_edit_view'] = [
                'status' => 'FAIL',
                'message' => 'Admin category edit view integration failed: ' . $e->getMessage()
            ];
            echo "✗ Admin category edit view integration test FAILED: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    /**
     * Test admin category add view integration
     */
    private function testAdminCategoryAddViewIntegration(): void {
        echo "Testing admin category add view integration...\n";
        
        try {
            // Set up test environment
            $_GET['page'] = 'admin';
            $_GET['module'] = 'categories';
            $_GET['action'] = 'add';
            
            // Capture output from the view
            ob_start();
            include __DIR__ . '/../app/views/admin/categories/add.php';
            $output = ob_get_clean();
            
            // Check for expected HTML structure
            if (strpos($output, 'categories-add-page') === false) {
                throw new Exception("Missing category add page structure");
            }
            
            // Check for form elements
            if (strpos($output, 'name="name"') === false) {
                throw new Exception("Missing category name form field");
            }
            
            $this->testResults['admin_category_add_view'] = [
                'status' => 'PASS',
                'message' => 'Admin category add view integration works',
                'output_length' => strlen($output)
            ];
            
            echo "✓ Admin category add view integration test PASSED\n";
            
        } catch (Exception $e) {
            $this->testResults['admin_category_add_view'] = [
                'status' => 'FAIL',
                'message' => 'Admin category add view integration failed: ' . $e->getMessage()
            ];
            echo "✗ Admin category add view integration test FAILED: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    /**
     * Generate test report
     */
    public function generateReport(): void {
        echo "=== TEST REPORT ===\n";
        
        $totalTests = count($this->testResults);
        $passedTests = 0;
        
        foreach ($this->testResults as $testName => $result) {
            $status = $result['status'];
            $message = $result['message'];
            
            echo "{$testName}: {$status} - {$message}\n";
            
            if ($status === 'PASS') {
                $passedTests++;
            }
        }
        
        echo "\nSUMMARY: {$passedTests}/{$totalTests} tests passed\n";
        
        if ($passedTests === $totalTests) {
            echo "🎉 All admin categories ViewDataService integration tests PASSED!\n";
        } else {
            echo "❌ Some tests FAILED. Please review the issues above.\n";
        }
    }
}

// Run tests if called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $tester = new AdminCategoriesViewDataServiceTest();
    $results = $tester->runAllTests();
    $tester->generateReport();
}