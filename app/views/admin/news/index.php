<?php
// Professional News Management
$page_title = "Quản lý Tin tức";
$breadcrumb = [
    ['text' => 'Dashboard', 'url' => '?page=admin&module=dashboard'],
    ['text' => 'Tin tức', 'url' => null]
];

// Load fake data
$fake_data_file = __DIR__ . '/../data/fake_data.json';
$allNews = [];

if (file_exists($fake_data_file)) {
    $json_data = json_decode(file_get_contents($fake_data_file), true);
    $allNews = $json_data['news'] ?? [];
}

// Get filter parameters
$filterStatus = $_GET['status'] ?? '';
$searchQuery = $_GET['search'] ?? '';
$page = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
$perPage = 15;

// Apply filters
$news = $allNews;

if ($searchQuery) {
    $news = array_filter($news, function($n) use ($searchQuery) {
        return stripos($n['title'], $searchQuery) !== false || 
               stripos($n['content'], $searchQuery) !== false;
    });
}

if ($filterStatus) {
    $news = array_filter($news, function($n) use ($filterStatus) {
        return $n['status'] === $filterStatus;
    });
}

// Sort by date
usort($news, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

// Stats
$stats = [
    'total' => count($allNews),
    'published' => count(array_filter($allNews, function($n) { return $n['status'] === 'published'; })),
    'draft' => count(array_filter($allNews, function($n) { return $n['status'] === 'draft'; })),
];

// Pagination
$totalNews = count($news);
$totalPages = ceil($totalNews / $perPage);
$offset = ($page - 1) * $perPage;
$news = array_slice($news, $offset, $perPage);
?>

<div class="admin-page-header">
    <div class="page-header-left">
        <h1><?php echo $page_title; ?></h1>
        <div class="admin-breadcrumb">
            <?php foreach ($breadcrumb as $index => $crumb): ?>
                <?php if ($crumb['url']): ?>
                    <a href="<?php echo $crumb['url']; ?>"><?php echo $crumb['text']; ?></a>
                <?php else: ?>
                    <span class="current"><?php echo $crumb['text']; ?></span>
                <?php endif; ?>
                <?php if ($index < count($breadcrumb) - 1): ?>
                    <span class="delimiter">/</span>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="page-header-right">
        <a href="?page=admin&module=news&action=change" class="admin-btn admin-btn-primary">
            <i class="fas fa-plus"></i> Thêm tin tức mới
        </a>
    </div>
</div>

<!-- Stats Summary -->
<div class="admin-stats-summary">
    <div class="stat-item">
        <span class="stat-label">Tổng cộng:</span>
        <span class="stat-value"><?php echo $stats['total']; ?></span>
    </div>
    <div class="stat-item">
        <span class="stat-label">Đã xuất bản:</span>
        <span class="stat-value text-success"><?php echo $stats['published']; ?></span>
    </div>
    <div class="stat-item">
        <span class="stat-label">Bản nháp:</span>
        <span class="stat-value text-muted"><?php echo $stats['draft']; ?></span>
    </div>
</div>

<!-- Filters -->
<div class="admin-filters-bar">
    <form method="GET" action="" class="filters-form">
        <input type="hidden" name="page" value="admin">
        <input type="hidden" name="module" value="news">
        
        <div class="filter-search">
            <i class="fas fa-search"></i>
            <input type="text" name="search" placeholder="Tìm kiếm tin tức..." 
                   value="<?php echo htmlspecialchars($searchQuery); ?>" class="search-input">
        </div>
        
        <div class="filter-group">
            <select name="status" class="filter-select">
                <option value="">Tất cả trạng thái</option>
                <option value="published" <?php echo $filterStatus === 'published' ? 'selected' : ''; ?>>Đã xuất bản</option>
                <option value="draft" <?php echo $filterStatus === 'draft' ? 'selected' : ''; ?>>Bản nháp</option>
                <option value="scheduled" <?php echo $filterStatus === 'scheduled' ? 'selected' : ''; ?>>Đã lên lịch</option>
            </select>
        </div>
        
        <button type="submit" class="admin-btn admin-btn-primary">
            <i class="fas fa-filter"></i> Lọc
        </button>
        
        <?php if ($searchQuery || $filterStatus): ?>
        <a href="?page=admin&module=news" class="admin-btn admin-btn-secondary">
            <i class="fas fa-times"></i> Xóa bộ lọc
        </a>
        <?php endif; ?>
    </form>
</div>

<div class="admin-card">
    <div class="admin-card-body">
        <?php if (empty($news)): ?>
            <div class="admin-empty-state">
                <i class="fas fa-newspaper" style="font-size: 48px; color: #9CA3AF; margin-bottom: 16px;"></i>
                <h3>Không tìm thấy tin tức</h3>
                <p>Thử thay đổi bộ lọc hoặc thêm tin tức mới</p>
                <a href="?page=admin&module=news&action=change" class="admin-btn admin-btn-primary">
                    <i class="fas fa-plus"></i> Thêm tin tức
                </a>
            </div>
        <?php else: ?>
            <div class="news-list">
                <?php foreach ($news as $item): ?>
                <div class="news-item">
                    <div class="news-content">
                        <div class="news-header">
                            <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                            <span class="news-badge badge-<?php echo $item['status']; ?>">
                                <?php 
                                $statusLabels = [
                                    'published' => 'Đã xuất bản',
                                    'draft' => 'Bản nháp',
                                    'scheduled' => 'Đã lên lịch'
                                ];
                                echo $statusLabels[$item['status']] ?? $item['status'];
                                ?>
                            </span>
                        </div>
                        <p class="news-excerpt"><?php echo htmlspecialchars(substr($item['content'], 0, 150)) . '...'; ?></p>
                        <div class="news-meta">
                            <span><i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($item['created_at'])); ?></span>
                            <span><i class="fas fa-user"></i> Admin</span>
                        </div>
                    </div>
                    <div class="news-actions">
                        <?php if ($item['status'] === 'draft'): ?>
                        <button class="action-btn btn-publish" onclick="publishNews(<?php echo $item['id']; ?>)">
                            <i class="fas fa-check"></i> Xuất bản
                        </button>
                        <?php endif; ?>
                        <a href="?page=admin&module=news&action=edit&id=<?php echo $item['id']; ?>" class="action-btn btn-edit">
                            <i class="fas fa-edit"></i> Sửa
                        </a>
                        <a href="?page=admin&module=news&action=delete&id=<?php echo $item['id']; ?>" 
                           class="action-btn btn-delete"
                           onclick="return confirm('Bạn có chắc chắn muốn xóa?')">
                            <i class="fas fa-trash"></i> Xóa
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="admin-pagination">
                <div class="pagination-info">
                    Hiển thị <?php echo $offset + 1; ?>-<?php echo min($offset + $perPage, $totalNews); ?> 
                    trong tổng số <?php echo $totalNews; ?> tin tức
                </div>
                <div class="pagination-links">
                    <?php if ($page > 1): ?>
                    <a href="?page=admin&module=news&p=<?php echo $page - 1; ?>&search=<?php echo urlencode($searchQuery); ?>&status=<?php echo $filterStatus; ?>" 
                       class="pagination-link">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <a href="?page=admin&module=news&p=<?php echo $i; ?>&search=<?php echo urlencode($searchQuery); ?>&status=<?php echo $filterStatus; ?>" 
                       class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                    <a href="?page=admin&module=news&p=<?php echo $page + 1; ?>&search=<?php echo urlencode($searchQuery); ?>&status=<?php echo $filterStatus; ?>" 
                       class="pagination-link">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.news-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.news-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
    padding: 20px;
    background: #F9FAFB;
    border: 1px solid #E5E7EB;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.news-item:hover {
    border-color: #356DF1;
    background: white;
}

