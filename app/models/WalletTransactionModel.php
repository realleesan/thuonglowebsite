<?php
/**
 * Wallet Transaction Model
 * Handles wallet transaction operations for affiliates
 */

require_once __DIR__ . '/BaseModel.php';

class WalletTransactionModel extends BaseModel {
    protected $table = 'wallet_transactions';
    protected $fillable = [
        'affiliate_id', 'type', 'amount', 'balance_before', 'balance_after',
        'reference_type', 'reference_id', 'order_id', 'withdrawal_id',
        'description', 'admin_note', 'metadata', 'status', 'created_by'
    ];
    
    // Transaction types
    const TYPE_COMMISSION = 'commission';
    const TYPE_WITHDRAWAL = 'withdrawal';
    const TYPE_ADJUSTMENT = 'adjustment';
    const TYPE_REFUND = 'refund';
    
    // Transaction statuses
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';
    
    /**
     * Create transaction with balance calculation
     */
    public function createTransaction($data) {
        // Validate required fields
        if (!isset($data['affiliate_id']) || !isset($data['type']) || !isset($data['amount'])) {
            throw new Exception('Missing required fields: affiliate_id, type, amount');
        }
        
        // Get current balance if not provided
        if (!isset($data['balance_before'])) {
            $affiliate = $this->db->table('affiliates')->find($data['affiliate_id']);
            if (!$affiliate) {
                throw new Exception('Affiliate not found');
            }
            $data['balance_before'] = $affiliate['balance'];
        }
        
        // Calculate balance_after if not provided
        if (!isset($data['balance_after'])) {
            $data['balance_after'] = $data['balance_before'] + $data['amount'];
        }
        
        // Set default status
        if (!isset($data['status'])) {
            $data['status'] = self::STATUS_COMPLETED;
        }
        
        // Encode metadata if it's an array
        if (isset($data['metadata']) && is_array($data['metadata'])) {
            $data['metadata'] = json_encode($data['metadata']);
        }
        
        return $this->create($data);
    }

    
    /**
     * Get transactions by affiliate
     */
    public function getByAffiliate($affiliateId, $limit = null, $type = null) {
        $query = $this->where('affiliate_id', $affiliateId);
        
        if ($type) {
            $query->where('type', $type);
        }
        
        $query->orderBy('created_at', 'DESC');
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->get();
    }
    
    /**
     * Get transaction with related data
     */
    public function getWithDetails($transactionId) {
        $sql = "
            SELECT wt.*, 
                   a.referral_code, a.user_id,
                   u.name as affiliate_name, u.email as affiliate_email,
                   o.order_number, o.total as order_total,
                   wr.withdraw_code, wr.status as withdrawal_status,
                   creator.name as created_by_name
            FROM {$this->table} wt
            LEFT JOIN affiliates a ON wt.affiliate_id = a.id
            LEFT JOIN users u ON a.user_id = u.id
            LEFT JOIN orders o ON wt.order_id = o.id
            LEFT JOIN withdrawal_requests wr ON wt.withdrawal_id = wr.id
            LEFT JOIN users creator ON wt.created_by = creator.id
            WHERE wt.id = ?
        ";
        
        $result = $this->db->query($sql, [$transactionId]);
        return $result ? $result[0] : null;
    }
    
    /**
     * Get transactions by type
     */
    public function getByType($type, $limit = null) {
        $query = $this->where('type', $type)->orderBy('created_at', 'DESC');
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->get();
    }
    
    /**
     * Get transactions by reference
     */
    public function getByReference($referenceType, $referenceId) {
        return $this->where('reference_type', $referenceType)
                    ->where('reference_id', $referenceId)
                    ->orderBy('created_at', 'DESC')
                    ->get();
    }
    
    /**
     * Get transactions by order
     */
    public function getByOrder($orderId) {
        return $this->where('order_id', $orderId)
                    ->orderBy('created_at', 'DESC')
                    ->get();
    }
    
