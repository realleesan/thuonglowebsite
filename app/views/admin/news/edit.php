<?php
/**
 * Admin News Edit
 * Sử dụng AdminService thông qua ServiceManager
 */

// Khởi tạo View & ServiceManager
require_once __DIR__ . '/../../../../core/view_init.php';

// Load CategoriesModel
require_once __DIR__ . '/../../../../app/models/CategoriesModel.php';

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
    
    // Get news categories for dropdown
    $news_categories = [];
    try {
        $categoriesModel = new \CategoriesModel();
        $news_categories = $categoriesModel->getNewsCategories();
    } catch (Exception $e) {
        // Log error but continue without categories
        error_log("Error loading news categories: " . $e->getMessage());
    }
    
    // Fetch unique tags from news_tags table
    $all_tags = [];
    try {
        if ($service) {
            $all_tags = $service->getAllNewsTags();
        }
    } catch (Exception $e) {
        error_log("Error loading news tags: " . $e->getMessage());
    }
    
} catch (Exception $e) {
    // Log error and redirect
    error_log('Admin News Edit View Error: ' . $e->getMessage());
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
    'tags' => ''
], $current_news);

// Handle form submission
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
    
    // Transform form data to match model fields
    $update_data = $form_data;
    if (isset($update_data['author'])) {
        $update_data['author_name'] = $update_data['author'];
        unset($update_data['author']);
    }
    
    // Handle image upload - if a file is uploaded, process it
    if (!empty($_FILES['image']['name'])) {
        // Handle image upload
        $uploadDir = dirname(__DIR__, 4) . '/assets/uploads/news/';
        $uploadUrl = '/assets/uploads/news/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileName = time() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $fileName;
        
        // Try move_uploaded_file first (more secure), then copy as fallback
        $uploadSuccess = move_uploaded_file($_FILES['image']['tmp_name'], $targetPath);
        if (!$uploadSuccess) {
            $uploadSuccess = copy($_FILES['image']['tmp_name'], $targetPath);
        }
        
        if ($uploadSuccess && file_exists($targetPath)) {
            $update_data['image'] = $uploadUrl . $fileName;
        } else {
            $errors[] = 'Không thể tải lên hình ảnh. Lỗi: ' . $_FILES['image']['error'];
        }
    } elseif (!empty($_POST['image_url'])) {
        // Handle image URL input - exactly like categories/edit.php
        $update_data['image'] = trim($_POST['image_url']);
    } else {
        // Keep existing image - don't include 'image' in update data
        unset($update_data['image']);
    }
    
    if (empty($errors)) {
        if ($service->updateNews($news_id, $update_data)) {
            // Use PRG pattern - redirect after successful POST
            if (!headers_sent($filename, $linenum)) {
                header('Location: ?page=admin&module=news&action=view&id=' . $news_id . '&updated=1');
                exit;
            } else {
                // Fallback: if headers sent, use JavaScript redirect
                ?>
                <script>
                console.log('Redirecting via JS...');
                window.location.href = "?page=admin&module=news&action=view&id=<?= $news_id ?>&updated=1";
                </script>
                <div style="padding:20px;text-align:center;">
                    <p>Đang chuyển hướng...</p>
                    <a href="?page=admin&module=news&action=view&id=<?= $news_id ?>&updated=1">Nhấn vào đây nếu không tự chuyển</a>
                </div>
                <?php
                exit;
            }
        } else {
            $errors[] = 'Có lỗi xảy ra khi cập nhật tin tức';
        }
    }
}

// Format date function
function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}

