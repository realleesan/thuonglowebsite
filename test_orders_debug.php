<?php
/**
 * Test file for diagnosing orders module issues
 * Access this file at: test_orders_debug.php?test=view&id=57
 */

// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h1>Orders Module Debug Test</h1>";
echo "<pre>";

$test = $_GET['test'] ?? '';

// Simulate the main entry point flow
$base_dir = __DIR__;

// 1. Load config
echo "1. Loading config...\n";
$config = require_once $base_dir . '/config.php';
echo "Config loaded: " . (is_array($config) ? "OK" : "FAIL") . "\n";

// 2. Load view_init
echo "\n2. Loading view_init.php...\n";
require_once $base_dir . '/core/view_init.php';
echo "VIEW_INIT_LOADED: " . (defined('VIEW_INIT_LOADED') ? "YES" : "NO") . "\n";

// 3. Check what services are available
echo "\n3. Checking services...\n";
echo "isset(\$adminService): " . (isset($adminService) ? "YES" : "NO") . "\n";
echo "isset(\$serviceManager): " . (isset($serviceManager) ? "YES" : "NO") . "\n";

// 4. Check if we can get admin service
echo "\n4. Getting admin service...\n";
$service = null;
if (isset($adminService)) {
    $service = $adminService;
    echo "Got service from \$adminService\n";
} elseif (isset($GLOBALS['adminService'])) {
    $service = $GLOBALS['adminService'];
    echo "Got service from \$GLOBALS['adminService']\n";
} else {
    global $serviceManager;
    if ($serviceManager) {
        $service = $serviceManager->getService('admin');
        echo "Got service from \$serviceManager\n";
    } else {
        echo "Could not get service!\n";
    }
}

echo "Service is null: " . ($service === null ? "YES - PROBLEM!" : "NO") . "\n";

// 5. Test getOrderDetailsData
if ($service && $test === 'view') {
    echo "\n5. Testing getOrderDetailsData...\n";
    $order_id = (int)($_GET['id'] ?? 0);
    echo "Order ID: $order_id\n";
    
    try {
        $orderData = $service->getOrderDetailsData($order_id);
        echo "getOrderDetailsData result:\n";
        print_r($orderData);
    } catch (Exception $e) {
        echo "ERROR in getOrderDetailsData: " . $e->getMessage() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    }
}

// Also test find method directly
if ($test === 'view') {
    echo "\n5b. Testing OrdersModel find directly...\n";
    require_once $base_dir . '/app/models/OrdersModel.php';
    $ordersModel = new OrdersModel();
    $order_id = (int)($_GET['id'] ?? 0);
    try {
        $order = $ordersModel->find($order_id);
        echo "find($order_id) result:\n";
        print_r($order);
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
}

// 6. Test getOrdersData
if ($service && $test === 'list') {
    echo "\n6. Testing getOrdersData...\n";
    
    try {
        $ordersData = $service->getOrdersData(1, 10, []);
        echo "getOrdersData result:\n";
        print_r($ordersData);
    } catch (Exception $e) {
        echo "ERROR in getOrdersData: " . $e->getMessage() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    }
}

// 7. Test deleteOrder
if ($service && $test === 'delete') {
    echo "\n7. Testing deleteOrder...\n";
    $order_id = (int)($_GET['id'] ?? 0);
    echo "Order ID to delete: $order_id\n";
    
    try {
        // First check if order exists
        $orderData = $service->getOrderDetailsData($order_id);
        if ($orderData['order']) {
            echo "Order found, attempting delete...\n";
            $result = $service->deleteOrder($order_id);
            echo "Delete result: " . ($result ? "SUCCESS" : "FAILED") . "\n";
        } else {
            echo "Order not found!\n";
        }
    } catch (Exception $e) {
        echo "ERROR in deleteOrder: " . $e->getMessage() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    }
}

// 8. Test OrdersModel directly
echo "\n8. Testing OrdersModel directly...\n";
require_once $base_dir . '/app/models/OrdersModel.php';
$ordersModel = new OrdersModel();
echo "OrdersModel created\n";

if ($test === 'delete' && isset($order_id)) {
    try {
        echo "Calling delete($order_id)...\n";
        $result = $ordersModel->delete($order_id);
        echo "Delete result: " . ($result !== false ? "SUCCESS" : "FAILED") . "\n";
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
}

echo "\n</pre>";

// Links for testing
echo "<h2>Test Links:</h2>";
echo "<ul>";
echo "<li><a href='?test=list'>Test getOrdersData (List)</a></li>";
echo "<li><a href='?test=view&id=57'>Test getOrderDetailsData (View ID 57)</a></li>";
echo "<li><a href='?test=delete&id=57'>Test deleteOrder (Delete ID 57)</a></li>";
echo "</ul>";
?>
