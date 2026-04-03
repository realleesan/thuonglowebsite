<?php
/**
 * Admin Affiliate Request Detail - Chi tiết yêu cầu đăng ký đại lý
 * Hiển thị thông tin chi tiết của một yêu cầu đăng ký đại lý
 */

require_once __DIR__ . '/../../../../core/view_init.php';
require_once __DIR__ . '/../../../../app/models/AffiliateModel.php';
require_once __DIR__ . '/../../../../app/models/UsersModel.php';

// Get request ID from URL
$request_id = (int)($_GET['id'] ?? 0);

$request = null;
$additional_info = [];
$error_msg = null;
$debug_info = [];

if ($request_id > 0) {
    try {
        $affiliateModel = new AffiliateModel();
        $debug_info[] = 'AffiliateModel created: OK';
        
        // Get affiliate request with user info
        $sql = "
            SELECT a.*, u.name as user_name, u.email as user_email, u.phone as user_phone, 
                   u.address as user_address, u.created_at as user_created_at
            FROM affiliates a
            LEFT JOIN users u ON a.user_id = u.id
            WHERE a.id = {$request_id}
        ";
        $debug_info[] = 'SQL: ' . $sql;
        $result = $affiliateModel->query($sql);
        $debug_info[] = 'Result count: ' . count($result ?? []);
        
        if (!empty($result)) {
            $request = $result[0];
            // Parse additional_info JSON
            if (!empty($request['additional_info'])) {
                $additional_info = json_decode($request['additional_info'], true) ?? [];
            }
        } else {
            $error_msg = 'Không tìm thấy yêu cầu đăng ký đại lý.';
        }
    } catch (Exception $e) {
        $debug_info[] = 'Exception: ' . $e->getMessage();
        $debug_info[] = 'File: ' . $e->getFile() . ':' . $e->getLine();
        error_log('Admin Affiliate Request Detail Error: ' . $e->getMessage());
        $error_msg = 'Đã xảy ra lỗi khi tải thông tin yêu cầu.';
    }
} else {
    $error_msg = 'ID yêu cầu không hợp lệ.';
}

// Format date helper
function formatDateDetail($date) {
    if (empty($date)) return 'N/A';
    return date('d/m/Y H:i', strtotime($date));
}

// Get status label
function getStatusLabelDetail($status) {
    $labels = [
        'pending' => 'Chờ duyệt',
        'approved' => 'Đã duyệt',
        'rejected' => 'Từ chối'
    ];
    return $labels[$status] ?? 'N/A';
}

// Get status badge class
function getStatusBadgeClassDetail($status) {
    $classes = [
        'pending' => 'status-pending',
        'approved' => 'status-approved',
        'rejected' => 'status-rejected'
    ];
    return $classes[$status] ?? '';
}
?>

