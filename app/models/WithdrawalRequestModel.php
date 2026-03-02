<?php
/**
 * Withdrawal Request Model
 * Handles affiliate withdrawal request operations
 */

require_once __DIR__ . '/BaseModel.php';

class WithdrawalRequestModel extends BaseModel {
    protected $table = 'withdrawal_requests';
    protected $fillable = [
        'affiliate_id', 'amount', 'withdraw_code', 'fee', 'net_amount',
        'bank_name', 'bank_account', 'account_holder', 'bank_branch',
        'status', 'admin_note', 'processed_by', 'processed_at',
        'sepay_transaction_id', 'sepay_qr_code', 'qr_generated_at', 'payment_completed_at',
        'webhook_received_at', 'webhook_data', 'requested_at'
    ];
    
    // Withdrawal statuses
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELLED = 'cancelled';
    
    /**
     * Create withdrawal request
     */
    public function createRequest($data) {
        // Validate required fields
        if (!isset($data['affiliate_id']) || !isset($data['amount'])) {
            throw new Exception('Missing required fields: affiliate_id, amount');
        }
        
        // Generate unique withdraw code if not provided
        if (!isset($data['withdraw_code'])) {
            $data['withdraw_code'] = $this->generateWithdrawCode();
        }
        
        // Calculate net amount (fee is 0 for now)
        if (!isset($data['fee'])) {
            $data['fee'] = 0.00;
        }
        
        if (!isset($data['net_amount'])) {
            $data['net_amount'] = $data['amount'] - $data['fee'];
        }
        
        // Set default status
        if (!isset($data['status'])) {
            $data['status'] = self::STATUS_PENDING;
        }
        
        // Set requested_at
        if (!isset($data['requested_at'])) {
            $data['requested_at'] = date('Y-m-d H:i:s');
        }
        
        // Encode webhook_data if it's an array
        if (isset($data['webhook_data']) && is_array($data['webhook_data'])) {
            $data['webhook_data'] = json_encode($data['webhook_data']);
        }
        
        return $this->create($data);
    }

    
    /**
     * Get withdrawal request with affiliate details
     */
    public function getWithDetails($requestId) {
        $sql = "
            SELECT wr.*, 
                   a.referral_code, a.balance, a.pending_withdrawal,
                   u.name as affiliate_name, u.email as affiliate_email, u.phone as affiliate_phone,
                   admin.name as processed_by_name
            FROM {$this->table} wr
            LEFT JOIN affiliates a ON wr.affiliate_id = a.id
            LEFT JOIN users u ON a.user_id = u.id
            LEFT JOIN users admin ON wr.processed_by = admin.id
            WHERE wr.id = ?
        ";
        
        $result = $this->db->query($sql, [$requestId]);
        return $result ? $result[0] : null;
    }
    
    /**
     * Get requests by affiliate
     */
    public function getByAffiliate($affiliateId, $limit = null, $status = null) {
        $query = $this->where('affiliate_id', $affiliateId);
        
        if ($status) {
            $query->where('status', $status);
        }
        
        $query->orderBy('requested_at', 'DESC');
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->get();
    }
    
    /**
     * Get requests by status
     */
    public function getByStatus($status, $limit = null) {
        $query = $this->where('status', $status)->orderBy('requested_at', 'DESC');
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->get();
    }
    
    /**
     * Get request by withdraw code
     */
    public function getByWithdrawCode($withdrawCode) {
        return $this->findBy('withdraw_code', $withdrawCode);
    }
    
    /**
     * Update request status
     */
    public function updateStatus($requestId, $status, $processedBy = null, $adminNote = null) {
        $updateData = [
            'status' => $status,
            'processed_at' => date('Y-m-d H:i:s')
        ];
        
        if ($processedBy) {
            $updateData['processed_by'] = $processedBy;
        }
        
        if ($adminNote) {
            $updateData['admin_note'] = $adminNote;
        }
        
        // Set payment_completed_at for completed status
        if ($status === self::STATUS_COMPLETED) {
            $updateData['payment_completed_at'] = date('Y-m-d H:i:s');
        }
        
        return $this->update($requestId, $updateData);
    }
    
    /**
     * Update SePay transaction info
     */
    public function updateSepayInfo($requestId, $transactionId, $qrCode = null) {
        $updateData = [
            'sepay_transaction_id' => $transactionId,
            'status' => self::STATUS_PROCESSING
        ];
        
        if ($qrCode) {
            $updateData['sepay_qr_code'] = $qrCode;
            $updateData['qr_generated_at'] = date('Y-m-d H:i:s');
        }
        
        return $this->update($requestId, $updateData);
    }
    