    /**
     * Get transactions by withdrawal request
     */
    public function getByWithdrawal($withdrawalId) {
        return $this->where('withdrawal_id', $withdrawalId)
                    ->orderBy('created_at', 'DESC')
                    ->get();
    }
    
    /**
     * Get affiliate transaction summary
     */
    public function getAffiliateSummary($affiliateId, $dateRange = null) {
        $whereClause = "WHERE affiliate_id = ?";
        $bindings = [$affiliateId];
        
        if ($dateRange) {
            $whereClause .= " AND created_at >= ? AND created_at <= ?";
            $bindings[] = $dateRange['start'];
            $bindings[] = $dateRange['end'];
        }
        
        $sql = "
            SELECT 
                type,
                COUNT(*) as transaction_count,
                SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END) as total_credit,
                SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END) as total_debit,
                SUM(amount) as net_amount
            FROM {$this->table}
            {$whereClause}
            GROUP BY type
        ";
        
        return $this->db->query($sql, $bindings);
    }
    
    /**
     * Get recent transactions with pagination
     */
    public function getRecentWithDetails($page = 1, $perPage = 20, $filters = []) {
        $offset = ($page - 1) * $perPage;
        $whereConditions = [];
        $bindings = [];
        
        // Apply filters
        if (!empty($filters['affiliate_id'])) {
            $whereConditions[] = "wt.affiliate_id = ?";
            $bindings[] = $filters['affiliate_id'];
        }
        
        if (!empty($filters['type'])) {
            $whereConditions[] = "wt.type = ?";
            $bindings[] = $filters['type'];
        }
        
        if (!empty($filters['status'])) {
            $whereConditions[] = "wt.status = ?";
            $bindings[] = $filters['status'];
        }
        
        if (!empty($filters['date_from'])) {
            $whereConditions[] = "wt.created_at >= ?";
            $bindings[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereConditions[] = "wt.created_at <= ?";
            $bindings[] = $filters['date_to'];
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        // Get transactions
        $sql = "
            SELECT wt.*, 
                   a.referral_code,
                   u.name as affiliate_name,
                   o.order_number,
                   wr.withdraw_code
            FROM {$this->table} wt
            LEFT JOIN affiliates a ON wt.affiliate_id = a.id
            LEFT JOIN users u ON a.user_id = u.id
            LEFT JOIN orders o ON wt.order_id = o.id
            LEFT JOIN withdrawal_requests wr ON wt.withdrawal_id = wr.id
            {$whereClause}
            ORDER BY wt.created_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ";
        
        $transactions = $this->db->query($sql, $bindings);
        
        // Get total count
        $countSql = "SELECT COUNT(*) as count FROM {$this->table} wt {$whereClause}";
        $countResult = $this->db->query($countSql, $bindings);
        $total = $countResult[0]['count'] ?? 0;
        
        return [
            'data' => $transactions,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => ceil($total / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total)
        ];
    }
    
    /**
     * Get transaction statistics
     */
    public function getStats($dateRange = null) {
        $whereClause = '';
        $bindings = [];
        
        if ($dateRange) {
            $whereClause = "WHERE created_at >= ? AND created_at <= ?";
            $bindings = [$dateRange['start'], $dateRange['end']];
        }
        
        $sql = "
            SELECT 
                COUNT(*) as total_transactions,
                SUM(CASE WHEN type = 'commission' THEN amount ELSE 0 END) as total_commissions,
                SUM(CASE WHEN type = 'withdrawal' THEN ABS(amount) ELSE 0 END) as total_withdrawals,
                SUM(CASE WHEN type = 'adjustment' THEN amount ELSE 0 END) as total_adjustments,
                SUM(CASE WHEN type = 'refund' THEN amount ELSE 0 END) as total_refunds,
                COUNT(DISTINCT affiliate_id) as active_affiliates
            FROM {$this->table}
            {$whereClause}
        ";
        
        $result = $this->db->query($sql, $bindings);
        return $result ? $result[0] : [];
    }
}
