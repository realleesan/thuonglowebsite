<?php

require_once __DIR__ . '/BaseModel.php';

class LatestNewsSectionModel extends BaseModel
{
    protected $table = 'latest_news_section';
    
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Lấy bản ghi đầu tiên của latest news section
     */
    public function getFirst()
    {
        error_log("LatestNewsSectionModel::getFirst - Table: " . $this->table);
        $sql = "SELECT * FROM {$this->table} ORDER BY id ASC LIMIT 1";
        error_log("LatestNewsSectionModel::getFirst - SQL: " . $sql);
        try {
            $result = $this->query($sql);
            error_log("LatestNewsSectionModel::getFirst - Result: " . ($result ? 'found' : 'null'));
            return $result ? $result[0] : null;
        } catch (Exception $e) {
            error_log("LatestNewsSectionModel::getFirst - Error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Tạo latest news section mới
     */
    public function createSection($data)
    {
        return $this->create($data);
    }
    
    /**
     * Cập nhật latest news section
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
