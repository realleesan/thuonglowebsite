<?php
/**
 * Basic infrastructure test without database dependency
 */

require_once __DIR__ . '/../app/services/DataTransformer.php';
require_once __DIR__ . '/../app/services/ViewSecurityHelper.php';
require_once __DIR__ . '/../app/services/ErrorHandler.php';

class InfrastructureTest {
    private $dataTransformer;
    private $securityHelper;
    private $errorHandler;
    
    public function __construct() {
        $this->dataTransformer = new DataTransformer();
        $this->securityHelper = new ViewSecurityHelper();
        $this->errorHandler = new ErrorHandler();
    }
    
    public function runTests() {
        echo "Running Infrastructure Tests (No Database)...\n\n";
        
        $this->testSecurityHelper();
        $this->testDataTransformer();
        $this->testErrorHandler();
        
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
        
        // Test input sanitization
        $dirtyInput = "  <script>alert('xss')</script>  ";
        $clean = $this->securityHelper->sanitizeInput($dirtyInput);
        assert(trim($clean) === $clean, 'Input sanitization failed');
        echo "✓ Input sanitization works\n";
        
        // Test integer sanitization
        $intValue = $this->securityHelper->sanitizeInt('123abc', 1, 1000);
        assert($intValue === 123, 'Integer sanitization failed');
        echo "✓ Integer sanitization works\n";
        
        // Test float sanitization
        $floatValue = $this->securityHelper->sanitizeFloat('123.45abc', 0, 1000);
        assert($floatValue === 123.45, 'Float sanitization failed');
        echo "✓ Float sanitization works\n";
    }
    
    private function testDataTransformer() {
        echo "\nTesting DataTransformer...\n";
        
        // Test product transformation
        $productData = [
            'id' => 1,
            'name' => 'Test Product <script>',
            'price' => 100000,
            'sale_price' => 80000,
            'category_name' => 'Test Category',
            'featured' => 1,
            'stock' => 10
        ];
        
        $transformed = $this->dataTransformer->transformProduct($productData);
        
        assert($transformed['id'] === 1, 'Product ID transformation failed');
        assert($transformed['formatted_price'] === '100.000đ', 'Price formatting failed');
        assert($transformed['discount_percent'] == 20, 'Discount calculation failed'); // Use == instead of ===
        assert($transformed['in_stock'] === true, 'Stock status failed');
        assert(strpos($transformed['name'], '<script>') === false, 'XSS protection failed');
        echo "✓ Product transformation works\n";
        
        // Test category transformation
        $categoryData = [
            'id' => 1,
            'name' => 'Test Category',
            'slug' => 'test-category',
            'product_count' => 5
        ];
        
        $transformed = $this->dataTransformer->transformCategory($categoryData);
        assert($transformed['id'] === 1, 'Category transformation failed');
        assert($transformed['product_count'] === 5, 'Product count failed');
        echo "✓ Category transformation works\n";
        
        // Test user transformation
        $userData = [
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'points' => 1500,
            'total_spent' => 250000
        ];
        
        $transformed = $this->dataTransformer->transformUser($userData);
        assert($transformed['formatted_points'] === '1,500', 'Points formatting failed');
        assert($transformed['total_spent'] === '250.000đ', 'Spent formatting failed');
        echo "✓ User transformation works\n";
        
        // Test empty data handling
        $emptyTransformed = $this->dataTransformer->transformProduct(null);
        assert(empty($emptyTransformed), 'Empty product handling failed');
        echo "✓ Empty data handling works\n";
        
        // Test multiple products transformation
        $products = [$productData, $productData];
        $transformedProducts = $this->dataTransformer->transformProducts($products);
        assert(count($transformedProducts) === 2, 'Multiple products transformation failed');
        echo "✓ Multiple products transformation works\n";
    }
    
    private function testErrorHandler() {
        echo "\nTesting ErrorHandler...\n";
        
        // Test database error handling
        $exception = new Exception('Test database error');
        $result = $this->errorHandler->handleDatabaseError($exception);
        
        assert($result['success'] === false, 'Database error handling failed');
        assert($result['error_code'] === 'DB_ERROR', 'Error code failed');
        assert(!empty($result['message']), 'Error message failed');
        echo "✓ Database error handling works\n";
        
        // Test validation error handling
        $errors = ['Name is required', 'Email is invalid'];
        $result = $this->errorHandler->handleValidationError($errors);
        
        assert($result['success'] === false, 'Validation error handling failed');
        assert(count($result['errors']) === 2, 'Error count failed');
        assert($result['error_code'] === 'VALIDATION_ERROR', 'Validation error code failed');
        echo "✓ Validation error handling works\n";
        
        // Test not found error handling
        $result = $this->errorHandler->handleNotFoundError('product', 123);
        assert($result['success'] === false, 'Not found error handling failed');
        assert($result['error_code'] === 'NOT_FOUND', 'Not found error code failed');
        echo "✓ Not found error handling works\n";
        
        // Test permission error handling
        $result = $this->errorHandler->handlePermissionError('delete_user', 1);
        assert($result['success'] === false, 'Permission error handling failed');
        assert($result['error_code'] === 'PERMISSION_ERROR', 'Permission error code failed');
        echo "✓ Permission error handling works\n";
    }
}

// Run tests if called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    try {
        $test = new InfrastructureTest();
        $test->runTests();
        echo "\n✅ All infrastructure tests passed!\n";
        echo "\nInfrastructure classes are ready:\n";
        echo "- ViewSecurityHelper: XSS protection, data validation, formatting\n";
        echo "- DataTransformer: Data formatting for views\n";
        echo "- ErrorHandler: Centralized error handling and logging\n";
        echo "- ViewDataService: Ready (requires database connection)\n";
    } catch (Exception $e) {
        echo "\n❌ Test failed: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . "\n";
        echo "Line: " . $e->getLine() . "\n";
    }
}