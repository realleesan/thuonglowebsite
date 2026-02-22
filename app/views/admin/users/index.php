<?php
/**
 * Admin Users Index
 * Sử dụng AdminService thông qua ServiceManager
 */

// Chọn service admin (được inject từ index.php)
$service = isset($currentService) ? $currentService : ($adminService ?? null);

try {
    // Search and filter parameters
    $search = $_GET['search'] ?? '';
    $role_filter = $_GET['role'] ?? '';
    $status_filter = $_GET['status'] ?? '';
    $current_page = max(1, (int)($_GET['page'] ?? 1));
    $per_page = 10;
    
    // Prepare filters for AdminService
    $filters = [
        'search' => $search,
        'role' => $role_filter,
        'status' => $status_filter
    ];
    
    // Get users data using AdminService
    $usersData = $service->getUsersData($current_page, $per_page, $filters);
    
    $paged_users = $usersData['users'];
    $total_users = $usersData['total'];
    $pagination = $usersData['pagination'];
    $total_pages = $pagination['last_page'];
    
} catch (Exception $e) {
    $errorHandler->logError('Admin Users Index', $e->getMessage());
    $paged_users = [];
    $total_users = 0;
    $total_pages = 1;
    $current_page = 1;
}

// Format date function
function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}

// Get role display name
function getRoleDisplayName($role) {
    $roles = [
        'admin' => 'Quản trị viên',
        'user' => 'Người dùng',
        'agent' => 'Đại lý'
    ];
    return $roles[$role] ?? $role;
}
?>

<div class="users-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-users"></i>
                Quản Lý Người Dùng
            </h1>
            <p class="page-description">Quản lý danh sách người dùng của hệ thống</p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=users&action=add" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Thêm Người Dùng
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-section">
        <form method="GET" class="filters-form">
            <input type="hidden" name="page" value="admin">
            <input type="hidden" name="module" value="users">
            
            <div class="filter-group">
                <div class="filter-item">
                    <label for="search">Tìm kiếm:</label>
                    <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Tên, email, số điện thoại...">
                </div>
                
                <div class="filter-item">
                    <label for="role">Vai trò:</label>
                    <select id="role" name="role">
                        <option value="">Tất cả vai trò</option>
                        <option value="admin" <?= $role_filter == 'admin' ? 'selected' : '' ?>>Quản trị viên</option>
                        <option value="user" <?= $role_filter == 'user' ? 'selected' : '' ?>>Người dùng</option>
                        <option value="agent" <?= $role_filter == 'agent' ? 'selected' : '' ?>>Đại lý</option>
                    </select>
                </div>
                
                <div class="filter-item">
                    <label for="status">Trạng thái:</label>
                    <select id="status" name="status">
                        <option value="">Tất cả trạng thái</option>
                        <option value="active" <?= $status_filter == 'active' ? 'selected' : '' ?>>Hoạt động</option>
                        <option value="inactive" <?= $status_filter == 'inactive' ? 'selected' : '' ?>>Không hoạt động</option>
                    </select>
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-search"></i>
                        Lọc
                    </button>
                    <a href="?page=admin&module=users" class="btn btn-outline">
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
            Hiển thị <?= count($paged_users) ?> trong tổng số <?= $total_users ?> người dùng
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

    <!-- Users Table -->
    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th width="40">
                        <input type="checkbox" id="select-all">
                    </th>
                    <th width="60">ID</th>
                    <th width="80">Avatar</th>
                    <th>Tên người dùng</th>
                    <th width="200">Email</th>
                    <th width="120">Số điện thoại</th>
                    <th width="120">Vai trò</th>
                    <th width="100">Trạng thái</th>
                    <th width="120">Ngày tạo</th>
                    <th width="120">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($paged_users)): ?>
                    <tr>
                        <td colspan="10" class="no-data">
                            <i class="fas fa-inbox"></i>
                            <p>Không tìm thấy người dùng nào</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($paged_users as $user): ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="user-checkbox" value="<?= $user['id'] ?>">
                            </td>
                            <td><?= $user['id'] ?></td>
                            <td>
                                <div class="user-avatar">
                                    <div class="avatar-circle">
                                        <?= strtoupper(substr($user['name'], 0, 2)) ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="user-info">
                                    <h4 class="user-name"><?= htmlspecialchars($user['name']) ?></h4>
                                    <p class="user-address"><?= htmlspecialchars($user['address']) ?></p>
                                </div>
                            </td>
                            <td>
                                <a href="mailto:<?= htmlspecialchars($user['email']) ?>" class="email-link">
                                    <?= htmlspecialchars($user['email']) ?>
                                </a>
                            </td>
                            <td>
                                <a href="tel:<?= htmlspecialchars($user['phone']) ?>" class="phone-link">
                                    <?= htmlspecialchars($user['phone']) ?>
                                </a>
                            </td>
                            <td>
                                <span class="role-badge role-<?= $user['role'] ?>">
                                    <?= getRoleDisplayName($user['role']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-<?= $user['status'] ?>">
                                    <?= $user['status'] == 'active' ? 'Hoạt động' : 'Không hoạt động' ?>
                                </span>
                            </td>
                            <td><?= formatDate($user['created_at']) ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="?page=admin&module=users&action=view&id=<?= $user['id'] ?>" 
                                       class="btn btn-sm btn-info" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="?page=admin&module=users&action=edit&id=<?= $user['id'] ?>" 
                                       class="btn btn-sm btn-warning" title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger delete-btn" 
                                            data-id="<?= $user['id'] ?>" data-name="<?= htmlspecialchars($user['name']) ?>" 
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
                    <a href="?page=admin&module=users&<?= http_build_query(array_merge($_GET, ['page' => $current_page - 1])) ?>" 
                       class="pagination-btn">
                        <i class="fas fa-chevron-left"></i>
                        Trước
                    </a>
                <?php endif; ?>

                <?php
                $start_page = max(1, $current_page - 2);
                $end_page = min($total_pages, $current_page + 2);
                
                if ($start_page > 1): ?>
                    <a href="?page=admin&module=users&<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>" 
                       class="pagination-number">1</a>
                    <?php if ($start_page > 2): ?>
                        <span class="pagination-dots">...</span>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <a href="?page=admin&module=users&<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
                       class="pagination-number <?= $i == $current_page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($end_page < $total_pages): ?>
                    <?php if ($end_page < $total_pages - 1): ?>
                        <span class="pagination-dots">...</span>
                    <?php endif; ?>
                    <a href="?page=admin&module=users&<?= http_build_query(array_merge($_GET, ['page' => $total_pages])) ?>" 
                       class="pagination-number"><?= $total_pages ?></a>
                <?php endif; ?>

                <?php if ($current_page < $total_pages): ?>
                    <a href="?page=admin&module=users&<?= http_build_query(array_merge($_GET, ['page' => $current_page + 1])) ?>" 
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
                <p>Bạn có chắc chắn muốn xóa người dùng <strong id="deleteUserName"></strong>?</p>
                <p class="text-danger">Hành động này không thể hoàn tác!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelDelete">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Xóa</button>
            </div>
        </div>
    </div>
</div>