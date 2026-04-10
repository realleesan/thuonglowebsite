<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Kiá»m tra Ä'Æ¡n hÃ ng - Simple</h1>";

try {
    // Káº¿t ná»'i database trá»±c tiáº¿p
    $config = require __DIR__ . '/config.php';
    $pdo = new PDO(
        'mysql:host=' . $config['database']['host'] . ';dbname=' . $config['database']['name'],
        $config['database']['username'],
        $config['database']['password']
    );
    
    // Kiá»m tra Ä'Æ¡n hÃ ng cá»§a user 88
    $stmt = $pdo->query("
        SELECT o.*, u.name as customer_name, u.email as customer_email
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        WHERE o.user_id = 88
        ORDER BY o.created_at DESC
        LIMIT 5
    ");
    $recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Ä'Æ¡n hÃ ng cá»§a user 88:</h3>";
    if ($recentOrders) {
        foreach ($recentOrders as $order) {
            echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
            echo "<p><strong>Order ID:</strong> " . $order['id'] . "</p>";
            echo "<p><strong>Order Number:</strong> " . $order['order_number'] . "</p>";
            echo "<p><strong>Total:</strong> " . number_format($order['total']) . "Ä'âº¡</p>";
            echo "<p><strong>Payment Status:</strong> " . $order['payment_status'] . "</p>";
            echo "<p><strong>Affiliate ID:</strong> " . ($order['affiliate_id'] ?? 'NULL') . "</p>";
            echo "<p><strong>Commission Amount:</strong> " . number_format($order['commission_amount']) . "Ä'âº¡</p>";
            echo "<p><strong>Created:</strong> " . $order['created_at'] . "</p>";
            echo "</div>";
        }
    } else {
        echo "<p>KhÃ´ng cÃ³ Ä'Æ¡n hÃ ng nÃ o</p>";
    }
    
    // Kiá»m tra affiliate cá»§a admin
    $stmt = $pdo->query("SELECT * FROM affiliates WHERE user_id = 1");
    $adminAffiliate = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h3>Info Admin Affiliate:</h3>";
    if ($adminAffiliate) {
        echo "<p><strong>Affiliate ID:</strong> " . $adminAffiliate['id'] . "</p>";
        echo "<p><strong>User ID:</strong> " . $adminAffiliate['user_id'] . "</p>";
        echo "<p><strong>Referral Code:</strong> " . $adminAffiliate['referral_code'] . "</p>";
        echo "<p><strong>Total Sales:</strong> " . number_format($adminAffiliate['total_sales']) . "Ä'âº¡</p>";
        echo "<p><strong>Total Commission:</strong> " . number_format($adminAffiliate['total_commission']) . "Ä'âº¡</p>";
    } else {
        echo "<p>Admin khÃ´ng cÃ³ affiliate</p>";
    }
    
    // Kiá»m tra user 88
    $stmt = $pdo->query("SELECT * FROM users WHERE id = 88");
    $user88 = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h3>Info User 88:</h3>";
    if ($user88) {
        echo "<p><strong>Name:</strong> " . $user88['name'] . "</p>";
        echo "<p><strong>Referred By:</strong> " . ($user88['referred_by'] ?? 'NULL') . "</p>";
        echo "<p><strong>Role:</strong> " . $user88['role'] . "</p>";
    }
    
    // Kiá»m tra foreign key constraint
    $stmt = $pdo->query("
        SELECT CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'orders' 
        AND COLUMN_NAME = 'affiliate_id'
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    $constraints = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Foreign Key Constraints:</h3>";
    if ($constraints) {
        foreach ($constraints as $constraint) {
            echo "<p>";
            echo "<strong>Constraint:</strong> " . $constraint['CONSTRAINT_NAME'] . "<br>";
            echo "<strong>Table:</strong> " . $constraint['TABLE_NAME'] . "." . $constraint['COLUMN_NAME'] . "<br>";
            echo "<strong>References:</strong> " . $constraint['REFERENCED_TABLE_NAME'] . "." . $constraint['REFERENCED_COLUMN_NAME'];
            echo "</p>";
        }
    } else {
        echo "<p>KhÃ´ng cÃ³ foreign key constraint cho affiliate_id</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . " Line: " . $e->getLine() . "</p>";
}
?>
