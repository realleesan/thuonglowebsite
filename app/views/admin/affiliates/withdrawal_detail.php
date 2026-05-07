<?php
/**
 * Admin Affiliate Withdrawal Detail Page - Chi tiết yêu cầu rút tiền
 */

// Chọn service admin (được inject từ index.php)
$service = isset($currentService) ? $currentService : ($adminService ?? null);

// Kiểm tra service availability
if ($service === null) {
    die('Error: AdminService not available. Please contact administrator.');
}

// Get error handler if available
$errorHandler = null;
if (isset($GLOBALS['errorHandler'])) {
    $errorHandler = $GLOBALS['errorHandler'];
} elseif (class_exists('ErrorHandler')) {
    $errorHandler = new ErrorHandler();
}

require_once __DIR__ . '/../../../../app/models/WithdrawalRequestModel.php';

$withdrawal_id = (int)($_GET['id'] ?? 0);
$withdrawal = null;

if ($withdrawal_id > 0) {
    try {
        $withdrawalModel = new WithdrawalRequestModel();
        $withdrawal = $withdrawalModel->getWithDetails($withdrawal_id);
    } catch (Exception $e) {
        error_log('Admin Withdrawal Detail Error: ' . $e->getMessage());
    }
}

// Format helpers
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' đ';
}

function formatDate($date) {
    return $date ? date('d/m/Y H:i', strtotime($date)) : 'N/A';
}

function getStatusBadge($status) {
    $badges = [
        'pending' => ['class' => 'status-pending', 'text' => 'Đang chờ'],
        'processing' => ['class' => 'status-processing', 'text' => 'Đang xử lý'],
        'completed' => ['class' => 'status-completed', 'text' => 'Đã duyệt'],
        'rejected' => ['class' => 'status-rejected', 'text' => 'Đã từ chối'],
        'cancelled' => ['class' => 'status-cancelled', 'text' => 'Đã hủy']
    ];
    return $badges[$status] ?? ['class' => 'status-unknown', 'text' => 'Không xác định'];
}

if (!$withdrawal) {
    echo '<div class="alert alert-danger">Không tìm thấy yêu cầu rút tiền!</div>';
    echo '<a href="?page=admin&module=affiliates&action=withdrawals" class="btn btn-secondary">← Quay lại danh sách</a>';
    return;
}

$statusBadge = getStatusBadge($withdrawal['status']);
?>

<div class="affiliates-page withdrawal-detail-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-money-bill-wave"></i>
                Chi Tiết Yêu Cầu Rút Tiền
            </h1>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=affiliates&action=withdrawals" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Quay lại
            </a>
        </div>
    </div>

    <!-- Withdrawal Code & Status -->
    <div class="withdrawal-header">
        <div class="withdrawal-code">
            <h2><?= htmlspecialchars($withdrawal['withdraw_code']) ?></h2>
            <span class="status-badge <?= $statusBadge['class'] ?>"><?= $statusBadge['text'] ?></span>
        </div>
        <div class="withdrawal-date">
            <i class="fas fa-calendar"></i>
            Yêu cầu lúc: <?= formatDate($withdrawal['requested_at']) ?>
        </div>
    </div>

    <div class="detail-grid">
        <!-- Affiliate Info -->
        <div class="detail-card">
            <h3><i class="fas fa-user"></i> Thông tin đại lý</h3>
            <div class="detail-content">
                <div class="detail-row">
                    <span class="label">Tên:</span>
                    <span class="value"><?= htmlspecialchars($withdrawal['affiliate_name'] ?? 'N/A') ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Email:</span>
                    <span class="value"><?= htmlspecialchars($withdrawal['affiliate_email'] ?? 'N/A') ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Số điện thoại:</span>
                    <span class="value"><?= htmlspecialchars($withdrawal['affiliate_phone'] ?? 'N/A') ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Mã giới thiệu:</span>
                    <span class="value"><?= htmlspecialchars($withdrawal['referral_code'] ?? 'N/A') ?></span>
                </div>
            </div>
        </div>

        <!-- Bank Info -->
        <div class="detail-card">
            <h3><i class="fas fa-university"></i> Thông tin ngân hàng</h3>
            <div class="detail-content">
                <div class="detail-row">
                    <span class="label">Ngân hàng:</span>
                    <span class="value"><?= htmlspecialchars($withdrawal['bank_name'] ?? 'N/A') ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Số tài khoản:</span>
                    <span class="value"><?= htmlspecialchars($withdrawal['bank_account'] ?? 'N/A') ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Chủ tài khoản:</span>
                    <span class="value"><?= htmlspecialchars($withdrawal['account_holder'] ?? 'N/A') ?></span>
                </div>
                <?php if (!empty($withdrawal['bank_branch'])): ?>
                <div class="detail-row">
                    <span class="label">Chi nhánh:</span>
                    <span class="value"><?= htmlspecialchars($withdrawal['bank_branch']) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Amount Info -->
        <div class="detail-card">
            <h3><i class="fas fa-money-bill"></i> Thông tin số tiền</h3>
            <div class="detail-content">
                <div class="detail-row highlight">
                    <span class="label">Số tiền yêu cầu:</span>
                    <span class="value amount"><?= formatPrice($withdrawal['amount']) ?></span>
                </div>
                <?php if ($withdrawal['fee'] > 0): ?>
                <div class="detail-row">
                    <span class="label">Phí:</span>
                    <span class="value fee">-<?= formatPrice($withdrawal['fee']) ?></span>
                </div>
                <div class="detail-row highlight">
                    <span class="label">Thực nhận:</span>
                    <span class="value amount"><?= formatPrice($withdrawal['net_amount']) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Processing Info -->
        <div class="detail-card">
            <h3><i class="fas fa-info-circle"></i> Thông tin xử lý</h3>
            <div class="detail-content">
                <div class="detail-row">
                    <span class="label">Trạng thái:</span>
                    <span class="value">
                        <span class="status-badge <?= $statusBadge['class'] ?>"><?= $statusBadge['text'] ?></span>
                    </span>
                </div>
                <?php if (!empty($withdrawal['processed_by_name'])): ?>
                <div class="detail-row">
                    <span class="label">Xử lý bởi:</span>
                    <span class="value"><?= htmlspecialchars($withdrawal['processed_by_name']) ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($withdrawal['processed_at'])): ?>
                <div class="detail-row">
                    <span class="label">Thời gian xử lý:</span>
                    <span class="value"><?= formatDate($withdrawal['processed_at']) ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($withdrawal['admin_note'])): ?>
                <div class="detail-row">
                    <span class="label">Ghi chú admin:</span>
                    <span class="value"><?= nl2br(htmlspecialchars($withdrawal['admin_note'])) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <?php if ($withdrawal['status'] === 'pending'): ?>
    <div class="detail-actions">
        <a href="?page=admin&module=affiliates&action=approve_withdrawal&id=<?= $withdrawal['id'] ?>"
           class="btn btn-success btn-lg"
           onclick="return confirm('Bạn có chắc muốn DUỆT và chuyển tiền tự động thông qua PayOS cho yêu cầu này?');">
            <i class="fas fa-check"></i>
            Duyệt & Chuyển tiền
        </a>
        <button type="button" class="btn btn-warning btn-lg"
                onclick="confirmReject()">
            <i class="fas fa-times"></i>
            Từ chối
        </button>
    </div>
    <?php endif; ?>
