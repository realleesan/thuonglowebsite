<?php
/**
 * Admin Products Edit - Dynamic Version
 * Sử dụng AdminService thông qua ServiceManager
 */

// Khởi tạo View & ServiceManager
require_once __DIR__ . '/../../../../core/view_init.php';

// Chọn service admin (được inject từ index.php)
$service = isset($currentService) ? $currentService : ($adminService ?? null);

try {
    // Get product ID from URL
    $product_id = (int)($_GET['id'] ?? 0);
    
    if (!$product_id) {
        header('Location: ?page=admin&module=products&error=invalid_id');
        exit;
    }
    
    // Get product data using AdminService
    $productData = $service->getProductDetailsData($product_id);
    $product = $productData['product'];
    $categories = $productData['categories'];
    
    // Redirect if product not found
    if (!$product) {
        header('Location: ?page=admin&module=products&error=not_found');
        exit;
    }
    
} catch (Exception $e) {
    $errorHandler->logError('Admin Products Edit View Error', $e);
    header('Location: ?page=admin&module=products&error=system_error');
    exit;
}

// Handle form submission
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
    
    // If no errors, update database
    if (empty($errors)) {
        $updated = $service->updateProduct($product_id, $_POST);
        if ($updated) {
            header('Location: ?page=admin&module=products&action=view&id=' . $product_id . '&success=updated');
            exit;
        } else {
            $errors[] = 'Không thể cập nhật sản phẩm';
        }
    }
}

// Use POST data if available, otherwise use product data
$form_data = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $product;
?>

