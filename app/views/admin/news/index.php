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
    
    // Get news data using ViewDataService
    $newsData = $viewDataService->getAdminNewsData($current_page, $per_page, $filters);
    $news = $newsData['news'];
    $pagination = $newsData['pagination'];
    $stats = $newsData['stats'];
    $total_news = $newsData['total'];
    
    // Extract filter values for form
    $search = $filters['search'];
    $status_filter = $filters['status'];
    
    // Pagination values
    $total_pages = $pagination['last_page'];
    $current_page = $pagination['current_page'];
    
} catch (Exception $e) {
    $errorHandler->logError('Admin News Index View Error', $e);
    $news = [];
    $stats = ['total' => 0, 'published' => 0, 'draft' => 0, 'archived' => 0];
    $total_news = 0;
    $total_pages = 1;
    $current_page = 1;
    $pagination = ['current_page' => 1, 'total' => 0];
}

// Format date function
function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}
?>

<div class="news-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-newspaper"></i>
                Quản Lý Tin Tức
            </h1>
            <p class="page-description">Quản lý bài viết và tin tức của hệ thống</p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=news&action=add" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Thêm Tin Tức
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-section">
        <form method="GET" class="filters-form">
            <input type="hidden" name="page" value="admin">
            <input type="hidden" name="module" value="news">
            
            <div class="filter-group">
                <div class="filter-item">
                    <label for="search">Tìm kiếm:</label>
                    <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Tiêu đề, nội dung, tóm tắt...">
                </div>
                
                <div class="filter-item">
                    <label for="status">Trạng thái:</label>
                    <select id="status" name="status">
                        <option value="">Tất cả trạng thái</option>
                        <option value="published" <?= $status_filter == 'published' ? 'selected' : '' ?>>Đã xuất bản</option>
                        <option value="draft" <?= $status_filter == 'draft' ? 'selected' : '' ?>>Bản nháp</option>
                        <option value="archived" <?= $status_filter == 'archived' ? 'selected' : '' ?>>Lưu trữ</option>
                    </select>
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-search"></i>
                        Lọc
                    </button>
                    <a href="?page=admin&module=news" class="btn btn-outline">
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
            Hiển thị <?= count($news) ?> trong tổng số <?= $total_news ?> tin tức
        </span>
        
        <!-- Bulk Actions -->
        <div class="bulk-actions">
            <select id="bulk-action" disabled>
                <option value="">Hành động hàng loạt</option>
                <option value="publish">Xuất bản</option>
                <option value="draft">Chuyển thành nháp</option>
                <option value="archive">Lưu trữ</option>
                <option value="delete">Xóa</option>
            </select>
            <button type="button" id="apply-bulk" class="btn btn-secondary" disabled>
                Áp dụng
            </button>
        </div>
    </div>

    <!-- News Table -->
    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th width="40">
                        <input type="checkbox" id="select-all">
                    </th>
                    <th width="60">ID</th>
                    <th width="80">Hình ảnh</th>
                    <th>Tiêu đề</th>
                    <th width="200">Tóm tắt</th>
                    <th width="120">Tác giả</th>
                    <th width="100">Trạng thái</th>
                    <th width="120">Ngày tạo</th>
                    <th width="120">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($news)): ?>
                    <tr>
                        <td colspan="9" class="no-data">
                            <i class="fas fa-inbox"></i>
                            <p>Không tìm thấy tin tức nào</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($news as $article): ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="news-checkbox" value="<?= $article['id'] ?>">
                            </td>
                            <td><?= $article['id'] ?></td>
                            <td>
                                <div class="news-image">
                                    <img src="<?= $article['image'] ?>" alt="<?= htmlspecialchars($article['title']) ?>" 
                                         onerror="this.src='<?php echo asset_url('images/placeholder.jpg'); ?>'">
                                </div>
                            </td>
                            <td>
                                <div class="news-info">
                                    <h4 class="news-title"><?= htmlspecialchars($article['title']) ?></h4>
                                    <p class="news-slug">
                                        <i class="fas fa-link"></i>
                                        <?= htmlspecialchars($article['slug']) ?>
                                    </p>
                                </div>
                            </td>
                            <td>
                                <p class="news-excerpt"><?= htmlspecialchars(substr($article['excerpt'], 0, 80)) ?>...</p>
                            </td>
                            <td>
                                <div class="author-info">
                                    <i class="fas fa-user"></i>
                                    <?= htmlspecialchars($article['author_name'] ?? 'N/A') ?>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge status-<?= $article['status'] ?>">
                                    <?php
                                    switch($article['status']) {
                                        case 'published': echo 'Đã xuất bản'; break;
                                        case 'draft': echo 'Bản nháp'; break;
                                        case 'archived': echo 'Lưu trữ'; break;
                                        default: echo ucfirst($article['status']);
                                    }
                                    ?>
                                </span>
                            </td>
                            <td><?= formatDate($article['created_at']) ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="?page=admin&module=news&action=view&id=<?= $article['id'] ?>" 
                                       class="btn btn-sm btn-info" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="?page=admin&module=news&action=edit&id=<?= $article['id'] ?>" 
                                       class="btn btn-sm btn-warning" title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger delete-btn" 
                                            data-id="<?= $article['id'] ?>" data-name="<?= htmlspecialchars($article['title']) ?>" 
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
                    <a href="?page=admin&module=news&<?= http_build_query(array_merge($_GET, ['page' => $current_page - 1])) ?>" 
                       class="pagination-btn">
                        <i class="fas fa-chevron-left"></i>
                        Trước
                    </a>
                <?php endif; ?>

                <?php
                $start_page = max(1, $current_page - 2);
                $end_page = min($total_pages, $current_page + 2);
                
                if ($start_page > 1): ?>
                    <a href="?page=admin&module=news&<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>" 
                       class="pagination-number">1</a>
                    <?php if ($start_page > 2): ?>
                        <span class="pagination-dots">...</span>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <a href="?page=admin&module=news&<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
                       class="pagination-number <?= $i == $current_page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($end_page < $total_pages): ?>
                    <?php if ($end_page < $total_pages - 1): ?>
                        <span class="pagination-dots">...</span>
                    <?php endif; ?>
                    <a href="?page=admin&module=news&<?= http_build_query(array_merge($_GET, ['page' => $total_pages])) ?>" 
                       class="pagination-number"><?= $total_pages ?></a>
                <?php endif; ?>

                <?php if ($current_page < $total_pages): ?>
                    <a href="?page=admin&module=news&<?= http_build_query(array_merge($_GET, ['page' => $current_page + 1])) ?>" 
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
                <p>Bạn có chắc chắn muốn xóa tin tức <strong id="deleteNewsName"></strong>?</p>
                <p class="text-danger">Hành động này không thể hoàn tác!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelDelete">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Xóa</button>
            </div>
        </div>
    </div>
</div>