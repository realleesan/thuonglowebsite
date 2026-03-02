<?php
/**
 * Commission Service
 * Handles commission calculation and processing for affiliate program
 * 
 * Features:
 * - Calculate commission based on order
 * - Process commission when order is paid
 * - Refund commission when order is cancelled
 * - Track commission history
 * - Handle commission rules and rates
 */

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/WalletService.php';

class CommissionService extends BaseService
{
    private array $config;
    private WalletService $walletService;
    
    public function __construct(?ErrorHandler $errorHandler = null)
    {
        parent::__construct($errorHandler, 'commission');
        
        // Load config
        $globalConfig = require __DIR__ . '/../../config.php';
        $this->config = $globalConfig['commission'] ?? [];
        
        // Initialize wallet service
        $this->walletService = new WalletService($errorHandler);
    }
    
    /**
     * Calculate commission for an order
     * 
     * @param int $orderId Order ID
     * @return array ['success' => bool, 'commission' => float, 'rate' => float, 'affiliate_id' => int]
     */
    public function calculateCommission(int $orderId): array
    {
        try {
            $orderModel = $this->getModel('OrdersModel');
            $affiliateModel = $this->getModel('AffiliateModel');
            
            // Get order
            $order = $orderModel->find($orderId);
            if (!$order) {
                throw new Exception('Order not found');
            }
            
            // Check if order has affiliate
            $affiliateId = $order['affiliate_id'] ?? null;
            if (!$affiliateId) {
                return [
                    'success' => true,
                    'commission' => 0,
                    'rate' => 0,
                    'message' => 'No affiliate associated with this order',
                ];
            }
            
            // Get affiliate
            $affiliate = $affiliateModel->find($affiliateId);
            if (!$affiliate) {
                throw new Exception('Affiliate not found');
            }
            
            // Check affiliate status
            if ($affiliate['status'] !== 'active') {
                return [
                    'success' => true,
                    'commission' => 0,
                    'rate' => 0,
                    'message' => 'Affiliate is not active',
                ];
            }
            
            // Get order total
            $orderTotal = (float)($order['total'] ?? 0);
            
            // Check minimum order amount
            $minOrder = $this->config['min_order_for_commission'] ?? 0;
            if ($orderTotal < $minOrder) {
                return [
                    'success' => true,
                    'commission' => 0,
                    'rate' => 0,
                    'message' => 'Order amount below minimum for commission',
                ];
            }
            
            // Get commission rate
            $rate = (float)($affiliate['commission_rate'] ?? $this->config['default_rate'] ?? 10);
            
            // Calculate commission
            $commission = ($orderTotal * $rate) / 100;
            
            // Round to 2 decimal places
            $commission = round($commission, 2);
            
            // Log calculation
            $this->logCommission('calculated', [
                'order_id' => $orderId,
                'affiliate_id' => $affiliateId,
                'order_total' => $orderTotal,
                'rate' => $rate,
                'commission' => $commission,
            ]);
            
            return [
                'success' => true,
                'commission' => $commission,
                'rate' => $rate,
                'affiliate_id' => $affiliateId,
                'order_total' => $orderTotal,
            ];
            
        } catch (Exception $e) {
            return $this->handleError($e, ['order_id' => $orderId]);
        }
    }
    
