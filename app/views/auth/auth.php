<?php
require_once __DIR__ . '/../../../core/view_init.php';

// Khởi tạo session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Làm sạch dữ liệu đầu vào
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Lấy mã giới thiệu từ URL (không từ Cookie)
 */
function getRefCodeFromUrl() {
    if (isset($_GET['ref']) && !empty($_GET['ref'])) {
        $refCode = sanitize($_GET['ref']);
        return $refCode;
    }
    return '';
}

/**
 * Đăng nhập người dùng thực tế
 */
function authenticateUser($login, $password) {
    global $publicService;
    
    // Ensure PublicService is available
    if (!isset($publicService)) {
        require_once __DIR__ . '/../../../core/view_init.php';
    }
    
    try {
        $result = $publicService->authenticateUser($login, $password);
        
        if ($result && $result['success']) {
            $user = $result['user'];
            
            // Lưu thông tin user vào session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['phone'] = $user['phone'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['name'];
            $_SESSION['status'] = $user['status'];
            
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
            
            $_SESSION['login_time'] = date('Y-m-d H:i:s');
            $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
            $_SESSION['device_id'] = generateDeviceId();
            
            return $user;
        }
        
        return false;
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        return false;
    }
}

/**
 * Đăng ký người dùng mới
 */
function registerUser($fullName, $email, $phone, $password, $refCode = '') {
    global $publicService;
    
    // Ensure PublicService is available
    if (!isset($publicService)) {
        require_once __DIR__ . '/../../../core/view_init.php';
    }
    
    try {
        $userData = [
            'name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'password' => $password,
            'ref_code' => $refCode
        ];
        
        $result = $publicService->registerUser($userData);
        
        if ($result && $result['success']) {
            $user = $result['user'];
            
            // Lưu thông tin referral nếu có
            if (!empty($refCode) && !empty($result['referral_info'])) {
                $_SESSION['referred_by'] = $result['referral_info']['referred_by'];
                $_SESSION['referral_code'] = $refCode;
                if (!headers_sent()) {
                    setcookie('ref_code', $refCode, time() + (30 * 24 * 60 * 60), '/');
                } else {
                    $_SESSION['queued_ref_code'] = $refCode;
                }
            }
            
            // Tự động đăng nhập sau khi đăng ký
            return authenticateUser($email, $password);
        }
        
        return false;
    } catch (Exception $e) {
        error_log("Registration error: " . $e->getMessage());
        throw $e;
    }
}

/**
 * Tạo Device ID
 */
function generateDeviceId() {
    return 'DEV_' . bin2hex(random_bytes(8));
}

/**
 * Kiểm tra người dùng đã đăng nhập chưa
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']) && is_numeric($_SESSION['user_id']);
}

/**
 * Đăng xuất
 */
function logout() {
    session_destroy();
    setcookie('ref_code', '', time() - 3600, '/');
    setcookie('remember_phone', '', time() - 3600, '/');
    setcookie('remember_role', '', time() - 3600, '/');
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
 * Kiểm tra quyền truy cập theo role
 */
function requireRole($allowedRoles, $redirectPage = 'login') {
    requireAuth($redirectPage);
    
    if (!is_array($allowedRoles)) {
        $allowedRoles = [$allowedRoles];
    }
    
    $userRole = $_SESSION['role'] ?? '';
    if (!in_array($userRole, $allowedRoles)) {
        header("Location: " . page_url('403'));
        exit;
    }
}

/**
 * Lấy thông tin user hiện tại
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['full_name'] ?? '',
        'email' => $_SESSION['email'] ?? '',
        'phone' => $_SESSION['phone'] ?? '',
        'role' => $_SESSION['role'] ?? 'user',
        'status' => $_SESSION['status'] ?? 'active'
    ];
}

/**
 * Cập nhật mật khẩu người dùng
 */
function updateUserPassword($userId, $currentPassword, $newPassword) {
    global $publicService;
    
    // Ensure PublicService is available
    if (!isset($publicService)) {
        require_once __DIR__ . '/../../../core/view_init.php';
    }
    
    try {
        $result = $publicService->updateUserPassword($userId, $currentPassword, $newPassword);
        return $result['success'] ?? false;
    } catch (Exception $e) {
        error_log("Password update error: " . $e->getMessage());
        throw $e;
    }
}

/**
 * Reset mật khẩu qua email/phone
 */
function resetPassword($contact, $newPassword, $verificationCode) {
    global $publicService;
    
    // Ensure PublicService is available
    if (!isset($publicService)) {
        require_once __DIR__ . '/../../../core/view_init.php';
    }
    
    try {
        // Kiểm tra mã xác thực từ session
        if (!isset($_SESSION['reset_code']) || $_SESSION['reset_code'] != $verificationCode) {
            throw new Exception('Mã xác thực không hợp lệ');
        }
        
        if (!isset($_SESSION['reset_contact']) || $_SESSION['reset_contact'] != $contact) {
            throw new Exception('Thông tin liên hệ không khớp');
        }
        
        if (isset($_SESSION['reset_expires']) && time() > $_SESSION['reset_expires']) {
            throw new Exception('Mã xác thực đã hết hạn');
        }
        
        $result = $publicService->resetUserPassword($contact, $newPassword);
        
        if ($result['success']) {
            // Xóa session reset
            unset($_SESSION['reset_code'], $_SESSION['reset_contact'], $_SESSION['reset_expires']);
            return true;
        }
        
        return false;
    } catch (Exception $e) {
        error_log("Password reset error: " . $e->getMessage());
        throw $e;
    }
}

/**
 * Gửi mã xác thực reset password
 */
function sendResetCode($contact) {
    global $publicService;
    
    // Ensure PublicService is available
    if (!isset($publicService)) {
        require_once __DIR__ . '/../../../core/view_init.php';
    }
    
    try {
        $result = $publicService->sendPasswordResetCode($contact);
        
        if ($result['success']) {
            // Lưu vào session
            $_SESSION['reset_code'] = $result['code'];
            $_SESSION['reset_contact'] = $contact;
            $_SESSION['reset_expires'] = time() + 600; // 10 phút
            
            return $result['code'];
        }
        
        throw new Exception($result['message'] ?? 'Không thể gửi mã xác thực');
    } catch (Exception $e) {
        error_log("Send reset code error: " . $e->getMessage());
        throw $e;
    }
}
?>