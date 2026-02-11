<?php
/**
 * Products Model
 * Handles product data operations with database
 */

require_once __DIR__ . '/BaseModel.php';

class ProductsModel extends BaseModel {
    protected $table = 'products';
    protected $fillable = [
        'name', 'slug', 'category_id', 'price', 'sale_price', 'stock', 'sku',
        'status', 'type', 'description', 'short_description', 'image', 'gallery',
        'meta_title', 'meta_description', 'featured', 'digital', 'downloadable',
        'download_limit', 'download_expiry', 'weight', 'dimensions'
    ];
    
    /**
     * Get products with category information
     */
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
    
    /**
     * Get products by category
     */
    public function getByCategory($categoryId, $limit = null) {
        $query = $this->where('category_id', $categoryId)
                     ->where('status', 'active')
                     ->orderBy('created_at', 'DESC');
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->get();
    }
    
    /**
     * Get products by type
     */
    public function getByType($type, $limit = null) {
        $query = $this->where('type', $type)
                     ->where('status', 'active')
                     ->orderBy('created_at', 'DESC');
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->get();
    }
    
    /**
     * Get featured products
     */
    public function getFeatured($limit = 6) {
        return $this->where('featured', true)
                   ->where('status', 'active')
                   ->orderBy('created_at', 'DESC')
                   ->limit($limit)
                   ->get();
    }
    
    /**
     * Get popular products (by sales count)
     */
    public function getPopular($limit = 6) {
        return $this->where('status', 'active')
                   ->orderBy('sales_count', 'DESC')
                   ->limit($limit)
                   ->get();
    }
    
    /**
     * Search products
     */
    public function searchProducts($query, $filters = []) {
        $sql = "SELECT p.*, c.name as category_name FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.status = 'active'";
        $bindings = [];
        
        // Search in name and description
        if (!empty($query)) {
            $sql .= " AND (p.name LIKE :search OR p.description LIKE :search)";
            $bindings['search'] = "%{$query}%";
        }
        
        // Filter by category
        if (!empty($filters['category_id'])) {
            $sql .= " AND p.category_id = :category_id";
            $bindings['category_id'] = $filters['category_id'];
        }
        
        // Filter by type
        if (!empty($filters['type'])) {
            $sql .= " AND p.type = :type";
            $bindings['type'] = $filters['type'];
        }
        
        // Filter by price range
        if (!empty($filters['min_price'])) {
            $sql .= " AND p.price >= :min_price";
            $bindings['min_price'] = $filters['min_price'];
        }
        
        if (!empty($filters['max_price'])) {
            $sql .= " AND p.price <= :max_price";
            $bindings['max_price'] = $filters['max_price'];
        }
        
        // Sort
        $sortBy = $filters['sort'] ?? 'created_at';
        $sortOrder = $filters['order'] ?? 'DESC';
        $sql .= " ORDER BY p.{$sortBy} {$sortOrder}";
        
        // Limit
        $limit = $filters['limit'] ?? 50;
        $sql .= " LIMIT {$limit}";
        
        return $this->db->query($sql, $bindings);
    }
    
    /**
     * Get product by slug
     */
    public function getBySlug($slug) {
        $sql = "
            SELECT p.*, c.name as category_name, c.slug as category_slug
            FROM {$this->table} p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.slug = ? AND p.status = 'active'
        ";
        
        $result = $this->db->query($sql, [$slug]);
        return $result ? $result[0] : null;
    }
    
    /**
     * Update product views
     */
    public function incrementViews($productId) {
        $sql = "UPDATE {$this->table} SET views = views + 1 WHERE id = ?";
        return $this->db->execute($sql, [$productId]);
    }
    
    /**
     * Update sales count
     */
    public function incrementSales($productId, $quantity = 1) {
        $sql = "UPDATE {$this->table} SET sales_count = sales_count + ? WHERE id = ?";
        return $this->db->execute($sql, [$quantity, $productId]);
    }
    
    /**
     * Update stock
     */
    public function updateStock($productId, $quantity, $operation = 'decrease') {
        $operator = $operation === 'increase' ? '+' : '-';
        $sql = "UPDATE {$this->table} SET stock = stock {$operator} ? WHERE id = ?";
        return $this->db->execute($sql, [$quantity, $productId]);
    }
    
