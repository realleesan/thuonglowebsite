<?php
/**
 * Base Model Class
 * Provides common functionality for all models
 */

require_once __DIR__ . '/../../core/Database.php';

abstract class BaseModel {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $hidden = [];
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all records
     */
    public function all($columns = '*') {
        return $this->db->table($this->table)->select($columns)->get();
    }
    
    /**
     * Find record by ID
     */
    public function find($id, $columns = '*') {
        return $this->db->table($this->table)->select($columns)->find($id);
    }
    
    /**
     * Find record by specific field
     */
    public function findBy($field, $value, $columns = '*') {
        return $this->db->table($this->table)->select($columns)->where($field, $value)->first();
    }
    
    /**
     * Get records with conditions
     */
    public function where($field, $operator = '=', $value = null) {
        return $this->db->table($this->table)->where($field, $operator, $value);
    }
    
    /**
     * Create new record
     */
    public function create($data) {
        // Filter only fillable fields
        $filteredData = $this->filterFillable($data);
        
        // Add timestamps if not provided
        if (!isset($filteredData['created_at'])) {
            $filteredData['created_at'] = date('Y-m-d H:i:s');
        }
        if (!isset($filteredData['updated_at'])) {
            $filteredData['updated_at'] = date('Y-m-d H:i:s');
        }
        
        $id = $this->db->table($this->table)->insert($filteredData);
        return $this->find($id);
    }
    
    /**
     * Update record
     */
    public function update($id, $data) {
        // Filter only fillable fields
        $filteredData = $this->filterFillable($data);
        
        // Add updated timestamp
        $filteredData['updated_at'] = date('Y-m-d H:i:s');
        
        $success = $this->db->table($this->table)->where($this->primaryKey, $id)->update($filteredData);
        
        if ($success) {
            return $this->find($id);
        }
        
        return false;
    }
    
    /**
     * Delete record
     */
    public function delete($id) {
        return $this->db->table($this->table)->where($this->primaryKey, $id)->delete();
    }
    
    /**
     * Get paginated results
     */
    public function paginate($page = 1, $perPage = 15, $columns = '*') {
        $offset = ($page - 1) * $perPage;
        
        $records = $this->db->table($this->table)
                          ->select($columns)
                          ->limit($perPage, $offset)
                          ->get();
        
        $total = $this->count();
        
        return [
            'data' => $records,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => ceil($total / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total)
        ];
    }
    
    /**
     * Count records
     */
    public function count($field = '*') {
        $result = $this->db->query("SELECT COUNT({$field}) as count FROM {$this->table}");
        return $result[0]['count'] ?? 0;
    }
    
    /**
     * Get records with search
     */
    public function search($query, $fields = [], $limit = 50) {
        if (empty($fields)) {
            return [];
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE ";
        $conditions = [];
        $bindings = [];
        
        foreach ($fields as $field) {
            $conditions[] = "{$field} LIKE :search_{$field}";
            $bindings["search_{$field}"] = "%{$query}%";
        }
        
        $sql .= implode(' OR ', $conditions);
        $sql .= " LIMIT {$limit}";
        
        return $this->db->query($sql, $bindings);
    }
    
    /**
     * Filter data to only fillable fields
     */
    protected function filterFillable($data) {
        if (empty($this->fillable)) {
            return $data;
        }
        
        return array_intersect_key($data, array_flip($this->fillable));
    }
    
    /**
     * Hide sensitive fields from output
     */
    protected function hideFields($data) {
        if (empty($this->hidden) || empty($data)) {
            return $data;
        }
        
        if (is_array($data) && isset($data[0])) {
            // Multiple records
            return array_map(function($record) {
                return array_diff_key($record, array_flip($this->hidden));
            }, $data);
        } else {
            // Single record
            return array_diff_key($data, array_flip($this->hidden));
        }
    }
    
    /**
     * Execute raw query
     */
    public function query($sql, $bindings = []) {
        return $this->db->query($sql, $bindings);
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->db->getPdo()->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        return $this->db->getPdo()->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->db->getPdo()->rollback();
    }
}