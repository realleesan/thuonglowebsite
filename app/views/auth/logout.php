<?php
/**
 * Logout View - Handles user logout process
 * This view processes the logout and redirects to login page
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include AuthService for proper logout handling
require_once __DIR__ . '/../../services/AuthService.php';

// Get base_url function if available
function getLogoutBaseUrl() {
    // Try to get from config or use relative path
    if (function_exists('base_url')) {
        return base_url();
    }
    // Try to get from environment
    $baseUrl = getenv('APP_URL') ?: 'https://thuonglo.com';
    return $baseUrl;
}

try {
    $authService = new AuthService();
    $result = $authService->logout();
    
    if ($result) {
        $_SESSION['flash_success'] = 'Đăng xuất thành công';
    } else {
        $_SESSION['flash_error'] = 'Có lỗi xảy ra khi đăng xuất';
    }
} catch (Exception $e) {
    $_SESSION['flash_error'] = 'Có lỗi xảy ra: ' . $e->getMessage();
}

// Redirect to home page
$baseUrl = getLogoutBaseUrl();
header('Location: ' . $baseUrl);
exit;
?>