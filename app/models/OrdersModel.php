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
                // Calculate expiry date
                $expiryDate = null;
                
                if ($isRenewal && $userId && isset($item['product_id'])) {
                    // For renewal, extend the existing expiry date
                    $existingExpiry = $this->getProductExpiryDate($userId, $item['product_id']);
                    if ($existingExpiry) {
                        // Add 30 days to existing expiry
                        $expiryDate = date('Y-m-d H:i:s', strtotime($existingExpiry . ' +' . self::DEFAULT_EXPIRY_DAYS . ' days'));
                    } else {
                        // No existing expiry, start from now + 30 days
                        $expiryDate = date('Y-m-d H:i:s', strtotime('+' . self::DEFAULT_EXPIRY_DAYS . ' days'));
                    }
                } else {
                    // New purchase: expiry = now + 30 days
                    $expiryDate = date('Y-m-d H:i:s', strtotime('+' . self::DEFAULT_EXPIRY_DAYS . ' days'));
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
            SELECT DISTINCT oi.product_id, oi.expiry_date, p.name, p.image, p.type
            FROM {$this->table} o
            INNER JOIN order_items oi ON o.id = oi.order_id
            LEFT JOIN products p ON oi.product_id = p.id
            WHERE o.user_id = ? 
              AND o.status = 'completed'
        ";
        
        return $this->db->query($sql, [$userId]);
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
            ORDER BY o.created_at DESC
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