<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Kiá»m tra Ä'Æ¡n hÃ ng</h1>";

try {
    require_once __DIR__ . '/core/view_init.php';
    
    // Láº¥y OrdersModel
    global $adminService;
    $ordersModel = $adminService->getModel('OrdersModel');
    
    // Kiá»m tra Ä'Æ¡n hÃ ng gáº§n nháº¥t cá»§a user ID 88 (chim chim)
    $recentOrders = $ordersModel->query("
        SELECT o.*, u.name as customer_name, u.email as customer_email
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        WHERE o.user_id = 88
        ORDER BY o.created_at DESC
        LIMIT 5
    ");
    
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
    $affiliateModel = $adminService->getModel('AffiliateModel');
    $adminAffiliate = $affiliateModel->getByUserId(1); // Admin ID = 1
    
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
    
    // Kiá»m tra user 88 cÃ³ referred_by khÃ´ng
    $usersModel = $adminService->getModel('UsersModel');
    $user88 = $usersModel->find(88);
    
    echo "<h3>Info User 88:</h3>";
    if ($user88) {
        echo "<p><strong>Name:</strong> " . $user88['name'] . "</p>";
        echo "<p><strong>Referred By:</strong> " . ($user88['referred_by'] ?? 'NULL') . "</p>";
        echo "<p><strong>Role:</strong> " . $user88['role'] . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
