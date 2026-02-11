<?php
// Load ViewDataService and ErrorHandler
require_once __DIR__ . '/../../../services/ViewDataService.php';
require_once __DIR__ . '/../../../services/ErrorHandler.php';

try {
    $viewDataService = new ViewDataService();
    $errorHandler = new ErrorHandler();
    
    // Get news ID from URL
    $news_id = (int)($_GET['id'] ?? 0);
    
    if (!$news_id) {
        header('Location: ?page=admin&module=news&error=invalid_id');
        exit;
    }
    
    // Get news data using ViewDataService
    $newsData = $viewDataService->getAdminNewsDetailsData($news_id);
    $current_news = $newsData['news'];
    $author = $newsData['author'];
    
    // Redirect if news not found
    if (!$current_news) {
        header('Location: ?page=admin&module=news&error=not_found');
        exit;
    }
    
} catch (Exception $e) {
    $errorHandler->logError('Admin News Edit View Error', $e);
    header('Location: ?page=admin&module=news&error=system_error');
    exit;
}

// Initialize form data with current news data
$form_data = array_merge([
    'title' => '',
    'slug' => '',
    'content' => '',
    'excerpt' => '',
    'image' => '',
    'status' => 'draft',
    'author' => 'Admin ThuongLo',
    'meta_title' => '',
    'meta_description' => '',
    'keywords' => ''
], $current_news);

// Handle form submission (demo - không lưu thật)
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data = array_merge($form_data, $_POST);
    
    // Validation
    if (empty($form_data['title'])) {
        $errors[] = 'Tiêu đề không được để trống';
    }
    
    if (empty($form_data['slug'])) {
        $errors[] = 'Slug không được để trống';
    }
    
    if (empty($form_data['content'])) {
        $errors[] = 'Nội dung không được để trống';
    }
    
    if (empty($form_data['excerpt'])) {
        $errors[] = 'Tóm tắt không được để trống';
    }
    
    if (empty($form_data['author'])) {
        $errors[] = 'Tác giả không được để trống';
    }
    
    // Check slug format
    if (!empty($form_data['slug']) && !preg_match('/^[a-z0-9-]+$/', $form_data['slug'])) {
        $errors[] = 'Slug chỉ được chứa chữ thường, số và dấu gạch ngang';
    }
    
    if (empty($errors)) {
        $success = true;
        // Trong thực tế sẽ cập nhật vào database
    }
}

// Format date function
function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}
?>

