<?php
// Load Models
require_once __DIR__ . '/../../../models/AffiliateModel.php';
require_once __DIR__ . '/../../../models/UsersModel.php';

$affiliateModel = new AffiliateModel();
$usersModel = new UsersModel();

// Search and filter parameters
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$current_page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;

// Build query conditions
$conditions = [];
$bindings = [];

if (!empty($search)) {
    $conditions[] = "(u.name LIKE ? OR u.email LIKE ? OR a.code LIKE ?)";
    $searchTerm = "%{$search}%";
    $bindings = array_merge($bindings, [$searchTerm, $searchTerm, $searchTerm]);
}

if (!empty($status_filter)) {
    $conditions[] = "a.status = ?";
    $bindings[] = $status_filter;
}

// Get total count for pagination
$countSql = "SELECT COUNT(*) as total FROM affiliates a LEFT JOIN users u ON a.user_id = u.id";
if (!empty($conditions)) {
    $countSql .= " WHERE " . implode(' AND ', $conditions);
}
$totalResult = $affiliateModel->db->query($countSql, $bindings);
$total_affiliates = $totalResult[0]['total'] ?? 0;

// Calculate pagination
$total_pages = ceil($total_affiliates / $per_page);
$current_page = max(1, min($total_pages, $current_page));
$offset = ($current_page - 1) * $per_page;

// Get affiliates with user info
$sql = "
    SELECT a.*, u.name as user_name, u.email as user_email
    FROM affiliates a
    LEFT JOIN users u ON a.user_id = u.id
";
if (!empty($conditions)) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}
$sql .= " ORDER BY a.created_at DESC LIMIT {$per_page} OFFSET {$offset}";

$affiliates = $affiliateModel->db->query($sql, $bindings);

// Create user lookup
$user_lookup = [];
foreach ($users as $user) {
    $user_lookup[$user['id']] = $user;
}

// Search and filter
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';

// Filter affiliates
$filtered_affiliates = $affiliates;

if (!empty($search)) {
    $filtered_affiliates = array_filter($filtered_affiliates, function($affiliate) use ($search, $user_lookup) {
        $user = $user_lookup[$affiliate['user_id']] ?? null;
        return ($user && (stripos($user['name'], $search) !== false || 
                         stripos($user['email'], $search) !== false)) ||
               stripos($affiliate['referral_code'], $search) !== false;
    });
}

if (!empty($status_filter)) {
    $filtered_affiliates = array_filter($filtered_affiliates, function($affiliate) use ($status_filter) {
        return $affiliate['status'] == $status_filter;
    });
}

// Pagination
$per_page = 10;
$total_affiliates = count($filtered_affiliates);
$total_pages = ceil($total_affiliates / $per_page);
$current_page = max(1, min($total_pages, (int)($_GET['page'] ?? 1)));
$offset = ($current_page - 1) * $per_page;
$paged_affiliates = array_slice($filtered_affiliates, $offset, $per_page);

// Format price function
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' VNĐ';
}

// Format date function
function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}
?>

