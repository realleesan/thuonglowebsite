<?php
/**
 * Affiliate Commissions Overview
 * Tổng quan hoa hồng
 */

// 1. Khởi tạo View & ServiceManager
require_once __DIR__ . '/../../../../core/view_init.php';

// 2. Chọn service affiliate (được inject từ index.php)
$service = isset($currentService) ? $currentService : ($affiliateService ?? null);

// Initialize data variables
$overview = [
    'total_commission' => 0,
    'pending_commission' => 0,
    'paid_commission' => 0,
    'total_earned' => 0,
    'from_subscription' => 0,
    'from_logistics' => 0,
    'pending' => 0,
    'paid' => 0
];
$history = [];

try {
    if ($service) {
        // Get current affiliate ID from session
        $affiliateId = $_SESSION['user_id'] ?? 0;
        
        // Validate affiliate is logged in
        if ($affiliateId <= 0) {
            throw new Exception('Vui lòng đăng nhập để xem hoa hồng');
        }
        
        // Get dashboard data FIRST for affiliate info (needed by header)
        $dashboardData = $service->getDashboardData($affiliateId);
        $affiliateInfo = $dashboardData['affiliate'] ?? [
            'name' => '',
            'email' => ''
        ];
        
        // Get commissions data từ AffiliateService
        $commissionsData = $service->getCommissionsData($affiliateId);
        
        $overview['total_commission'] = $commissionsData['total_commission'] ?? 0;
        $overview['pending_commission'] = $commissionsData['pending_commission'] ?? 0;
        $overview['paid_commission'] = $commissionsData['paid_commission'] ?? 0;
        $overview['total_earned'] = $overview['total_commission'];
        
        // Get subscription vs logistics breakdown from database
        $overview['from_subscription'] = $commissionsData['from_subscription'] ?? ($overview['total_commission'] * 0.7);
        $overview['from_logistics'] = $commissionsData['from_logistics'] ?? ($overview['total_commission'] * 0.3);
        $overview['pending'] = $overview['pending_commission'];
        $overview['paid'] = $overview['paid_commission'];
    }
} catch (Exception $e) {
    $errorHandler->handleViewError($e, 'affiliate_commissions', []);
    error_log('Commissions Error: ' . $e->getMessage());
}

// Set page info cho master layout
$page_title = 'Quản lý hoa hồng';
$page_module = 'commissions';

// Include master layout
ob_start();
?>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-wallet"></i>
        Quản lý hoa hồng
    </h1>
    <p class="page-subtitle">Theo dõi và quản lý hoa hồng của bạn</p>
</div>

<!-- Overview Stats -->
<div class="stats-grid stats-grid-3">
    <!-- Tổng hoa hồng đã nhận -->
    <div class="stat-card stat-card-success">
        <div class="stat-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Tổng đã nhận</div>
            <div class="stat-value" data-value="<?php echo $overview['paid']; ?>">
                <?php echo number_format($overview['paid']); ?>đ
            </div>
            <div class="stat-meta">
                Đã thanh toán
            </div>
        </div>
    </div>

    <!-- Đang chờ duyệt -->
    <div class="stat-card stat-card-warning">
        <div class="stat-icon">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Đang chờ duyệt</div>
            <div class="stat-value" data-value="<?php echo $overview['pending']; ?>">
                <?php echo number_format($overview['pending']); ?>đ
            </div>
            <div class="stat-meta">
                Chờ thanh toán
            </div>
        </div>
    </div>

    <!-- Tổng hoa hồng -->
    <div class="stat-card stat-card-primary">
        <div class="stat-icon">
            <i class="fas fa-coins"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Tổng hoa hồng</div>
            <div class="stat-value" data-value="<?php echo $overview['total_earned']; ?>">
                <?php echo number_format($overview['total_earned']); ?>đ
            </div>
            <div class="stat-meta">
                Tích lũy
            </div>
        </div>
    </div>
</div>

