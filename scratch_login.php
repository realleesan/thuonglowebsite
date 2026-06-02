<?php
define('THUONGLO_INIT', true);
session_start();
$config = require_once __DIR__ . '/config.php';

// Get user ID 1
$db = new PDO(
    "mysql:host=" . $config['database']['host'] . ";dbname=" . $config['database']['name'] . ";charset=" . $config['database']['charset'],
    $config['database']['username'],
    $config['database']['password'],
    $config['database']['options']
);
$stmt = $db->prepare("SELECT * FROM users WHERE id = 1");
$stmt->execute();
$user = $stmt->fetch();

if ($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_name'] = $user['name'] ?? '';
    $_SESSION['username'] = $user['username'] ?? '';
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();
    $_SESSION['is_authenticated'] = true;
    $_SESSION['agent_request_status'] = $user['agent_request_status'] ?? 'none';
    $_SESSION['session_token'] = bin2hex(random_bytes(16));
    $_SESSION['login_ip'] = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $_SESSION['login_user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $_SESSION['session_fingerprint'] = md5($_SESSION['login_ip'] . $_SESSION['login_user_agent']);
    $_SESSION['session_created'] = time();
    
    // Add device to database if not exists
    $stmt = $db->prepare("SELECT id FROM user_devices WHERE user_id = 1 LIMIT 1");
    $stmt->execute();
    $device = $stmt->fetch();
    if (!$device) {
        $db->prepare("INSERT INTO user_devices (user_id, device_name, ip_address, user_agent, is_verified, session_id, last_activity) VALUES (1, 'Chrome Windows', '127.0.0.1', ?, 1, ?, NOW())")
           ->execute([$_SESSION['login_user_agent'], session_id()]);
        $deviceId = $db->lastInsertId();
    } else {
        $deviceId = $device['id'];
        $db->prepare("UPDATE user_devices SET session_id = ?, last_activity = NOW() WHERE id = ?")
           ->execute([session_id(), $deviceId]);
    }
    $_SESSION['device_id'] = $deviceId;
    
    header('Location: index.php?page=affiliate');
    exit;
} else {
    echo "User not found";
}
