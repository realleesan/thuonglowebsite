<?php
/**
 * Admin Affiliate Withdrawals Page - Danh sách yêu cầu rút tiền
 * Hiển thị các yêu cầu rút tiền từ đại lý
 */

require_once __DIR__ . '/../../../../core/view_init.php';
require_once __DIR__ . '/../../../../app/models/WithdrawalRequestModel.php';

// Get current page and filters
$current_page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$per_page = 10;

$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

$withdrawals = [];
$total_withdrawals = 0;
$total_pages = 1;

try {
    $withdrawalModel = new WithdrawalRequestModel();
    
    // Build filters
    $filters = [];
    if (!empty($status_filter)) {
        $filters['status'] = $status_filter;
    }
    if (!empty($search)) {
        $filters['search'] = $search;
    }
    
    // Get withdrawals with pagination
    $result = $withdrawalModel->getWithPagination($current_page, $per_page, $filters);
    
    $withdrawals = $result['data'] ?? [];
    $total_withdrawals = $result['total'] ?? 0;
    $total_pages = $result['last_page'] ?? 1;
    
} catch (Exception $e) {
    error_log('Admin Withdrawals View Error: ' . $e->getMessage());
    $withdrawals = [];
}

// Get action results from URL
$success = $_GET['success'] ?? null;
$error = $_GET['error'] ?? null;

// Format helpers
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' đ';
}

function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
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
?>

