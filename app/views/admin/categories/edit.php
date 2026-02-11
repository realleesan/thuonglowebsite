<?php
// Load Categories Model
require_once __DIR__ . '/../../../models/CategoriesModel.php';

$categoriesModel = new CategoriesModel();

// Get category ID from URL
$category_id = (int)($_GET['id'] ?? 0);

// Find category
$category = $categoriesModel->find($category_id);

// Redirect if category not found
if (!$category) {
    header('Location: ?page=admin&module=categories&error=not_found');
    exit;
}

// Get category ID from URL
$category_id = (int)($_GET['id'] ?? 0);

// Find category
$category = null;
foreach ($categories as $cat) {
    if ($cat['id'] == $category_id) {
        $category = $cat;
        break;
    }
}

// Redirect if category not found
if (!$category) {
    header('Location: ?page=admin&module=categories&error=not_found');
    exit;
}

// Handle form submission (demo)
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
    
    // If no errors, simulate save (demo)
    if (empty($errors)) {
        $success = true;
        // In real app: update database
        // header('Location: ?page=admin&module=categories&success=updated');
        // exit;
    }
} else {
    // Pre-fill form with existing data
    $_POST = [
        'name' => $category['name'],
        'slug' => $category['slug'],
        'description' => $category['description'],
        'status' => $category['status'],
        'meta_title' => $category['meta_title'] ?? '',
        'meta_description' => $category['meta_description'] ?? '',
        'keywords' => $category['keywords'] ?? '',
        'sort_order' => $category['sort_order'] ?? 0,
        'show_in_menu' => $category['show_in_menu'] ?? 1,
        'featured' => $category['featured'] ?? 0
    ];
}
?>

