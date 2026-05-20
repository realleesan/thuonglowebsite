<?php

require_once __DIR__ . '/BaseModel.php';

class FeaturedBrandsSectionModel extends BaseModel
{
    protected $table = 'featured_brands_section';
    
    public function __construct()
    {
        error_log("FeaturedBrandsSectionModel::__construct - Starting...");
        try {
            parent::__construct();
            error_log("FeaturedBrandsSectionModel::__construct - Parent constructor completed");
            error_log("FeaturedBrandsSectionModel::__construct - Table: " . $this->table);
        } catch (Exception $e) {
            error_log("FeaturedBrandsSectionModel::__construct - Error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Lấy bản ghi đầu tiên của featured brands section
     */
    public function getFirst()
    {
        error_log("FeaturedBrandsSectionModel::getFirst - Table: " . $this->table);
        $sql = "SELECT * FROM {$this->table} ORDER BY id ASC LIMIT 1";
        error_log("FeaturedBrandsSectionModel::getFirst - SQL: " . $sql);
        try {
            $result = $this->query($sql);
            error_log("FeaturedBrandsSectionModel::getFirst - Result: " . ($result ? 'found' : 'null'));
            return $result ? $result[0] : null;
        } catch (Exception $e) {
            error_log("FeaturedBrandsSectionModel::getFirst - Error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Tạo featured brands section mới
     */
    public function createSection($data)
    {
        return $this->create($data);
    }
    
    /**
     * Cập nhật featured brands section
     */
    public function updateSection($id, $data)
    {
        return $this->update($id, $data);
    }
    
    /**
     * Toggle trạng thái is_active
     */
    public function toggleStatus($id)
    {
        $section = $this->find($id);
        if (!$section) {
            return false;
        }
        
        $newStatus = $section['is_active'] == 1 ? 0 : 1;
        return $this->update($id, ['is_active' => $newStatus]);
    }
}
