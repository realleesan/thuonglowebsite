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
        'status', 'payment_method', 'payment_details', 'approved_by', 'additional_info',
        // Wallet fields
        'balance', 'pending_withdrawal', 'total_withdrawn',
        // Bank information
        'bank_name', 'bank_account', 'account_holder', 'bank_branch',
        'bank_verified', 'bank_verified_at',
        // OTP for bank changes
        'bank_change_otp', 'bank_change_otp_expires_at', 'bank_last_changed_at'
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
     * Create affiliate with custom data (for admin approval)
     */
    public function createAffiliateWithData($data) {
        // Set default values if not provided
        $data = array_merge([
            'referral_code' => $this->generateReferralCode($data['user_id']),
            'commission_rate' => 10,
            'total_sales' => 0,
            'total_commission' => 0,
            'paid_commission' => 0,
            'pending_commission' => 0
        ], $data);

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
     * Get commissions for affiliate
     */
    public function getCommissions($affiliateId, $limit = null) {
        $sql = "
            SELECT o.id as order_id, o.order_number, o.total as order_total,
                   o.commission_amount as amount, o.status, o.created_at,
                   u.name as customer_name
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            WHERE o.affiliate_id = ? AND o.commission_amount > 0
            ORDER BY o.created_at DESC
        ";
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        return $this->db->query($sql, [$affiliateId]);
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
    
    // ==================== WALLET MANAGEMENT METHODS ====================
    
    /**
     * Credit balance (add money to wallet)
     */
    public function creditBalance($affiliateId, $amount, $description = null) {
        if ($amount <= 0) {
            throw new Exception('Credit amount must be positive');
        }
        
        $affiliate = $this->find($affiliateId);
        if (!$affiliate) {
            throw new Exception('Affiliate not found');
        }
        
        $newBalance = $affiliate['balance'] + $amount;
        
        return $this->update($affiliateId, [
            'balance' => $newBalance
        ]);
    }
    
    /**
     * Debit balance (subtract money from wallet)
     */
    public function debitBalance($affiliateId, $amount, $description = null) {
        if ($amount <= 0) {
            throw new Exception('Debit amount must be positive');
        }
        
        $affiliate = $this->find($affiliateId);
        if (!$affiliate) {
            throw new Exception('Affiliate not found');
        }
        
        if ($affiliate['balance'] < $amount) {
            throw new Exception('Insufficient balance');
        }
        
        $newBalance = $affiliate['balance'] - $amount;
        
        return $this->update($affiliateId, [
            'balance' => $newBalance
        ]);
    }
    
    /**
     * Freeze balance for withdrawal (move from balance to pending_withdrawal)
     */
    public function freezeBalance($affiliateId, $amount) {
        if ($amount <= 0) {
            throw new Exception('Freeze amount must be positive');
        }
        
        $affiliate = $this->find($affiliateId);
        if (!$affiliate) {
            throw new Exception('Affiliate not found');
        }
        
        if ($affiliate['balance'] < $amount) {
            throw new Exception('Insufficient balance to freeze');
        }
        
        return $this->update($affiliateId, [
            'balance' => $affiliate['balance'] - $amount,
            'pending_withdrawal' => $affiliate['pending_withdrawal'] + $amount
        ]);
    }
    
    /**
     * Unfreeze balance (move from pending_withdrawal back to balance)
     */
    public function unfreezeBalance($affiliateId, $amount) {
        if ($amount <= 0) {
            throw new Exception('Unfreeze amount must be positive');
        }
        
        $affiliate = $this->find($affiliateId);
        if (!$affiliate) {
            throw new Exception('Affiliate not found');
        }
        
        if ($affiliate['pending_withdrawal'] < $amount) {
            throw new Exception('Insufficient pending withdrawal to unfreeze');
        }
        
        return $this->update($affiliateId, [
            'balance' => $affiliate['balance'] + $amount,
            'pending_withdrawal' => $affiliate['pending_withdrawal'] - $amount
        ]);
    }
    
    /**
     * Complete withdrawal (move from pending_withdrawal to total_withdrawn)
     */
    public function completeWithdrawal($affiliateId, $amount) {
        if ($amount <= 0) {
            throw new Exception('Withdrawal amount must be positive');
        }
        
        $affiliate = $this->find($affiliateId);
        if (!$affiliate) {
            throw new Exception('Affiliate not found');
        }
        
        if ($affiliate['pending_withdrawal'] < $amount) {
            throw new Exception('Insufficient pending withdrawal');
        }
        
        return $this->update($affiliateId, [
            'pending_withdrawal' => $affiliate['pending_withdrawal'] - $amount,
            'total_withdrawn' => $affiliate['total_withdrawn'] + $amount
        ]);
    }
    
    /**
     * Get wallet balance info
     */
    public function getWalletBalance($affiliateId) {
        $affiliate = $this->find($affiliateId);
        if (!$affiliate) {
            return null;
        }
        
        return [
            'balance' => $affiliate['balance'],
            'pending_withdrawal' => $affiliate['pending_withdrawal'],
            'total_withdrawn' => $affiliate['total_withdrawn'],
            'available_for_withdrawal' => $affiliate['balance']
        ];
    }
    
    // ==================== BANK INFORMATION METHODS ====================
    
    /**
     * Update bank information
     */
    public function updateBankInfo($affiliateId, $bankData) {
        $updateData = [];
        
        if (isset($bankData['bank_name'])) {
            $updateData['bank_name'] = $bankData['bank_name'];
        }
        
        if (isset($bankData['bank_account'])) {
            $updateData['bank_account'] = $bankData['bank_account'];
        }
        
        if (isset($bankData['account_holder'])) {
            $updateData['account_holder'] = $bankData['account_holder'];
        }
        
        if (isset($bankData['bank_branch'])) {
            $updateData['bank_branch'] = $bankData['bank_branch'];
        }
        
        // Reset verification when bank info changes
        $updateData['bank_verified'] = 0;
        $updateData['bank_verified_at'] = null;
        $updateData['bank_last_changed_at'] = date('Y-m-d H:i:s');
        
        return $this->update($affiliateId, $updateData);
    }
    
    /**
     * Verify bank information
     */
    public function verifyBankInfo($affiliateId) {
        return $this->update($affiliateId, [
            'bank_verified' => 1,
            'bank_verified_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Check if bank info is complete
     */
    public function hasBankInfo($affiliateId) {
        $affiliate = $this->find($affiliateId);
        if (!$affiliate) {
            return false;
        }
        
        return !empty($affiliate['bank_name']) && 
               !empty($affiliate['bank_account']) && 
               !empty($affiliate['account_holder']);
    }
    
    /**
     * Get bank information
     */
    public function getBankInfo($affiliateId) {
        $affiliate = $this->find($affiliateId);
        if (!$affiliate) {
            return null;
        }
        
        return [
            'bank_name' => $affiliate['bank_name'],
            'bank_account' => $affiliate['bank_account'],
            'account_holder' => $affiliate['account_holder'],
            'bank_branch' => $affiliate['bank_branch'],
            'bank_verified' => $affiliate['bank_verified'],
            'bank_verified_at' => $affiliate['bank_verified_at'],
            'bank_last_changed_at' => $affiliate['bank_last_changed_at']
        ];
    }
    
    // ==================== OTP METHODS ====================
    
    /**
     * Generate and save OTP for bank info change
     */
    public function generateBankChangeOTP($affiliateId) {
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        $this->update($affiliateId, [
            'bank_change_otp' => $otp,
            'bank_change_otp_expires_at' => $expiresAt
        ]);
        
        return $otp;
    }
    
    /**
     * Verify OTP for bank info change
     */
    public function verifyBankChangeOTP($affiliateId, $otp) {
        $affiliate = $this->find($affiliateId);
        if (!$affiliate) {
            return false;
        }
        
        // Check if OTP matches
        if ($affiliate['bank_change_otp'] !== $otp) {
            return false;
        }
        
        // Check if OTP is expired
        if (strtotime($affiliate['bank_change_otp_expires_at']) < time()) {
            return false;
        }
        
        // Clear OTP after successful verification
        $this->update($affiliateId, [
            'bank_change_otp' => null,
            'bank_change_otp_expires_at' => null
        ]);
        
        return true;
    }
    
    /**
     * Clear OTP
     */
    public function clearBankChangeOTP($affiliateId) {
        return $this->update($affiliateId, [
            'bank_change_otp' => null,
            'bank_change_otp_expires_at' => null
        ]);
    }
}