<div class="products-page products-edit-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-edit"></i>
                Chỉnh Sửa Sản Phẩm
            </h1>
            <p class="page-description">Cập nhật thông tin sản phẩm: <?= htmlspecialchars($product['name']) ?></p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=products&action=view&id=<?= $product['id'] ?>" class="btn btn-info">
                <i class="fas fa-eye"></i>
                Xem chi tiết
            </a>
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
            Cập nhật sản phẩm thành công!
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

    <!-- Edit Product Form -->
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
                                   value="<?= htmlspecialchars($form_data['name'] ?? '') ?>" 
                                   placeholder="Nhập tên sản phẩm" required>
                        </div>

                        <div class="form-group">
                            <label for="category_id" class="required">Danh mục</label>
                            <select id="category_id" name="category_id" required>
                                <option value="">Chọn danh mục</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>" 
                                            <?= (($form_data['category_id'] ?? '') == $category['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="price" class="required">Giá (VNĐ)</label>
                                <input type="number" id="price" name="price" 
                                       value="<?= htmlspecialchars($form_data['price'] ?? '') ?>" 
                                       placeholder="0" min="0" step="1000" required>
                            </div>

                            <div class="form-group">
                                <label for="stock">Số lượng tồn kho</label>
                                <input type="number" id="stock" name="stock" 
                                       value="<?= htmlspecialchars($form_data['stock'] ?? '0') ?>" 
                                       placeholder="0" min="0">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="status">Trạng thái</label>
                            <select id="status" name="status">
                                <option value="active" <?= (($form_data['status'] ?? 'active') == 'active') ? 'selected' : '' ?>>
                                    Hoạt động
                                </option>
                                <option value="inactive" <?= (($form_data['status'] ?? '') == 'inactive') ? 'selected' : '' ?>>
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
                                      placeholder="Nhập mô tả chi tiết về sản phẩm..." required><?= htmlspecialchars($form_data['description'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="section-title">Thông Tin Hệ Thống</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>ID sản phẩm</label>
                                <input type="text" value="<?= $product['id'] ?>" readonly class="readonly">
                            </div>

                            <div class="form-group">
                                <label>Ngày tạo</label>
                                <input type="text" value="<?= date('d/m/Y H:i', strtotime($product['created_at'])) ?>" readonly class="readonly">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Lần cập nhật cuối</label>
                            <input type="text" value="<?= date('d/m/Y H:i', strtotime($product['updated_at'] ?? 'now')) ?>" readonly class="readonly">
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
                                    <?php if (!empty($product['image'])): ?>
                                        <img src="<?= $product['image'] ?>" alt="<?= htmlspecialchars($product['name']) ?>" 
                                             onerror="this.parentElement.innerHTML='<i class=\'fas fa-image\'></i><p>Hình ảnh không tồn tại</p>'">
                                    <?php else: ?>
                                        <i class="fas fa-image"></i>
                                        <p>Chọn hình ảnh</p>
                                    <?php endif; ?>
                                </div>
                                <input type="file" id="image" name="image" accept="image/*" class="image-input">
                                <div class="image-upload-info">
                                    <small>Định dạng: JPG, PNG, GIF. Kích thước tối đa: 2MB</small>
                                    <?php if (!empty($product['image'])): ?>
                                        <div class="current-image-info">
                                            <strong>Hình ảnh hiện tại:</strong> <?= basename($product['image']) ?>
                                        </div>
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
                                   value="<?= htmlspecialchars($form_data['meta_title'] ?? $product['name']) ?>" 
                                   placeholder="Tiêu đề tối ưu cho SEO">
                            <small>Tối đa 60 ký tự</small>
                        </div>

                        <div class="form-group">
                            <label for="meta_description">Mô tả SEO</label>
                            <textarea id="meta_description" name="meta_description" rows="3" 
                                      placeholder="Mô tả ngắn gọn cho SEO"><?= htmlspecialchars($form_data['meta_description'] ?? substr($product['description'], 0, 160)) ?></textarea>
                            <small>Tối đa 160 ký tự</small>
                        </div>

                        <div class="form-group">
                            <label for="tags">Tags</label>
                            <input type="text" id="tags" name="tags" 
                                   value="<?= htmlspecialchars($form_data['tags'] ?? '') ?>" 
                                   placeholder="tag1, tag2, tag3">
                            <small>Phân cách bằng dấu phẩy</small>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="section-title">Thông Tin Bổ Sung</h3>
                        
                        <div class="form-group">
                            <label for="sku">Mã SKU</label>
                            <input type="text" id="sku" name="sku" 
                                   value="<?= htmlspecialchars($form_data['sku'] ?? 'SKU-' . $product['id']) ?>" 
                                   placeholder="Mã sản phẩm duy nhất">
                        </div>

                        <div class="form-group">
                            <label for="weight">Trọng lượng (gram)</label>
                            <input type="number" id="weight" name="weight" 
                                   value="<?= htmlspecialchars($form_data['weight'] ?? '') ?>" 
                                   placeholder="0" min="0">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="length">Dài (cm)</label>
                                <input type="number" id="length" name="length" 
                                       value="<?= htmlspecialchars($form_data['length'] ?? '') ?>" 
                                       placeholder="0" min="0" step="0.1">
                            </div>

                            <div class="form-group">
                                <label for="width">Rộng (cm)</label>
                                <input type="number" id="width" name="width" 
                                       value="<?= htmlspecialchars($form_data['width'] ?? '') ?>" 
                                       placeholder="0" min="0" step="0.1">
                            </div>

                            <div class="form-group">
                                <label for="height">Cao (cm)</label>
                                <input type="number" id="height" name="height" 
                                       value="<?= htmlspecialchars($form_data['height'] ?? '') ?>" 
                                       placeholder="0" min="0" step="0.1">
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="section-title">Thống Kê</h3>
                        
                        <div class="stats-grid">
                            <div class="stat-item">
                                <div class="stat-label">Lượt xem</div>
                                <div class="stat-value"><?= number_format($product['view_count'] ?? 0) ?></div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-label">Đã bán</div>
                                <div class="stat-value"><?= number_format($product['sold_count'] ?? 0) ?></div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-label">Đánh giá</div>
                                <div class="stat-value"><?= number_format($product['avg_rating'] ?? 0, 1) ?>/5</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-label">Yêu thích</div>
                                <div class="stat-value"><?= number_format($product['wishlist_count'] ?? 0) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Cập Nhật Sản Phẩm
                </button>
                <button type="button" class="btn btn-warning" onclick="resetForm()">
                    <i class="fas fa-undo"></i>
                    Khôi phục
                </button>
                <a href="?page=admin&module=products&action=view&id=<?= $product['id'] ?>" class="btn btn-info">
                    <i class="fas fa-eye"></i>
                    Xem chi tiết
                </a>
                <a href="?page=admin&module=products" class="btn btn-outline">
                    <i class="fas fa-times"></i>
                    Hủy
                </a>
            </div>
        </form>
    </div>
</div>