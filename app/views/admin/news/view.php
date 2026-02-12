<?php
$service = isset($currentService) ? $currentService : ($adminService ?? null);

try {
    // Get news ID from URL
    $news_id = (int)($_GET['id'] ?? 0);
    
    if (!$news_id) {
        header('Location: ?page=admin&module=news&error=invalid_id');
        exit;
    }
    
    // Get news data using AdminService
    $newsData = $service->getNewsDetailsData($news_id);
    $current_news = $newsData['news'];
    $author = $newsData['author'];
    
    // Redirect if news not found
    if (!$current_news) {
        header('Location: ?page=admin&module=news&error=not_found');
        exit;
    }
    
} catch (Exception $e) {
    $errorHandler->logError('Admin News View Error', $e);
    header('Location: ?page=admin&module=news&error=system_error');
    exit;
}

// Format date function
function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}

// Format content for display
function formatContent($content) {
    // Trong thực tế sẽ xử lý HTML, markdown, etc.
    return nl2br(htmlspecialchars($content));
}

// Get word count
function getWordCount($text) {
    return str_word_count(strip_tags($text));
}

// Get reading time (average 200 words per minute)
function getReadingTime($text) {
    $wordCount = getWordCount($text);
    $minutes = ceil($wordCount / 200);
    return $minutes . ' phút đọc';
}
?>

