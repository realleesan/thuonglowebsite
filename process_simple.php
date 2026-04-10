<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Xá» l lÃ½ Hoa Há»ng - Simple</h1>";

try {
    // Káº¿t ná»'i database trá»±c tiáº¿p
    $config = require __DIR__ . '/config.php';
    $pdo = new PDO(
        'mysql:host=' . $config['database']['host'] . ';dbname=' . $config['database']['name'],
        $config['database']['username'],
        $config['database']['password']
    );
    
    $orderId = 65;
    $commissionRate = 0.2; // 20%
    
    echo "<p>Äang xá» l lÃ½ Order ID: $orderId...</p>";
    
    // Láº¥y thông tin Ä'Æ¡n hÃ ng
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        throw new Exception("Order $orderId khÃ´ng tá»"n táº¡i");
    }
    
    echo "<p>Order found: " . $order['order_number'] . "</p>";
    echo "<p>Total: " . number_format($order['total']) . "Ä'âº¡</p>";
    echo "<p>Affiliate ID: " . ($order['affiliate_id'] ?? 'NULL') . "</p>";
    
    if (empty($order['affiliate_id'])) {
        throw new Exception("Order khÃ´ng cÃ³ affiliate_id");
    }
    
    if ($order['commission_amount'] > 0) {
        echo "<p style='color: orange;'>â Order ÄÃ£ cÃ³ hoa há»ng: " . number_format($order['commission_amount']) . "Ä'âº¡</p>";
        exit;
    }
    
    // TÃnh hoa há»ng
    $commission = $order['total'] * $commissionRate;
    
    echo "<p>Hoa há»ng cáº§n thanh toÃ¡n: " . number_format($commission) . "Ä'âº¡</p>";
    
    // Cáºp nháºt hoa há»ng vÃ o Ä'Æ¡n hÃ ng
    $stmt = $pdo->prepare("UPDATE orders SET commission_amount = ? WHERE id = ?");
    $stmt->execute([$commission, $orderId]);
    
    // Cáºp nháº¥t affiliate
    $stmt = $pdo->prepare("UPDATE affiliates SET total_sales = total_sales + ?, total_commission = total_commission + ?, balance = balance + ? WHERE id = ?");
    $stmt->execute([$order['total'], $commission, $commission, $order['affiliate_id']]);
    
    // Láº¥y láº¡i thÃ´ng tin affiliate
    $stmt = $pdo->prepare("SELECT * FROM affiliates WHERE id = ?");
    $stmt->execute([$order['affiliate_id']]);
    $affiliate = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h3>â <strong>ThÃ nh cÃ´ng!</strong></h3>";
    echo "<p>Hoa há»ng " . number_format($commission) . "Ä'âº¡ ÄÃ£ Ä'Æ°á»£c xá» l lÃ½</p>";
    
    echo "<h3>ThÃ´ng tin Affiliate:</h3>";
    echo "<p>";
    echo "<strong>Total Sales:</strong> " . number_format($affiliate['total_sales']) . "Ä'âº¡<br>";
    echo "<strong>Total Commission:</strong> " . number_format($affiliate['total_commission']) . "Ä'âº¡<br>";
    echo "<strong>Balance:</strong> " . number_format($affiliate['balance']) . "Ä'âº¡";
    echo "</p>";
    
    echo "<p><a href='?page=affiliate'>Xem trang Affiliate</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . " Line: " . $e->getLine() . "</p>";
}
?>
