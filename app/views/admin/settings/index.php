<?php
/**
 * Admin Settings Index
 * Sử dụng AdminService thông qua ServiceManager
 */

// Chọn service admin (được inject từ index.php)
$service = isset($currentService) ? $currentService : ($adminService ?? null);

try {
    // Get filters
    $search = $_GET['search'] ?? '';
    $type_filter = $_GET['type'] ?? '';
    $current_page = max(1, (int)($_GET['page'] ?? 1));
    $per_page = 10;
    
    $filters = [
        'search' => $search,
        'type' => $type_filter
    ];
    
    // Get settings data from service
    $settingsData = $service->getSettingsData($current_page, $per_page, $filters);
    $paged_settings = $settingsData['settings'];
    $setting_types = $settingsData['types'];
    $total_settings = $settingsData['total'];
    $total_pages = $settingsData['pagination']['last_page'];
    
} catch (Exception $e) {
    $errorHandler->logError('Admin Settings Error', $e);
    $paged_settings = [];
    $setting_types = [];
    $total_settings = 0;
    $total_pages = 1;
    $current_page = 1;
}

// Format setting type display
function formatSettingType($type) {
    $types = [
        'text' => 'Văn bản',
        'textarea' => 'Văn bản dài',
        'email' => 'Email',
        'url' => 'URL',
        'number' => 'Số',
        'boolean' => 'Đúng/Sai',
        'select' => 'Lựa chọn',
        'file' => 'Tệp tin'
    ];
    return $types[$type] ?? ucfirst($type);
}

// Format setting value display
function formatSettingValue($value, $type) {
    switch ($type) {
        case 'textarea':
            return strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value;
        case 'boolean':
            return $value ? 'Có' : 'Không';
        case 'number':
            return number_format($value);
        default:
            return $value;
    }
}
?>

<div class="settings-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-cog"></i>
                Quản Lý Cài Đặt
            </h1>
            <p class="page-description">Quản lý các cài đặt hệ thống</p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=settings&action=add" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Thêm Cài Đặt
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-section">
        <form method="GET" class="filters-form">
            <input type="hidden" name="page" value="admin">
            <input type="hidden" name="module" value="settings">
            
            <div class="filter-group">
                <div class="filter-item">
                    <label for="search">Tìm kiếm:</label>
                    <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Tên cài đặt, mô tả, giá trị...">
                </div>
                
                <div class="filter-item">
                    <label for="type">Loại:</label>
                    <select id="type" name="type">
                        <option value="">Tất cả loại</option>
                        <?php foreach ($setting_types as $type): ?>
                            <option value="<?= $type ?>" <?= $type_filter == $type ? 'selected' : '' ?>>
                                <?= formatSettingType($type) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-search"></i>
                        Lọc
                    </button>
                    <a href="?page=admin&module=settings" class="btn btn-outline">
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
            Hiển thị <?= count($paged_settings) ?> trong tổng số <?= $total_settings ?> cài đặt
        </span>
        
        <!-- Bulk Actions -->
        <div class="bulk-actions">
            <select id="bulk-action" disabled>
                <option value="">Hành động hàng loạt</option>
                <option value="delete">Xóa</option>
            </select>
            <button type="button" id="apply-bulk" class="btn btn-secondary" disabled>
                Áp dụng
            </button>
        </div>
    </div>

    <!-- Settings Table -->
    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th width="40">
                        <input type="checkbox" id="select-all">
                    </th>
                    <th width="200">Tên cài đặt</th>
                    <th>Mô tả</th>
                    <th width="300">Giá trị</th>
                    <th width="120">Loại</th>
                    <th width="120">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($paged_settings)): ?>
                    <tr>
                        <td colspan="6" class="no-data">
                            <i class="fas fa-inbox"></i>
                            <p>Không tìm thấy cài đặt nào</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($paged_settings as $setting): ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="setting-checkbox" value="<?= htmlspecialchars($setting['key']) ?>">
                            </td>
                            <td>
                                <div class="setting-key">
                                    <strong><?= htmlspecialchars($setting['key']) ?></strong>
                                </div>
                            </td>
                            <td>
                                <div class="setting-description">
                                    <?= htmlspecialchars($setting['description']) ?>
                                </div>
                            </td>
                            <td>
                                <div class="setting-value">
                                    <code><?= htmlspecialchars(formatSettingValue($setting['value'], $setting['type'])) ?></code>
                                </div>
                            </td>
                            <td>
                                <span class="type-badge type-<?= $setting['type'] ?>">
                                    <?= formatSettingType($setting['type']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="?page=admin&module=settings&action=view&key=<?= urlencode($setting['key']) ?>" 
                                       class="btn btn-sm btn-info" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="?page=admin&module=settings&action=edit&key=<?= urlencode($setting['key']) ?>" 
                                       class="btn btn-sm btn-warning" title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger delete-btn" 
                                            data-key="<?= htmlspecialchars($setting['key']) ?>" 
                                            data-description="<?= htmlspecialchars($setting['description']) ?>" 
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
                    <a href="?page=admin&module=settings&<?= http_build_query(array_merge($_GET, ['page' => $current_page - 1])) ?>" 
                       class="pagination-btn">
                        <i class="fas fa-chevron-left"></i>
                        Trước
                    </a>
                <?php endif; ?>

                <?php
                $start_page = max(1, $current_page - 2);
                $end_page = min($total_pages, $current_page + 2);
                
                if ($start_page > 1): ?>
                    <a href="?page=admin&module=settings&<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>" 
                       class="pagination-number">1</a>
                    <?php if ($start_page > 2): ?>
                        <span class="pagination-dots">...</span>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <a href="?page=admin&module=settings&<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
                       class="pagination-number <?= $i == $current_page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($end_page < $total_pages): ?>
                    <?php if ($end_page < $total_pages - 1): ?>
                        <span class="pagination-dots">...</span>
                    <?php endif; ?>
                    <a href="?page=admin&module=settings&<?= http_build_query(array_merge($_GET, ['page' => $total_pages])) ?>" 
                       class="pagination-number"><?= $total_pages ?></a>
                <?php endif; ?>

                <?php if ($current_page < $total_pages): ?>
                    <a href="?page=admin&module=settings&<?= http_build_query(array_merge($_GET, ['page' => $current_page + 1])) ?>" 
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
                <p>Bạn có chắc chắn muốn xóa cài đặt <strong id="deleteSettingKey"></strong>?</p>
                <p class="text-danger">Hành động này không thể hoàn tác!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelDelete">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Xóa</button>
            </div>
        </div>
    </div>
</div>