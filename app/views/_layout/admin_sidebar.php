<?php
// Lấy thông tin trang hiện tại
$current_page = $_GET['page'] ?? 'admin';
$current_module = $_GET['module'] ?? 'dashboard';
$current_action = $_GET['action'] ?? 'index';
?>

<div class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <div class="logo">
            <img src="<?php echo icon_url('logo/logo.svg'); ?>" alt="ThuongLo Admin" class="logo-img logo-full">
            <img src="<?php echo icon_url('logo/logo_mini.svg'); ?>" alt="ThuongLo Admin" class="logo-img logo-mini">
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <ul class="nav-list">
            <?php 
            try {
                require_once __DIR__ . '/../../services/AdminService.php';
                $adminService = new AdminService();
                $menuResponse = $adminService->getAdminMenus();
                $menus = $menuResponse['data'] ?? [];
            } catch (Exception $e) {
                error_log('Admin sidebar error: ' . $e->getMessage());
                $menus = [];
            }
            
            // Fallback menus if no menus from service
            if (empty($menus)) {
                $menus = [
                    ['name' => 'Dashboard', 'url' => '?page=admin&module=dashboard', 'icon' => 'fas fa-home'],
                    ['name' => 'Sản phẩm', 'url' => '?page=admin&module=products', 'icon' => 'fas fa-box'],
                    ['name' => 'Đơn hàng', 'url' => '?page=admin&module=orders', 'icon' => 'fas fa-shopping-cart'],
                    ['name' => 'Người dùng', 'url' => '?page=admin&module=users', 'icon' => 'fas fa-users'],
                    ['name' => 'Danh mục', 'url' => '?page=admin&module=categories', 'icon' => 'fas fa-tags'],
                    ['name' => 'Tin tức', 'url' => '?page=admin&module=news', 'icon' => 'fas fa-newspaper'],
                    ['name' => 'Sự kiện', 'url' => '?page=admin&module=events', 'icon' => 'fas fa-calendar'],
                    ['name' => 'Liên hệ', 'url' => '?page=admin&module=contact', 'icon' => 'fas fa-envelope'],
                    ['name' => 'Đại lý', 'url' => '?page=admin&module=affiliates', 'icon' => 'fas fa-store'],
                    ['name' => 'Doanh thu', 'url' => '?page=admin&module=revenue', 'icon' => 'fas fa-chart-line'],
                ];
            }
            
            foreach ($menus as $menu): 
                // Xử lý active state dựa trên URL
                $activeClass = '';
                if (strpos($menu['url'], "module=$current_module") !== false) {
                    $activeClass = 'active';
                }
            ?>
            <li class="nav-item <?php echo $activeClass; ?>">
                <a href="<?php echo htmlspecialchars($menu['url']); ?>" class="nav-link">
                    <i class="<?php echo htmlspecialchars($menu['icon']); ?> nav-icon"></i>
                    <span class="nav-text"><?php echo htmlspecialchars($menu['name']); ?></span>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </nav>
</div>