    /**
     * Process commission when order is paid
     * This is called from webhook when payment is successful
     * 
     * @param int $orderId Order ID
     * @return array ['success' => bool, 'commission' => float, 'transaction_id' => int]
     */
    public function processOrderCommission(int $orderId): array
    {
        try {
            $orderModel = $this->getModel('OrdersModel');
            $affiliateModel = $this->getModel('AffiliateModel');
            
            // Get order
            $order = $orderModel->find($orderId);
            if (!$order) {
                throw new Exception('Order not found');
            }
            
            // Check if commission already processed
            if (!empty($order['commission_amount']) && $order['commission_amount'] > 0) {
                return [
                    'success' => true,
                    'commission' => (float)$order['commission_amount'],
                    'message' => 'Commission already processed',
                ];
            }
            
            // Calculate commission
            $calculation = $this->calculateCommission($orderId);
            
            if (!$calculation['success'] || $calculation['commission'] <= 0) {
                return [
                    'success' => true,
                    'commission' => 0,
                    'message' => $calculation['message'] ?? 'No commission to process',
                ];
            }
            
            $commission = $calculation['commission'];
            $affiliateId = $calculation['affiliate_id'];
            
            // Add commission to wallet
            $walletResult = $this->walletService->addCommission(
                $affiliateId,
                $orderId,
                $commission,
                "Commission from order #{$order['order_number']}"
            );
            
            if (!$walletResult['success']) {
                throw new Exception('Failed to add commission to wallet');
            }
            
            // Update order with commission amount
            $orderModel->update($orderId, [
                'commission_amount' => $commission,
            ]);
            
            // Update affiliate totals
            $affiliate = $affiliateModel->find($affiliateId);
            $affiliateModel->update($affiliateId, [
                'total_sales' => ($affiliate['total_sales'] ?? 0) + $order['total'],
            ]);
            
            // Log success
            $this->logCommission('processed', [
                'order_id' => $orderId,
                'affiliate_id' => $affiliateId,
                'commission' => $commission,
                'transaction_id' => $walletResult['transaction_id'] ?? null,
            ]);
            
            // Send notification email (if EmailNotificationService is available)
            $this->sendCommissionNotification($affiliateId, $orderId, $commission);
            
            return [
                'success' => true,
                'commission' => $commission,
                'transaction_id' => $walletResult['transaction_id'] ?? null,
                'new_balance' => $walletResult['new_balance'] ?? 0,
            ];
            
        } catch (Exception $e) {
            return $this->handleError($e, ['order_id' => $orderId]);
        }
    }
    
    /**
     * Refund commission when order is cancelled/refunded
     * 
     * @param int $orderId Order ID
     * @param string $reason Reason for refund
     * @return array ['success' => bool, 'refunded_amount' => float]
     */
    public function refundCommission(int $orderId, string $reason = 'Order cancelled'): array
    {
        try {
            $orderModel = $this->getModel('OrdersModel');
            
            // Get order
            $order = $orderModel->find($orderId);
            if (!$order) {
                throw new Exception('Order not found');
            }
            
            // Check if there's commission to refund
            $commissionAmount = (float)($order['commission_amount'] ?? 0);
            if ($commissionAmount <= 0) {
                return [
                    'success' => true,
                    'refunded_amount' => 0,
                    'message' => 'No commission to refund',
                ];
            }
            
            $affiliateId = $order['affiliate_id'];
            if (!$affiliateId) {
                throw new Exception('No affiliate associated with this order');
            }
            
            // Adjust wallet balance (negative amount)
            $adjustResult = $this->walletService->adjustBalance(
                $affiliateId,
                -$commissionAmount,
                "Commission refund: $reason (Order #{$order['order_number']})",
                $orderId
            );
            
            if (!$adjustResult['success']) {
                throw new Exception('Failed to refund commission');
            }
            
            // Update order commission to 0
            $orderModel->update($orderId, [
                'commission_amount' => 0,
            ]);
            
            // Log refund
            $this->logCommission('refunded', [
                'order_id' => $orderId,
                'affiliate_id' => $affiliateId,
                'refunded_amount' => $commissionAmount,
                'reason' => $reason,
            ]);
            
            return [
                'success' => true,
                'refunded_amount' => $commissionAmount,
                'new_balance' => $adjustResult['new_balance'] ?? 0,
            ];
            
        } catch (Exception $e) {
            return $this->handleError($e, [
                'order_id' => $orderId,
                'reason' => $reason,
            ]);
        }
    }
    
    /**
     * Get commission statistics for affiliate
     * 
     * @param int $affiliateId Affiliate ID
     * @param string|null $startDate Start date (Y-m-d)
     * @param string|null $endDate End date (Y-m-d)
     * @return array Commission statistics
     */
    public function getAffiliateStats(int $affiliateId, ?string $startDate = null, ?string $endDate = null): array
    {
        try {
            $orderModel = $this->getModel('OrdersModel');
            $transactionModel = $this->getModel('WalletTransactionModel');
            
            // Build date filter
            $dateFilter = '';
            $params = [$affiliateId];
            
            if ($startDate && $endDate) {
                $dateFilter = " AND o.created_at BETWEEN ? AND ?";
                $params[] = $startDate . ' 00:00:00';
                $params[] = $endDate . ' 23:59:59';
            }
            
            // Get order statistics
            $sql = "
                SELECT 
                    COUNT(*) as total_orders,
                    SUM(o.total) as total_sales,
                    SUM(o.commission_amount) as total_commission,
                    AVG(o.commission_amount) as avg_commission
                FROM orders o
                WHERE o.affiliate_id = ? 
                AND o.payment_status = 'paid'
                AND o.commission_amount > 0
                $dateFilter
            ";
            
            $db = $orderModel->getDb();
            $stats = $db->query($sql, $params);
            $orderStats = $stats[0] ?? [];
            
            // Get commission transactions
            $transactions = $transactionModel->getByAffiliate($affiliateId, 100);
            $commissionTransactions = array_filter($transactions, function($t) {
                return $t['type'] === 'commission';
            });
            
            return [
                'success' => true,
                'total_orders' => (int)($orderStats['total_orders'] ?? 0),
                'total_sales' => (float)($orderStats['total_sales'] ?? 0),
                'total_commission' => (float)($orderStats['total_commission'] ?? 0),
                'avg_commission' => (float)($orderStats['avg_commission'] ?? 0),
                'recent_commissions' => array_slice($commissionTransactions, 0, 10),
            ];
            
        } catch (Exception $e) {
            return $this->handleError($e, ['affiliate_id' => $affiliateId]);
        }
    }
    
