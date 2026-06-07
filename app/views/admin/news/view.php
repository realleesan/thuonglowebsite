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
    // Hiển thị HTML đã được định dạng từ custom editor
    // Không cần htmlspecialchars vì nội dung từ editor đã được xử lý
    return $content;
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
                            <?= htmlspecialchars($current_news['author_name'] ?? 'N/A') ?>
                        </span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Danh mục:</span>
                        <span class="meta-value">
                            <i class="fas fa-folder"></i>
                            <?php if (!empty($current_news['category_name'])): ?>
                                <?= htmlspecialchars($current_news['category_name']) ?>
                            <?php else: ?>
                                <span class="text-muted">Chưa phân loại</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Ngày tạo:</span>
                        <span class="meta-value">
                            <i class="fas fa-calendar"></i>
                            <?= formatDate($current_news['created_at']) ?>
                        </span>
                    </div>
                    <div class="meta-item tag-meta-item">
                        <span class="meta-label">Thẻ (Tags):</span>
                        <span class="meta-value">
                            <i class="fas fa-tags" style="color: #6b7280; margin-right: 4px;"></i>
                            <?php if (!empty($current_news['tags'])): ?>
                                <?php 
                                $tags = array_map('trim', explode(',', $current_news['tags']));
                                foreach ($tags as $tag): 
                                ?>
                                    <span class="admin-view-tag-chip"><?= htmlspecialchars($tag) ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="text-muted">Chưa gắn thẻ</span>
                            <?php endif; ?>
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
                                    <?= number_format($current_news['views'] ?? 0) ?>
                                </div>
                                <div class="stat-label">Lượt xem</div>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- News Details Tabs -->
    <div class="news-details-tabs">
        

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
    
    .admin-view-tag-chip {
        display: inline-block;
        background: #eff6ff;
        color: #356DF1;
        font-size: 12px;
        font-weight: 500;
        padding: 2px 8px;
        border-radius: 6px;
        border: 1px solid #dbeafe;
        margin-right: 4px;
        margin-bottom: 4px;
        line-height: 1.4;
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

<style>
/* Content Display Styles */
.content-display {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 30px;
    min-height: 200px;
    line-height: 1.8;
    font-size: 15px;
    color: #374151;
}

.content-display h1,
.content-display h2,
.content-display h3,
.content-display h4,
.content-display h5,
.content-display h6 {
    color: #111827;
    margin-top: 2em;
    margin-bottom: 1em;
    font-weight: 600;
}

.content-display h1 { font-size: 2em; }
.content-display h2 { font-size: 1.8em; }
.content-display h3 { font-size: 1.6em; }
.content-display h4 { font-size: 1.4em; }
.content-display h5 { font-size: 1.2em; }
.content-display h6 { font-size: 1.1em; }

.content-display p {
    margin-bottom: 1.2em;
    text-align: justify;
}

.content-display ul,
.content-display ol {
    margin: 1.5em 0;
    padding-left: 2em;
}

.content-display li {
    margin-bottom: 0.5em;
}

.content-display blockquote {
    border-left: 4px solid #3b82f6;
    padding-left: 1.5em;
    margin: 1.5em 0;
    font-style: italic;
    color: #6b7280;
    background: #f9fafb;
    padding: 1em 1.5em;
    border-radius: 0 8px 8px 0;
}

.content-display strong,
.content-display b {
    color: #111827;
    font-weight: 600;
}

.content-display em,
.content-display i {
    color: #4b5563;
    font-style: italic;
}

.content-display u {
    text-decoration: underline;
    color: #3b82f6;
}

.content-display a {
    color: #3b82f6;
    text-decoration: none;
    border-bottom: 1px solid transparent;
    transition: border-color 0.2s;
}

.content-display a:hover {
    border-bottom-color: #3b82f6;
}

.content-display code {
    background: #f3f4f6;
    padding: 0.2em 0.4em;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
    font-size: 0.9em;
    color: #d1d5db;
}

.content-display pre {
    background: #1f2937;
    color: #f9fafb;
    padding: 1.5em;
    border-radius: 8px;
    overflow-x: auto;
    margin: 1.5em 0;
}

.content-display pre code {
    background: none;
    color: inherit;
    padding: 0;
}

.content-display table {
    width: 100%;
    border-collapse: collapse;
    margin: 1.5em 0;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    overflow: hidden;
}

.content-display th,
.content-display td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}

.content-display th {
    background: #f9fafb;
    font-weight: 600;
    color: #374151;
}

.content-display tr:last-child td {
    border-bottom: none;
}

.content-display img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin: 1.5em 0;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.content-display hr {
    border: none;
    height: 1px;
    background: #e5e7eb;
    margin: 2em 0;
}

.text-muted {
    color: #9ca3af;
    font-style: italic;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .content-display {
        padding: 20px;
        font-size: 14px;
    }
    
    .content-display h1 { font-size: 1.8em; }
    .content-display h2 { font-size: 1.6em; }
    .content-display h3 { font-size: 1.4em; }
}
</style>