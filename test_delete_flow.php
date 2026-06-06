<?php
define('THUONGLO_INIT', true);
require_once 'config.php';
require_once 'core/database.php';
require_once 'app/services/AdminService.php';

$adminService = new AdminService(null, 'admin');

// Let's find some product IDs with orders
$db = Database::getInstance();
$items = $db->query("SELECT DISTINCT product_id FROM order_items LIMIT 5");
echo "Products with orders:\n";
print_r($items);

foreach ($items as $item) {
    $pid = $item['product_id'];
    if (empty($pid)) continue;
    $check = $adminService->checkProductHasOrders($pid);
    echo "Product ID $pid has orders: " . ($check['has_orders'] ? 'YES' : 'NO') . "\n";
}
