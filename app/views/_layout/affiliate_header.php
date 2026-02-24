<?php
/**
 * Affiliate Header
 * Design System: Giống Admin
 * 
 * Note: $affiliateInfo is already available from dashboard.php via $service->getDashboardData()
 * No need to initialize or call service here
 */
?>

<header class="affiliate-header">
    <!-- Left Side -->
    <div class="header-left">
        <!-- Home Button -->
        <a href="<?php echo base_url(); ?>" class="home-btn" title="Về trang chủ website">
            <i class="fas fa-home"></i>
            <span>Trang chủ</span>
        </a>
        
        <!-- Sidebar Toggle -->
        <button type="button" class="sidebar-toggle-btn" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <!-- Right Side -->
    <div class="header-right">
        <!-- Notifications -->
        <div class="header-item notifications-dropdown">
            <button type="button" class="header-btn" id="notificationsBtn">
                <i class="fas fa-bell"></i>
                <?php if (isset($unreadCount) && $unreadCount > 0): ?>
                <span class="badge"><?php echo $unreadCount > 9 ? '9+' : $unreadCount; ?></span>
                <?php endif; ?>
            </button>
            <div class="dropdown-menu notifications-menu" id="notificationsMenu">
                <div class="dropdown-header">
                    <h6>Thông báo</h6>
                </div>
                <div class="dropdown-body">
                    <?php if (!empty($notifications)): ?>
                        <?php foreach ($notifications as $notif): ?>
                        <div class="notification-item">
                            <div class="notification-icon">
                                <?php 
                                $iconClass = 'fa-info-circle text-info';
                                $type = $notif['type'] ?? 'info';
                                if ($type === 'commission' || $type === 'success') $iconClass = 'fa-dollar-sign text-success';
                                elseif ($type === 'customer' || $type === 'user') $iconClass = 'fa-user-plus text-info';
                                elseif ($type === 'order') $iconClass = 'fa-shopping-cart text-warning';
                                elseif ($type === 'error' || $type === 'danger') $iconClass = 'fa-exclamation-circle text-danger';
                                ?>
                                <i class="fas <?php echo $iconClass; ?>"></i>
                            </div>
                            <div class="notification-content">
                                <p class="notification-text"><?php echo htmlspecialchars($notif['message'] ?? $notif['title'] ?? ''); ?></p>
                                <span class="notification-time"><?php echo isset($notif['created_at']) ? timeAgo($notif['created_at']) : 'Vừa xong'; ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="notification-empty" style="text-align: center; padding: 20px; color: #6B7280;">
                            <i class="fas fa-bell-slash" style="font-size: 24px; margin-bottom: 8px;"></i>
                            <p style="margin: 0;">Không có thông báo nào</p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="dropdown-footer">
                    <a href="?page=affiliate&module=notifications" class="view-all-btn">Xem tất cả</a>
                </div>
            </div>
        </div>

        <!-- User Menu -->
        <div class="header-item user-dropdown">
            <button type="button" class="header-btn user-btn" id="userMenuBtn">
                <img src="<?php echo !empty($affiliateInfo['avatar'] ?? '') ? htmlspecialchars($affiliateInfo['avatar']) : base_url() . 'assets/images/home/home-banner-final.png'; ?>" 
                     alt="<?php echo htmlspecialchars($affiliateInfo['name'] ?? 'User'); ?>" 
                     class="user-avatar">
                <span class="user-name"><?php echo htmlspecialchars($affiliateInfo['name'] ?? 'Đại lý'); ?></span>
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="dropdown-menu user-menu" id="userMenu">
                <div class="dropdown-header">
                    <div class="user-info">
                        <img src="<?php echo !empty($affiliateInfo['avatar'] ?? '') ? htmlspecialchars($affiliateInfo['avatar']) : base_url() . 'assets/images/home/home-banner-final.png'; ?>" 
                             alt="<?php echo htmlspecialchars($affiliateInfo['name'] ?? 'User'); ?>" 
                             class="user-avatar-large">
                        <div class="user-details">
                            <h6 class="user-name"><?php echo htmlspecialchars($affiliateInfo['name'] ?? 'Đại lý'); ?></h6>
                            <p class="user-email"><?php echo htmlspecialchars($affiliateInfo['email'] ?? ''); ?></p>
                        </div>
                    </div>
                </div>
                <div class="dropdown-body">
                    <a href="?page=affiliate&module=profile" class="dropdown-item">
                        <i class="fas fa-user"></i>
                        <span>Hồ sơ cá nhân</span>
                    </a>
                    <a href="?page=affiliate&module=profile&action=settings" class="dropdown-item">
                        <i class="fas fa-cog"></i>
                        <span>Cài đặt</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="?page=auth&action=logout" class="dropdown-item text-danger">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Đăng xuất</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<?php
// Helper function for time ago
if (!function_exists('timeAgo')) {
    function timeAgo($datetime) {
        if (empty($datetime)) return 'Vừa xong';
        $timestamp = strtotime($datetime);
        $diff = time() - $timestamp;
        
        if ($diff < 60) return 'Vừa xong';
        if ($diff < 3600) return floor($diff / 60) . ' phút trước';
        if ($diff < 86400) return floor($diff / 3600) . ' giờ trước';
        if ($diff < 604800) return floor($diff / 86400) . ' ngày trước';
        
        return date('d/m/Y', $timestamp);
    }
}
?>
