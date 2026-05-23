<?php
/**
 * Admin Affiliate Requests Page - Danh sách yêu cầu đăng ký đại lý
 * Hiển thị các yêu cầu đăng ký đại lý đang chờ duyệt
 */

require_once __DIR__ . '/../../../../core/view_init.php';
require_once __DIR__ . '/../../../../app/models/AffiliateModel.php';
require_once __DIR__ . '/../../../../app/models/UsersModel.php';

// Get current page and filters
$current_page = max(1, (int)($_GET['p'] ?? 1));
$per_page = 10;

$requests = [];
$paged_requests = [];
$total_requests = 0;
$total_pages = 1;
$debug_info = [];

try {
    $affiliateModel = new AffiliateModel();
    $debug_info[] = 'AffiliateModel created: OK';
    
    // Count ALL requests (pending + inactive/rejected + active/approved) - show history
    $countSql = "SELECT COUNT(*) as total FROM affiliates WHERE status IN ('pending', 'inactive', 'active')";
    $debug_info[] = 'Count SQL: ' . $countSql;
    $countResult = $affiliateModel->query($countSql);
    $debug_info[] = 'Count Result: ' . print_r($countResult, true);
    $total_requests = $countResult[0]['total'] ?? 0;
    $debug_info[] = 'Total requests: ' . $total_requests;
    
    // Get ALL requests with user info (pending + inactive/rejected + active/approved)
    $offset = max(0, ($current_page - 1) * $per_page);
    $requestsSql = "
        SELECT a.*, u.name as user_name, u.email as user_email, u.phone as user_phone
        FROM affiliates a
        LEFT JOIN users u ON a.user_id = u.id
        WHERE a.status IN ('pending', 'inactive', 'active')
        ORDER BY a.created_at DESC
        LIMIT {$per_page} OFFSET {$offset}
    ";
    $debug_info[] = 'Requests SQL: ' . $requestsSql;
    $requests = $affiliateModel->query($requestsSql);
    $debug_info[] = 'Requests count: ' . count($requests ?? []);
    
    $total_pages = max(1, ceil($total_requests / $per_page));
    $paged_requests = $requests ?? [];
    
} catch (Exception $e) {
    $debug_info[] = 'Exception: ' . $e->getMessage();
    $debug_info[] = 'File: ' . $e->getFile() . ':' . $e->getLine();
    error_log('Admin Affiliate Requests View Error: ' . $e->getMessage());
    $requests = [];
    $paged_requests = [];
    // Don't reset $total_requests - keep the count from successful query
    $total_pages = max(1, ceil($total_requests / $per_page));
}

$start_page = max(1, $current_page - 2);
$end_page = min($total_pages, $current_page + 2);

