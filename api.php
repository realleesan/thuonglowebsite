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

        // ==========================================
        // DEVICE ACCESS MANAGEMENT API
        // ==========================================
        
        case 'device/verify-email':
            if ($method === 'POST') {
                require_once __DIR__ . '/app/services/DeviceAccessService.php';
                $service = new DeviceAccessService();
                $input = json_decode(file_get_contents('php://input'), true);
                $userId = $_SESSION['pending_user_id'] ?? ($_SESSION['user_id'] ?? 0);
                $deviceSessionId = $_SESSION['pending_device_session_id'] ?? ($input['device_session_id'] ?? 0);
                $result = $service->initiateEmailVerification($userId, $input['email'] ?? '', (int)$deviceSessionId);
                echo json_encode($result);
            } else {
                throw new Exception('Method not allowed', 405);
            }
            break;

        case 'device/verify-otp':
            if ($method === 'POST') {
                require_once __DIR__ . '/app/services/DeviceAccessService.php';
                $service = new DeviceAccessService();
                $input = json_decode(file_get_contents('php://input'), true);
                $userId = $_SESSION['pending_user_id'] ?? ($_SESSION['user_id'] ?? 0);
                $deviceSessionId = $_SESSION['pending_device_session_id'] ?? ($input['device_session_id'] ?? 0);
                $result = $service->verifyOTP($userId, $input['code'] ?? '', (int)$deviceSessionId);
                
                // Nếu xác thực thành công, tạo session đầy đủ
                if ($result['success']) {
                    if (isset($_SESSION['pending_user_data'])) {
                        $userData = $_SESSION['pending_user_data'];
                        $_SESSION['user_id'] = $userData['id'];
                        $_SESSION['user_name'] = $userData['name'];
                        $_SESSION['username'] = $userData['username'] ?? '';
                        $_SESSION['user_email'] = $userData['email'];
                        $_SESSION['user_role'] = $userData['role'];
                        $_SESSION['is_logged_in'] = true;
                        // Clear pending data
                        unset($_SESSION['pending_user_id']);
                        unset($_SESSION['pending_user_data']);
                        unset($_SESSION['pending_device_session_id']);
                    }
                }
                
                echo json_encode($result);
            } else {
                throw new Exception('Method not allowed', 405);
            }
            break;

        case 'device/resend-otp':
            if ($method === 'POST') {
                require_once __DIR__ . '/app/services/DeviceAccessService.php';
                $service = new DeviceAccessService();
                $input = json_decode(file_get_contents('php://input'), true);
                $userId = $_SESSION['pending_user_id'] ?? ($_SESSION['user_id'] ?? 0);
                $deviceSessionId = $_SESSION['pending_device_session_id'] ?? ($input['device_session_id'] ?? 0);
                $result = $service->resendOTP($userId, (int)$deviceSessionId);
                echo json_encode($result);
            } else {
                throw new Exception('Method not allowed', 405);
            }
            break;

        case 'device/poll-status':
            if ($method === 'GET') {
                require_once __DIR__ . '/app/services/DeviceAccessService.php';
                $service = new DeviceAccessService();
                $deviceSessionId = $_GET['device_session_id'] ?? ($_SESSION['pending_device_session_id'] ?? 0);
                $result = $service->pollDeviceStatus((int)$deviceSessionId);
                echo json_encode($result);
            } else {
                throw new Exception('Method not allowed', 405);
            }
            break;

        case 'device/list':
            if ($method === 'GET') {
                if (empty($_SESSION['user_id'])) {
                    throw new Exception('Unauthorized', 401);
                }
                require_once __DIR__ . '/app/services/DeviceAccessService.php';
                $service = new DeviceAccessService();
                $result = $service->getDeviceList((int)$_SESSION['user_id']);
                echo json_encode($result);
            } else {
                throw new Exception('Method not allowed', 405);
            }
            break;

        case 'device/approve':
            if ($method === 'POST') {
                if (empty($_SESSION['user_id'])) {
                    throw new Exception('Unauthorized', 401);
                }
                require_once __DIR__ . '/app/services/DeviceAccessService.php';
                $service = new DeviceAccessService();
                $input = json_decode(file_get_contents('php://input'), true);
                $result = $service->approveDevice(
                    (int)$_SESSION['user_id'],
                    (int)($input['device_session_id'] ?? 0),
                    $input['password'] ?? ''
                );
                echo json_encode($result);
            } else {
                throw new Exception('Method not allowed', 405);
            }
            break;

        case 'device/reject':
            if ($method === 'POST') {
                if (empty($_SESSION['user_id'])) {
                    throw new Exception('Unauthorized', 401);
                }
                require_once __DIR__ . '/app/services/DeviceAccessService.php';
                $service = new DeviceAccessService();
                $input = json_decode(file_get_contents('php://input'), true);
                $result = $service->rejectDevice(
                    (int)$_SESSION['user_id'],
                    (int)($input['device_session_id'] ?? 0)
                );
                echo json_encode($result);
            } else {
                throw new Exception('Method not allowed', 405);
            }
            break;

        case 'device/remove':
            if ($method === 'POST') {
                if (empty($_SESSION['user_id'])) {
                    throw new Exception('Unauthorized', 401);
                }
                require_once __DIR__ . '/app/services/DeviceAccessService.php';
                $service = new DeviceAccessService();
                $input = json_decode(file_get_contents('php://input'), true);
                $result = $service->removeDevice(
                    (int)$_SESSION['user_id'],
                    (int)($input['device_id'] ?? 0)
                );
                
                echo json_encode($result);
            } else {
                throw new Exception('Method not allowed', 405);
            }
            break;

        case 'device/pending':
            if ($method === 'GET') {
                if (empty($_SESSION['user_id'])) {
                    throw new Exception('Unauthorized', 401);
                }
                require_once __DIR__ . '/app/services/DeviceAccessService.php';
                $service = new DeviceAccessService();
                $model = $service->getModel('DeviceAccessModel');
                $pending = $model->getPendingDevices((int)$_SESSION['user_id']);
                echo json_encode(['success' => true, 'devices' => $pending]);
            } else {
                throw new Exception('Method not allowed', 405);
            }
            break;

        case 'device/auto-login':
            if ($method === 'POST') {
                require_once __DIR__ . '/app/services/DeviceAccessService.php';
                $service = new DeviceAccessService();
                $input = json_decode(file_get_contents('php://input'), true);
                $deviceSessionId = (int)($input['device_session_id'] ?? 0);
                
                if (!$deviceSessionId) {
                    throw new Exception('Device session ID is required', 400);
                }
                
                // Giữ nguyên session_id từ cookie nếu có
                $currentSessionId = session_id();
                
                $result = $service->autoLogin($deviceSessionId);
                
                if ($result['success']) {
                    // Khôi phục session_id để giữ nguyên session
                    if (session_id() !== $currentSessionId) {
                        session_id($currentSessionId);
                    }
                    
                    // Tạo session cho user
                    $_SESSION['user_id'] = $result['user']['id'];
                    $_SESSION['user_name'] = $result['user']['name'];
                    $_SESSION['user_email'] = $result['user']['email'];
                    $_SESSION['username'] = $result['user']['username'];
                    $_SESSION['user_role'] = $result['user']['role'];
                    $_SESSION['user'] = $result['user'];
                    
                    // Xóa pending device session
                    unset($_SESSION['pending_user_id']);
                    unset($_SESSION['pending_user_data']);
                    unset($_SESSION['pending_device_session_id']);
                    
                    // Lưu session
                    session_write_close();
                }
                
                echo json_encode($result);
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