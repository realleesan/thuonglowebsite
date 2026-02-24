<?php
/**
 * Reports - Báo Cáo Clicks
 * Analytics về clicks, nguồn traffic
 */

// 1. Khởi tạo View & ServiceManager
require_once __DIR__ . '/../../../../core/view_init.php';

// 2. Chọn service affiliate (được inject từ index.php)
$service = isset($currentService) ? $currentService : ($affiliateService ?? null);

// Initialize data variables
$totalClicks = 0;
$uniqueClicks = 0;
$clicksByDate = [];
$clicksBySource = [];

try {
    if ($service) {
        // Get current affiliate ID from session
        $affiliateId = $_SESSION['user_id'] ?? 0;
        
        // Validate affiliate is logged in
        if ($affiliateId <= 0) {
            throw new Exception('Vui lòng đăng nhập để xem báo cáo');
        }
        
        // Get dashboard data FIRST for affiliate info (needed by header)
        $dashboardData = $service->getDashboardData($affiliateId);
        $affiliateInfo = $dashboardData['affiliate'] ?? [
            'name' => '',
            'email' => ''
        ];
        
        $stats = $dashboardData['stats'] ?? [];
        
        $totalClicks = $stats['total_clicks'] ?? 0;
        // Get unique clicks from database, fallback to calculation only if not available
        $uniqueClicks = $stats['unique_clicks'] ?? (int)($totalClicks * 0.6);
        
        // Get clicks data from service
        $clicksData = $service->getClicksData($affiliateId);
        $clicksByDate = $clicksData['by_date'] ?? [];
        $clicksBySource = $clicksData['by_source'] ?? [];
    }
} catch (Exception $e) {
    $errorHandler->handleViewError($e, 'affiliate_reports_clicks', []);
}

// Page title
$page_title = 'Báo Cáo Clicks';
$load_chartjs = true;

// Include master layout
ob_start();
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-content">
        <h1 class="page-title">
            <i class="fas fa-mouse-pointer"></i>
            Báo Cáo Clicks
        </h1>
        <p class="page-description">Phân tích lượt click vào link giới thiệu</p>
    </div>
    <div class="page-header-actions">
        <button type="button" class="btn btn-outline" onclick="exportClicksReport()">
            <i class="fas fa-file-excel"></i>
            <span>Xuất Excel</span>
        </button>
    </div>
</div>

<!-- Stats Cards -->
<div class="reports-stats">
    <div class="stat-card stat-card-primary">
        <div class="stat-icon">
            <i class="fas fa-mouse-pointer"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Tổng Clicks</div>
            <div class="stat-value"><?php echo number_format($totalClicks); ?></div>
            <div class="stat-footer">
                <span class="stat-note">Tất cả lượt click</span>
            </div>
        </div>
    </div>

    <div class="stat-card stat-card-success">
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Unique Clicks</div>
            <div class="stat-value"><?php echo number_format($uniqueClicks); ?></div>
            <div class="stat-footer">
                <span class="stat-note">Người dùng duy nhất</span>
            </div>
        </div>
    </div>

    <div class="stat-card stat-card-info">
        <div class="stat-icon">
            <i class="fas fa-percentage"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Tỷ Lệ Unique</div>
            <div class="stat-value"><?php echo number_format(($uniqueClicks / $totalClicks) * 100, 1); ?>%</div>
            <div class="stat-footer">
                <span class="stat-note">Unique / Total</span>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="reports-charts">
    <!-- Clicks by Date Chart -->
    <div class="chart-card">
        <div class="chart-card-header">
            <h3 class="chart-title">
                <i class="fas fa-chart-line"></i>
                Clicks Theo Ngày
            </h3>
        </div>
        <div class="chart-card-body">
            <canvas id="clicksByDateChart" 
                    data-labels='<?php echo json_encode(array_column($clicksByDate, 'date')); ?>'
                    data-clicks='<?php echo json_encode(array_column($clicksByDate, 'clicks')); ?>'
                    data-unique='<?php echo json_encode(array_column($clicksByDate, 'unique_clicks')); ?>'>
            </canvas>
        </div>
    </div>

    <!-- Clicks by Source Chart -->
    <div class="chart-card">
        <div class="chart-card-header">
            <h3 class="chart-title">
                <i class="fas fa-chart-pie"></i>
                Nguồn Traffic
            </h3>
        </div>
        <div class="chart-card-body">
            <canvas id="clicksBySourceChart"
                    data-labels='<?php echo json_encode(array_column($clicksBySource, 'source')); ?>'
                    data-clicks='<?php echo json_encode(array_column($clicksBySource, 'clicks')); ?>'>
            </canvas>
        </div>
    </div>
</div>

<!-- Source Details Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-table"></i>
            Chi Tiết Nguồn Traffic
        </h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nguồn</th>
                        <th>Clicks</th>
                        <th>Tỷ lệ</th>
                        <th>Chuyển đổi</th>
                        <th>Tỷ lệ chuyển đổi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clicksBySource as $source): ?>
                    <tr>
                        <td>
                            <div class="source-name">
                                <?php if ($source['source'] === 'Facebook'): ?>
                                    <i class="fab fa-facebook text-primary"></i>
                                <?php elseif ($source['source'] === 'Website'): ?>
                                    <i class="fas fa-globe text-success"></i>
                                <?php elseif ($source['source'] === 'Email'): ?>
                                    <i class="fas fa-envelope text-warning"></i>
                                <?php else: ?>
                                    <i class="fas fa-link text-secondary"></i>
                                <?php endif; ?>
                                <span><?php echo htmlspecialchars($source['source']); ?></span>
                            </div>
                        </td>
                        <td>
                            <strong><?php echo number_format($source['clicks']); ?></strong>
                        </td>
                        <td>
                            <div class="progress-bar-container">
                                <div class="progress-bar" style="width: <?php echo $source['percentage']; ?>%"></div>
                                <span class="progress-text"><?php echo $source['percentage']; ?>%</span>
                            </div>
                        </td>
                        <td>
                            <?php echo number_format($source['conversions']); ?>
                        </td>
                        <td>
                            <span class="badge badge-success">
                                <?php echo number_format(($source['conversions'] / $source['clicks']) * 100, 2); ?>%
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../_layout/affiliate_master.php';
?>
