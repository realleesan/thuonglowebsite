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
$action = $_GET['action'] ?? '';

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
    // Handle action-based requests (legacy format)
    if ($action && empty($path)) {
        switch ($action) {
            case 'getUserData':
                require_once __DIR__ . '/app/services/UserService.php';
                $userService = new UserService();
                $userId = $_SESSION['user_id'] ?? 0;
                
                if ($userId > 0) {
                    $accountData = $userService->getAccountData($userId);
                    $cartData = $userService->getCartData($userId);
                    $wishlistData = $userService->getWishlistData($userId);
                    
                    echo json_encode([
                        'success' => true,
                        'user' => $accountData['user'] ?? ['name' => 'Người dùng', 'level' => 'Basic'],
                        'cart' => $cartData['items'] ?? [],
                        'wishlist' => $wishlistData['items'] ?? []
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Not authenticated'
                    ]);
                }
                exit;
                
            default:
                throw new Exception('Unknown action: ' . $action, 404);
        }
    }
    
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
                
                // Nếu thiết bị đã được duyệt và có pending_user_id, hoàn tất đăng nhập
                if ($result['success'] && $result['status'] === 'active' && !empty($_SESSION['pending_user_id'])) {
                    require_once __DIR__ . '/app/services/AuthService.php';
                    $authService = new AuthService();
                    $completeResult = $authService->completePendingLogin($_SESSION['pending_user_id']);
                    if ($completeResult['success']) {
                        $result['login_completed'] = true;
                        $result['redirect_url'] = '?page=users';
                        // Xóa pending data
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

        // ==========================================
        // CART MANAGEMENT API
        // ==========================================
        
        case 'cart/add':
            if ($method === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                $productId = (int)($input['product_id'] ?? 0);
                $quantity = (int)($input['quantity'] ?? 1);
                
                // Check if user is logged in
                if (empty($_SESSION['user_id'])) {
                    echo json_encode([
                        'success' => false,
                        'require_login' => true,
                        'message' => 'Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng'
                    ]);
                    exit;
                }
                
                require_once __DIR__ . '/app/services/UserService.php';
                $userService = new UserService();
                
                // Get product price first
                require_once __DIR__ . '/app/models/ProductsModel.php';
                $productsModel = new ProductsModel();
                $product = $productsModel->find($productId);
                
                if (!$product) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Không tìm thấy sản phẩm'
                    ]);
                    exit;
                }
                
                $price = $product['price'] ?? 0;
                
                try {
                    $result = $userService->addToCart($_SESSION['user_id'], $productId, $quantity, $price);
                    
                    echo json_encode([
                        'success' => $result,
                        'message' => $result ? 'Đã thêm sản phẩm vào giỏ hàng' : 'Thêm vào giỏ hàng thất bại'
                    ]);
                } catch (Exception $e) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Lỗi: ' . $e->getMessage()
                    ]);
                }
            } else {
                throw new Exception('Method not allowed', 405);
            }
            break;

        case 'cart/checkout':
            if ($method === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                $productId = (int)($input['product_id'] ?? 0);
                $quantity = (int)($input['quantity'] ?? 1);
                
                // Check if user is logged in
                if (empty($_SESSION['user_id'])) {
                    echo json_encode([
                        'success' => false,
                        'require_login' => true,
                        'message' => 'Vui lòng đăng nhập để đặt hàng'
                    ]);
                    exit;
                }
                
                require_once __DIR__ . '/app/services/UserService.php';
                $userService = new UserService();
                
                // Get product price first
                require_once __DIR__ . '/app/models/ProductsModel.php';
                $productsModel = new ProductsModel();
                $product = $productsModel->find($productId);
                
                if (!$product) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Không tìm thấy sản phẩm'
                    ]);
                    exit;
                }
                
                $price = $product['price'] ?? 0;
                
                // Add to cart first
                $result = $userService->addToCart($_SESSION['user_id'], $productId, $quantity, $price);
                
                if ($result) {
                    // Redirect to checkout page
                    echo json_encode([
                        'success' => true,
                        'redirect' => '?page=payment&action=checkout'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Có lỗi xảy ra, vui lòng thử lại'
                    ]);
                }
            } else {
                throw new Exception('Method not allowed', 405);
            }
            break;

        case 'wishlist/add':
            if ($method === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                $productId = (int)($input['product_id'] ?? 0);
                
                // Check if user is logged in
                if (empty($_SESSION['user_id'])) {
                    echo json_encode([
                        'success' => false,
                        'require_login' => true,
                        'message' => 'Vui lòng đăng nhập để thêm sản phẩm vào yêu thích'
                    ]);
                    exit;
                }
                
                require_once __DIR__ . '/app/services/UserService.php';
                $userService = new UserService();
                $result = $userService->addToWishlist($_SESSION['user_id'], $productId);
                
                echo json_encode([
                    'success' => $result,
                    'message' => $result ? 'Đã thêm sản phẩm vào yêu thích' : 'Thêm vào yêu thích thất bại'
                ]);
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