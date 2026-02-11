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
        $placeholder = ':' . str_replace('.', '_', $column) . '_' . count($this->bindings);
        if (strpos($this->query, 'WHERE') === false) {
            $this->query .= " WHERE {$column} {$operator} {$placeholder}";
        } else {
            $this->query .= " AND {$column} {$operator} {$placeholder}";
        }
        $this->bindings[$placeholder] = $value;
        return $this;
    }
    
    public function limit($limit, $offset = 0) {
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
            $stmt->execute($bindings);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Raw query failed: " . $e->getMessage());
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

    public function getPdo() { return $this->pdo; }
}