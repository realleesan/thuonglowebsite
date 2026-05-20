<?php

class LatestProductsSectionModel extends BaseModel {
    protected $table = 'latest_products_section';
    
    public function __construct() {
        parent::__construct();
        // $this->createTableIfNotExists(); // Disabled - tables should exist
    }
    
    private function createTableIfNotExists() {
        $sql = "CREATE TABLE IF NOT EXISTS `latest_products_section` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `title` text NOT NULL,
            `is_active` tinyint(1) NOT NULL DEFAULT 1,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        try {
            $this->db->exec($sql);
            
            // Insert default data if table is empty
            $result = $this->db->query("SELECT COUNT(*) as count FROM `latest_products_section`");
            $count = $result[0]['count'];
            if ($count == 0) {
                $defaultTitle = '<h2 class="section-title">Sản phẩm <span class="highlight">Mới nhất</span></h2>';
                $insertSql = "INSERT INTO `latest_products_section` (title, is_active) VALUES (:title, 1)";
                $this->query($insertSql, ['title' => $defaultTitle]);
            }
        } catch (Exception $e) {
            error_log("Error creating latest_products_section table: " . $e->getMessage());
        }
    }
    
    public function getFirst() {
        $sql = "SELECT * FROM `latest_products_section` ORDER BY id ASC LIMIT 1";
        $result = $this->query($sql);
        return $result[0] ?? null;
    }
    
    public function createSection($data) {
        $sql = "INSERT INTO `latest_products_section` (title, is_active) VALUES (:title, :is_active)";
        return $this->query($sql, [
            'title' => $data['title'],
            'is_active' => $data['is_active'] ?? 1
        ]);
    }
    
    public function updateSection($id, $data) {
        $sql = "UPDATE `latest_products_section` SET title = :title, is_active = :is_active WHERE id = :id";
        return $this->query($sql, [
            'title' => $data['title'],
            'is_active' => $data['is_active'],
            'id' => $id
        ]);
    }
    
    public function getActive() {
        $sql = "SELECT * FROM `latest_products_section` WHERE is_active = 1 ORDER BY id ASC LIMIT 1";
        $result = $this->query($sql);
        return $result[0] ?? null;
    }
    
    public function toggleStatus($id) {
        $sql = "UPDATE `latest_products_section` SET is_active = NOT is_active WHERE id = :id";
        return $this->query($sql, ['id' => $id]);
    }
}
