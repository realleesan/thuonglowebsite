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
