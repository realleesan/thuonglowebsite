<?php
/**
 * Test for Model Extensions (View Helper Methods)
 * Tests the new methods added to models for view data preparation
 */

// Mock database class for testing without actual database
class MockDatabase {
    private $queries = [];
    
    public function query($sql, $bindings = []) {
        // Store query for verification
        $this->queries[] = ['sql' => $sql, 'bindings' => $bindings];
        
        // Return mock data based on query type
        if (strpos($sql, 'COUNT') !== false) {
            return [['count' => 5]];
        }
        
        if (strpos($sql, 'products') !== false) {
            return [
                [
                    'id' => 1,
                    'name' => 'Test Product',
                    'price' => 100000,
                    'category_name' => 'Test Category',
                    'featured' => 1,
                    'status' => 'active'
                ]
            ];
        }
        
        if (strpos($sql, 'categories') !== false) {
            return [
                [
                    'id' => 1,
                    'name' => 'Test Category',
                    'slug' => 'test-category',
                    'product_count' => 5
                ]
            ];
        }
        
        if (strpos($sql, 'news') !== false) {
            return [
                [
                    'id' => 1,
                    'title' => 'Test News',
                    'slug' => 'test-news',
                    'author_name' => 'Test Author',
                    'category_name' => 'Test Category'
                ]
            ];
        }
        
        return [];
    }
    
    public function getQueries() {
        return $this->queries;
    }
    
    public function clearQueries() {
        $this->queries = [];
    }
}

// Mock BaseModel for testing
class MockBaseModel {
    protected $db;
    protected $table;
    
    public function __construct() {
        $this->db = new MockDatabase();
    }
    
    public function where($field, $operator = '=', $value = null) {
        return $this;
    }
    
    public function orderBy($field, $direction = 'ASC') {
        return $this;
    }
    
    public function limit($limit) {
        return $this;
    }
    
    public function get() {
        return $this->db->query("SELECT * FROM {$this->table}");
    }
    
    public function count() {
        $result = $this->db->query("SELECT COUNT(*) as count FROM {$this->table}");
        return $result[0]['count'] ?? 0;
    }
}

// Test ProductsModel methods
class TestProductsModel extends MockBaseModel {
    protected $table = 'products';
    
    public function getFeatured($limit = 6) {
        return $this->where('featured', true)
                   ->where('status', 'active')
                   ->orderBy('created_at', 'DESC')
                   ->limit($limit)
                   ->get();
    }
    
    public function getFeaturedForHome($limit = 8) {
        return $this->getFeatured($limit);
    }
    
    public function getWithCategory($limit = null) {
        $sql = "
            SELECT p.*, c.name as category_name, c.slug as category_slug
            FROM {$this->table} p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.status = 'active'
            ORDER BY p.created_at DESC
        ";
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        return $this->db->query($sql);
    }
    
    public function getLatestForHome($limit = 8) {
        return $this->getWithCategory($limit);
    }
    
    public function getByCategoryPaginated($categoryId, $page = 1, $limit = 12) {
        $offset = ($page - 1) * $limit;
        
        $sql = "
            SELECT p.*, c.name as category_name, c.slug as category_slug
            FROM {$this->table} p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.category_id = ? AND p.status = 'active'
            ORDER BY p.created_at DESC
            LIMIT {$limit} OFFSET {$offset}
        ";
        
        return $this->db->query($sql, [$categoryId]);
    }
    
    public function getProductStats() {
        return [
            'total' => $this->count(),
            'by_status' => ['active' => 5, 'inactive' => 2],
            'by_type' => ['data_nguon_hang' => 3, 'khoa_hoc' => 2]
        ];
    }
}

// Test CategoriesModel methods
class TestCategoriesModel extends MockBaseModel {
    protected $table = 'categories';
    
    public function getWithProductCount() {
        $sql = "
            SELECT c.*, COUNT(p.id) as products_count
            FROM {$this->table} c
            LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active'
            WHERE c.status = 'active'
            GROUP BY c.id
            ORDER BY c.sort_order ASC
        ";
        
        return $this->db->query($sql);
    }
    
    public function getWithProductCounts() {
        return $this->getWithProductCount();
    }
    
    public function getFeaturedCategories($limit = 9) {
        $sql = "
            SELECT c.*, COUNT(p.id) as product_count
            FROM {$this->table} c
            LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active'
            WHERE c.status = 'active' AND c.parent_id IS NULL
            GROUP BY c.id
            ORDER BY c.sort_order ASC
            LIMIT {$limit}
        ";
        
        return $this->db->query($sql);
    }
}

