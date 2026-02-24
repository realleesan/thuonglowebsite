<?php
/**
 * Affiliate Customers List
 * Danh sách khách hàng của đại lý
 */

// 1. Khởi tạo View & ServiceManager
require_once __DIR__ . '/../../../../core/view_init.php';

// 2. Chọn service affiliate (được inject từ index.php)
$service = isset($currentService) ? $currentService : ($affiliateService ?? null);

// Initialize data variables
$customers = [];

try {
    if ($service) {
        // Get current affiliate ID from session
        $affiliateId = $_SESSION['user_id'] ?? 0;
        
        // Validate affiliate is logged in
        if ($affiliateId <= 0) {
            throw new Exception('Vui lòng đăng nhập để xem danh sách khách hàng');
        }
        
        // Get dashboard data FIRST for affiliate info (needed by header)
        $dashboardData = $service->getDashboardData($affiliateId);
        $affiliateInfo = $dashboardData['affiliate'] ?? [
            'name' => '',
            'email' => ''
        ];
        
        // Get customers data từ AffiliateService
        $customersData = $service->getCustomersData($affiliateId);
        $customers = $customersData['customers'] ?? [];
    }
} catch (Exception $e) {
    $errorHandler->handleViewError($e, 'affiliate_customers_list', []);
    error_log('Customers List Error: ' . $e->getMessage());
}

// Set page info cho master layout
$page_title = 'Danh sách khách hàng';
$page_module = 'customers';

// Include master layout
ob_start();
?>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-users"></i>
        Danh sách khách hàng
    </h1>
    <p class="page-description">Quản lý và theo dõi khách hàng được giới thiệu</p>
</div>

<!-- Stats Cards -->
<div class="stats-grid stats-grid-4">
    <!-- Tổng khách hàng -->
    <div class="stat-card stat-card-primary">
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Tổng khách hàng</div>
            <div class="stat-value"><?php echo count($customers); ?></div>
        </div>
    </div>

    <!-- Khách hàng active -->
    <div class="stat-card stat-card-success">
        <div class="stat-icon">
            <i class="fas fa-user-check"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Đang hoạt động</div>
            <div class="stat-value">
                <?php 
                $activeCount = count(array_filter($customers, function($c) { 
                    return $c['status'] === 'active'; 
                }));
                echo $activeCount;
                ?>
            </div>
        </div>
    </div>

    <!-- Tổng doanh số -->
    <div class="stat-card stat-card-info">
        <div class="stat-icon">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Tổng doanh số</div>
            <div class="stat-value">
                <?php 
                $totalSpent = array_sum(array_column($customers, 'total_spent'));
                echo number_format($totalSpent);
                ?>đ
            </div>
        </div>
    </div>

    <!-- Tổng hoa hồng -->
    <div class="stat-card stat-card-warning">
        <div class="stat-icon">
            <i class="fas fa-wallet"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Tổng hoa hồng</div>
            <div class="stat-value">
                <?php 
                $totalCommission = array_sum(array_column($customers, 'commission_earned'));
                echo number_format($totalCommission);
                ?>đ
            </div>
        </div>
    </div>
</div>