<div class="news-edit-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-edit"></i>
                Chỉnh Sửa Tin Tức
            </h1>
            <p class="page-description">Cập nhật thông tin tin tức #<?= $news_id ?></p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=news&action=view&id=<?= $news_id ?>" class="btn btn-info">
                <i class="fas fa-eye"></i>
                Xem chi tiết
            </a>
            <a href="?page=admin&module=news" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i>
                Quay lại danh sách
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <div>
                <strong>Thành công!</strong> Tin tức đã được cập nhật thành công.
                <br><a href="?page=admin&module=news&action=view&id=<?= $news_id ?>">Xem tin tức đã cập nhật</a>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                <strong>Có lỗi xảy ra:</strong>
                <ul class="error-list">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <!-- Form Container -->
    <div class="form-container">
        <form method="POST" class="admin-form" enctype="multipart/form-data">
            <div class="form-grid">
                <!-- Left Column -->
                <div class="form-column">
                    <!-- Basic Information -->
                    <div class="form-section">
                        <h3 class="section-title">Thông Tin Cơ Bản</h3>
                        
                        <div class="form-group">
                            <label for="title" class="required">Tiêu đề:</label>
                            <input type="text" id="title" name="title" 
                                   value="<?= htmlspecialchars($form_data['title']) ?>" 
                                   placeholder="Nhập tiêu đề tin tức..."
                                   onkeyup="generateSlugFromTitle()" required>
                            <small>Tiêu đề sẽ hiển thị trên trang chủ và trang chi tiết</small>
                        </div>

                        <div class="form-group">
                            <label for="slug" class="required">Slug (URL thân thiện):</label>
                            <input type="text" id="slug" name="slug" 
                                   value="<?= htmlspecialchars($form_data['slug']) ?>" 
                                   placeholder="vi-du-slug-tin-tuc" required>
                            <small>Chỉ sử dụng chữ thường, số và dấu gạch ngang. VD: xu-huong-kinh-doanh-2024</small>
                        </div>

                        <div class="form-group">
                            <label for="excerpt" class="required">Tóm tắt:</label>
                            <textarea id="excerpt" name="excerpt" rows="4" 
                                      placeholder="Nhập tóm tắt ngắn gọn về nội dung tin tức..." required><?= htmlspecialchars($form_data['excerpt']) ?></textarea>
                            <small>Tóm tắt sẽ hiển thị trong danh sách tin tức (tối đa 200 ký tự)</small>
                        </div>

                        <div class="form-group">
                            <label for="author" class="required">Tác giả:</label>
                            <input type="text" id="author" name="author" 
                                   value="<?= htmlspecialchars($form_data['author']) ?>" 
                                   placeholder="Tên tác giả..." required>
                            <small>Tên tác giả sẽ hiển thị cùng với bài viết</small>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="form-section">
                        <h3 class="section-title">Nội Dung</h3>
                        
                        <div class="form-group">
                            <label for="content" class="required">Nội dung chi tiết:</label>
                            <textarea id="content" name="content" rows="15" 
                                      placeholder="Nhập nội dung chi tiết của tin tức..." required><?= htmlspecialchars($form_data['content']) ?></textarea>
                            <small>Nội dung đầy đủ của bài viết. Hỗ trợ HTML cơ bản</small>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="form-column">
                    <!-- Publishing Options -->
                    <div class="form-section">
                        <h3 class="section-title">Tùy Chọn Xuất Bản</h3>
                        
                        <div class="form-group">
                            <label for="status">Trạng thái:</label>
                            <select id="status" name="status">
                                <option value="draft" <?= $form_data['status'] == 'draft' ? 'selected' : '' ?>>Bản nháp</option>
                                <option value="published" <?= $form_data['status'] == 'published' ? 'selected' : '' ?>>Đã xuất bản</option>
                                <option value="archived" <?= $form_data['status'] == 'archived' ? 'selected' : '' ?>>Lưu trữ</option>
                            </select>
                            <small>Chọn trạng thái cho bài viết</small>
                        </div>

                        <div class="form-group">
                            <label>Ngày tạo:</label>
                            <input type="text" value="<?= formatDate($current_news['created_at']) ?>" class="readonly" readonly>
                            <small>Thời gian tạo bài viết ban đầu</small>
                        </div>

                        <div class="form-group">
                            <label>Cập nhật lần cuối:</label>
                            <input type="text" value="<?= date('d/m/Y H:i') ?>" class="readonly" readonly>
                            <small>Thời gian cập nhật hiện tại</small>
                        </div>
                    </div>

                    <!-- Featured Image -->
                    <div class="form-section">
                        <h3 class="section-title">Hình Ảnh Đại Diện</h3>
                        
                        <div class="form-group">
                            <label for="image">Chọn hình ảnh mới:</label>
                            <div class="image-upload-container">
                                <div class="image-preview" onclick="document.getElementById('image').click()">
                                    <?php if (!empty($form_data['image'])): ?>
                                        <img id="preview-img" src="<?= $form_data['image'] ?>" alt="Current Image">
                                        <div id="preview-placeholder" style="display: none;">
                                            <i class="fas fa-image"></i>
                                            <p>Click để chọn hình ảnh</p>
                                        </div>
                                    <?php else: ?>
                                        <img id="preview-img" src="" alt="Preview" style="display: none;">
                                        <div id="preview-placeholder">
                                            <i class="fas fa-image"></i>
                                            <p>Click để chọn hình ảnh</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <input type="file" id="image" name="image" class="image-input" 
                                       accept="image/*" onchange="previewImage(this)">
                                <div class="image-upload-info">
                                    <small>Định dạng: JPG, PNG, GIF. Kích thước tối đa: 2MB</small>
                                    <small>Kích thước khuyến nghị: 800x600px</small>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($form_data['image'])): ?>
                            <div class="current-image-info">
                                <strong>Hình ảnh hiện tại:</strong><br>
                                <?= basename($form_data['image']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- SEO Options -->
                    <div class="form-section">
                        <h3 class="section-title">Tối Ưu SEO</h3>
                        
                        <div class="form-group">
                            <label for="meta_title">Meta Title:</label>
                            <input type="text" id="meta_title" name="meta_title" 
                                   value="<?= htmlspecialchars($form_data['meta_title'] ?? $form_data['title']) ?>" 
                                   placeholder="Tiêu đề SEO">
                            <small>Tiêu đề hiển thị trên kết quả tìm kiếm (60 ký tự)</small>
                        </div>

                        <div class="form-group">
                            <label for="meta_description">Meta Description:</label>
                            <textarea id="meta_description" name="meta_description" rows="3" 
                                      placeholder="Mô tả SEO"><?= htmlspecialchars($form_data['meta_description'] ?? $form_data['excerpt']) ?></textarea>
                            <small>Mô tả hiển thị trên kết quả tìm kiếm (160 ký tự)</small>
                        </div>

                        <div class="form-group">
                            <label for="keywords">Từ khóa:</label>
                            <input type="text" id="keywords" name="keywords" 
                                   value="<?= htmlspecialchars($form_data['keywords'] ?? '') ?>" 
                                   placeholder="từ khóa 1, từ khóa 2, từ khóa 3">
                            <small>Các từ khóa liên quan, cách nhau bằng dấu phẩy</small>
                        </div>
                    </div>

                    <!-- Revision History -->
                    <div class="form-section">
                        <h3 class="section-title">Lịch Sử Chỉnh Sửa</h3>
                        
                        <div class="revision-list">
                            <div class="revision-item">
                                <div class="revision-date"><?= formatDate($current_news['created_at']) ?></div>
                                <div class="revision-action">Tạo bài viết</div>
                                <div class="revision-author">bởi <?= htmlspecialchars($author['name'] ?? $current_news['author_name'] ?? 'N/A') ?></div>
                            </div>
                            <div class="revision-item current">
                                <div class="revision-date"><?= date('d/m/Y H:i') ?></div>
                                <div class="revision-action">Đang chỉnh sửa</div>
                                <div class="revision-author">bởi Admin hiện tại</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Cập Nhật Tin Tức
                </button>
                <button type="button" class="btn btn-secondary" onclick="saveDraft()">
                    <i class="fas fa-file-alt"></i>
                    Lưu Nháp
                </button>
                <button type="button" class="btn btn-info" onclick="previewNews()">
                    <i class="fas fa-eye"></i>
                    Xem Trước
                </button>
                <a href="?page=admin&module=news" class="btn btn-outline">
                    <i class="fas fa-times"></i>
                    Hủy
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Generate slug from title
function generateSlugFromTitle() {
    const title = document.getElementById('title').value;
    const slug = title
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/[\s-]+/g, '-')
        .replace(/^-+|-+$/g, '');
    document.getElementById('slug').value = slug;
}