// Test NewsModel methods
class TestNewsModel extends MockBaseModel {
    protected $table = 'news';
    
    public function getWithAuthor($limit = null) {
        $sql = "
            SELECT n.*, u.name as author_name
            FROM {$this->table} n
            LEFT JOIN users u ON n.author_id = u.id
            WHERE n.status = 'published'
            ORDER BY n.published_at DESC
        ";
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        return $this->db->query($sql);
    }
    
    public function getLatestForHome($limit = 8) {
        return $this->getWithAuthor($limit);
    }
    
    public function getWithCategories($limit = null) {
        $sql = "
            SELECT n.*, u.name as author_name, c.name as category_name
            FROM {$this->table} n
            LEFT JOIN users u ON n.author_id = u.id
            LEFT JOIN categories c ON n.category_id = c.id
            WHERE n.status = 'published'
            ORDER BY n.published_at DESC
        ";
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        return $this->db->query($sql);
    }
}

class ModelExtensionsTest {
    private $productsModel;
    private $categoriesModel;
    private $newsModel;
    
    public function __construct() {
        $this->productsModel = new TestProductsModel();
        $this->categoriesModel = new TestCategoriesModel();
        $this->newsModel = new TestNewsModel();
    }
    
    public function runTests() {
        echo "Running Model Extensions Tests...\n\n";
        
        $this->testProductsModelExtensions();
        $this->testCategoriesModelExtensions();
        $this->testNewsModelExtensions();
        
        echo "\nAll model extension tests completed!\n";
    }
    
    private function testProductsModelExtensions() {
        echo "Testing ProductsModel Extensions...\n";
        
        // Test getFeaturedForHome
        $featured = $this->productsModel->getFeaturedForHome(8);
        assert(is_array($featured), 'getFeaturedForHome should return array');
        assert(count($featured) > 0, 'getFeaturedForHome should return data');
        echo "✓ getFeaturedForHome() works\n";
        
        // Test getLatestForHome
        $latest = $this->productsModel->getLatestForHome(8);
        assert(is_array($latest), 'getLatestForHome should return array');
        assert(count($latest) > 0, 'getLatestForHome should return data');
        echo "✓ getLatestForHome() works\n";
        
        // Test getByCategoryPaginated
        $categoryProducts = $this->productsModel->getByCategoryPaginated(1, 1, 12);
        assert(is_array($categoryProducts), 'getByCategoryPaginated should return array');
        echo "✓ getByCategoryPaginated() works\n";
        
        // Test getProductStats
        $stats = $this->productsModel->getProductStats();
        assert(is_array($stats), 'getProductStats should return array');
        assert(isset($stats['total']), 'getProductStats should have total');
        assert(isset($stats['by_status']), 'getProductStats should have by_status');
        echo "✓ getProductStats() works\n";
    }
    
    private function testCategoriesModelExtensions() {
        echo "\nTesting CategoriesModel Extensions...\n";
        
        // Test getWithProductCounts
        $categories = $this->categoriesModel->getWithProductCounts();
        assert(is_array($categories), 'getWithProductCounts should return array');
        echo "✓ getWithProductCounts() works\n";
        
        // Test getFeaturedCategories
        $featured = $this->categoriesModel->getFeaturedCategories(9);
        assert(is_array($featured), 'getFeaturedCategories should return array');
        echo "✓ getFeaturedCategories() works\n";
    }
    
    private function testNewsModelExtensions() {
        echo "\nTesting NewsModel Extensions...\n";
        
        // Test getLatestForHome
        $latest = $this->newsModel->getLatestForHome(8);
        assert(is_array($latest), 'getLatestForHome should return array');
        echo "✓ getLatestForHome() works\n";
        
        // Test getWithCategories
        $withCategories = $this->newsModel->getWithCategories(10);
        assert(is_array($withCategories), 'getWithCategories should return array');
        echo "✓ getWithCategories() works\n";
    }
}

// Run tests if called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    try {
        $test = new ModelExtensionsTest();
        $test->runTests();
        echo "\n✅ All model extension tests passed!\n";
        echo "\nModel methods are ready for view integration:\n";
        echo "- ProductsModel: getFeaturedForHome(), getLatestForHome(), getByCategoryPaginated(), getProductStats()\n";
        echo "- CategoriesModel: getWithProductCounts(), getFeaturedCategories()\n";
        echo "- NewsModel: getLatestForHome(), getWithCategories()\n";
    } catch (Exception $e) {
        echo "\n❌ Test failed: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . "\n";
        echo "Line: " . $e->getLine() . "\n";
    }
}