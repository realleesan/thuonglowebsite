<?php
/**
 * Admin Notifications View
 * Xem chi tiết thông báo và đánh dấu đã đọc
 */

// Khởi tạo View & ServiceManager
require_once __DIR__ . '/../../../../core/view_init.php';

// Chọn service admin (được inject từ index.php)
$service = isset($currentService) ? $currentService : ($adminService ?? null);

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    // Tự động đánh dấu đã đọc
    $service->markNotificationAsRead($id);
    
    // Tìm link để chuyển hướng (nếu có)
    $db = $service->getModel('BaseModel')->getDb();
    $stmt = $db->prepare("SELECT link FROM admin_notifications WHERE id = ?");
    $stmt->execute([$id]);
    $notif = $stmt->fetch();
    
    if ($notif && !empty($notif['link'])) {
        header('Location: ' . $notif['link']);
        exit;
    }
}

header('Location: ?page=admin&module=notifications');
exit;
