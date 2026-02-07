<?php
/**
 * Finance - Ví của tôi
 * Hiển thị số dư, lịch sử giao dịch
 */

// Load data
require_once __DIR__ . '/../../../../core/AffiliateDataLoader.php';
$dataLoader = new AffiliateDataLoader();
$financeData = $dataLoader->getData('finance');

$wallet = $financeData['wallet'];
$transactions = $financeData['transactions'];
$withdrawalSettings = $financeData['withdrawal_settings'];

// Page title
$page_title = 'Ví của tôi';

// Include master layout
ob_start();
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-content">
        <h1 class="page-title">
            <i class="fas fa-wallet"></i>
            Ví của tôi
        </h1>
        <p class="page-description">Quản lý số dư và lịch sử giao dịch</p>
    </div>
    <div class="page-header-actions">
        <a href="?page=affiliate&module=finance&action=withdraw" class="btn btn-primary">
            <i class="fas fa-money-bill-wave"></i>
            <span>Rút tiền</span>
        </a>
    </div>
</div>

<!-- Webhook Demo Info -->
<div class="alert alert-info" style="margin-bottom: 24px;">
    <i class="fas fa-flask"></i>
    <div class="alert-content">
        <strong>Chế độ Demo:</strong>
        <p style="margin: 8px 0 0 0;">
            Để test tính năng nhận hoa hồng tự động và duyệt lệnh rút tiền, 
            hãy truy cập <a href="?page=affiliate&module=finance&action=webhook_demo" style="color: #356DF1; font-weight: 600;">
            <i class="fas fa-external-link-alt"></i> Trang Webhook Demo</a>
        </p>
    </div>
</div>

<!-- Wallet Stats -->
<div class="wallet-stats">
    <!-- Available Balance -->
    <div class="stat-card stat-card-primary">
        <div class="stat-icon">
            <i class="fas fa-wallet"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Số dư khả dụng</div>
            <div class="stat-value"><?php echo number_format($wallet['balance']); ?> đ</div>
            <div class="stat-footer">
                <span class="stat-note">Có thể rút ngay</span>
            </div>
        </div>
    </div>

    <!-- Frozen Balance -->
    <div class="stat-card stat-card-warning">
        <div class="stat-icon">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Đang xử lý</div>
            <div class="stat-value"><?php echo number_format($wallet['frozen']); ?> đ</div>
            <div class="stat-footer">
                <span class="stat-note">Lệnh rút đang chờ</span>
            </div>
        </div>
    </div>

    <!-- Total Earned -->
    <div class="stat-card stat-card-success">
        <div class="stat-icon">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Tổng thu nhập</div>
            <div class="stat-value"><?php echo number_format($wallet['total_earned']); ?> đ</div>
            <div class="stat-footer">
                <span class="stat-note">Tất cả thời gian</span>
            </div>
        </div>
    </div>
</div>

<!-- Withdrawal Info Card -->
<div class="info-card">
    <div class="info-card-header">
        <i class="fas fa-info-circle"></i>
        <h3>Quy định rút tiền</h3>
    </div>
    <div class="info-card-body">
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Số tiền tối thiểu</div>
                <div class="info-value"><?php echo number_format($withdrawalSettings['min_amount']); ?> đ</div>
            </div>
            <div class="info-item">
                <div class="info-label">Số tiền tối đa</div>
                <div class="info-value"><?php echo number_format($withdrawalSettings['max_amount']); ?> đ</div>
            </div>
            <div class="info-item">
                <div class="info-label">Thời gian xử lý</div>
                <div class="info-value"><?php echo htmlspecialchars($withdrawalSettings['processing_time']); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Phí rút tiền</div>
                <div class="info-value text-success">Miễn phí</div>
            </div>
        </div>
    </div>
</div>

