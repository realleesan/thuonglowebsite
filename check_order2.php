<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Kiem tra don hang thu 2</h1>";

try {
    $config = require __DIR__ . '/config.php';
    $pdo = new PDO(
        'mysql:host=' . $config['database']['host'] . ';dbname=' . $config['database']['name'],
        $config['database']['username'],
        $config['database']['password']
    );
    
    // Lay don hang cua user 88, sap xep theo ngay tao
    $stmt = $pdo->query("
        SELECT o.*, u.name as customer_name, u.email as customer_email
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        WHERE o.user_id = 88
        ORDER BY o.created_at DESC
        LIMIT 5
    ");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Tat ca don hang cua user 88:</h3>";
    foreach ($orders as $order) {
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
        echo "<p><strong>Order ID:</strong> " . $order['id'] . "</p>";
        echo "<p><strong>Order Number:</strong> " . $order['order_number'] . "</p>";
        echo "<p><strong>Total:</strong> " . number_format($order['total']) . " VND</p>";
        echo "<p><strong>Payment Status:</strong> " . $order['payment_status'] . "</p>";
        echo "<p><strong>Affiliate ID:</strong> " . ($order['affiliate_id'] ?? 'NULL') . "</p>";
        echo "<p><strong>Commission Amount:</strong> " . number_format($order['commission_amount']) . " VND</p>";
        echo "<p><strong>Created:</strong> " . $order['created_at'] . "</p>";
        
        // Neu don hang chua co commission, tinh hoa hong
        if ($order['commission_amount'] == 0 && $order['payment_status'] == 'paid' && !empty($order['affiliate_id'])) {
            $commission = $order['total'] * 0.2;
            echo "<p style='color: red;'><strong>CHUA CO HOA HONG - Nen co: " . number_format($commission) . " VND</strong></p>";
            echo "<button onclick='processCommission(" . $order['id'] . ")'>Tinh hoa hong</button>";
        }
        echo "</div>";
    }
    
    ?>
    <script>
    function processCommission(orderId) {
        if (confirm('Tinh hoa hong cho order ' + orderId + '?')) {
            window.open('process_single.php?order_id=' + orderId, '_blank');
        }
    }
    </script>
    <?php
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