    /**
     * Get top earning affiliates
     * 
     * @param int $limit Number of affiliates to return
     * @param string $period 'today', 'week', 'month', 'year', 'all'
     * @return array Top affiliates
     */
    public function getTopAffiliates(int $limit = 10, string $period = 'month'): array
    {
        try {
            $affiliateModel = $this->getModel('AffiliateModel');
            
            // Calculate date range
            $dateFilter = $this->getDateFilterForPeriod($period);
            
            $sql = "
                SELECT 
                    a.id,
                    a.user_id,
                    a.referral_code,
                    a.commission_rate,
                    u.name as user_name,
                    u.email as user_email,
                    COUNT(o.id) as order_count,
                    SUM(o.total) as total_sales,
                    SUM(o.commission_amount) as total_commission
                FROM affiliates a
                LEFT JOIN users u ON a.user_id = u.id
                LEFT JOIN orders o ON o.affiliate_id = a.id 
                    AND o.payment_status = 'paid' 
                    AND o.commission_amount > 0
                    $dateFilter
                WHERE a.status = 'active'
                GROUP BY a.id
                ORDER BY total_commission DESC
                LIMIT $limit
            ";
            
            $db = $affiliateModel->getDb();
            $topAffiliates = $db->query($sql);
            
            return [
                'success' => true,
                'affiliates' => $topAffiliates,
                'period' => $period,
            ];
            
        } catch (Exception $e) {
            return $this->handleError($e, ['limit' => $limit, 'period' => $period]);
        }
    }
    
    /**
     * Check if auto-credit is enabled
     * 
     * @return bool
     */
    public function isAutoCreditEnabled(): bool
    {
        return $this->config['auto_credit'] ?? true;
    }
    
    // ==================== PRIVATE METHODS ====================
    
    /**
     * Get date filter SQL for period
     */
    private function getDateFilterForPeriod(string $period): string
    {
        switch ($period) {
            case 'today':
                return " AND DATE(o.created_at) = CURDATE()";
            case 'week':
                return " AND o.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            case 'month':
                return " AND o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            case 'year':
                return " AND YEAR(o.created_at) = YEAR(NOW())";
            case 'all':
            default:
                return "";
        }
    }
    
    /**
     * Send commission notification email
     */
    private function sendCommissionNotification(int $affiliateId, int $orderId, float $commission): void
    {
        try {
            // Check if EmailNotificationService exists
            if (!class_exists('EmailNotificationService')) {
                return;
            }
            
            $emailService = new EmailNotificationService();
            $affiliateModel = $this->getModel('AffiliateModel');
            $orderModel = $this->getModel('OrdersModel');
            
            $affiliate = $affiliateModel->getWithUser($affiliateId);
            $order = $orderModel->find($orderId);
            
            if ($affiliate && $order) {
                $emailService->sendCommissionEarned(
                    $affiliate['email'],
                    $affiliate['name'],
                    $commission,
                    $order['order_number']
                );
            }
            
        } catch (Exception $e) {
            // Don't fail if email fails
            $this->errorHandler->logWarning('Failed to send commission notification email', [
                'affiliate_id' => $affiliateId,
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Log commission activity
     */
    private function logCommission(string $action, array $data): void
    {
        $logFile = __DIR__ . '/../../logs/commission.log';
        $logEntry = date('Y-m-d H:i:s') . " | $action | " . json_encode($data) . PHP_EOL;
        @file_put_contents($logFile, $logEntry, FILE_APPEND);
    }
}
