<?php
/**
 * Test file to debug device approval flow
 * This simulates what happens when:
 * 1. Device A approves Device B
 * 2. Device B tries to login
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/app/models/DeviceAccessModel.php';
require_once __DIR__ . '/app/services/DeviceAccessService.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h2>=== Device Approval Flow Debug ===</h2>";

$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    echo "<p style='color:red'>⚠️ User not logged in. Please login first.</p>";
    exit;
}

$model = new DeviceAccessModel();

echo "<h3>Current User ID: $userId</h3>";

echo "<h3>All Device Sessions (including rejected):</h3>";

// Get ALL device sessions
$allDevices = $model->query(
    "SELECT * FROM device_sessions WHERE user_id = :user_id ORDER BY created_at DESC",
    ['user_id' => $userId]
);

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Session ID</th><th>Browser</th><th>OS</th><th>IP Address</th><th>Status</th><th>is_current</th><th>Created</th><th>Last Activity</th></tr>";
foreach ($allDevices as $device) {
    echo "<tr>";
    echo "<td>" . $device['id'] . "</td>";
    echo "<td>" . ($device['session_id'] ?? 'NULL') . "</td>";
    echo "<td>" . ($device['browser'] ?? 'Unknown') . "</td>";
    echo "<td>" . ($device['os'] ?? 'Unknown') . "</td>";
    echo "<td>" . ($device['ip_address'] ?? 'Unknown') . "</td>";
    echo "<td style='color:" . ($device['status'] === 'active' ? 'green' : ($device['status'] === 'rejected' ? 'red' : 'orange')) . "'>" . $device['status'] . "</td>";
    echo "<td>" . $device['is_current'] . "</td>";
    echo "<td>" . $device['created_at'] . "</td>";
    echo "<td>" . $device['last_activity'] . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>Check Device Status for Current Session:</h3>";

$currentSessionId = session_id();
echo "<p>Current Session ID: $currentSessionId</p>";

// Check if current session is in device table
$currentDevice = $model->findBySessionId($currentSessionId);
if ($currentDevice) {
    echo "<p>Device found: ID=" . $currentDevice['id'] . ", Status=" . $currentDevice['status'] . "</p>";
} else {
    echo "<p style='color:red'>⚠️ No device found for current session!</p>";
}

// Check session variables
echo "<h3>Session Variables:</h3>";
echo "<pre>";
echo "user_id: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";
echo "device_id: " . ($_SESSION['device_id'] ?? 'NOT SET') . "\n";
echo "is_authenticated: " . ($_SESSION['is_authenticated'] ?? 'NOT SET') . "\n";
echo "</pre>";

// Validate device status
echo "<h3>Device Status Validation:</h3>";
if (isset($_SESSION['device_id'])) {
    $deviceId = $_SESSION['device_id'];
    $device = $model->find($deviceId);
    if ($device) {
        echo "<p>Device ID $deviceId status: <strong>" . $device['status'] . "</strong></p>";
        if ($device['status'] === 'active') {
            echo "<p style='color:green'>✓ Device is active - should allow login</p>";
        } else {
            echo "<p style='color:red'>✗ Device is NOT active - will cause login failure!</p>";
        }
    } else {
        echo "<p style='color:red'>✗ Device ID $deviceId not found in database!</p>";
    }
} else {
    echo "<p style='color:orange'>⚠️ No device_id in session</p>";
}

echo "<h3>Test checkDeviceOnLogin:</h3>";
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
