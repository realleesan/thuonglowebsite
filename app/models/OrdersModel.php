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
    
    /**
     * Create new order with items
     */
    public function createOrder($orderData, $items) {
        $this->beginTransaction();
        
        try {
            // Generate order number
            $orderData['order_number'] = $this->generateOrderNumber();
            
            // Calculate totals
            $subtotal = 0;
            foreach ($items as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }
            
            $orderData['subtotal'] = $subtotal;
            $orderData['total'] = $subtotal + ($orderData['tax_amount'] ?? 0) + 
                                 ($orderData['shipping_amount'] ?? 0) - 
                                 ($orderData['discount_amount'] ?? 0);
            
            // Create order
            $order = $this->create($orderData);
            
            if (!$order) {
                throw new Exception('Failed to create order');
            }
            
            // Create order items
            foreach ($items as $item) {
                $itemData = [
                    'order_id' => $order['id'],
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'product_sku' => $item['product_sku'] ?? null,
                    'product_type' => $item['product_type'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['price'] * $item['quantity'],
                    'product_data' => json_encode($item['product_data'] ?? [])
                ];
                
                $this->db->table('order_items')->insert($itemData);
                
                // Update product sales count and stock
                if (isset($item['product_id'])) {
                    $this->db->execute(
                        "UPDATE products SET sales_count = sales_count + ?, stock = stock - ? WHERE id = ?",
                        [$item['quantity'], $item['quantity'], $item['product_id']]
                    );
                }
            }
            
            $this->commit();
            return $this->getOrderWithItems($order['id']);
            
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
            SELECT o.*, COUNT(oi.id) as items_count
            FROM {$this->table} o
            LEFT JOIN order_items oi ON o.id = oi.order_id
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
}