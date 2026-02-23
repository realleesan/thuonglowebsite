<?php
/**
 * Admin Notifications Index
 * Hiển thị danh sách tất cả thông báo hệ thống
 */

// Khởi tạo View & ServiceManager
require_once __DIR__ . '/../../../../core/view_init.php';

// Chọn service admin (được inject từ index.php)
$service = isset($currentService) ? $currentService : ($adminService ?? null);

// Xử lý hành động
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'mark_all_read') {
        $service->markAllNotificationsAsRead();
        header('Location: ?page=admin&module=notifications&status=all_read');
        exit;
    }
    if ($_GET['action'] === 'mark_read' && isset($_GET['id'])) {
        $service->markNotificationAsRead((int)$_GET['id']);
        header('Location: ?page=admin&module=notifications&status=read');
        exit;
    }
}

// Lấy dữ liệu phân trang
$page = (int)($_GET['p'] ?? 1);
$perPage = 15;
$data = $service->getAllNotifications($page, $perPage);

$notifications = $data['notifications'] ?? [];
$pagination = $data['pagination'] ?? [];
$total = $data['total'] ?? 0;

?>

<div class="notifications-page">
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-bell"></i>
                Thông Báo Hệ Thống
            </h1>
            <p class="page-description">Bạn có tất cả <?= $total ?> thông báo</p>
        </div>
        <div class="page-header-right">
            <?php if ($total > 0): ?>
                <a href="?page=admin&module=notifications&action=mark_all_read" class="btn btn-outline">
                    <i class="fas fa-check-double"></i>
                    Đánh dấu tất cả là đã đọc
                </a>
            <?php endif; ?>
            <button class="btn btn-secondary" onclick="location.reload()">
                <i class="fas fa-sync"></i>
                Làm mới
            </button>
        </div>
    </div>

    <?php if (isset($_GET['status'])): ?>
        <?php if ($_GET['status'] === 'all_read'): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                Tất cả thông báo đã được đánh dấu là đã đọc.
            </div>
        <?php elseif ($_GET['status'] === 'read'): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                Thông báo đã được đánh dấu là đã đọc.
            </div>
        <?php elseif ($_GET['status'] === 'deleted'): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                Thông báo đã được xóa thành công.
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="notifications-container card">
        <?php if (empty($notifications)): ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-bell-slash"></i>
                </div>
                <h3>Không có thông báo nào</h3>
                <p>Hệ thống hiện tại chưa có thông báo mới nào dành cho bạn.</p>
            </div>
        <?php else: ?>
            <div class="notifications-list">
                <?php foreach ($notifications as $notif): ?>
                    <div class="notification-item <?= !$notif['is_read'] ? 'unread' : '' ?>" id="notif-<?= $notif['id'] ?>">
                        <div class="notif-icon-box">
                            <div class="notif-icon">
                                <i class="<?= $notif['icon'] ?: 'fas fa-info-circle' ?>"></i>
                            </div>
                        </div>
                        <div class="notif-content">
                            <div class="notif-text">
                                <p><?= htmlspecialchars($notif['message']) ?></p>
                            </div>
                            <div class="notif-meta">
                                <span class="notif-time"><i class="far fa-clock"></i> <?= $notif['time_ago'] ?></span>
                                <span class="notif-date"><?= date('d/m/Y H:i', strtotime($notif['time'])) ?></span>
                            </div>
                        </div>
                        <div class="notif-actions">
                            <?php if ($notif['link']): ?>
                                <a href="<?= $notif['link'] ?>" class="btn-action" title="Xem chi tiết">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php if (!$notif['is_read']): ?>
                                <a href="?page=admin&module=notifications&action=mark_read&id=<?= $notif['id'] ?>" class="btn-action text-success" title="Đánh dấu đã đọc">
                                    <i class="fas fa-check"></i>
                                </a>
                            <?php endif; ?>
                            
                            <a href="?page=admin&module=notifications&action=delete&id=<?= $notif['id'] ?>" class="btn-action text-danger" title="Xóa">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($pagination['last_page'] > 1): ?>
                <div class="pagination-container">
                    <ul class="pagination">
                        <?php if ($pagination['current_page'] > 1): ?>
                            <li><a href="?page=admin&module=notifications&p=<?= $pagination['current_page'] - 1 ?>"><i class="fas fa-chevron-left"></i></a></li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $pagination['last_page']; $i++): ?>
                            <li class="<?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                                <a href="?page=admin&module=notifications&p=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
                            <li><a href="?page=admin&module=notifications&p=<?= $pagination['current_page'] + 1 ?>"><i class="fas fa-chevron-right"></i></a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.notifications-container {
    padding: 0;
    overflow: hidden;
}
.notification-item {
    display: flex;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #edf2f7;
    transition: all 0.2s;
    align-items: center;
}
.notification-item:last-child {
    border-bottom: none;
}
.notification-item:hover {
    background-color: #f8fafc;
}
.notification-item.unread {
    background-color: #f0f9ff;
    border-left: 4px solid #3b82f6;
}
.notif-icon-box {
    margin-right: 1.25rem;
}
.notif-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}
.notif-icon i {
    font-size: 1.1rem;
}
.notif-content {
    flex: 1;
}
.notif-text p {
    margin: 0;
    font-size: 0.95rem;
    color: #1e293b;
    font-weight: 500;
}
.notification-item.unread .notif-text p {
    font-weight: 600;
}
.notif-meta {
    margin-top: 0.25rem;
    display: flex;
    gap: 1rem;
    font-size: 0.8rem;
    color: #64748b;
}
.notif-actions {
    display: flex;
    gap: 0.5rem;
    opacity: 0;
    transition: opacity 0.2s;
}
.notification-item:hover .notif-actions {
    opacity: 1;
}
.btn-action {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    background: #fff;
    border: 1px solid #e2e8f0;
    color: #64748b;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-action:hover {
    background: #f1f5f9;
    color: #1e293b;
    border-color: #cbd5e1;
}
.empty-state {
    padding: 4rem 2rem;
    text-align: center;
}
.empty-icon {
    font-size: 4rem;
    color: #cbd5e1;
    margin-bottom: 1.5rem;
}
.empty-state h3 {
    font-size: 1.5rem;
    color: #1e293b;
    margin-bottom: 0.5rem;
}
.empty-state p {
    color: #64748b;
}

/* Pagination Styles */
.pagination-container {
    padding: 1.5rem;
    border-top: 1px solid #edf2f7;
    display: flex;
    justify-content: center;
}
.pagination {
    display: flex;
    list-style: none;
    padding: 0;
    gap: 0.25rem;
}
.pagination li a {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    height: 36px;
    padding: 0 0.5rem;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    color: #475569;
    text-decoration: none;
    transition: all 0.2s;
}
.pagination li.active a {
    background-color: #3b82f6;
    border-color: #3b82f6;
    color: #fff;
}
.pagination li a:hover:not(.active) {
    background-color: #f1f5f9;
    border-color: #cbd5e1;
}
</style>
