<?php
/**
 * ThuongLo Auth System - Middleware & Authentication Functions
 * Sử dụng Database thay vì JSON
 */

// Load Users Model
require_once __DIR__ . '/../../models/UsersModel.php';

// Khởi tạo session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Kiểm tra người dùng đã đăng nhập chưa
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Kiểm tra quyền truy cập theo Role
 */
function checkAccess($requiredRole) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $userRole = $_SESSION['role'] ?? 'user';
    
    // Admin có quyền truy cập tất cả
    if ($userRole === 'admin') {
        return true;
    }
    
    return $userRole === $requiredRole;
}

/**
 * Kiểm tra đăng nhập với database
 */
function authenticateUser($login, $password) {
    $usersModel = new UsersModel();
    return $usersModel->authenticate($login, $password);
}

/**
 * Đăng nhập người dùng
 */
function loginUser($login, $password) {
    $user = authenticateUser($login, $password);
    
    if ($user) {
        // Lưu thông tin user vào session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['phone'] = $user['phone'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['full_name'] = $user['name'];
        
        // Xác định dashboard URL theo role
        switch($user['role']) {
            case 'admin':
                $_SESSION['dashboard_url'] = '?page=admin&module=dashboard';
                break;
            case 'agent':
                $_SESSION['dashboard_url'] = '?page=affiliate&module=dashboard';
                break;
            default:
                $_SESSION['dashboard_url'] = '?page=users&module=dashboard';
        }
        
        // Gán thông tin bảo mật
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $_SESSION['device_id'] = generateDeviceId();
        
        return true;
    }
    
    return false;
}

/**
 * Đăng ký người dùng mới
 */
function registerUser($userData) {
    $usersModel = new UsersModel();
    
    try {
        $user = $usersModel->register($userData);
        return $user;
    } catch (Exception $e) {
        return false;
    }
}
    $_SESSION['login_time'] = date('Y-m-d H:i:s');
    
    // Quản lý thiết bị - Xóa session cũ nếu đăng nhập thiết bị mới
    handleDeviceManagement();
    
    // Ghi log bảo mật (mô phỏng)
    logSecurityEvent('login_success', $_SESSION['user_id']);
    
    return true;
}

if (!function_exists('mockRegister')) {
    function mockRegister($fullName, $email, $phone, $password, $refCode = '') {
        if (!empty($refCode)) {
            $_SESSION['referred_by'] = $refCode;
            if (!headers_sent()) {
                setcookie('ref_code', $refCode, time() + (30 * 24 * 60 * 60), '/');
            } else {
                $_SESSION['queued_ref_code'] = $refCode;
            }
        }

        return mockLogin($phone, $password, 'user');
    }
}

if (!function_exists('maybe_flush_auth_header')) {
    function maybe_flush_auth_header(string $url): void {
        if (!headers_sent()) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            header('Location: ' . $url);
            exit;
        }

        echo '<script>window.location.href = ' . json_encode($url) . ';</script>';
        echo '<noscript><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"></noscript>';
        exit;
    }
}

/**
 * Tạo User ID từ số điện thoại
 */
function generateUserId($phone) {
    return 'USR_' . substr(md5($phone), 0, 8);
}

/**
 * Tạo tên đầy đủ mẫu từ số điện thoại
 */
function generateFullName($phone) {
    // Use a simple hash-based approach to generate consistent names
    $hash = crc32($phone);
    
    // Generate name components based on phone hash
    $firstNameIndex = abs($hash) % 10;
    $middleNameIndex = abs($hash >> 8) % 8;
    $lastNameIndex = abs($hash >> 16) % 10;
    
    // Use configurable name arrays (could be moved to database in future)
    static $nameComponents = [
        'first' => ['Nguyễn', 'Trần', 'Lê', 'Phạm', 'Hoàng', 'Huỳnh', 'Phan', 'Vũ', 'Võ', 'Đặng'],
        'middle' => ['Văn', 'Thị', 'Minh', 'Hữu', 'Đức', 'Thanh', 'Quang', 'Anh'],
        'last' => ['An', 'Bình', 'Cường', 'Dũng', 'Hải', 'Khoa', 'Long', 'Nam', 'Phong', 'Quân']
    ];
    
    return $nameComponents['first'][$firstNameIndex] . ' ' . 
           $nameComponents['middle'][$middleNameIndex] . ' ' . 
           $nameComponents['last'][$lastNameIndex];
}

