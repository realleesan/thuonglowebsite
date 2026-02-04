<?php
/**
 * ThuongLo Auth System - Middleware & Authentication Functions
 * MÃ´ phá»ng há»‡ thá»‘ng xÃ¡c thá»±c khÃ´ng cáº§n Database
 */

// Khá»Ÿi táº¡o session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Kiá»ƒm tra ngÆ°á»i dÃ¹ng Ä‘Ã£ Ä‘Äƒng nháº­p chÆ°a
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Kiá»ƒm tra quyá»n truy cáº­p theo Role
 */
function checkAccess($requiredRole) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $userRole = $_SESSION['role'] ?? 'user';
    
    // Admin cÃ³ quyá»n truy cáº­p táº¥t cáº£
    if ($userRole === 'admin') {
        return true;
    }
    
    return $userRole === $requiredRole;
}

/**
 * MÃ´ phá»ng Ä‘Äƒng nháº­p - LuÃ´n thÃ nh cÃ´ng
 */
function mockLogin($phone, $password, $role = 'user') {
    // Tá»± Ä‘á»™ng gÃ¡n thÃ´ng tin ngÆ°á»i dÃ¹ng
    $_SESSION['user_id'] = generateUserId($phone);
    $_SESSION['phone'] = $phone;
    $_SESSION['role'] = $role;
    $_SESSION['full_name'] = generateFullName($phone);
    
    // GÃ¡n thÃ´ng tin báº£o máº­t
    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    $_SESSION['device_id'] = generateDeviceId();
    $_SESSION['login_time'] = date('Y-m-d H:i:s');
    
    // Quáº£n lÃ½ thiáº¿t bá»‹ - XÃ³a session cÅ© náº¿u Ä‘Äƒng nháº­p thiáº¿t bá»‹ má»›i
    handleDeviceManagement();
    
    // Ghi log báº£o máº­t (mÃ´ phá»ng)
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
 * Táº¡o User ID tá»« sá»‘ Ä‘iá»‡n thoáº¡i
 */
function generateUserId($phone) {
    return 'USR_' . substr(md5($phone), 0, 8);
}

/**
 * Táº¡o tÃªn Ä‘áº§y Ä‘á»§ máº«u tá»« sá»‘ Ä‘iá»‡n thoáº¡i
 */
function generateFullName($phone) {
    $names = ['Nguyá»…n VÄƒn A', 'Tráº§n Thá»‹ B', 'LÃª VÄƒn C', 'Pháº¡m Thá»‹ D', 'HoÃ ng VÄƒn E'];
    return $names[array_sum(str_split($phone)) % count($names)];
}

/**
 * Táº¡o Device ID ngáº«u nhiÃªn
 */
function generateDeviceId() {
    return 'DEV_' . uniqid() . '_' . rand(1000, 9999);
}

/**
 * Quáº£n lÃ½ thiáº¿t bá»‹ - MÃ´ phá»ng logout thiáº¿t bá»‹ cÅ©
 */
function handleDeviceManagement() {
    $currentDeviceId = $_SESSION['device_id'];
    $previousDeviceId = $_SESSION['previous_device_id'] ?? null;
    
    if ($previousDeviceId && $previousDeviceId !== $currentDeviceId) {
        // MÃ´ phá»ng logout thiáº¿t bá»‹ cÅ©
        logSecurityEvent('device_logout', $_SESSION['user_id'], "Old device: $previousDeviceId");
    }
    
    $_SESSION['previous_device_id'] = $currentDeviceId;
}

/**
 * PhÃ¡t hiá»‡n Ä‘Äƒng nháº­p báº¥t thÆ°á»ng
 */
function detectSuspiciousLogin() {
    $currentIp = $_SESSION['ip_address'] ?? '';
    $previousIp = $_SESSION['previous_ip'] ?? '';
    
    // MÃ´ phá»ng: Náº¿u IP khÃ¡c nhau thÃ¬ cáº£nh bÃ¡o
    if ($previousIp && $previousIp !== $currentIp) {
        $_SESSION['security_alert'] = 'PhÃ¡t hiá»‡n IP láº¡';
        logSecurityEvent('suspicious_ip', $_SESSION['user_id'], "New IP: $currentIp, Previous: $previousIp");
        return true;
    }
    
    $_SESSION['previous_ip'] = $currentIp;
    $_SESSION['security_alert'] = 'BÃ¬nh thÆ°á»ng';
    return false;
}

/**
 * Ghi log báº£o máº­t (mÃ´ phá»ng)
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
    
    // Trong thá»±c táº¿ sáº½ ghi vÃ o database hoáº·c file log
    // Hiá»‡n táº¡i chá»‰ lÆ°u vÃ o session Ä‘á»ƒ demo
    if (!isset($_SESSION['security_logs'])) {
        $_SESSION['security_logs'] = [];
    }
    $_SESSION['security_logs'][] = $logEntry;
}

/**
 * Láº¥y mÃ£ giá»›i thiá»‡u tá»« Cookie hoáº·c URL
 */
function getRefCode() {
    // Æ¯u tiÃªn láº¥y tá»« URL parameter
    if (isset($_GET['ref']) && !empty($_GET['ref'])) {
        $refCode = sanitize($_GET['ref']);

        return $refCode;
    }
    
    // Láº¥y tá»« Cookie
    return $_COOKIE['ref_code'] ?? '';
}

/**
 * Chá»‰ láº¥y mÃ£ giá»›i thiá»‡u tá»« URL (khÃ´ng tá»« Cookie)
 */
function getRefCodeFromUrl() {
    if (isset($_GET['ref']) && !empty($_GET['ref'])) {
        $refCode = sanitize($_GET['ref']);
        return $refCode;
    }
    return '';
}

/**
 * LÃ m sáº¡ch dá»¯ liá»‡u Ä‘áº§u vÃ o
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * ÄÄƒng xuáº¥t
 */
function logout() {
    if (isLoggedIn()) {
        logSecurityEvent('logout', $_SESSION['user_id']);
    }
    
    session_destroy();
    
    // XÃ³a cookie ref_code khi logout
    setcookie('ref_code', '', time() - 3600, '/');
    
    header('Location: ' . page_url('login'));
    exit;
}

/**
 * YÃªu cáº§u Ä‘Äƒng nháº­p
 */
function requireAuth($redirectPage = 'login') {
    if (!isLoggedIn()) {
        header("Location: " . page_url($redirectPage));
        exit;
    }
}

/**
 * YÃªu cáº§u quyá»n truy cáº­p
 */
function requireRole($requiredRole, $redirectTo = 'login') {
    if (!checkAccess($requiredRole)) {
        header("Location: " . page_url('login'));
        exit;
    }
}

/**
 * Láº¥y thÃ´ng tin debug cho Debug Console
 */
function getDebugInfo() {
    return [
        'status' => isLoggedIn() ? 'Logged In' : 'Guest',
        'role' => $_SESSION['role'] ?? 'N/A',
        'ref_code' => getRefCode() ?: 'KhÃ´ng cÃ³',
        'security_alert' => $_SESSION['security_alert'] ?? 'BÃ¬nh thÆ°á»ng',
        'device_id' => $_SESSION['device_id'] ?? 'N/A',
        'login_time' => $_SESSION['login_time'] ?? 'N/A'
    ];
}

// Tá»± Ä‘á»™ng phÃ¡t hiá»‡n Ä‘Äƒng nháº­p báº¥t thÆ°á»ng náº¿u Ä‘Ã£ Ä‘Äƒng nháº­p
if (isLoggedIn()) {
    detectSuspiciousLogin();
}
?>