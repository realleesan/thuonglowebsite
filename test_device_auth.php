<?php
/**
 * Test file to debug device authentication logic
 * Run this from browser or CLI to trace the authentication flow
 * 
 * Add ?clear=1 to URL to clear all device sessions first
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/app/models/DeviceAccessModel.php';
require_once __DIR__ . '/app/services/DeviceAccessService.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h2>=== Device Authentication Debug ===</h2>";

// Check if we need to clear device sessions
if (isset($_GET['clear'])) {
    $model = new DeviceAccessModel();
    $userId = $_SESSION['user_id'] ?? null;
    if ($userId) {
        $model->query("DELETE FROM device_sessions WHERE user_id = :user_id", ['user_id' => $userId]);
        $model->query("DELETE FROM device_verification_codes WHERE user_id = :user_id", ['user_id' => $userId]);
        echo "<p style='color:red'>⚠️ All device sessions cleared for user $userId</p>";
    }
    echo "<p><a href='test_device_auth.php'>Click here to test again</a></p>";
    exit;
}

// Get current session info
$sessionId = session_id();
$userId = $_SESSION['user_id'] ?? null;

echo "<h3>Current Session Info:</h3>";
echo "<pre>";
echo "Session ID: " . $sessionId . "\n";
echo "User ID: " . ($userId ?? 'NOT LOGGED IN') . "\n";
echo "User Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown') . "\n";
echo "</pre>";

if (!$userId) {
    echo "<p style='color:red'>⚠️ User not logged in. Please login first.</p>";
    echo "<p><a href='?clear=1'>Clear sessions and re-login</a></p>";
    exit;
}

echo "<p><a href='?clear=1'>[Clear All Device Sessions]</a></p>";

echo "<h3>Device Info Parsing:</h3>";
$model = new DeviceAccessModel();
$ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
$ip = $model->getClientIP();
$deviceInfo = $model->parseUserAgent($ua);

echo "<pre>";
echo "IP: " . $ip . "\n";
echo "Device Name: " . ($deviceInfo['device_name'] ?? 'Unknown') . "\n";
echo "Device Type: " . ($deviceInfo['device_type'] ?? 'Unknown') . "\n";
echo "Browser: " . ($deviceInfo['browser'] ?? 'Unknown') . "\n";
echo "OS: " . ($deviceInfo['os'] ?? 'Unknown') . "\n";
echo "</pre>";

echo "<h3>Device Sessions in Database:</h3>";

// Get all device sessions for this user
$activeDevices = $model->getActiveDevices($userId);
$pendingDevices = $model->getPendingDevices($userId);

echo "<h4>Active Devices (" . count($activeDevices) . "):</h4>";
if (empty($activeDevices)) {
    echo "<p>No active devices</p>";
} else {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Session ID</th><th>Browser</th><th>OS</th><th>IP Address</th><th>Status</th><th>Last Activity</th></tr>";
    foreach ($activeDevices as $device) {
        echo "<tr>";
        echo "<td>" . $device['id'] . "</td>";
        echo "<td>" . ($device['session_id'] ?? 'NULL') . "</td>";
        echo "<td>" . ($device['browser'] ?? 'Unknown') . "</td>";
        echo "<td>" . ($device['os'] ?? 'Unknown') . "</td>";
        echo "<td>" . ($device['ip_address'] ?? 'Unknown') . "</td>";
        echo "<td>" . $device['status'] . "</td>";
        echo "<td>" . $device['last_activity'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<h4>Pending Devices (" . count($pendingDevices) . "):</h4>";
if (empty($pendingDevices)) {
    echo "<p>No pending devices</p>";
} else {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Session ID</th><th>Browser</th><th>OS</th><th>IP Address</th><th>Status</th><th>Created At</th></tr>";
    foreach ($pendingDevices as $device) {
        echo "<tr>";
        echo "<td>" . $device['id'] . "</td>";
        echo "<td>" . ($device['session_id'] ?? 'NULL') . "</td>";
        echo "<td>" . ($device['browser'] ?? 'Unknown') . "</td>";
        echo "<td>" . ($device['os'] ?? 'Unknown') . "</td>";
        echo "<td>" . ($device['ip_address'] ?? 'Unknown') . "</td>";
        echo "<td>" . $device['status'] . "</td>";
        echo "<td>" . $device['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<h3>Authentication Check Flow:</h3>";

// Step 1: Check by session_id
echo "<h4>Step 1: Find by session_id (session_id = $sessionId)</h4>";
$existingBySession = $model->findByUserAndSession($userId, $sessionId);
if ($existingBySession) {
    echo "<p style='color:green'>✓ Found device by session_id - Device ID: " . $existingBySession['id'] . ", Status: " . $existingBySession['status'] . "</p>";
} else {
    echo "<p style='color:orange'>✗ NOT found by session_id</p>";
}

$activeCount = $model->getActiveDeviceCount($userId);
echo "<p>Active device count: $activeCount</p>";

if ($activeCount === 0) {
    echo "<p style='color:green'>→ Would allow login (first device)</p>";
} else {
    // Step 2: Check by IP + Browser + OS
    echo "<h4>Step 2: Find by IP + Browser + OS</h4>";
    $existingByIPDevice = $model->findByIPAndDevice($userId, $ip, $deviceInfo['browser'], $deviceInfo['os']);
    if ($existingByIPDevice) {
        echo "<p style='color:green'>✓ Found by IP+Browser+OS - Device ID: " . $existingByIPDevice['id'] . ", Status: " . $existingByIPDevice['status'] . "</p>";
    } else {
        echo "<p style='color:orange'>✗ NOT found by IP+Browser+OS</p>";
    }
    
    // Step 3: Check IP only
    echo "<h4>Step 3: Find by IP only (ACTIVE devices)</h4>";
    $foundByIP = false;
    foreach ($activeDevices as $device) {
        if ($device['ip_address'] === $ip) {
            echo "<p style='color:orange'>⚠️ Found active device with same IP - Device ID: " . $device['id'] . " (BUT this should NOT auto-activate for different browser!)</p>";
            $foundByIP = true;
            break;
        }
    }
    if (!$foundByIP) {
        echo "<p style='color:green'>✓ No active device with same IP</p>";
    }
    
    // Check pending devices
    echo "<h4>Step 4: Find by IP only (PENDING devices)</h4>";
    $foundPendingByIP = false;
    foreach ($pendingDevices as $device) {
        if ($device['ip_address'] === $ip) {
            echo "<p style='color:green'>✓ Found pending device with same IP + Browser + OS - Device ID: " . $device['id'] . "</p>";
            $foundPendingByIP = true;
            break;
        }
    }
    if (!$foundPendingByIP) {
        echo "<p style='color:green'>✓ No pending device with same IP + Browser + OS</p>";
    }
}

echo "<h3>Final Result:</h3>";
$service = new DeviceAccessService();
$result = $service->checkDeviceOnLogin($userId);

echo "<pre>";
print_r($result);
echo "</pre>";

if ($result['requires_verification']) {
    echo "<p style='color:red; font-size:20px'>⚠️ VERIFICATION REQUIRED</p>";
} else {
    echo "<p style='color:green; font-size:20px'>✓ NO VERIFICATION NEEDED</p>";
}
?>
