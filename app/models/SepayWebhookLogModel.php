<?php
/**
 * SePay Webhook Log Model
 * Handles logging and tracking of SePay webhooks
 */

require_once __DIR__ . '/BaseModel.php';

class SepayWebhookLogModel extends BaseModel {
    protected $table = 'sepay_webhooks_log';
    protected $fillable = [
        'webhook_type', 'transaction_id', 'reference_code', 'amount', 'content',
        'bank_account', 'status', 'success', 'processed', 'processed_at',
        'processing_error', 'order_id', 'withdrawal_id', 'raw_data', 'headers',
        'ip_address', 'signature', 'signature_verified', 'received_at'
    ];
    
    // Webhook types
    const TYPE_PAYMENT_IN = 'payment_in';
    const TYPE_PAYMENT_OUT = 'payment_out';
    const TYPE_UNKNOWN = 'unknown';
    
    /**
     * Create new record (override to exclude updated_at)
     */
    public function create($data) {
        // Filter only fillable fields
        $filteredData = $this->filterFillableData($data);
        
        // Add created_at if not provided (but NOT updated_at)
        if (!isset($filteredData['created_at'])) {
            $filteredData['created_at'] = date('Y-m-d H:i:s');
        }
        
        $id = $this->db->table($this->table)->insert($filteredData);
        return $id;
    }
    
    /**
     * Update record (override to exclude updated_at)
     */
    public function update($id, $data) {
        // Filter only fillable fields
        $filteredData = $this->filterFillableData($data);
        
        // Do NOT add updated_at for this table
        
        $success = $this->db->table($this->table)->where($this->primaryKey, $id)->update($filteredData);
        
        if ($success) {
            return $this->find($id);
        }
        
        return false;
    }
    
    /**
     * Filter data to only fillable fields (local version)
     */
    private function filterFillableData($data) {
        if (empty($this->fillable)) {
            return $data;
        }
        
        return array_intersect_key($data, array_flip($this->fillable));
    }
    
    /**
     * Log incoming webhook
     */
    public function logWebhook($data) {
        // Encode arrays to JSON
        if (isset($data['raw_data']) && is_array($data['raw_data'])) {
            $data['raw_data'] = json_encode($data['raw_data']);
        }
        
        if (isset($data['headers']) && is_array($data['headers'])) {
            $data['headers'] = json_encode($data['headers']);
        }
        
        // Set default values
        if (!isset($data['received_at'])) {
            $data['received_at'] = date('Y-m-d H:i:s');
        }
        
        if (!isset($data['processed'])) {
            $data['processed'] = 0;
        }
        
        if (!isset($data['success'])) {
            $data['success'] = 0;
        }
        
        if (!isset($data['signature_verified'])) {
            $data['signature_verified'] = 0;
        }
        
        return $this->create($data);
    }
    
    /**
     * Mark webhook as processed
     */
    public function markProcessed($logId, $success = true, $error = null) {
        $updateData = [
            'processed' => 1,
            'processed_at' => date('Y-m-d H:i:s'),
            'success' => $success ? 1 : 0
        ];
        
        if ($error) {
            $updateData['processing_error'] = $error;
        }
        
        return $this->update($logId, $updateData);
    }
    
    /**
     * Link webhook to order
     */
    public function linkToOrder($logId, $orderId) {
        return $this->update($logId, ['order_id' => $orderId]);
    }
    
    /**
     * Link webhook to withdrawal
     */
    public function linkToWithdrawal($logId, $withdrawalId) {
        return $this->update($logId, ['withdrawal_id' => $withdrawalId]);
    }
    
    /**
     * Get webhook by transaction ID
     */
    public function getByTransactionId($transactionId) {
        return $this->findBy('transaction_id', $transactionId);
    }
    
    /**
     * Get webhook by reference code
     */
    public function getByReferenceCode($referenceCode) {
        return $this->findBy('reference_code', $referenceCode);
    }
    
    /**
     * Get webhooks by type
     */
    public function getByType($type, $limit = null) {
        $query = $this->where('webhook_type', $type)->orderBy('received_at', 'DESC');
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->get();
    }
    
