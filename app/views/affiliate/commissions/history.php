<?php
/**
 * Affiliate Commissions History
 * Lịch sử hoa hồng với phân biệt Subscription vs Logistics
 */

// 1. Khởi tạo View & ServiceManager
require_once __DIR__ . '/../../../../core/view_init.php';

// 2. Chọn service affiliate (được inject từ index.php)
$service = isset($currentService) ? $currentService : ($affiliateService ?? null);

// Initialize data variables
$history = [];
$overview = [];

try {
    if ($service) {
        // Get current affiliate ID from session
        $affiliateId = $_SESSION['user_id'] ?? 0;
        
        // Validate affiliate is logged in
        if ($affiliateId <= 0) {
            throw new Exception('Vui lòng đăng nhập để xem lịch sử hoa hồng');
        }
        
        // Get dashboard data FIRST for affiliate info (needed by header)
        $dashboardData = $service->getDashboardData($affiliateId);
        $affiliateInfo = $dashboardData['affiliate'] ?? [
            'name' => '',
            'email' => ''
        ];
        
        $history = $dashboardData['recent_customers'] ?? [];
        $overview = [
            'total_commission' => $dashboardData['stats']['total_commission'] ?? 0,
            'pending_commission' => $dashboardData['stats']['pending_commission'] ?? 0,
            'paid_commission' => $dashboardData['stats']['paid_commission'] ?? 0
        ];
    }
} catch (Exception $e) {
    $errorHandler->handleViewError($e, 'affiliate_commissions_history', []);
    error_log("Commission History Error: " . $e->getMessage());
}

// Set page info cho master layout
$page_title = 'Lịch sử hoa hồng';
$page_module = 'commissions';
$page_action = 'history';

ob_start();
?>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-history"></i>
        Lịch sử hoa hồng
    </h1>
    <p class="page-subtitle">Theo dõi chi tiết các giao dịch hoa hồng của bạn</p>
</div>

