<?php
/**
 * Affiliate Customers List
 * Danh sách khách hàng của đại lý
 * Design: Synchronized with Admin
 */

require_once __DIR__ . '/../../../../core/view_init.php';

$service = isset($currentService) ? $currentService : ($affiliateService ?? null);

$customers = [];
$stats = ['total' => 0, 'active' => 0, 'total_spent' => 0, 'total_commission' => 0];
$pagination = ['current_page' => 1, 'total_pages' => 1, 'total' => 0];
$search = '';
$status_filter = '';
$sort_by = 'registered_date_desc';

try {
    if ($service) {
        $affiliateId = $_SESSION['user_id'] ?? 0;
        
        if ($affiliateId <= 0) {
            header('Location: ?page=auth&module=login');
            exit;
        }
        
        // Get filter params
        $search = $_GET['search'] ?? '';
        $status_filter = $_GET['status'] ?? '';
        $sort_by = $_GET['sort'] ?? 'registered_date_desc';
        $current_page = max(1, (int)($_GET['page'] ?? 1));
        $per_page = 10;
        
        // Get dashboard data for affiliate info
        $dashboardData = $service->getDashboardData($affiliateId);
        $affiliateInfo = $dashboardData['affiliate'] ?? ['name' => '', 'email' => ''];
        
        // Get customers data
        $customersData = $service->getCustomersData($affiliateId, [
            'search' => $search,
            'status' => $status_filter,
            'sort' => $sort_by,
            'page' => $current_page,
            'per_page' => $per_page
        ]);
        
        $customers = $customersData['customers'] ?? [];
        $stats = $customersData['stats'] ?? $stats;
        $pagination = $customersData['pagination'] ?? $pagination;
    }
} catch (Exception $e) {
    error_log('Customers List Error: ' . $e->getMessage());
}

$page_title = 'Danh sách khách hàng';
$page_module = 'customers';

ob_start();
?>

