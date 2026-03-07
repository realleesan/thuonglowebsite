<?php
/**
 * Test gọi trực tiếp PublicService->getCategoriesWithProductCounts()
 * Chạy: http://localhost/thuonglowebsite/test_publicservice_direct.php
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/app/services/BaseService.php';
require_once __DIR__ . '/app/services/DataTransformer.php';
require_once __DIR__ . '/app/models/BaseModel.php';
require_once __DIR__ . '/app/models/CategoriesModel.php';

echo "<h1>Test PublicService Direct</h1>";

try {
    // Create service manually
    $db = new mysqli('localhost', 'test1_thuonglowebsite', '21042005nhat', 'test1_thuonglowebsite');
    
    // Create DataTransformer
    class MockSecurity {
        public function escapeHtml($text) { return htmlspecialchars($text); }
        public function formatMoney($amount) { return number_format($amount, 0, ',', '.') . ' VNĐ'; }
    }
    $security = new MockSecurity();
    $transformer = new DataTransformer($security);
    
    // Create a simple service to call the method
    class TestService {
        private $db;
        private $transformer;
        
        public function __construct($db, $transformer) {
            $this->db = $db;
            $this->transformer = $transformer;
        }
        
        public function callModelMethod($modelClass, $method, $params = [], $bindings = []) {
            require_once __DIR__ . '/app/models/' . $modelClass . '.php';
            $model = new $modelClass($this->db);
            return call_user_func_array([$model, $method], $params);
        }
        
        public function getCategoriesWithProductCounts() {
            try {
                $categories = $this->callModelMethod(
                    'CategoriesModel',
                    'getWithProductCounts',
                    [],
                    []
                );

                return [
                    'categories' => $this->transformer->transformCategories($categories),
                ];
            } catch (\Exception $e) {
                return [
                    'categories' => [],
                ];
            }
        }
    }
    
    $testService = new TestService($db, $transformer);
    
    echo "<h2>Gọi TestService->getCategoriesWithProductCounts()</h2>";
    
    $result = $testService->getCategoriesWithProductCounts();
    
    echo "<h2>Kết quả categories:</h2>";
    $categories = $result['categories'] ?? [];
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Name</th><th>product_count</th></tr>";
    foreach ($categories as $cat) {
        echo "<tr><td>{$cat['id']}</td><td>{$cat['name']}</td><td>{$cat['product_count']}</td></tr>";
    }
    echo "</table>";
    
    $db->close();
    
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<p>Done!</p>";