<div class="news-view-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-newspaper"></i>
                Chi Tiết Tin Tức
            </h1>
            <p class="page-description">Xem thông tin chi tiết tin tức #<?= $news_id ?></p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=news&action=edit&id=<?= $news_id ?>" class="btn btn-warning">
                <i class="fas fa-edit"></i>
                Chỉnh sửa
            </a>
            <button type="button" class="btn btn-danger delete-btn" 
                    data-id="<?= $news_id ?>" data-name="<?= htmlspecialchars($current_news['title']) ?>">
                <i class="fas fa-trash"></i>
                Xóa
            </button>
            <a href="?page=admin&module=news" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i>
                Quay lại danh sách
            </a>
        </div>
    </div>

    <!-- News Overview -->
    <div class="news-overview">
        <div class="news-overview-grid">
            <!-- News Image Section -->
            <div class="news-image-section">
                <div class="news-image-main" onclick="openImageZoom('<?= $current_news['image'] ?>')">
                    <img src="<?= $current_news['image'] ?>" alt="<?= htmlspecialchars($current_news['title']) ?>" 
                         onerror="this.src='<?php echo asset_url('images/placeholder.jpg'); ?>'"">
                </div>
                <div class="news-image-info">
                    <i class="fas fa-info-circle"></i>
                    Click để phóng to hình ảnh
                </div>
            </div>

            <!-- News Info Section -->
            <div class="news-info-section">
                <div class="news-header">
                    <h2 class="news-name"><?= htmlspecialchars($current_news['title']) ?></h2>
                    <div class="news-actions">
                        <span class="status-badge status-<?= $current_news['status'] ?>">
                            <?php
                            switch($current_news['status']) {
                                case 'published': echo 'Đã xuất bản'; break;
                                case 'draft': echo 'Bản nháp'; break;
                                case 'archived': echo 'Lưu trữ'; break;
                                default: echo ucfirst($current_news['status']);
                            }
                            ?>
                        </span>
                    </div>
                </div>

                <div class="news-meta">
                    <div class="meta-item">
                        <span class="meta-label">ID:</span>
                        <span class="meta-value">#<?= $current_news['id'] ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Slug:</span>
                        <span class="meta-value">
                            <code><?= htmlspecialchars($current_news['slug']) ?></code>
                        </span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Tác giả:</span>
                        <span class="meta-value">
                            <i class="fas fa-user"></i>
                            <?= htmlspecialchars($author['name'] ?? $current_news['author_name'] ?? 'N/A') ?>
                        </span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Ngày tạo:</span>
                        <span class="meta-value">
                            <i class="fas fa-calendar"></i>
                            <?= formatDate($current_news['created_at']) ?>
                        </span>
                    </div>
                </div>

                <!-- News Excerpt -->
                <div class="news-excerpt">
                    <h4>Tóm tắt</h4>
                    <p><?= htmlspecialchars($current_news['excerpt']) ?></p>
                </div>

                <!-- News Stats -->
                <div class="news-stats-section">
                    <h4>Thống kê bài viết</h4>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-file-word"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number" title="<?= getWordCount($current_news['content']) ?> từ">
                                    <?= getWordCount($current_news['content']) ?>
                                </div>
                                <div class="stat-label">Số từ</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number">
                                    <?= getReadingTime($current_news['content']) ?>
                                </div>
                                <div class="stat-label">Thời gian đọc</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-eye"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number">
                                    <?= number_format($news['view_count'] ?? 0) ?>
                                </div>
                                <div class="stat-label">Lượt xem</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-share"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number">
                                    <?= number_format($news['share_count'] ?? 0) ?>
                                </div>
                                <div class="stat-label">Lượt chia sẻ</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- News Details Tabs -->
    <div class="news-details-tabs">
        <div class="tabs-header">
            <button class="tab-btn active" onclick="showTab('content')">
                <i class="fas fa-file-alt"></i>
                Nội dung
            </button>
            <button class="tab-btn" onclick="showTab('seo')">
                <i class="fas fa-search"></i>
                SEO
            </button>
            <button class="tab-btn" onclick="showTab('analytics')">
                <i class="fas fa-chart-line"></i>
                Phân tích
            </button>
            <button class="tab-btn" onclick="showTab('history')">
                <i class="fas fa-history"></i>
                Lịch sử
            </button>
        </div>

        <div class="tabs-content">
            <!-- Content Tab -->
            <div id="content-tab" class="tab-content active">
                <div class="content-section">
                    <h4>Nội dung đầy đủ</h4>
                    <div class="content-display">
                        <?= formatContent($current_news['content']) ?>
                    </div>
                </div>
            </div>

            <!-- SEO Tab -->
            <div id="seo-tab" class="tab-content">
                <div class="details-grid">
                    <div class="details-section">
                        <h4>Thông tin SEO</h4>
                        <table class="details-table">
                            <tr>
                                <td>Meta Title:</td>
                                <td><?= htmlspecialchars($current_news['title']) ?></td>
                            </tr>
                            <tr>
                                <td>Meta Description:</td>
                                <td><?= htmlspecialchars($current_news['excerpt']) ?></td>
                            </tr>
                            <tr>
                                <td>URL:</td>
                                <td>
                                    <code>/news/<?= htmlspecialchars($current_news['slug']) ?></code>
                                </td>
                            </tr>
                            <tr>
                                <td>Canonical URL:</td>
                                <td>
                                    <code>https://thuonglo.com/news/<?= htmlspecialchars($current_news['slug']) ?></code>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="details-section">
                        <h4>Tối ưu hóa</h4>
                        <div class="seo-score">
                            <div class="score-item">
                                <span class="score-label">SEO Score:</span>
                                <span class="score-value good">85/100</span>
                            </div>
                            <div class="score-item">
                                <span class="score-label">Readability:</span>
                                <span class="score-value excellent">92/100</span>
                            </div>
                        </div>
                        
                        <div class="seo-suggestions">
                            <h5>Gợi ý cải thiện:</h5>
                            <ul>
                                <li class="suggestion-good">
                                    <i class="fas fa-check"></i>
                                    Tiêu đề có độ dài phù hợp
                                </li>
                                <li class="suggestion-good">
                                    <i class="fas fa-check"></i>
                                    Có hình ảnh đại diện
                                </li>
                                <li class="suggestion-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Nên thêm từ khóa vào nội dung
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Analytics Tab -->
            <div id="analytics-tab" class="tab-content">
                <div class="analytics-section">
                    <h4>Thống kê truy cập</h4>
                    <div class="analytics-grid">
                        <div class="analytics-card">
                            <div class="analytics-header">
                                <h5>Lượt xem theo ngày</h5>
                                <span class="analytics-period">7 ngày qua</span>
                            </div>
                            <div class="analytics-chart">
                                <canvas id="viewsChart" width="300" height="150"></canvas>
                            </div>
                        </div>
                        
                        <div class="analytics-card">
                            <div class="analytics-header">
                                <h5>Nguồn truy cập</h5>
                            </div>
                            <div class="source-list">
                                <div class="source-item">
                                    <span class="source-name">Tìm kiếm Google</span>
                                    <span class="source-percent">45%</span>
                                </div>
                                <div class="source-item">
                                    <span class="source-name">Facebook</span>
                                    <span class="source-percent">30%</span>
                                </div>
                                <div class="source-item">
                                    <span class="source-name">Trực tiếp</span>
                                    <span class="source-percent">25%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- History Tab -->
            <div id="history-tab" class="tab-content">
                <div class="history-section">
                    <h4>Lịch sử thay đổi</h4>
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <strong>Tạo bài viết</strong>
                                    <span class="timeline-date"><?= formatDate($current_news['created_at']) ?></span>
                                </div>
                                <p>Bài viết được tạo bởi <?= htmlspecialchars($author['name'] ?? $current_news['author_name'] ?? 'N/A') ?></p>
                            </div>
                        </div>
                        
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <strong>Xuất bản</strong>
                                    <span class="timeline-date"><?= formatDate($current_news['created_at']) ?></span>
                                </div>
                                <p>Bài viết được xuất bản và hiển thị công khai</p>
                            </div>
                        </div>
                        
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <strong>Xem chi tiết</strong>
                                    <span class="timeline-date"><?= date('d/m/Y H:i') ?></span>
                                </div>
                                <p>Đang xem chi tiết bài viết</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                <a href="?page=admin&module=news&action=delete&id=<?= $news_id ?>" class="btn btn-danger" id="confirmDelete">Xóa</a>
            </div>
        </div>
    </div>

    <!-- Image Zoom Overlay -->
    <div id="imageZoomOverlay" class="image-zoom-overlay" onclick="closeImageZoom()">
        <div class="image-zoom-container">
            <img id="zoomedImage" src="" alt="Zoomed Image">
            <button class="zoom-close" onclick="closeImageZoom()">&times;</button>
        </div>
    </div>
