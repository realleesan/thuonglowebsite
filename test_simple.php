<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Simple Test</h1>";

// Test 1: Basic PHP
echo "<p>1. PHP working</p>";

// Test 2: Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
echo "<p>2. Session started</p>";

// Test 3: Check user login
$userId = $_SESSION['user_id'] ?? null;
if ($userId) {
    echo "<p>3. User logged in: ID = $userId</p>";
    echo "<p>User role: " . ($_SESSION['user_role'] ?? 'not set') . "</p>";
} else {
    echo "<p>3. User NOT logged in</p>";
    echo "<p><a href='?page=login'>Please login first</a></p>";
}

// Test 4: Try to load view_init
try {
    require_once __DIR__ . '/core/view_init.php';
    echo "<p>4. view_init.php loaded</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>4. Error loading view_init: " . $e->getMessage() . "</p>";
}

// Test 5: Check affiliateService
global $affiliateService;
if (isset($affiliateService)) {
    echo "<p>5. affiliateService exists</p>";
} else {
    echo "<p>5. affiliateService NOT exists</p>";
}

echo "<hr>";
echo "<p><a href='?page=affiliate'>Try affiliate page</a></p>";
echo "<p><a href='debug_affiliate.php'>Run debug script</a></p>";
?>
