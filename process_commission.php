<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Xá» l lÃ½ Hoa Há»ng</h1>";

try {
    require_once __DIR__ . '/core/view_init.php';
    
    // Load CommissionService
    require_once __DIR__ . '/app/services/CommissionService.php';
    require_once __DIR__ . '/app/services/ErrorHandler.php';
    $errorHandler = new ErrorHandler();
    $commissionService = new CommissionService($errorHandler);
    
    // Process order 65
    $orderId = 65;
    echo "<p>Äang xá» l lÃ½ hoa há»ng cho Order ID: $orderId...</p>";
    
    $result = $commissionService->processOrderCommission($orderId);
    
    echo "<h3>Káº¿t quáº£:</h3>";
    if ($result['success']) {
        echo "<p style='color: green;'>â <strong>ThÃ nh cÃ´ng!</strong></p>";
        echo "<p>Message: " . $result['message'] . "</p>";
        
        if (isset($result['commission'])) {
            echo "<p>Hoa há»ng: " . number_format($result['commission']) . "Ä'âº¡</p>";
        }
        
        if (isset($result['affiliate'])) {
            echo "<p>Affiliate: " . $result['affiliate']['name'] . "</p>";
        }
    } else {
        echo "<p style='color: red;'>â <strong>Tháº¥t báº¡i!</strong></p>";
        echo "<p>Error: " . $result['message'] . "</p>";
    }
    
    // Kiá»m tra láº¡i Ä'Æ¡n hÃ ng
    echo "<h3>Kiá»m tra láº¡i Ä'Æ¡n hÃ ng:</h3>";
    $ordersModel = $adminService->getModel('OrdersModel');
    $order = $ordersModel->find($orderId);
    
    if ($order) {
        echo "<p>";
        echo "<strong>Commission Amount:</strong> " . number_format($order['commission_amount']) . "Ä'âº¡<br>";
        echo "<strong>Affiliate ID:</strong> " . ($order['affiliate_id'] ?? 'NULL') . "<br>";
        echo "<strong>Payment Status:</strong> " . $order['payment_status'];
        echo "</p>";
    }
    
    // Kiá»m tra láº¡i affiliate
    echo "<h3>Kiá»m tra láº¡i Admin Affiliate:</h3>";
    $affiliateModel = $adminService->getModel('AffiliateModel');
    $affiliate = $affiliateModel->find(6); // Admin affiliate ID = 6
    
    if ($affiliate) {
        echo "<p>";
        echo "<strong>Total Sales:</strong> " . number_format($affiliate['total_sales']) . "Ä'âº¡<br>";
        echo "<strong>Total Commission:</strong> " . number_format($affiliate['total_commission']) . "Ä'âº¡<br>";
        echo "<strong>Balance:</strong> " . number_format($affiliate['balance']) . "Ä'âº¡";
        echo "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . " Line: " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