// Helper function to get selected category name
function getSelectedCategoryName($categories, $selectedId) {
    foreach ($categories as $category) {
        if ($category['id'] == $selectedId) {
            return $category['name'];
        }
    }
    return '';
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

                        <div class="form-group">
                            <label for="category_id">Danh mục:</label>
                            <div class="category-dropdown-wrapper">
                                <div class="custom-category-dropdown">
                                    <input type="hidden" id="category_id" name="category_id" value="<?= htmlspecialchars($form_data['category_id'] ?? '') ?>">
                                    <div class="category-dropdown-display" onclick="toggleCategoryDropdown()">
                                        <span id="selected-category-text">
                                            <?= $form_data['category_id'] ? getSelectedCategoryName($news_categories, $form_data['category_id']) : '-- Chọn danh mục --' ?>
                                        </span>
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                    <div class="category-dropdown-options" id="categoryDropdown" style="display: none;">
                                        <div class="category-option" data-value="" onclick="selectCategory('', '-- Chọn danh mục --')">
                                            <span>-- Chọn danh mục --</span>
                                        </div>
                                        <?php foreach ($news_categories as $category): ?>
                                            <div class="category-option" data-value="<?= $category['id'] ?>" 
                                                 onclick="selectCategory('<?= $category['id'] ?>', '<?= htmlspecialchars($category['name']) ?>')">
                                                <span><?= htmlspecialchars($category['name']) ?></span>
                                                <button type="button" class="btn-delete-category" 
                                                        onclick="deleteCategory(<?= $category['id'] ?>, '<?= htmlspecialchars($category['name']) ?>', event)"
                                                        title="Xóa danh mục">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline" onclick="addNewCategory()">
                                    <i class="fas fa-plus"></i> Thêm danh mục mới
                                </button>
                            </div>
                            <small>Chọn danh mục để phân loại tin tức. Nếu chưa có, bạn có thể thêm nhanh danh mục mới.</small>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="form-section">
                        <h3 class="section-title">Nội Dung</h3>
                        
                        <div class="form-group">
                            <label for="content" class="required">Nội dung chi tiết:</label>
                            
                            <!-- Enhanced Custom Rich Text Toolbar -->
                            <div class="custom-editor-toolbar" data-for="content">
                                <div class="toolbar-group">
                                    <button type="button" onclick="applyFormat('bold', 'content')" title="In đậm"><i class="fas fa-bold"></i></button>
                                    <button type="button" onclick="applyFormat('italic', 'content')" title="In nghiêng"><i class="fas fa-italic"></i></button>
                                    <button type="button" onclick="applyFormat('underline', 'content')" title="Gạch chân"><i class="fas fa-underline"></i></button>
                                </div>
                                <div class="toolbar-group">
                                    <select onchange="applyStyle('fontFamily', this.value, 'content')" class="font-select">
                                        <option value="">Font chữ</option>
                                        <option value="Arial, sans-serif">Arial</option>
                                        <option value="'Inter', sans-serif">Inter</option>
                                        <option value="'Roboto', sans-serif">Roboto</option>
                                        <option value="serif">Serif</option>
                                        <option value="monospace">Monospace</option>
                                    </select>
                                    <div class="size-input-wrapper">
                                        <input type="number" value="16" min="10" max="100" onchange="applyStyle('fontSize', this.value + 'px', 'content')" class="size-input">
                                        <span>px</span>
                                    </div>
                                </div>
                                <div class="toolbar-group">
                                    <div class="color-picker-wrapper">
                                        <input type="color" onchange="applyStyle('color', this.value, 'content')" title="Màu chữ">
                                        <i class="fas fa-font"></i>
                                    </div>
                                    <button type="button" onclick="applyFormat('removeFormat', 'content')" title="Xóa định dạng"><i class="fas fa-eraser"></i></button>
                                </div>
                                <div class="toolbar-group">
                                    <button type="button" onclick="applyFormat('justifyLeft', 'content')" title="Căn trái"><i class="fas fa-align-left"></i></button>
                                    <button type="button" onclick="applyFormat('justifyCenter', 'content')" title="Căn giữa"><i class="fas fa-align-center"></i></button>
                                    <button type="button" onclick="applyFormat('justifyRight', 'content')" title="Căn phải"><i class="fas fa-align-right"></i></button>
                                </div>
                                <div class="toolbar-group">
                                    <button type="button" onclick="applyFormat('insertUnorderedList', 'content')" title="Danh sách gạch đầu dòng"><i class="fas fa-list-ul"></i></button>
                                    <button type="button" onclick="applyFormat('insertOrderedList', 'content')" title="Danh sách số thứ tự"><i class="fas fa-list-ol"></i></button>
                                </div>
                            </div>
                            
                            <!-- Editable Area -->
                            <div id="editor-content" class="custom-editable-area" contenteditable="true" oninput="syncEditor('content')" style="min-height: 400px;">
                                <?= $form_data['content'] ?>
                            </div>
                            <textarea id="content" name="content" style="display:none;" required><?= htmlspecialchars($form_data['content']) ?></textarea>
                            <small>Sử dụng thanh công cụ để định dạng văn bản: in đậm, in nghiêng, gạch chân, màu sắc, kích thước font, căn lề, danh sách, v.v.</small>
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
                            <label>Chọn hình ảnh:</label>
                            <div class="image-upload-container">
                                <div class="image-preview" id="imagePreview" onclick="document.getElementById('image').click()" style="cursor:pointer;">
                                    <?php if (!empty($form_data['image'])): ?>
                                        <img id="preview-img" src="<?= htmlspecialchars($form_data['image']) ?>" alt="Current Image" style="max-width:100%;max-height:100%;object-fit:contain;">
                                        <div id="preview-placeholder" style="display:none;">
                                            <i class="fas fa-image"></i>
                                            <p>Click để chọn hình ảnh</p>
                                        </div>
                                    <?php else: ?>
                                        <img id="preview-img" src="" alt="Preview" style="display:none;">
                                        <div id="preview-placeholder">
                                            <i class="fas fa-image"></i>
                                            <p>Click để chọn hình ảnh</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <input type="file" id="image" name="image" class="image-input" 
                                       accept="image/*" style="display:none;" onchange="previewUploadedImage(this)">
                                <div class="image-upload-info">
                                    <small>Định dạng: JPG, PNG, GIF. Kích thước tối đa: 2MB</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group" style="margin-top:16px;">
                            <label for="image_url">Hoặc nhập URL ảnh:</label>
                            <input type="text" id="image_url" name="image_url" 
                                   value="<?= htmlspecialchars($form_data['image'] ?? '') ?>" 
                                   placeholder="https://example.com/image.jpg"
                                   oninput="updateImageFromUrl(this.value)">
                            <small>Nhập URL ảnh (vd: https://example.com/image.jpg) hoặc click ra ngoài để cập nhật preview</small>
                        </div>
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
                            <label for="tagInput">Thẻ (Tags):</label>
                            <div class="tags-input-wrapper">
                                <div class="tags-chips-container" id="tagsChipsContainer">
                                    <!-- Chips dynamically generated -->
                                </div>
                                <input type="text" id="tagInput" placeholder="Chọn thẻ từ danh sách hoặc nhập..." autocomplete="off">
                                <div class="tag-suggestions-dropdown" id="tagSuggestionsDropdown" style="display: none;">
                                    <!-- Inline Add Form at the top -->
                                    <div class="tag-add-inline-form" onclick="event.stopPropagation();">
                                        <input type="text" id="inlineNewTagName" placeholder="Nhập thẻ mới..." autocomplete="off">
                                        <button type="button" id="btnInlineAddTag"><i class="fas fa-plus"></i> Thêm</button>
                                    </div>
                                    <div class="tag-dropdown-divider"></div>
                                    <!-- Tag list container -->
                                    <div class="tag-list-container" id="dropdownTagList">
                                        <!-- Dynamic tags loaded with delete button -->
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" id="newsTagsInput" name="tags" value="<?= htmlspecialchars($form_data['tags'] ?? '') ?>">
                            <small>Chọn từ các thẻ có sẵn hoặc thêm thẻ mới. Thẻ giúp phân loại và tối ưu SEO cho bài viết.</small>
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

// Preview image from file upload
function previewUploadedImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            // Update the main image preview
            var imagePreview = document.getElementById('imagePreview');
            imagePreview.innerHTML = '<img id="preview-img" src="' + e.target.result + '" alt="New image" style="max-width:100%;max-height:100%;object-fit:contain;">';
            // Clear URL input when file is selected
            var urlInput = document.getElementById('image_url');
            if (urlInput) {
                urlInput.value = '';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Update image preview from URL
function updateImageFromUrl(url) {
    // Don't process if it's a base64 data URL
    if (url && url.indexOf('data:') === 0) {
        console.log('Skipping base64 data URL');
        return;
    }
    
    if (url && url.indexOf('http') === 0) {
        var imagePreview = document.getElementById('imagePreview');
        imagePreview.innerHTML = '<img id="preview-img" src="' + url + '" alt="URL Image" style="max-width:100%;max-height:100%;object-fit:contain;" onerror="this.onerror=null; this.src=\'/assets/images/default-news.jpg\';">';
        // Clear file input
        var fileInput = document.getElementById('image');
        if (fileInput) {
            fileInput.value = '';
        }
    } else {
        // Clear preview if URL is invalid
        var imagePreview = document.getElementById('imagePreview');
        if (imagePreview) {
            imagePreview.innerHTML = '<div id="preview-placeholder"><i class="fas fa-image"></i><p>Click để chọn hình ảnh</p></div>';
        }
    }
}

// Preview image (legacy - kept for compatibility)
function previewImage(input) {
    previewUploadedImage(input);
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

// Warn before leaving if form is dirty - only when navigating away WITHOUT submitting
var adminNewsFormChanged = false;
var isFormSubmitting = false;

document.querySelectorAll('input, textarea, select').forEach(function(element) {
    element.addEventListener('input', function() {
        adminNewsFormChanged = true;
    });
    element.addEventListener('change', function() {
        adminNewsFormChanged = true;
    });
});

// Prevent default beforeunload behavior - we'll handle it manually
window.addEventListener('beforeunload', function(e) {
    // Chỉ hiển thị cảnh báo khi KHÔNG phải đang submit form
    if (adminNewsFormChanged && !isFormSubmitting) {
        e.preventDefault();
        // Chrome không cần returnValue
        return;
    }
});

// Submit button click - set flag TRƯỚC KHI form submission xảy ra
var submitButtons = document.querySelectorAll('button[type="submit"], input[type="submit"]');
submitButtons.forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        isFormSubmitting = true;
        adminNewsFormChanged = false;
    });
});

