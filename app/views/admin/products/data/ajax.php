<?php
/**
 * AJAX Handler for Product Data Management
 * Xử lý các thao tác CRUD dữ liệu sản phẩm qua AJAX
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set headers for JSON response
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Allow CORS for local development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Khởi tạo database connection
require_once __DIR__ . '/../../../../core/view_init.php';

require_once __DIR__ . '/../../../models/ProductDataModel.php';

$productDataModel = new ProductDataModel();

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Response helper function
function jsonResponse($success, $message = '', $data = [], $code = 200) {
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Check authentication
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    jsonResponse(false, 'Unauthorized', [], 401);
}

try {
    switch ($method) {
        case 'GET':
            handleGet($action, $productDataModel);
            break;
            
        case 'POST':
            handlePost($action, $productDataModel);
            break;
            
        default:
            jsonResponse(false, 'Method not allowed', [], 405);
    }
} catch (Exception $e) {
    error_log('Product Data AJAX Error: ' . $e->getMessage());
    jsonResponse(false, 'Lỗi server: ' . $e->getMessage(), [], 500);
}

/**
 * Handle GET requests
 */
function handleGet($action, $productDataModel) {
    $productId = (int)($_GET['product_id'] ?? 0);
    
    if (!$productId) {
        jsonResponse(false, 'Thiếu ID sản phẩm', [], 400);
    }
    
    switch ($action) {
        case 'list':
            // Get paginated data
            $page = max(1, (int)($_GET['page'] ?? 1));
            $perPage = (int)($_GET['per_page'] ?? 10);
            
            $result = $productDataModel->getByProductPaginated($productId, $page, $perPage);
            jsonResponse(true, '', $result);
            break;
            
        case 'count':
            // Get data count for a product
            $count = $productDataModel->countByProduct($productId);
            jsonResponse(true, '', ['count' => $count]);
            break;
            
        case 'get':
            // Get single data item
            $dataId = (int)($_GET['data_id'] ?? 0);
            if (!$dataId) {
                jsonResponse(false, 'Thiếu ID dữ liệu', [], 400);
            }
            
            $item = $productDataModel->find($dataId);
            if (!$item) {
                jsonResponse(false, 'Không tìm thấy dữ liệu', [], 404);
            }
            
            jsonResponse(true, '', $item);
            break;
            
        default:
            jsonResponse(false, 'Action không hợp lệ', [], 400);
    }
}

/**
 * Handle POST requests
 */
function handlePost($action, $productDataModel) {
    $productId = (int)($_POST['product_id'] ?? 0);
    
    if (!$productId) {
        jsonResponse(false, 'Thiếu ID sản phẩm', [], 400);
    }
    
    switch ($action) {
        case 'create':
            // Create new data
            $data = [
                'product_id' => $productId,
                'supplier_name' => trim($_POST['supplier_name'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
                'wechat_account' => trim($_POST['wechat_account'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'wechat_qr' => trim($_POST['wechat_qr'] ?? '')
            ];
            
            // Validate
            if (empty($data['supplier_name']) && empty($data['wechat_account']) && empty($data['phone'])) {
                jsonResponse(false, 'Vui lòng nhập ít nhất một thông tin', [], 400);
            }
            
            $id = $productDataModel->create($data);
            
            if ($id) {
                $count = $productDataModel->countByProduct($productId);
                jsonResponse(true, 'Thêm dữ liệu thành công', ['id' => $id, 'count' => $count]);
            } else {
                jsonResponse(false, 'Không thể thêm dữ liệu', [], 500);
            }
            break;
            
        case 'update':
            // Update existing data
            $dataId = (int)($_POST['data_id'] ?? 0);
            if (!$dataId) {
                jsonResponse(false, 'Thiếu ID dữ liệu', [], 400);
            }
            
            $data = [
                'supplier_name' => trim($_POST['supplier_name'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
                'wechat_account' => trim($_POST['wechat_account'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'wechat_qr' => trim($_POST['wechat_qr'] ?? '')
            ];
            
            $result = $productDataModel->update($dataId, $data);
            
            if ($result) {
                jsonResponse(true, 'Cập nhật dữ liệu thành công');
            } else {
                jsonResponse(false, 'Không thể cập nhật dữ liệu', [], 500);
            }
            break;
            
        case 'delete':
            // Delete single data
            $dataId = (int)($_POST['data_id'] ?? 0);
            if (!$dataId) {
                jsonResponse(false, 'Thiếu ID dữ liệu', [], 400);
            }
            
            $result = $productDataModel->delete($dataId);
            
            if ($result) {
                $count = $productDataModel->countByProduct($productId);
                jsonResponse(true, 'Xóa dữ liệu thành công', ['count' => $count]);
            } else {
                jsonResponse(false, 'Không thể xóa dữ liệu', [], 500);
            }
            break;
            
        case 'delete_all':
            // Delete all data for product
            $result = $productDataModel->deleteByProduct($productId);
            
            if ($result) {
                jsonResponse(true, 'Xóa tất cả dữ liệu thành công', ['count' => 0]);
            } else {
                jsonResponse(false, 'Không thể xóa dữ liệu', [], 500);
            }
            break;
            
        case 'import':
            // Handle Excel import - handled by import.php
            jsonResponse(false, 'Action import được xử lý bởi file riêng', [], 400);
            break;
            
        default:
            jsonResponse(false, 'Action không hợp lệ', [], 400);
    }
}
