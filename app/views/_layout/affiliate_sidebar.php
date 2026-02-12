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
                <img src="<?php echo icon_url('logo/logo.svg'); ?>" alt="ThuongLo" class="logo-img logo-full">
                <img src="<?php echo icon_url('logo/logo_mini.svg'); ?>" alt="ThuongLo" class="logo-img logo-mini">
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

            <!-- Commissions with Dropdown -->
            <li class="nav-item has-submenu <?php echo ($active_menu === 'commissions') ? 'active' : ''; ?>">
                <a href="<?php echo base_url(); ?>?page=affiliate&module=commissions" class="nav-link">
                    <i class="nav-icon fas fa-dollar-sign"></i>
                    <span class="nav-text">Hoa hồng</span>
                    <i class="nav-arrow fas fa-chevron-down"></i>
                </a>
                <ul class="submenu">
                    <li class="submenu-item <?php echo ($active_menu === 'commissions' && $current_action === 'index') ? 'active' : ''; ?>">
                        <a href="<?php echo base_url(); ?>?page=affiliate&module=commissions" class="submenu-link">
                            <i class="submenu-icon fas fa-circle"></i>
                            <span class="submenu-text">Tổng quan</span>
                        </a>
                    </li>
                    <li class="submenu-item <?php echo ($active_menu === 'commissions' && $current_action === 'history') ? 'active' : ''; ?>">
                        <a href="<?php echo base_url(); ?>?page=affiliate&module=commissions&action=history" class="submenu-link">
                            <i class="submenu-icon fas fa-circle"></i>
                            <span class="submenu-text">Lịch sử</span>
                        </a>
                    </li>
                    <li class="submenu-item <?php echo ($active_menu === 'commissions' && $current_action === 'policy') ? 'active' : ''; ?>">
                        <a href="<?php echo base_url(); ?>?page=affiliate&module=commissions&action=policy" class="submenu-link">
                            <i class="submenu-icon fas fa-circle"></i>
                            <span class="submenu-text">Chính sách</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Customers with Dropdown -->
            <li class="nav-item has-submenu <?php echo ($active_menu === 'customers') ? 'active' : ''; ?>">
                <a href="<?php echo base_url(); ?>?page=affiliate&module=customers" class="nav-link">
                    <i class="nav-icon fas fa-users"></i>
                    <span class="nav-text">Khách hàng</span>
                    <i class="nav-arrow fas fa-chevron-down"></i>
                </a>
                <ul class="submenu">
                    <li class="submenu-item <?php echo ($active_menu === 'customers' && $current_action === 'list') ? 'active' : ''; ?>">
                        <a href="<?php echo base_url(); ?>?page=affiliate&module=customers&action=list" class="submenu-link">
                            <i class="submenu-icon fas fa-circle"></i>
                            <span class="submenu-text">Danh sách</span>
                        </a>
                    </li>
                </ul>
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
                            <i class="submenu-icon fas fa-circle"></i>
                            <span class="submenu-text">Ví của tôi</span>
                        </a>
                    </li>
                    <li class="submenu-item <?php echo ($active_menu === 'finance' && $current_action === 'withdraw') ? 'active' : ''; ?>">
                        <a href="<?php echo base_url(); ?>?page=affiliate&module=finance&action=withdraw" class="submenu-link">
                            <i class="submenu-icon fas fa-circle"></i>
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

            <!-- Reports with Dropdown -->
            <li class="nav-item has-submenu <?php echo ($active_menu === 'reports') ? 'active' : ''; ?>">
                <a href="<?php echo base_url(); ?>?page=affiliate&module=reports" class="nav-link">
                    <i class="nav-icon fas fa-chart-bar"></i>
                    <span class="nav-text">Báo cáo</span>
                    <i class="nav-arrow fas fa-chevron-down"></i>
                </a>
                <ul class="submenu">
                    <li class="submenu-item <?php echo ($active_menu === 'reports' && $current_action === 'clicks') ? 'active' : ''; ?>">
                        <a href="<?php echo base_url(); ?>?page=affiliate&module=reports&action=clicks" class="submenu-link">
                            <i class="submenu-icon fas fa-circle"></i>
                            <span class="submenu-text">Clicks</span>
                        </a>
                    </li>
                    <li class="submenu-item <?php echo ($active_menu === 'reports' && $current_action === 'orders') ? 'active' : ''; ?>">
                        <a href="<?php echo base_url(); ?>?page=affiliate&module=reports&action=orders" class="submenu-link">
                            <i class="submenu-icon fas fa-circle"></i>
                            <span class="submenu-text">Đơn hàng</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Profile -->
            <li class="nav-item <?php echo ($active_menu === 'profile') ? 'active' : ''; ?>">
                <a href="<?php echo base_url(); ?>?page=affiliate&module=profile" class="nav-link">
                    <i class="nav-icon fas fa-user-circle"></i>
                    <span class="nav-text">Hồ sơ</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>