// Form submit event - as backup
document.querySelector('form').addEventListener('submit', function(e) {
    isFormSubmitting = true;
    adminNewsFormChanged = false;
});

// Custom Editor Functions (like Hero Section)
function applyFormat(command, field) {
    document.getElementById('editor-' + field).focus();
    document.execCommand(command, false, null);
    syncEditor(field);
    adminNewsFormChanged = true;
}

function applyStyle(property, value, field) {
    const editor = document.getElementById('editor-' + field);
    editor.focus();
    
    // Get selection
    const selection = window.getSelection();
    if (!selection.rangeCount) return;
    
    // For font size, color, font family - use spans for exact control
    if (property === 'fontSize' || property === 'color' || property === 'fontFamily') {
        const range = selection.getRangeAt(0);
        if (range.collapsed) return; // No selection
        
        const span = document.createElement('span');
        span.style[property] = value;
        range.surroundContents(span);
    } else {
        document.execCommand(property, false, value);
    }
    
    syncEditor(field);
    adminNewsFormChanged = true;
}

function syncEditor(field) {
    const editor = document.getElementById('editor-' + field);
    const textarea = document.getElementById(field);
    textarea.value = editor.innerHTML;
}

// Initialize custom editor
document.addEventListener('DOMContentLoaded', function() {
    // Sync form submission with custom editor
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Sync editor content before submission
            syncEditor('content');
        });
    }
    
    // Track editor changes for dirty form tracking
    const editor = document.getElementById('editor-content');
    if (editor) {
        editor.addEventListener('input', function() {
            adminNewsFormChanged = true;
        });
        
        editor.addEventListener('paste', function() {
            adminNewsFormChanged = true;
        });
    }
});
</script>

