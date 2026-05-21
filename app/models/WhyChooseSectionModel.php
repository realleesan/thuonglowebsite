<?php
require_once __DIR__ . '/BaseModel.php';

class WhyChooseSectionModel extends BaseModel
{
    protected $table = 'why_choose_section';
    protected $fillable = ['title', 'is_active'];
    
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Get the first record of Why Choose section
     */
    public function getFirst()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY id ASC LIMIT 1";
        try {
            $result = $this->query($sql);
            return $result ? $result[0] : null;
        } catch (Exception $e) {
            error_log("WhyChooseSectionModel::getFirst - Error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Create why choose section
     */
    public function createSection($data)
    {
        return $this->create($data);
    }
    
    /**
     * Update why choose section
     */
    public function updateSection($id, $data)
    {
        return $this->update($id, $data);
    }
    
    /**
     * Toggle is_active status
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
    
    /**
     * Get section with all its items sorted by sort_order
     */
    public function getWithItems($id = null)
    {
        try {
            if ($id === null) {
                $section = $this->getFirst();
                if (!$section) return null;
                $id = $section['id'];
            } else {
                $section = $this->find($id);
                if (!$section) return null;
            }
            
            $sql = "SELECT * FROM why_choose_items WHERE section_id = ? ORDER BY sort_order ASC, id ASC";
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute([$id]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $section['items'] = $items ?: [];
            return $section;
        } catch (Exception $e) {
            error_log("WhyChooseSectionModel::getWithItems error: " . $e->getMessage());
            return null;
        }
    }
}
