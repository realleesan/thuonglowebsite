<?php
/**
 * Test Admin News Views ViewDataService Integration
 * Validates that admin news views properly use ViewDataService
 */

require_once __DIR__ . '/../app/services/ViewDataService.php';
require_once __DIR__ . '/../app/services/ErrorHandler.php';

class AdminNewsViewDataServiceTest {
    private $viewDataService;
    private $errorHandler;
    private $testResults = [];
    
    public function __construct() {
        $this->viewDataService = new ViewDataService();
        $this->errorHandler = new ErrorHandler();
    }
    
    public function runAllTests(): array {
        echo "=== Admin News ViewDataService Integration Tests ===\n\n";
        
        $this->testAdminNewsDataMethod();
        $this->testAdminNewsDetailsDataMethod();
        $this->testAdminNewsViewIntegration();
        $this->testAdminNewsEditViewIntegration();
        $this->testAdminNewsAddViewIntegration();
        
        return $this->testResults;
    }
    
    /**
     * Test getAdminNewsData method
     */
    private function testAdminNewsDataMethod(): void {
        echo "Testing getAdminNewsData method...\n";
        
        try {
            $data = $this->viewDataService->getAdminNewsData(1, 10);
            
            // Check required keys
            $requiredKeys = ['news', 'pagination', 'filters', 'total', 'stats'];
            foreach ($requiredKeys as $key) {
                if (!array_key_exists($key, $data)) {
                    throw new Exception("Missing required key: {$key}");
                }
            }
            
            // Check pagination structure
            if (!is_array($data['pagination']) || !isset($data['pagination']['current_page'])) {
                throw new Exception("Invalid pagination structure");
            }
            
            // Check news array
            if (!is_array($data['news'])) {
                throw new Exception("News should be an array");
            }
            
            // Check stats structure
            $requiredStats = ['total', 'published', 'draft', 'archived'];
            foreach ($requiredStats as $stat) {
                if (!array_key_exists($stat, $data['stats'])) {
                    throw new Exception("Missing required stat: {$stat}");
                }
            }
            
            $this->testResults['admin_news_data'] = [
                'status' => 'PASS',
                'message' => 'getAdminNewsData method works correctly',
                'data_keys' => array_keys($data),
                'news_count' => count($data['news']),
                'stats' => $data['stats']
            ];
            
            echo "✓ getAdminNewsData method test PASSED\n";
            
        } catch (Exception $e) {
            $this->testResults['admin_news_data'] = [
                'status' => 'FAIL',
                'message' => 'getAdminNewsData method failed: ' . $e->getMessage()
            ];
            echo "✗ getAdminNewsData method test FAILED: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    /**
     * Test getAdminNewsDetailsData method
     */
    private function testAdminNewsDetailsDataMethod(): void {
        echo "Testing getAdminNewsDetailsData method...\n";
        
        try {
            // Test with news ID 1 (should exist from seeder)
            $data = $this->viewDataService->getAdminNewsDetailsData(1);
            
            // Check required keys
            $requiredKeys = ['news', 'author'];
            foreach ($requiredKeys as $key) {
                if (!array_key_exists($key, $data)) {
                    throw new Exception("Missing required key: {$key}");
                }
            }
            
            $this->testResults['admin_news_details_data'] = [
                'status' => 'PASS',
                'message' => 'getAdminNewsDetailsData method works correctly',
                'data_keys' => array_keys($data),
                'has_news' => !is_null($data['news']),
                'has_author' => !is_null($data['author'])
            ];
            
            echo "✓ getAdminNewsDetailsData method test PASSED\n";
            
        } catch (Exception $e) {
            $this->testResults['admin_news_details_data'] = [
                'status' => 'FAIL',
                'message' => 'getAdminNewsDetailsData method failed: ' . $e->getMessage()
            ];
            echo "✗ getAdminNewsDetailsData method test FAILED: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    /**
     * Test admin news index view integration
     */
    private function testAdminNewsViewIntegration(): void {
        echo "Testing admin news index view integration...\n";
        
        try {
            // Capture output from the view
            ob_start();
            $_GET['page'] = 'admin';
            $_GET['module'] = 'news';
            
            // Include the view file
            include __DIR__ . '/../app/views/admin/news/index.php';
            $output = ob_get_clean();
            
            // Check for expected HTML structure
            if (strpos($output, 'news-page') === false) {
                throw new Exception("Missing news page structure");
            }
            
            // Check for table structure
            if (strpos($output, 'admin-table') === false) {
                throw new Exception("Missing admin table structure");
            }
            
            $this->testResults['admin_news_view'] = [
                'status' => 'PASS',
                'message' => 'Admin news index view integration works',
                'output_length' => strlen($output)
            ];
            
            echo "✓ Admin news index view integration test PASSED\n";
            
        } catch (Exception $e) {
            $this->testResults['admin_news_view'] = [
                'status' => 'FAIL',
                'message' => 'Admin news index view integration failed: ' . $e->getMessage()
            ];
            echo "✗ Admin news index view integration test FAILED: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    /**
     * Test admin news edit view integration
     */
    private function testAdminNewsEditViewIntegration(): void {
        echo "Testing admin news edit view integration...\n";
        
        try {
            // Set up test environment
            $_GET['id'] = '1';
            $_GET['page'] = 'admin';
            $_GET['module'] = 'news';
            $_GET['action'] = 'edit';
            
            // Capture output from the view
            ob_start();
            include __DIR__ . '/../app/views/admin/news/edit.php';
            $output = ob_get_clean();
            
            // Check for expected HTML structure
            if (strpos($output, 'news-edit-page') === false) {
                throw new Exception("Missing news edit page structure");
            }
            
            // Check for form elements
            if (strpos($output, 'name="title"') === false) {
                throw new Exception("Missing news title form field");
            }
            
            $this->testResults['admin_news_edit_view'] = [
                'status' => 'PASS',
                'message' => 'Admin news edit view integration works',
                'output_length' => strlen($output)
            ];
            
            echo "✓ Admin news edit view integration test PASSED\n";
            
        } catch (Exception $e) {
            $this->testResults['admin_news_edit_view'] = [
                'status' => 'FAIL',
                'message' => 'Admin news edit view integration failed: ' . $e->getMessage()
            ];
            echo "✗ Admin news edit view integration test FAILED: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    /**
     * Test admin news add view integration
     */
    private function testAdminNewsAddViewIntegration(): void {
        echo "Testing admin news add view integration...\n";
        
        try {
            // Set up test environment
            $_GET['page'] = 'admin';
            $_GET['module'] = 'news';
            $_GET['action'] = 'add';
            
            // Capture output from the view
            ob_start();
            include __DIR__ . '/../app/views/admin/news/add.php';
            $output = ob_get_clean();
            
            // Check for expected HTML structure
            if (strpos($output, 'news-add-page') === false) {
                throw new Exception("Missing news add page structure");
            }
            
            // Check for form elements
            if (strpos($output, 'name="title"') === false) {
                throw new Exception("Missing news title form field");
            }
            
            $this->testResults['admin_news_add_view'] = [
                'status' => 'PASS',
                'message' => 'Admin news add view integration works',
                'output_length' => strlen($output)
            ];
            
            echo "✓ Admin news add view integration test PASSED\n";
            
        } catch (Exception $e) {
            $this->testResults['admin_news_add_view'] = [
                'status' => 'FAIL',
                'message' => 'Admin news add view integration failed: ' . $e->getMessage()
            ];
            echo "✗ Admin news add view integration test FAILED: " . $e->getMessage() . "\n";
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
            echo "🎉 All admin news ViewDataService integration tests PASSED!\n";
        } else {
            echo "❌ Some tests FAILED. Please review the issues above.\n";
        }
    }
}

// Run tests if called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $tester = new AdminNewsViewDataServiceTest();
    $results = $tester->runAllTests();
    $tester->generateReport();
}