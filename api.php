<?php
/**
 * API Router for Agent Registration System
 * Handles AJAX requests and API endpoints
 */

// Define security constant
define('THUONGLO_INIT', true);

// Start session
session_start();

// Load configuration
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/security.php';
require_once __DIR__ . '/core/functions.php';

// Set JSON response headers
header('Content-Type: application/json; charset=utf-8');

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = $_GET['path'] ?? '';

// Basic CORS headers for AJAX requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS requests
if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Route API requests
    switch ($path) {
        case 'agent/register':
            if ($method === 'POST') {
                require_once __DIR__ . '/app/controllers/AffiliateController.php';
                $controller = new AffiliateController();
                $controller->processRegistration();
            } else {
                throw new Exception('Method not allowed', 405);
            }
            break;
            
        case 'agent/popup':
            if ($method === 'GET') {
                require_once __DIR__ . '/app/controllers/AffiliateController.php';
                $controller = new AffiliateController();
                $controller->showRegistrationPopup();
            } else {
                throw new Exception('Method not allowed', 405);
            }
            break;
            
        case 'agent/status':
            if ($method === 'GET') {
                require_once __DIR__ . '/app/controllers/AffiliateController.php';
                $controller = new AffiliateController();
                $controller->checkStatus();
            } else {
                throw new Exception('Method not allowed', 405);
            }
            break;
            
        case 'admin/agents/approve':
            if ($method === 'POST') {
                require_once __DIR__ . '/app/controllers/AdminController.php';
                $controller = new AdminController();
                $controller->approveAgentRequest();
            } else {
                throw new Exception('Method not allowed', 405);
            }
            break;
            
        case 'admin/agents/update-status':
            if ($method === 'POST') {
                require_once __DIR__ . '/app/controllers/AdminController.php';
                $controller = new AdminController();
                $controller->updateAgentStatus();
            } else {
                throw new Exception('Method not allowed', 405);
            }
            break;
            
        case 'auth/register-with-agent':
            if ($method === 'POST') {
                require_once __DIR__ . '/app/controllers/AuthController.php';
                $controller = new AuthController();
                $controller->registerWithAgentOption();
            } else {
                throw new Exception('Method not allowed', 405);
            }
            break;
            
        case 'admin/dashboard/revenue':
            if ($method === 'GET') {
                require_once __DIR__ . '/app/controllers/AdminDashboardController.php';
                $controller = new AdminDashboardController();
                $controller->revenue();
            } else {
                throw new Exception('Method not allowed', 405);
            }
            break;

        case 'admin/dashboard/top-products':
            if ($method === 'GET') {
                require_once __DIR__ . '/app/controllers/AdminDashboardController.php';
                $controller = new AdminDashboardController();
                $controller->topProducts();
            } else {
                throw new Exception('Method not allowed', 405);
            }
            break;

        case 'admin/dashboard/orders-status':
            if ($method === 'GET') {
                require_once __DIR__ . '/app/controllers/AdminDashboardController.php';
                $controller = new AdminDashboardController();
                $controller->ordersStatus();
            } else {
                throw new Exception('Method not allowed', 405);
            }
            break;

        case 'admin/dashboard/new-users':
            if ($method === 'GET') {
                require_once __DIR__ . '/app/controllers/AdminDashboardController.php';
                $controller = new AdminDashboardController();
                $controller->newUsers();
            } else {
                throw new Exception('Method not allowed', 405);
            }
            break;

        case 'admin/dashboard/statistics':
            if ($method === 'GET') {
                require_once __DIR__ . '/app/controllers/AdminDashboardController.php';
                $controller = new AdminDashboardController();
                $controller->statistics();
            } else {
                throw new Exception('Method not allowed', 405);
            }
            break;

        case 'admin/dashboard/all':
            if ($method === 'GET') {
                require_once __DIR__ . '/app/controllers/AdminDashboardController.php';
                $controller = new AdminDashboardController();
                $controller->allCharts();
            } else {
                throw new Exception('Method not allowed', 405);
            }
            break;

        case 'admin/dashboard/notifications':
            if ($method === 'GET') {
                require_once __DIR__ . '/app/controllers/AdminDashboardController.php';
                $controller = new AdminDashboardController();
                $controller->notifications();
            } else {
                throw new Exception('Method not allowed', 405);
            }
            break;

        case 'admin/dashboard/cache/flush':
            if ($method === 'POST') {
                require_once __DIR__ . '/app/controllers/AdminDashboardController.php';
                $controller = new AdminDashboardController();
                $controller->flushCache();
            } else {
                throw new Exception('Method not allowed', 405);
            }
            break;

        default:
            throw new Exception('Endpoint not found', 404);
    }
    
} catch (Exception $e) {
    // Handle errors
    $statusCode = $e->getCode() ?: 500;
    http_response_code($statusCode);
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'code' => $statusCode
    ]);
}
?>