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

// 1. Get affiliate info
$stmt = $db->prepare("SELECT * FROM affiliates WHERE id = ?");
$stmt->execute([$affiliateId]);
$aff = $stmt->fetch();
echo "Affiliate Info:\n";
print_r($aff);

// 2. Get orders count and total
$stmt = $db->prepare("SELECT COUNT(*) as cnt, SUM(total) as sum_total FROM orders WHERE affiliate_id = ?");
$stmt->execute([$affiliateId]);
$ordersSummary = $stmt->fetch();
echo "\nOrders Summary (All statuses):\n";
print_r($ordersSummary);

// 3. Get orders by status
$stmt = $db->prepare("SELECT status, COUNT(*) as cnt, SUM(total) as sum_total FROM orders WHERE affiliate_id = ? GROUP BY status");
$stmt->execute([$affiliateId]);
$statusSummary = $stmt->fetchAll();
echo "\nOrders by Status:\n";
print_r($statusSummary);

// 5. Get transactions summary
$stmt = $db->prepare("SELECT type, status, COUNT(*) as cnt, SUM(amount) as sum_amount FROM wallet_transactions WHERE affiliate_id = ? GROUP BY type, status");
$stmt->execute([$affiliateId]);
$txSummary = $stmt->fetchAll();
echo "\nTransactions Summary:\n";
print_r($txSummary);

// 6. List all transactions
$stmt = $db->prepare("SELECT id, type, amount, balance_before, balance_after, status, created_at FROM wallet_transactions WHERE affiliate_id = ?");
$stmt->execute([$affiliateId]);
$txList = $stmt->fetchAll();
echo "\nDetail Transactions List:\n";
print_r($txList);

