<?php
// Admin Sidebar Component
// Requires: User session data and current page info

// Get current user info (assuming it's stored in session)
$current_user = [
    'name' => $_SESSION['full_name'] ?? 'Admin User',
    'role' => $_SESSION['role'] === 'admin' ? 'Quản trị viên' : 'Người dùng',
    'avatar' => null
];

// Get current page for active menu highlighting
$current_page = $_GET['page'] ?? '';
$current_module = $_GET['module'] ?? 'dashboard';

// Menu items configuration
$menu_items = [
    [
        'href' => '?page=admin&module=dashboard',
        'icon' => 'fas fa-chart-line',
        'text' => 'Dashboard',
        'key' => 'dashboard'
    ],
    [
        'href' => '?page=admin&module=products',
        'icon' => 'fas fa-box',
        'text' => 'Sản phẩm',
        'key' => 'products'
    ],
    [
        'href' => '?page=admin&module=categories',
        'icon' => 'fas fa-folder',
        'text' => 'Danh mục',
        'key' => 'categories'
    ],
    [
        'href' => '?page=admin&module=orders',
        'icon' => 'fas fa-shopping-cart',
        'text' => 'Đơn hàng',
        'key' => 'orders'
    ],
    [
        'href' => '?page=admin&module=news',
        'icon' => 'fas fa-newspaper',
        'text' => 'Tin tức',
        'key' => 'news'
    ],
    [
        'href' => '?page=admin&module=events',
        'icon' => 'fas fa-calendar-alt',
        'text' => 'Sự kiện',
        'key' => 'events'
    ],
    [
        'href' => '?page=admin&module=users',
        'icon' => 'fas fa-users',
        'text' => 'Người dùng',
        'key' => 'users'
    ],
    [
        'href' => '?page=admin&module=contact',
        'icon' => 'fas fa-envelope',
        'text' => 'Liên hệ',
        'key' => 'contact'
    ]
];

// Function to check if menu item is active
function isActiveMenu($menuKey, $currentModule) {
    return $menuKey === $currentModule;
}

// Function to get user initials for avatar
function getUserInitials($name) {
    $words = explode(' ', trim($name));
    if (count($words) >= 2) {
        return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
    }
    return strtoupper(substr($name, 0, 2));
}
?>

<aside class="admin-sidebar" id="adminSidebar">
    <!-- Sidebar Header -->
    <div class="sidebar-header">
        <h3>ThuongLo Admin</h3>
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle Sidebar">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <!-- User Info Section -->
    <div class="sidebar-user">
        <div class="sidebar-user-avatar">
            <?php echo getUserInitials($current_user['name']); ?>
        </div>
        <div class="sidebar-user-info">
            <h4><?php echo htmlspecialchars($current_user['name']); ?></h4>
            <p><?php echo htmlspecialchars($current_user['role']); ?></p>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="sidebar-nav">
        <ul class="nav-menu">
            <?php foreach ($menu_items as $item): ?>
                <li>
                    <a href="<?php echo htmlspecialchars($item['href']); ?>" 
                       class="<?php echo isActiveMenu($item['key'], $current_module) ? 'active' : ''; ?>"
                       data-menu="<?php echo $item['key']; ?>">
                        <span class="nav-menu-icon">
                            <i class="<?php echo $item['icon']; ?>"></i>
                        </span>
                        <span class="nav-menu-text"><?php echo htmlspecialchars($item['text']); ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <a href="?page=admin&module=settings" title="Cài đặt">
            <span class="sidebar-footer-icon">
                <i class="fas fa-cog"></i>
            </span>
            Cài đặt
        </a>
        <a href="?page=auth&action=logout" title="Đăng xuất">
            <span class="sidebar-footer-icon">
                <i class="fas fa-sign-out-alt"></i>
            </span>
            Đăng xuất
        </a>
        <a href="/" title="Về trang chủ" target="_blank">
            <span class="sidebar-footer-icon">
                <i class="fas fa-home"></i>
            </span>
            Trang chủ
        </a>
    </div>
</aside>

<!-- Mobile Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<script>
// Pass PHP data to JavaScript
window.adminSidebarData = {
    currentModule: '<?php echo $current_module; ?>',
    currentUser: <?php echo json_encode($current_user); ?>,
    menuItems: <?php echo json_encode($menu_items); ?>
};
</script>