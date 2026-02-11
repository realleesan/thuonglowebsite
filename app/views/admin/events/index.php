<?php
// Load ViewDataService and ErrorHandler
require_once __DIR__ . '/../../../services/ViewDataService.php';
require_once __DIR__ . '/../../../services/ErrorHandler.php';

try {
    $viewDataService = new ViewDataService();
    $errorHandler = new ErrorHandler();
    
    // Get filter parameters
    $filters = [
        'search' => $_GET['search'] ?? '',
        'status' => $_GET['status'] ?? ''
    ];
    
    $current_page = max(1, (int)($_GET['page'] ?? 1));
    $per_page = 10;
    
    // Get events data using ViewDataService
    $eventsData = $viewDataService->getAdminEventsData($current_page, $per_page, $filters);
    $events = $eventsData['events'];
    $pagination = $eventsData['pagination'];
    $total_events = $eventsData['total'];
    $stats = $eventsData['stats'];
    
    // Calculate pagination variables for template
    $total_pages = $pagination['last_page'];
    
} catch (Exception $e) {
    $errorHandler->logError('Admin Events Index View Error', $e);
    $events = [];
    $total_events = 0;
    $total_pages = 1;
    $current_page = 1;
    $filters = ['search' => '', 'status' => ''];
}

// Extract filter values for template
$search = $filters['search'];
$status_filter = $filters['status'];

// Format date function
function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}

// Format price function
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' VNĐ';
}
?>