<!-- Filters & Search -->
<div class="card">
    <div class="card-body">
        <form class="filters-form" id="customersFilterForm">
            <div class="filters-grid filters-grid-4">
                <!-- Search -->
                <div class="filter-item">
                    <label class="filter-label">Tìm kiếm</label>
                    <input type="text" 
                           class="form-control" 
                           id="searchInput" 
                           placeholder="Tên, email, số điện thoại...">
                </div>

                <!-- Status Filter -->
                <div class="filter-item">
                    <label class="filter-label">Trạng thái</label>
                    <select class="form-control" id="statusFilter">
                        <option value="">Tất cả</option>
                        <option value="active">Đang hoạt động</option>
                        <option value="inactive">Không hoạt động</option>
                    </select>
                </div>

                <!-- Sort By -->
                <div class="filter-item">
                    <label class="filter-label">Sắp xếp theo</label>
                    <select class="form-control" id="sortBy">
                        <option value="registered_date_desc">Ngày đăng ký (Mới nhất)</option>
                        <option value="registered_date_asc">Ngày đăng ký (Cũ nhất)</option>
                        <option value="total_spent_desc">Doanh số (Cao nhất)</option>
                        <option value="total_spent_asc">Doanh số (Thấp nhất)</option>
                        <option value="total_orders_desc">Đơn hàng (Nhiều nhất)</option>
                        <option value="total_orders_asc">Đơn hàng (Ít nhất)</option>
                    </select>
                </div>

                <!-- Filter Actions -->
                <div class="filter-item filter-actions">
                    <label class="filter-label">&nbsp;</label>
                    <div class="filter-buttons">
                        <button type="button" class="btn btn-primary" onclick="filterCustomers()">
                            <i class="fas fa-filter"></i>
                            Lọc
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="resetFilters()">
                            <i class="fas fa-redo"></i>
                            Đặt lại
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Customers Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list"></i>
            Danh sách khách hàng
        </h3>
        <div class="card-actions">
            <button type="button" class="btn btn-sm btn-secondary" onclick="exportCustomers()">
                <i class="fas fa-file-excel"></i>
                Xuất Excel
            </button>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($customers)): ?>
            <!-- Empty State -->
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
            <!-- Customers Table -->
            <div class="table-responsive">
                <table class="table" id="customersTable">
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
                        <tr data-customer-id="<?php echo $customer['id']; ?>">
                            <!-- Khách hàng -->
                            <td>
                                <div class="customer-info">
                                    <div class="customer-avatar">
                                        <?php echo strtoupper(substr($customer['name'], 0, 1)); ?>
                                    </div>
                                    <div class="customer-details">
                                        <div class="customer-name"><?php echo htmlspecialchars($customer['name']); ?></div>
                                        <div class="customer-id">ID: <?php echo $customer['id']; ?></div>
                                    </div>
                                </div>
                            </td>

                            <!-- Liên hệ -->
                            <td>
                                <div class="customer-contact">
                                    <div class="contact-item">
                                        <i class="fas fa-envelope"></i>
                                        <?php echo htmlspecialchars($customer['email']); ?>
                                    </div>
                                    <div class="contact-item">
                                        <i class="fas fa-phone"></i>
                                        <?php echo htmlspecialchars($customer['phone']); ?>
                                    </div>
                                </div>
                            </td>

                            <!-- Ngày đăng ký -->
                            <td>
                                <div class="customer-date">
                                    <?php echo date('d/m/Y', strtotime($customer['registered_date'])); ?>
                                </div>
                            </td>

                            <!-- Đơn hàng -->
                            <td>
                                <div class="customer-orders">
                                    <span class="badge badge-info">
                                        <?php echo $customer['total_orders']; ?> đơn
                                    </span>
                                </div>
                            </td>

                            <!-- Doanh số -->
                            <td>
                                <div class="customer-spent">
                                    <?php echo number_format($customer['total_spent']); ?>đ
                                </div>
                            </td>

                            <!-- Hoa hồng -->
                            <td>
                                <div class="customer-commission">
                                    <span class="commission-amount">
                                        <?php echo number_format($customer['commission_earned']); ?>đ
                                    </span>
                                </div>
                            </td>

                            <!-- Trạng thái -->
                            <td>
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

                            <!-- Thao tác -->
                            <td>
                                <div class="table-actions">
                                    <a href="?page=affiliate&module=customers&action=detail&id=<?php echo $customer['id']; ?>" 
                                       class="btn btn-sm btn-primary"
                                       title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4"><strong>Tổng cộng</strong></td>
                            <td><strong><?php echo number_format($totalSpent); ?>đ</strong></td>
                            <td><strong><?php echo number_format($totalCommission); ?>đ</strong></td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination-container">
                <div class="pagination-info">
                    Hiển thị <strong>1-<?php echo count($customers); ?></strong> trong tổng số <strong><?php echo count($customers); ?></strong> khách hàng
                </div>
                <div class="pagination">
                    <button class="pagination-btn" disabled>
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="pagination-btn active">1</button>
                    <button class="pagination-btn" disabled>
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../_layout/affiliate_master.php';
?>