<style>
/* Custom Editor Styles (like Hero Section) */
.custom-editor-toolbar { 
    background: #fdfdfd; 
    border: 1px solid #e0e0e0; 
    border-bottom: none; 
    border-radius: 8px 8px 0 0; 
    padding: 10px; 
    display: flex; 
    flex-wrap: wrap; 
    gap: 15px; 
    align-items: center; 
}

.toolbar-group { 
    display: flex; 
    gap: 5px; 
    align-items: center; 
    border-right: 1px solid #eee; 
    padding-right: 15px; 
}

.toolbar-group:last-child { 
    border-right: none; 
}

.custom-editor-toolbar button { 
    background: white; 
    border: 1px solid #ddd; 
    border-radius: 6px; 
    width: 34px; 
    height: 34px; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    cursor: pointer; 
    color: #555; 
    transition: all 0.2s;
}

.custom-editor-toolbar button:hover { 
    background: #f0f4ff; 
    color: #356DF1; 
    border-color: #356DF1; 
}

.font-select { 
    padding: 6px 10px; 
    border: 1px solid #ddd; 
    border-radius: 6px; 
    font-size: 13px; 
}

.size-input-wrapper { 
    display: flex; 
    align-items: center; 
    gap: 5px; 
    background: #fff; 
    border: 1px solid #ddd; 
    border-radius: 6px; 
    padding: 0 8px; 
}

.size-input { 
    border: none; 
    width: 45px; 
    padding: 6px 0; 
    font-size: 13px; 
    text-align: center; 
    outline: none; 
}

.size-input-wrapper span { 
    font-size: 12px; 
    color: #888; 
}

.color-picker-wrapper { 
    position: relative; 
    width: 34px; 
    height: 34px; 
    border: 1px solid #ddd; 
    border-radius: 6px; 
    overflow: hidden; 
}

.color-picker-wrapper input[type="color"] { 
    position: absolute; 
    top: -5px; 
    left: -5px; 
    width: 50px; 
    height: 50px; 
    cursor: pointer; 
    opacity: 0; 
    z-index: 2; 
}

.color-picker-wrapper i { 
    position: absolute; 
    top: 50%; 
    left: 50%; 
    transform: translate(-50%, -50%); 
    z-index: 1; 
    color: #555; 
}

.custom-editable-area { 
    min-height: 150px; 
    border: 1px solid #e0e0e0; 
    border-radius: 0 0 8px 8px; 
    padding: 20px; 
    background: white; 
    outline: none; 
    font-size: 1rem; 
    line-height: 1.6; 
}

.custom-editable-area:focus { 
    border-color: #356DF1; 
    box-shadow: 0 0 0 3px rgba(53, 109, 241, 0.1);
}

/* Category Dropdown Styles */
.category-dropdown-wrapper {
    display: flex;
    gap: 10px;
    align-items: center;
}

.category-dropdown-wrapper .form-select {
    flex: 1;
    padding: 10px 15px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    background: white;
    transition: border-color 0.2s;
}

