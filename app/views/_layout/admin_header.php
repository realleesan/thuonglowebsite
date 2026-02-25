<?php
// Lấy thông tin user hiện tại từ session
$headerUserName  = $_SESSION['user_name']  ?? $_SESSION['user_email'] ?? 'Admin';
$headerUserEmail = $_SESSION['user_email'] ?? '';
$headerUserRole  = $_SESSION['user_role']  ?? 'admin';
?>

<header class="admin-header">
    <div class="header-left">
        <a href="<?php echo base_url(); ?>" class="home-btn" title="Về trang chủ website">
            <i class="fas fa-home"></i>
            <span>Trang chủ</span>
        </a>
        
        <button class="sidebar-toggle-btn" id="sidebarToggleBtn">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="search-box">
            <form class="search-form" action="" method="GET">
                <input type="hidden" name="page" value="admin">
                <input type="text" name="search" placeholder="Tìm kiếm..." class="search-input">
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
    </div>
    
    <div class="header-right">
        <!-- Notifications -->
        <div class="header-item notifications-dropdown">
            <button class="header-btn" id="notificationsBtn">
                <i class="fas fa-bell"></i>
            </button>
            <div class="dropdown-menu notifications-menu" id="notificationsMenu">
                <div class="dropdown-header">
                    <h6>Thông báo</h6>
                </div>
                <div class="dropdown-body">
                    <div class="notification-empty" style="text-align: center; padding: 20px; color: #6B7280;">
                        <i class="fas fa-bell-slash" style="font-size: 24px; margin-bottom: 8px;"></i>
                        <p style="margin: 0;">Không có thông báo nào</p>
                    </div>
                </div>
                <div class="dropdown-footer">
                    <a href="?page=admin&module=notifications" class="view-all-btn">Xem tất cả</a>
                </div>
            </div>
        </div>
        
        <!-- User Profile -->
        <div class="header-item user-dropdown">
            <button class="header-btn user-btn" id="userBtn">
                <img src="<?php echo img_url('home/home-banner-final.png'); ?>" alt="Admin" class="user-avatar">
                <span class="user-name"><?php echo htmlspecialchars($headerUserName, ENT_QUOTES, 'UTF-8'); ?></span>
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="dropdown-menu user-menu" id="userMenu">
                <div class="dropdown-header">
                    <div class="user-info">
                        <img src="<?php echo img_url('home/home-banner-final.png'); ?>" alt="Admin" class="user-avatar-large">
                        <div class="user-details">
                            <h6 class="user-name"><?php echo htmlspecialchars($headerUserName, ENT_QUOTES, 'UTF-8'); ?></h6>
                            <p class="user-email"><?php echo htmlspecialchars($headerUserEmail, ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                    </div>
                </div>
                <div class="dropdown-body">
                    <a href="?page=admin&module=profile" class="dropdown-item">
                        <i class="fas fa-user"></i>
                        <span>Hồ sơ cá nhân</span>
                    </a>
                    <a href="?page=admin&module=settings" class="dropdown-item">
                        <i class="fas fa-cog"></i>
                        <span>Cài đặt</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="?page=logout" class="dropdown-item text-danger">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Đăng xuất</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>