// Get action results from URL
$success = $_GET['success'] ?? null;
$error = $_GET['error'] ?? null;
?>
<div class="affiliates-page affiliates-requests-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1>Yêu cầu đăng ký đại lý</h1>
            <span class="results-count">
                <?= $total_requests ?> yêu cầu
            </span>
        </div>
    </div>

    <!-- Notifications -->
    <?php if ($success): ?>
        <div class="alert alert-success">
            <?php switch ($success):
                case 'approved': ?>
                    Đã duyệt yêu cầu thành công!
                    <?php break; ?>
                <?php case 'rejected': ?>
                    Đã từ chối yêu cầu!
                    <?php break; ?>
                <?php case 'deleted': ?>
                    Đã xóa yêu cầu!
                    <?php break; ?>
                <?php default: ?>
                    Thao tác thành công!
            <?php endswitch; ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <?php switch ($error):
                case 'invalid_id': ?>
                    ID yêu cầu không hợp lệ!
                    <?php break; ?>
                <?php case 'not_found': ?>
                    Không tìm thấy yêu cầu!
                    <?php break; ?>
                <?php case 'system_error': ?>
                    Đã xảy ra lỗi hệ thống. Vui lòng thử lại sau!
                    <?php break; ?>
                <?php default: ?>
                    Đã xảy ra lỗi!
            <?php endswitch; ?>
        </div>
    <?php endif; ?>

    <!-- Requests Table -->
    <div class="data-table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Người dùng</th>
                    <th>Thông tin liên hệ</th>
                    <th>Ngày đăng ký</th>
                    <th>Mã giới thiệu</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($paged_requests)): ?>
                    <tr>
                        <td colspan="7" class="no-data">
                            Chưa có yêu cầu đăng ký đại lý nào
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($paged_requests as $index => $request): ?>
                        <tr>
                            <td><?= ($current_page - 1) * $per_page + $index + 1 ?></td>
                            <td>
                                <div class="user-info">
                                    <strong><?= htmlspecialchars($request['user_name'] ?? 'N/A') ?></strong>
                                    <span class="small">ID: <?= $request['user_id'] ?? 'N/A' ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="contact-info">
                                    <div><i class="fas fa-envelope"></i> <?= htmlspecialchars($request['user_email'] ?? 'N/A') ?></div>
                                    <div><i class="fas fa-phone"></i> <?= htmlspecialchars($request['user_phone'] ?? 'N/A') ?></div>
                                </div>
                            </td>
                            <td><?= isset($request['created_at']) ? date('d/m/Y H:i', strtotime($request['created_at'])) : 'N/A' ?></td>
                            <td><?= htmlspecialchars($request['referral_code'] ?? 'N/A') ?></td>
                            <td>
                                <span class="status-badge status-<?= $request['status'] ?? 'pending' ?>">
                                    <?php
                                    $status = $request['status'] ?? 'pending';
                                    if ($status === 'pending') {
                                        echo 'Chờ duyệt';
                                    } elseif ($status === 'inactive') {
                                        echo 'Đã từ chối';
                                    } elseif ($status === 'active') {
                                        echo 'Đã duyệt';
                                    } else {
                                        echo htmlspecialchars(ucfirst($status));
                                    }
                                    ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="?page=admin&module=affiliates&action=request_detail&id=<?= $request['id'] ?>"
                                       class="btn btn-sm btn-info" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if (($request['status'] ?? 'pending') === 'pending'): ?>
                                        <a href="?page=admin&module=affiliates&action=approve_request&id=<?= $request['id'] ?>"
                                           class="btn btn-sm btn-success" title="Duyệt">
                                            <i class="fas fa-check"></i>
                                        </a>
                                        <a href="?page=admin&module=affiliates&action=reject_request&id=<?= $request['id'] ?>"
                                           class="btn btn-sm btn-warning" title="Từ chối">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    <?php elseif (($request['status'] ?? '') === 'inactive'): ?>
                                    <?php elseif (($request['status'] ?? '') === 'active'): ?>
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
                <a href="?<?= http_build_query(array_merge($_GET, ['p' => $current_page - 1])) ?>"
                   class="pagination-btn">
                    <i class="fas fa-chevron-left"></i>
                </a>
            <?php endif; ?>

            <?php if ($start_page > 1): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['p' => 1])) ?>"
                   class="pagination-number">1</a>
                <?php if ($start_page > 2): ?>
                    <span class="pagination-ellipsis">...</span>
                <?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['p' => $i])) ?>"
                   class="pagination-number <?= $i == $current_page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>

            <?php if ($end_page < $total_pages): ?>
                <?php if ($end_page < $total_pages - 1): ?>
                    <span class="pagination-ellipsis">...</span>
                <?php endif; ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['p' => $total_pages])) ?>"
                   class="pagination-number"><?= $total_pages ?></a>
            <?php endif; ?>

            <?php if ($current_page < $total_pages): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['p' => $current_page + 1])) ?>"
                   class="pagination-btn">
                    <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

    <!-- Delete Confirmation Modal -->
    <div id="requestDeleteModal" style="display: none;">
        <div class="request-modal-overlay" onclick="closeRequestDeleteModal()"></div>
        <div class="request-modal-container">
            <div class="request-modal-header">
                <h3>Xác nhận xóa yêu cầu</h3>
                <button class="request-modal-close" onclick="closeRequestDeleteModal()">&times;</button>
            </div>
            <div class="request-modal-body">
                <p>Bạn có chắc chắn muốn xóa yêu cầu đăng ký đại lý này?</p>
                <p class="request-modal-warning">Hành động này không thể hoàn tác!</p>
            </div>
            <div class="request-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeRequestDeleteModal()">Hủy</button>
                <button type="button" class="btn btn-danger" id="requestConfirmDeleteBtn">Xóa</button>
            </div>
        </div>
    </div>

<style>
/* Requests Page Specific Styles */
.affiliates-requests-page {
    padding: 24px;
}

