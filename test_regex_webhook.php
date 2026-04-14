<?php
/**
 * Test regex extraction for SePay webhook formats
 */

require_once __DIR__ . '/core/database.php';
require_once __DIR__ . '/app/models/OrdersModel.php';

$db = Database::getInstance();
$ordersModel = new OrdersModel($db);

echo "=== TEST EXTRACT REFERENCE FROM CONTENT ===\n\n";

$testCases = [
    'THANHTOAN ORD_cb4fc702-CHUYEN TIEN-1776144449' => 'ORD_cb4fc702',  // Test format (with _)
    'THANHTOAN ORD473b21e2- Ma GD ACSP/yi677807' => 'ORD473b21e2',        // SePay real (no _)
    'THANHTOAN ORDca94fac2- Ma GD ACSP/bq704752' => 'ORDca94fac2',        // SePay real (no _)
];

function extractReference(string $content): ?string {
    // Try ORD_{code} with underscore
    if (preg_match('/(ORD_[a-zA-Z0-9_]+)/', $content, $matches)) {
        return $matches[1];
    }
    // Try ORD{code} without underscore
    if (preg_match('/(ORD[a-zA-Z0-9]+)/', $content, $matches)) {
        return $matches[1];
    }
    return $content;
}

foreach ($testCases as $input => $expected) {
    $result = extractReference($input);
    $status = ($result === $expected) ? '✅' : '❌';
    echo "{$status} Input: {$input}\n";
    echo "   Expected: {$expected}\n";
    echo "   Got: {$result}\n\n";
}

echo "=== TEST FIND ORDER BY REFERENCE ===\n\n";

$refs = ['ORD_cb4fc702', 'ORD473b21e2', 'ORD7a22f4ab', 'ORDca94fac2'];

foreach ($refs as $ref) {
    echo "Reference: {$ref}\n";
    
    // Try with underscore
    if (strpos($ref, 'ORD_') === 0) {
        $order = $ordersModel->findByOrderNumber($ref);
        echo "  Try '{$ref}': " . ($order ? "Found ID {$order['id']}" : "Not found") . "\n";
    } 
    // Try without underscore -> add underscore
    elseif (strpos($ref, 'ORD') === 0) {
        $withUnderscore = 'ORD_' . substr($ref, 3);
        $order = $ordersModel->findByOrderNumber($withUnderscore);
        echo "  Try '{$withUnderscore}': " . ($order ? "Found ID {$order['id']}" : "Not found") . "\n";
        
        // Also try without underscore directly
        $order2 = $ordersModel->findByOrderNumber($ref);
        echo "  Try '{$ref}' directly: " . ($order2 ? "Found ID {$order2['id']}" : "Not found") . "\n";
    }
    echo "\n";
}

echo "=== RECENT ORDERS IN DB ===\n\n";
$orders = $ordersModel->getRecent(5);
foreach ($orders as $o) {
    echo "ID: {$o['id']}, Order Number: {$o['order_number']}, Status: {$o['payment_status']}\n";
}
