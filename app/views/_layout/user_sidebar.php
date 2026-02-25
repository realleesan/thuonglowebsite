<?php
// User Sidebar Navigation - Simplified to avoid WSOD
$current_module = $_GET['module'] ?? 'dashboard';

// Get current user data from session only (no database calls)
$currentUser = null;
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    $currentUser = [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'] ?? 'User',
        'username' => $_SESSION['username'] ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'role' => $_SESSION['user_role'] ?? 'user',
        'points' => 0,
        'level' => 'Bronze'
    ];
}

// Default counts (no database calls)
$cartCount = 0;
$wishlistCount = 0;

$userName = $currentUser['name'] ?? 'Người dùng';
$userLevel = ($currentUser['level'] ?? 'Bronze') . ' Member';
?>

<div class="user-sidebar" id="userSidebar">
    <!-- User Profile Section -->
    <div class="user-profile-section">
        <div class="user-avatar">
            <img src="<?php echo img_url('home/home-banner-final.png'); ?>" alt="User Avatar" id="userAvatarImg">
        </div>
        <div class="user-info">
            <h4 class="user-name" id="userName"><?php echo htmlspecialchars($userName); ?></h4>
            <span class="user-level" id="userLevel"><?php echo htmlspecialchars($userLevel); ?></span>
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
                    <?php if ($cartCount > 0): ?>
                    <span class="cart-count" id="cartCount"><?php echo $cartCount; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            
            <li class="nav-item <?php echo $current_module === 'wishlist' ? 'active' : ''; ?>">
                <a href="?page=users&module=wishlist" class="nav-link">
                    <i class="nav-icon fas fa-heart"></i>
                    <span class="nav-text">Yêu thích</span>
                    <?php if ($wishlistCount > 0): ?>
                    <span class="wishlist-count" id="wishlistCount"><?php echo $wishlistCount; ?></span>
                    <?php endif; ?>
                </a>
            </li>

            <li class="nav-item <?php echo $current_module === 'access' ? 'active' : ''; ?>">
                <a href="?page=users&module=access" class="nav-link">
                    <i class="nav-icon fas fa-shield-alt"></i>
                    <span class="nav-text">Truy cập</span>
                </a>
            </li>
            
            <li class="nav-divider"></li>
            
            <li class="nav-item">
                <a href="?page=logout" class="nav-link logout-link">
                    <i class="nav-icon fas fa-sign-out-alt"></i>
                    <span class="nav-text">Đăng xuất</span>
                </a>
            </li>
        </ul>
    </nav>
</div>

<!-- Mobile Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>