<div class="categories-page categories-edit-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-edit"></i>
                Chỉnh Sửa Danh Mục
            </h1>
            <p class="page-description">Chỉnh sửa thông tin danh mục: <?= htmlspecialchars($category['name']) ?></p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=categories&action=view&id=<?= $category['id'] ?>" class="btn btn-info">
                <i class="fas fa-eye"></i>
                Xem chi tiết
            </a>
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
            Cập nhật danh mục thành công! (Demo - dữ liệu không được lưu thật)
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

    <!-- Edit Category Form -->
    <div class="form-container">
        <form method="POST" class="admin-form">
            <div class="form-grid">
                <!-- Left Column -->
                <div class="form-column">
                    <div class="form-section">
                        <h3 class="section-title">Thông Tin Cơ Bản</h3>
                        
                        <div class="form-group">
                            <label for="name" class="required">Tên danh mục</label>
                            <input type="text" id="name" name="name" 
                                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" 
                                   placeholder="Nhập tên danh mục" required>
                            <small>Tên hiển thị của danh mục</small>
                        </div>

                        <div class="form-group">
                            <label for="slug" class="required">Slug</label>
                            <input type="text" id="slug" name="slug" 
                                   value="<?= htmlspecialchars($_POST['slug'] ?? '') ?>" 
                                   placeholder="ten-danh-muc" required>
                            <small>URL thân thiện (chỉ chữ thường, số và dấu gạch ngang)</small>
                            <button type="button" id="generateSlug" class="btn btn-sm btn-outline">
                                <i class="fas fa-magic"></i>
                                Tự động tạo từ tên
                            </button>
                        </div>

                        <div class="form-group">
                            <label for="status">Trạng thái</label>
                            <select id="status" name="status">
                                <option value="active" <?= (($_POST['status'] ?? 'active') == 'active') ? 'selected' : '' ?>>
                                    Hoạt động
                                </option>
                                <option value="inactive" <?= (($_POST['status'] ?? '') == 'inactive') ? 'selected' : '' ?>>
                                    Không hoạt động
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="section-title">Mô Tả Danh Mục</h3>
                        
                        <div class="form-group">
                            <label for="description" class="required">Mô tả chi tiết</label>
                            <textarea id="description" name="description" rows="8" 
                                      placeholder="Nhập mô tả chi tiết về danh mục..." required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                            <small>Mô tả sẽ hiển thị trên trang danh mục</small>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="form-column">
                    <div class="form-section">
                        <h3 class="section-title">Hình Ảnh Danh Mục</h3>
                        
                        <div class="form-group">
                            <label for="image">Hình ảnh đại diện</label>
                            <div class="image-upload-container">
                                <div class="image-preview" id="imagePreview">
                                    <?php if (!empty($category['image'])): ?>
                                        <img src="<?= htmlspecialchars($category['image']) ?>" alt="Current image">
                                    <?php else: ?>
                                        <i class="fas fa-image"></i>
                                        <p>Chọn hình ảnh</p>
                                    <?php endif; ?>
                                </div>
                                <input type="file" id="image" name="image" accept="image/*" class="image-input">
                                <div class="image-upload-info">
                                    <small>Định dạng: JPG, PNG, GIF. Kích thước tối đa: 2MB</small>
                                    <?php if (!empty($category['image'])): ?>
                                        <small>Để trống nếu không muốn thay đổi hình ảnh</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="section-title">SEO & Metadata</h3>
                        
                        <div class="form-group">
                            <label for="meta_title">Tiêu đề SEO</label>
                            <input type="text" id="meta_title" name="meta_title" 
                                   value="<?= htmlspecialchars($_POST['meta_title'] ?? '') ?>" 
                                   placeholder="Tiêu đề tối ưu cho SEO">
                            <small>Tối đa 60 ký tự</small>
                        </div>

                        <div class="form-group">
                            <label for="meta_description">Mô tả SEO</label>
                            <textarea id="meta_description" name="meta_description" rows="3" 
                                      placeholder="Mô tả ngắn gọn cho SEO"><?= htmlspecialchars($_POST['meta_description'] ?? '') ?></textarea>
                            <small>Tối đa 160 ký tự</small>
                        </div>

                        <div class="form-group">
                            <label for="keywords">Từ khóa SEO</label>
                            <input type="text" id="keywords" name="keywords" 
                                   value="<?= htmlspecialchars($_POST['keywords'] ?? '') ?>" 
                                   placeholder="từ khóa 1, từ khóa 2, từ khóa 3">
                            <small>Phân cách bằng dấu phẩy</small>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="section-title">Cài Đặt Hiển Thị</h3>
                        
                        <div class="form-group">
                            <label for="sort_order">Thứ tự sắp xếp</label>
                            <input type="number" id="sort_order" name="sort_order" 
                                   value="<?= htmlspecialchars($_POST['sort_order'] ?? '0') ?>" 
                                   placeholder="0" min="0">
                            <small>Số càng nhỏ càng hiển thị trước</small>
                        </div>

                        <div class="form-group">
                            <div class="checkbox-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="show_in_menu" value="1" 
                                           <?= (($_POST['show_in_menu'] ?? '1') == '1') ? 'checked' : '' ?>>
                                    <span class="checkmark"></span>
                                    Hiển thị trong menu chính
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="checkbox-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="featured" value="1" 
                                           <?= (($_POST['featured'] ?? '') == '1') ? 'checked' : '' ?>>
                                    <span class="checkmark"></span>
                                    Danh mục nổi bật
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Category Info -->
                    <div class="form-section">
                        <h3 class="section-title">Thông Tin Danh Mục</h3>
                        
                        <div class="info-group">
                            <div class="info-item">
                                <label>ID danh mục:</label>
                                <span class="info-value"><?= $category['id'] ?></span>
                            </div>
                            
                            <div class="info-item">
                                <label>Ngày tạo:</label>
                                <span class="info-value"><?= date('d/m/Y H:i', strtotime($category['created_at'])) ?></span>
                            </div>
                            
                            <div class="info-item">
                                <label>Lần cập nhật cuối:</label>
                                <span class="info-value"><?= date('d/m/Y H:i', strtotime($category['updated_at'] ?? $category['created_at'])) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Cập Nhật Danh Mục
                </button>
                <button type="button" class="btn btn-secondary" onclick="resetForm()">
                    <i class="fas fa-undo"></i>
                    Đặt lại
                </button>
                <a href="?page=admin&module=categories&action=view&id=<?= $category['id'] ?>" class="btn btn-info">
                    <i class="fas fa-eye"></i>
                    Xem chi tiết
                </a>
                <a href="?page=admin&module=categories" class="btn btn-outline">
                    <i class="fas fa-times"></i>
                    Hủy
                </a>
            </div>
        </form>
    </div>
</div>

