<?php
/**
 * Finance - Webhook Demo
 * Trang mô phỏng Webhook SePay
 */

// Load data
require_once __DIR__ . '/../../../../core/AffiliateDataLoader.php';
$dataLoader = new AffiliateDataLoader();
$financeData = $dataLoader->getData('finance');

$wallet = $financeData['wallet'];
$withdrawals = $financeData['withdrawals'];

// Page title
$page_title = 'Webhook Demo - Mô phỏng';

// Include master layout
ob_start();
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-content">
        <h1 class="page-title">
            <i class="fas fa-flask"></i>
            Webhook Demo
        </h1>
        <p class="page-description">Mô phỏng Webhook SePay - Chỉ dùng để test</p>
    </div>
    <div class="page-header-actions">
        <a href="?page=affiliate&module=finance" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i>
            <span>Quay lại</span>
        </a>
    </div>
</div>

<!-- Warning Alert -->
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle"></i>
    <div class="alert-content">
        <strong>Chú ý - Trang Demo/Test:</strong>
        <p>Đây là trang demo để test tính năng Webhook. Trong môi trường thực tế, các sự kiện này sẽ được kích hoạt tự động từ hệ thống thanh toán SePay.</p>
        <ul style="margin: 8px 0 0 20px; padding: 0;">
            <li><strong>Webhook 1:</strong> Giả lập khách hàng thanh toán → Hoa hồng tự động cộng vào ví</li>
            <li><strong>Webhook 2:</strong> Giả lập Admin duyệt lệnh rút → Tiền được chuyển, trạng thái cập nhật</li>
        </ul>
        <p style="margin: 8px 0 0 0;">
            <i class="fas fa-info-circle"></i> 
            Để tạo lệnh rút tiền mới, hãy vào 
            <a href="?page=affiliate&module=finance&action=withdraw" style="color: #92400E; font-weight: 600;">
                Trang Rút Tiền
            </a>
        </p>
    </div>
</div>

<!-- Current Wallet Status -->
<div class="wallet-status-card">
    <div class="wallet-status-header">
        <h3>
            <i class="fas fa-wallet"></i>
            Trạng thái ví hiện tại
        </h3>
        <button type="button" class="btn btn-sm btn-outline" onclick="refreshWalletStatus()">
            <i class="fas fa-sync-alt"></i>
            Làm mới
        </button>
    </div>
    <div class="wallet-status-body">
        <div class="status-grid">
            <div class="status-item">
                <div class="status-label">Số dư khả dụng</div>
                <div class="status-value" id="currentBalance"><?php echo number_format($wallet['balance']); ?> đ</div>
            </div>
            <div class="status-item">
                <div class="status-label">Đang xử lý</div>
                <div class="status-value" id="currentFrozen"><?php echo number_format($wallet['frozen']); ?> đ</div>
            </div>
            <div class="status-item">
                <div class="status-label">Tổng thu nhập</div>
                <div class="status-value" id="currentEarned"><?php echo number_format($wallet['total_earned']); ?> đ</div>
            </div>
        </div>
    </div>
</div>