<!-- Commission Breakdown -->
<div class="dashboard-section">
    <h2 class="section-title">
        <i class="fas fa-chart-pie"></i>
        Phân loại hoa hồng
    </h2>
    
    <div class="commission-breakdown-grid">
        <!-- From Subscription -->
        <div class="card">
            <div class="card-body">
                <div class="commission-breakdown-item">
                    <div class="commission-breakdown-icon commission-breakdown-purple">
                        <i class="fas fa-database"></i>
                    </div>
                    <div class="commission-breakdown-content">
                        <div class="commission-breakdown-label">
                            Từ Gói Data (Subscription)
                        </div>
                        <div class="commission-breakdown-value">
                            <?php echo number_format($overview['from_subscription']); ?>đ
                        </div>
                        <div class="commission-breakdown-percentage">
                            <?php 
                            $subscriptionPercent = 0;
                            if (isset($overview['total_earned']) && $overview['total_earned'] > 0) {
                                $subscriptionPercent = ($overview['from_subscription'] / $overview['total_earned']) * 100;
                            }
                            echo number_format($subscriptionPercent, 1); 
                            ?>% tổng hoa hồng
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- From Logistics -->
        <div class="card">
            <div class="card-body">
                <div class="commission-breakdown-item">
                    <div class="commission-breakdown-icon commission-breakdown-orange">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="commission-breakdown-content">
                        <div class="commission-breakdown-label">
                            Từ Vận chuyển (Logistics)
                        </div>
                        <div class="commission-breakdown-value">
                            <?php echo number_format($overview['from_logistics']); ?>đ
                        </div>
                        <div class="commission-breakdown-percentage">
                            <?php 
                            $logisticsPercent = 0;
                            if (isset($overview['total_earned']) && $overview['total_earned'] > 0) {
                                $logisticsPercent = ($overview['from_logistics'] / $overview['total_earned']) * 100;
                            }
                            echo number_format($logisticsPercent, 1); 
                            ?>% tổng hoa hồng
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="dashboard-section">
    <div class="quick-actions-grid">
        <a href="?page=affiliate&module=commissions&action=history" class="quick-action-card">
            <div class="quick-action-icon">
                <i class="fas fa-history"></i>
            </div>
            <div class="quick-action-content">
                <h3 class="quick-action-title">Lịch sử hoa hồng</h3>
                <p class="quick-action-text">Xem chi tiết các giao dịch</p>
            </div>
            <div class="quick-action-arrow">
                <i class="fas fa-arrow-right"></i>
            </div>
        </a>

        <a href="?page=affiliate&module=commissions&action=policy" class="quick-action-card">
            <div class="quick-action-icon">
                <i class="fas fa-file-contract"></i>
            </div>
            <div class="quick-action-content">
                <h3 class="quick-action-title">Chính sách hoa hồng</h3>
                <p class="quick-action-text">Tìm hiểu về mức hoa hồng</p>
            </div>
            <div class="quick-action-arrow">
                <i class="fas fa-arrow-right"></i>
            </div>
        </a>

        <a href="?page=affiliate&module=finance" class="quick-action-card">
            <div class="quick-action-icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="quick-action-content">
                <h3 class="quick-action-title">Yêu cầu rút tiền</h3>
                <p class="quick-action-text">Rút hoa hồng về tài khoản</p>
            </div>
            <div class="quick-action-arrow">
                <i class="fas fa-arrow-right"></i>
            </div>
        </a>
    </div>
</div>

<!-- Recent Commissions -->
<div class="dashboard-section">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i>
                Giao dịch gần đây
            </h3>
            <a href="?page=affiliate&module=commissions&action=history" class="btn btn-sm btn-secondary">
                Xem tất cả
            </a>
        </div>
        <div class="card-body">
            <?php if (empty($history)): ?>
                <!-- Empty State -->
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <h3 class="empty-state-title">Chưa có giao dịch nào</h3>
                    <p class="empty-state-text">
                        Các giao dịch hoa hồng của bạn sẽ hiển thị tại đây
                    </p>
                </div>
            <?php else: ?>
                <!-- Table -->
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Ngày</th>
                                <th>Nguồn</th>
                                <th>Mô tả</th>
                                <th class="text-right">Hoa hồng</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Hiển thị 5 giao dịch gần nhất
                            $recentHistory = array_slice($history, 0, 5);
                            foreach ($recentHistory as $item): 
                            ?>
                            <tr>
                                <!-- Ngày -->
                                <td>
                                    <span class="text-nowrap">
                                        <?php echo date('d/m/Y', strtotime($item['date'])); ?>
                                    </span>
                                </td>

                                <!-- Nguồn -->
                                <td>
                                    <?php if ($item['product_type'] === 'data_subscription'): ?>
                                        <span class="badge badge-purple">
                                            <i class="fas fa-database"></i>
                                            Gói Data
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-orange">
                                            <i class="fas fa-truck"></i>
                                            Vận chuyển
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Mô tả -->
                                <td>
                                    <?php echo htmlspecialchars($item['description']); ?>
                                </td>

                                <!-- Hoa hồng -->
                                <td class="text-right">
                                    <span class="commission-amount">
                                        <?php echo number_format($item['commission_amount']); ?>đ
                                    </span>
                                </td>

                                <!-- Trạng thái -->
                                <td>
                                    <?php if ($item['status'] === 'paid'): ?>
                                        <span class="badge badge-success">
                                            <i class="fas fa-check-circle"></i>
                                            Đã thanh toán
                                        </span>
                                    <?php elseif ($item['status'] === 'pending'): ?>
                                        <span class="badge badge-warning">
                                            <i class="fas fa-clock"></i>
                                            Chờ thanh toán
                                        </span>
                                    <?php elseif ($item['status'] === 'cancelled'): ?>
                                        <span class="badge badge-danger">
                                            <i class="fas fa-times-circle"></i>
                                            Đã hủy
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../_layout/affiliate_master.php';
?>
