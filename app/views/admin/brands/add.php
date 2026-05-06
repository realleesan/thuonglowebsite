<?php
/**
 * Admin Brands Add
 * Sử dụng AdminService thông qua ServiceManager
 */

// Khởi tạo View & ServiceManager
require_once __DIR__ . '/../../../../core/view_init.php';

// Chọn service admin (được inject từ index.php)
$service = isset($currentService) ? $currentService : ($adminService ?? null);

try {
} catch (Exception $e) {
    $errorHandler = new ErrorHandler();
    $errorHandler->logError('Admin Brands Add View Error', $e);
    header('Location: ?page=admin&module=brands&error=system_error');
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
        $errors[] = 'Tên thương hiệu không được để trống';
    }

    if (empty($slug)) {
        $errors[] = 'Slug không được để trống';
    } elseif (!preg_match('/^[a-z0-9-]+$/', $slug)) {
        $errors[] = 'Slug chỉ được chứa chữ thường, số và dấu gạch ngang';
    }

    // If no errors, save to database
    if (empty($errors)) {
        $brandData = [
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'status' => $status,
            'website' => $_POST['website'] ?? '',
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
            'show_in_filter' => isset($_POST['show_in_filter']) ? 1 : 0,
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0
        ];

        // Handle image upload
        if (!empty($_FILES['image']['name'])) {
            $uploadDir = dirname(__DIR__, 4) . '/assets/uploads/brands/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileName = time() . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $brandData['image'] = '/assets/uploads/brands/' . $fileName;
            }
        } elseif (!empty($_POST['image_url'])) {
            // Handle image URL
            $brandData['image'] = trim($_POST['image_url']);
        }

        $saved = $service->createBrand($brandData);
        if ($saved) {
            // Use PRG pattern - redirect after successful POST
            if (!headers_sent($filename, $linenum)) {
                header('Location: ?page=admin&module=brands&success=added');
                exit;
            } else {
                // Fallback: if headers sent, use JavaScript redirect
                ?>
                <script>
                window.location.href = "?page=admin&module=brands&success=added";
                </script>
                <div style="padding:20px;text-align:center;">
                    <p>Đang chuyển hướng...</p>
                    <a href="?page=admin&module=brands&success=added">Nhấn vào đây nếu không tự chuyển</a>
                </div>
                <?php
                exit;
            }
        } else {
            $errors[] = 'Không thể lưu thương hiệu';
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

<div class="brands-page brands-add-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-plus"></i>
                Thêm Thương Hiệu Mới
            </h1>
            <p class="page-description">Thêm thương hiệu sản phẩm mới vào hệ thống</p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=brands" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Quay lại danh sách
            </a>
        </div>
    </div>

    <!-- Error Messages -->
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

    <!-- Add Brand Form -->
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
                                <label for="name" class="required">Tên thương hiệu</label>
                                <input type="text" id="name" name="name"
                                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                                       placeholder="Nhập tên thương hiệu" required>
                            </div>
                            <div class="form-group col-6">
                                <label for="slug" class="required">Slug </label>
                                <div class="input-with-btn">
                                    <input type="text" id="slug" name="slug"
                                           value="<?= htmlspecialchars($_POST['slug'] ?? '') ?>"
                                           placeholder="ten-thuong-hieu" required>
                                    <button type="button" id="generateSlug" class="btn btn-sm btn-outline" title="Tự động tạo từ tên">
                                        <i class="fas fa-magic"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-6">
                                <label for="website">Website</label>
                                <input type="url" id="website" name="website"
                                       value="<?= htmlspecialchars($_POST['website'] ?? '') ?>"
                                       placeholder="https://example.com">
                                <small class="input-hint">Website chính thức của thương hiệu (nếu có)</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Mô tả thương hiệu</label>
                            <textarea id="description" name="description" rows="6"
                                      placeholder="Mô tả chi tiết về thương hiệu..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <!-- Tab 2: Hình Ảnh -->
                    <div class="tab-pane" id="tab-image">
                        <div class="form-group">
                            <label>Hình ảnh đại diện thương hiệu</label>
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
                                       placeholder="https://example.com/logo.png">
                                <small class="input-hint">Định dạng: JPG, PNG, GIF, SVG. Kích thước tối đa: 2MB</small>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 3: Cài Đặt Hiển Thị -->
                    <div class="tab-pane" id="tab-display">
                        <div class="form-row">
                            <div class="form-group col-6">
                                <label for="status">Trạng thái</label>
                                <select id="status" name="status">
                                    <option value="active" <?= (($_POST['status'] ?? 'active') == 'active') ? 'selected' : '' ?>>Hoạt động</option>
                                    <option value="inactive" <?= (($_POST['status'] ?? '') == 'inactive') ? 'selected' : '' ?>>Không hoạt động</option>
                                </select>
                            </div>
                            <div class="form-group col-6">
                                <label for="sort_order">Thứ tự sắp xếp</label>
                                <input type="number" id="sort_order" name="sort_order"
                                       value="<?= htmlspecialchars($_POST['sort_order'] ?? '0') ?>"
                                       placeholder="0" min="0">
                                <small class="input-hint">Số nhỏ hơn hiển thị trước</small>
                            </div>
                        </div>
                        
                        <div class="form-section-divider"></div>
                        <h4 class="subsection-title">Tùy chọn hiển thị</h4>

                        <div class="form-row checkbox-row">
                            <?php
                            // Xác định giá trị checkbox: nếu đang POST (có lỗi) thì dùng isset, nếu load lần đầu thì dùng giá trị mặc định
                            $isPost = ($_SERVER['REQUEST_METHOD'] === 'POST');
                            $showInFilter = $isPost ? (isset($_POST['show_in_filter']) ? 1 : 0) : 1; // default = 1
                            $featured = $isPost ? (isset($_POST['is_featured']) ? 1 : 0) : 0; // default = 0
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
                                    <input type="checkbox" name="is_featured" value="1" <?= $featured ? 'checked' : '' ?>>
                                    <span class="check-icon"><i class="fas fa-star"></i></span>
                                    <span class="checkbox-info">
                                        <strong>Thương hiệu nổi bật</strong>
                                        <small>Hiển thị ở section thương hiệu nổi bật trên trang chủ</small>
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
                    Lưu Thương Hiệu
                </button>
                <button type="button" class="btn btn-secondary" onclick="resetForm()">
                    <i class="fas fa-undo"></i>
                    Đặt lại
                </button>
                <a href="?page=admin&module=brands" class="btn btn-outline">
                    <i class="fas fa-times"></i>
                    Hủy
                </a>
            </div>
        </form>
    </div>
</div>

