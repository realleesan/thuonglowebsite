<?php
/**
 * Orders Model
 * Handles order data operations with database
 */

require_once __DIR__ . '/BaseModel.php';

class OrdersModel extends BaseModel {
    protected $table = 'orders';
    protected $fillable = [
        'order_number', 'user_id', 'status', 'payment_status', 'payment_method',
        'payment_reference', 'subtotal', 'tax_amount', 'shipping_amount', 
        'discount_amount', 'total', 'shipping_name', 'shipping_email', 
        'shipping_phone', 'shipping_address', 'shipping_city', 'shipping_state',
        'shipping_postal_code', 'shipping_country', 'billing_name', 'billing_email',
        'billing_phone', 'billing_address', 'billing_city', 'billing_state',
        'billing_postal_code', 'billing_country', 'notes', 'admin_notes',
        'coupon_code', 'affiliate_id', 'commission_amount'
    ];
    
    // Default expiry days for products (30 days)
    const DEFAULT_EXPIRY_DAYS = 30;
    // Default quota for products (100)
    const DEFAULT_QUOTA = 100;
    // Default quota per usage (10)
    const DEFAULT_QUOTA_PER_USAGE = 10;
    
    /**
     * Get expiry days for a product
     */
    private function getProductExpiryDays($productId) {
        if (!$productId) {
            return self::DEFAULT_EXPIRY_DAYS;
        }
        
        $product = $this->db->query("SELECT expiry_days FROM products WHERE id = ?", [$productId]);
        if (!empty($product) && !empty($product[0]['expiry_days'])) {
            return (int) $product[0]['expiry_days'];
        }
        return self::DEFAULT_EXPIRY_DAYS;
    }
    
    /**
     * Get quota for a product
     */
    private function getProductQuota($productId) {
        if (!$productId) {
            return self::DEFAULT_QUOTA;
        }
        
        $product = $this->db->query("SELECT quota FROM products WHERE id = ?", [$productId]);
        if (!empty($product) && !empty($product[0]['quota'])) {
            return (int) $product[0]['quota'];
        }
        return self::DEFAULT_QUOTA;
    }
    
    /**
     * Get quota per usage for a product
     */
    private function getProductQuotaPerUsage($productId) {
        if (!$productId) {
            return self::DEFAULT_QUOTA_PER_USAGE;
        }
        
        $product = $this->db->query("SELECT quota_per_usage FROM products WHERE id = ?", [$productId]);
        if (!empty($product) && !empty($product[0]['quota_per_usage'])) {
            return (int) $product[0]['quota_per_usage'];
        }
        return self::DEFAULT_QUOTA_PER_USAGE;
    }
    
    /**
     * Get used quota for a specific purchased product
     */
    public function getProductUsedQuota($userId, $productId) {
        $sql = "
            SELECT oi.used_quota
            FROM {$this->table} o
            INNER JOIN order_items oi ON o.id = oi.order_id
            WHERE o.user_id = ? 
              AND oi.product_id = ?
              AND o.status = 'completed'
              AND (oi.expiry_date IS NULL OR oi.expiry_date > NOW())
            ORDER BY oi.expiry_date DESC
            LIMIT 1
        ";
        
        $result = $this->db->query($sql, [$userId, $productId]);
        return !empty($result) ? (int) $result[0]['used_quota'] : 0;
    }
    
