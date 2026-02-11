<?php
/**
 * Basic test for ViewDataService infrastructure
 */

require_once __DIR__ . '/../app/services/ViewDataService.php';
require_once __DIR__ . '/../app/services/DataTransformer.php';
require_once __DIR__ . '/../app/services/ViewSecurityHelper.php';
require_once __DIR__ . '/../app/services/ErrorHandler.php';

class ViewDataServiceTest {
    private $viewDataService;
    private $dataTransformer;
    private $securityHelper;
    private $errorHandler;
    
    public function __construct() {
        $this->viewDataService = new ViewDataService();
        $this->dataTransformer = new DataTransformer();
        $this->securityHelper = new ViewSecurityHelper();
        $this->errorHandler = new ErrorHandler();
    }
    
    public function runTests() {
        echo "Running ViewDataService Infrastructure Tests...\n\n";
        
        $this->testSecurityHelper();
        $this->testDataTransformer();
        $this->testErrorHandler();
        $this->testViewDataService();
        
        echo "\nAll infrastructure tests completed!\n";
    }
    
    private function testSecurityHelper() {
        echo "Testing ViewSecurityHelper...\n";
        
        // Test HTML escaping
        $maliciousInput = '<script>alert("XSS")</script>';
        $escaped = $this->securityHelper->escapeHtml($maliciousInput);
        assert(strpos($escaped, '<script>') === false, 'HTML escaping failed');
        echo "✓ HTML escaping works\n";
        
        // Test money formatting
        $amount = 1234567.89;
        $formatted = $this->securityHelper->formatMoney($amount);
        assert($formatted === '1.234.568đ', 'Money formatting failed');
        echo "✓ Money formatting works\n";
        
        // Test email validation
        assert($this->securityHelper->validateEmail('test@example.com') === true, 'Email validation failed');
        assert($this->securityHelper->validateEmail('invalid-email') === false, 'Email validation failed');
        echo "✓ Email validation works\n";
        
        // Test phone validation
        assert($this->securityHelper->validatePhone('0901234567') === true, 'Phone validation failed');
        assert($this->securityHelper->validatePhone('123') === false, 'Phone validation failed');
        echo "✓ Phone validation works\n";
    }
    
    private function testDataTransformer() {
        echo "\nTesting DataTransformer...\n";
        
        // Test product transformation
        $productData = [
            'id' => 1,
            'name' => 'Test Product',
            'price' => 100000,
            'sale_price' => 80000,
            'category_name' => 'Test Category',
            'featured' => 1,
            'stock' => 10
        ];
        
        $transformed = $this->dataTransformer->transformProduct($productData);
        
        assert($transformed['id'] === 1, 'Product ID transformation failed');
        assert($transformed['formatted_price'] === '100.000đ', 'Price formatting failed');
        assert($transformed['discount_percent'] === 20, 'Discount calculation failed');
        assert($transformed['in_stock'] === true, 'Stock status failed');
        echo "✓ Product transformation works\n";
        
        // Test empty product handling
        $emptyTransformed = $this->dataTransformer->transformProduct(null);
        assert(empty($emptyTransformed), 'Empty product handling failed');
        echo "✓ Empty product handling works\n";
    }
    
    private function testErrorHandler() {
        echo "\nTesting ErrorHandler...\n";
        
        // Test error logging
        $this->errorHandler->logInfo('Test info message', ['test' => true]);
        echo "✓ Error logging works\n";
        
        // Test database error handling
        $exception = new Exception('Test database error');
        $result = $this->errorHandler->handleDatabaseError($exception);
        
        assert($result['success'] === false, 'Database error handling failed');
        assert($result['error_code'] === 'DB_ERROR', 'Error code failed');
        echo "✓ Database error handling works\n";
        
        // Test validation error handling
        $errors = ['Name is required', 'Email is invalid'];
        $result = $this->errorHandler->handleValidationError($errors);
        
        assert($result['success'] === false, 'Validation error handling failed');
        assert(count($result['errors']) === 2, 'Error count failed');
        echo "✓ Validation error handling works\n";
    }
    
    private function testViewDataService() {
        echo "\nTesting ViewDataService...\n";
        
        // Test empty state handling
        $emptyState = $this->viewDataService->handleEmptyState('home');
        
        assert(isset($emptyState['featured_products']), 'Empty state structure failed');
        assert(isset($emptyState['message']), 'Empty state message failed');
        echo "✓ Empty state handling works\n";
        
        // Test empty state for different types
        $productEmptyState = $this->viewDataService->handleEmptyState('products');
        assert(isset($productEmptyState['products']), 'Product empty state failed');
        echo "✓ Different empty states work\n";
    }
}

// Run tests if called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    try {
        $test = new ViewDataServiceTest();
        $test->runTests();
        echo "\n✅ All infrastructure tests passed!\n";
    } catch (Exception $e) {
        echo "\n❌ Test failed: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . "\n";
        echo "Line: " . $e->getLine() . "\n";
    }
}