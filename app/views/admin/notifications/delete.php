<?php
/**
 * Admin Notifications Delete
 * Xử lý xóa thông báo hệ thống
 */

// Khởi tạo View & ServiceManager
require_once __DIR__ . '/../../../../core/view_init.php';

// Chọn service admin (được inject từ index.php)
$service = isset($currentService) ? $currentService : ($adminService ?? null);

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    if ($service->deleteNotification($id)) {
        header('Location: ?page=admin&module=notifications&status=deleted');
    } else {
        header('Location: ?page=admin&module=notifications&error=delete_failed');
    }
} else {
    header('Location: ?page=admin&module=notifications');
}
exit;