<!-- Webhook Simulation Controls -->
<div class="webhook-controls">
    <!-- Commission Webhook -->
    <div class="webhook-card">
        <div class="webhook-card-header">
            <div class="webhook-icon webhook-icon-success">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="webhook-info">
                <h3 class="webhook-title">Giả lập nhận hoa hồng</h3>
                <p class="webhook-description">Mô phỏng khách hàng thanh toán đơn hàng</p>
            </div>
        </div>
        <div class="webhook-card-body">
            <div class="form-group">
                <label class="form-label">Số tiền đơn hàng</label>
                <div class="amount-input-wrapper">
                    <input type="text" 
                           class="form-input" 
                           id="orderAmount" 
                           value="1000000"
                           placeholder="Nhập số tiền">
                    <span class="amount-suffix">VNĐ</span>
                </div>
                <small class="form-help">
                    Hoa hồng 10%: <strong id="commissionPreview">100,000 đ</strong>
                </small>
            </div>
            <div class="form-group">
                <label class="form-label">Loại đơn hàng</label>
                <select class="form-select" id="orderType">
                    <option value="logistics">Logistics (Vận chuyển)</option>
                    <option value="subscription">Data Subscription</option>
                </select>
            </div>
            <button type="button" class="btn btn-success btn-block" onclick="simulateCommission()">
                <i class="fas fa-plus-circle"></i>
                <span>Giả lập nhận hoa hồng</span>
            </button>
        </div>
    </div>

    <!-- Withdrawal Approval Webhook -->
    <div class="webhook-card">
        <div class="webhook-card-header">
            <div class="webhook-icon webhook-icon-primary">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="webhook-info">
                <h3 class="webhook-title">Giả lập duyệt lệnh rút</h3>
                <p class="webhook-description">Mô phỏng admin duyệt yêu cầu rút tiền</p>
            </div>
        </div>
        <div class="webhook-card-body">
            <div class="form-group">
                <label class="form-label">Chọn lệnh rút</label>
                <select class="form-select" id="withdrawalSelect">
                    <option value="">-- Chọn lệnh rút --</option>
                    <?php 
                    $hasPending = false;
                    foreach ($withdrawals as $withdrawal): 
                        if ($withdrawal['status'] === 'pending'):
                            $hasPending = true;
                    ?>
                        <option value="<?php echo htmlspecialchars($withdrawal['id']); ?>"
                                data-amount="<?php echo $withdrawal['amount']; ?>"
                                data-code="<?php echo htmlspecialchars($withdrawal['withdrawal_code']); ?>">
                            <?php echo htmlspecialchars($withdrawal['withdrawal_code']); ?> - 
                            <?php echo number_format($withdrawal['amount']); ?> đ
                        </option>
                    <?php 
                        endif;
                    endforeach; 
                    
                    if (!$hasPending):
                    ?>
                        <option value="" disabled>Không có lệnh rút nào đang chờ</option>
                    <?php endif; ?>
                </select>
                <?php if (!$hasPending): ?>
                <small class="form-help">
                    <i class="fas fa-info-circle"></i>
                    Tạo lệnh rút mới tại <a href="?page=affiliate&module=finance&action=withdraw" style="color: #356DF1;">Trang Rút Tiền</a>
                </small>
                <?php endif; ?>
            </div>
            <div class="withdrawal-preview" id="withdrawalPreview" style="display: none;">
                <div class="preview-item">
                    <span class="preview-label">Mã rút tiền:</span>
                    <span class="preview-value" id="previewCode">-</span>
                </div>
                <div class="preview-item">
                    <span class="preview-label">Số tiền:</span>
                    <span class="preview-value" id="previewAmount">-</span>
                </div>
            </div>
            <button type="button" class="btn btn-primary btn-block" onclick="simulateWithdrawalApproval()" id="approveBtn" disabled>
                <i class="fas fa-check-circle"></i>
                <span>Giả lập duyệt lệnh rút</span>
            </button>
        </div>
    </div>
</div>

<!-- Webhook Logs -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-terminal"></i>
            Webhook Logs
        </h3>
        <div class="card-actions">
            <button type="button" class="btn btn-sm btn-outline" onclick="clearLogs()">
                <i class="fas fa-trash"></i>
                <span>Xóa logs</span>
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="webhook-logs" id="webhookLogs">
            <div class="log-empty">
                <i class="fas fa-info-circle"></i>
                <p>Chưa có webhook nào được kích hoạt</p>
            </div>
        </div>
    </div>
</div>

<!-- How It Works -->
<div class="info-card">
    <div class="info-card-header">
        <i class="fas fa-question-circle"></i>
        <h3>Cách hoạt động</h3>
    </div>
    <div class="info-card-body">
        <div class="how-it-works">
            <div class="step-item">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h4>Nhận hoa hồng</h4>
                    <p>Khi khách hàng thanh toán đơn hàng, Webhook từ SePay sẽ gửi thông báo về hệ thống. Hoa hồng sẽ được cộng tự động vào ví.</p>
                </div>
            </div>
            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h4>Yêu cầu rút tiền</h4>
                    <p>Agent tạo yêu cầu rút tiền. Số tiền sẽ được chuyển từ "Khả dụng" sang "Đang xử lý".</p>
                </div>
            </div>
            <div class="step-item">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h4>Admin duyệt</h4>
                    <p>Admin kiểm tra và duyệt lệnh rút. Webhook sẽ thông báo kết quả và cập nhật trạng thái.</p>
                </div>
            </div>
            <div class="step-item">
                <div class="step-number">4</div>
                <div class="step-content">
                    <h4>Hoàn tất</h4>
                    <p>Tiền được chuyển vào tài khoản ngân hàng. Lịch sử giao dịch được cập nhật.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Technical Info -->
<div class="alert alert-info">
    <i class="fas fa-code"></i>
    <div class="alert-content">
        <strong>Thông tin kỹ thuật:</strong>
        <p>Trong production, Webhook endpoint sẽ nhận POST request từ SePay với payload chứa thông tin giao dịch. 
        Hệ thống sẽ verify signature, xử lý logic và cập nhật database realtime.</p>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../_layout/affiliate_master.php';
?>
