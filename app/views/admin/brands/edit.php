<?php
/**
 * Admin Brands Edit
 * Sử dụng AdminService thông qua ServiceManager
 */

// Khởi tạo View & ServiceManager
require_once __DIR__ . '/../../../../core/view_init.php';

// Chọn service admin (được inject từ index.php)
$service = isset($currentService) ? $currentService : ($adminService ?? null);

try {
    // Get brand ID from URL
    $brand_id = (int)($_GET['id'] ?? 0);

    if (!$brand_id) {
        header('Location: ?page=admin&module=brands&error=invalid_id');
        exit;
    }

    // Get brand data using AdminService
    $brandData = $service->getBrandDetailsData($brand_id);
    $brand = $brandData['brand'];

    // Redirect if brand not found
    if (!$brand) {
        header('Location: ?page=admin&module=brands&error=not_found');
        exit;
    }

} catch (Exception $e) {
    $errorHandler->logError('Admin Brands Edit View Error', $e);
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

    // If no errors, update database
    if (empty($errors)) {
        $updateData = [
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'status' => $status,
            'website' => $_POST['website'] ?? '',
            'sort_order' => (int)($_POST['sort_order'] ?? 0)
        ];

        // Only update image if a new file was uploaded
        if (!empty($_FILES['image']['name'])) {
            // Handle image upload
            $uploadDir = dirname(__DIR__, 4) . '/assets/uploads/brands/';
            $uploadUrl = '/assets/uploads/brands/';
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
                $updateData['image'] = '/assets/uploads/brands/' . $fileName;
            } else {
                $errors[] = 'Không thể tải lên hình ảnh. Lỗi: ' . $_FILES['image']['error'] . ' - File not found after upload';
            }
        } elseif (isset($_POST['remove_image'])) {
            // Handle remove image request
            $updateData['image'] = '';
        } elseif (!empty($_POST['image_url'])) {
            // Handle image URL input
            $updateData['image'] = trim($_POST['image_url']);
        } else {
            // Keep existing image - don't include 'image' in update data
            unset($updateData['image']);
        }

        $updated = $service->updateBrand($brand_id, $updateData);

        if ($updated) {
            // Use PRG pattern - redirect after successful POST
            if (!headers_sent($filename, $linenum)) {
                header('Location: ?page=admin&module=brands&action=view&id=' . $brand_id . '&updated=1');
                exit;
            } else {
                // Fallback: if headers sent, use JavaScript redirect
                ?>
                <script>
                window.location.href = "?page=admin&module=brands&action=view&id=<?= $brand_id ?>&updated=1";
                </script>
                <div style="padding:20px;text-align:center;">
                    <p>Đang chuyển hướng...</p>
                    <a href="?page=admin&module=brands&action=view&id=<?= $brand_id ?>&updated=1">Nhấn vào đây nếu không tự chuyển</a>
                </div>
                <?php
                exit;
            }
        } else {
            $errors[] = 'Không thể cập nhật thương hiệu';
        }
    }
} else {
    // Pre-fill form with existing data
    $_POST = [
        'name' => $brand['name'],
        'slug' => $brand['slug'],
        'description' => $brand['description'],
        'website' => $brand['website'] ?? '',
        'status' => $brand['status'],
        'sort_order' => $brand['sort_order'] ?? 0,
        'image' => $brand['image'] ?? ''
    ];
}
?>

<div class="brands-page brands-edit-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-edit"></i>
                Chỉnh Sửa Thương Hiệu
            </h1>
            <p class="page-description">Chỉnh sửa thông tin thương hiệu: <?= htmlspecialchars($brand['name']) ?></p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=brands" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Quay lại danh sách
            </a>
            <a href="?page=admin&module=brands&action=view&id=<?= $brand_id ?>" class="btn btn-info">
                <i class="fas fa-eye"></i>
                Xem chi tiết
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

    <!-- Edit Brand Form -->
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
                            <div class="form-group col-6">
                                <label for="sort_order">Thứ tự sắp xếp</label>
                                <input type="number" id="sort_order" name="sort_order"
                                       value="<?= htmlspecialchars($_POST['sort_order'] ?? '0') ?>"
                                       placeholder="0" min="0">
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

                            <?php if (!empty($_POST['image'])): ?>
                                <div class="current-image">
                                    <img src="<?= htmlspecialchars($_POST['image']) ?>" alt="Current Image">
                                    <label class="remove-image">
                                        <input type="checkbox" name="remove_image" value="1">
                                        <i class="fas fa-trash"></i> Xóa hình ảnh hiện tại
                                    </label>
                                </div>
                            <?php endif; ?>

                            <div class="image-upload-box" id="imagePreview" onclick="document.getElementById('image').click()">
                                <div class="image-placeholder">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span>Click để tải ảnh lên</span>
                                    <small>Hoặc kéo thả ảnh vào đây</small>
                                </div>
                            </div>
                            <input type="file" id="image" name="image" accept="image/*" style="display:none;" onchange="previewImageAdd(this)">

                            <div class="image-input-group">
                                <label>Hoặc nhập URL ảnh mới</label>
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
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group col-6">
                                <label>Hiển thị ở bộ lọc</label>
                                <div class="radio-group">
                                    <label class="radio-label">
                                        <input type="radio" name="show_in_filter" value="1" <?= (($_POST['show_in_filter'] ?? ($brand['show_in_filter'] ?? '1')) == '1') ? 'checked' : '' ?>>
                                        <span>Có</span>
                                    </label>
                                    <label class="radio-label">
                                        <input type="radio" name="show_in_filter" value="0" <?= (($_POST['show_in_filter'] ?? ($brand['show_in_filter'] ?? '')) == '0') ? 'checked' : '' ?>>
                                        <span>Không</span>
                                    </label>
                                </div>
                                <small class="input-hint">Hiển thị trong dropdown bộ lọc ở header và sidebar</small>
                            </div>
                            <div class="form-group col-6">
                                <label>Thương hiệu nổi bật</label>
                                <div class="radio-group">
                                    <label class="radio-label">
                                        <input type="radio" name="is_featured" value="1" <?= (($_POST['is_featured'] ?? ($brand['is_featured'] ?? '')) == '1') ? 'checked' : '' ?>>
                                        <span>Có</span>
                                    </label>
                                    <label class="radio-label">
                                        <input type="radio" name="is_featured" value="0" <?= (($_POST['is_featured'] ?? ($brand['is_featured'] ?? '0')) == '0') ? 'checked' : '' ?>>
                                        <span>Không</span>
                                    </label>
                                </div>
                                <small class="input-hint">Đánh dấu là thương hiệu nổi bật để hiển thị ưu tiên</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Cập Nhật Thương Hiệu
                </button>
                <button type="button" class="btn btn-secondary" onclick="window.location.reload()">
                    <i class="fas fa-undo"></i>
                    Hoàn tác
                </button>
                <a href="?page=admin&module=brands&action=view&id=<?= $brand_id ?>" class="btn btn-outline">
                    <i class="fas fa-times"></i>
                    Hủy
                </a>
            </div>
        </form>
    </div>
</div>

