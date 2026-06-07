<?php
/**
 * Affiliate Sidebar Navigation
 * Design System: Giống Admin
 */

// Get current page/module
$current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$current_module = isset($_GET['module']) ? $_GET['module'] : '';
$current_action = isset($_GET['action']) ? $_GET['action'] : '';

// Determine active menu item
$active_menu = $current_page === 'affiliate' ? ($current_module ?: 'dashboard') : '';
?>

<aside class="affiliate-sidebar" id="affiliateSidebar">
    <!-- Logo -->
    <div class="sidebar-header">
        <div class="logo">
            <a href="<?php echo base_url(); ?>?page=affiliate">
                <img src="<?php echo icon_url(get_logo('logo_affiliate_full', 'logo/logo.svg')); ?>" alt="ThuongLo" class="logo-img logo-full">
                <img src="<?php echo icon_url(get_logo('logo_affiliate_mini', 'logo/logo_mini.svg')); ?>" alt="ThuongLo" class="logo-img logo-mini">
            </a>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="sidebar-nav">
        <ul class="nav-list">
            <!-- Dashboard -->
            <li class="nav-item <?php echo ($active_menu === 'dashboard') ? 'active' : ''; ?>">
                <a href="<?php echo base_url(); ?>?page=affiliate&module=dashboard" class="nav-link">
                    <i class="nav-icon fas fa-tachometer-alt"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>

            <!-- Customers -->
            <li class="nav-item <?php echo ($active_menu === 'customers') ? 'active' : ''; ?>">
                <a href="<?php echo base_url(); ?>?page=affiliate&module=customers&action=list" class="nav-link">
                    <i class="nav-icon fas fa-users"></i>
                    <span class="nav-text">Khách hàng</span>
                </a>
            </li>

            <!-- Finance with Dropdown -->
            <li class="nav-item has-submenu <?php echo ($active_menu === 'finance') ? 'active' : ''; ?>">
                <a href="<?php echo base_url(); ?>?page=affiliate&module=finance" class="nav-link">
                    <i class="nav-icon fas fa-wallet"></i>
                    <span class="nav-text">Tài chính</span>
                    <i class="nav-arrow fas fa-chevron-down"></i>
                </a>
                <ul class="submenu">
                    <li class="submenu-item <?php echo ($active_menu === 'finance' && $current_action === 'index') ? 'active' : ''; ?>">
                        <a href="<?php echo base_url(); ?>?page=affiliate&module=finance" class="submenu-link">
                            <i class="submenu-icon fas fa-coins"></i>
                            <span class="submenu-text">Ví của tôi</span>
                        </a>
                    </li>
                    <li class="submenu-item <?php echo ($active_menu === 'finance' && $current_action === 'withdraw') ? 'active' : ''; ?>">
                        <a href="<?php echo base_url(); ?>?page=affiliate&module=finance&action=withdraw" class="submenu-link">
                            <i class="submenu-icon fas fa-money-bill-wave"></i>
                            <span class="submenu-text">Rút tiền</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Marketing -->
            <li class="nav-item <?php echo ($active_menu === 'marketing') ? 'active' : ''; ?>">
                <a href="<?php echo base_url(); ?>?page=affiliate&module=marketing" class="nav-link">
                    <i class="nav-icon fas fa-bullhorn"></i>
                    <span class="nav-text">Marketing</span>
                </a>
            </li>

            <!-- Reports -->
            <li class="nav-item <?php echo ($active_menu === 'reports') ? 'active' : ''; ?>">
                <a href="<?php echo base_url(); ?>?page=affiliate&module=reports&action=orders" class="nav-link">
                    <i class="nav-icon fas fa-chart-bar"></i>
                    <span class="nav-text">Báo cáo</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>
