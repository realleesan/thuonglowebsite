<?php
/**
 * Database Class - PDO Wrapper
 */

class Database {
    private static $instance = null;
    private $pdo;
    private $table;
    private $query;
    private $bindings = [];
    
    private function __construct() {
        $this->connect();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function connect() {
        try {
            // Lấy config từ biến toàn cục hoặc nạp lại
            global $config;
            if (!isset($config['database'])) {
                $config = require __DIR__ . '/../config.php';
                // Nếu require trả về 1 (true) thay vì mảng, chứng tỏ biến global config đã tồn tại
                if ($config === 1 || $config === true) {
                    global $config;
                }
            }
            
            $dbConfig = $config['database'];
            $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['name']};charset={$dbConfig['charset']}";
            
            $this->pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);
            
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("Config error: " . $e->getMessage());
        }
    }
    
    public function table($table) {
        $this->table = $table;
        $this->query = '';
        $this->bindings = [];
        return $this;
    }
    
    public function select($columns = '*') {
        if (is_array($columns)) {
            $columns = implode(', ', $columns);
        }
        $this->query = "SELECT {$columns} FROM {$this->table}";
        return $this;
    }
    
    public function where($column, $operator = '=', $value = null) {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        
        // Initialize query with SELECT if it's empty
        if (empty($this->query)) {
            $this->select();
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

    public function orderBy($column, $direction = 'ASC') {
        if (strpos($this->query, 'ORDER BY') === false) {
            $this->query .= " ORDER BY {$column} {$direction}";
        } else {
            $this->query .= ", {$column} {$direction}";
        }
        return $this;
    }
    
    public function limit($limit, $offset = 0) {
        // Initialize query with SELECT if it's empty to prevent syntax error
        if (empty($this->query)) {
            $this->select();
        }
        
        if ($offset > 0) {
            $this->query .= " LIMIT {$offset}, {$limit}";
        } else {
            $this->query .= " LIMIT {$limit}";
        }
        return $this;
    }
    
    public function get() {
        if (empty($this->query)) $this->select();
        try {
            $stmt = $this->pdo->prepare($this->query);
            $stmt->execute($this->bindings);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Query failed: " . $e->getMessage());
        }
    }
    
    public function first() {
        // Ensure query has SELECT clause before limiting
        if (empty($this->query)) {
            $this->select();
        }
        $this->limit(1);
        $results = $this->get();
        return !empty($results) ? $results[0] : null;
    }
    
    public function find($id) {
        return $this->where('id', $id)->first();
    }
    
    public function query($sql, $bindings = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $success = $stmt->execute($bindings);
            
            // Native PDO check: only fetch rows if the query returns a result set (columns > 0)
            if ($stmt->columnCount() > 0) {
                return $stmt->fetchAll();
            }
            
            return $success;
        } catch (PDOException $e) {
            throw new Exception("Raw query failed: " . $e->getMessage());
        }
    }
    
    public function insert($data) {
        $columns = array_keys($data);
        $placeholders = array_map(function($col) { return ':' . $col; }, $columns);
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($data);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Insert failed: " . $e->getMessage());
        }
    }
    
    public function update($data) {
        $setParts = [];
        $updateBindings = [];
        
        foreach ($data as $column => $value) {
            $placeholder = ':update_' . $column;
            $setParts[] = "{$column} = {$placeholder}";
            $updateBindings[$placeholder] = $value;
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setParts);
        
        // Build WHERE clause from bindings (set by where() method)
        if (!empty($this->bindings)) {
            $whereParts = [];
            
            // Parse the query string to extract WHERE conditions
            if (!empty($this->query) && strpos($this->query, 'WHERE') !== false) {
                // Extract WHERE clause from query
                $whereClause = substr($this->query, strpos($this->query, 'WHERE'));
                $sql .= " " . $whereClause;
            } else {
                // Build WHERE from bindings if query is empty
                $sql .= " WHERE 1=1";
                foreach ($this->bindings as $placeholder => $value) {
                    // Extract column name from placeholder (e.g., :setting_key_0 -> setting_key)
                    $column = str_replace(':', '', $placeholder);
                    $column = preg_replace('/_\d+$/', '', $column);
                    $sql .= " AND {$column} = {$placeholder}";
                }
            }
            
            $bindings = array_merge($updateBindings, $this->bindings);
        } else {
            $bindings = $updateBindings;
        }
        
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($bindings);
        } catch (PDOException $e) {
            throw new Exception("Update failed: " . $e->getMessage());
        }
    }
    
    public function delete() {
        $sql = "DELETE FROM {$this->table}";
        
        // Add WHERE conditions if they exist
        if (!empty($this->bindings)) {
            $whereParts = [];
            foreach ($this->bindings as $placeholder => $value) {
                $column = str_replace(':', '', $placeholder);
                $column = preg_replace('/_\d+$/', '', $column); // Remove trailing numbers
                $whereParts[] = "{$column} = {$placeholder}";
            }
            $sql .= " WHERE " . implode(' AND ', $whereParts);
        }
        
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($this->bindings);
        } catch (PDOException $e) {
            throw new Exception("Delete failed: " . $e->getMessage());
        }
    }
    
    public function testConnection() {
        try {
            $result = $this->query("SELECT 1 as test");
            return !empty($result) && $result[0]['test'] == 1;
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    public function commit() {
        return $this->pdo->commit();
    }
    
    public function rollBack() {
        return $this->pdo->rollBack();
    }

    public function getPdo() { return $this->pdo; }

    public function exec($sql) {
        try {
            return $this->pdo->exec($sql);
        } catch (PDOException $e) {
            throw new Exception("Exec failed: " . $e->getMessage());
        }
    }
}

/**
 * Get database connection (backward compatibility)
 */
function getConnection() {
    try {
        $database = Database::getInstance();
        return $database->getPdo();
    } catch (Exception $e) {
        error_log("Database connection error: " . $e->getMessage());
        return null;
    }
}