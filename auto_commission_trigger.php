<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>TRIGGER HOA HONG TU DONG</h1>";

try {
    $config = require __DIR__ . '/config.php';
    $pdo = new PDO(
        'mysql:host=' . $config['database']['host'] . ';dbname=' . $config['database']['name'],
        $config['database']['username'],
        $config['database']['password']
    );
    
    // Find all paid orders without commission
    $stmt = $pdo->query("
        SELECT o.* 
        FROM orders o 
        WHERE o.payment_status = 'paid' 
        AND o.commission_amount = 0 
        AND o.affiliate_id IS NOT NULL
        AND o.affiliate_id > 0
        ORDER BY o.created_at DESC
    ");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Found " . count($orders) . " orders need commission:</h3>";
    
    foreach ($orders as $order) {
        $commission = $order['total'] * 0.2;
        
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
        echo "<p><strong>Order:</strong> {$order['order_number']}</p>";
        echo "<p><strong>Total:</strong> " . number_format($order['total']) . " VND</p>";
        echo "<p><strong>Commission:</strong> " . number_format($commission) . " VND</p>";
        
        // Update order
        $stmt = $pdo->prepare("UPDATE orders SET commission_amount = ? WHERE id = ?");
        $stmt->execute([$commission, $order['id']]);
        
        // Update affiliate
        $stmt = $pdo->prepare("UPDATE affiliates SET total_sales = total_sales + ?, total_commission = total_commission + ?, balance = balance + ? WHERE id = ?");
        $stmt->execute([$order['total'], $commission, $commission, $order['affiliate_id']]);
        
        echo "<p style='color: green'>â DONE!</p>";
        echo "</div>";
    }
    
    echo "<h2 style='color: green; font-size: 24px;'>â â â TÄT Äá»NG! Há» thá»ng ÄÃ£ Ä'Æ°á»£c sá»a!</h2>";
    echo "<p><a href='?page=affiliate'>Xem trang Affiliate</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
