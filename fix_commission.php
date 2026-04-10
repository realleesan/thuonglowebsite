<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Fix Commission</h1>";

try {
    $config = require __DIR__ . '/config.php';
    $pdo = new PDO(
        'mysql:host=' . $config['database']['host'] . ';dbname=' . $config['database']['name'],
        $config['database']['username'],
        $config['database']['password']
    );
    
    $orderId = 65;
    $commissionRate = 0.2;
    
    // Get order
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        echo "Order not found";
        exit;
    }
    
    $commission = $order['total'] * $commissionRate;
    
    echo "Order: " . $order['order_number'] . "<br>";
    echo "Total: " . number_format($order['total']) . " VND<br>";
    echo "Commission: " . number_format($commission) . " VND<br>";
    echo "Affiliate ID: " . $order['affiliate_id'] . "<br><br>";
    
    // Update order commission
    $stmt = $pdo->prepare("UPDATE orders SET commission_amount = ? WHERE id = ?");
    $stmt->execute([$commission, $orderId]);
    
    // Update affiliate
    $stmt = $pdo->prepare("UPDATE affiliates SET total_sales = total_sales + ?, total_commission = total_commission + ?, balance = balance + ? WHERE id = ?");
    $stmt->execute([$order['total'], $commission, $commission, $order['affiliate_id']]);
    
    echo "<strong style='color: green'>DONE!</strong><br>";
    echo "Commission processed successfully!<br>";
    echo "<a href='?page=affiliate'>Check Affiliate Page</a>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