.category-dropdown-wrapper .form-select:focus {
    border-color: #356DF1;
    outline: none;
    box-shadow: 0 0 0 3px rgba(53, 109, 241, 0.1);
}

.category-dropdown-wrapper .btn-sm {
    padding: 8px 12px;
    font-size: 13px;
    white-space: nowrap;
}

/* Modal for adding new category */
.category-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;
}

.category-modal-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 25px;
    border-radius: 12px;
    width: 90%;
    max-width: 400px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

.category-modal-header {
    margin-bottom: 20px;
}

.category-modal-header h3 {
    margin: 0;
    font-size: 18px;
    color: #111827;
}

.category-modal-body {
    margin-bottom: 20px;
}

.category-modal-body input {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
}

.category-modal-body input:focus {
    border-color: #356DF1;
    outline: none;
    box-shadow: 0 0 0 3px rgba(53, 109, 241, 0.1);
}

.category-modal-footer {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.category-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

/* Custom Category Dropdown Styles */
.custom-category-dropdown {
    position: relative;
    width: 100%;
}

.category-dropdown-display {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    background: white;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: border-color 0.2s;
}

.category-dropdown-display:hover {
    border-color: #356DF1;
}

.category-dropdown-display i {
    color: #666;
    transition: transform 0.2s;
}

.category-dropdown-display.active i {
    transform: rotate(180deg);
}

.category-dropdown-options {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ddd;
    border-top: none;
    border-radius: 0 0 6px 6px;
    max-height: 200px;
    overflow-y: auto;
    z-index: 1000;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.category-option {
    padding: 10px 12px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #f0f0f0;
    transition: background-color 0.2s;
}

.category-option:last-child {
    border-bottom: none;
}

.category-option:hover {
    background-color: #f8f9fa;
}

.category-option span {
    flex: 1;
}

.btn-delete-category {
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 4px 8px;
    cursor: pointer;
    font-size: 12px;
    opacity: 0.8;
    transition: all 0.2s;
    margin-left: 8px;
}

.btn-delete-category:hover {
    opacity: 1;
    background: #c82333;
    transform: scale(1.05);
}

.btn-delete-category i {
    font-size: 11px;
}

/* Premium Tags Input Styles */
.tags-input-wrapper {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: #ffffff;
    min-height: 46px;
    position: relative;
    transition: all 0.2s ease;
}

.tags-input-wrapper:focus-within {
    border-color: #356DF1;
    box-shadow: 0 0 0 3px rgba(53, 109, 241, 0.15);
}

.tags-chips-container {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.tag-chip {
    display: inline-flex;
    align-items: center;
    background: #eff6ff;
    color: #356DF1;
    font-size: 13px;
    font-weight: 500;
    padding: 4px 10px;
    border-radius: 6px;
    border: 1px solid #dbeafe;
    transition: all 0.2s ease;
    user-select: none;
}

.tag-chip:hover {
    background: #dbeafe;
    color: #1e40af;
}

.tag-chip-remove {
    margin-left: 6px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    font-size: 10px;
    transition: all 0.15s ease;
    color: #93c5fd;
}

.tag-chip-remove:hover {
    background: #356DF1;
    color: #ffffff;
}

#tagInput {
    border: none !important;
    outline: none !important;
    background: transparent !important;
    padding: 4px 0 !important;
    margin: 0 !important;
    flex: 1;
    min-width: 120px;
    font-size: 14px;
    color: #1f2937;
    box-shadow: none !important;
}

#tagInput::placeholder {
    color: #9ca3af;
}

/* Tag Suggestions Dropdown */
.tag-suggestions-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    margin-top: 4px;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    z-index: 1000;
    display: none;
}

.tag-add-inline-form {
    display: flex;
    gap: 6px;
    padding: 8px 12px;
    background: #f9fafb;
    border-bottom: 1px solid #f3f4f6;
    position: sticky;
    top: 0;
    z-index: 1;
}

.tag-add-inline-form input {
    flex: 1;
    height: 30px;
    padding: 4px 8px !important;
    font-size: 13px !important;
    border: 1px solid #d1d5db !important;
    border-radius: 4px !important;
    background: #ffffff !important;
    box-shadow: none !important;
}

.tag-add-inline-form input:focus {
    border-color: #356DF1 !important;
    outline: none !important;
}

.tag-add-inline-form button {
    height: 30px;
    padding: 0 10px;
    background: #356DF1;
    color: #ffffff;
    border: none;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
    font-weight: 500;
    transition: background-color 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.tag-add-inline-form button:hover {
    background: #1d4ed8;
}

.tag-dropdown-divider {
    height: 1px;
    background: #e5e7eb;
}

.tag-list-container {
    max-height: 200px;
    overflow-y: auto;
}

.dropdown-tag-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 12px;
    cursor: pointer;
    font-size: 13px;
    color: #374151;
    transition: all 0.15s ease;
}

