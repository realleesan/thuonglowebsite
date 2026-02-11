<?php
// Load ViewDataService and ErrorHandler
require_once __DIR__ . '/../../../services/ViewDataService.php';
require_once __DIR__ . '/../../../services/ErrorHandler.php';

try {
    $viewDataService = new ViewDataService();
    $errorHandler = new ErrorHandler();
    
    // Get categories for dropdown using ViewDataService
    $categoriesData = $viewDataService->getActiveCategoriesForDropdown();
    $categories = $categoriesData['categories'] ?? [];
    
} catch (Exception $e) {
    $errorHandler->logError('Admin Products Add View Error', $e);
    $categories = [];
}

// Handle form submission (demo)
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation
    $name = trim($_POST['name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'active';
    
    if (empty($name)) {
        $errors[] = 'Tên sản phẩm không được để trống';
    }
    
    if ($category_id <= 0) {
        $errors[] = 'Vui lòng chọn danh mục';
    }
    
    if ($price <= 0) {
        $errors[] = 'Giá sản phẩm phải lớn hơn 0';
    }
    
    if ($stock < 0) {
        $errors[] = 'Số lượng tồn kho không được âm';
    }
    
    if (empty($description)) {
        $errors[] = 'Mô tả sản phẩm không được để trống';
    }
    
    // If no errors, simulate save (demo)
    if (empty($errors)) {
        $success = true;
        // In real app: save to database
        // header('Location: ?page=admin&module=products&success=added');
        // exit;
    }
}
?>

<div class="products-page products-add-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-plus"></i>
                Thêm Sản Phẩm Mới
            </h1>
            <p class="page-description">Thêm sản phẩm mới vào hệ thống</p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=products" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Quay lại danh sách
            </a>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            Thêm sản phẩm thành công! (Demo - dữ liệu không được lưu thật)
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

    <!-- Add Product Form -->
    <div class="form-container">
        <form method="POST" enctype="multipart/form-data" class="admin-form">
            <div class="form-grid">
                <!-- Left Column -->
                <div class="form-column">
                    <div class="form-section">
                        <h3 class="section-title">Thông Tin Cơ Bản</h3>
                        
                        <div class="form-group">
                            <label for="name" class="required">Tên sản phẩm</label>
                            <input type="text" id="name" name="name" 
                                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" 
                                   placeholder="Nhập tên sản phẩm" required>
                        </div>

                        <div class="form-group">
                            <label for="category_id" class="required">Danh mục</label>
                            <select id="category_id" name="category_id" required>
                                <option value="">Chọn danh mục</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>" 
                                            <?= (($_POST['category_id'] ?? '') == $category['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="price" class="required">Giá (VNĐ)</label>
                                <input type="number" id="price" name="price" 
                                       value="<?= htmlspecialchars($_POST['price'] ?? '') ?>" 
                                       placeholder="0" min="0" step="1000" required>
                            </div>

                            <div class="form-group">
                                <label for="stock">Số lượng tồn kho</label>
                                <input type="number" id="stock" name="stock" 
                                       value="<?= htmlspecialchars($_POST['stock'] ?? '0') ?>" 
                                       placeholder="0" min="0">
                            </div>
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
                        <h3 class="section-title">Mô Tả Sản Phẩm</h3>
                        
                        <div class="form-group">
                            <label for="description" class="required">Mô tả chi tiết</label>
                            <textarea id="description" name="description" rows="8" 
                                      placeholder="Nhập mô tả chi tiết về sản phẩm..." required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="form-column">
                    <div class="form-section">
                        <h3 class="section-title">Hình Ảnh Sản Phẩm</h3>
                        
                        <div class="form-group">
                            <label for="image">Hình ảnh chính</label>
                            <div class="image-upload-container">
                                <div class="image-preview" id="imagePreview">
                                    <i class="fas fa-image"></i>
                                    <p>Chọn hình ảnh</p>
                                </div>
                                <input type="file" id="image" name="image" accept="image/*" class="image-input">
                                <div class="image-upload-info">
                                    <small>Định dạng: JPG, PNG, GIF. Kích thước tối đa: 2MB</small>
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
                            <label for="tags">Tags</label>
                            <input type="text" id="tags" name="tags" 
                                   value="<?= htmlspecialchars($_POST['tags'] ?? '') ?>" 
                                   placeholder="tag1, tag2, tag3">
                            <small>Phân cách bằng dấu phẩy</small>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="section-title">Thông Tin Bổ Sung</h3>
                        
                        <div class="form-group">
                            <label for="sku">Mã SKU</label>
                            <input type="text" id="sku" name="sku" 
                                   value="<?= htmlspecialchars($_POST['sku'] ?? '') ?>" 
                                   placeholder="Mã sản phẩm duy nhất">
                        </div>

                        <div class="form-group">
                            <label for="weight">Trọng lượng (gram)</label>
                            <input type="number" id="weight" name="weight" 
                                   value="<?= htmlspecialchars($_POST['weight'] ?? '') ?>" 
                                   placeholder="0" min="0">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="length">Dài (cm)</label>
                                <input type="number" id="length" name="length" 
                                       value="<?= htmlspecialchars($_POST['length'] ?? '') ?>" 
                                       placeholder="0" min="0" step="0.1">
                            </div>

                            <div class="form-group">
                                <label for="width">Rộng (cm)</label>
                                <input type="number" id="width" name="width" 
                                       value="<?= htmlspecialchars($_POST['width'] ?? '') ?>" 
                                       placeholder="0" min="0" step="0.1">
                            </div>

                            <div class="form-group">
                                <label for="height">Cao (cm)</label>
                                <input type="number" id="height" name="height" 
                                       value="<?= htmlspecialchars($_POST['height'] ?? '') ?>" 
                                       placeholder="0" min="0" step="0.1">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Lưu Sản Phẩm
                </button>
                <button type="button" class="btn btn-secondary" onclick="resetForm()">
                    <i class="fas fa-undo"></i>
                    Đặt lại
                </button>
                <a href="?page=admin&module=products" class="btn btn-outline">
                    <i class="fas fa-times"></i>
                    Hủy
                </a>
            </div>
        </form>
    </div>
</div>