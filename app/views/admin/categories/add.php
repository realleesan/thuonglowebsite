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
                                <div class="image-preview" id="imagePreview" onclick="document.getElementById('image').click()" style="cursor:pointer;">
                                    <i class="fas fa-image"></i>
                                    <p>Chọn hình ảnh</p>
                                </div>
                                <input type="file" id="image" name="image" accept="image/*" class="image-input" style="display:none;" onchange="previewImageAdd(this)">
                                <div class="image-upload-info">
                                    <small>Định dạng: JPG, PNG, GIF. Kích thước tối đa: 2MB</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group" style="margin-top:16px;">
                            <label for="image_url">Hoặc nhập URL ảnh</label>
                            <input type="url" id="image_url" name="image_url" 
                                   value="<?= htmlspecialchars($_POST['image_url'] ?? '') ?>" 
                                   placeholder="https://example.com/image.jpg">
                            <small>Nếu upload ảnh thì URL này sẽ bị bỏ qua</small>
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
// Preview image when selected for add form
function previewImageAdd(input) {
    const preview = document.getElementById('imagePreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview" style="max-width:100%;max-height:200px;border-radius:8px;">';
            preview.style.background = '#f0f0f0';
            preview.style.border = '2px solid #28a745';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

