<?php
/**
 * Admin Categories Add
 * Sử dụng AdminService thông qua ServiceManager
 */

// Khởi tạo View & ServiceManager
require_once __DIR__ . '/../../../../core/view_init.php';

// Chọn service admin (được inject từ index.php)
$service = isset($currentService) ? $currentService : ($adminService ?? null);

try {
} catch (Exception $e) {
    $errorHandler = new ErrorHandler();
    $errorHandler->logError('Admin Categories Add View Error', $e);
    header('Location: ?page=admin&module=categories&error=system_error');
    exit;
}

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'active';
    
    if (empty($name)) {
        $errors[] = 'Tên danh mục không được để trống';
    }
    
    if (empty($slug)) {
        $errors[] = 'Slug không được để trống';
    } elseif (!preg_match('/^[a-z0-9-]+$/', $slug)) {
        $errors[] = 'Slug chỉ được chứa chữ thường, số và dấu gạch ngang';
    }
    
    if (empty($description)) {
        $errors[] = 'Mô tả danh mục không được để trống';
    }
    
    // If no errors, save to database
    if (empty($errors)) {
        $categoryData = $_POST;
        
        // Handle image upload
        if (!empty($_FILES['image']['name'])) {
            $uploadDir = dirname(__DIR__, 4) . '/assets/uploads/categories/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = time() . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $categoryData['image'] = '/assets/uploads/categories/' . $fileName;
            }
        } elseif (!empty($_POST['image_url'])) {
            // Handle image URL
            $categoryData['image'] = trim($_POST['image_url']);
        }
        
        $saved = $service->createCategory($categoryData);
        if ($saved) {
            // Use PRG pattern - redirect after successful POST
            if (!headers_sent($filename, $linenum)) {
                header('Location: ?page=admin&module=categories&success=added');
                exit;
            } else {
                // Fallback: if headers sent, use JavaScript redirect
                ?>
                <script>
                window.location.href = "?page=admin&module=categories&success=added";
                </script>
                <div style="padding:20px;text-align:center;">
                    <p>Đang chuyển hướng...</p>
                    <a href="?page=admin&module=categories&success=added">Nhấn vào đây nếu không tự chuyển</a>
                </div>
                <?php
                exit;
            }
        } else {
            $errors[] = 'Không thể lưu danh mục';
        }
    }
}

// Auto-generate slug from name
function generateSlug($name) {
    $slug = strtolower($name);
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug;
}
?>

