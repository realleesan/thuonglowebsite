<?php
// User Sidebar Navigation
$current_module = $_GET['module'] ?? 'dashboard';
?>

<div class="user-sidebar" id="userSidebar">
    <!-- User Profile Section -->
    <div class="user-profile-section">
        <div class="user-avatar">
            <img src="<?php echo img_url('home/home-banner-final.png'); ?>" alt="User Avatar" id="userAvatarImg">
        </div>
        <div class="user-info">
            <h4 class="user-name" id="userName">Nguyễn Văn An</h4>
            <span class="user-level" id="userLevel">VIP Member</span>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="sidebar-nav">
        <ul class="nav-list">
            <li class="nav-item <?php echo $current_module === 'dashboard' ? 'active' : ''; ?>">
                <a href="?page=users&module=dashboard" class="nav-link">
                    <i class="nav-icon fas fa-tachometer-alt"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            
            <li class="nav-item <?php echo $current_module === 'account' ? 'active' : ''; ?>">
                <a href="?page=users&module=account" class="nav-link">
                    <i class="nav-icon fas fa-user"></i>
                    <span class="nav-text">Tài khoản</span>
                </a>
            </li>
            
            <li class="nav-item <?php echo $current_module === 'orders' ? 'active' : ''; ?>">
                <a href="?page=users&module=orders" class="nav-link">
                    <i class="nav-icon fas fa-shopping-bag"></i>
                    <span class="nav-text">Đơn hàng</span>
                </a>
            </li>
            
            <li class="nav-item <?php echo $current_module === 'cart' ? 'active' : ''; ?>">
                <a href="?page=users&module=cart" class="nav-link">
                    <i class="nav-icon fas fa-shopping-cart"></i>
                    <span class="nav-text">Giỏ hàng</span>
                    <span class="cart-count" id="cartCount">2</span>
                </a>
            </li>
            
            <li class="nav-item <?php echo $current_module === 'wishlist' ? 'active' : ''; ?>">
                <a href="?page=users&module=wishlist" class="nav-link">
                    <i class="nav-icon fas fa-heart"></i>
                    <span class="nav-text">Yêu thích</span>
                    <span class="wishlist-count" id="wishlistCount">2</span>
                </a>
            </li>
            
            <li class="nav-divider"></li>
            
            <li class="nav-item">
                <a href="?page=auth&action=logout" class="nav-link logout-link">
                    <i class="nav-icon fas fa-sign-out-alt"></i>
                    <span class="nav-text">Đăng xuất</span>
                </a>
            </li>
        </ul>
    </nav>
</div>

<!-- Mobile Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>