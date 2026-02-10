<?php
/**
 * Database Class - PDO Wrapper with Query Builder
 * Singleton Pattern for single connection
 * jQuery-like syntax for easy database operations
 */

class Database {
    private static $instance = null;
    private $pdo;
    private $table;
    private $query;
    private $bindings = [];
    
    /**
     * Private constructor for Singleton pattern
     */
    private function __construct() {
        $this->connect();
    }
    
    /**
     * Get singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Establish database connection
     */
    private function connect() {
        try {
            // Load configuration
            $config = include __DIR__ . '/../config.php';
            $dbConfig = $config['database'];
            
            $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['name']};charset={$dbConfig['charset']}";
            
            $this->pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);
            
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    /**
     * Set table for query
     */
    public function table($table) {
        $this->table = $table;
        $this->query = '';
        $this->bindings = [];
        return $this;
    }
    
    /**
     * Select columns
     */
    public function select($columns = '*') {
        if (is_array($columns)) {
            $columns = implode(', ', $columns);
        }
        $this->query = "SELECT {$columns} FROM {$this->table}";
        return $this;
    }
    
    /**
     * Add WHERE condition
     */
    public function where($column, $operator = '=', $value = null) {
        // If only 2 parameters, assume operator is '='
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        
        $placeholder = ':' . str_replace('.', '_', $column) . '_' . count($this->bindings);
        
        if (strpos($this->query, 'WHERE') === false) {
            $this->query .= " WHERE {$column} {$operator} {$placeholder}";
        } else {
            $this->query .= " AND {$column} {$operator} {$placeholder}";
        }
        
        $this->bindings[$placeholder] = $value;
        return $this;
    }
    
    /**
     * Add OR WHERE condition
     */
    public function orWhere($column, $operator = '=', $value = null) {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        
        $placeholder = ':' . str_replace('.', '_', $column) . '_' . count($this->bindings);
        
        if (strpos($this->query, 'WHERE') === false) {
            $this->query .= " WHERE {$column} {$operator} {$placeholder}";
        } else {
            $this->query .= " OR {$column} {$operator} {$placeholder}";
        }
        
        $this->bindings[$placeholder] = $value;
        return $this;
    }
    
    /**
     * Add ORDER BY
     */
    public function orderBy($column, $direction = 'ASC') {
        $this->query .= " ORDER BY {$column} {$direction}";
        return $this;
    }
    
    /**
     * Add LIMIT
     */
    public function limit($limit, $offset = 0) {
        if ($offset > 0) {
            $this->query .= " LIMIT {$offset}, {$limit}";
        } else {
            $this->query .= " LIMIT {$limit}";
        }
        return $this;
    }
    
    /**
     * Execute query and get all results
     */
    public function get() {
        if (empty($this->query)) {
            $this->select();
        }
        
        try {
            $stmt = $this->pdo->prepare($this->query);
            $stmt->execute($this->bindings);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Query execution failed: " . $e->getMessage());
        }
    }
    
    /**
     * Execute query and get first result
     */
    public function first() {
        $this->limit(1);
        $results = $this->get();
        return !empty($results) ? $results[0] : null;
    }
    
    /**
     * Find record by ID
     */
    public function find($id) {
        $this->select();
        return $this->where('id', $id)->first();
    }
    
    /**
     * Insert new record
     */
    public function insert($data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $query = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($data);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Insert failed: " . $e->getMessage());
        }
    }
    
    /**
     * Update records
     */
    public function update($data) {
        $setParts = [];
        foreach ($data as $column => $value) {
            $setParts[] = "{$column} = :{$column}";
        }
        $setClause = implode(', ', $setParts);
        
        $query = "UPDATE {$this->table} SET {$setClause}";
        
        // Add WHERE conditions if any
        if (!empty($this->bindings)) {
            // Extract WHERE clause from existing query
            if (strpos($this->query, 'WHERE') !== false) {
                $whereClause = substr($this->query, strpos($this->query, 'WHERE'));
                $query .= " " . $whereClause;
                $data = array_merge($data, $this->bindings);
            }
        }
        
        try {
            $stmt = $this->pdo->prepare($query);
            return $stmt->execute($data);
        } catch (PDOException $e) {
            throw new Exception("Update failed: " . $e->getMessage());
        }
    }
    
    /**
     * Delete records
     */
    public function delete() {
        $query = "DELETE FROM {$this->table}";
        
        // Add WHERE conditions if any
        if (!empty($this->bindings)) {
            // Extract WHERE clause from existing query
            if (strpos($this->query, 'WHERE') !== false) {
                $whereClause = substr($this->query, strpos($this->query, 'WHERE'));
                $query .= " " . $whereClause;
            }
        }
        
        try {
            $stmt = $this->pdo->prepare($query);
            return $stmt->execute($this->bindings);
        } catch (PDOException $e) {
            throw new Exception("Delete failed: " . $e->getMessage());
        }
    }
    
    /**
     * Execute raw SQL query
     */
    public function query($sql, $bindings = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($bindings);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Raw query failed: " . $e->getMessage());
        }
    }
    
    /**
     * Execute raw SQL statement (for INSERT, UPDATE, DELETE)
     */
    public function execute($sql, $bindings = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($bindings);
        } catch (PDOException $e) {
            throw new Exception("Statement execution failed: " . $e->getMessage());
        }
    }
    
    /**
     * Get PDO instance for advanced operations
     */
    public function getPdo() {
        return $this->pdo;
    }
    
    /**
     * Test database connection
     */
    public function testConnection() {
        try {
            $result = $this->query("SELECT 1 as test");
            return !empty($result) && $result[0]['test'] == 1;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get database info
     */
    public function getInfo() {
        try {
            $version = $this->query("SELECT VERSION() as version");
            $database = $this->query("SELECT DATABASE() as db_name");
            
            return [
                'version' => $version[0]['version'] ?? 'Unknown',
                'database' => $database[0]['db_name'] ?? 'Unknown',
                'connection' => 'Active'
            ];
        } catch (Exception $e) {
            return [
                'version' => 'Unknown',
                'database' => 'Unknown', 
                'connection' => 'Failed: ' . $e->getMessage()
            ];
        }
    }
    
    // Prevent cloning and unserialization
    private function __clone() {}
    public function __wakeup() {}
}