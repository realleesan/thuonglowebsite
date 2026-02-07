<?php
/**
 * Affiliate Breadcrumb
 * Design System: Giống Admin
 */

// Get current page info
$module = isset($_GET['module']) ? $_GET['module'] : 'dashboard';
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Define breadcrumb paths
$breadcrumbs = [
    'dashboard' => [
        ['title' => 'Dashboard', 'url' => '']
    ],
    'commissions' => [
        ['title' => 'Hoa hồng', 'url' => '?page=affiliate&module=commissions']
    ],
    'customers' => [
        ['title' => 'Khách hàng', 'url' => '?page=affiliate&module=customers']
    ],
    'finance' => [
        ['title' => 'Tài chính', 'url' => '?page=affiliate&module=finance']
    ],
    'marketing' => [
        ['title' => 'Marketing', 'url' => '?page=affiliate&module=marketing']
    ],
    'reports' => [
        ['title' => 'Báo cáo', 'url' => '?page=affiliate&module=reports']
    ],
    'profile' => [
        ['title' => 'Hồ sơ', 'url' => '?page=affiliate&module=profile']
    ]
];

// Add action to breadcrumb if exists
$action_titles = [
    'history' => 'Lịch sử',
    'policy' => 'Chính sách',
    'list' => 'Danh sách',
    'detail' => 'Chi tiết',
    'balance' => 'Số dư',
    'withdraw' => 'Rút tiền',
    'tools' => 'Công cụ',
    'campaigns' => 'Chiến dịch',
    'settings' => 'Cài đặt',
    'clicks' => 'Lượt click',
    'orders' => 'Đơn hàng'
];

$current_breadcrumbs = isset($breadcrumbs[$module]) ? $breadcrumbs[$module] : [['title' => 'Dashboard', 'url' => '']];

if ($action && isset($action_titles[$action])) {
    $current_breadcrumbs[] = ['title' => $action_titles[$action], 'url' => ''];
}
?>

<div class="affiliate-breadcrumb">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb-list">
            <!-- Home -->
            <li class="breadcrumb-item">
                <a href="<?php echo base_url(); ?>?page=affiliate" class="breadcrumb-link">
                    <i class="fas fa-home"></i>
                </a>
            </li>

            <?php foreach ($current_breadcrumbs as $index => $crumb): ?>
                <?php if ($index > 0 || $module !== 'dashboard'): ?>
                    <li class="breadcrumb-separator">
                        <i class="fas fa-chevron-right"></i>
                    </li>
                <?php endif; ?>

                <li class="breadcrumb-item <?php echo ($index === count($current_breadcrumbs) - 1) ? 'active' : ''; ?>">
                    <?php if ($crumb['url'] && $index < count($current_breadcrumbs) - 1): ?>
                        <a href="<?php echo base_url() . $crumb['url']; ?>" class="breadcrumb-link">
                            <?php echo htmlspecialchars($crumb['title']); ?>
                        </a>
                    <?php else: ?>
                        <?php echo htmlspecialchars($crumb['title']); ?>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>
    </nav>
</div>
