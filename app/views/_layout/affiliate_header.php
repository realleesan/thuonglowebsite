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
                <span class="badge">3</span>
            </button>
            <div class="dropdown-menu notifications-menu" id="notificationsMenu">
                <div class="dropdown-header">
                    <h6>Thông báo</h6>
                </div>
                <div class="dropdown-body">
                    <div class="notification-item">
                        <div class="notification-icon">
                            <i class="fas fa-dollar-sign text-success"></i>
                        </div>
                        <div class="notification-content">
                            <p class="notification-text">Hoa hồng mới đã được duyệt</p>
                            <span class="notification-time">5 phút trước</span>
                        </div>
                    </div>
                    <div class="notification-item">
                        <div class="notification-icon">
                            <i class="fas fa-user-plus text-info"></i>
                        </div>
                        <div class="notification-content">
                            <p class="notification-text">Khách hàng mới đăng ký</p>
                            <span class="notification-time">1 giờ trước</span>
                        </div>
                    </div>
                    <div class="notification-item">
                        <div class="notification-icon">
                            <i class="fas fa-chart-line text-warning"></i>
                        </div>
                        <div class="notification-content">
                            <p class="notification-text">Doanh số tháng này tăng 15%</p>
                            <span class="notification-time">2 giờ trước</span>
                        </div>
                    </div>
                </div>
                <div class="dropdown-footer">
                    <a href="?page=affiliate&module=notifications" class="view-all-btn">Xem tất cả</a>
                </div>
            </div>
        </div>

        <!-- User Menu -->
        <div class="header-item user-dropdown">
            <button type="button" class="header-btn user-btn" id="userMenuBtn">
                <img src="<?php echo base_url(); ?>assets/images/home/home-banner-final.png" 
                     alt="<?php echo htmlspecialchars($affiliateInfo['name'] ?? 'User'); ?>" 
                     class="user-avatar">
                <span class="user-name"><?php echo htmlspecialchars($affiliateInfo['name'] ?? 'Đại lý'); ?></span>
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="dropdown-menu user-menu" id="userMenu">
                <div class="dropdown-header">
                    <div class="user-info">
                        <img src="<?php echo base_url(); ?>assets/images/home/home-banner-final.png" 
                             alt="<?php echo htmlspecialchars($affiliateInfo['name'] ?? 'User'); ?>" 
                             class="user-avatar-large">
                        <div class="user-details">
                            <h6 class="user-name"><?php echo htmlspecialchars($affiliateInfo['name'] ?? 'Đại lý'); ?></h6>
                            <p class="user-email"><?php echo htmlspecialchars($affiliateInfo['email'] ?? 'affiliate@thuonglo.com'); ?></p>
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
