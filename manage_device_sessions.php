<?php
/**
 * Script quản lý phiên đăng nhập thiết bị
 * 
 * Chức năng:
 * - Xem tất cả các thiết bị đang đăng nhập
 * - Xóa phiên đăng nhập của một user cụ thể
 * - Xóa tất cả phiên đăng nhập
 * 
 * Sử dụng: 
 * - Truy cập trực tiếp để xem danh sách
 * - Thêm ?action=clear&user_id=XXX để xóa phiên của user cụ thể
 * - Thêm ?action=clear_all để xóa tất cả phiên
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/app/models/DeviceAccessModel.php';

$model = new DeviceAccessModel();

echo "<!DOCTYPE html>";
echo "<html lang='vi'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Quản lý phiên đăng nhập - Device Sessions</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo "h1 { color: #333; }";
echo "h2 { color: #555; margin-top: 30px; }";
echo "table { width: 100%; border-collapse: collapse; background: white; margin-top: 10px; }";
echo "th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }";
echo "th { background: #356DF1; color: white; }";
echo "tr:hover { background: #f5f5f5; }";
echo ".btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; }";
echo ".btn-danger { background: #dc3545; color: white; }";
echo ".btn-danger:hover { background: #c82333; }";
echo ".btn-primary { background: #356DF1; color: white; }";
echo ".btn-primary:hover { background: #2851c8; }";
echo ".btn-warning { background: #ffc107; color: #333; }";
echo ".btn-warning:hover { background: #e0a800; }";
echo ".btn-secondary { background: #6c757d; color: white; }";
echo ".btn-secondary:hover { background: #5a6268; }";
echo ".alert { padding: 15px; margin: 20px 0; border-radius: 4px; }";
echo ".alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }";
echo ".alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }";
echo ".alert-info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }";
echo ".status-active { color: #28a745; font-weight: bold; }";
echo ".status-pending { color: #ffc107; font-weight: bold; }";
echo ".status-inactive { color: #6c757d; font-weight: bold; }";
echo ".search-box { margin: 20px 0; }";
echo ".search-box input { padding: 8px; width: 300px; border: 1px solid #ddd; border-radius: 4px; }";
echo ".form-group { margin: 15px 0; }";
echo ".form-group label { display: block; margin-bottom: 5px; font-weight: bold; }";
echo ".form-group input { padding: 8px; width: 200px; border: 1px solid #ddd; border-radius: 4px; }";
echo ".checkbox-wrapper { margin: 10px 0; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<h1>📱 Quản lý phiên đăng nhập thiết bị</h1>";

// Xử lý các action
$action = $_GET['action'] ?? '';
$message = '';
$messageType = '';

if ($action === 'clear_user' && isset($_GET['user_id'])) {
    $userId = (int)$_GET['user_id'];
    
    // Lấy thông tin các thiết bị trước khi xóa
    $devices = $model->query("SELECT * FROM device_sessions WHERE user_id = ?", [$userId]);
    
    // Xóa phiên
    $model->query("DELETE FROM device_sessions WHERE user_id = ?", [$userId]);
    $model->query("DELETE FROM device_verification_codes WHERE user_id = ?", [$userId]);
    
    $message = "✅ Đã xóa " . count($devices) . " phiên đăng nhập của user ID: $userId";
    $messageType = 'success';
}

if ($action === 'clear_all') {
    // Lấy thông tin trước khi xóa
    $allDevices = $model->query("SELECT COUNT(*) as count FROM device_sessions WHERE status = 'active'");
    $count = $allDevices[0]['count'] ?? 0;
    
    // Xóa tất cả phiên đăng nhập (chỉ xóa active và pending, giữ lại rejected/inactive)
    $model->query("DELETE FROM device_sessions WHERE status IN ('active', 'pending')");
    $model->query("DELETE FROM device_verification_codes");
    
    $message = "✅ Đã xóa $count phiên đăng nhập của tất cả users";
    $messageType = 'success';
}

if ($action === 'deactivate_others' && isset($_GET['user_id']) && isset($_GET['except_session_id'])) {
    $userId = (int)$_GET['user_id'];
    $exceptSessionId = $_GET['except_session_id'];
    
    // Xóa tất cả trừ session hiện tại
    $model->query("DELETE FROM device_sessions WHERE user_id = ? AND session_id != ?", [$userId, $exceptSessionId]);
    $model->query("DELETE FROM device_verification_codes WHERE user_id = ?", [$userId]);
    
    $message = "✅ Đã xóa tất cả phiên đăng nhập khác của user ID: $userId (giữ lại session hiện tại)";
    $messageType = 'success';
}

// Hiển thị thông báo
if ($message) {
    echo "<div class='alert alert-$messageType'>$message</div>";
}

// Lấy danh sách tất cả thiết bị đang hoạt động
echo "<h2>📋 Danh sách thiết bị đang đăng nhập</h2>";

$allDevices = $model->query("
    SELECT ds.*, u.name as user_name, u.email as user_email 
    FROM device_sessions ds 
    LEFT JOIN users u ON ds.user_id = u.id 
    WHERE ds.status IN ('active', 'pending')
    ORDER BY ds.user_id, ds.last_activity DESC
");

if (empty($allDevices)) {
    echo "<div class='alert alert-info'>Không có thiết bị nào đang đăng nhập.</div>";
} else {
    // Nhóm theo user
    $devicesByUser = [];
    foreach ($allDevices as $device) {
        $userId = $device['user_id'];
        if (!isset($devicesByUser[$userId])) {
            $devicesByUser[$userId] = [
                'user_name' => $device['user_name'] ?? 'Unknown',
                'user_email' => $device['user_email'] ?? 'Unknown',
                'devices' => []
            ];
        }
        $devicesByUser[$userId]['devices'][] = $device;
    }
    
    echo "<table>";
    echo "<tr>";
    echo "<th>User</th>";
    echo "<th>Thiết bị</th>";
    echo "<th>IP</th>";
    echo "<th>Trình duyệt</th>";
    echo "<th>Trạng thái</th>";
    echo "<th>Hoạt động cuối</th>";
    echo "<th>Hành động</th>";
    echo "</tr>";
    
    foreach ($devicesByUser as $userId => $userData) {
        $rowSpan = count($userData['devices']);
        $first = true;
        
        foreach ($userData['devices'] as $index => $device) {
            echo "<tr>";
            
            // Hiển thị thông tin user chỉ trong dòng đầu tiên
            if ($first) {
                echo "<td rowspan='$rowSpan'>";
                echo "<strong>" . htmlspecialchars($userData['user_name']) . "</strong><br>";
                echo "<small>" . htmlspecialchars($userData['user_email']) . "</small><br>";
                echo "<small>User ID: $userId</small>";
                echo "</td>";
                $first = false;
            }
            
            // Thông tin thiết bị
            echo "<td>";
            echo htmlspecialchars($device['device_name'] ?? 'Unknown') . "<br>";
            echo "<small>" . htmlspecialchars($device['os'] ?? '') . "</small>";
            echo "</td>";
            
            // IP
            echo "<td>" . htmlspecialchars($device['ip_address'] ?? 'Unknown') . "</td>";
            
            // Trình duyệt
            echo "<td>" . htmlspecialchars($device['browser'] ?? 'Unknown') . "</td>";
            
            // Trạng thái
            $statusClass = 'status-' . $device['status'];
            $statusText = $device['status'] === 'active' ? 'Hoạt động' : 'Chờ xác nhận';
            echo "<td class='$statusClass'>$statusText</td>";
            
            // Hoạt động cuối
            echo "<td>" . htmlspecialchars($device['last_activity'] ?? '') . "</td>";
            
            // Hành động
            echo "<td>";
            if ($index === 0 && $rowSpan > 1) {
                // Dòng đầu tiên của user - có thể xóa tất cả các phiên khác
                $currentSessionId = $device['session_id'] ?? '';
                echo "<a href='?action=deactivate_others&user_id=$userId&except_session_id=" . urlencode($currentSessionId) . "' class='btn btn-warning btn-sm' onclick=\"return confirm('Xóa tất cả phiên khác của user này? Thiết bị hiện tại sẽ được giữ lại.')\">Xóa phiên khác</a>";
            }
            echo "<a href='?action=clear_user&user_id=$userId' class='btn btn-danger btn-sm' onclick=\"return confirm('Xóa tất cả phiên của user này?')\">Xóa</a>";
            echo "</td>";
            
            echo "</tr>";
        }
    }
    echo "</table>";
}

echo "<h2>🔧 Thao tác khác</h2>";

echo "<div class='form-group'>";
echo "<a href='?action=clear_all' class='btn btn-danger' onclick=\"return confirm('CẢNH BÁO: Điều này sẽ đăng xuất TẤT CẢ người dùng khỏi tất cả thiết bị! Tiếp tục?')\">🗑️ Xóa tất cả phiên đăng nhập</a>";
echo "</div>";

echo "<div class='form-group'>";
echo "<label>Nhập User ID để xóa phiên:</label>";
echo "<form method='get' style='display:inline-block;'>";
echo "<input type='hidden' name='action' value='clear_user'>";
echo "<input type='number' name='user_id' placeholder='User ID' required min='1'>";
echo "<button type='submit' class='btn btn-danger'>Xóa phiên của user</button>";
echo "</form>";
echo "</div>";

echo "<h2>📊 Thống kê</h2>";

$stats = [];
$stats['total'] = $model->query("SELECT COUNT(*) as count FROM device_sessions")[0]['count'] ?? 0;
$stats['active'] = $model->query("SELECT COUNT(*) as count FROM device_sessions WHERE status = 'active'")[0]['count'] ?? 0;
$stats['pending'] = $model->query("SELECT COUNT(*) as count FROM device_sessions WHERE status = 'pending'")[0]['count'] ?? 0;
$stats['inactive'] = $model->query("SELECT COUNT(*) as count FROM device_sessions WHERE status = 'inactive'")[0]['count'] ?? 0;

echo "<table>";
echo "<tr><th>Trạng thái</th><th>Số lượng</th></tr>";
echo "<tr><td>🔵 Tổng số thiết bị</td><td><strong>{$stats['total']}</strong></td></tr>";
echo "<tr><td>🟢 Đang hoạt động</td><td><strong class='status-active'>{$stats['active']}</strong></td></tr>";
echo "<tr><td>🟡 Chờ xác nhận</td><td><strong class='status-pending'>{$stats['pending']}</strong></td></tr>";
echo "<tr><td>⚫ Không hoạt động</td><td><strong class='status-inactive'>{$stats['inactive']}</strong></td></tr>";
echo "</table>";

// Thông tin người dùng hiện tại (nếu đang đăng nhập)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    echo "<h2>👤 Thông tin phiên hiện tại</h2>";
    echo "<p><strong>User ID:</strong> " . $_SESSION['user_id'] . "</p>";
    echo "<p><strong>Tên:</strong> " . ($_SESSION['user_name'] ?? 'N/A') . "</p>";
    echo "<p><strong>Email:</strong> " . ($_SESSION['user_email'] ?? 'N/A') . "</p>";
    echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
    echo "<p><a href='?action=clear_user&user_id=" . $_SESSION['user_id'] . "' class='btn btn-danger' onclick=\"return confirm('Xóa tất cả phiên của bạn? Bạn sẽ bị đăng xuất.')\">Đăng xuất khỏi tất cả thiết bị</a></p>";
} else {
    echo "<div class='alert alert-info'>Bạn chưa đăng nhập.</div>";
}

echo "<p style='margin-top: 30px; color: #666;'>";
echo "<small>Trang quản lý phiên đăng nhập | ";
echo "<a href='test_device_auth.php'>Test Device Auth</a> | ";
echo "<a href='?page=home'>Về trang chủ</a>";
echo "</small>";
echo "</p>";

echo "</body>";
echo "</html>";
