<?php
/**
 * Test file to simulate the redirect scenario
 * Run this at: https://test1.web3b.com/test_redirect.php?id=34
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Redirect Test</h1>";

$product_id = (int)($_GET['id'] ?? 34);

// Check if this is a redirect (simulating the POST -> redirect scenario)
$is_redirect = isset($_GET['redirect']);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If redirect, set the session variable (like after successful save)
if ($is_redirect) {
    $_SESSION['product_saved'] = $product_id;
    echo "<p>Set session product_saved = $product_id</p>";
}

echo "<p>Session product_saved: " . ($_SESSION['product_saved'] ?? 'not set') . "</p>";

// Now simulate what the edit.php does
$showSuccessMessage = false;
if (isset($_SESSION['product_saved']) && $_SESSION['product_saved'] === $product_id) {
    $showSuccessMessage = true;
    echo "<p style='color:green'>Success message will be shown!</p>";
    unset($_SESSION['product_saved']);
} else {
    echo "<p style='color:orange'>Success message will NOT be shown</p>";
}

echo "<hr>";
echo "<h2>Test Links</h2>";
echo "<ul>";
echo "<li><a href='?id=$product_id'>Normal load (GET)</a></li>";
echo "<li><a href='?id=$product_id&redirect=1'>Simulate redirect (GET with redirect param)</a></li>";
echo "<li><a href='?page=admin&module=products&action=edit&id=$product_id'>Direct edit URL</a></li>";
echo "</ul>";

echo "<hr>";
echo "<h2>Direct Edit Page Test</h2>";
echo "<p><a href='?page=admin&module=products&action=edit&id=$product_id' target='_blank'>Open edit page in new tab</a></p>";