<div class="events-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-calendar"></i>
                Quản Lý Sự Kiện
            </h1>
            <p class="page-description">Quản lý các sự kiện, workshop và hội thảo</p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=events&action=add" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Thêm Sự Kiện
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-section">
        <form method="GET" class="filters-form">
            <input type="hidden" name="page" value="admin">
            <input type="hidden" name="module" value="events">
            
            <div class="filter-group">
                <div class="filter-item">
                    <label for="search">Tìm kiếm:</label>
                    <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Tên sự kiện, mô tả, địa điểm...">
                </div>
                
                <div class="filter-item">
                    <label for="status">Trạng thái:</label>
                    <select id="status" name="status">
                        <option value="">Tất cả trạng thái</option>
                        <option value="upcoming" <?= $status_filter == 'upcoming' ? 'selected' : '' ?>>Sắp diễn ra</option>
                        <option value="ongoing" <?= $status_filter == 'ongoing' ? 'selected' : '' ?>>Đang diễn ra</option>
                        <option value="completed" <?= $status_filter == 'completed' ? 'selected' : '' ?>>Đã kết thúc</option>
                        <option value="cancelled" <?= $status_filter == 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                    </select>
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-search"></i>
                        Lọc
                    </button>
                    <a href="?page=admin&module=events" class="btn btn-outline">
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
            Hiển thị <?= count($events) ?> trong tổng số <?= $total_events ?> sự kiện
        </span>
        
        <!-- Bulk Actions -->
        <div class="bulk-actions">
            <select id="bulk-action" disabled>
                <option value="">Hành động hàng loạt</option>
                <option value="upcoming">Chuyển thành sắp diễn ra</option>
                <option value="ongoing">Chuyển thành đang diễn ra</option>
                <option value="completed">Chuyển thành đã kết thúc</option>
                <option value="cancelled">Hủy sự kiện</option>
                <option value="delete">Xóa</option>
            </select>
            <button type="button" id="apply-bulk" class="btn btn-secondary" disabled>
                Áp dụng
            </button>
        </div>
    </div>

    <!-- Events Table -->
    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th width="40">
                        <input type="checkbox" id="select-all">
                    </th>
                    <th width="60">ID</th>
                    <th width="80">Hình ảnh</th>
                    <th>Tên sự kiện</th>
                    <th width="120">Thời gian</th>
                    <th width="100">Địa điểm</th>
                    <th width="80">Giá vé</th>
                    <th width="80">Số lượng</th>
                    <th width="100">Trạng thái</th>
                    <th width="120">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($events)): ?>
                    <tr>
                        <td colspan="10" class="no-data">
                            <i class="fas fa-inbox"></i>
                            <p>Không tìm thấy sự kiện nào</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($events as $event): ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="event-checkbox" value="<?= $event['id'] ?>">
                            </td>
                            <td><?= $event['id'] ?></td>
                            <td>
                                <div class="event-image">
                                    <img src="<?= $event['image'] ?>" alt="<?= htmlspecialchars($event['title']) ?>" 
                                         onerror="this.src='/assets/images/placeholder.jpg'">
                                </div>
                            </td>
                            <td>
                                <div class="event-info">
                                    <h4 class="event-title"><?= htmlspecialchars($event['title']) ?></h4>
                                    <p class="event-slug">
                                        <i class="fas fa-link"></i>
                                        <?= htmlspecialchars($event['slug']) ?>
                                    </p>
                                </div>
                            </td>
                            <td>
                                <div class="event-time">
                                    <div class="start-time">
                                        <i class="fas fa-play"></i>
                                        <?= formatDate($event['start_date']) ?>
                                    </div>
                                    <div class="end-time">
                                        <i class="fas fa-stop"></i>
                                        <?= formatDate($event['end_date']) ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="event-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?= htmlspecialchars($event['location']) ?>
                                </div>
                            </td>
                            <td>
                                <div class="event-price">
                                    <?= formatPrice($event['price']) ?>
                                </div>
                            </td>
                            <td>
                                <div class="participant-info">
                                    <div class="current"><?= $event['current_participants'] ?></div>
                                    <div class="max">/ <?= $event['max_participants'] ?></div>
                                    <div class="progress-bar">
                                        <div class="progress" style="width: <?= ($event['current_participants'] / $event['max_participants']) * 100 ?>%"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge status-<?= $event['status'] ?>">
                                    <?php
                                    switch($event['status']) {
                                        case 'upcoming': echo 'Sắp diễn ra'; break;
                                        case 'ongoing': echo 'Đang diễn ra'; break;
                                        case 'completed': echo 'Đã kết thúc'; break;
                                        case 'cancelled': echo 'Đã hủy'; break;
                                        default: echo ucfirst($event['status']);
                                    }
                                    ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="?page=admin&module=events&action=view&id=<?= $event['id'] ?>" 
                                       class="btn btn-sm btn-info" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="?page=admin&module=events&action=edit&id=<?= $event['id'] ?>" 
                                       class="btn btn-sm btn-warning" title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger delete-btn" 
                                            data-id="<?= $event['id'] ?>" data-name="<?= htmlspecialchars($event['title']) ?>" 
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
                    <a href="?page=admin&module=events&<?= http_build_query(array_merge($_GET, ['page' => $current_page - 1])) ?>" 
                       class="pagination-btn">
                        <i class="fas fa-chevron-left"></i>
                        Trước
                    </a>
                <?php endif; ?>

                <?php
                $start_page = max(1, $current_page - 2);
                $end_page = min($total_pages, $current_page + 2);
                
                if ($start_page > 1): ?>
                    <a href="?page=admin&module=events&<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>" 
                       class="pagination-number">1</a>
                    <?php if ($start_page > 2): ?>
                        <span class="pagination-dots">...</span>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <a href="?page=admin&module=events&<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
                       class="pagination-number <?= $i == $current_page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($end_page < $total_pages): ?>
                    <?php if ($end_page < $total_pages - 1): ?>
                        <span class="pagination-dots">...</span>
                    <?php endif; ?>
                    <a href="?page=admin&module=events&<?= http_build_query(array_merge($_GET, ['page' => $total_pages])) ?>" 
                       class="pagination-number"><?= $total_pages ?></a>
                <?php endif; ?>

                <?php if ($current_page < $total_pages): ?>
                    <a href="?page=admin&module=events&<?= http_build_query(array_merge($_GET, ['page' => $current_page + 1])) ?>" 
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
                <p>Bạn có chắc chắn muốn xóa sự kiện <strong id="deleteEventName"></strong>?</p>
                <p class="text-danger">Hành động này không thể hoàn tác!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelDelete">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Xóa</button>
            </div>
        </div>
    </div>
</div>