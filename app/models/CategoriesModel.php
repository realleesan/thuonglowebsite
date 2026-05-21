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
        'sort_order', 'status', 'featured', 'show_in_filter', 'type', 'icon'
    ];
    
    /**
     * Get all active categories
     */
    public function getActive() {
        // Use direct query to avoid query builder issues
        return $this->query("SELECT * FROM categories WHERE status = 'active' ORDER BY id ASC") ?? [];
    }

    /**
     * Get all active categories for filter display (show_in_filter = 1)
     */
    public function getActiveForFilter() {
        return $this->query("SELECT * FROM categories WHERE status = 'active' AND show_in_filter = 1 AND (type != 'news' OR type IS NULL) ORDER BY sort_order ASC") ?? [];
    }
    
    /**
     * Get category by slug
     */
    public function getBySlug($slug) {
        return $this->findBy('slug', $slug);
    }
    
    /**
     * Get categories with product count (for filter display)
     */
    public function getWithProductCount() {
        $sql = "
            SELECT c.*, COUNT(p.id) as product_count
            FROM {$this->table} c
            LEFT JOIN products p ON c.id = p.category_id
            WHERE c.status = 'active' AND c.show_in_filter = 1 AND (c.type != 'news' OR c.type IS NULL)
            GROUP BY c.id
            ORDER BY c.sort_order ASC
        ";
        
        return $this->db->query($sql);
    }
    
    /**
     * Get parent categories (top level) - hiển thị ở bộ lọc
     */
    public function getParentCategories() {
        return $this->query("SELECT * FROM {$this->table} WHERE parent_id IS NULL AND status = 'active' AND show_in_filter = 1 AND (type != 'news' OR type IS NULL) ORDER BY sort_order ASC") ?? [];
    }

    /**
     * Get parent categories for filter/display (show_in_filter = 1)
     */
    public function getParentCategoriesForFilter() {
        return $this->query("SELECT * FROM {$this->table} WHERE parent_id IS NULL AND status = 'active' AND show_in_filter = 1 AND (type != 'news' OR type IS NULL) ORDER BY sort_order ASC") ?? [];
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
     * @param array $categories Danh sách danh mục phẳng
     * @param int|null $parentId ID của danh mục cha
     * @param int $maxDepth Độ sâu tối đa (mặc định 10)
     * @param int $currentDepth Độ sâu hiện tại (để kiểm tra giới hạn)
     * @param array $visitedIds Mảng theo dõi các ID đã duyệt (tránh circular reference)
     */
    public function buildTree($categories, $parentId = null, $maxDepth = 10, $currentDepth = 0, $visitedIds = []) {
        // Dừng nếu vượt quá độ sâu tối đa
        if ($currentDepth >= $maxDepth) {
            return [];
        }

        $tree = [];

        foreach ($categories as $category) {
            if ($category['parent_id'] == $parentId) {
                // Kiểm tra circular reference
                if (in_array($category['id'], $visitedIds)) {
                    error_log("Circular reference detected in buildTree at category ID: {$category['id']}");
                    continue;
                }

                $newVisitedIds = array_merge($visitedIds, [$category['id']]);
                $category['children'] = $this->buildTree($categories, $category['id'], $maxDepth, $currentDepth + 1, $newVisitedIds);
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
     * Get ALL active categories with product counts (for categories page - exclude news categories)
     */
    public function getAllWithProductCounts() {
        $sql = "
            SELECT c.*, COUNT(p.id) as product_count
            FROM {$this->table} c
            LEFT JOIN products p ON c.id = p.category_id
            WHERE c.status = 'active' AND (c.type != 'news' OR c.type IS NULL)
            GROUP BY c.id
            ORDER BY c.sort_order ASC
        ";

        return $this->db->query($sql);
    }

    /**
     * Get featured categories for home page (featured = 1 and show_in_filter = 1)
     */
    public function getFeaturedCategories($limit = 9) {
        $sql = "
            SELECT c.*, COUNT(p.id) as product_count
            FROM {$this->table} c
            LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active'
            WHERE c.status = 'active' AND c.featured = 1 AND c.show_in_filter = 1 AND (c.type != 'news' OR c.type IS NULL)
            GROUP BY c.id
            ORDER BY c.sort_order ASC
            LIMIT {$limit}
        ";

        return $this->db->query($sql);
    }

    /**
     * Get news categories for news pages only
     */
    public function getNewsCategories() {
        return $this->query("SELECT * FROM categories WHERE type = 'news' AND status = 'active' ORDER BY name ASC") ?? [];
    }

    /**
     * Search categories
     */
    public function searchCategories($query) {
        return $this->search($query, ['name', 'description']);
    }

    /**
     * Lấy tất cả ID danh mục con (đệ quy) - dùng cho filter sản phẩm
     * @param int $parentId ID danh mục cha
     * @param array $visitedIds Mảng theo dõi các ID đã duyệt (tránh circular reference)
     * @param int $maxDepth Độ sâu tối đa (mặc định 10)
     * @param int $currentDepth Độ sâu hiện tại
     */
    public function getAllChildCategoryIds($parentId, $visitedIds = [], $maxDepth = 10, $currentDepth = 0) {
        // Kiểm tra circular reference
        if (in_array($parentId, $visitedIds)) {
            error_log("Circular reference detected in getAllChildCategoryIds at category ID: {$parentId}");
            return [];
        }

        // Dừng nếu vượt quá độ sâu tối đa
        if ($currentDepth >= $maxDepth) {
            return [$parentId];
        }

        $allIds = [$parentId];
        $newVisitedIds = array_merge($visitedIds, [$parentId]);

        // Lấy danh mục con trực tiếp
        $children = $this->query(
            "SELECT id FROM {$this->table} WHERE parent_id = ? AND status = 'active'",
            [$parentId]
        );

        if (!empty($children)) {
            foreach ($children as $child) {
                $childId = $child['id'];
                // Kiểm tra xem đã duyệt chưa
                if (in_array($childId, $newVisitedIds)) {
                    continue;
                }
                $allIds[] = $childId;
                // Đệ quy lấy con của con
                $grandChildren = $this->getAllChildCategoryIds($childId, $newVisitedIds, $maxDepth, $currentDepth + 1);
                // Loại bỏ ID đầu tiên (chính nó) vì đã thêm rồi
                array_shift($grandChildren);
                $allIds = array_merge($allIds, $grandChildren);
            }
            $allIds[] = $childId;
            // Đệ quy lấy con của con
            $grandChildren = $this->getAllChildCategoryIds($childId, $newVisitedIds, $maxDepth, $currentDepth + 1);
            // Loại bỏ ID đầu tiên (chính nó) vì đã thêm rồi
            array_shift($grandChildren);
        }

        return array_unique($allIds);
    }

    /**
     * Check if category has products
     */
    public function hasProducts($categoryId) {
        try {
            $sql = "SELECT COUNT(*) as count FROM products WHERE category_id = ?";
            $result = parent::query($sql, [$categoryId]);
            if ($result && isset($result[0]['count'])) {
                return (int)$result[0]['count'] > 0;
            }
            return false;
        } catch (\Exception $e) {
            error_log("Error in hasProducts: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if category has child categories
     */
    public function hasChildCategories($categoryId) {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE parent_id = ? AND status = 'active'";
            $result = parent::query($sql, [$categoryId]);
            if ($result && isset($result[0]['count'])) {
                return (int)$result[0]['count'] > 0;
            }
            return false;
        } catch (\Exception $e) {
            error_log("Error in hasChildCategories: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get product count for a category
     */
    public function getProductCount($categoryId) {
        try {
            $sql = "SELECT COUNT(*) as count FROM products WHERE category_id = ?";
            $result = parent::query($sql, [$categoryId]);
            if ($result && isset($result[0]['count'])) {
                return (int)$result[0]['count'];
            }
            return 0;
        } catch (\Exception $e) {
            error_log("Error in getProductCount: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get child categories count for a category
     */
    public function getChildCategoriesCount($categoryId) {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE parent_id = ? AND status = 'active'";
            $result = parent::query($sql, [$categoryId]);
            if ($result && isset($result[0]['count'])) {
                return (int)$result[0]['count'];
            }
            return 0;
        } catch (\Exception $e) {
            error_log("Error in getChildCategoriesCount: " . $e->getMessage());
            return 0;
        }
    }
}