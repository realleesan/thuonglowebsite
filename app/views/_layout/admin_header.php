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
                <span class="badge">3</span>
            </button>
            <div class="dropdown-menu notifications-menu" id="notificationsMenu">
                <div class="dropdown-header">
                    <h6>Thông báo</h6>
                </div>
                <div class="dropdown-body">
                    <div class="notification-item">
                        <div class="notification-icon">
                            <i class="fas fa-shopping-cart text-success"></i>
                        </div>
                        <div class="notification-content">
                            <p class="notification-text">Đơn hàng mới #1001</p>
                            <span class="notification-time">5 phút trước</span>
                        </div>
                    </div>
                    <div class="notification-item">
                        <div class="notification-icon">
                            <i class="fas fa-exclamation-triangle text-warning"></i>
                        </div>
                        <div class="notification-content">
                            <p class="notification-text">Sản phẩm sắp hết hàng</p>
                            <span class="notification-time">1 giờ trước</span>
                        </div>
                    </div>
                    <div class="notification-item">
                        <div class="notification-icon">
                            <i class="fas fa-envelope text-info"></i>
                        </div>
                        <div class="notification-content">
                            <p class="notification-text">Liên hệ mới từ khách hàng</p>
                            <span class="notification-time">2 giờ trước</span>
                        </div>
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
                <span class="user-name">Admin ThuongLo</span>
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="dropdown-menu user-menu" id="userMenu">
                <div class="dropdown-header">
                    <div class="user-info">
                        <img src="<?php echo img_url('home/home-banner-final.png'); ?>" alt="Admin" class="user-avatar-large">
                        <div class="user-details">
                            <h6 class="user-name">Admin ThuongLo</h6>
                            <p class="user-email">admin@thuonglo.com</p>
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
                    <a href="?page=auth&action=logout" class="dropdown-item text-danger">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Đăng xuất</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>