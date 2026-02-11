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
            <li class="nav-item <?php echo ($current_module == 'dashboard') ? 'active' : ''; ?>">
                <a href="?page=admin&module=dashboard" class="nav-link">
                    <i class="fas fa-tachometer-alt nav-icon"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            
            <li class="nav-item <?php echo ($current_module == 'products') ? 'active' : ''; ?>">
                <a href="?page=admin&module=products" class="nav-link">
                    <i class="fas fa-box nav-icon"></i>
                    <span class="nav-text">Sản phẩm</span>
                </a>
            </li>
            
            <li class="nav-item <?php echo ($current_module == 'categories') ? 'active' : ''; ?>">
                <a href="?page=admin&module=categories" class="nav-link">
                    <i class="fas fa-tags nav-icon"></i>
                    <span class="nav-text">Danh mục</span>
                </a>
            </li>
            
            <li class="nav-item <?php echo ($current_module == 'news') ? 'active' : ''; ?>">
                <a href="?page=admin&module=news" class="nav-link">
                    <i class="fas fa-newspaper nav-icon"></i>
                    <span class="nav-text">Tin tức</span>
                </a>
            </li>
            
            <li class="nav-item <?php echo ($current_module == 'events') ? 'active' : ''; ?>">
                <a href="?page=admin&module=events" class="nav-link">
                    <i class="fas fa-calendar nav-icon"></i>
                    <span class="nav-text">Sự kiện</span>
                </a>
            </li>
            
            <li class="nav-item <?php echo ($current_module == 'orders') ? 'active' : ''; ?>">
                <a href="?page=admin&module=orders" class="nav-link">
                    <i class="fas fa-shopping-cart nav-icon"></i>
                    <span class="nav-text">Đơn hàng</span>
                </a>
            </li>
            
            <li class="nav-item <?php echo ($current_module == 'users') ? 'active' : ''; ?>">
                <a href="?page=admin&module=users" class="nav-link">
                    <i class="fas fa-users nav-icon"></i>
                    <span class="nav-text">Người dùng</span>
                </a>
            </li>
            
            <li class="nav-item <?php echo ($current_module == 'affiliates') ? 'active' : ''; ?>">
                <a href="?page=admin&module=affiliates" class="nav-link">
                    <i class="fas fa-handshake nav-icon"></i>
                    <span class="nav-text">Đại lý</span>
                </a>
            </li>
            
            <li class="nav-item <?php echo ($current_module == 'contact') ? 'active' : ''; ?>">
                <a href="?page=admin&module=contact" class="nav-link">
                    <i class="fas fa-envelope nav-icon"></i>
                    <span class="nav-text">Liên hệ</span>
                </a>
            </li>
            
            <li class="nav-item <?php echo ($current_module == 'revenue') ? 'active' : ''; ?>">
                <a href="?page=admin&module=revenue" class="nav-link">
                    <i class="fas fa-chart-line nav-icon"></i>
                    <span class="nav-text">Doanh thu</span>
                </a>
            </li>
            
            <li class="nav-item <?php echo ($current_module == 'settings') ? 'active' : ''; ?>">
                <a href="?page=admin&module=settings" class="nav-link">
                    <i class="fas fa-cog nav-icon"></i>
                    <span class="nav-text">Cài đặt</span>
                </a>
            </li>
        </ul>
    </nav>
</div>