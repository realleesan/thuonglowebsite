<?php
/**
 * Products Model
 * Handles product data operations with database
 */

require_once __DIR__ . '/BaseModel.php';

class ProductsModel extends BaseModel {
    protected $table = 'products';
    protected $fillable = [
        'name', 'slug', 'category_id', 'brand_id', 'price', 'sale_price', 'stock', 'sku',
        'status', 'type', 'description', 'short_description', 'image', 'gallery',
        'meta_title', 'meta_description', 'featured', 'digital', 'downloadable',
        'download_limit', 'download_expiry', 'weight', 'dimensions',
        // Data fields
        'record_count', 'data_size', 'data_type', 'data_format', 'data_source',
        'reliability', 'quota', 'quota_per_usage',
        // Supplier fields
        'supplier_name', 'supplier_title', 'supplier_bio', 'supplier_avatar', 'supplier_social',
        // JSON fields
        'benefits', 'data_structure',
        // Additional fields
        'expiry_days', 'views', 'sales_count', 'rating_average', 'rating_count',
        'created_at', 'updated_at'
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
     * Get products by category (bao gồm cả sản phẩm từ danh mục con)
     */
    public function getByCategory($categoryId, $limit = null) {
        // Lấy tất cả ID danh mục con (bao gồm cả chính nó)
        $categoryIds = [$categoryId];
        if (class_exists('CategoriesModel')) {
            $categoriesModel = new CategoriesModel();
            $categoryIds = $categoriesModel->getAllChildCategoryIds($categoryId);
        }

        // Tạo placeholders cho IN clause
        $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));

