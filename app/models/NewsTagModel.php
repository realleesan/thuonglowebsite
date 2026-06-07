<?php
/**
 * News Tag Model
 * Handles database operations for news tags (dictionary tables)
 */

require_once __DIR__ . '/BaseModel.php';

class NewsTagModel extends BaseModel {
    protected $table = 'news_tags';
    protected $fillable = ['name', 'slug'];

    public function __construct() {
        parent::__construct();
        $this->syncTagsFromNewsIfNeeded();
    }

    /**
     * Check if news_tags is empty, and sync existing tags from news table
     */
    private function syncTagsFromNewsIfNeeded(): void {
        try {
            $pdo = $this->db->getPdo();
            // Check if news_tags table exists before running queries (in case migration is not run yet)
            $checkStmt = $pdo->prepare("SHOW TABLES LIKE 'news_tags'");
            $checkStmt->execute();
            if (!$checkStmt->fetch()) {
                return; // Table doesn't exist yet, wait for migration
            }

            // Check if table is empty
            $countStmt = $pdo->query("SELECT COUNT(*) FROM `{$this->table}`");
            $count = $countStmt->fetchColumn();
            if ($count == 0) {
                // Get all non-empty tags from news
                $newsStmt = $pdo->query("SELECT DISTINCT tags FROM news WHERE tags IS NOT NULL AND tags != ''");
                $newsTags = $newsStmt->fetchAll(PDO::FETCH_COLUMN);

                $uniqueTags = [];
                foreach ($newsTags as $tagsStr) {
                    $tags = array_map('trim', explode(',', $tagsStr));
                    foreach ($tags as $tag) {
                        if ($tag !== '') {
                            // Case-insensitive de-duplication
                            $lowerTag = mb_strtolower($tag, 'UTF-8');
                            if (!isset($uniqueTags[$lowerTag])) {
                                $uniqueTags[$lowerTag] = $tag;
                            }
                        }
                    }
                }

                foreach ($uniqueTags as $tagName) {
                    $slug = $this->generateUniqueSlug($tagName);
                    $stmt = $pdo->prepare("INSERT IGNORE INTO `{$this->table}` (name, slug) VALUES (:name, :slug)");
                    $stmt->execute(['name' => $tagName, 'slug' => $slug]);
                }
            }
        } catch (Exception $e) {
            error_log("NewsTagModel sync tags error: " . $e->getMessage());
        }
    }

    /**
     * Generate unique slug
     */
    public function generateUniqueSlug(string $name): string {
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
     * Generate URL slug from string
     */
    private function generateSlug(string $string): string {
        $slug = strtolower($string);
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
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        return trim($slug, '-');
    }
}
