<?php
/**
 * Test Commission System
 * Script để kiểm tra hệ thống hoa hồng
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/app/models/AffiliateModel.php';
require_once __DIR__ . '/app/models/UsersModel.php';
require_once __DIR__ . '/app/models/OrdersModel.php';
require_once __DIR__ . '/app/services/CommissionService.php';

echo "<h1>Test Commission System</h1>";

try {
    // Kết nối database
    $pdo = new PDO(
        'mysql:host=' . $config['database']['host'] . ';dbname=' . $config['database']['name'],
        $config['database']['username'],
        $config['database']['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>1. Kiểm tra user 'chim chim' (ID: 88)</h2>";
    
    // Kiểm tra user 88
    $stmt = $pdo->prepare("SELECT id, name, email, referred_by FROM users WHERE id = 88");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "<p><strong>User:</strong> {$user['name']} ({$user['email']})</p>";
        echo "<p><strong>Referred by:</strong> " . ($user['referred_by'] ?? 'NULL') . "</p>";
        
        if (!empty($user['referred_by'])) {
            // Kiểm tra affiliate
            $stmt = $pdo->prepare("SELECT a.*, u.name as affiliate_name FROM affiliates a LEFT JOIN users u ON a.user_id = u.id WHERE a.user_id = ?");
            $stmt->execute([$user['referred_by']]);
            $affiliate = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($affiliate) {
                echo "<p><strong>Affiliate:</strong> {$affiliate['affiliate_name']} ({$affiliate['referral_code']})</p>";
                echo "<p><strong>Commission Rate:</strong> {$affiliate['commission_rate']}%</p>";
                echo "<p><strong>Total Sales:</strong> " . number_format($affiliate['total_sales']) . " VNĐ</p>";
                echo "<p><strong>Total Commission:</strong> " . number_format($affiliate['total_commission']) . " VNĐ</p>";
                echo "<p><strong>Balance:</strong> " . number_format($affiliate['balance']) . " VNĐ</p>";
            } else {
                echo "<p style='color: red;'>Không tìm thấy affiliate cho user_id = {$user['referred_by']}</p>";
            }
        }
    } else {
        echo "<p style='color: red;'>Không tìm thấy user ID 88</p>";
    }
    
    echo "<h2>2. Kiểm tra đơn hàng của user 88</h2>";
    
    // Kiểm tra orders của user 88
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = 88 ORDER BY created_at DESC");
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($orders) {
        foreach ($orders as $order) {
            echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
            echo "<p><strong>Order #{$order['id']}:</strong> {$order['order_number']}</p>";
            echo "<p><strong>Total:</strong> " . number_format($order['total']) . " VNĐ</p>";
            echo "<p><strong>Payment Status:</strong> {$order['payment_status']}</p>";
            echo "<p><strong>Affiliate ID:</strong> " . ($order['affiliate_id'] ?? 'NULL') . "</p>";
            echo "<p><strong>Commission Amount:</strong> " . number_format($order['commission_amount'] ?? 0) . " VNĐ</p>";
            echo "<p><strong>Created:</strong> {$order['created_at']}</p>";
            echo "</div>";
        }
    } else {
        echo "<p>Không có đơn hàng nào cho user 88</p>";
    }
    
    echo "<h2>3. Kiểm tra admin affiliate</h2>";
    
    // Kiểm tra admin affiliate
    $stmt = $pdo->prepare("SELECT a.*, u.name as affiliate_name, u.email as affiliate_email FROM affiliates a LEFT JOIN users u ON a.user_id = u.id WHERE a.referral_code = 'REF0001'");
    $stmt->execute();
    $adminAffiliate = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($adminAffiliate) {
        echo "<p><strong>Admin Affiliate:</strong> {$adminAffiliate['affiliate_name']} ({$adminAffiliate['affiliate_email']})</p>";
        echo "<p><strong>Commission Rate:</strong> {$adminAffiliate['commission_rate']}%</p>";
        echo "<p><strong>Total Sales:</strong> " . number_format($adminAffiliate['total_sales']) . " VNĐ</p>";
        echo "<p><strong>Total Commission:</strong> " . number_format($adminAffiliate['total_commission']) . " VNĐ</p>";
        echo "<p><strong>Balance:</strong> " . number_format($adminAffiliate['balance']) . " VNĐ</p>";
    } else {
        echo "<p style='color: red;'>Không tìm thấy admin affiliate với mã REF0001</p>";
    }
    
    echo "<h2>4. Test tính hoa hồng cho đơn hàng gần nhất</h2>";
    
    if (!empty($orders)) {
        $latestOrder = $orders[0];
        
        if ($latestOrder['payment_status'] === 'paid' && !empty($latestOrder['affiliate_id'])) {
            echo "<p>Đang test tính hoa hồng cho order #{$latestOrder['id']}...</p>";
            
            $commissionService = new CommissionService();
            $result = $commissionService->calculateCommission($latestOrder['id']);
            
            if ($result['success']) {
                echo "<p style='color: green;'><strong>✓ Commission calculation successful!</strong></p>";
                echo "<p><strong>Commission:</strong> " . number_format($result['commission']) . " VNĐ</p>";
                echo "<p><strong>Rate:</strong> {$result['rate']}%</p>";
                echo "<p><strong>Affiliate ID:</strong> {$result['affiliate_id']}</p>";
            } else {
                echo "<p style='color: red;'><strong>✗ Commission calculation failed:</strong> {$result['message']}</p>";
            }
        } else {
            echo "<p>Order gần nhất không phù hợp để test (payment_status: {$latestOrder['payment_status']}, affiliate_id: " . ($latestOrder['affiliate_id'] ?? 'NULL') . ")</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Lỗi:</strong> " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

?>