        $sql = "
            SELECT p.*, c.name as category_name, c.slug as category_slug
            FROM {$this->table} p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.category_id IN ({$placeholders}) AND p.status = 'active'
            ORDER BY p.created_at DESC
        ";

        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }

        return $this->db->query($sql, $categoryIds);
    }
    
    /**
     * Get products by brand
     */
    public function getByBrand($brandId, $limit = null) {
        $sql = "
            SELECT p.*, c.name as category_name, c.slug as category_slug
            FROM {$this->table} p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.brand_id = ? AND p.status = 'active'
            ORDER BY p.created_at DESC
        ";

        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }

        return $this->db->query($sql, [$brandId]);
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
        $sql = "
            SELECT p.*, c.name as category_name, c.slug as category_slug
            FROM {$this->table} p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.status = 'active'
            ORDER BY p.created_at DESC
            LIMIT {$limit}
        ";
        
        return $this->db->query($sql);
    }
    
    /**
     * Get budget products (cheapest) for home page
     */
    public function getBudgetForHome($limit = 8) {
        $sql = "
            SELECT p.*, c.name as category_name, c.slug as category_slug
            FROM {$this->table} p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.status = 'active'
            ORDER BY p.price ASC, p.created_at DESC
            LIMIT {$limit}
        ";
        
        return $this->db->query($sql);
    }
    
    /**
     * Get sale products for home page
     */
    public function getSaleForHome($limit = 8) {
        $sql = "
            SELECT p.*, c.name as category_name, c.slug as category_slug
            FROM {$this->table} p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.status = 'active' AND (p.sale_price IS NOT NULL AND p.sale_price > 0 AND p.sale_price < p.price)
            ORDER BY (p.price - p.sale_price) DESC, p.created_at DESC
            LIMIT {$limit}
        ";
        
        return $this->db->query($sql);
    }
    
    /**
     * Get products by category with pagination (bao gồm cả sản phẩm từ danh mục con)
     */
    public function getByCategoryPaginated($categoryId, $page = 1, $limit = 12) {
        $offset = ($page - 1) * $limit;

        // Lấy tất cả ID danh mục con (bao gồm cả chính nó)
        $categoryIds = [$categoryId];
        if (class_exists('CategoriesModel')) {
            $categoriesModel = new CategoriesModel();
            $categoryIds = $categoriesModel->getAllChildCategoryIds($categoryId);
        }

        // Tạo placeholders cho IN clause
        $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));

        $sql = "
            SELECT p.*, c.name as category_name, c.slug as category_slug
            FROM {$this->table} p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.category_id IN ({$placeholders}) AND p.status = 'active'
            ORDER BY p.created_at DESC
            LIMIT {$limit} OFFSET {$offset}
        ";

        return $this->db->query($sql, $categoryIds);
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
    
    /**
     * Get products belonging to a category filtered by display type
     */
    public function getByCategoryAndType($categoryId, $type, $limit = 8) {
        $categoryIds = [$categoryId];
        if (class_exists('CategoriesModel')) {
            $categoriesModel = new CategoriesModel();
            $categoryIds = $categoriesModel->getAllChildCategoryIds($categoryId);
        }

        // Create placeholders for IN clause
        $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
        
        $sql = "
            SELECT p.*, c.name as category_name, c.slug as category_slug
            FROM {$this->table} p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.category_id IN ({$placeholders}) AND p.status = 'active'
        ";
        
        $bindings = $categoryIds;
        
        switch ($type) {
            case 'featured':
                $sql .= " AND p.featured = 1 ORDER BY p.created_at DESC";
                break;
            case 'budget':
                $sql .= " ORDER BY p.price ASC, p.created_at DESC";
                break;
            case 'sale':
                $sql .= " AND (p.sale_price IS NOT NULL AND p.sale_price > 0 AND p.sale_price < p.price) ORDER BY (p.price - p.sale_price) DESC, p.created_at DESC";
                break;
            case 'latest':
            default:
                $sql .= " ORDER BY p.created_at DESC";
                break;
        }
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        try {
            return $this->db->query($sql, $bindings) ?: [];
        } catch (Exception $e) {
            error_log("ProductsModel::getByCategoryAndType error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get product reviews
     */
    public function getProductReviews($productId): array {
        $sql = "SELECT * FROM product_reviews WHERE product_id = ? AND status = 'approved' ORDER BY created_at DESC";
        return $this->db->query($sql, [$productId]) ?: [];
    }

    public function getLatest($limit = 8) {
        return $this->getLatestForHome($limit);
    }

    public function getBudget($limit = 8) {
        return $this->getBudgetForHome($limit);
    }

    public function getSale($limit = 8) {
        return $this->getSaleForHome($limit);
    }

    /**
     * Get category IDs associated with a product
     */
    public function getProductCategories($productId): array {
        try {
            $sql = "SELECT category_id FROM product_categories WHERE product_id = ?";
            $rows = $this->db->query($sql, [$productId]);
            if (empty($rows)) {
                // Fallback to single category_id if table or rows do not exist/are empty
                $prod = $this->find($productId);
                return !empty($prod['category_id']) ? [(int)$prod['category_id']] : [];
            }
            return array_map('intval', array_column($rows, 'category_id'));
        } catch (\Exception $e) {
            // Fallback in case table does not exist yet
            $prod = $this->find($productId);
            return !empty($prod['category_id']) ? [(int)$prod['category_id']] : [];
        }
    }

    /**
     * Update category associations for a product
     */
    public function updateProductCategories($productId, array $categoryIds): bool {
        try {
            // Delete old mappings
            $this->db->query("DELETE FROM product_categories WHERE product_id = ?", [$productId]);
            
            if (empty($categoryIds)) {
                return true;
            }
            
            // Insert new mappings
            foreach ($categoryIds as $catId) {
                $this->db->query("INSERT IGNORE INTO product_categories (product_id, category_id) VALUES (?, ?)", [$productId, (int)$catId]);
            }
            
            return true;
        } catch (\Exception $e) {
            error_log("Error in updateProductCategories: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get count of products matching specific filters
     */
    public function getFilteredProductsCount(array $filters): int {
        $conditions = [];
        $bindings = [];

        $conditions[] = "p.status = 'active'";

        // 1. Category Filter
        if (!empty($filters['category_id'])) {
            $catIds = is_array($filters['category_id']) ? $filters['category_id'] : [(int)$filters['category_id']];
            $expandedIds = [];
            if (class_exists('CategoriesModel')) {
                $categoriesModel = new CategoriesModel();
                foreach ($catIds as $catId) {
                    $expandedIds[] = (int)$catId;
                    $childIds = $categoriesModel->getAllChildCategoryIds($catId);
                    if (is_array($childIds)) {
                        $expandedIds = array_merge($expandedIds, $childIds);
                    }
                }
            } else {
                $expandedIds = array_map('intval', $catIds);
            }
            $expandedIds = array_unique($expandedIds);
            
            if (!empty($expandedIds)) {
                $placeholders1 = [];
                $placeholders2 = [];
                foreach ($expandedIds as $i => $id) {
                    $key1 = "cat_a_" . $i;
                    $key2 = "cat_b_" . $i;
                    $placeholders1[] = ":" . $key1;
                    $placeholders2[] = ":" . $key2;
                    $bindings[$key1] = $id;
                    $bindings[$key2] = $id;
                }
                $placeholderStr1 = implode(',', $placeholders1);
                $placeholderStr2 = implode(',', $placeholders2);
                $conditions[] = "(p.category_id IN ({$placeholderStr1}) OR EXISTS (
                    SELECT 1 FROM product_categories pc 
                    WHERE pc.product_id = p.id AND pc.category_id IN ({$placeholderStr2})
                ))";
            }
        }

        // 2. Brand Filter
        if (!empty($filters['brand_id'])) {
            $brandIds = is_array($filters['brand_id']) ? $filters['brand_id'] : [(int)$filters['brand_id']];
            $placeholders = [];
            foreach ($brandIds as $i => $id) {
                $key = "brand_" . $i;
                $placeholders[] = ":" . $key;
                $bindings[$key] = (int)$id;
            }
            $conditions[] = "p.brand_id IN (" . implode(',', $placeholders) . ")";
        }

        // 3. Price type (free vs paid)
        if (!empty($filters['price_type'])) {
            if ($filters['price_type'] === 'free') {
                $conditions[] = "(p.price = 0 OR p.sale_price = 0)";
            } elseif ($filters['price_type'] === 'paid') {
                $conditions[] = "(p.price > 0 OR p.sale_price > 0)";
            }
        }

        // 4. Price range
        if (isset($filters['min_price']) && $filters['min_price'] !== '') {
            $conditions[] = "(CASE WHEN p.sale_price > 0 THEN p.sale_price ELSE p.price END) >= :min_price";
            $bindings['min_price'] = (float)$filters['min_price'];
        }
        if (isset($filters['max_price']) && $filters['max_price'] !== '') {
            $conditions[] = "(CASE WHEN p.sale_price > 0 THEN p.sale_price ELSE p.price END) <= :max_price";
            $bindings['max_price'] = (float)$filters['max_price'];
        }

        // 5. Supplier filter
        if (!empty($filters['supplier'])) {
            $conditions[] = "p.supplier_name LIKE :supplier";
            $bindings['supplier'] = "%" . $filters['supplier'] . "%";
        }

        // 6. Search keyword
        if (!empty($filters['search'])) {
            $conditions[] = "(p.name LIKE :search OR p.description LIKE :search)";
            $bindings['search'] = "%" . $filters['search'] . "%";
        }

        $whereClause = "";
        if (!empty($conditions)) {
            $whereClause = "WHERE " . implode(' AND ', $conditions);
        }

        $sql = "SELECT COUNT(DISTINCT p.id) as count FROM {$this->table} p {$whereClause}";
        $result = $this->db->query($sql, $bindings);
        return (int)($result[0]['count'] ?? 0);
    }

    /**
     * Get paginated products matching specific filters
     */
    public function getFilteredProducts(array $filters, int $limit, int $offset): array {
        $conditions = [];
        $bindings = [];

        $conditions[] = "p.status = 'active'";

        // 1. Category Filter
        if (!empty($filters['category_id'])) {
            $catIds = is_array($filters['category_id']) ? $filters['category_id'] : [(int)$filters['category_id']];
            $expandedIds = [];
            if (class_exists('CategoriesModel')) {
                $categoriesModel = new CategoriesModel();
                foreach ($catIds as $catId) {
                    $expandedIds[] = (int)$catId;
                    $childIds = $categoriesModel->getAllChildCategoryIds($catId);
                    if (is_array($childIds)) {
                        $expandedIds = array_merge($expandedIds, $childIds);
                    }
                }
            } else {
                $expandedIds = array_map('intval', $catIds);
            }
            $expandedIds = array_unique($expandedIds);
            
            if (!empty($expandedIds)) {
                $placeholders1 = [];
                $placeholders2 = [];
                foreach ($expandedIds as $i => $id) {
                    $key1 = "cat_a_" . $i;
                    $key2 = "cat_b_" . $i;
                    $placeholders1[] = ":" . $key1;
                    $placeholders2[] = ":" . $key2;
                    $bindings[$key1] = $id;
                    $bindings[$key2] = $id;
                }
                $placeholderStr1 = implode(',', $placeholders1);
                $placeholderStr2 = implode(',', $placeholders2);
                $conditions[] = "(p.category_id IN ({$placeholderStr1}) OR EXISTS (
                    SELECT 1 FROM product_categories pc 
                    WHERE pc.product_id = p.id AND pc.category_id IN ({$placeholderStr2})
                ))";
            }
        }

        // 2. Brand Filter
        if (!empty($filters['brand_id'])) {
            $brandIds = is_array($filters['brand_id']) ? $filters['brand_id'] : [(int)$filters['brand_id']];
            $placeholders = [];
            foreach ($brandIds as $i => $id) {
                $key = "brand_" . $i;
                $placeholders[] = ":" . $key;
                $bindings[$key] = (int)$id;
            }
            $conditions[] = "p.brand_id IN (" . implode(',', $placeholders) . ")";
        }

        // 3. Price type (free vs paid)
        if (!empty($filters['price_type'])) {
            if ($filters['price_type'] === 'free') {
                $conditions[] = "(p.price = 0 OR p.sale_price = 0)";
            } elseif ($filters['price_type'] === 'paid') {
                $conditions[] = "(p.price > 0 OR p.sale_price > 0)";
            }
        }

        // 4. Price range
        if (isset($filters['min_price']) && $filters['min_price'] !== '') {
            $conditions[] = "(CASE WHEN p.sale_price > 0 THEN p.sale_price ELSE p.price END) >= :min_price";
            $bindings['min_price'] = (float)$filters['min_price'];
        }
        if (isset($filters['max_price']) && $filters['max_price'] !== '') {
            $conditions[] = "(CASE WHEN p.sale_price > 0 THEN p.sale_price ELSE p.price END) <= :max_price";
            $bindings['max_price'] = (float)$filters['max_price'];
        }

        // 5. Supplier filter
        if (!empty($filters['supplier'])) {
            $conditions[] = "p.supplier_name LIKE :supplier";
            $bindings['supplier'] = "%" . $filters['supplier'] . "%";
        }

        // 6. Search keyword
        if (!empty($filters['search'])) {
            $conditions[] = "(p.name LIKE :search OR p.description LIKE :search)";
            $bindings['search'] = "%" . $filters['search'] . "%";
        }

        $whereClause = "";
        if (!empty($conditions)) {
            $whereClause = "WHERE " . implode(' AND ', $conditions);
        }

        // Ordering mapping
        $orderBy = $filters['order_by'] ?? 'post_date';
        $orderClause = "ORDER BY p.created_at DESC";
        switch ($orderBy) {
            case 'price_asc':
                $orderClause = "ORDER BY CASE WHEN p.sale_price > 0 THEN p.sale_price ELSE p.price END ASC";
                break;
            case 'price_desc':
                $orderClause = "ORDER BY CASE WHEN p.sale_price > 0 THEN p.sale_price ELSE p.price END DESC";
                break;
            case 'title_asc':
                $orderClause = "ORDER BY p.name ASC";
                break;
            case 'title_desc':
                $orderClause = "ORDER BY p.name DESC";
                break;
            case 'popular':
                $orderClause = "ORDER BY p.views DESC";
                break;
            case 'post_date':
            default:
                $orderClause = "ORDER BY p.created_at DESC";
                break;
        }

        $limitVal = (int)$limit;
        $offsetVal = (int)$offset;

        $sql = "
            SELECT p.*, c.name as category_name, c.slug as category_slug
            FROM {$this->table} p
            LEFT JOIN categories c ON p.category_id = c.id
            {$whereClause}
            {$orderClause}
            LIMIT {$limitVal} OFFSET {$offsetVal}
        ";

        return $this->db->query($sql, $bindings) ?? [];
    }
}