</div>

<script>
function confirmReject() {
    if (confirm('Bạn có chắc muốn TỪ CHỐI yêu cầu rút tiền này?')) {
        // Redirect đến reject action
        window.location.href = '?page=admin&module=affiliates&action=reject_withdrawal&id=<?= $withdrawal['id'] ?>';
    }
}
</script>

<style>
.withdrawal-detail-page .withdrawal-header {
    background: #fff;
    padding: 20px 25px;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.withdrawal-detail-page .withdrawal-header h2 {
    margin: 0 0 5px 0;
    font-size: 24px;
}
.withdrawal-detail-page .withdrawal-date {
    color: #6c757d;
    font-size: 14px;
}
.withdrawal-detail-page .detail-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}
.withdrawal-detail-page .detail-card {
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.withdrawal-detail-page .detail-card h3 {
    background: #f8f9fa;
    margin: 0;
    padding: 15px 20px;
    font-size: 16px;
    border-bottom: 1px solid #e9ecef;
    color: #495057;
}
.withdrawal-detail-page .detail-card h3 i {
    margin-right: 8px;
    color: #007bff;
}
.withdrawal-detail-page .detail-content {
    padding: 20px;
}
.withdrawal-detail-page .detail-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #f1f3f5;
}
.withdrawal-detail-page .detail-row:last-child {
    border-bottom: none;
}
.withdrawal-detail-page .detail-row .label {
    color: #6c757d;
    font-weight: 500;
}
.withdrawal-detail-page .detail-row .value {
    color: #212529;
    font-weight: 500;
}
.withdrawal-detail-page .detail-row.highlight {
    background: #f8f9fa;
    margin: 0 -20px;
    padding: 15px 20px;
}
.withdrawal-detail-page .detail-row .amount {
    color: #28a745;
    font-size: 18px;
    font-weight: 600;
}
.withdrawal-detail-page .detail-row .fee {
    color: #dc3545;
}
.withdrawal-detail-page .status-badge {
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}
.withdrawal-detail-page .status-pending {
    background: #fff3cd;
    color: #856404;
}
.withdrawal-detail-page .status-processing {
    background: #d1ecf1;
    color: #0c5460;
}
.withdrawal-detail-page .status-completed {
    background: #d4edda;
    color: #155724;
}
.withdrawal-detail-page .status-rejected,
.withdrawal-detail-page .status-cancelled {
    background: #f8d7da;
    color: #721c24;
}
.withdrawal-detail-page .detail-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    padding: 20px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Modal styles */
#rejectModal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.4);
    justify-content: center;
    align-items: center;
    display: flex;
}

#rejectModalContent {
    background-color: #fefefe;
    padding: 0;
    border-radius: 8px;
    width: 500px;
    max-width: 90%;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}
#rejectModalHeader {
    padding: 15px 20px;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

#rejectModalHeader h3 {
    margin: 0;
}

#rejectModalClose {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

#rejectModalClose:hover {
    color: #000;
}

#rejectModalBody {
    padding: 20px;
}

#rejectFormGroup {
    margin-bottom: 15px;
}

#rejectModalBody label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
}

#rejectModalBody textarea {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    resize: vertical;
}

#rejectNoteText {
    color: #6c757d;
    font-size: 13px;
    margin-top: 10px;
    background: #fff3cd;
    padding: 10px;
    border-radius: 4px;
}

#rejectModalFooter {
    padding: 15px 20px;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

@media (max-width: 768px) {
    .withdrawal-detail-page .detail-grid {
        grid-template-columns: 1fr;
    }
}

</style>
