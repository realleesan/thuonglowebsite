<?php
/**
 * ProductData Model
 * Handles product data operations (supplier info, wechat, phone, etc.)
 */

require_once __DIR__ . '/BaseModel.php';

class ProductDataModel extends BaseModel {
    protected $table = 'product_data';
    protected $primaryKey = 'id';
    protected $fillable = [
        'product_id',
        'supplier_name',
        'address',
        'wechat_account',
        'phone',
        'wechat_qr',
        'row_index'
    ];
    
    /**
     * Get all data for a product
     */
    public function getByProduct($productId, $limit = null, $offset = 0) {
        $query = $this->db->table($this->table)
            ->select('*')
            ->where('product_id', $productId)
            ->orderBy('row_index', 'ASC');
        
        if ($limit) {
            $query->limit($limit, $offset);
        }
        
        return $query->get();
    }
    
    /**
     * Get data with pagination for a product
     */
    public function getByProductPaginated($productId, $page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        
        $data = $this->db->table($this->table)
            ->select('*')
            ->where('product_id', $productId)
            ->orderBy('row_index', 'ASC')
            ->limit($perPage, $offset)
            ->get();
        
        $total = $this->countByProduct($productId);
        
        return [
            'data' => $data,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => ceil($total / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total)
        ];
    }
    
    /**
     * Count total data for a product
     */
    public function countByProduct($productId) {
        $result = $this->db->query(
            "SELECT COUNT(*) as count FROM {$this->table} WHERE product_id = ?",
            [$productId]
        );
        return $result[0]['count'] ?? 0;
    }
    
    /**
     * Delete all data for a product
     */
    public function deleteByProduct($productId) {
        return $this->db->table($this->table)
            ->where('product_id', $productId)
            ->delete();
    }
    
    /**
     * Insert multiple records (bulk)
     */
    public function bulkInsert($dataArray) {
        if (empty($dataArray)) {
            return 0;
        }
        
        $values = [];
        $bindings = [];
        
        foreach ($dataArray as $index => $data) {
            $filtered = $this->filterFillable($data);
            $filtered['row_index'] = $index + 1;
            $filtered['created_at'] = date('Y-m-d H:i:s');
            $filtered['updated_at'] = date('Y-m-d H:i:s');
            
            $placeholders = [];
            foreach ($filtered as $key => $value) {
                $placeholders[] = ":{$key}_{$index}";
                $bindings["{$key}_{$index}"] = $value;
            }
            
            $values[] = "(" . implode(", ", $placeholders) . ")";
        }
        
        $sql = "INSERT INTO {$this->table} (" . implode(", ", array_keys($filtered)) . ") VALUES " . implode(", ", $values);
        
        return $this->db->query($sql, $bindings);
    }
    
    /**
     * Find by access token
     */
    public function findByToken($token) {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("SELECT * FROM product_data_access WHERE access_token = ?");
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Create access token for user
     */
    public function createAccess($userId, $productId, $durationMinutes = 15) {
        $token = bin2hex(random_bytes(32)); // 64 character token
        $expiresAt = date('Y-m-d H:i:s', time() + ($durationMinutes * 60));
        
        $data = [
            'user_id' => $userId,
            'product_id' => $productId,
            'access_token' => $token,
            'expires_at' => $expiresAt,
            'viewed_rows' => json_encode([]),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $id = $this->db->table('product_data_access')->insert($data);
        
        return [
            'id' => $id,
            'access_token' => $token,
            'expires_at' => $expiresAt
        ];
    }
    
    /**
     * Validate access token
     */
    public function validateAccess($token) {
        $access = $this->findByToken($token);
        
        if (!$access) {
            return ['valid' => false, 'error' => 'Token không hợp lệ'];
        }
        
        // Check if expired
        if (strtotime($access['expires_at']) < time()) {
            return ['valid' => false, 'error' => 'Phiên xem đã hết hạn'];
        }
        
        return [
            'valid' => true,
            'access' => $access
        ];
    }
    
    /**
     * Delete expired access tokens
     */
    public function cleanExpiredAccess() {
        return $this->db->query(
            "DELETE FROM product_data_access WHERE expires_at < NOW()"
        );
    }
    
    /**
     * Get data count by product ID (static method for quick check)
     */
    public static function getDataCount($productId) {
        $db = Database::getInstance();
        $result = $db->query(
            "SELECT COUNT(*) as count FROM product_data WHERE product_id = ?",
            [$productId]
        );
        return $result[0]['count'] ?? 0;
    }
}
