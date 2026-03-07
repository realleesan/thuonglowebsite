<?php
/**
 * CLI Script - Xóa tất cả phiên đăng nhập
 * 
 * Chạy từ cronjob mỗi ngày lúc 6h sáng
 * Usage: php clear_all_sessions.php
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/app/models/DeviceAccessModel.php';

$model = new DeviceAccessModel();

echo "[" . date('Y-m-d H:i:s') . "] Bắt đầu xóa tất cả phiên đăng nhập...\n";

// Lấy thông tin trước khi xóa
$allDevices = $model->query("SELECT COUNT(*) as count FROM device_sessions WHERE status = 'active'");
$count = $allDevices[0]['count'] ?? 0;

echo "[" . date('Y-m-d H:i:s') . "] Tìm thấy $count phiên đăng nhập active\n";

// Xóa tất cả phiên đăng nhập (chỉ xóa active và pending, giữ lại rejected/inactive)
$model->query("DELETE FROM device_sessions WHERE status IN ('active', 'pending')");
$model->query("DELETE FROM device_verification_codes");

echo "[" . date('Y-m-d H:i:s') . "] Đã xóa $count phiên đăng nhập của tất cả users\n";
echo "[" . date('Y-m-d H:i:s') . "] Hoàn tất!\n";
