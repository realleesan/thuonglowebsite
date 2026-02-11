<?php
/**
 * Affiliate Model
 * Handles affiliate program operations with database
 */

require_once __DIR__ . '/BaseModel.php';

class AffiliateModel extends BaseModel {
    protected $table = 'affiliates';
    protected $fillable = [
        'user_id', 'referral_code', 'commission_rate', 'total_sales',
        'total_commission', 'paid_commission', 'pending_commission',
        'status', 'payment_method', 'payment_details', 'approved_by'
    ];
    
    /**
     * Get affiliate with user information
     */
    public function getWithUser($affiliateId) {
        $sql = "
            SELECT a.*, u.name, u.email, u.phone
            FROM {$this->table} a
            LEFT JOIN users u ON a.user_id = u.id
            WHERE a.id = ?
        ";
        
        $result = $this->db->query($sql, [$affiliateId]);
        return $result ? $result[0] : null;
    }
    
    /**
     * Get affiliate by user ID
     */
    public function getByUserId($userId) {
        return $this->findBy('user_id', $userId);
    }
    
    /**
     * Get affiliate by referral code
     */
    public function getByReferralCode($code) {
        return $this->findBy('referral_code', $code);
    }
    
    /**
     * Create new affiliate
     */
    public function createAffiliate($userId, $commissionRate = 10) {
        // Check if user already has affiliate account
        if ($this->getByUserId($userId)) {
            throw new Exception('User already has an affiliate account');
        }
        
        // Generate unique referral code
        $referralCode = $this->generateReferralCode($userId);
        
        $data = [
            'user_id' => $userId,
            'referral_code' => $referralCode,
            'commission_rate' => $commissionRate,
            'status' => 'pending'
        ];
        
        return $this->create($data);
    }
    
    /**
     * Approve affiliate
     */
    public function approve($affiliateId, $approvedBy) {
        return $this->update($affiliateId, [
            'status' => 'active',
            'approved_by' => $approvedBy,
            'approved_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Record commission from order
     */
    public function recordCommission($affiliateId, $orderTotal, $orderId) {
        $affiliate = $this->find($affiliateId);
        if (!$affiliate || $affiliate['status'] !== 'active') {
            return false;
        }
        
        $commission = ($orderTotal * $affiliate['commission_rate']) / 100;
        
        // Update affiliate totals
        $this->update($affiliateId, [
            'total_sales' => $affiliate['total_sales'] + $orderTotal,
            'total_commission' => $affiliate['total_commission'] + $commission,
            'pending_commission' => $affiliate['pending_commission'] + $commission
        ]);
        
        // Record commission transaction (if you have a commissions table)
        // This would be implemented if you add a separate commissions tracking table
        
        return $commission;
    }
    
    /**
     * Process commission payment
     */
    public function processPayment($affiliateId, $amount, $paymentMethod, $reference = null) {
        $affiliate = $this->find($affiliateId);
        if (!$affiliate) {
            return false;
        }
        
        if ($amount > $affiliate['pending_commission']) {
            throw new Exception('Payment amount exceeds pending commission');
        }
        
        return $this->update($affiliateId, [
            'paid_commission' => $affiliate['paid_commission'] + $amount,
            'pending_commission' => $affiliate['pending_commission'] - $amount,
            'payment_method' => $paymentMethod
        ]);
    }
    
    /**
     * Get affiliate statistics
     */
    public function getStats($affiliateId = null) {
        $whereClause = $affiliateId ? "WHERE id = {$affiliateId}" : '';
        
        $sql = "
            SELECT 
                COUNT(*) as total_affiliates,
                SUM(total_sales) as total_sales,
                SUM(total_commission) as total_commission,
                SUM(paid_commission) as paid_commission,
                SUM(pending_commission) as pending_commission,
                AVG(commission_rate) as avg_commission_rate
            FROM {$this->table}
            {$whereClause}
        ";
        
        $result = $this->db->query($sql);
        return $result ? $result[0] : [];
    }
    
    /**
     * Get top performing affiliates
     */
    public function getTopPerformers($limit = 10) {
        $sql = "
            SELECT a.*, u.name, u.email
            FROM {$this->table} a
            LEFT JOIN users u ON a.user_id = u.id
            WHERE a.status = 'active'
            ORDER BY a.total_sales DESC
            LIMIT {$limit}
        ";
        
        return $this->db->query($sql);
    }
    
    /**
     * Generate unique referral code
     */
    private function generateReferralCode($userId) {
        $baseCode = 'REF' . str_pad($userId, 4, '0', STR_PAD_LEFT);
        $code = $baseCode;
        $counter = 1;
        
        while ($this->getByReferralCode($code)) {
            $code = $baseCode . $counter;
            $counter++;
        }
        
        return $code;
    }
    
    /**
     * Get affiliate dashboard data
     */
    public function getDashboardData($affiliateId) {
        $affiliate = $this->find($affiliateId);
        if (!$affiliate) {
            return null;
        }
        
        // Get recent orders through this affiliate
        $recentOrders = $this->db->query("
            SELECT o.*, u.name as customer_name
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            WHERE o.affiliate_id = ?
            ORDER BY o.created_at DESC
            LIMIT 10
        ", [$affiliateId]);
        
        // Get monthly performance
        $monthlyStats = $this->db->query("
            SELECT 
                MONTH(created_at) as month,
                YEAR(created_at) as year,
                COUNT(*) as orders_count,
                SUM(total) as sales,
                SUM(commission_amount) as commission
            FROM orders
            WHERE affiliate_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY YEAR(created_at), MONTH(created_at)
            ORDER BY year DESC, month DESC
        ", [$affiliateId]);
        
        return [
            'affiliate' => $affiliate,
            'recent_orders' => $recentOrders,
            'monthly_stats' => $monthlyStats
        ];
    }
}