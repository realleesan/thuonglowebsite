<?php
require_once __DIR__ . '/BaseModel.php';

class WhyChooseItemModel extends BaseModel
{
    protected $table = 'why_choose_items';
    protected $fillable = ['section_id', 'title', 'content', 'sort_order'];
    
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Get all items for a section
     */
    public function getBySection($sectionId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE section_id = ? ORDER BY sort_order ASC, id ASC";
        return $this->db->query($sql, [$sectionId]);
    }
    
    /**
     * Create a new item
     */
    public function createItem($data)
    {
        $filteredData = array_intersect_key($data, array_flip($this->fillable));
        if (empty($filteredData['sort_order'])) {
            $filteredData['sort_order'] = $this->getNextSortOrder($filteredData['section_id'] ?? 1);
        }
        return $this->create($filteredData);
    }
    
    /**
     * Update an item
     */
    public function updateItem($id, $data)
    {
        $filteredData = array_intersect_key($data, array_flip($this->fillable));
        return $this->update($id, $filteredData);
    }
    
    /**
     * Delete an item
     */
    public function deleteItem($id)
    {
        return $this->delete($id);
    }
    
    /**
     * Get next sort order for items in a section
     */
    private function getNextSortOrder($sectionId)
    {
        $result = $this->db->table($this->table)
            ->select('MAX(sort_order) as max_sort')
            ->where('section_id', $sectionId)
            ->first();
        return ($result && $result['max_sort']) ? $result['max_sort'] + 1 : 1;
    }
}
