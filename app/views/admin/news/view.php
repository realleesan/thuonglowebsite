<?php
/**
 * Admin News View
 * Sử dụng AdminService thông qua ServiceManager
 */

// Khởi tạo View & ServiceManager
require_once __DIR__ . '/../../../../core/view_init.php';

// Chọn service admin (được inject từ index.php)
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

// Check if redirected after successful update
$updated = isset($_GET['updated']) && $_GET['updated'] == '1';

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

    <!-- Success Message -->
    <?php if ($updated): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <div>
                <strong>Thành công!</strong> Tin tức đã được cập nhật thành công.
            </div>
        </div>
    <?php endif; ?>

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
    <div id="productDeleteModal" style="display: none;">
        <div class="product-modal-overlay"></div>
        <div class="product-modal-container">
            <div class="product-modal-header">
                <h3>Xác nhận xóa</h3>
                <button class="product-modal-close" onclick="closeProductDeleteModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa tin tức "<strong id="productDeleteName"></strong>"?</p>
                <p class="product-modal-warning">Hành động này không thể hoàn tác!</p>
            </div>
            <div class="product-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeProductDeleteModal()">Hủy</button>
                <button type="button" class="btn btn-danger" id="prConfirmDeleteBtn">Xóa</button>
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

    <style>
    /* Modal Styles */
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: 999999;
    }
    
    .modal-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
    }
    
    .modal-container {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        border-radius: 12px;
        width: 90%;
        max-width: 500px;
    }
    
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .modal-header h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: #111827;
    }
    
    .modal-close {
        background: none;
        border: none;
        font-size: 24px;
        color: #9ca3af;
        cursor: pointer;
        padding: 4px;
        border-radius: 4px;
    }
    
    .modal-close:hover {
        color: #374151;
        background: #f3f4f6;
    }
    
    .modal-body {
        padding: 20px;
    }
    
    .modal-body p {
        margin: 0 0 8px 0;
        color: #374151;
    }
    
    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        padding: 16px 20px;
        border-top: 1px solid #e5e7eb;
        background: #f9fafb;
        border-radius: 0 0 12px 12px;
    }
    
    /* Status Badge Styles */
    .status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
        text-align: center;
        white-space: nowrap;
    }
    
    .status-published {
        background-color: #d1fae5;
        color: #065f46;
    }
    
    .status-draft {
        background-color: #fef3c7;
        color: #92400e;
    }
    
    .status-archived {
        background-color: #e5e7eb;
        color: #374151;
    }
    
    .product-thumbnail {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid #e5e7eb;
    }
    
    .no-image {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 50px;
        height: 50px;
        background: #f3f4f6;
        border-radius: 4px;
        color: #9ca3af;
    }
    
    .product-category {
        font-size: 11px;
        color: #6b7280;
        margin-top: 2px;
    }
    
    #productDeleteModal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: 999999;
    }

    .product-modal-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
    }

    .product-modal-container {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        border-radius: 12px;
        width: 90%;
        max-width: 500px;
    }

    .product-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        border-bottom: 1px solid #e5e7eb;
    }

    .product-modal-header h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: #111827;
    }

    .product-modal-close {
        background: none;
        border: none;
        font-size: 24px;
        color: #9ca3af;
        cursor: pointer;
        padding: 4px;
        border-radius: 4px;
    }

    .product-modal-close:hover {
        color: #374151;
        background: #f3f4f6;
    }

    .product-modal-body {
        padding: 20px;
    }

    .product-modal-body p {
        margin: 0 0 8px 0;
        color: #374151;
    }

    .product-modal-warning {
        color: #dc2626 !important;
        font-size: 13px;
        font-weight: 500;
    }

    .product-modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        padding: 16px 20px;
        border-top: 1px solid #e5e7eb;
        background: #f9fafb;
        border-radius: 0 0 12px 12px;
    }
    </style>
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

// Delete button click handler
    document.addEventListener('DOMContentLoaded', function() {
        const deleteButtons = document.querySelectorAll('.delete-btn');
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const newsId = this.getAttribute('data-id');
                const newsName = this.getAttribute('data-name');
                
                const nameElement = document.getElementById('productDeleteName');
                if (nameElement) {
                    nameElement.textContent = newsName || 'tin tức này';
                }
                
                const modal = document.getElementById('productDeleteModal');
                if (modal) {
                    modal.dataset.deleteId = newsId;
                    modal.style.display = 'block';
                    document.body.style.overflow = 'hidden';
                }
            });
        });
        
        // Confirm delete button
        const confirmDeleteBtn = document.getElementById('prConfirmDeleteBtn');
        if (confirmDeleteBtn) {
            confirmDeleteBtn.addEventListener('click', function() {
                const modal = document.getElementById('productDeleteModal');
                const deleteId = modal ? modal.dataset.deleteId : null;
                if (deleteId) {
                    window.location.href = '?page=admin&module=news&action=delete&id=' + deleteId;
                }
            });
        }
        
        // Close modal on overlay click
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('product-modal-overlay')) {
                closeProductDeleteModal();
            }
        });
        
        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modal = document.getElementById('productDeleteModal');
                if (modal && modal.style.display === 'block') {
                    closeProductDeleteModal();
                }
            }
        });
    });
    
    function closeProductDeleteModal() {
        const modal = document.getElementById('productDeleteModal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
            delete modal.dataset.deleteId;
        }
    }
</script>