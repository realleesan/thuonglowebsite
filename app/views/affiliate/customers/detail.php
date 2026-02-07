<?php
/**
 * Affiliate Customer Detail
 * Chi tiết khách hàng
 */

// Load data từ AffiliateDataLoader
require_once __DIR__ . '/../../../../core/AffiliateDataLoader.php';
require_once __DIR__ . '/../../../../core/AffiliateErrorHandler.php';

try {
    $loader = new AffiliateDataLoader();
    $customers = $loader->getData('customers') ?? [];
    
    // Get customer ID from URL
    $customerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    // Find customer by ID
    $customer = null;
    foreach ($customers as $c) {
        if ($c['id'] === $customerId) {
            $customer = $c;
            break;
        }
    }
    
    // If customer not found, redirect to list
    if (!$customer) {
        header('Location: ?page=affiliate&module=customers&action=list');
        exit;
    }
    
} catch (Exception $e) {
    AffiliateErrorHandler::handleError($e, 'customer_detail');
    exit;
}

// Set page info cho master layout
$page_title = 'Chi tiết khách hàng';
$page_module = 'customers';

// Include master layout
ob_start();
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-left">
        <a href="?page=affiliate&module=customers&action=list" class="btn btn-secondary btn-back">
            <i class="fas fa-arrow-left"></i>
            Quay lại
        </a>
        <div class="page-header-info">
            <h1 class="page-title">
                <i class="fas fa-user"></i>
                Chi tiết khách hàng
            </h1>
            <p class="page-description">Thông tin chi tiết và lịch sử đơn hàng</p>
        </div>
    </div>
</div>