    /**
     * Mark webhook received
     */
    public function markWebhookReceived($requestId, $webhookData) {
        $updateData = [
            'webhook_received_at' => date('Y-m-d H:i:s'),
            'webhook_data' => is_array($webhookData) ? json_encode($webhookData) : $webhookData
        ];
        
        return $this->update($requestId, $updateData);
    }
    
    /**
     * Get pending requests with details
     */
    public function getPendingWithDetails($limit = null) {
        $sql = "
            SELECT wr.*, 
                   a.referral_code, a.balance,
                   u.name as affiliate_name, u.email as affiliate_email, u.phone as affiliate_phone
            FROM {$this->table} wr
            LEFT JOIN affiliates a ON wr.affiliate_id = a.id
            LEFT JOIN users u ON a.user_id = u.id
            WHERE wr.status = ?
            ORDER BY wr.requested_at ASC
        ";
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        return $this->db->query($sql, [self::STATUS_PENDING]);
    }
    
    /**
     * Get requests with pagination and filters
     */
    public function getWithPagination($page = 1, $perPage = 20, $filters = []) {
        $offset = ($page - 1) * $perPage;
        $whereConditions = [];
        $bindings = [];
        
        // Apply filters
        if (!empty($filters['affiliate_id'])) {
            $whereConditions[] = "wr.affiliate_id = ?";
            $bindings[] = $filters['affiliate_id'];
        }
        
        if (!empty($filters['status'])) {
            $whereConditions[] = "wr.status = ?";
            $bindings[] = $filters['status'];
        }
        
        if (!empty($filters['date_from'])) {
            $whereConditions[] = "wr.requested_at >= ?";
            $bindings[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereConditions[] = "wr.requested_at <= ?";
            $bindings[] = $filters['date_to'];
        }
        
        if (!empty($filters['search'])) {
            $whereConditions[] = "(wr.withdraw_code LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $bindings[] = $searchTerm;
            $bindings[] = $searchTerm;
            $bindings[] = $searchTerm;
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        // Get requests
        $sql = "
            SELECT wr.*, 
                   a.referral_code,
                   u.name as affiliate_name, u.email as affiliate_email,
                   admin.name as processed_by_name
            FROM {$this->table} wr
            LEFT JOIN affiliates a ON wr.affiliate_id = a.id
            LEFT JOIN users u ON a.user_id = u.id
            LEFT JOIN users admin ON wr.processed_by = admin.id
            {$whereClause}
            ORDER BY wr.requested_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ";
        
        $requests = $this->db->query($sql, $bindings);
        
        // Get total count
        $countSql = "
            SELECT COUNT(*) as count 
            FROM {$this->table} wr
            LEFT JOIN affiliates a ON wr.affiliate_id = a.id
            LEFT JOIN users u ON a.user_id = u.id
            {$whereClause}
        ";
        $countResult = $this->db->query($countSql, $bindings);
        $total = $countResult[0]['count'] ?? 0;
        
        return [
            'data' => $requests,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => ceil($total / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total)
        ];
    }
    
    /**
     * Get withdrawal statistics
     */
    public function getStats($dateRange = null, $affiliateId = null) {
        $whereConditions = [];
        $bindings = [];
        
        if ($dateRange) {
            $whereConditions[] = "requested_at >= ?";
            $whereConditions[] = "requested_at <= ?";
            $bindings[] = $dateRange['start'];
            $bindings[] = $dateRange['end'];
        }
        
        if ($affiliateId) {
            $whereConditions[] = "affiliate_id = ?";
            $bindings[] = $affiliateId;
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        $sql = "
            SELECT 
                COUNT(*) as total_requests,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing_count,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_count,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count,
                SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as total_withdrawn,
                SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_amount,
                AVG(CASE WHEN status = 'completed' THEN amount ELSE NULL END) as avg_withdrawal_amount
            FROM {$this->table}
            {$whereClause}
        ";
        
        $result = $this->db->query($sql, $bindings);
        return $result ? $result[0] : [];
    }
    
    /**
     * Generate unique withdraw code
     */
    private function generateWithdrawCode() {
        $prefix = 'RUT';
        $date = date('ymd');
        
        // Get last withdraw code for today
        $sql = "SELECT withdraw_code FROM {$this->table} 
                WHERE withdraw_code LIKE ? 
                ORDER BY withdraw_code DESC LIMIT 1";
        
        $result = $this->db->query($sql, ["{$prefix}{$date}%"]);
        
        if (empty($result)) {
            $sequence = 1;
        } else {
            $lastCode = $result[0]['withdraw_code'];
            $sequence = intval(substr($lastCode, -4)) + 1;
        }
        
        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