<div class="affiliates-page withdrawals-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-money-bill-wave"></i>
                Quản Lý Yêu Cầu Rút Tiền
            </h1>
            <span class="results-count">
                <?= $total_withdrawals ?> yêu cầu
            </span>
        </div>
    </div>

    <!-- Notifications -->
    <?php if ($success): ?>
        <div class="alert alert-success">
            <?php switch ($success):
                case 'approved': ?>
                    Đã duyệt và chuyển tiền thành công!
                    <?php break; ?>
                <?php case 'rejected': ?>
                    Đã từ chối yêu cầu rút tiền!
                    <?php break; ?>
                <?php default: ?>
                    Thao tác thành công!
            <?php endswitch; ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <?php switch ($error):
                case 'not_found': ?>
                    Không tìm thấy yêu cầu rút tiền!
                    <?php break; ?>
                <?php case 'already_processed': ?>
                    Yêu cầu này đã được xử lý trước đó!
                    <?php break; ?>
                <?php case 'invalid_id': ?>
                    ID yêu cầu không hợp lệ!
                    <?php break; ?>
                <?php default: ?>
                    <?= htmlspecialchars($error) ?>
            <?php endswitch; ?>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="filters-section">
        <form method="GET" class="filters-form">
            <input type="hidden" name="page" value="admin">
            <input type="hidden" name="module" value="affiliates">
            <input type="hidden" name="action" value="withdrawals">
            
            <div class="filter-group">
                <div class="filter-item">
                    <label for="status">Trạng thái:</label>
                    <select id="status" name="status">
                        <option value="">Tất cả</option>
                        <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>Đang chờ</option>
                        <option value="processing" <?= $status_filter == 'processing' ? 'selected' : '' ?>>Đang xử lý</option>
                        <option value="completed" <?= $status_filter == 'completed' ? 'selected' : '' ?>>Đã duyệt</option>
                        <option value="rejected" <?= $status_filter == 'rejected' ? 'selected' : '' ?>>Đã từ chối</option>
                        <option value="cancelled" <?= $status_filter == 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                    </select>
                </div>
                
                <div class="filter-item">
                    <label for="search">Tìm kiếm:</label>
                    <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Mã rút tiền, tên đại lý, email...">
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-search"></i>
                        Lọc
                    </button>
                    <a href="?page=admin&module=affiliates&action=withdrawals" class="btn btn-outline">
                        <i class="fas fa-times"></i>
                        Xóa bộ lọc
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Results Info -->
    <div class="results-info">
        <span class="results-count">
            Hiển thị <?= count($withdrawals) ?> trong tổng số <?= $total_withdrawals ?> yêu cầu
        </span>
    </div>

    <!-- Withdrawals Table -->
    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th width="40">ID</th>
                    <th>Mã rút tiền</th>
                    <th>Đại lý</th>
                    <th>Số tiền</th>
                    <th>Ngân hàng</th>
                    <th>Ngày yêu cầu</th>
                    <th>Trạng thái</th>
                    <th width="150">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($withdrawals)): ?>
                    <tr>
                        <td colspan="8" class="no-data">
                            <i class="fas fa-inbox"></i>
                            <p>Chưa có yêu cầu rút tiền nào</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($withdrawals as $withdrawal): 
                        $statusBadge = getStatusBadge($withdrawal['status']);
                    ?>
                        <tr>
                            <td><?= $withdrawal['id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($withdrawal['withdraw_code']) ?></strong>
                            </td>
                            <td>
                                <div class="user-info">
                                    <strong><?= htmlspecialchars($withdrawal['affiliate_name'] ?? 'N/A') ?></strong>
                                    <span class="small"><?= htmlspecialchars($withdrawal['affiliate_email'] ?? '') ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="amount-info">
                                    <strong><?= formatPrice($withdrawal['net_amount']) ?></strong>
                                    <?php if ($withdrawal['fee'] > 0): ?>
                                        <small class="fee">Phí: <?= formatPrice($withdrawal['fee']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="bank-info">
                                    <div><?= htmlspecialchars($withdrawal['bank_name'] ?? 'N/A') ?></div>
                                    <small><?= htmlspecialchars($withdrawal['bank_account'] ?? '') ?></small>
                                    <small><?= htmlspecialchars($withdrawal['account_holder'] ?? '') ?></small>
                                </div>
                            </td>
                            <td><?= formatDate($withdrawal['requested_at']) ?></td>
                            <td>
                                <span class="status-badge <?= $statusBadge['class'] ?>">
                                    <?= $statusBadge['text'] ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="?page=admin&module=affiliates&action=withdrawal_detail&id=<?= $withdrawal['id'] ?>"
                                       class="btn btn-sm btn-info" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <?php if ($withdrawal['status'] === 'pending'): ?>
                                        <a href="?page=admin&module=affiliates&action=approve_withdrawal&id=<?= $withdrawal['id'] ?>"
                                           class="btn btn-sm btn-success" title="Duyệt và chuyển tiền"
                                           onclick="return confirm('Bạn có chắc muốn duyệt và chuyển tiền cho yêu cầu này?');">
                                            <i class="fas fa-check"></i>
                                        </a>
                                        <a href="?page=admin&module=affiliates&action=reject_withdrawal&id=<?= $withdrawal['id'] ?>"
                                           class="btn btn-sm btn-warning" title="Từ chối"
                                           onclick="return confirm('Bạn có chắc muốn TỪ CHỐI yêu cầu rút tiền này?');">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($current_page > 1): ?>
                <a href="?page=admin&module=affiliates&action=withdrawals&p=<?= $current_page - 1 ?>&status=<?= $status_filter ?>&search=<?= urlencode($search) ?>" 
                   class="pagination-link">
                    <i class="fas fa-chevron-left"></i>
                </a>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <?php if ($i == $current_page): ?>
                    <span class="pagination-link active"><?= $i ?></span>
                <?php else: ?>
                    <a href="?page=admin&module=affiliates&action=withdrawals&p=<?= $i ?>&status=<?= $status_filter ?>&search=<?= urlencode($search) ?>" 
                       class="pagination-link"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($current_page < $total_pages): ?>
                <a href="?page=admin&module=affiliates&action=withdrawals&p=<?= $current_page + 1 ?>&status=<?= $status_filter ?>&search=<?= urlencode($search) ?>" 
                   class="pagination-link">
                    <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>



<style>
.withdrawals-page .amount-info {
    display: flex;
    flex-direction: column;
}
.withdrawals-page .amount-info .fee {
    color: #6c757d;
    font-size: 12px;
}
.withdrawals-page .bank-info small {
    display: block;
    color: #6c757d;
}
.withdrawals-page .status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}
.withdrawals-page .status-pending {
    background: #fff3cd;
    color: #856404;
}
.withdrawals-page .status-processing {
    background: #d1ecf1;
    color: #0c5460;
}
.withdrawals-page .status-completed {
    background: #d4edda;
    color: #155724;
}
.withdrawals-page .status-rejected,
.withdrawals-page .status-cancelled {
    background: #f8d7da;
    color: #721c24;
}
.withdrawals-page .status-unknown {
    background: #e2e3e5;
    color: #383d41;
}

/* Modal styles */
.withdrawals-page .modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.4);
}
.withdrawals-page .modal-content {
    background-color: #fefefe;
    margin: 15% auto;
    padding: 0;
    border-radius: 8px;
    width: 500px;
    max-width: 90%;
}
.withdrawals-page .modal-header {
    padding: 15px 20px;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.withdrawals-page .modal-header h3 {
    margin: 0;
}
.withdrawals-page .close {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}
.withdrawals-page .close:hover {
    color: #000;
}
.withdrawals-page .modal-body {
    padding: 20px;
}
.withdrawals-page .modal-body .form-group {
    margin-bottom: 15px;
}
.withdrawals-page .modal-body label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
}
.withdrawals-page .modal-body textarea {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    resize: vertical;
}
.withdrawals-page .modal-body .note {
    color: #6c757d;
    font-size: 13px;
    margin-top: 10px;
}
.withdrawals-page .modal-footer {
    padding: 15px 20px;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}
</style>
