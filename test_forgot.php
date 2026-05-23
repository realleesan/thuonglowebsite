<?php
define('THUONGLO_INIT', true);
session_start();

// Force error displaying
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Set exception handler to print everything
set_exception_handler(function($e) {
    echo "<h2>Uncaught Exception during diagnostics:</h2>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . " on line " . $e->getLine() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    exit;
});

echo "<h1>Starting Forgot Password Flow Diagnostics</h1>";

$base_dir = __DIR__;
$config = require_once $base_dir . '/config.php';
require_once $base_dir . '/core/security.php';
require_once $base_dir . '/core/functions.php';
require_once $base_dir . '/app/middleware/AuthMiddleware.php';
require_once $base_dir . '/core/view_init.php';

echo "<p>1. Framework initialization: <strong>SUCCESS</strong></p>";

// Initialize dependencies
require_once __DIR__ . '/app/services/InputValidator.php';
require_once __DIR__ . '/app/models/UsersModel.php';
require_once __DIR__ . '/app/services/EmailNotificationService.php';

$usersModel = new UsersModel();
$validator = new InputValidator();
$emailService = new EmailNotificationService();

echo "<p>2. Dependency Instantiation: <strong>SUCCESS</strong></p>";

$email = 'realleesan.dev@gmail.com';
echo "<p>Testing email: <strong>$email</strong></p>";

// Test 1: Validate email format
if (!$validator->validateEmail($email)) {
    echo "<p style='color:red;'>Test 1 (Email Validation): FAILED - Email format invalid</p>";
} else {
    echo "<p style='color:green;'>Test 1 (Email Validation): SUCCESS</p>";
}

// Test 2: Search user in DB
$user = $usersModel->findBy('email', $email);
if (!$user) {
    echo "<p style='color:orange;'>Test 2 (DB User Search): FAILED - User with email $email not found. Searching for any active user to test with...</p>";
    // Find any user to try with
    $anyUser = $usersModel->db->table('users')->first();
    if ($anyUser) {
        $email = $anyUser['email'];
        $user = $anyUser;
        echo "<p>Found active user email to test: <strong>$email</strong></p>";
    } else {
        echo "<p style='color:red;'>Test 2: FAILED - No users exist in database at all!</p>";
        exit;
    }
} else {
    echo "<p style='color:green;'>Test 2 (DB User Search): SUCCESS - User found with Name: " . htmlspecialchars($user['name']) . "</p>";
}

// Test 3: DB Invalidation query
try {
    echo "<p>Attempting DB UPDATE to invalidate old tokens...</p>";
    $usersModel->query("UPDATE password_reset_tokens SET used_at = NOW() WHERE email = ? AND used_at IS NULL", [$email]);
    echo "<p style='color:green;'>Test 3 (DB Update Invalidation): SUCCESS</p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>Test 3 (DB Update Invalidation): FAILED - " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test 4: DB Token Insertion
$otpCode = sprintf("%06d", random_int(100000, 999999));
$expiresAt = date('Y-m-d H:i:s', time() + 600);
try {
    echo "<p>Attempting DB INSERT for token: $otpCode, expires at: $expiresAt...</p>";
    $sql = "INSERT INTO password_reset_tokens (email, token, expires_at, created_at) VALUES (?, ?, ?, NOW())";
    $usersModel->query($sql, [$email, $otpCode, $expiresAt]);
    echo "<p style='color:green;'>Test 4 (DB Insert Token): SUCCESS</p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>Test 4 (DB Insert Token): FAILED - " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test 5: Email sending
try {
    echo "<p>Attempting to send email notification to $email with OTP: $otpCode...</p>";
    $sendResult = $emailService->sendPasswordResetCode($email, $user['name'] ?? $email, $otpCode);
    if ($sendResult) {
        echo "<p style='color:green;'>Test 5 (Email Sending): SUCCESS - Email sent successfully</p>";
    } else {
        echo "<p style='color:red;'>Test 5 (Email Sending): FAILED - Email sending failed (returned false)</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>Test 5 (Email Sending): FAILED with Exception: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<h2>All Diagnostics Finished!</h2>";
