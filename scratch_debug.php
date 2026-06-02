<?php
define('THUONGLO_INIT', true);
header('Content-Type: text/plain; charset=utf-8');

try {
    // Mock session and server info
    $_SESSION['user_id'] = 1;
    $_SESSION['user_role'] = 'affiliate';
    
    // Boot the app core
    require_once __DIR__ . '/core/security.php';
    require_once __DIR__ . '/core/functions.php';
    require_once __DIR__ . '/core/view_init.php';
    require_once __DIR__ . '/app/services/AffiliateService.php';

    $service = new AffiliateService();

    echo "=== TEST getDashboardData (User ID 1) ===\n";
    $dashData = $service->getDashboardData(1);
    echo "Stats:\n";
    print_r($dashData['stats']);
    echo "Revenue Chart Config (First 5 labels/data):\n";
    print_r(array_slice($dashData['revenue_chart']['labels'], 0, 5));
    print_r(array_slice($dashData['revenue_chart']['data'], 0, 5));

    echo "\n=== TEST getFinanceData (User ID 1) ===\n";
    $financeData = $service->getFinanceData(1);
    print_r(array_diff_key($financeData, ['transactions' => []]));
    echo "Transactions Count: " . count($financeData['transactions']) . "\n";
    if (!empty($financeData['transactions'])) {
        echo "First Transaction:\n";
        print_r($financeData['transactions'][0]);
    }

    echo "\n=== TEST getCommissionsData (User ID 1) ===\n";
    $commissionsData = $service->getCommissionsData(1);
    print_r(array_diff_key($commissionsData, ['history' => []]));
    echo "History Count: " . count($commissionsData['history']) . "\n";
    if (!empty($commissionsData['history'])) {
        echo "First History Item:\n";
        print_r($commissionsData['history'][0]);
    }

    echo "\n=== TEST getClicksData (User ID 1) ===\n";
    $clicksData = $service->getClicksData(1);
    echo "Total Clicks: " . $clicksData['total_clicks'] . "\n";
    echo "Unique Clicks: " . $clicksData['unique_clicks'] . "\n";
    echo "Click Rate: " . $clicksData['click_rate'] . "%\n";
    echo "by_date Count: " . count($clicksData['by_date']) . "\n";
    echo "by_source Count: " . count($clicksData['by_source']) . "\n";

    echo "\n=== TEST getOrdersData (User ID 1) ===\n";
    $ordersData = $service->getOrdersData(1);
    echo "Total Orders: " . $ordersData['total_orders'] . "\n";
    echo "Total Revenue: " . $ordersData['total_revenue'] . "\n";
    echo "Total Commission: " . $ordersData['total_commission'] . "\n";
    echo "by_date Count: " . count($ordersData['by_date']) . "\n";
    echo "by_status:\n";
    print_r($ordersData['by_status']);

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