</div>

<script>
// Tab functionality
function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab
    document.getElementById(tabName + '-tab').classList.add('active');
    event.target.classList.add('active');
}

// Image zoom functionality
function openImageZoom(imageSrc) {
    document.getElementById('zoomedImage').src = imageSrc;
    document.getElementById('imageZoomOverlay').style.display = 'flex';
}

function closeImageZoom() {
    document.getElementById('imageZoomOverlay').style.display = 'none';
}

// Delete functionality
document.querySelector('.delete-btn').addEventListener('click', function() {
    const newsName = this.getAttribute('data-name');
    document.getElementById('deleteNewsName').textContent = newsName;
    document.getElementById('deleteModal').style.display = 'flex';
});

document.getElementById('cancelDelete').addEventListener('click', function() {
    document.getElementById('deleteModal').style.display = 'none';
});

document.querySelector('.modal-close').addEventListener('click', function() {
    document.getElementById('deleteModal').style.display = 'none';
});

// Analytics chart
if (document.getElementById('viewsChart')) {
    const ctx = document.getElementById('viewsChart').getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 150);
    gradient.addColorStop(0, 'rgba(53, 109, 241, 0.3)');
    gradient.addColorStop(1, 'rgba(53, 109, 241, 0.05)');
    
    // Use real data or empty
    const chartData = window.chartData || [20, 45, 30, 60, 40, 80, 65];
    const width = 300;
    const height = 150;
    const padding = 20;
    
    ctx.beginPath();
    ctx.moveTo(padding, height - padding - (chartData[0] / 100 * (height - 2 * padding)));
    
    for (let i = 1; i < chartData.length; i++) {
        const x = padding + (i / (chartData.length - 1)) * (width - 2 * padding);
        const y = height - padding - (chartData[i] / 100 * (height - 2 * padding));
        ctx.lineTo(x, y);
    }
    
    ctx.stroke();
    
    // Fill area under curve
    ctx.lineTo(width - padding, height - padding);
    ctx.lineTo(padding, height - padding);
    ctx.closePath();
    ctx.fill();
}
</script>