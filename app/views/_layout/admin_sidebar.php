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
                    ['name' => 'Sản phẩm', 'url' => '?page=admin&module=products', 'icon' => 'fas fa-box', 'submenus' => [
                        ['name' => 'Danh sách', 'url' => '?page=admin&module=products', 'icon' => 'fas fa-list'],
                        ['name' => 'Dữ liệu', 'url' => '?page=admin&module=products&action=data', 'icon' => 'fas fa-database']
                    ]],
                    ['name' => 'Đơn hàng', 'url' => '?page=admin&module=orders', 'icon' => 'fas fa-shopping-cart'],
                    ['name' => 'Người dùng', 'url' => '?page=admin&module=users', 'icon' => 'fas fa-users'],
                    ['name' => 'Danh mục', 'url' => '?page=admin&module=categories', 'icon' => 'fas fa-tags'],
                    ['name' => 'Tin tức', 'url' => '?page=admin&module=news', 'icon' => 'fas fa-newspaper'],
                    ['name' => 'Liên hệ', 'url' => '?page=admin&module=contact', 'icon' => 'fas fa-envelope'],
                    ['name' => 'Đại lý', 'url' => '?page=admin&module=affiliates', 'icon' => 'fas fa-store'],
                    ['name' => 'Doanh thu', 'url' => '?page=admin&module=revenue', 'icon' => 'fas fa-chart-line'],
                ];
            } else {
                // Database menus - convert products to have submenu
                $updatedMenus = [];
                foreach ($menus as $menu) {
                    if (isset($menu['url']) && strpos($menu['url'], 'module=products') !== false && strpos($menu['url'], 'action=data') === false) {
                        // This is products menu - add submenu
                        $menu['submenus'] = [
                            ['name' => 'Danh sách', 'url' => '?page=admin&module=products', 'icon' => 'fas fa-list'],
                            ['name' => 'Dữ liệu', 'url' => '?page=admin&module=products&action=data', 'icon' => 'fas fa-database']
                        ];
                    }
                    $updatedMenus[] = $menu;
                }
                $menus = $updatedMenus;
            }
            
            // Check if we're on products module
            $isProductsModule = ($current_module === 'products');
            $isDataAction = ($current_action === 'data');
            
            foreach ($menus as $menu): 
                // Check if this menu item is active
                $isActive = false;
                if (isset($menu['submenus'])) {
                    // For parent menu, check if any submenu is active
                    foreach ($menu['submenus'] as $submenu) {
                        if (strpos($submenu['url'], "module=$current_module") !== false) {
                            if ($current_action === 'data' && strpos($submenu['url'], 'action=data') !== false) {
                                $isActive = true;
                            } elseif ($current_action !== 'data') {
                                $isActive = true;
                            }
                        }
                    }
                } else {
                    if (strpos($menu['url'], "module=$current_module") !== false) {
                        if ($current_module !== 'products' || ($current_action !== 'data')) {
                            $isActive = true;
                        }
                    }
                }
            ?>
            <li class="nav-item <?php echo $isActive ? 'active' : ''; ?> <?php echo isset($menu['submenus']) ? 'has-submenu' : ''; ?>">
                <a href="<?php echo htmlspecialchars($menu['url']); ?>" class="nav-link">
                    <i class="<?php echo htmlspecialchars($menu['icon']); ?> nav-icon"></i>
                    <span class="nav-text"><?php echo htmlspecialchars($menu['name']); ?></span>
                    <?php if (isset($menu['submenus'])): ?>
                    <i class="fas fa-chevron-down submenu-arrow"></i>
                    <?php endif; ?>
                </a>
                <?php if (isset($menu['submenus'])): ?>
                <ul class="submenu">
                    <?php foreach ($menu['submenus'] as $submenu): 
                        $subActive = false;
                        if (strpos($submenu['url'], "module=$current_module") !== false) {
                            if ($current_action === 'data' && strpos($submenu['url'], 'action=data') !== false) {
                                $subActive = true;
                            } elseif ($current_action !== 'data' && strpos($submenu['url'], 'action=data') === false) {
                                $subActive = true;
                            }
                        }
                    ?>
                    <li class="submenu-item <?php echo $subActive ? 'active' : ''; ?>">
                        <a href="<?php echo htmlspecialchars($submenu['url']); ?>">
                            <i class="<?php echo htmlspecialchars($submenu['icon']); ?>"></i>
                            <span><?php echo htmlspecialchars($submenu['name']); ?></span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ul>
    </nav>
</div>

<style>
/* Submenu styles */
.nav-item.has-submenu {
    position: relative;
}

.submenu-arrow {
    margin-left: auto;
    font-size: 10px;
    transition: transform 0.3s;
}

.nav-item.has-submenu.active .submenu-arrow {
    transform: rotate(180deg);
}

.submenu {
    display: none;
    list-style: none;
    padding: 0;
    margin: 0;
    background: #ffffff;
    padding: 8px 0;
    min-width: 180px;
}

.nav-item.has-submenu.active .submenu {
    display: block;
}

.submenu-item {
    padding: 0;
}

.submenu-item a {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 20px 10px 20px;
    color: #333;
    text-decoration: none;
    font-size: 14px;
    transition: all 0.2s;
    border: none;
    background: transparent;
    width: 100%;
    text-align: left;
    cursor: pointer;
}

.submenu-item a:hover {
    background: #f0f4f8;
    color: #007bff;
}

.submenu-item.active a {
    background: #e7f3ff;
    color: #007bff;
    font-weight: 500;
}

.submenu-item i {
    font-size: 14px;
    width: 16px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle submenu on click instead of hover
    const hasSubmenu = document.querySelectorAll('.nav-item.has-submenu');
    hasSubmenu.forEach(item => {
        const link = item.querySelector('.nav-link');
        if (link) {
            link.addEventListener('click', function(e) {
                // Only toggle if clicking on the parent menu (not submenu items)
                if (!e.target.closest('.submenu')) {
                    e.preventDefault();
                    // Close other submenus
                    hasSubmenu.forEach(other => {
                        if (other !== item) {
                            other.classList.remove('active');
                        }
                    });
                    // Toggle current submenu
                    item.classList.toggle('active');
                }
            });
        }
    });
    
    // Close submenu when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.nav-item.has-submenu')) {
            hasSubmenu.forEach(item => {
                item.classList.remove('active');
            });
        }
    });
});
</script>