.affiliates-requests-page .page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.affiliates-requests-page .page-header-left h1 {
    margin: 0 0 4px 0;
    font-size: 24px;
    font-weight: 600;
    color: #333;
}

.affiliates-requests-page .results-count {
    color: #666;
    font-size: 14px;
}

.affiliates-requests-page .user-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.affiliates-requests-page .user-info .small {
    font-size: 12px;
    color: #888;
}

.affiliates-requests-page .contact-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
    font-size: 13px;
}

.affiliates-requests-page .contact-info i {
    width: 16px;
    margin-right: 4px;
    color: #888;
}

.affiliates-requests-page .status-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.affiliates-requests-page .status-pending {
    background: #FEF3C7;
    color: #92400E;
}

.affiliates-requests-page .status-approved {
    background: #D1FAE5;
    color: #065F46;
}

.affiliates-requests-page .status-rejected {
    background: #FEE2E2;
    color: #991B1B;
}

.affiliates-requests-page .status-active {
    background: #D1FAE5;
    color: #065F46;
}

.affiliates-requests-page .action-buttons {
    display: flex;
    gap: 4px;
}

.affiliates-requests-page .action-buttons .btn {
    padding: 6px 8px;
    font-size: 14px;
}

.affiliates-requests-page .no-data {
    text-align: center;
    padding: 40px;
    color: #888;
}

/* Modal Styles */
.affiliates-requests-page .modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    justify-content: center;
    align-items: center;
}

.affiliates-requests-page .modal.show {
    display: flex;
}

.affiliates-requests-page .modal-content {
    background: white;
    padding: 24px;
    border-radius: 8px;
    max-width: 400px;
    width: 90%;
}

.affiliates-requests-page .modal-content h3 {
    margin: 0 0 16px 0;
    font-size: 18px;
}

.affiliates-requests-page .modal-content p {
    margin: 0 0 16px 0;
    color: #666;
}

.affiliates-requests-page .modal-actions {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
}

/* Request Delete Modal */
#requestDeleteModal {
   position: fixed;
   top: 0;
   left: 0;
   width: 100vw;
   height: 100vh;
   z-index: 999999;
}

.request-modal-overlay {
   position: absolute;
   top: 0;
   left: 0;
   width: 100%;
   height: 100%;
   background: rgba(0, 0, 0, 0.6);
}

.request-modal-container {
   position: absolute;
   top: 50%;
   left: 50%;
   transform: translate(-50%, -50%);
   background: white;
   border-radius: 12px;
   width: 90%;
   max-width: 500px;
}

.request-modal-header {
   display: flex;
   justify-content: space-between;
   align-items: center;
   padding: 20px;
   border-bottom: 1px solid #e5e7eb;
}

.request-modal-header h3 {
   margin: 0;
   font-size: 18px;
   font-weight: 600;
   color: #111827;
}

.request-modal-close {
   background: none;
   border: none;
   font-size: 24px;
   color: #9ca3af;
   cursor: pointer;
   padding: 4px;
   border-radius: 4px;
}

.request-modal-close:hover {
   color: #374151;
   background: #f3f4f6;
}

.request-modal-footer {
   display: flex;
   justify-content: flex-end;
   gap: 12px;
   padding: 16px 20px;
   border-top: 1px solid #e5e7eb;
   background: #f9fafb;
   border-radius: 0 0 12px 12px;
}

.request-modal-warning {
   color: #dc2626 !important;
   font-size: 13px;
   font-weight: 500;
}

.request-modal-body {
   padding: 20px;
}

.request-modal-body p {
   margin: 0 0 8px 0;
}
</style>

<script>
let requestDeleteId = null;

// Open delete modal
function confirmDelete(id) {
    requestDeleteId = id;
    const modal = document.getElementById('requestDeleteModal');
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
}

// Close delete modal
function closeRequestDeleteModal() {
    const modal = document.getElementById('requestDeleteModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
    requestDeleteId = null;
}

// Confirm delete button click
document.addEventListener('click', function(e) {
    if (e.target.id === 'requestConfirmDeleteBtn') {
        if (requestDeleteId) {
            // Redirect to delete action (handled in index.php)
            window.location.href = '?page=admin&module=affiliates&action=delete_request&id=' + requestDeleteId;
        }
    }
});

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('requestDeleteModal');
        if (modal && modal.style.display === 'block') {
            closeRequestDeleteModal();
        }
    }
});
</script>