<div class="affiliates-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-handshake"></i>
                Quản Lý Đại Lý
            </h1>
            <p class="page-description">Quản lý danh sách đại lý và hoa hồng của hệ thống</p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=affiliates&action=add" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Thêm Đại Lý
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-section">
        <form method="GET" class="filters-form">
            <input type="hidden" name="page" value="admin">
            <input type="hidden" name="module" value="affiliates">
            
            <div class="filter-group">
                <div class="filter-item">
                    <label for="search">Tìm kiếm:</label>
                    <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Tên đại lý, email, mã giới thiệu...">
                </div>
                
                <div class="filter-item">
                    <label for="status">Trạng thái:</label>
                    <select id="status" name="status">
                        <option value="">Tất cả trạng thái</option>
                        <option value="active" <?= $status_filter == 'active' ? 'selected' : '' ?>>Hoạt động</option>
                        <option value="inactive" <?= $status_filter == 'inactive' ? 'selected' : '' ?>>Không hoạt động</option>
                        <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>Chờ duyệt</option>
                    </select>
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-search"></i>
                        Lọc
                    </button>
                    <a href="?page=admin&module=affiliates" class="btn btn-outline">
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
            Hiển thị <?= count($paged_affiliates) ?> trong tổng số <?= $total_affiliates ?> đại lý
        </span>
        
        <!-- Bulk Actions -->
        <div class="bulk-actions">
            <select id="bulk-action" disabled>
                <option value="">Hành động hàng loạt</option>
                <option value="activate">Kích hoạt</option>
                <option value="deactivate">Vô hiệu hóa</option>
                <option value="delete">Xóa</option>
            </select>
            <button type="button" id="apply-bulk" class="btn btn-secondary" disabled>
                Áp dụng
            </button>
        </div>
    </div>

    <!-- Affiliates Table -->
    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th width="40">
                        <input type="checkbox" id="select-all">
                    </th>
                    <th width="60">ID</th>
                    <th>Tên đại lý</th>
                    <th width="150">Email</th>
                    <th width="120">Mã giới thiệu</th>
                    <th width="100">Hoa hồng (%)</th>
                    <th width="130">Tổng doanh số</th>
                    <th width="130">Tổng hoa hồng</th>
                    <th width="100">Trạng thái</th>
                    <th width="120">Ngày tham gia</th>
                    <th width="120">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($paged_affiliates)): ?>
                    <tr>
                        <td colspan="11" class="no-data">
                            <i class="fas fa-inbox"></i>
                            <p>Không tìm thấy đại lý nào</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($paged_affiliates as $affiliate): ?>
                        <?php $user = $user_lookup[$affiliate['user_id']] ?? null; ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="affiliate-checkbox" value="<?= $affiliate['id'] ?>">
                            </td>
                            <td><?= $affiliate['id'] ?></td>
                            <td>
                                <div class="affiliate-info">
                                    <h4 class="affiliate-name"><?= htmlspecialchars($user['name'] ?? 'N/A') ?></h4>
                                    <p class="affiliate-phone"><?= htmlspecialchars($user['phone'] ?? '') ?></p>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($user['email'] ?? 'N/A') ?></td>
                            <td>
                                <span class="referral-code">
                                    <?= htmlspecialchars($affiliate['referral_code']) ?>
                                </span>
                            </td>
                            <td class="commission-rate">
                                <span class="rate-badge">
                                    <?= $affiliate['commission_rate'] ?>%
                                </span>
                            </td>
                            <td class="sales-cell">
                                <?= formatPrice($affiliate['total_sales']) ?>
                            </td>
                            <td class="commission-cell">
                                <?= formatPrice($affiliate['total_commission']) ?>
                            </td>
                            <td>
                                <span class="status-badge status-<?= $affiliate['status'] ?>">
                                    <?php
                                    switch($affiliate['status']) {
                                        case 'active': echo 'Hoạt động'; break;
                                        case 'inactive': echo 'Không hoạt động'; break;
                                        case 'pending': echo 'Chờ duyệt'; break;
                                        default: echo 'N/A';
                                    }
                                    ?>
                                </span>
                            </td>
                            <td><?= formatDate($affiliate['created_at']) ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="?page=admin&module=affiliates&action=view&id=<?= $affiliate['id'] ?>" 
                                       class="btn btn-sm btn-info" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="?page=admin&module=affiliates&action=edit&id=<?= $affiliate['id'] ?>" 
                                       class="btn btn-sm btn-warning" title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger delete-btn" 
                                            data-id="<?= $affiliate['id'] ?>" data-name="<?= htmlspecialchars($user['name'] ?? 'N/A') ?>" 
                                            title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </button>
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
        <div class="pagination-container">
            <div class="pagination">
                <?php if ($current_page > 1): ?>
                    <a href="?page=admin&module=affiliates&<?= http_build_query(array_merge($_GET, ['page' => $current_page - 1])) ?>" 
                       class="pagination-btn">
                        <i class="fas fa-chevron-left"></i>
                        Trước
                    </a>
                <?php endif; ?>

                <?php
                $start_page = max(1, $current_page - 2);
                $end_page = min($total_pages, $current_page + 2);
                
                if ($start_page > 1): ?>
                    <a href="?page=admin&module=affiliates&<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>" 
                       class="pagination-number">1</a>
                    <?php if ($start_page > 2): ?>
                        <span class="pagination-dots">...</span>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <a href="?page=admin&module=affiliates&<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
                       class="pagination-number <?= $i == $current_page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($end_page < $total_pages): ?>
                    <?php if ($end_page < $total_pages - 1): ?>
                        <span class="pagination-dots">...</span>
                    <?php endif; ?>
                    <a href="?page=admin&module=affiliates&<?= http_build_query(array_merge($_GET, ['page' => $total_pages])) ?>" 
                       class="pagination-number"><?= $total_pages ?></a>
                <?php endif; ?>

                <?php if ($current_page < $total_pages): ?>
                    <a href="?page=admin&module=affiliates&<?= http_build_query(array_merge($_GET, ['page' => $current_page + 1])) ?>" 
                       class="pagination-btn">
                        Sau
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
            
            <div class="pagination-info">
                Trang <?= $current_page ?> / <?= $total_pages ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Xác nhận xóa</h3>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa đại lý <strong id="deleteAffiliateName"></strong>?</p>
                <p class="text-danger">Hành động này không thể hoàn tác!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelDelete">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Xóa</button>
            </div>
        </div>
    </div>
</div>