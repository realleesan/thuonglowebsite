<?php
/**
 * News Model
 * Handles news and blog operations with database
 */

require_once __DIR__ . '/BaseModel.php';

class NewsModel extends BaseModel {
    protected $table = 'news';
    protected $fillable = [
        'title', 'slug', 'excerpt', 'content', 'image', 'status',
        'featured', 'author_id', 'category_id', 'meta_title', 
        'meta_description', 'published_at'
    ];
    
    /**
     * Get published news
     */
    public function getPublished($limit = null) {
        $query = $this->where('status', 'published')
                     ->where('published_at', '<=', date('Y-m-d H:i:s'))
                     ->orderBy('published_at', 'DESC');
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->get();
    }
    
    /**
     * Get featured news
     */
    public function getFeatured($limit = 5) {
        return $this->where('status', 'published')
                   ->where('featured', true)
                   ->where('published_at', '<=', date('Y-m-d H:i:s'))
                   ->orderBy('published_at', 'DESC')
                   ->limit($limit)
                   ->get();
    }
    
    /**
     * Get news by slug
     */
    public function getBySlug($slug) {
        $sql = "
            SELECT n.*, u.name as author_name
            FROM {$this->table} n
            LEFT JOIN users u ON n.author_id = u.id
            WHERE n.slug = ? AND n.status = 'published'
        ";
        
        $result = $this->db->query($sql, [$slug]);
        return $result ? $result[0] : null;
    }
    
    /**
     * Get news with author info
     */
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
    
    /**
     * Get related news
     */
    public function getRelated($newsId, $categoryId = null, $limit = 4) {
        $sql = "
            SELECT * FROM {$this->table}
            WHERE id != ? AND status = 'published'
        ";
        $bindings = [$newsId];
        
        if ($categoryId) {
            $sql .= " AND category_id = ?";
            $bindings[] = $categoryId;
        }
        
        $sql .= " ORDER BY published_at DESC LIMIT {$limit}";
        
        return $this->db->query($sql, $bindings);
    }
    
    /**
     * Increment views
     */
    public function incrementViews($newsId) {
        $sql = "UPDATE {$this->table} SET views = views + 1 WHERE id = ?";
        return $this->db->execute($sql, [$newsId]);
    }
    
    /**
     * Search news
     */
    public function searchNews($query, $limit = 20) {
        $sql = "
            SELECT n.*, u.name as author_name
            FROM {$this->table} n
            LEFT JOIN users u ON n.author_id = u.id
            WHERE n.status = 'published' 
            AND (n.title LIKE ? OR n.excerpt LIKE ? OR n.content LIKE ?)
            ORDER BY n.published_at DESC
            LIMIT {$limit}
        ";
        
        $searchTerm = "%{$query}%";
        return $this->db->query($sql, [$searchTerm, $searchTerm, $searchTerm]);
    }
    
    /**
     * Create news with auto-generated slug
     */
    public function create($data) {
        // Generate slug if not provided
        if (empty($data['slug']) && !empty($data['title'])) {
            $data['slug'] = $this->generateUniqueSlug($data['title']);
        }
        
        // Set published_at if not provided and status is published
        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }
        
        return parent::create($data);
    }
    
    /**
     * Publish news
     */
    public function publish($newsId) {
        return $this->update($newsId, [
            'status' => 'published',
            'published_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Unpublish news
     */
    public function unpublish($newsId) {
        return $this->update($newsId, [
            'status' => 'draft',
            'published_at' => null
        ]);
    }
    
    /**
     * Get news statistics
     */
    public function getStats() {
        $stats = [];
        
        // Total news
        $stats['total'] = $this->count();
        
        // By status
        $statuses = ['draft', 'published', 'archived'];
        foreach ($statuses as $status) {
            $count = $this->db->query("SELECT COUNT(*) as count FROM {$this->table} WHERE status = ?", [$status]);
            $stats['by_status'][$status] = $count[0]['count'] ?? 0;
        }
        
        // Featured count
        $featuredCount = $this->db->query("SELECT COUNT(*) as count FROM {$this->table} WHERE featured = 1");
        $stats['featured'] = $featuredCount[0]['count'] ?? 0;
        
        // Total views
        $totalViews = $this->db->query("SELECT SUM(views) as total FROM {$this->table}");
        $stats['total_views'] = $totalViews[0]['total'] ?? 0;
        
        // Recent published (last 30 days)
        $recentCount = $this->db->query("
            SELECT COUNT(*) as count FROM {$this->table} 
            WHERE status = 'published' AND published_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $stats['recent_published'] = $recentCount[0]['count'] ?? 0;
        
        return $stats;
    }
    
    /**
     * Get latest news for home page
     */
    public function getLatestForHome($limit = 8) {
        return $this->getWithAuthor($limit);
    }
    
    /**
     * Get news with categories
     */
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
    
    /**
     * Get popular news (by views)
     */
    public function getPopular($limit = 10) {
        return $this->where('status', 'published')
                   ->orderBy('views', 'DESC')
                   ->limit($limit)
                   ->get();
    }
    
    /**
     * Generate unique slug
     */
    private function generateUniqueSlug($title) {
        $slug = $this->generateSlug($title);
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