<!-- Transaction History -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-history"></i>
            Lịch sử biến động số dư
        </h3>
        <div class="card-actions">
            <button type="button" class="btn btn-sm btn-outline" onclick="exportTransactions()">
                <i class="fas fa-download"></i>
                <span>Xuất Excel</span>
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card-filters">
        <div class="filter-group">
            <label class="filter-label">Loại giao dịch</label>
            <select class="filter-select" id="transactionTypeFilter" onchange="filterTransactions()">
                <option value="all">Tất cả</option>
                <option value="commission">Hoa hồng</option>
                <option value="withdrawal">Rút tiền</option>
            </select>
        </div>

        <div class="filter-group">
            <label class="filter-label">Trạng thái</label>
            <select class="filter-select" id="transactionStatusFilter" onchange="filterTransactions()">
                <option value="all">Tất cả</option>
                <option value="completed">Hoàn thành</option>
                <option value="pending">Đang xử lý</option>
            </select>
        </div>

        <div class="filter-group">
            <button type="button" class="btn btn-sm btn-outline" onclick="resetTransactionFilters()">
                <i class="fas fa-redo"></i>
                <span>Đặt lại</span>
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Ngày giờ</th>
                        <th>Loại giao dịch</th>
                        <th>Mô tả</th>
                        <th>Số tiền</th>
                        <th>Số dư sau GD</th>
                        <th>Trạng thái</th>
                        <th>Mã tham chiếu</th>
                    </tr>
                </thead>
                <tbody id="transactionsTableBody">
                    <?php foreach ($transactions as $transaction): ?>
                    <tr class="transaction-row" 
                        data-type="<?php echo htmlspecialchars($transaction['type']); ?>"
                        data-status="<?php echo htmlspecialchars($transaction['status']); ?>">
                        <td>
                            <div class="transaction-date">
                                <?php 
                                $date = new DateTime($transaction['date']);
                                echo $date->format('d/m/Y H:i');
                                ?>
                            </div>
                        </td>
                        <td>
                            <?php if ($transaction['type'] === 'commission'): ?>
                                <span class="badge badge-purple">
                                    <i class="fas fa-plus-circle"></i>
                                    Hoa hồng
                                </span>
                            <?php else: ?>
                                <span class="badge badge-orange">
                                    <i class="fas fa-minus-circle"></i>
                                    Rút tiền
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="transaction-description">
                                <?php echo htmlspecialchars($transaction['description']); ?>
                            </div>
                        </td>
                        <td>
                            <?php if ($transaction['amount'] > 0): ?>
                                <span class="amount amount-positive">
                                    +<?php echo number_format($transaction['amount']); ?> đ
                                </span>
                            <?php else: ?>
                                <span class="amount amount-negative">
                                    <?php echo number_format($transaction['amount']); ?> đ
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="balance-after">
                                <?php echo number_format($transaction['balance_after']); ?> đ
                            </span>
                        </td>
                        <td>
                            <?php if ($transaction['status'] === 'completed'): ?>
                                <span class="badge badge-success">
                                    <i class="fas fa-check-circle"></i>
                                    Hoàn thành
                                </span>
                            <?php elseif ($transaction['status'] === 'pending'): ?>
                                <span class="badge badge-warning">
                                    <i class="fas fa-clock"></i>
                                    Đang xử lý
                                </span>
                            <?php else: ?>
                                <span class="badge badge-danger">
                                    <i class="fas fa-times-circle"></i>
                                    Đã hủy
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <code class="reference-code"><?php echo htmlspecialchars($transaction['reference']); ?></code>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Empty State -->
        <div class="empty-state" id="emptyState" style="display: none;">
            <div class="empty-icon">
                <i class="fas fa-inbox"></i>
            </div>
            <h3 class="empty-title">Không tìm thấy giao dịch</h3>
            <p class="empty-description">Thử thay đổi bộ lọc để xem kết quả khác</p>
        </div>
    </div>

    <!-- Pagination -->
    <div class="card-footer">
        <div class="pagination">
            <button class="pagination-btn" disabled>
                <i class="fas fa-chevron-left"></i>
            </button>
            <span class="pagination-info">Trang 1 / 1</span>
            <button class="pagination-btn" disabled>
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../_layout/affiliate_master.php';
?>