<div class="categories-page categories-add-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-plus"></i>
                Thêm Danh Mục Mới
            </h1>
            <p class="page-description">Thêm danh mục sản phẩm mới vào hệ thống</p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=categories" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Quay lại danh sách
            </a>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            Thêm danh mục thành công!
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i>
            <ul class="error-list">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Add Category Form -->
    <div class="form-container">
        <form method="POST" class="admin-form" enctype="multipart/form-data">

            <!-- Tab Navigation -->
            <div class="category-details-tabs">
                <div class="tabs-header">
                    <button type="button" class="tab-btn active" data-tab="tab-basic">
                        <i class="fas fa-info-circle"></i>
                        Thông Tin Cơ Bản
                    </button>
                    <button type="button" class="tab-btn" data-tab="tab-image">
                        <i class="fas fa-image"></i>
                        Hình Ảnh
                    </button>
                    <button type="button" class="tab-btn" data-tab="tab-seo">
                        <i class="fas fa-search"></i>
                        SEO
                    </button>
                    <button type="button" class="tab-btn" data-tab="tab-display">
                        <i class="fas fa-cog"></i>
                        Cài Đặt Hiển Thị
                    </button>
                </div>

                <div class="tabs-content">
                    <!-- Tab 1: Thông Tin Cơ Bản -->
                    <div class="tab-pane active" id="tab-basic">
                        <div class="form-row">
                            <div class="form-group col-6">
                                <label for="name" class="required">Tên danh mục</label>
                                <input type="text" id="name" name="name"
                                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                                       placeholder="Nhập tên danh mục" required>
                            </div>
                            <div class="form-group col-6">
                                <label for="slug" class="required">Slug </label>
                                <div class="input-with-btn">
                                    <input type="text" id="slug" name="slug"
                                           value="<?= htmlspecialchars($_POST['slug'] ?? '') ?>"
                                           placeholder="ten-danh-muc" required>
                                    <button type="button" id="generateSlug" class="btn btn-sm btn-outline" title="Tự động tạo từ tên">
                                        <i class="fas fa-magic"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description" class="required">Mô tả danh mục</label>
                            <textarea id="description" name="description" rows="6"
                                      placeholder="Mô tả chi tiết về danh mục..." required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <!-- Tab 2: Hình Ảnh -->
                    <div class="tab-pane" id="tab-image">
                        <div class="form-group">
                            <label>Hình ảnh đại diện danh mục</label>
                            <div class="image-upload-box" id="imagePreview" onclick="document.getElementById('image').click()">
                                <div class="image-placeholder">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span>Click để tải ảnh lên</span>
                                    <small>Hoặc kéo thả ảnh vào đây</small>
                                </div>
                            </div>
                            <input type="file" id="image" name="image" accept="image/*" style="display:none;" onchange="previewImageAdd(this)">

                            <div class="image-input-group">
                                <label>Hoặc nhập URL ảnh</label>
                                <input type="url" id="image_url" name="image_url"
                                       value="<?= htmlspecialchars($_POST['image_url'] ?? '') ?>"
                                       placeholder="https://example.com/image.jpg">
                                <small class="input-hint">Định dạng: JPG, PNG, GIF. Kích thước tối đa: 2MB</small>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 3: SEO -->
                    <div class="tab-pane" id="tab-seo">
                        <div class="form-row">
                            <div class="form-group col-6">
                                <label for="meta_title">Tiêu đề SEO</label>
                                <input type="text" id="meta_title" name="meta_title" maxlength="60"
                                       value="<?= htmlspecialchars($_POST['meta_title'] ?? '') ?>"
                                       placeholder="Tiêu đề hiển thị trên Google">
                                <small>Tối đa 60 ký tự</small>
                            </div>
                            <div class="form-group col-6">
                                <label for="keywords">Từ khóa</label>
                                <input type="text" id="keywords" name="keywords"
                                       value="<?= htmlspecialchars($_POST['keywords'] ?? '') ?>"
                                       placeholder="từ khóa 1, từ khóa 2, từ khóa 3">
                                <small>Phân cách bằng dấu phẩy</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="meta_description">Mô tả SEO</label>
                            <textarea id="meta_description" name="meta_description" rows="4" maxlength="160"
                                      placeholder="Mô tả ngắn gọn hiển thị trên Google"><?= htmlspecialchars($_POST['meta_description'] ?? '') ?></textarea>
                            <small>Tối đa 160 ký tự</small>
                        </div>
                    </div>

                    <!-- Tab 4: Cài Đặt Hiển Thị -->
                    <div class="tab-pane" id="tab-display">
                        <div class="form-row">
                            <div class="form-group col-6">
                                <label for="status">Trạng thái</label>
                                <select id="status" name="status">
                                    <option value="active" <?= (($_POST['status'] ?? 'active') == 'active') ? 'selected' : '' ?>>Hoạt động</option>
                                    <option value="inactive" <?= (($_POST['status'] ?? '') == 'inactive') ? 'selected' : '' ?>>Không hoạt động</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-section-divider"></div>
                        <h4 class="subsection-title">Tùy chọn hiển thị</h4>

                        <div class="form-row checkbox-row">
                            <?php
                            // Xác định giá trị checkbox: nếu đang POST (có lỗi) thì dùng isset, nếu load lần đầu thì dùng giá trị mặc định
                            $isPost = ($_SERVER['REQUEST_METHOD'] === 'POST');
                            $showInFilter = $isPost ? (isset($_POST['show_in_filter']) ? 1 : 0) : 1; // default = 1
                            $featured = $isPost ? (isset($_POST['featured']) ? 1 : 0) : 0; // default = 0
                            ?>
                            <div class="form-group checkbox-col">
                                <label class="checkbox-card">
                                    <input type="checkbox" name="show_in_filter" value="1" <?= $showInFilter ? 'checked' : '' ?>>
                                    <span class="check-icon"><i class="fas fa-filter"></i></span>
                                    <span class="checkbox-info">
                                        <strong>Hiển thị ở bộ lọc</strong>
                                        <small>Xuất hiện ở filter sản phẩm, dropdown header</small>
                                    </span>
                                </label>
                            </div>
                            <div class="form-group checkbox-col">
                                <label class="checkbox-card">
                                    <input type="checkbox" name="featured" value="1" <?= $featured ? 'checked' : '' ?>>
                                    <span class="check-icon"><i class="fas fa-star"></i></span>
                                    <span class="checkbox-info">
                                        <strong>Danh mục nổi bật</strong>
                                        <small>Hiển thị ở section danh mục nổi bật trên trang chủ</small>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Lưu Danh Mục
                </button>
                <button type="button" class="btn btn-secondary" onclick="resetForm()">
                    <i class="fas fa-undo"></i>
                    Đặt lại
                </button>
                <a href="?page=admin&module=categories" class="btn btn-outline">
                    <i class="fas fa-times"></i>
                    Hủy
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Tab switching
document.querySelectorAll('.tabs-header .tab-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var tabId = this.getAttribute('data-tab');

        // Remove active from all buttons and panes
        document.querySelectorAll('.tabs-header .tab-btn').forEach(function(b) {
            b.classList.remove('active');
        });
        document.querySelectorAll('.tabs-content .tab-pane').forEach(function(p) {
            p.classList.remove('active');
        });

        // Add active to clicked button and corresponding pane
        this.classList.add('active');
        document.getElementById(tabId).classList.add('active');
    });
});

// Preview image when selected for add form
function previewImageAdd(input) {
    const preview = document.getElementById('imagePreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">' +
                '<div class="image-overlay"><span><i class="fas fa-camera"></i> Click để thay đổi ảnh</span></div>';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

