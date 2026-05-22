<?php
/**
 * Agent Content Model
 * Handles database operations for dynamic agent/affiliate pages
 */

require_once __DIR__ . '/BaseModel.php';

class AgentContentModel extends BaseModel {
    protected $table = 'agent_contents';
    protected $fillable = [
        'page_key', 'title', 'content', 'image', 'meta_title', 'meta_description'
    ];
    
    /**
     * Get agent content by page key
     * @param string $key
     * @return array|null
     */
    public function getByPageKey($key) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE page_key = :page_key LIMIT 1";
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute(['page_key' => $key]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (Exception $e) {
            error_log("Get agent content error (key: $key): " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get all agent contents
     * @return array
     */
    public function getAllContents() {
        try {
            $sql = "SELECT * FROM {$this->table} ORDER BY id ASC";
            return $this->db->getPdo()->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Get all agent contents error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update agent content by key
     * @param string $key
     * @param array $data
     * @return bool
     */
    public function updateByKey($key, $data) {
        try {
            $filteredData = array_intersect_key($data, array_flip($this->fillable));
            if (empty($filteredData)) {
                return false;
            }
            
            $setClause = [];
            $params = ['page_key' => $key];
            
            foreach ($filteredData as $column => $value) {
                $setClause[] = "`$column` = :$column";
                $params[$column] = $value;
            }
            
            $sql = "UPDATE {$this->table} SET " . implode(', ', $setClause) . " WHERE page_key = :page_key";
            $stmt = $this->db->getPdo()->prepare($sql);
            return $stmt->execute($params);
        } catch (Exception $e) {
            error_log("Update agent content by key error (key: $key): " . $e->getMessage());
            return false;
        }
    }
}
