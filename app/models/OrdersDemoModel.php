<?php
/**
 * Orders Demo Model
 * Model cho đơn hàng demo
 */

require_once __DIR__ . '/BaseModel.php';

class OrdersDemoModel extends BaseModel {
    protected $table = 'orders_demo';
    protected $fillable = [
        'order_number', 'user_id', 'status', 'payment_status', 'payment_method',
        'payment_reference', 'subtotal', 'total', 'customer_name', 
        'customer_email', 'customer_phone', 'notes'
    ];
    
    /**
     * Create new demo order with items
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
            $orderData['total'] = $subtotal;
            
            // Create order
            $orderId = $this->create($orderData);
            
            if (!$orderId) {
                throw new Exception('Failed to create demo order');
            }
            
            // Create order items
            foreach ($items as $item) {
                $itemData = [
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'product_sku' => $item['product_sku'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['price'] * $item['quantity']
                ];
                
                $this->db->table('order_items_demo')->insert($itemData);
            }
            
            $this->commit();
            return $this->getOrderWithItems($orderId);
            
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
    
    /**
     * Get order with items
     */
    public function getOrderWithItems($orderId) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $order = $this->db->query($sql, [$orderId]);
        
        if (empty($order)) {
            return null;
        }
        
        $order = $order[0];
        
        // Get order items
        $itemsSql = "SELECT * FROM order_items_demo WHERE order_id = ?";
        $order['items'] = $this->db->query($itemsSql, [$orderId]);
        
        return $order;
    }
    
    /**
     * Get order by order number
     */
    public function getByOrderNumber($orderNumber) {
        $sql = "SELECT * FROM {$this->table} WHERE order_number = ?";
        $result = $this->db->query($sql, [$orderNumber]);
        return $result ? $result[0] : null;
    }
    
    /**
     * Update payment status
     */
    public function updatePaymentStatus($orderId, $paymentStatus, $paymentReference = null) {
        $updateData = ['payment_status' => $paymentStatus];
        
        if ($paymentReference) {
            $updateData['payment_reference'] = $paymentReference;
        }
        
        if ($paymentStatus === 'paid') {
            $updateData['status'] = 'completed';
        }
        
        return $this->update($orderId, $updateData);
    }
    
    /**
     * Generate unique order number
     */
    private function generateOrderNumber() {
        $prefix = 'DEMO';
        $date = date('Ymd');
        
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
}
