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
        $history = $commissionsData['history'] ?? [];
        
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

<!-- Overview Stats -->
<div class="stats-grid stats-grid-3">
    <!-- Tổng hoa hồng đã nhận -->
    <div class="stat-card stat-card-success">
        <div class="stat-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Tổng hoa hồng đã nhận</div>
            <div class="stat-value" data-value="<?php echo $overview['paid']; ?>">
                <?php echo number_format($overview['paid'], 0, ',', '.'); ?>đ
            </div>
            <div class="stat-meta">
                Đã thanh toán
            </div>
        </div>
    </div>

    <!-- Đang chờ thanh toán -->
    <div class="stat-card stat-card-warning">
        <div class="stat-icon">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Đang chờ thanh toán</div>
            <div class="stat-value" data-value="<?php echo $overview['pending']; ?>">
                <?php echo number_format($overview['pending'], 0, ',', '.'); ?>đ
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
                <?php echo number_format($overview['total_earned'], 0, ',', '.'); ?>đ
            </div>
            <div class="stat-meta">
                Tích lũy
            </div>
        </div>
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
                                        <?php echo number_format($item['commission_amount'], 0, ',', '.'); ?>đ
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
