<?php
/**
 * View Initialization System
 * Standardizes service loading and prevents "White Screen of Death"
 */

// 1. Load basic configurations if not already loaded
if (!isset($config)) {
    $config = require_once __DIR__ . '/../config.php';
    if ($config === 1 || $config === true) {
        global $config;
    }
}

// 2. Load Core Functions
require_once __DIR__ . '/functions.php';

// 3. Initialize core services only if not already present
// This ensures safety when included via index.php OR called directly
if (!isset($errorHandler)) {
    require_once __DIR__ . '/../app/services/ErrorHandler.php';
    $errorHandler = new ErrorHandler();
}

// ServiceManager & typed services (public/user/admin/affiliate)
if (!isset($serviceManager)) {
    require_once __DIR__ . '/../app/services/ServiceManager.php';
    require_once __DIR__ . '/../app/services/PublicService.php';
    require_once __DIR__ . '/../app/services/UserService.php';
    require_once __DIR__ . '/../app/services/AdminService.php';
    require_once __DIR__ . '/../app/services/AffiliateService.php';

    $serviceManager = new ServiceManager($errorHandler);

    // Khởi tạo các service chính một lần, dùng lại cho toàn bộ request
    $publicService = $serviceManager->getService('public');
    $userService = $serviceManager->getService('user');
    $adminService = $serviceManager->getService('admin');
    $affiliateService = $serviceManager->getService('affiliate');
}

// 4. Set common error reporting for views
if (is_array($config) && isset($config['app']['debug']) && $config['app']['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    // Keep environment defaults from config.php if array is not available
    // or if debug is false
}
?>
