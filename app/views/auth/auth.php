<?php
/**
 * ThuongLo Auth System - Middleware & Authentication Functions
 * Mô phỏng hệ thống xác thực không cần Database
 */

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
 * Load demo accounts từ JSON file
 */
function loadDemoAccounts() {
    $jsonFile = __DIR__ . '/data/demo_accounts.json';
    if (file_exists($jsonFile)) {
        $jsonContent = file_get_contents($jsonFile);
        return json_decode($jsonContent, true);
    }
    return [];
}

/**
 * Kiểm tra đăng nhập với demo account
 */
function checkDemoLogin($phone, $password, $role) {
    $demoAccounts = loadDemoAccounts();
    
    if (isset($demoAccounts[$role])) {
        $account = $demoAccounts[$role];
        return ($account['phone'] === $phone && $account['password'] === $password);
    }
    
    return false;
}

/**
 * Lấy thông tin demo account theo role
 */
function getDemoAccount($role) {
    $demoAccounts = loadDemoAccounts();
    return $demoAccounts[$role] ?? null;
}

/**
 * Mô phỏng đăng nhập - Luôn thành công
 */
function mockLogin($phone, $password, $role = 'user') {
    // Kiểm tra demo account trước
    $demoAccount = getDemoAccount($role);
    if ($demoAccount && checkDemoLogin($phone, $password, $role)) {
        // Sử dụng thông tin từ demo account
        $_SESSION['user_id'] = generateUserId($phone);
        $_SESSION['phone'] = $demoAccount['phone'];
        $_SESSION['role'] = $demoAccount['role'];
        $_SESSION['full_name'] = $demoAccount['full_name'];
        $_SESSION['email'] = $demoAccount['email'];
        $_SESSION['dashboard_url'] = $demoAccount['dashboard_url'];
    } else {
        // Fallback về logic cũ
        $_SESSION['user_id'] = generateUserId($phone);
        $_SESSION['phone'] = $phone;
        $_SESSION['role'] = $role;
        $_SESSION['full_name'] = generateFullName($phone);
        $_SESSION['email'] = $phone . '@thuonglo.com';
        
        // Xác định dashboard URL theo role
        switch($role) {
            case 'admin':
                $_SESSION['dashboard_url'] = '?page=admin&module=dashboard';
                break;
            case 'agent':
                $_SESSION['dashboard_url'] = '?page=affiliate&module=dashboard';
                break;
            default:
                $_SESSION['dashboard_url'] = '?page=users&module=dashboard';
        }
    }
    
    // Gán thông tin bảo mật
    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    $_SESSION['device_id'] = generateDeviceId();
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
    $names = ['Nguyễn Văn A', 'Trần Thị B', 'Lê Văn C', 'Phạm Thị D', 'Hoàng Văn E'];
    return $names[array_sum(str_split($phone)) % count($names)];
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