.news-content {
    flex: 1;
}

.news-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 8px;
}

.news-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #1F2937;
    flex: 1;
}

.news-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
}

.badge-published {
    background: #D1FAE5;
    color: #065F46;
}

.badge-draft {
    background: #FEF3C7;
    color: #92400E;
}

.badge-scheduled {
    background: #DBEAFE;
    color: #1E40AF;
}

.news-excerpt {
    margin: 0 0 12px 0;
    font-size: 14px;
    color: #6B7280;
    line-height: 1.6;
}

.news-meta {
    display: flex;
    gap: 16px;
    font-size: 13px;
    color: #9CA3AF;
}

.news-meta span {
    display: flex;
    align-items: center;
    gap: 6px;
}

.news-actions {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 8px 16px;
    border: 1px solid #D1D5DB;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s ease;
    white-space: nowrap;
    cursor: pointer;
    background: white;
}

.btn-publish {
    color: #10B981;
    border-color: #10B981;
}

.btn-publish:hover {
    background: #10B981;
    color: white;
}

.btn-edit {
    color: #F59E0B;
    border-color: #F59E0B;
}

.btn-edit:hover {
    background: #F59E0B;
    color: white;
}

.btn-delete {
    color: #EF4444;
    border-color: #EF4444;
}

.btn-delete:hover {
    background: #EF4444;
    color: white;
}

@media (max-width: 768px) {
    .news-item {
        flex-direction: column;
    }
    
    .news-actions {
        flex-direction: row;
        width: 100%;
    }
    
    .action-btn {
        flex: 1;
    }
}
</style>

<script>
function publishNews(id) {
    if (confirm('Bạn có chắc chắn muốn xuất bản tin tức này?')) {
        // In real app, send AJAX request here
        alert('Chức năng này sẽ được triển khai với backend');
    }
}
</script>