/**
 * Tạo Device ID ngẫu nhiên
 */
function generateDeviceId() {
    return 'DEV_' . uniqid() . '_' . rand(1000, 9999);
}

/**
 * Quản lý thiết bị - Mô phỏng logout thiết bị cũ
 */
function handleDeviceManagement() {
    $currentDeviceId = $_SESSION['device_id'];
    $previousDeviceId = $_SESSION['previous_device_id'] ?? null;
    
    if ($previousDeviceId && $previousDeviceId !== $currentDeviceId) {
        // Mô phỏng logout thiết bị cũ
        logSecurityEvent('device_logout', $_SESSION['user_id'], "Old device: $previousDeviceId");
    }
    
    $_SESSION['previous_device_id'] = $currentDeviceId;
}

/**
 * Phát hiện đăng nhập bất thường
 */
function detectSuspiciousLogin() {
    $currentIp = $_SESSION['ip_address'] ?? '';
    $previousIp = $_SESSION['previous_ip'] ?? '';
    
    // Mô phỏng: Nếu IP khác nhau thì cảnh báo
    if ($previousIp && $previousIp !== $currentIp) {
        $_SESSION['security_alert'] = 'Phát hiện IP lạ';
        logSecurityEvent('suspicious_ip', $_SESSION['user_id'], "New IP: $currentIp, Previous: $previousIp");
        return true;
    }
    
    $_SESSION['previous_ip'] = $currentIp;
    $_SESSION['security_alert'] = 'Bình thường';
    return false;
}

/**
 * Ghi log bảo mật (mô phỏng)
 */
function logSecurityEvent($action, $userId, $details = '') {
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'action' => $action,
        'user_id' => $userId,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
        'details' => $details
    ];
    
    // Trong thực tế sẽ ghi vào database hoặc file log
    // Hiện tại chỉ lưu vào session để demo
    if (!isset($_SESSION['security_logs'])) {
        $_SESSION['security_logs'] = [];
    }
    $_SESSION['security_logs'][] = $logEntry;
}

/**
 * Lấy mã giới thiệu từ Cookie hoặc URL
 */
function getRefCode() {
    // Ưu tiên lấy từ URL parameter
    if (isset($_GET['ref']) && !empty($_GET['ref'])) {
        $refCode = sanitize($_GET['ref']);

        return $refCode;
    }
    
    // Lấy từ Cookie
    return $_COOKIE['ref_code'] ?? '';
}

/**
 * Chỉ lấy mã giới thiệu từ URL (không từ Cookie)
 */
function getRefCodeFromUrl() {
    if (isset($_GET['ref']) && !empty($_GET['ref'])) {
        $refCode = sanitize($_GET['ref']);
        return $refCode;
    }
    return '';
}

/**
 * Làm sạch dữ liệu đầu vào
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Đăng xuất
 */
function logout() {
    if (isLoggedIn()) {
        logSecurityEvent('logout', $_SESSION['user_id']);
    }
    
    session_destroy();
    
    // Xóa cookie ref_code khi logout
    setcookie('ref_code', '', time() - 3600, '/');
    
    header('Location: ' . page_url('login'));
    exit;
}

/**
 * Yêu cầu đăng nhập
 */
function requireAuth($redirectPage = 'login') {
    if (!isLoggedIn()) {
        header("Location: " . page_url($redirectPage));
        exit;
    }
}

/**
 * Yêu cầu quyền truy cập
 */
function requireRole($requiredRole, $redirectTo = 'login') {
    if (!checkAccess($requiredRole)) {
        header("Location: " . page_url('login'));
        exit;
    }
}

/**
 * Lấy thông tin debug cho Debug Console
 */
function getDebugInfo() {
    return [
        'status' => isLoggedIn() ? 'Logged In' : 'Guest',
        'role' => $_SESSION['role'] ?? 'N/A',
        'ref_code' => getRefCode() ?: 'Không có',
        'security_alert' => $_SESSION['security_alert'] ?? 'Bình thường',
        'device_id' => $_SESSION['device_id'] ?? 'N/A',
        'login_time' => $_SESSION['login_time'] ?? 'N/A'
    ];
}

// Tự động phát hiện đăng nhập bất thường nếu đã đăng nhập
if (isLoggedIn()) {
    detectSuspiciousLogin();
}
?>