    /**
     * Get related products
     */
    public function getRelated($productId, $categoryId, $limit = 4) {
        return $this->where('category_id', $categoryId)
                   ->where('id', '!=', $productId)
                   ->where('status', 'active')
                   ->orderBy('sales_count', 'DESC')
                   ->limit($limit)
                   ->get();
    }
    
    /**
     * Get featured products for home page
     */
    public function getFeaturedForHome($limit = 8) {
        return $this->getFeatured($limit);
    }
    
    /**
     * Get latest products for home page
     */
    public function getLatestForHome($limit = 8) {
        return $this->getWithCategory($limit);
    }
    
    /**
     * Get products by category with pagination
     */
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
    
    /**
     * Get product statistics
     */
    public function getStats() {
        $stats = [];
        
        // Total products
        $stats['total'] = $this->count();
        
        // Products by status
        $statuses = ['active', 'inactive', 'draft', 'out_of_stock'];
        foreach ($statuses as $status) {
            $count = $this->db->query("SELECT COUNT(*) as count FROM {$this->table} WHERE status = ?", [$status]);
            $stats['by_status'][$status] = $count[0]['count'] ?? 0;
        }
        
        // Products by type
        $types = ['data_nguon_hang', 'khoa_hoc', 'tool', 'dich_vu', 'van_chuyen'];
        foreach ($types as $type) {
            $count = $this->db->query("SELECT COUNT(*) as count FROM {$this->table} WHERE type = ?", [$type]);
            $stats['by_type'][$type] = $count[0]['count'] ?? 0;
        }
        
        // Total sales and revenue
        $salesData = $this->db->query("
            SELECT 
                SUM(sales_count) as total_sales,
                AVG(price) as avg_price,
                MAX(price) as max_price,
                MIN(price) as min_price
            FROM {$this->table} 
            WHERE status = 'active'
        ");
        
        $stats['sales'] = $salesData[0] ?? [];
        
        return $stats;
    }
    
    /**
     * Get product statistics for admin views (alias for getStats)
     */
    public function getProductStats() {
        return $this->getStats();
    }
    
    /**
     * Create product with auto-generated slug
     */
    public function create($data) {
        // Generate slug if not provided
        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = $this->generateUniqueSlug($data['name']);
        }
        
        return parent::create($data);
    }
    
    /**
     * Generate unique slug
     */
    private function generateUniqueSlug($name) {
        $slug = $this->generateSlug($name);
        $originalSlug = $slug;
        $counter = 1;
        
        while ($this->findBy('slug', $slug)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
    
    /**
     * Generate slug from string
     */
    private function generateSlug($string) {
        // Convert to lowercase
        $slug = strtolower($string);
        
        // Replace Vietnamese characters
        $vietnamese = [
            'à', 'á', 'ạ', 'ả', 'ã', 'â', 'ầ', 'ấ', 'ậ', 'ẩ', 'ẫ', 'ă', 'ằ', 'ắ', 'ặ', 'ẳ', 'ẵ',
            'è', 'é', 'ẹ', 'ẻ', 'ẽ', 'ê', 'ề', 'ế', 'ệ', 'ể', 'ễ',
            'ì', 'í', 'ị', 'ỉ', 'ĩ',
            'ò', 'ó', 'ọ', 'ỏ', 'õ', 'ô', 'ồ', 'ố', 'ộ', 'ổ', 'ỗ', 'ơ', 'ờ', 'ớ', 'ợ', 'ở', 'ỡ',
            'ù', 'ú', 'ụ', 'ủ', 'ũ', 'ư', 'ừ', 'ứ', 'ự', 'ử', 'ữ',
            'ỳ', 'ý', 'ỵ', 'ỷ', 'ỹ',
            'đ'
        ];
        
        $ascii = [
            'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a',
            'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e',
            'i', 'i', 'i', 'i', 'i',
            'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o',
            'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u',
            'y', 'y', 'y', 'y', 'y',
            'd'
        ];
        
        $slug = str_replace($vietnamese, $ascii, $slug);
        
        // Replace spaces and special characters with hyphens
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        
        // Remove leading/trailing hyphens
        $slug = trim($slug, '-');
        
        return $slug;
    }
}