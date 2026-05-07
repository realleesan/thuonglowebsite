<?php
/**
 * Brands Model
 * Handles brand data operations with database
 */

require_once __DIR__ . '/BaseModel.php';

class BrandsModel extends BaseModel {
    protected $table = 'brands';
    protected $fillable = [
        'name', 'slug', 'description', 'image', 'website',
        'status', 'sort_order', 'show_in_filter', 'is_featured'
    ];
    
    /**
     * Get all active brands
     */
    public function getActive() {
        return $this->query("SELECT * FROM brands WHERE status = 'active' ORDER BY sort_order ASC, name ASC") ?? [];
    }

    /**
     * Get all brands for dropdown
     */
    public function getForDropdown() {
        return $this->query("SELECT id, name FROM brands WHERE status = 'active' ORDER BY name ASC") ?? [];
    }
    
    /**
     * Get brand by slug
     */
    public function getBySlug($slug) {
        return $this->findBy('slug', $slug);
    }
    
    /**
     * Get brands with product count
     */
    public function getWithProductCount() {
        $sql = "
            SELECT b.*, COUNT(p.id) as product_count
            FROM {$this->table} b
            LEFT JOIN products p ON b.id = p.brand_id
            WHERE b.status = 'active'
            GROUP BY b.id
            ORDER BY b.sort_order ASC, b.name ASC
        ";
        
        return $this->db->query($sql);
    }

    /**
     * Get brands for public filter/header dropdown
     */
    public function getForFilter() {
        $sql = "
            SELECT b.*, COUNT(p.id) as product_count
            FROM {$this->table} b
            LEFT JOIN products p ON b.id = p.brand_id AND p.status = 'active'
            WHERE b.status = 'active'
              AND b.show_in_filter = 1
            GROUP BY b.id
            ORDER BY b.sort_order ASC, b.name ASC
        ";

        return $this->db->query($sql) ?? [];
    }

    /**
     * Get featured brands for home page section
     */
    public function getFeatured($limit = 6) {
        $limit = max(1, (int)$limit);
        $sql = "
            SELECT b.*, COUNT(p.id) as product_count
            FROM {$this->table} b
            LEFT JOIN products p ON b.id = p.brand_id
            WHERE b.status = 'active'
              AND b.is_featured = 1
            GROUP BY b.id
            ORDER BY b.sort_order ASC, b.name ASC
            LIMIT {$limit}
        ";

        return $this->db->query($sql) ?? [];
    }
    
    /**
     * Create brand with auto-generated slug
     */
    public function create($data) {
        // Generate slug if not provided
        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = $this->generateUniqueSlug($data['name']);
        }
        
        return $this->db->table($this->table)->insert($data);
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