    /**
     * Get unprocessed webhooks
     */
    public function getUnprocessed($limit = null) {
        $query = $this->where('processed', 0)->orderBy('received_at', 'ASC');
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->get();
    }
    
    /**
     * Get failed webhooks
     */
    public function getFailed($limit = null) {
        $query = $this->where('processed', 1)
                     ->where('success', 0)
                     ->orderBy('received_at', 'DESC');
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->get();
    }
    
    /**
     * Get webhooks by order
     */
    public function getByOrder($orderId) {
        return $this->where('order_id', $orderId)
                    ->orderBy('received_at', 'DESC')
                    ->get();
    }
    
    /**
     * Get webhooks by withdrawal
     */
    public function getByWithdrawal($withdrawalId) {
        return $this->where('withdrawal_id', $withdrawalId)
                    ->orderBy('received_at', 'DESC')
                    ->get();
    }
    
    /**
     * Get recent webhooks with pagination
     */
    public function getRecentWithPagination($page = 1, $perPage = 50, $filters = []) {
        $offset = ($page - 1) * $perPage;
        $whereConditions = [];
        $bindings = [];
        
        // Apply filters
        if (!empty($filters['webhook_type'])) {
            $whereConditions[] = "webhook_type = ?";
            $bindings[] = $filters['webhook_type'];
        }
        
        if (isset($filters['processed'])) {
            $whereConditions[] = "processed = ?";
            $bindings[] = $filters['processed'];
        }
        
        if (isset($filters['success'])) {
            $whereConditions[] = "success = ?";
            $bindings[] = $filters['success'];
        }
        
        if (!empty($filters['date_from'])) {
            $whereConditions[] = "received_at >= ?";
            $bindings[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereConditions[] = "received_at <= ?";
            $bindings[] = $filters['date_to'];
        }
        
        if (!empty($filters['search'])) {
            $whereConditions[] = "(transaction_id LIKE ? OR reference_code LIKE ? OR content LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $bindings[] = $searchTerm;
            $bindings[] = $searchTerm;
            $bindings[] = $searchTerm;
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        // Get webhooks
        $sql = "
            SELECT * FROM {$this->table}
            {$whereClause}
            ORDER BY received_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ";
        
        $webhooks = $this->db->query($sql, $bindings);
        
        // Get total count
        $countSql = "SELECT COUNT(*) as count FROM {$this->table} {$whereClause}";
        $countResult = $this->db->query($countSql, $bindings);
        $total = $countResult[0]['count'] ?? 0;
        
        return [
            'data' => $webhooks,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => ceil($total / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total)
        ];
    }
    
    /**
     * Get webhook statistics
     */
    public function getStats($dateRange = null) {
        $whereClause = '';
        $bindings = [];
        
        if ($dateRange) {
            $whereClause = "WHERE received_at >= ? AND received_at <= ?";
            $bindings = [$dateRange['start'], $dateRange['end']];
        }
        
        $sql = "
            SELECT 
                COUNT(*) as total_webhooks,
                SUM(CASE WHEN webhook_type = 'payment_in' THEN 1 ELSE 0 END) as payment_in_count,
                SUM(CASE WHEN webhook_type = 'payment_out' THEN 1 ELSE 0 END) as payment_out_count,
                SUM(CASE WHEN webhook_type = 'unknown' THEN 1 ELSE 0 END) as unknown_count,
                SUM(CASE WHEN processed = 1 THEN 1 ELSE 0 END) as processed_count,
                SUM(CASE WHEN processed = 0 THEN 1 ELSE 0 END) as unprocessed_count,
                SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as success_count,
                SUM(CASE WHEN processed = 1 AND success = 0 THEN 1 ELSE 0 END) as failed_count,
                SUM(CASE WHEN signature_verified = 1 THEN 1 ELSE 0 END) as verified_count
            FROM {$this->table}
            {$whereClause}
        ";
        
        $result = $this->db->query($sql, $bindings);
        return $result ? $result[0] : [];
    }
    
    /**
     * Clean old logs (older than specified days)
     */
    public function cleanOldLogs($daysToKeep = 90) {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$daysToKeep} days"));
        
        $sql = "DELETE FROM {$this->table} WHERE received_at < ? AND processed = 1";
        
        return $this->db->execute($sql, [$cutoffDate]);
    }
}
