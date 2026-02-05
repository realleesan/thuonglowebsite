<?php
// Lấy thông tin trang hiện tại
$current_page = $_GET['page'] ?? 'admin';
$current_module = $_GET['module'] ?? 'dashboard';
$current_action = $_GET['action'] ?? 'index';

// Định nghĩa breadcrumb cho từng module
$breadcrumbs = [
    'dashboard' => [
        'title' => 'Dashboard',
        'icon' => 'fas fa-tachometer-alt'
    ],
    'products' => [
        'title' => 'Sản phẩm',
        'icon' => 'fas fa-box'
    ],
    'categories' => [
        'title' => 'Danh mục',
        'icon' => 'fas fa-tags'
    ],
    'news' => [
        'title' => 'Tin tức',
        'icon' => 'fas fa-newspaper'
    ],
    'events' => [
        'title' => 'Sự kiện',
        'icon' => 'fas fa-calendar'
    ],
    'orders' => [
        'title' => 'Đơn hàng',
        'icon' => 'fas fa-shopping-cart'
    ],
    'users' => [
        'title' => 'Người dùng',
        'icon' => 'fas fa-users'
    ],
    'affiliates' => [
        'title' => 'Đại lý',
        'icon' => 'fas fa-handshake'
    ],
    'contact' => [
        'title' => 'Liên hệ',
        'icon' => 'fas fa-envelope'
    ],
    'revenue' => [
        'title' => 'Doanh thu',
        'icon' => 'fas fa-chart-line'
    ],
    'settings' => [
        'title' => 'Cài đặt',
        'icon' => 'fas fa-cog'
    ]
];

// Định nghĩa action titles
$action_titles = [
    'index' => 'Danh sách',
    'add' => 'Thêm mới',
    'edit' => 'Chỉnh sửa',
    'view' => 'Xem chi tiết',
    'delete' => 'Xóa'
];

$current_breadcrumb = $breadcrumbs[$current_module] ?? $breadcrumbs['dashboard'];
$action_title = $action_titles[$current_action] ?? '';
?>

<div class="admin-breadcrumb">
    <div class="breadcrumb-content">
        <div class="breadcrumb-left">
            <nav class="breadcrumb-nav">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="?page=admin&module=dashboard" class="breadcrumb-link">
                            <i class="fas fa-home"></i>
                            <span>Trang chủ</span>
                        </a>
                    </li>
                    
                    <?php if ($current_module != 'dashboard'): ?>
                    <li class="breadcrumb-item">
                        <a href="?page=admin&module=<?php echo $current_module; ?>" class="breadcrumb-link">
                            <i class="<?php echo $current_breadcrumb['icon']; ?>"></i>
                            <span><?php echo $current_breadcrumb['title']; ?></span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if ($current_action != 'index' && !empty($action_title)): ?>
                    <li class="breadcrumb-item active">
                        <span><?php echo $action_title; ?></span>
                    </li>
                    <?php endif; ?>
                </ol>
            </nav>
        </div>
        
        <div class="breadcrumb-right">
            <div class="page-title">
                <h1 class="title">
                    <i class="<?php echo $current_breadcrumb['icon']; ?>"></i>
                    <?php echo $current_breadcrumb['title']; ?>
                    <?php if (!empty($action_title) && $current_action != 'index'): ?>
                        <span class="subtitle"> - <?php echo $action_title; ?></span>
                    <?php endif; ?>
                </h1>
            </div>
        </div>
    </div>
</div>