.dropdown-tag-item:hover {
    background: #f3f4f6;
    color: #111827;
}

.dropdown-tag-item.selected {
    background: #eff6ff;
    color: #1e40af;
    font-weight: 500;
}

.dropdown-tag-item span.tag-name-text {
    flex: 1;
    display: inline-flex;
    align-items: center;
}

.dropdown-tag-item.selected span.tag-name-text::after {
    content: '✓';
    margin-left: 8px;
    font-size: 11px;
    color: #356DF1;
}

.tag-delete-btn {
    border: none;
    background: transparent;
    color: #9ca3af;
    cursor: pointer;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 12px;
    transition: all 0.2s ease;
}

.tag-delete-btn:hover {
    background: #fee2e2;
    color: #ef4444;
}

</style>

<script>
// Add new category functionality
function addNewCategory() {
    // Create modal if it doesn't exist
    let modal = document.getElementById('categoryModal');
    if (!modal) {
        modal = createCategoryModal();
        document.body.appendChild(modal);
    }
    
    // Show modal
    modal.style.display = 'block';
    document.getElementById('categoryNameInput').value = '';
    document.getElementById('categoryNameInput').focus();
}

function createCategoryModal() {
    const modal = document.createElement('div');
    modal.id = 'categoryModal';
    modal.className = 'category-modal';
    modal.innerHTML = `
        <div class="category-modal-overlay" onclick="closeCategoryModal()"></div>
        <div class="category-modal-content">
            <div class="category-modal-header">
                <h3>Thêm Danh Mục Mới</h3>
            </div>
            <div class="category-modal-body">
                <input type="text" id="categoryNameInput" placeholder="Nhập tên danh mục..." 
                       onkeypress="if(event.key === 'Enter') saveNewCategory()">
            </div>
            <div class="category-modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeCategoryModal()">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="saveNewCategory()">Thêm</button>
            </div>
        </div>
    `;
    return modal;
}