    /**
     * Update used quota for a purchased product (deduct quota when viewing)
     */
    public function deductQuota($userId, $productId) {
        // Fixed quota deduction: 10 per click
        $quotaPerUsage = 10;
        
        // Get quota info from all valid orders (same logic as getProductQuotaInfo)
        $quotaInfo = $this->getProductQuotaInfo($userId, $productId);
        $productQuota = $quotaInfo['total'];
        $currentUsed = $quotaInfo['used'];
        $remainingQuota = $quotaInfo['remaining'];
        
        // Only deduct if there's remaining quota
        if ($remainingQuota <= 0) {
            return ['success' => false, 'message' => 'Quota đã hết', 'remaining' => 0];
        }
        
        // Check if remaining quota is enough for deduction
        if ($remainingQuota < $quotaPerUsage) {
            return [
                'success' => false, 
                'message' => 'Không đủ quota. Cần ít nhất ' . $quotaPerUsage . ' quota', 
                'remaining' => $remainingQuota
            ];
        }
        
        // Determine how much to deduct (can't exceed remaining)
        $toDeduct = min($quotaPerUsage, $remainingQuota);
        
        // Update the order_item with highest expiry (most recent valid purchase)
        $sql = "
            UPDATE order_items oi
            INNER JOIN {$this->table} o ON o.id = oi.order_id
            SET oi.used_quota = oi.used_quota + ?
            WHERE o.user_id = ? 
              AND oi.product_id = ?
              AND o.status = 'completed'
              AND (oi.expiry_date IS NULL OR oi.expiry_date > NOW())
            ORDER BY oi.expiry_date DESC
            LIMIT 1
        ";
        
        $this->db->query($sql, [$toDeduct, $userId, $productId]);
        
        // Create access token for data viewing
        require_once __DIR__ . '/ProductDataModel.php';
        $productDataModel = new ProductDataModel();
        
        // Get view duration from product (default 15 minutes)
        $productsModel = new ProductsModel();
        $product = $productsModel->find($productId);
        $viewDuration = !empty($product['data_view_duration']) ? (int)$product['data_view_duration'] : 15;
        
        // Create access token
        $access = $productDataModel->createAccess($userId, $productId, $viewDuration);
        
        return [
            'success' => true,
            'message' => "Đã sử dụng {$toDeduct} quota",
            'deducted' => $toDeduct,
            'remaining' => $remainingQuota - $toDeduct,
            'access_token' => $access['access_token'],
            'expires_at' => $access['expires_at']
        ];
    }
    
