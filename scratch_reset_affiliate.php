<?php
define('THUONGLO_INIT', true);
$config = require_once __DIR__ . '/config.php';
$db = new PDO(
    "mysql:host=" . $config['database']['host'] . ";dbname=" . $config['database']['name'] . ";charset=" . $config['database']['charset'],
    $config['database']['username'],
    $config['database']['password'],
    $config['database']['options']
);

$affiliateId = 6; // User ID 1 is affiliate ID 6

try {
    $db->beginTransaction();

    // 1. Delete wallet transactions
    $stmt1 = $db->prepare("DELETE FROM wallet_transactions WHERE affiliate_id = ?");
    $stmt1->execute([$affiliateId]);
    $txDeleted = $stmt1->rowCount();

    // 2. Delete withdrawal requests
    $stmt2 = $db->prepare("DELETE FROM withdrawal_requests WHERE affiliate_id = ?");
    $stmt2->execute([$affiliateId]);
    $wdDeleted = $stmt2->rowCount();

    // 3. Delete orders
    $stmt3 = $db->prepare("DELETE FROM orders WHERE affiliate_id = ?");
    $stmt3->execute([$affiliateId]);
    $ordersDeleted = $stmt3->rowCount();

    // 4. Update affiliates table row to 0
    $stmt4 = $db->prepare("
        UPDATE affiliates SET 
            balance = 0.00,
            pending_withdrawal = 0.00,
            total_withdrawn = 0.00,
            total_commission = 0.00,
            total_sales = 0.00,
            paid_commission = 0.00,
            pending_commission = 0.00
        WHERE id = ?
    ");
    $stmt4->execute([$affiliateId]);

    $db->commit();

    echo "<h3>Reset Affiliate Data Successful!</h3>";
    echo "<ul>";
    echo "<li>Deleted <strong>$txDeleted</strong> wallet transactions</li>";
    echo "<li>Deleted <strong>$wdDeleted</strong> withdrawal requests</li>";
    echo "<li>Deleted <strong>$ordersDeleted</strong> orders</li>";
    echo "<li>Reset all affiliate stats, balances, and totals in 'affiliates' table to 0.00</li>";
    echo "</ul>";
    echo "<p><a href='index.php?page=affiliate'>Go back to Affiliate Dashboard</a></p>";

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo "<h3>Error during reset:</h3> <pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}
