<?php
/**
 * Orders Seeder
 * Seeds orders and order_items tables with data from JSON files
 */

require_once __DIR__ . '/BaseSeeder.php';

class OrdersSeeder extends BaseSeeder {
    protected $tableName = 'orders';
    
    public function run() {
        echo "🌱 Seeding orders and order_items tables...\n";
        
        // Truncate tables first
        $this->truncateTable('order_items');
        $this->truncateTable('orders');
        
        // Load fake data
        $fakeData = $this->loadJsonData(__DIR__ . '/../../app/views/admin/data/fake_data.json');
        
        $insertedOrders = 0;
        $insertedItems = 0;
        
        if (isset($fakeData['orders'])) {
            foreach ($fakeData['orders'] as $order) {
                // Generate order number
                $orderNumber = 'ORD' . str_pad($order['id'], 6, '0', STR_PAD_LEFT);
                
                // Get user info for shipping
                $user = $this->db->table('users')->find($order['user_id']);
                $userName = $user ? $user['name'] : 'Unknown User';
                $userEmail = $user ? $user['email'] : 'unknown@example.com';
                $userPhone = $user ? $user['phone'] : null;
                
                $orderData = [
                    'order_number' => $orderNumber,
                    'user_id' => $order['user_id'],
                    'status' => $order['status'],
                    'payment_status' => $this->mapPaymentStatus($order['status']),
                    'payment_method' => $order['payment_method'],
                    'subtotal' => $order['total'],
                    'total' => $order['total'],
                    'shipping_name' => $userName,
                    'shipping_email' => $userEmail,
                    'shipping_phone' => $userPhone,
                    'shipping_address' => $order['shipping_address'] ?? null,
                    'shipping_city' => 'TP.HCM',
                    'shipping_country' => 'Vietnam',
                    'created_at' => $this->formatDateTime($order['created_at'] ?? null),
                    'updated_at' => $this->formatDateTime($order['created_at'] ?? null)
                ];
                
                $orderId = $this->insertData('orders', $orderData);
                $insertedOrders++;
                echo "   ✓ Inserted order: {$orderNumber}\n";
                
                // Create order item
                $product = $this->db->table('products')->find($order['product_id']);
                if ($product) {
                    $itemData = [
                        'order_id' => $orderId,
                        'product_id' => $order['product_id'],
                        'product_name' => $product['name'],
                        'product_sku' => $product['sku'],
                        'product_type' => $product['type'],
                        'quantity' => $order['quantity'],
                        'price' => $product['price'],
                        'total' => $product['price'] * $order['quantity'],
                        'product_data' => json_encode([
                            'name' => $product['name'],
                            'price' => $product['price'],
                            'image' => $product['image']
                        ]),
                        'created_at' => $this->formatDateTime($order['created_at'] ?? null),
                        'updated_at' => $this->formatDateTime($order['created_at'] ?? null)
                    ];
                    
                    $this->insertData('order_items', $itemData);
                    $insertedItems++;
                    echo "     ✓ Added item: {$product['name']}\n";
                }
            }
        }
        
        echo "   📊 Total orders inserted: {$insertedOrders}\n";
        echo "   📊 Total order items inserted: {$insertedItems}\n\n";
    }
    
    /**
     * Map order status to payment status
     */
    private function mapPaymentStatus($orderStatus) {
        switch ($orderStatus) {
            case 'completed':
                return 'paid';
            case 'processing':
                return 'paid';
            case 'pending':
                return 'pending';
            case 'cancelled':
                return 'failed';
            default:
                return 'pending';
        }
    }
}