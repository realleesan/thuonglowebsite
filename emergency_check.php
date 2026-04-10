<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>EMERGENCY CHECK</h1>";

try {
    $config = require __DIR__ . '/config.php';
    $pdo = new PDO(
        'mysql:host=' . $config['database']['host'] . ';dbname=' . $config['database']['name'],
        $config['database']['username'],
        $config['database']['password']
    );
    
    // Check affiliate 6 (admin)
    $stmt = $pdo->prepare("SELECT * FROM affiliates WHERE id = 6");
    $stmt->execute();
    $affiliate = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h3>Affiliate 6 (Admin):</h3>";
    if ($affiliate) {
        echo "<p>Total Sales: " . number_format($affiliate['total_sales']) . " VND</p>";
        echo "<p>Total Commission: " . number_format($affiliate['total_commission']) . " VND</p>";
        echo "<p>Balance: " . number_format($affiliate['balance']) . " VND</p>";
    }
    
    // Check all orders of user 88
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = 88 ORDER BY created_at DESC");
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>All Orders User 88:</h3>";
    $totalCommission = 0;
    foreach ($orders as $order) {
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 5px 0;'>";
        echo "<p>Order {$order['id']} ({$order['order_number']})</p>";
        echo "<p>Total: " . number_format($order['total']) . " VND</p>";
        echo "<p>Commission: " . number_format($order['commission_amount']) . " VND</p>";
        echo "<p>Payment: {$order['payment_status']}</p>";
        echo "<p>Affiliate ID: " . ($order['affiliate_id'] ?? 'NULL') . "</p>";
        $totalCommission += $order['commission_amount'];
        echo "</div>";
    }
    
    echo "<h3>Summary:</h3>";
    echo "<p>Total Orders: " . count($orders) . "</p>";
    echo "<p>Total Commission in Orders: " . number_format($totalCommission) . " VND</p>";
    echo "<p>Affiliate Total Commission: " . number_format($affiliate['total_commission'] ?? 0) . " VND</p>";
    
    // Check if mismatch
    if ($totalCommission != ($affiliate['total_commission'] ?? 0)) {
        echo "<p style='color: red; font-weight: bold;'>MISMATCH DETECTED!</p>";
        echo "<p>Need to fix affiliate commission to: " . number_format($totalCommission) . " VND</p>";
        
        // Fix it
        $stmt = $pdo->prepare("UPDATE affiliates SET total_commission = ?, balance = ? WHERE id = 6");
        $stmt->execute([$totalCommission, $totalCommission]);
        echo "<p style='color: green;'>FIXED! Affiliate updated.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