<!-- Customer Info Card -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-user-circle"></i>
            Thông tin khách hàng
        </h3>
        <div class="card-actions">
            <?php if ($customer['status'] === 'active'): ?>
                <span class="badge badge-success badge-lg">
                    <i class="fas fa-check-circle"></i>
                    Đang hoạt động
                </span>
            <?php else: ?>
                <span class="badge badge-secondary badge-lg">
                    <i class="fas fa-pause-circle"></i>
                    Không hoạt động
                </span>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <div class="customer-detail-grid">
            <!-- Customer Avatar & Name -->
            <div class="customer-detail-section">
                <div class="customer-detail-avatar">
                    <?php echo strtoupper(substr($customer['name'], 0, 2)); ?>
                </div>
                <div class="customer-detail-info">
                    <h2 class="customer-detail-name"><?php echo htmlspecialchars($customer['name']); ?></h2>
                    <p class="customer-detail-id">ID: <?php echo $customer['id']; ?></p>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="customer-detail-section">
                <h4 class="section-title">
                    <i class="fas fa-address-card"></i>
                    Thông tin liên hệ
                </h4>
                <div class="info-list">
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-envelope"></i>
                            Email
                        </div>
                        <div class="info-value"><?php echo htmlspecialchars($customer['email']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-phone"></i>
                            Số điện thoại
                        </div>
                        <div class="info-value"><?php echo htmlspecialchars($customer['phone']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-calendar"></i>
                            Ngày đăng ký
                        </div>
                        <div class="info-value"><?php echo date('d/m/Y', strtotime($customer['registered_date'])); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="stats-grid stats-grid-4">
    <!-- Tổng đơn hàng -->
    <div class="stat-card stat-card-primary">
        <div class="stat-icon">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Tổng đơn hàng</div>
            <div class="stat-value"><?php echo $customer['total_orders']; ?></div>
        </div>
    </div>

    <!-- Tổng chi tiêu -->
    <div class="stat-card stat-card-info">
        <div class="stat-icon">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Tổng chi tiêu</div>
            <div class="stat-value"><?php echo number_format($customer['total_spent']); ?>đ</div>
        </div>
    </div>

    <!-- Hoa hồng đã nhận -->
    <div class="stat-card stat-card-success">
        <div class="stat-icon">
            <i class="fas fa-wallet"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Hoa hồng đã nhận</div>
            <div class="stat-value"><?php echo number_format($customer['commission_earned']); ?>đ</div>
        </div>
    </div>

    <!-- Giá trị trung bình -->
    <div class="stat-card stat-card-warning">
        <div class="stat-icon">
            <i class="fas fa-chart-bar"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Giá trị TB/đơn</div>
            <div class="stat-value">
                <?php 
                $avgOrder = $customer['total_orders'] > 0 ? $customer['total_spent'] / $customer['total_orders'] : 0;
                echo number_format($avgOrder);
                ?>đ
            </div>
        </div>
    </div>
</div>

<!-- Orders History -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-history"></i>
            Lịch sử đơn hàng
        </h3>
        <div class="card-actions">
            <span class="badge badge-info">
                <?php echo count($customer['orders']); ?> đơn hàng
            </span>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($customer['orders'])): ?>
            <!-- Empty State -->
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h3 class="empty-state-title">Chưa có đơn hàng nào</h3>
                <p class="empty-state-description">
                    Khách hàng này chưa có đơn hàng nào.
                </p>
            </div>
        <?php else: ?>
            <!-- Orders Table -->
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Mã đơn hàng</th>
                            <th>Ngày đặt</th>
                            <th>Sản phẩm</th>
                            <th>Giá trị</th>
                            <th>Hoa hồng</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalOrderAmount = 0;
                        $totalOrderCommission = 0;
                        foreach ($customer['orders'] as $order): 
                            $commission = $order['amount'] * 0.10; // 10% commission
                            $totalOrderAmount += $order['amount'];
                            $totalOrderCommission += $commission;
                        ?>
                        <tr>
                            <!-- Mã đơn hàng -->
                            <td>
                                <div class="order-id">
                                    <strong><?php echo htmlspecialchars($order['id']); ?></strong>
                                </div>
                            </td>

                            <!-- Ngày đặt -->
                            <td>
                                <div class="order-date">
                                    <?php echo date('d/m/Y', strtotime($order['date'])); ?>
                                </div>
                            </td>

                            <!-- Sản phẩm -->
                            <td>
                                <div class="order-products">
                                    <?php foreach ($order['products'] as $product): ?>
                                        <span class="product-tag"><?php echo htmlspecialchars($product); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </td>

                            <!-- Giá trị -->
                            <td>
                                <div class="order-amount">
                                    <?php echo number_format($order['amount']); ?>đ
                                </div>
                            </td>

                            <!-- Hoa hồng -->
                            <td>
                                <div class="order-commission">
                                    <span class="commission-amount">
                                        <?php echo number_format($commission); ?>đ
                                    </span>
                                    <span class="commission-rate">(10%)</span>
                                </div>
                            </td>

                            <!-- Trạng thái -->
                            <td>
                                <?php if ($order['status'] === 'completed'): ?>
                                    <span class="badge badge-success">
                                        <i class="fas fa-check-circle"></i>
                                        Hoàn thành
                                    </span>
                                <?php elseif ($order['status'] === 'processing'): ?>
                                    <span class="badge badge-warning">
                                        <i class="fas fa-clock"></i>
                                        Đang xử lý
                                    </span>
                                <?php elseif ($order['status'] === 'cancelled'): ?>
                                    <span class="badge badge-danger">
                                        <i class="fas fa-times-circle"></i>
                                        Đã hủy
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">
                                        <?php echo htmlspecialchars($order['status']); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3"><strong>Tổng cộng</strong></td>
                            <td><strong><?php echo number_format($totalOrderAmount); ?>đ</strong></td>
                            <td><strong><?php echo number_format($totalOrderCommission); ?>đ</strong></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Customer Insights -->
<div class="dashboard-grid-2">
    <!-- Purchase Timeline -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-line"></i>
                Thời gian mua hàng
            </h3>
        </div>
        <div class="card-body">
            <div class="timeline">
                <?php 
                // Sort orders by date descending
                $sortedOrders = $customer['orders'];
                usort($sortedOrders, function($a, $b) {
                    return strtotime($b['date']) - strtotime($a['date']);
                });
                
                foreach ($sortedOrders as $index => $order): 
                ?>
                <div class="timeline-item">
                    <div class="timeline-marker">
                        <?php if ($order['status'] === 'completed'): ?>
                            <i class="fas fa-check-circle text-success"></i>
                        <?php elseif ($order['status'] === 'processing'): ?>
                            <i class="fas fa-clock text-warning"></i>
                        <?php else: ?>
                            <i class="fas fa-times-circle text-danger"></i>
                        <?php endif; ?>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-date"><?php echo date('d/m/Y', strtotime($order['date'])); ?></div>
                        <div class="timeline-title"><?php echo htmlspecialchars($order['id']); ?></div>
                        <div class="timeline-description">
                            <?php echo implode(', ', $order['products']); ?>
                        </div>
                        <div class="timeline-amount"><?php echo number_format($order['amount']); ?>đ</div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Customer Value -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-star"></i>
                Giá trị khách hàng
            </h3>
        </div>
        <div class="card-body">
            <div class="customer-value-metrics">
                <!-- Lifetime Value -->
                <div class="metric-item">
                    <div class="metric-icon metric-icon-primary">
                        <i class="fas fa-gem"></i>
                    </div>
                    <div class="metric-content">
                        <div class="metric-label">Lifetime Value</div>
                        <div class="metric-value"><?php echo number_format($customer['total_spent']); ?>đ</div>
                        <div class="metric-description">Tổng giá trị mang lại</div>
                    </div>
                </div>

                <!-- Commission Rate -->
                <div class="metric-item">
                    <div class="metric-icon metric-icon-success">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div class="metric-content">
                        <div class="metric-label">Tỷ lệ hoa hồng</div>
                        <div class="metric-value">10%</div>
                        <div class="metric-description">Hoa hồng trên mỗi đơn</div>
                    </div>
                </div>

                <!-- Customer Tier -->
                <div class="metric-item">
                    <div class="metric-icon metric-icon-warning">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="metric-content">
                        <div class="metric-label">Hạng khách hàng</div>
                        <div class="metric-value">
                            <?php 
                            if ($customer['total_spent'] >= 10000000) {
                                echo '<span class="badge badge-gold">VIP</span>';
                            } elseif ($customer['total_spent'] >= 5000000) {
                                echo '<span class="badge badge-silver">Thân thiết</span>';
                            } else {
                                echo '<span class="badge badge-bronze">Thường</span>';
                            }
                            ?>
                        </div>
                        <div class="metric-description">Dựa trên tổng chi tiêu</div>
                    </div>
                </div>

                <!-- Days Since Registration -->
                <div class="metric-item">
                    <div class="metric-icon metric-icon-info">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="metric-content">
                        <div class="metric-label">Thời gian tham gia</div>
                        <div class="metric-value">
                            <?php 
                            $daysSince = floor((time() - strtotime($customer['registered_date'])) / 86400);
                            echo $daysSince;
                            ?> ngày
                        </div>
                        <div class="metric-description">Kể từ ngày đăng ký</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../_layout/affiliate_master.php';
?>