function closeCategoryModal() {
    const modal = document.getElementById('categoryModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function saveNewCategory() {
    const categoryName = document.getElementById('categoryNameInput').value.trim();
    
    if (!categoryName) {
        alert('Vui lòng nhập tên danh mục');
        document.getElementById('categoryNameInput').focus();
        return;
    }
    
    // Create slug from category name
    const slug = categoryName
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/[\s-]+/g, '-')
        .replace(/^-+|-+$/g, '');
    
    // Send AJAX request to save category
    fetch('?page=admin&module=news&action=add_category_ajax', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `name=${encodeURIComponent(categoryName)}&slug=${encodeURIComponent(slug)}&type=news`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Add new option to custom dropdown
            const dropdown = document.getElementById('categoryDropdown');
            const newOption = document.createElement('div');
            newOption.className = 'category-option';
            newOption.setAttribute('data-value', data.category_id);
            newOption.onclick = function() {
                selectCategory(data.category_id, categoryName);
            };
            newOption.innerHTML = `
                <span>${categoryName}</span>
                <button type="button" class="btn-delete-category" 
                        onclick="deleteCategory(${data.category_id}, '${categoryName}', event)"
                        title="Xóa danh mục">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            
            // Insert before the last child (before the empty option)
            const emptyOption = dropdown.querySelector('[data-value=""]');
            if (emptyOption) {
                dropdown.insertBefore(newOption, emptyOption.nextSibling);
            } else {
                dropdown.appendChild(newOption);
            }
            
            // Select the new category
            selectCategory(data.category_id, categoryName);
            
            // Close modal
            closeCategoryModal();
            
            // Show success message
            showSuccessMessage('Đã thêm danh mục "' + categoryName + '" thành công!');
        } else {
            alert('Lỗi: ' + (data.message || 'Không thể thêm danh mục'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra. Vui lòng thử lại.');
    });
}

function showSuccessMessage(message) {
    // Create success alert
    const alert = document.createElement('div');
    alert.className = 'alert alert-success';
    alert.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 10000; min-width: 300px;';
    alert.innerHTML = `
        <i class="fas fa-check-circle"></i>
        <div>${message}</div>
    `;
    
    document.body.appendChild(alert);
    
    // Remove after 3 seconds
    setTimeout(() => {
        if (alert.parentNode) {
            alert.parentNode.removeChild(alert);
        }
    }, 3000);
}

// Handle ESC key for modal
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCategoryModal();
    }
});

// Custom Dropdown Functions
function toggleCategoryDropdown() {
    const dropdown = document.getElementById('categoryDropdown');
    if (dropdown.style.display === 'none') {
        dropdown.style.display = 'block';
    } else {
        dropdown.style.display = 'none';
    }
}

function selectCategory(value, text) {
    document.getElementById('category_id').value = value;
    document.getElementById('selected-category-text').textContent = text;
    document.getElementById('categoryDropdown').style.display = 'none';
}

function deleteCategory(categoryId, categoryName, event) {
    event.stopPropagation();
    
    if (!confirm(`Bạn có chắc chắn muốn xóa danh mục "${categoryName}"?\n\nLưu ý: Danh mục này sẽ bị xóa vĩnh viễn và không thể khôi phục lại.`)) {
        return;
    }
    
    // Send AJAX request to delete category
    fetch('?page=admin&module=news&action=delete_category_ajax', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `category_id=${categoryId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove the category option from dropdown
            const option = document.querySelector(`[data-value="${categoryId}"]`);
            if (option) {
                option.remove();
            }
            
            // If this category was selected, reset selection
            if (document.getElementById('category_id').value == categoryId) {
                selectCategory('', '-- Chọn danh mục --');
            }
            
            showSuccessMessage(`Đã xóa danh mục "${categoryName}" thành công!`);
        } else {
            alert('Lỗi: ' + (data.message || 'Không thể xóa danh mục'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra. Vui lòng thử lại.');
    });
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('categoryDropdown');
    const dropdownDisplay = document.querySelector('.category-dropdown-display');
    
    if (dropdown && dropdownDisplay && !dropdownDisplay.contains(event.target) && !dropdown.contains(event.target)) {
        dropdown.style.display = 'none';
    }
});

// Premium Tags Input System with Inline Dropdown Management
document.addEventListener('DOMContentLoaded', function() {
    let existingTags = <?= json_encode($all_tags) ?>;
    const newsTagsInput = document.getElementById('newsTagsInput');
    const tagsChipsContainer = document.getElementById('tagsChipsContainer');
    const tagInput = document.getElementById('tagInput');
    const tagSuggestionsDropdown = document.getElementById('tagSuggestionsDropdown');
    const dropdownTagList = document.getElementById('dropdownTagList');
    const inlineNewTagName = document.getElementById('inlineNewTagName');
    const btnInlineAddTag = document.getElementById('btnInlineAddTag');
    
    let currentTags = [];

    // Initialize tags
    if (newsTagsInput && newsTagsInput.value) {
        currentTags = newsTagsInput.value.split(',')
            .map(t => t.trim())
            .filter(t => t !== '');
        renderTags();
    }

    function renderTags() {
        tagsChipsContainer.innerHTML = '';
        currentTags.forEach((tag, index) => {
            const chip = document.createElement('div');
            chip.className = 'tag-chip';
            chip.innerHTML = `
                <span>${escapeHtml(tag)}</span>
                <span class="tag-chip-remove" data-index="${index}"><i class="fas fa-times"></i></span>
            `;
            tagsChipsContainer.appendChild(chip);
        });
        
        // Update hidden input
        newsTagsInput.value = currentTags.join(',');
        
        // Setup remove click events
        document.querySelectorAll('.tag-chip-remove').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const idx = parseInt(this.getAttribute('data-index'));
                removeTag(idx);
            });
        });

        // Update selected state in dropdown
        renderDropdownList();
    }

    function addTag(tagText) {
        tagText = tagText.trim();
        if (!tagText) return;
        
        // Check duplicate case-insensitive
        const lowerTags = currentTags.map(t => t.toLowerCase());
        if (!lowerTags.includes(tagText.toLowerCase())) {
            currentTags.push(tagText);
            renderTags();
            if (typeof adminNewsFormChanged !== 'undefined') {
                adminNewsFormChanged = true;
            }
        }
    }

    function removeTag(index) {
        currentTags.splice(index, 1);
        renderTags();
        if (typeof adminNewsFormChanged !== 'undefined') {
            adminNewsFormChanged = true;
        }
    }

    // Render dropdown list of tags
    function renderDropdownList() {
        if (!dropdownTagList) return;
        dropdownTagList.innerHTML = '';

        const searchQuery = tagInput.value.toLowerCase().trim();

        // Sort existing tags alphabetically by name
        existingTags.sort((a, b) => a.name.localeCompare(b.name, 'vi'));

        let visibleCount = 0;
        existingTags.forEach(tag => {
            const isSelected = currentTags.some(t => t.toLowerCase() === tag.name.toLowerCase());
            const matchesSearch = tag.name.toLowerCase().includes(searchQuery);

            if (searchQuery && !matchesSearch) {
                return; // skip if doesn't match search
            }

            visibleCount++;
            const item = document.createElement('div');
            item.className = 'dropdown-tag-item';
            if (isSelected) {
                item.classList.add('selected');
            }

            item.innerHTML = `
                <span class="tag-name-text">${escapeHtml(tag.name)}</span>
                <button type="button" class="tag-delete-btn" data-id="${tag.id}" data-name="${escapeHtml(tag.name)}" title="Xóa thẻ khỏi hệ thống">
                    <i class="fas fa-trash"></i>
                </button>
            `;

            // Toggle selection on click (clicking the item, but not the delete button)
            item.addEventListener('click', function(e) {
                if (e.target.closest('.tag-delete-btn')) return; // ignore delete clicks

                if (isSelected) {
                    // Remove
                    const index = currentTags.findIndex(t => t.toLowerCase() === tag.name.toLowerCase());
                    if (index !== -1) {
                        removeTag(index);
                    }
                } else {
                    // Add
                    addTag(tag.name);
                }
                tagInput.value = ''; // clear search input on select
                renderDropdownList();
            });

            // Handle delete button click
            const deleteBtn = item.querySelector('.tag-delete-btn');
            deleteBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                const tagId = this.getAttribute('data-id');
                const tagName = this.getAttribute('data-name');
                deleteSystemTag(tagId, tagName);
            });

            dropdownTagList.appendChild(item);
        });

        if (visibleCount === 0) {
            const noResult = document.createElement('div');
            noResult.style.padding = '8px 12px';
            noResult.style.color = '#9ca3af';
            noResult.style.fontSize = '13px';
            noResult.textContent = searchQuery ? 'Không tìm thấy thẻ nào khớp' : 'Chưa có thẻ nào trong hệ thống';
            dropdownTagList.appendChild(noResult);
        }
    }

    // Ajax add system tag
    function addSystemTag(name) {
        name = name.trim();
        if (!name) return;

        // Check locally if exists in existingTags
        const exists = existingTags.some(t => t.name.toLowerCase() === name.toLowerCase());
        if (exists) {
            alert('Thẻ này đã tồn tại trong danh sách.');
            // Automatically select it
            addTag(name);
            inlineNewTagName.value = '';
            tagInput.value = '';
            renderDropdownList();
            return;
        }

        const formData = new FormData();
        formData.append('name', name);

        fetch('?page=admin&module=news&action=add_tag_ajax', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.tag) {
                existingTags.push(data.tag);
                addTag(data.tag.name);
                inlineNewTagName.value = '';
                tagInput.value = '';
                renderDropdownList();
            } else {
                alert('Lỗi: ' + (data.message || 'Không thể thêm thẻ'));
            }
        })
        .catch(error => {
            console.error('Error adding tag:', error);
            alert('Có lỗi xảy ra khi thêm thẻ.');
        });
    }

    // Ajax delete system tag
    function deleteSystemTag(tagId, tagName) {
        if (!confirm(`Bạn có chắc chắn muốn xóa thẻ "${tagName}" khỏi hệ thống?\nThao tác này sẽ gỡ thẻ khỏi tất cả các bài viết đang gán thẻ này.`)) {
            return;
        }

        const formData = new FormData();
        formData.append('tag_id', tagId);

        fetch('?page=admin&module=news&action=delete_tag_ajax', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove from existingTags list
                existingTags = existingTags.filter(t => parseInt(t.id) !== parseInt(tagId));
                
                // Remove from current article's selected tags
                const index = currentTags.findIndex(t => t.toLowerCase() === tagName.toLowerCase());
                if (index !== -1) {
                    currentTags.splice(index, 1);
                }
                
                renderTags();
            } else {
                alert('Lỗi: ' + (data.message || 'Không thể xóa thẻ'));
            }
        })
        .catch(error => {
            console.error('Error deleting tag:', error);
            alert('Có lỗi xảy ra khi xóa thẻ.');
        });
    }

    function escapeHtml(string) {
        return String(string).replace(/[&<>"'`=\/]/g, function (s) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;',
                '/': '&#x2F;',
                '=': '&#x3D;'
            }[s];
        });
    }

    // Inline Add Tag event listeners
    if (btnInlineAddTag) {
        btnInlineAddTag.addEventListener('click', function() {
            addSystemTag(inlineNewTagName.value);
        });
    }

    if (inlineNewTagName) {
        inlineNewTagName.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addSystemTag(this.value);
            }
        });
    }

    // Main tagInput events for filtering and showing dropdown
    tagInput.addEventListener('focus', function() {
        tagSuggestionsDropdown.style.display = 'block';
        renderDropdownList();
    });

    tagInput.addEventListener('input', function() {
        tagSuggestionsDropdown.style.display = 'block';
        renderDropdownList();
    });

    // Close dropdown when clicking outside the input wrapper
    document.addEventListener('click', function(e) {
        const wrapper = document.querySelector('.tags-input-wrapper');
        if (wrapper && !wrapper.contains(e.target)) {
            tagSuggestionsDropdown.style.display = 'none';
        }
    });

    // Prevent closing when clicking inside the dropdown
    tagSuggestionsDropdown.addEventListener('click', function(e) {
        e.stopPropagation();
    });
});
</script>