<div class="customers-page">

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card stat-card-primary">
            <div class="stat-card-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-card-content">
                <div class="stat-card-label">Tổng khách hàng</div>
                <div class="stat-card-value"><?= $stats['total'] ?></div>
            </div>
        </div>
        <div class="stat-card stat-card-success">
            <div class="stat-card-icon">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="stat-card-content">
                <div class="stat-card-label">Đang hoạt động</div>
                <div class="stat-card-value"><?= $stats['active'] ?></div>
            </div>
        </div>
        <div class="stat-card stat-card-info">
            <div class="stat-card-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-card-content">
                <div class="stat-card-label">Tổng doanh số</div>
                <div class="stat-card-value"><?= number_format($stats['total_spent']) ?>đ</div>
            </div>
        </div>
        <div class="stat-card stat-card-warning">
            <div class="stat-card-icon">
                <i class="fas fa-wallet"></i>
            </div>
            <div class="stat-card-content">
                <div class="stat-card-label">Tổng hoa hồng</div>
                <div class="stat-card-value"><?= number_format($stats['total_commission']) ?>đ</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-section">
        <form method="GET" class="filters-form">
            <input type="hidden" name="page" value="affiliate">
            <input type="hidden" name="module" value="customers">
            
            <div class="filter-group">
                <div class="filter-item">
                    <label for="search">Tìm kiếm</label>
                    <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Tên, email, số điện thoại...">
                </div>
                
                <div class="filter-item">
                    <label for="status">Trạng thái</label>
                    <select id="status" name="status">
                        <option value="">Tất cả</option>
                        <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>Đang hoạt động</option>
                        <option value="inactive" <?= $status_filter === 'inactive' ? 'selected' : '' ?>>Không hoạt động</option>
                    </select>
                </div>
                
                <div class="filter-item">
                    <label for="sort">Sắp xếp</label>
                    <select id="sort" name="sort">
                        <option value="registered_date_desc" <?= $sort_by === 'registered_date_desc' ? 'selected' : '' ?>>Ngày đăng ký (Mới nhất)</option>
                        <option value="registered_date_asc" <?= $sort_by === 'registered_date_asc' ? 'selected' : '' ?>>Ngày đăng ký (Cũ nhất)</option>
                        <option value="total_spent_desc" <?= $sort_by === 'total_spent_desc' ? 'selected' : '' ?>>Doanh số (Cao nhất)</option>
                        <option value="total_spent_asc" <?= $sort_by === 'total_spent_asc' ? 'selected' : '' ?>>Doanh số (Thấp nhất)</option>
                        <option value="total_orders_desc" <?= $sort_by === 'total_orders_desc' ? 'selected' : '' ?>>Đơn hàng (Nhiều nhất)</option>
                        <option value="total_orders_asc" <?= $sort_by === 'total_orders_asc' ? 'selected' : '' ?>>Đơn hàng (Ít nhất)</option>
                    </select>
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-search"></i>
                        Lọc
                    </button>
                    <a href="?page=affiliate&module=customers" class="btn btn-outline">
                        <i class="fas fa-times"></i>
                        Đặt lại
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Results Info -->
    <div class="results-info">
        <span class="results-count">
            Hiển thị <?= count($customers) ?> trong tổng số <?= $pagination['total'] ?> khách hàng
        </span>
    </div>

    <!-- Customers Table -->
    <div class="table-container">
        <?php if (empty($customers)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3 class="empty-state-title">Chưa có khách hàng nào</h3>
                <p class="empty-state-description">
                    Bạn chưa có khách hàng nào được giới thiệu. Hãy chia sẻ link affiliate của bạn để bắt đầu!
                </p>
                <a href="?page=affiliate&module=marketing&action=tools" class="btn btn-primary">
                    <i class="fas fa-link"></i>
                    Xem Link Affiliate
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table customers-table">
                    <thead>
                        <tr>
                            <th>Khách hàng</th>
                            <th>Liên hệ</th>
                            <th>Ngày đăng ký</th>
                            <th>Đơn hàng</th>
                            <th>Doanh số</th>
                            <th>Hoa hồng</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $customer): ?>
                        <tr data-customer-id="<?= $customer['id'] ?>">
                            <td data-label="Khách hàng">
                                <div class="customer-info">
                                    <div class="customer-avatar">
                                        <?= strtoupper(substr($customer['name'], 0, 1)) ?>
                                    </div>
                                    <div class="customer-details">
                                        <div class="customer-name"><?= htmlspecialchars($customer['name']) ?></div>
                                        <div class="customer-id">ID: <?= $customer['id'] ?></div>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Liên hệ">
                                <div class="customer-contact">
                                    <div class="contact-item">
                                        <i class="fas fa-envelope"></i>
                                        <?= htmlspecialchars($customer['email']) ?>
                                    </div>
                                    <div class="contact-item">
                                        <i class="fas fa-phone"></i>
                                        <?= htmlspecialchars($customer['phone']) ?>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Ngày đăng ký">
                                <div class="customer-date">
                                    <?= date('d/m/Y', strtotime($customer['registered_date'])) ?>
                                </div>
                            </td>
                            <td data-label="Đơn hàng">
                                <div class="customer-orders">
                                    <span class="badge badge-info">
                                        <?= $customer['total_orders'] ?> đơn
                                    </span>
                                </div>
                            </td>
                            <td data-label="Doanh số">
                                <div class="customer-spent">
                                    <?= number_format($customer['total_spent']) ?>đ
                                </div>
                            </td>
                            <td data-label="Hoa hồng">
                                <div class="customer-commission">
                                    <span class="commission-amount">
                                        <?= number_format($customer['commission_earned']) ?>đ
                                    </span>
                                </div>
                            </td>
                            <td data-label="Trạng thái">
                                <?php if ($customer['status'] === 'active'): ?>
                                    <span class="badge badge-success">
                                        <i class="fas fa-check-circle"></i>
                                        Hoạt động
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">
                                        <i class="fas fa-pause-circle"></i>
                                        Không hoạt động
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Thao tác" class="table-actions">
                                <a href="?page=affiliate&module=customers&action=detail&id=<?= $customer['id'] ?>" 
                                   class="btn-icon btn-primary"
                                   title="Xem chi tiết">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4"><strong>Tổng cộng</strong></td>
                            <td><strong><?= number_format($stats['total_spent']) ?>đ</strong></td>
                            <td><strong><?= number_format($stats['total_commission']) ?>đ</strong></td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($pagination['total_pages'] > 1): ?>
                <div class="pagination-container">
                    <div class="pagination">
                        <?php if ($pagination['current_page'] > 1): ?>
                            <a href="?page=affiliate&module=customers&<?= http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] - 1])) ?>" 
                               class="pagination-btn">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>

                        <?php
                        $start_page = max(1, $pagination['current_page'] - 2);
                        $end_page = min($pagination['total_pages'], $pagination['current_page'] + 2);
                        
                        if ($start_page > 1): ?>
                            <a href="?page=affiliate&module=customers&<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>" 
                               class="pagination-number">1</a>
                            <?php if ($start_page > 2): ?>
                                <span class="pagination-dots">...</span>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <a href="?page=affiliate&module=customers&<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
                               class="pagination-number <?= $i == $pagination['current_page'] ? 'active' : '' ?>"><?= $i ?></a>
                        <?php endfor; ?>

                        <?php if ($end_page < $pagination['total_pages']): ?>
                            <?php if ($end_page < $pagination['total_pages'] - 1): ?>
                                <span class="pagination-dots">...</span>
                            <?php endif; ?>
                            <a href="?page=affiliate&module=customers&<?= http_build_query(array_merge($_GET, ['page' => $pagination['total_pages']])) ?>" 
                               class="pagination-number"><?= $pagination['total_pages'] ?></a>
                        <?php endif; ?>

                        <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                            <a href="?page=affiliate&module=customers&<?= http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] + 1])) ?>" 
                               class="pagination-btn">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <div class="pagination-info">
                        Trang <?= $pagination['current_page'] ?> / <?= $pagination['total_pages'] ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../_layout/affiliate_master.php';
?>