<div class="affiliates-page affiliates-request-detail-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1>Chi tiết yêu cầu đăng ký đại lý</h1>
            <?php if ($request): ?>
                <span class="results-count">
                    Yêu cầu #<?= $request['id'] ?>
                </span>
            <?php endif; ?>
        </div>
        <div class="page-header-right">
            <?php if ($request && $request['status'] === 'pending'): ?>
                <a href="?page=admin&module=affiliates&action=approve_request&id=<?= $request['id'] ?>" 
                   class="btn btn-success"
                   onclick="return confirm('Bạn có chắc chắn muốn duyệt yêu cầu này?')">
                    <i class="fas fa-check"></i>
                    Duyệt yêu cầu
                </a>
                <a href="?page=admin&module=affiliates&action=reject_request&id=<?= $request['id'] ?>" 
                   class="btn btn-warning"
                   onclick="return confirm('Bạn có chắc chắn muốn từ chối yêu cầu này?')">
                    <i class="fas fa-times"></i>
                    Từ chối
                </a>
            <?php endif; ?>
            <a href="?page=admin&module=affiliates&action=requests" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Quay lại danh sách
            </a>
        </div>
    </div>

    <?php if ($error_msg): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i>
            <?= htmlspecialchars($error_msg) ?>
        </div>
    <?php elseif ($request): ?>
        <!-- Request Info Cards -->
        <div class="detail-cards">
            <!-- Basic Info Card -->
            <div class="detail-card">
                <div class="card-header">
                    <h3><i class="fas fa-user"></i> Thông tin người dùng</h3>
                </div>
                <div class="card-body">
                    <div class="detail-row">
                        <label>Họ tên:</label>
                        <span><?= htmlspecialchars($request['user_name'] ?? 'N/A') ?></span>
                    </div>
                    <div class="detail-row">
                        <label>Email:</label>
                        <span><?= htmlspecialchars($request['user_email'] ?? 'N/A') ?></span>
                    </div>
                    <div class="detail-row">
                        <label>Số điện thoại:</label>
                        <span><?= htmlspecialchars($request['user_phone'] ?? 'N/A') ?></span>
                    </div>
                    <div class="detail-row">
                        <label>ID Người dùng:</label>
                        <span>#<?= $request['user_id'] ?? 'N/A' ?></span>
                    </div>
                    <div class="detail-row">
                        <label>Ngày tạo tài khoản:</label>
                        <span><?= formatDateDetail($request['user_created_at']) ?></span>
                    </div>
                </div>
            </div>

            <!-- Affiliate Info Card -->
            <div class="detail-card">
                <div class="card-header">
                    <h3><i class="fas fa-handshake"></i> Thông tin đại lý</h3>
                    <span class="status-badge <?= getStatusBadgeClassDetail($request['status']) ?>">
                        <?= getStatusLabelDetail($request['status']) ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="detail-row">
                        <label>ID Yêu cầu:</label>
                        <span>#<?= $request['id'] ?></span>
                    </div>
                    <div class="detail-row">
                        <label>Mã giới thiệu:</label>
                        <span class="referral-code"><?= htmlspecialchars($request['referral_code'] ?? 'N/A') ?></span>
                    </div>
                    <div class="detail-row">
                        <label>Tỷ lệ hoa hồng:</label>
                        <span><?= $request['commission_rate'] ?>%</span>
                    </div>
                    <div class="detail-row">
                        <label>Ngày đăng ký:</label>
                        <span><?= formatDateDetail($request['created_at']) ?></span>
                    </div>
                    <div class="detail-row">
                        <label>Cập nhật lần cuối:</label>
                        <span><?= formatDateDetail($request['updated_at']) ?></span>
                    </div>
                </div>
            </div>

            <!-- Additional Info Card -->
            <?php if (!empty($additional_info)): ?>
            <div class="detail-card">
                <div class="card-header">
                    <h3><i class="fas fa-info-circle"></i> Thông tin bổ sung</h3>
                </div>
                <div class="card-body">
                    <?php if (isset($additional_info['full_name'])): ?>
                    <div class="detail-row">
                        <label>Họ tên (đăng ký):</label>
                        <span><?= htmlspecialchars($additional_info['full_name']) ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($additional_info['phone_number'])): ?>
                    <div class="detail-row">
                        <label>Số điện thoại (đăng ký):</label>
                        <span><?= htmlspecialchars($additional_info['phone_number']) ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($additional_info['registration_source'])): ?>
                    <div class="detail-row">
                        <label>Nguồn đăng ký:</label>
                        <span><?= htmlspecialchars($additional_info['registration_source']) ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($additional_info['target_market'])): ?>
                    <div class="detail-row">
                        <label>Thị trường mục tiêu:</label>
                        <span><?= htmlspecialchars($additional_info['target_market']) ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($additional_info['motivation'])): ?>
                    <div class="detail-row">
                        <label>Động lực tham gia:</label>
                        <span><?= htmlspecialchars($additional_info['motivation']) ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($additional_info['requested_at'])): ?>
                    <div class="detail-row">
                        <label>Thời gian yêu cầu:</label>
                        <span><?= formatDateDetail($additional_info['requested_at']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Financial Info Card -->
            <div class="detail-card">
                <div class="card-header">
                    <h3><i class="fas fa-chart-line"></i> Thống kê tài chính</h3>
                </div>
                <div class="card-body">
                    <div class="detail-row">
                        <label>Tổng doanh số:</label>
                        <span><?= number_format($request['total_sales'] ?? 0, 0, ',', '.') ?> VNĐ</span>
                    </div>
                    <div class="detail-row">
                        <label>Tổng hoa hồng:</label>
                        <span><?= number_format($request['total_commission'] ?? 0, 0, ',', '.') ?> VNĐ</span>
                    </div>
                    <div class="detail-row">
                        <label>Hoa hồng đã thanh toán:</label>
                        <span><?= number_format($request['paid_commission'] ?? 0, 0, ',', '.') ?> VNĐ</span>
                    </div>
                    <div class="detail-row">
                        <label>Hoa hồng chờ thanh toán:</label>
                        <span><?= number_format($request['pending_commission'] ?? 0, 0, ',', '.') ?> VNĐ</span>
                    </div>
                    <div class="detail-row">
                        <label>Số dư:</label>
                        <span><?= number_format($request['balance'] ?? 0, 0, ',', '.') ?> VNĐ</span>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
/* Request Detail Page Styles */
.affiliates-request-detail-page {
    padding: 24px;
}

.affiliates-request-detail-page .page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 16px;
}

.affiliates-request-detail-page .page-header-left h1 {
    margin: 0 0 4px 0;
    font-size: 24px;
    font-weight: 600;
    color: #333;
}

.affiliates-request-detail-page .results-count {
    color: #666;
    font-size: 14px;
}

.affiliates-request-detail-page .page-header-right {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.affiliates-request-detail-page .detail-cards {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

@media (max-width: 768px) {
    .affiliates-request-detail-page .detail-cards {
        grid-template-columns: 1fr;
    }
}

.affiliates-request-detail-page .detail-card {
    background: #ffffff;
    border: 1px solid #E5E7EB;
    border-radius: 8px;
    overflow: hidden;
}

.affiliates-request-detail-page .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    background: #F9FAFB;
    border-bottom: 1px solid #E5E7EB;
}

.affiliates-request-detail-page .card-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #333;
    display: flex;
    align-items: center;
    gap: 8px;
}

.affiliates-request-detail-page .card-header h3 i {
    color: #666;
}

.affiliates-request-detail-page .card-body {
    padding: 16px 20px;
}

.affiliates-request-detail-page .detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #F3F4F6;
}

.affiliates-request-detail-page .detail-row:last-child {
    border-bottom: none;
}

.affiliates-request-detail-page .detail-row label {
    font-weight: 500;
    color: #666;
    font-size: 14px;
    min-width: 180px;
}

.affiliates-request-detail-page .detail-row span {
    color: #333;
    font-size: 14px;
    text-align: right;
}

.affiliates-request-detail-page .referral-code {
    font-family: monospace;
    background: #F3F4F6;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 14px;
}

.affiliates-request-detail-page .status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
}

.affiliates-request-detail-page .status-pending {
    background: #FEF3C7;
    color: #92400E;
}

.affiliates-request-detail-page .status-approved {
    background: #D1FAE5;
    color: #065F46;
}

.affiliates-request-detail-page .status-rejected {
    background: #FEE2E2;
    color: #991B1B;
}

.affiliates-request-detail-page .alert {
    padding: 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.affiliates-request-detail-page .alert-danger {
    background: #FEE2E2;
    color: #991B1B;
    border: 1px solid #FECACA;
}
</style>