<!-- Filters Section -->
<div class="card">
    <div class="card-body">
        <form class="filters-form" id="commissionsFilters">
            <div class="filter-group">
                <!-- Month Filter -->
                <div class="filter-item">
                    <label class="filter-label">Tháng</label>
                    <select class="form-control" name="month" id="filterMonth">
                        <option value="">Tất cả</option>
                        <option value="01">Tháng 1</option>
                        <option value="02" selected>Tháng 2</option>
                        <option value="03">Tháng 3</option>
                        <option value="04">Tháng 4</option>
                        <option value="05">Tháng 5</option>
                        <option value="06">Tháng 6</option>
                        <option value="07">Tháng 7</option>
                        <option value="08">Tháng 8</option>
                        <option value="09">Tháng 9</option>
                        <option value="10">Tháng 10</option>
                        <option value="11">Tháng 11</option>
                        <option value="12">Tháng 12</option>
                    </select>
                </div>

                <!-- Year Filter -->
                <div class="filter-item">
                    <label class="filter-label">Năm</label>
                    <select class="form-control" name="year" id="filterYear">
                        <option value="">Tất cả</option>
                        <?php 
                        $currentYear = date('Y');
                        for ($year = $currentYear; $year >= $currentYear - 5; $year--) {
                            $selected = ($year == $currentYear) ? 'selected' : '';
                            echo "<option value=\"$year\" $selected>$year</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="filter-item">
                    <label class="filter-label">Trạng thái</label>
                    <select class="form-control" name="status" id="filterStatus">
                        <option value="">Tất cả</option>
                        <option value="paid">Đã thanh toán</option>
                        <option value="pending">Chờ thanh toán</option>
                        <option value="cancelled">Đã hủy</option>
                    </select>
                </div>

                <!-- Product Type Filter -->
                <div class="filter-item">
                    <label class="filter-label">Loại sản phẩm</label>
                    <select class="form-control" name="product_type" id="filterProductType">
                        <option value="">Tất cả</option>
                        <option value="data_subscription">Gói Data</option>
                        <option value="logistics_service">Vận chuyển</option>
                    </select>
                </div>

                <!-- Filter Actions -->
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i>
                        Lọc
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="resetFilters()">
                        <i class="fas fa-redo"></i>
                        Đặt lại
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Commissions History Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list"></i>
            Danh sách giao dịch
        </h3>
        <div class="card-actions">
            <button class="btn btn-sm btn-secondary" onclick="exportCommissions()">
                <i class="fas fa-download"></i>
                Xuất Excel
            </button>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($history)): ?>
            <!-- Empty State -->
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-inbox"></i>
                </div>
                <h3 class="empty-state-title">Chưa có giao dịch nào</h3>
                <p class="empty-state-text">
                    Các giao dịch hoa hồng của bạn sẽ hiển thị tại đây
                </p>
            </div>
        <?php else: ?>
            <!-- Table -->
            <div class="table-responsive">
                <table class="table" id="commissionsTable">
                    <thead>
                        <tr>
                            <th>Ngày</th>
                            <th>Nguồn</th>
                            <th>Mô tả</th>
                            <th>Khách hàng</th>
                            <th class="text-right">Doanh số</th>
                            <th class="text-right">Hoa hồng</th>
                            <th>Trạng thái</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $item): ?>
                        <tr data-status="<?php echo $item['status']; ?>" 
                            data-type="<?php echo $item['product_type']; ?>"
                            data-date="<?php echo $item['date']; ?>">
                            <!-- Ngày -->
                            <td>
                                <span class="text-nowrap">
                                    <?php echo date('d/m/Y', strtotime($item['date'])); ?>
                                </span>
                            </td>

                            <!-- Nguồn (Product Type) -->
                            <td>
                                <?php if ($item['product_type'] === 'data_subscription'): ?>
                                    <span class="badge badge-purple">
                                        <i class="fas fa-database"></i>
                                        Gói Data
                                    </span>
                                <?php elseif ($item['product_type'] === 'logistics_service'): ?>
                                    <span class="badge badge-orange">
                                        <i class="fas fa-truck"></i>
                                        Vận chuyển
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Mô tả -->
                            <td>
                                <div class="commission-description">
                                    <div class="description-text">
                                        <?php echo htmlspecialchars($item['description']); ?>
                                    </div>
                                    <div class="description-meta">
                                        Mã: <?php echo htmlspecialchars($item['order_id']); ?>
                                    </div>
                                </div>
                            </td>

                            <!-- Khách hàng -->
                            <td>
                                <?php echo htmlspecialchars($item['customer_name']); ?>
                            </td>

                            <!-- Doanh số -->
                            <td class="text-right">
                                <span class="text-muted">
                                    <?php echo number_format($item['order_amount']); ?>đ
                                </span>
                            </td>

                            <!-- Hoa hồng -->
                            <td class="text-right">
                                <span class="commission-amount">
                                    <?php echo number_format($item['commission_amount']); ?>đ
                                </span>
                                <div class="commission-rate">
                                    <?php echo $item['commission_rate']; ?>%
                                </div>
                            </td>

                            <!-- Trạng thái -->
                            <td>
                                <?php if ($item['status'] === 'paid'): ?>
                                    <span class="badge badge-success">
                                        <i class="fas fa-check-circle"></i>
                                        Đã thanh toán
                                    </span>
                                <?php elseif ($item['status'] === 'pending'): ?>
                                    <span class="badge badge-warning">
                                        <i class="fas fa-clock"></i>
                                        Chờ thanh toán
                                    </span>
                                <?php elseif ($item['status'] === 'cancelled'): ?>
                                    <span class="badge badge-danger">
                                        <i class="fas fa-times-circle"></i>
                                        Đã hủy
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Thao tác -->
                            <td class="text-center">
                                <button class="btn btn-sm btn-secondary" 
                                        onclick="viewCommissionDetail(<?php echo $item['id']; ?>)"
                                        title="Xem chi tiết">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-summary">
                            <td colspan="5" class="text-right fw-semibold">Tổng cộng:</td>
                            <td class="text-right fw-bold">
                                <?php 
                                $totalCommission = array_sum(array_column($history, 'commission_amount'));
                                echo number_format($totalCommission); 
                                ?>đ
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination-wrapper">
                <div class="pagination-info">
                    Hiển thị <strong>1-<?php echo count($history); ?></strong> 
                    trong tổng số <strong><?php echo count($history); ?></strong> giao dịch
                </div>
                <nav class="pagination">
                    <button class="btn btn-sm btn-secondary" disabled>
                        <i class="fas fa-chevron-left"></i>
                        Trước
                    </button>
                    <button class="btn btn-sm btn-primary">1</button>
                    <button class="btn btn-sm btn-secondary" disabled>
                        Sau
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- JavaScript for Filters -->
<script>
// Filter form submission
document.getElementById('commissionsFilters')?.addEventListener('submit', function(e) {
    e.preventDefault();
    filterCommissions();
});

function filterCommissions() {
    const month = document.getElementById('filterMonth').value;
    const year = document.getElementById('filterYear').value;
    const status = document.getElementById('filterStatus').value;
    const productType = document.getElementById('filterProductType').value;
    
    const rows = document.querySelectorAll('#commissionsTable tbody tr');
    
    rows.forEach(row => {
        const rowDate = row.dataset.date;
        const rowStatus = row.dataset.status;
        const rowType = row.dataset.type;
        
        let showRow = true;
        
        // Filter by month/year
        if (month && !rowDate.includes(`-${month}-`)) {
            showRow = false;
        }
        if (year && !rowDate.startsWith(year)) {
            showRow = false;
        }
        
        // Filter by status
        if (status && rowStatus !== status) {
            showRow = false;
        }
        
        // Filter by product type
        if (productType && rowType !== productType) {
            showRow = false;
        }
        
        row.style.display = showRow ? '' : 'none';
    });
    
    showAlert('Đã áp dụng bộ lọc', 'success');
}

function resetFilters() {
    document.getElementById('commissionsFilters').reset();
    const rows = document.querySelectorAll('#commissionsTable tbody tr');
    rows.forEach(row => row.style.display = '');
    showAlert('Đã đặt lại bộ lọc', 'info');
}

function viewCommissionDetail(id) {
    // TODO: Implement AJAX call to fetch commission details
    console.log('View commission detail:', id);
    showAlert('Chi tiết hoa hồng #' + id, 'info');
}

function exportCommissions() {
    // TODO: Implement Excel export via API
    console.log('Export commissions');
    showAlert('Tính năng xuất Excel sẽ sớm khả dụng', 'info');
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../_layout/affiliate_master.php';
?>
