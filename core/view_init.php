<?php
/**
 * View Initialization System
 * 
 * Phase 4: Sử dụng ServiceManager với lazy loading.
 * Không còn phụ thuộc vào ViewDataService.
 * Mỗi service chỉ được tạo khi cần thiết thông qua ServiceManager.
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

// 3. Initialize ErrorHandler
if (!isset($errorHandler)) {
    require_once __DIR__ . '/../app/services/ErrorHandler.php';
    $errorHandler = new ErrorHandler();
}

// 4. Initialize ServiceManager (lazy loading - services created on demand)
if (!isset($serviceManager)) {
    require_once __DIR__ . '/../app/services/ServiceManager.php';

    $serviceManager = new ServiceManager($errorHandler);

    // Khởi tạo các service chính - lazy loaded qua ServiceManager
    // Mỗi service chỉ thực sự tạo instance khi getService() được gọi
    $publicService = $serviceManager->getService('public');
    $userService = $serviceManager->getService('user');
    $adminService = $serviceManager->getService('admin');
    $affiliateService = $serviceManager->getService('affiliate');
}

// 5. Set error reporting based on config
if (is_array($config) && isset($config['app']['debug']) && $config['app']['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    // Keep environment defaults
}
?>