// Preview image
function previewImage(input) {
    const preview = document.getElementById('preview-img');
    const placeholder = document.getElementById('preview-placeholder');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            placeholder.style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Save as draft
function saveDraft() {
    document.getElementById('status').value = 'draft';
    document.querySelector('form').submit();
}

// Preview news
function previewNews() {
    // Trong thực tế sẽ mở tab mới với preview
    alert('Chức năng xem trước sẽ được triển khai trong phiên bản tiếp theo');
}

// Auto-update meta fields if empty
document.getElementById('title').addEventListener('input', function() {
    const metaTitle = document.getElementById('meta_title');
    if (!metaTitle.value || metaTitle.value === metaTitle.defaultValue) {
        metaTitle.value = this.value;
    }
});

document.getElementById('excerpt').addEventListener('input', function() {
    const metaDesc = document.getElementById('meta_description');
    if (!metaDesc.value || metaDesc.value === metaDesc.defaultValue) {
        metaDesc.value = this.value;
    }
});

// Warn before leaving if form is dirty
let formChanged = false;
document.querySelectorAll('input, textarea, select').forEach(element => {
    element.addEventListener('change', () => formChanged = true);
});

window.addEventListener('beforeunload', function(e) {
    if (formChanged) {
        e.preventDefault();
        e.returnValue = '';
    }
});

document.querySelector('form').addEventListener('submit', () => formChanged = false);
</script>