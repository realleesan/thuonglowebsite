<?php
/**
 * Base Seeder Class
 * Provides common functionality for all seeders
 */

abstract class BaseSeeder {
    protected $db;
    protected $tableName;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Abstract method that must be implemented by child classes
     */
    abstract public function run();
    
    /**
     * Truncate table before seeding
     */
    protected function truncateTable($table = null) {
        $table = $table ?: $this->tableName;
        $this->db->execute("SET FOREIGN_KEY_CHECKS = 0");
        $this->db->execute("TRUNCATE TABLE `{$table}`");
        $this->db->execute("SET FOREIGN_KEY_CHECKS = 1");
        echo "   ✓ Truncated table: {$table}\n";
    }
    
    /**
     * Load JSON data from file
     */
    protected function loadJsonData($filePath) {
        if (!file_exists($filePath)) {
            throw new Exception("JSON file not found: {$filePath}");
        }
        
        $jsonContent = file_get_contents($filePath);
        $data = json_decode($jsonContent, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON in file: {$filePath}. Error: " . json_last_error_msg());
        }
        
        return $data;
    }
    
    /**
     * Insert data with error handling
     */
    protected function insertData($table, $data) {
        try {
            $id = $this->db->table($table)->insert($data);
            return $id;
        } catch (Exception $e) {
            echo "   ❌ Error inserting into {$table}: " . $e->getMessage() . "\n";
            echo "   Data: " . json_encode($data) . "\n";
            throw $e;
        }
    }
    
    /**
     * Generate slug from string
     */
    protected function generateSlug($string) {
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
     * Hash password
     */
    protected function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    /**
     * Format datetime
     */
    protected function formatDateTime($dateString) {
        if (empty($dateString)) {
            return date('Y-m-d H:i:s');
        }
        
        try {
            $date = new DateTime($dateString);
            return $date->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            return date('Y-m-d H:i:s');
        }
    }
}