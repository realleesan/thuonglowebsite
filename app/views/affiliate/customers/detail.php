<?php
/**
 * Affiliate Customer Detail
 * Chi tiết khách hàng của đại lý
 * Design: Synchronized with Admin
 */

require_once __DIR__ . '/../../../../core/view_init.php';

$service = isset($currentService) ? $currentService : ($affiliateService ?? null);

$customer = null;
$orders = [];
$stats = ['total_orders' => 0, 'total_spent' => 0, 'total_commission' => 0, 'avg_order_value' => 0];
$timeline = [];

try {
    if ($service) {
        $affiliateId = $_SESSION['user_id'] ?? 0;
        $customerId = (int)($_GET['id'] ?? 0);
        
        if ($affiliateId <= 0 || $customerId <= 0) {
            header('Location: ?page=affiliate&module=customers');
            exit;
        }
        
        // Get customer detail
        $customerData = $service->getCustomerDetail($affiliateId, $customerId);
        $customer = $customerData['customer'] ?? null;
        $orders = $customerData['orders'] ?? [];
        $stats = $customerData['stats'] ?? $stats;
        $timeline = $customerData['timeline'] ?? [];
        
        if (!$customer) {
            header('Location: ?page=affiliate&module=customers&error=not_found');
            exit;
        }
    }
} catch (Exception $e) {
    error_log('Customer Detail Error: ' . $e->getMessage());
    header('Location: ?page=affiliate&module=customers&error=system_error');
    exit;
}

// Helper functions
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . 'đ';
}

function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}

function formatDateShort($date) {
    return date('d/m/Y', strtotime($date));
}

function getStatusLabel($status) {
    $labels = [
        'pending' => 'Chờ xử lý',
        'processing' => 'Đang xử lý',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã hủy'
    ];
    return $labels[$status] ?? $status;
}

function getStatusClass($status) {
    $classes = [
        'pending' => 'badge-warning',
        'processing' => 'badge-info',
        'completed' => 'badge-success',
        'cancelled' => 'badge-danger'
    ];
    return $classes[$status] ?? 'badge-secondary';
}

$page_title = 'Chi tiết khách hàng - ' . ($customer['name'] ?? '');
$page_module = 'customers';

ob_start();
?>

<div class="detail-page">
    <!-- Detail Header -->
    <div class="detail-header">
        <div class="detail-header-left">
            <a href="?page=affiliate&module=customers" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                Quay lại
            </a>
            <div class="detail-title">
                <h1>
                    <i class="fas fa-user"></i>
                    Chi tiết khách hàng
                </h1>
                <p>Thông tin chi tiết của <?= htmlspecialchars($customer['name']) ?></p>
            </div>
        </div>
    </div>

    <!-- Detail Grid -->
    <div class="detail-grid">
        <!-- Customer Info Card -->
        <div class="detail-card">
            <div class="detail-card-header">
                <h3>
                    <i class="fas fa-user-circle"></i>
                    Thông tin khách hàng
                </h3>
            </div>
            <div class="detail-card-body">
                <!-- Avatar -->
                <div class="customer-detail-avatar">
                    <?= strtoupper(substr($customer['name'], 0, 1)) ?>
                </div>
                <div class="customer-detail-info">
                    <h2 class="customer-detail-name"><?= htmlspecialchars($customer['name']) ?></h2>
                    <p class="customer-detail-id">ID: <?= $customer['id'] ?></p>
                    <?php if ($customer['status'] === 'active'): ?>
                        <span class="customer-detail-status active">
                            <i class="fas fa-check-circle"></i>
                            Hoạt động
                        </span>
                    <?php else: ?>
                        <span class="customer-detail-status inactive">
                            <i class="fas fa-pause-circle"></i>
                            Không hoạt động
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Info List -->
                <div class="info-list" style="margin-top: 24px;">
                    <div class="info-item">
                        <span class="info-label">
                            <i class="fas fa-envelope"></i>
                            Email
                        </span>
                        <span class="info-value"><?= htmlspecialchars($customer['email'] ?? 'Chưa có') ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">
                            <i class="fas fa-phone"></i>
                            Số điện thoại
                        </span>
                        <span class="info-value"><?= htmlspecialchars($customer['phone'] ?? 'Chưa có') ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">
                            <i class="fas fa-calendar"></i>
                            Ngày đăng ký
                        </span>
                        <span class="info-value"><?= formatDateShort($customer['registered_date']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">
                            <i class="fas fa-link"></i>
                            Mã giới thiệu
                        </span>
                        <span class="info-value"><?= htmlspecialchars($customer['referral_code'] ?? 'N/A') ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats & Orders Card -->
        <div class="detail-card">
            <div class="detail-card-header">
                <h3>
                    <i class="fas fa-chart-bar"></i>
                    Thống kê & Đơn hàng
                </h3>
            </div>
            <div class="detail-card-body">
                <!-- Stats Metrics -->
                <div class="stats-metrics">
                    <div class="metric-card">
                        <div class="metric-icon metric-icon-primary">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="metric-label">Tổng đơn hàng</div>
                        <div class="metric-value"><?= $stats['total_orders'] ?></div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-icon metric-icon-success">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="metric-label">Tổng doanh số</div>
                        <div class="metric-value"><?= formatPrice($stats['total_spent']) ?></div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-icon metric-icon-warning">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div class="metric-label">Hoa hồng</div>
                        <div class="metric-value"><?= formatPrice($stats['total_commission']) ?></div>
                    </div>
                </div>

                <!-- Orders Table -->
                <h4 style="margin: 24px 0 12px; font-size: 16px; font-weight: 600; color: #111827;">
                    <i class="fas fa-list" style="color: #356DF1; margin-right: 8px;"></i>
                    Lịch sử đơn hàng
                </h4>
                
                <?php if (empty($orders)): ?>
                    <div style="text-align: center; padding: 30px; color: #9CA3AF;">
                        <i class="fas fa-inbox" style="font-size: 36px; margin-bottom: 12px; display: block; color: #D1D5DB;"></i>
                        <p style="margin: 0; font-size: 14px;">Chưa có đơn hàng nào</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive" style="margin-top: 16px;">
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Ngày đặt</th>
                                    <th>Trạng thái</th>
                                    <th>Thành tiền</th>
                                    <th>Hoa hồng</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>
                                        <span class="order-code">#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></span>
                                    </td>
                                    <td><?= formatDate($order['created_at']) ?></td>
                                    <td>
                                        <span class="badge <?= getStatusClass($order['status']) ?>">
                                            <?= getStatusLabel($order['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="order-amount"><?= formatPrice($order['total']) ?></span>
                                    </td>
                                    <td>
                                        <span class="order-commission"><?= formatPrice($order['commission_amount'] ?? 0) ?></span>
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
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../_layout/affiliate_master.php';
?>
