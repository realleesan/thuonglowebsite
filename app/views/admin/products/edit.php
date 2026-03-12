<?php
/**
 * Admin Products Edit - Tái cấu trúc cho sản phẩm số (Data Nguồn Hàng)
 * Designed for digital products / data products
 * Using Tab Layout
 */

// Khởi tạo View & ServiceManager
require_once __DIR__ . '/../../../../core/view_init.php';

// Get product ID from URL
$product_id = (int)($_GET['id'] ?? 0);

if (!$product_id) {
    header('Location: ?page=admin&module=products&error=invalid_id');
    exit;
}

try {
    require_once __DIR__ . '/../../../models/ProductsModel.php';
    require_once __DIR__ . '/../../../models/CategoriesModel.php';
    
    $productsModel = new ProductsModel();
    $categoriesModel = new CategoriesModel();
    
    // Get product
    $products = $productsModel->query("SELECT * FROM products WHERE id = ?", [$product_id]);
    $product = !empty($products) ? $products[0] : null;
    
    // Get categories
    $categories = $categoriesModel->getActive();
    
    // Redirect if product not found
    if (!$product) {
        header('Location: ?page=admin&module=products&error=not_found');
        exit;
    }
    
} catch (Exception $e) {
    error_log('Admin Products Edit Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' line ' . $e->getLine());
    echo '<div style="padding:20px;background:#ffebee;color:#c62828;">';
    echo '<h2>Lỗi tải sản phẩm</h2>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '</div>';
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
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'active';
    
    if (empty($name)) {
        $errors[] = 'Tên data không được để trống';
    }
    
    if ($category_id <= 0) {
        $errors[] = 'Vui lòng chọn danh mục';
    }
    
    if ($price <= 0) {
        $errors[] = 'Giá data phải lớn hơn 0';
    }
    
    if (empty($description)) {
        $errors[] = 'Mô tả data không được để trống';
    }
    
    // If no errors, update database
    if (empty($errors)) {
        try {
            $updateData = [
                'name' => $name,
                'category_id' => $category_id,
                'price' => $price,
                'stock' => !empty($_POST['record_count']) ? (int)$_POST['record_count'] : 100,
                'description' => $description,
                'status' => $status,
                'type' => $_POST['type'] ?? 'data_nguon_hang',
                'sale_price' => !empty($_POST['sale_price']) ? $_POST['sale_price'] : null,
                'expiry_days' => !empty($_POST['expiry_days']) ? (int)$_POST['expiry_days'] : 30,
                'sku' => $_POST['sku'] ?? '',
                'short_description' => $_POST['short_description'] ?? '',
                'meta_title' => $_POST['meta_title'] ?? '',
                'meta_description' => $_POST['meta_description'] ?? '',
                // Data fields
                'record_count' => !empty($_POST['record_count']) ? (int)$_POST['record_count'] : 100,
                'data_size' => $_POST['data_size'] ?? '',
                'data_format' => $_POST['data_format'] ?? '',
                'data_source' => $_POST['data_source'] ?? '',
                'reliability' => $_POST['reliability'] ?? '',
                'quota' => !empty($_POST['quota']) ? (int)$_POST['quota'] : 100,
                'quota_per_usage' => !empty($_POST['quota_per_usage']) ? (int)$_POST['quota_per_usage'] : 10,
                // Supplier fields
                'supplier_name' => $_POST['supplier_name'] ?? '',
                'supplier_title' => $_POST['supplier_title'] ?? '',
                'supplier_bio' => $_POST['supplier_bio'] ?? '',
                'supplier_avatar' => $_POST['supplier_avatar'] ?? '',
                'supplier_social' => $_POST['supplier_social'] ?? '',
                // JSON fields
                'benefits' => $_POST['benefits'] ?? '',
                'data_structure' => $_POST['data_structure'] ?? '',
                // Digital product
                'featured' => isset($_POST['featured']) ? 1 : 0,
                'downloadable' => isset($_POST['downloadable']) ? 1 : 0,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $updated = $productsModel->update($product_id, $updateData);
        } catch (Exception $e) {
            $errors[] = 'Lỗi cập nhật: ' . $e->getMessage();
            $updated = false;
        }
        
        if ($updated) {
            header('Location: ?page=admin&module=products&action=view&id=' . $product_id . '&success=updated');
            exit;
        } else {
            $errors[] = 'Không thể cập nhật data';
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
                Chỉnh Sửa Data
            </h1>
            <p class="page-description">Cập nhật thông tin data: <?= htmlspecialchars($product['name']) ?></p>
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
        Cập nhật data thành công!
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

    <!-- Tab Navigation -->
    <div class="form-tabs">
        <button type="button" class="tab-button active" data-tab="tab-basic">
            <i class="fas fa-info-circle"></i>
            Thông Tin Cơ Bản
        </button>
        <button type="button" class="tab-button" data-tab="tab-data">
            <i class="fas fa-database"></i>
            Thông Tin Data
        </button>
        <button type="button" class="tab-button" data-tab="tab-supplier">
            <i class="fas fa-building"></i>
            Nhà Cung Cấp
        </button>
        <button type="button" class="tab-button" data-tab="tab-benefits">
            <i class="fas fa-gift"></i>
            Lợi Ích & Cấu Trúc
        </button>
        <button type="button" class="tab-button" data-tab="tab-image">
            <i class="fas fa-image"></i>
            Hình Ảnh
        </button>
        <button type="button" class="tab-button" data-tab="tab-seo">
            <i class="fas fa-search"></i>
            SEO
        </button>
    </div>

    <!-- Edit Product Form -->
    <div class="form-container">
        <form method="POST" enctype="multipart/form-data" class="admin-form" id="productForm">
            
            <!-- Tab 1: Thông Tin Cơ Bản -->
            <div class="tab-content active" id="tab-basic">
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-info-circle"></i>
                        Thông Tin Cơ Bản
                    </h3>
                    
                    <div class="form-row">
                        <div class="form-group form-group-8">
                            <label for="name" class="required">Tên Data</label>
                            <input type="text" id="name" name="name" 
                                   value="<?= htmlspecialchars($form_data['name'] ?? '') ?>" 
                                   placeholder="Ví dụ: Gói 100 Data Ngành Quần Áo" required>
                        </div>
                        
                        <div class="form-group form-group-4">
                            <label for="type">Loại Data</label>
                            <select id="type" name="type">
                                <option value="data_nguon_hang" <?= (($form_data['type'] ?? 'data_nguon_hang') == 'data_nguon_hang') ? 'selected' : '' ?>>Data Nguồn Hàng</option>
                                <option value="khoa_hoc" <?= (($form_data['type'] ?? '') == 'khoa_hoc') ? 'selected' : '' ?>>Khóa Học</option>
                                <option value="tool" <?= (($form_data['type'] ?? '') == 'tool') ? 'selected' : '' ?>>Công Cụ</option>
                                <option value="dich_vu" <?= (($form_data['type'] ?? '') == 'dich_vu') ? 'selected' : '' ?>>Dịch Vụ</option>
                            </select>
                        </div>
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
                            <label for="sale_price">Giá khuyến mãi (VNĐ)</label>
                            <input type="number" id="sale_price" name="sale_price" 
                                   value="<?= htmlspecialchars($form_data['sale_price'] ?? '') ?>" 
                                   placeholder="0" min="0" step="1000">
                        </div>

                        <div class="form-group">
                            <label for="status">Trạng thái</label>
                            <select id="status" name="status">
                                <option value="active" <?= (($form_data['status'] ?? 'active') == 'active') ? 'selected' : '' ?>>Hoạt động</option>
                                <option value="inactive" <?= (($form_data['status'] ?? '') == 'inactive') ? 'selected' : '' ?>>Không hoạt động</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="sku">Mã SKU</label>
                            <input type="text" id="sku" name="sku" 
                                   value="<?= htmlspecialchars($form_data['sku'] ?? '') ?>" 
                                   placeholder="DATA-001">
                        </div>

                        <div class="form-group">
                            <label for="expiry_days">Số ngày hết hạn</label>
                            <input type="number" id="expiry_days" name="expiry_days" 
                                   value="<?= htmlspecialchars($form_data['expiry_days'] ?? '30') ?>" 
                                   placeholder="30" min="1">
                        </div>

                        <div class="form-group">
                            <label for="featured">Nổi bật</label>
                            <div class="checkbox-wrapper">
                                <input type="checkbox" id="featured" name="featured" value="1" 
                                       <?= (($form_data['featured'] ?? '') == '1') ? 'checked' : '' ?>>
                                <label for="featured">Hiển thị trang chủ</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="short_description">Mô tả ngắn</label>
                        <textarea id="short_description" name="short_description" rows="2" 
                                  placeholder="Mô tả ngắn gọn về data"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="description" class="required">Mô tả chi tiết</label>
                        <textarea id="description" name="description" rows="8" 
                                  placeholder="Nhập mô tả chi tiết về data nguồn hàng..." required><?= htmlspecialchars($form_data['description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Thông Tin Data -->
            <div class="tab-content" id="tab-data">
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-database"></i>
                        Thông Tin Data
                    </h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="record_count">Số lượng Record</label>
                            <input type="number" id="record_count" name="record_count" 
                                   value="<?= htmlspecialchars($form_data['record_count'] ?? '100') ?>" 
                                   placeholder="100" min="1">
                            <small>Số lượng thông tin trong data</small>
                        </div>

                        <div class="form-group">
                            <label for="data_size">Dung lượng Data</label>
                            <input type="text" id="data_size" name="data_size" 
                                   value="<?= htmlspecialchars($form_data['data_size'] ?? '') ?>" 
                                   placeholder="15 KB">
                            <small>Ví dụ: 15 KB, 2 MB</small>
                        </div>

                        <div class="form-group">
                            <label for="data_format">Định dạng File</label>
                            <input type="text" id="data_format" name="data_format" 
                                   value="<?= htmlspecialchars($form_data['data_format'] ?? '') ?>" 
                                   placeholder="Excel, CSV, JSON">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="data_source">Nguồn Gốc</label>
                            <input type="text" id="data_source" name="data_source" 
                                   value="<?= htmlspecialchars($form_data['data_source'] ?? '') ?>" 
                                   placeholder="Việt Nam, Trung Quốc...">
                        </div>

                        <div class="form-group">
                            <label for="reliability">Độ Tin Cậy</label>
                            <input type="text" id="reliability" name="reliability" 
                                   value="<?= htmlspecialchars($form_data['reliability'] ?? '') ?>" 
                                   placeholder="90%">
                            <small>Tỷ lệ chính xác của data</small>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="quota">Số Lần Tải (Quota)</label>
                            <input type="number" id="quota" name="quota" 
                                   value="<?= htmlspecialchars($form_data['quota'] ?? '100') ?>" 
                                   placeholder="100" min="1">
                            <small>Số lần khách được tải data</small>
                        </div>

                        <div class="form-group">
                            <label for="quota_per_usage">Số Record Mỗi Lần Tải</label>
                            <input type="number" id="quota_per_usage" name="quota_per_usage" 
                                   value="<?= htmlspecialchars($form_data['quota_per_usage'] ?? '10') ?>" 
                                   placeholder="10" min="1">
                            <small>Số record được tải mỗi lần</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 3: Nhà Cung Cấp -->
            <div class="tab-content" id="tab-supplier">
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-building"></i>
                        Thông Tin Nhà Cung Cấp
                    </h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="supplier_name">Tên Nhà Cung Cấp</label>
                            <input type="text" id="supplier_name" name="supplier_name" 
                                   value="<?= htmlspecialchars($form_data['supplier_name'] ?? '') ?>" 
                                   placeholder="Công ty TNHH Data Logistics VN">
                        </div>

                        <div class="form-group">
                            <label for="supplier_title">Chức Danh</label>
                            <input type="text" id="supplier_title" name="supplier_title" 
                                   value="<?= htmlspecialchars($form_data['supplier_title'] ?? '') ?>" 
                                   placeholder="Đối tác chiến lược">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="supplier_bio">Giới Thiệu</label>
                        <textarea id="supplier_bio" name="supplier_bio" rows="3" 
                                  placeholder="Giới thiệu về nhà cung cấp"><?= htmlspecialchars($form_data['supplier_bio'] ?? '') ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="supplier_avatar">Avatar URL</label>
                            <input type="url" id="supplier_avatar" name="supplier_avatar" 
                                   value="<?= htmlspecialchars($form_data['supplier_avatar'] ?? '') ?>" 
                                   placeholder="https://...">
                        </div>

                        <div class="form-group">
                            <label for="supplier_social">Mạng Xã Hội (JSON)</label>
                            <textarea id="supplier_social" name="supplier_social" rows="2" 
                                      placeholder='{"website":"https://...","hotline":"19001234"}'><?= htmlspecialchars($form_data['supplier_social'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 4: Lợi Ích & Cấu Trúc -->
            <div class="tab-content" id="tab-benefits">
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-gift"></i>
                        Lợi Ích & Cấu Trúc Data
                    </h3>
                    
                    <div class="form-group">
                        <label for="benefits">Lợi Ích (JSON Array)</label>
                        <textarea id="benefits" name="benefits" rows="4" 
                                  placeholder='["Lợi ích 1","Lợi ích 2","Lợi ích 3"]'><?= htmlspecialchars($form_data['benefits'] ?? '') ?></textarea>
                        <small>Danh sách các lợi ích khi mua data</small>
                    </div>

                    <div class="form-group">
                        <label for="data_structure">Cấu Trúc Data (JSON)</label>
                        <textarea id="data_structure" name="data_structure" rows="8" 
                                  placeholder='[{"title":"Thông tin cơ bản","items":[{"title":"Tên nhà phân phối"},{"title":"Địa chỉ"}]}]'><?= htmlspecialchars($form_data['data_structure'] ?? '') ?></textarea>
                        <small>Cấu trúc chi tiết của data</small>
                    </div>
                </div>
            </div>

            <!-- Tab 5: Hình Ảnh -->
            <div class="tab-content" id="tab-image">
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-image"></i>
                        Hình Ảnh Data
                    </h3>
                    
                    <div class="form-group">
                        <label for="image">Hình Ảnh Chính</label>
                        <div class="image-upload-container">
                            <div class="image-preview" id="imagePreview">
                                <?php if (!empty($product['image'])): ?>
                                    <img src="<?= $product['image'] ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                                <?php else: ?>
                                    <i class="fas fa-image"></i>
                                    <p>Chọn hình ảnh</p>
                                <?php endif; ?>
                            </div>
                            <input type="file" id="image" name="image" accept="image/*" class="image-input">
                            <input type="hidden" id="image_url" name="image" value="<?= htmlspecialchars($product['image'] ?? '') ?>">
                            <div class="image-upload-info">
                                <small>Định dạng: JPG, PNG, GIF</small>
                                <div class="url-input-wrapper">
                                    <label>Hoặc nhập URL:</label>
                                    <input type="url" id="imageUrlInput" placeholder="https://..." 
                                           value="<?= htmlspecialchars($product['image'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 6: SEO -->
            <div class="tab-content" id="tab-seo">
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-search"></i>
                        SEO & Metadata
                    </h3>
                    
                    <div class="form-group">
                        <label for="meta_title">Tiêu Đề SEO</label>
                        <input type="text" id="meta_title" name="meta_title" 
                               value="<?= htmlspecialchars($form_data['meta_title'] ?? '') ?>" 
                               placeholder="Tiêu đề tối ưu cho SEO">
                    </div>

                    <div class="form-group">
                        <label for="meta_description">Mô Tả SEO</label>
                        <textarea id="meta_description" name="meta_description" rows="3" 
                                  placeholder="Mô tả ngắn gọn cho SEO"><?= htmlspecialchars($form_data['meta_description'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="tags">Tags</label>
                        <input type="text" id="tags" name="tags" 
                               value="<?= htmlspecialchars($form_data['tags'] ?? '') ?>" 
                               placeholder="data, nguon-hang, logistics">
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions sticky-form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Lưu Thay Đổi
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
