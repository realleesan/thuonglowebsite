<?php
/**
 * Categories Model
 * Handles category data operations with database
 */

require_once __DIR__ . '/BaseModel.php';

class CategoriesModel extends BaseModel {
    protected $table = 'categories';
    protected $fillable = [
        'name', 'slug', 'description', 'image', 'parent_id', 
        'sort_order', 'status'
    ];
    
    /**
     * Get all active categories
     */
    public function getActive() {
        return $this->where('status', 'active')
                   ->orderBy('sort_order', 'ASC')
                   ->get();
    }
    
    /**
     * Get category by slug
     */
    public function getBySlug($slug) {
        return $this->findBy('slug', $slug);
    }
    
    /**
     * Get categories with product count
     */
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
    
    /**
     * Get parent categories (top level)
     */
    public function getParentCategories() {
        return $this->where('parent_id', null)
                   ->where('status', 'active')
                   ->orderBy('sort_order', 'ASC')
                   ->get();
    }
    
    /**
     * Get child categories
     */
    public function getChildCategories($parentId) {
        return $this->where('parent_id', $parentId)
                   ->where('status', 'active')
                   ->orderBy('sort_order', 'ASC')
                   ->get();
    }
    
    /**
     * Get category tree (hierarchical structure)
     */
    public function getCategoryTree() {
        $categories = $this->getActive();
        return $this->buildTree($categories);
    }
    
    /**
     * Build hierarchical tree from flat array
     */
    private function buildTree($categories, $parentId = null) {
        $tree = [];
        
        foreach ($categories as $category) {
            if ($category['parent_id'] == $parentId) {
                $category['children'] = $this->buildTree($categories, $category['id']);
                $tree[] = $category;
            }
        }
        
        return $tree;
    }
    
    /**
     * Create category with auto-generated slug
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
     * Update sort order
     */
    public function updateSortOrder($categoryId, $sortOrder) {
        return $this->update($categoryId, ['sort_order' => $sortOrder]);
    }
    
    /**
     * Get category statistics
     */
    public function getStats() {
        $stats = [];
        
        // Total categories
        $stats['total'] = $this->count();
        
        // Active categories
        $activeCount = $this->db->query("SELECT COUNT(*) as count FROM {$this->table} WHERE status = 'active'");
        $stats['active'] = $activeCount[0]['count'] ?? 0;
        
        // Parent categories
        $parentCount = $this->db->query("SELECT COUNT(*) as count FROM {$this->table} WHERE parent_id IS NULL");
        $stats['parent_categories'] = $parentCount[0]['count'] ?? 0;
        
        // Categories with products
        $withProductsCount = $this->db->query("
            SELECT COUNT(DISTINCT c.id) as count 
            FROM {$this->table} c 
            INNER JOIN products p ON c.id = p.category_id 
            WHERE p.status = 'active'
        ");
        $stats['with_products'] = $withProductsCount[0]['count'] ?? 0;
        
        return $stats;
    }
    
    /**
     * Get categories with product counts (alias for existing method)
     */
    public function getWithProductCounts() {
        return $this->getWithProductCount();
    }
    
    /**
     * Get featured categories for home page
     */
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
    
    /**
     * Search categories
     */
    public function searchCategories($query) {
        return $this->search($query, ['name', 'description']);
    }
}