    /**
     * Create new order with items
     */
    public function createOrder($orderData, $items, $isRenewal = false) {
        $this->beginTransaction();
        
        try {
            // Generate order number only if not provided
            if (empty($orderData['order_number'])) {
                $orderData['order_number'] = $this->generateOrderNumber();
            }
            
            // Calculate totals
            $subtotal = 0;
            foreach ($items as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }
            
            $orderData['subtotal'] = $subtotal;
            $orderData['total'] = $subtotal + ($orderData['tax_amount'] ?? 0) + 
                                 ($orderData['shipping_amount'] ?? 0) - 
                                 ($orderData['discount_amount'] ?? 0);
            
            // Create order - create() returns the insert ID
            $orderId = $this->create($orderData);
            
            if (!$orderId) {
                throw new Exception('Failed to create order');
            }
            
            // Get the order ID (could be int or string)
            $orderId = (int) $orderId;
            
            // Get user_id from order data
            $userId = $orderData['user_id'] ?? null;
            
            // Create order items
            foreach ($items as $item) {
                // Get product's expiry days setting
                $expiryDays = $this->getProductExpiryDays($item['product_id'] ?? null);
                // Get product's quota setting
                $quota = $this->getProductQuota($item['product_id'] ?? null);
                
                // Calculate expiry date
                $expiryDate = null;
                
                if ($isRenewal && $userId && isset($item['product_id'])) {
                    // For renewal, extend the existing expiry date
                    $existingExpiry = $this->getProductExpiryDate($userId, $item['product_id']);
                    if ($existingExpiry) {
                        // Add product's expiry days to existing expiry
                        $expiryDate = date('Y-m-d H:i:s', strtotime($existingExpiry . ' +' . $expiryDays . ' days'));
                    } else {
                        // No existing expiry, start from now + product's expiry days
                        $expiryDate = date('Y-m-d H:i:s', strtotime('+' . $expiryDays . ' days'));
                    }
                } else {
                    // New purchase: expiry = now + product's expiry days
                    $expiryDate = date('Y-m-d H:i:s', strtotime('+' . $expiryDays . ' days'));
                }
                
                $itemData = [
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'] ?? 'Sản phẩm',
                    'product_sku' => $item['product_sku'] ?? null,
                    'product_type' => $item['product_type'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['price'] * $item['quantity'],
                    'expiry_date' => $expiryDate,
                    'used_quota' => 0, // Start with 0 used quota
                    'product_data' => json_encode($item['product_data'] ?? [])
                ];
                
                $this->db->table('order_items')->insert($itemData);
                
                // Update product sales count and stock
                if (isset($item['product_id'])) {
                    $this->db->query(
                        "UPDATE products SET sales_count = sales_count + ?, stock = stock - ? WHERE id = ?",
                        [$item['quantity'], $item['quantity'], $item['product_id']]
                    );
                }
            }
            
            $this->commit();
            return $this->getOrderWithItems($orderId);
            
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
    
    /**
     * Get order with items and user info
     */
    public function getOrderWithItems($orderId) {
        // Get order with user info
        $sql = "
            SELECT o.*, u.name as user_name, u.email as user_email
            FROM {$this->table} o
            LEFT JOIN users u ON o.user_id = u.id
            WHERE o.id = ?
        ";
        
        $order = $this->db->query($sql, [$orderId]);
        if (empty($order)) {
            return null;
        }
        
        $order = $order[0];
        
        // Get order items
        $itemsSql = "
            SELECT oi.*, p.name as current_product_name, p.image as current_product_image
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ";
        
        $order['items'] = $this->db->query($itemsSql, [$orderId]);
        
        return $order;
    }
    
    /**
     * Get orders by user
     */
    public function getByUser($userId, $limit = null) {
        $sql = "
            SELECT o.*, 
                   oi.product_name,
                   oi.product_type as type,
                   oi.product_id,
                   p.name as product_name_db,
                   p.image as product_image,
                   p.type as product_type_db,
                   c.name as category_name,
                   COUNT(oi.id) as items_count
            FROM {$this->table} o
            LEFT JOIN order_items oi ON o.id = oi.order_id
            LEFT JOIN products p ON oi.product_id = p.id
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE o.user_id = ?
            GROUP BY o.id
            ORDER BY o.created_at DESC
        ";
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        return $this->db->query($sql, [$userId]);
    }
    
    /**
     * Get orders by user ID (alias for getByUser)
     */
    public function getByUserId($userId, $limit = null) {
        return $this->getByUser($userId, $limit);
    }
    
    /**
     * Get orders by status
     */
    public function getByStatus($status, $limit = null) {
        $query = $this->where('status', $status)->orderBy('created_at', 'DESC');
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->get();
    }
    
    /**
     * Update order status
     */
    public function updateStatus($orderId, $status, $notes = null) {
        $updateData = ['status' => $status];
        
        // Add timestamp for specific statuses
        switch ($status) {
            case 'shipped':
                $updateData['shipped_at'] = date('Y-m-d H:i:s');
                break;
            case 'delivered':
                $updateData['delivered_at'] = date('Y-m-d H:i:s');
                break;
        }
        
        if ($notes) {
            $updateData['admin_notes'] = $notes;
        }
        
        return $this->update($orderId, $updateData);
    }
    
    /**
     * Update payment status
     */
    public function updatePaymentStatus($orderId, $paymentStatus, $paymentReference = null) {
        $updateData = ['payment_status' => $paymentStatus];
        
        if ($paymentReference) {
            $updateData['payment_reference'] = $paymentReference;
        }
        
        return $this->update($orderId, $updateData);
    }
    
    /**
     * Check if user has purchased a product (has completed order for this product)
     */
    public function hasUserPurchasedProduct($userId, $productId) {
        $sql = "
            SELECT COUNT(*) as count
            FROM {$this->table} o
            INNER JOIN order_items oi ON o.id = oi.order_id
            WHERE o.user_id = ? 
              AND oi.product_id = ? 
              AND o.status = 'completed'
        ";
        
        $result = $this->db->query($sql, [$userId, $productId]);
        
        return !empty($result) && $result[0]['count'] > 0;
    }
    
    /**
     * Get purchased products by user
     */
    public function getPurchasedProducts($userId) {
        $sql = "
            SELECT DISTINCT oi.product_id, oi.expiry_date, oi.used_quota, 
                   p.name, p.image, p.type, p.quota, p.quota_per_usage
            FROM {$this->table} o
            INNER JOIN order_items oi ON o.id = oi.order_id
            LEFT JOIN products p ON oi.product_id = p.id
            WHERE o.user_id = ? 
              AND o.status = 'completed'
        ";
        
        return $this->db->query($sql, [$userId]);
    }
    
    /**
     * Get quota info for a purchased product (total and used)
     */
    public function getProductQuotaInfo($userId, $productId) {
        $sql = "
            SELECT 
                SUM(p.quota) as total_quota,
                SUM(COALESCE(oi.used_quota, 0)) as used_quota
            FROM {$this->table} o
            INNER JOIN order_items oi ON o.id = oi.order_id
            LEFT JOIN products p ON oi.product_id = p.id
            WHERE o.user_id = ? 
              AND oi.product_id = ?
              AND o.status = 'completed'
              AND (oi.expiry_date IS NULL OR oi.expiry_date > NOW())
        ";
        
        $result = $this->db->query($sql, [$userId, $productId]);
        if (!empty($result)) {
            return [
                'total' => (int) ($result[0]['total_quota'] ?? 0),
                'used' => (int) ($result[0]['used_quota'] ?? 0),
                'remaining' => (int) (($result[0]['total_quota'] ?? 0) - ($result[0]['used_quota'] ?? 0))
            ];
        }
        return ['total' => 0, 'used' => 0, 'remaining' => 0];
    }
    
    /**
     * Get expiry date for a specific purchased product
     */
    public function getProductExpiryDate($userId, $productId) {
        $sql = "
            SELECT oi.expiry_date
            FROM {$this->table} o
            INNER JOIN order_items oi ON o.id = oi.order_id
            WHERE o.user_id = ? 
              AND oi.product_id = ?
              AND o.status = 'completed'
              AND (oi.expiry_date IS NULL OR oi.expiry_date > NOW())
            ORDER BY oi.expiry_date DESC
            LIMIT 1
        ";
        
        $result = $this->db->query($sql, [$userId, $productId]);
        return !empty($result) ? $result[0]['expiry_date'] : null;
    }
    
    /**
     * Get order statistics
     */
    public function getStats($dateRange = null) {
        $stats = [];
        $whereClause = '';
        $bindings = [];
        
        if ($dateRange) {
            $whereClause = "WHERE created_at >= ? AND created_at <= ?";
            $bindings = [$dateRange['start'], $dateRange['end']];
        }
        
        // Total orders
        $totalSql = "SELECT COUNT(*) as count FROM {$this->table} {$whereClause}";
        $total = $this->db->query($totalSql, $bindings);
        $stats['total_orders'] = $total[0]['count'] ?? 0;
        
        // Total revenue
        $revenueSql = "SELECT SUM(total) as revenue FROM {$this->table} {$whereClause}";
        $revenue = $this->db->query($revenueSql, $bindings);
        $stats['total_revenue'] = $revenue[0]['revenue'] ?? 0;
        
        // Orders by status
        $statuses = ['pending', 'processing', 'shipped', 'delivered', 'completed', 'cancelled', 'refunded'];
        foreach ($statuses as $status) {
            $statusSql = "SELECT COUNT(*) as count FROM {$this->table} WHERE status = ?";
            $statusBindings = [$status];
            
            if ($dateRange) {
                $statusSql .= " AND created_at >= ? AND created_at <= ?";
                $statusBindings = array_merge($statusBindings, $bindings);
            }
            
            $count = $this->db->query($statusSql, $statusBindings);
            $stats['by_status'][$status] = $count[0]['count'] ?? 0;
        }
        
        // Average order value
        if ($stats['total_orders'] > 0) {
            $stats['average_order_value'] = $stats['total_revenue'] / $stats['total_orders'];
        } else {
            $stats['average_order_value'] = 0;
        }
        
        return $stats;
    }
    
    /**
     * Get recent orders
     */
    public function getRecent($limit = 10) {
        $sql = "
            SELECT o.*, u.name as user_name, COUNT(oi.id) as items_count
            FROM {$this->table} o
            LEFT JOIN users u ON o.user_id = u.id
            LEFT JOIN order_items oi ON o.id = oi.order_id
            GROUP BY o.id
            ORDER BY o.created_at DESC
            LIMIT {$limit}
        ";
        
        return $this->db->query($sql);
    }
    
    /**
     * Search orders
     */
    public function searchOrders($query) {
        $sql = "
            SELECT o.*, u.name as user_name
            FROM {$this->table} o
            LEFT JOIN users u ON o.user_id = u.id
            WHERE o.order_number LIKE ? 
               OR u.name LIKE ? 
               OR u.email LIKE ?
               OR o.shipping_name LIKE ?
            ORDER BY o.created_at DESC
            LIMIT 50
        ";
        
        $searchTerm = "%{$query}%";
        return $this->db->query($sql, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    }
    
    /**
     * Generate unique order number
     */
    private function generateOrderNumber() {
        $prefix = 'ORD';
        $date = date('Ymd');
        
        // Get last order number for today
        $sql = "SELECT order_number FROM {$this->table} 
                WHERE order_number LIKE ? 
                ORDER BY order_number DESC LIMIT 1";
        
        $result = $this->db->query($sql, ["{$prefix}{$date}%"]);
        
        if (empty($result)) {
            $sequence = 1;
        } else {
            $lastNumber = $result[0]['order_number'];
            $sequence = intval(substr($lastNumber, -4)) + 1;
        }
        
        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Get monthly revenue data
     */
    public function getMonthlyRevenue($year = null) {
        $year = $year ?: date('Y');
        
        $sql = "
            SELECT 
                MONTH(created_at) as month,
                SUM(total) as revenue,
                COUNT(*) as orders_count
            FROM {$this->table}
            WHERE YEAR(created_at) = ? AND status IN ('completed', 'delivered')
            GROUP BY MONTH(created_at)
            ORDER BY month
        ";
        
        return $this->db->query($sql, [$year]);
    }
    
    // ==================== COMMISSION TRACKING METHODS ====================
    
    /**
     * Record commission for completed order
     */
    public function recordCommission($orderId, $affiliateId, $commissionAmount) {
        if ($commissionAmount <= 0) {
            throw new Exception('Commission amount must be positive');
        }
        
        return $this->update($orderId, [
            'affiliate_id' => $affiliateId,
            'commission_amount' => $commissionAmount
        ]);
    }
    
    /**
     * Get orders by affiliate
     */
    public function getByAffiliate($affiliateId, $limit = null, $status = null) {
        $query = $this->where('affiliate_id', $affiliateId);
        
        if ($status) {
            $query->where('status', $status);
        }
        
        $query->orderBy('created_at', 'DESC');
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->get();
    }
    
    /**
     * Get affiliate commission summary
     */
    public function getAffiliateCommissionSummary($affiliateId, $dateRange = null) {
        $whereClause = "WHERE affiliate_id = ?";
        $bindings = [$affiliateId];
        
        if ($dateRange) {
            $whereClause .= " AND created_at >= ? AND created_at <= ?";
            $bindings[] = $dateRange['start'];
            $bindings[] = $dateRange['end'];
        }
        
        $sql = "
            SELECT 
                COUNT(*) as total_orders,
                SUM(total) as total_sales,
                SUM(commission_amount) as total_commission,
                AVG(commission_amount) as avg_commission,
                SUM(CASE WHEN status = 'completed' THEN commission_amount ELSE 0 END) as completed_commission,
                SUM(CASE WHEN status = 'pending' THEN commission_amount ELSE 0 END) as pending_commission
            FROM {$this->table}
            {$whereClause}
        ";
        
        $result = $this->db->query($sql, $bindings);
        return $result ? $result[0] : [];
    }
    
    /**
     * Get orders with commission for affiliate
     */
    public function getAffiliateOrdersWithCommission($affiliateId, $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "
            SELECT o.*, u.name as customer_name, u.email as customer_email
            FROM {$this->table} o
            LEFT JOIN users u ON o.user_id = u.id
            WHERE o.affiliate_id = ? AND o.commission_amount > 0
            ORDER BY o.created_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ";
        
        $orders = $this->db->query($sql, [$affiliateId]);
        
        // Get total count
        $countSql = "SELECT COUNT(*) as count FROM {$this->table} 
                     WHERE affiliate_id = ? AND commission_amount > 0";
        $countResult = $this->db->query($countSql, [$affiliateId]);
        $total = $countResult[0]['count'] ?? 0;
        
        return [
            'data' => $orders,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => ceil($total / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total)
        ];
    }
    
    /**
     * Get total commission statistics
     */
    public function getCommissionStats($dateRange = null) {
        $whereClause = "WHERE commission_amount > 0";
        $bindings = [];
        
        if ($dateRange) {
            $whereClause .= " AND created_at >= ? AND created_at <= ?";
            $bindings = [$dateRange['start'], $dateRange['end']];
        }
        
        $sql = "
            SELECT 
                COUNT(*) as total_orders_with_commission,
                COUNT(DISTINCT affiliate_id) as active_affiliates,
                SUM(commission_amount) as total_commission_paid,
                AVG(commission_amount) as avg_commission_per_order,
                SUM(total) as total_sales_with_affiliate
            FROM {$this->table}
            {$whereClause}
        ";
        
        $result = $this->db->query($sql, $bindings);
        return $result ? $result[0] : [];
    }
}