<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>TEST WEBHOOK AUTO</h1>";

try {
    // Simulate webhook data for order 67
    $webhookData = [
        'transferType' => 'in',
        'referenceCode' => 'ORD_240294ca',
        'amount' => 170000,
        'status' => 'SUCCESS',
        'transactionId' => 'TEST_' . time()
    ];
    
    echo "<p>Simulating webhook for Order 67...</p>";
    echo "<pre>" . print_r($webhookData, true) . "</pre>";
    
    // Load webhook controller
    require_once __DIR__ . '/app/controllers/WebhookController.php';
    
    $controller = new WebhookController();
    
    // Call webhook method
    $result = $controller->handleSepayWebhook($webhookData);
    
    echo "<h3>Result:</h3>";
    if ($result) {
        echo "<p style='color: green'>â Webhook processed successfully!</p>";
    } else {
        echo "<p style='color: red'>â Webhook failed!</p>";
    }
    
    // Check result
    echo "<h3>Check Order 67:</h3>";
    $config = require __DIR__ . '/config.php';
    $pdo = new PDO(
        'mysql:host=' . $config['database']['host'] . ';dbname=' . $config['database']['name'],
        $config['database']['username'],
        $config['database']['password']
    );
    
    $stmt = $pdo->prepare("SELECT commission_amount FROM orders WHERE id = 67");
    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>Commission Amount: " . number_format($order['commission_amount']) . " VND</p>";
    
    if ($order['commission_amount'] > 0) {
        echo "<p style='color: green; font-size: 20px;'>â â â TÄT Äá»NG! Há» thá»ng Tá»° Äá»NG hoa há»ng!</p>";
    } else {
        echo "<p style='color: red'>â â â Váºn n cÃ²n á» webhook!</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
