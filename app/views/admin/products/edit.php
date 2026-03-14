<?php
/**
 * Admin Products Edit - Tái cấu trúc cho sản phẩm số (Data Nguồn Hàng)
 * Designed for digital products / data products
 * Using 2-layer tab layout (tabs container + tab-pane)
 */

// Start session for PRG pattern
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

// Check for success message from previous redirect (PRG pattern)
$showSuccessMessage = false;
if (isset($_SESSION['product_saved']) && $_SESSION['product_saved'] === $product_id) {
    $showSuccessMessage = true;
    unset($_SESSION['product_saved']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST['data_action'])) {
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
    
    // Handle image upload
    $image_path = $product['image'] ?? ''; // default to existing image
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'assets/images/products/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];
        if (in_array($ext, $allowed)) {
            $filename = 'product_' . $product_id . '_' . time() . '.' . $ext;
            $dest = $upload_dir . $filename;
            if (move_uploaded_file($_FILES['image_file']['tmp_name'], $dest)) {
                $image_path = $dest;
            } else {
                $errors[] = 'Không thể upload hình ảnh';
            }
        } else {
            $errors[] = 'Định dạng ảnh không hợp lệ (chỉ chấp nhận jpg, png, gif, webp)';
        }
    } elseif (!empty($_POST['image_url'])) {
        $image_path = trim($_POST['image_url']);
    }
    
    // If no errors, update database
    if (empty($errors)) {
        error_log("EDIT.PHP: Validation passed, attempting to update database...");
        try {
            $updateData = [
                'name'             => $name,
                'category_id'      => $category_id,
                'price'            => $price,
                'description'      => $description,
                'status'           => $status,
                'type'             => $_POST['type'] ?? 'data_nguon_hang',
                'sale_price'       => isset($_POST['sale_price']) && $_POST['sale_price'] !== '' ? (float)$_POST['sale_price'] : null,
                // FIX: use isset so that value 0 is still saved
                'expiry_days'      => isset($_POST['expiry_days']) && $_POST['expiry_days'] !== '' ? (int)$_POST['expiry_days'] : 30,
                'sku'              => !empty($_POST['sku']) ? $_POST['sku'] : null,
                'short_description'=> $_POST['short_description'] ?? '',
                'meta_title'       => $_POST['meta_title'] ?? '',
                'meta_description' => $_POST['meta_description'] ?? '',
                'image'            => $image_path,
                // Data fields
                'record_count'     => isset($_POST['record_count']) && $_POST['record_count'] !== '' ? (int)$_POST['record_count'] : 0,
                'stock'            => isset($_POST['record_count']) && $_POST['record_count'] !== '' ? (int)$_POST['record_count'] : 0,
                'data_size'        => $_POST['data_size'] ?? '',
                'data_format'      => $_POST['data_format'] ?? '',
                'data_source'      => $_POST['data_source'] ?? '',
                'reliability'      => $_POST['reliability'] ?? '',
                // FIX: use isset so 0 is a valid value
                'quota'            => isset($_POST['quota']) && $_POST['quota'] !== '' ? (int)$_POST['quota'] : 100,
                'quota_per_usage'  => isset($_POST['quota_per_usage']) && $_POST['quota_per_usage'] !== '' ? (int)$_POST['quota_per_usage'] : 10,
                // Supplier fields
                'supplier_name'    => !empty($_POST['supplier_name']) ? $_POST['supplier_name'] : null,
                'supplier_title'   => !empty($_POST['supplier_title']) ? $_POST['supplier_title'] : null,
                'supplier_bio'     => !empty($_POST['supplier_bio']) ? $_POST['supplier_bio'] : null,
                'supplier_avatar'  => !empty($_POST['supplier_avatar']) ? $_POST['supplier_avatar'] : null,
                'supplier_social'  => !empty($_POST['supplier_social']) ? $_POST['supplier_social'] : null,
                // JSON fields
                'benefits'         => !empty($_POST['benefits']) ? $_POST['benefits'] : null,
                'data_structure'   => !empty($_POST['data_structure']) ? $_POST['data_structure'] : null,
                // Digital product
                'featured'         => isset($_POST['featured']) ? 1 : 0,
                'downloadable'     => isset($_POST['downloadable']) ? 1 : 0,
                'updated_at'       => date('Y-m-d H:i:s')
            ];
            $updated = $productsModel->update($product_id, $updateData);
        } catch (Exception $e) {
            $errors[] = 'Lỗi cập nhật: ' . $e->getMessage();
            $updated = false;
        }
        
        if ($updated) {
            // Store success in session for PRG pattern
            $_SESSION['product_saved'] = $product_id;
            
            // Show success message on same page (no redirect)
            $showSuccessMessage = true;
            
            // Refresh product data from database
            $products = $productsModel->query("SELECT * FROM products WHERE id = ?", [$product_id]);
            if (!empty($products)) {
                $product = $products[0];
            }
        } else {
            if (empty($errors)) {
                $errors[] = 'Không thể cập nhật data';
            }
        }
    }
}

// Use POST data only if there's a validation error (not after successful redirect)
// After successful save, we redirect and should show database data
$form_data = $product;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($errors)) {
    // Only show POST data if there were validation errors
    $form_data = array_merge($product, $_POST);
}

// Decode JSON fields for preview
$benefits_json = $form_data['benefits'] ?? '';
$data_structure_json = $form_data['data_structure'] ?? '';
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

    <!-- Error Messages -->
    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i>
        <ul class="error-list" style="margin:0;padding-left:20px;">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <!-- Success Message -->
    <?php if ($showSuccessMessage): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        Đã lưu thông tin sản phẩm thành công!
    </div>
    <?php endif; ?>

    <!-- Form -->
    <form method="POST" action="?page=admin&module=products&action=edit&id=<?= $product_id ?>&tab=<?= htmlspecialchars($_GET['tab'] ?? 'tab-basic') ?>" enctype="multipart/form-data" class="admin-form" novalidate>
        <input type="hidden" name="data_action" id="data_action" value="">
        <input type="hidden" name="data_id" id="delete_row_id" value="">
        <!-- Tab Navigation -->
        <div class="product-details-tabs">
            <div class="tabs-header">
                <button type="button" class="tab-btn active" data-tab="tab-basic">
                    <i class="fas fa-info-circle"></i>
                    Thông Tin Cơ Bản
                </button>
                <button type="button" class="tab-btn" data-tab="tab-data">
                    <i class="fas fa-database"></i>
                    Thông Tin Data
                </button>
                <button type="button" class="tab-btn" data-tab="tab-supplier">
                    <i class="fas fa-building"></i>
                    Nhà Cung Cấp
                </button>
                <button type="button" class="tab-btn" data-tab="tab-benefits">
                    <i class="fas fa-gift"></i>
                    Lợi Ích
                </button>
                <button type="button" class="tab-btn" data-tab="tab-structure">
                    <i class="fas fa-sitemap"></i>
                    Cấu Trúc Data
                </button>
                <button type="button" class="tab-btn" data-tab="tab-image">
                    <i class="fas fa-image"></i>
                    Hình Ảnh
                </button>
                <button type="button" class="tab-btn" data-tab="tab-seo">
                    <i class="fas fa-search"></i>
                    SEO
                </button>
                <button type="button" class="tab-btn" data-tab="tab-datamanagement">
                    <i class="fas fa-database"></i>
                    Quản Lý Dữ Liệu
                </button>
            </div>

            <div class="tabs-content">
                <!-- Tab 1: Thông Tin Cơ Bản -->
                <div class="tab-pane active" id="tab-basic">
                    <div class="form-row">
                        <div class="form-group form-group-8">
                            <label for="name" class="required">Tên Data</label>
                            <input type="text" id="name" name="name" 
                                   value="<?= htmlspecialchars($form_data['name'] ?? '') ?>" 
                                   placeholder="Ví dụ: Gói 100 Data Ngành Quần Áo" required>
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
                            <small>Số ngày sản phẩm có hiệu lực sau khi mua</small>
                        </div>

                        <div class="form-group">
                            <label for="featured">Nổi bật</label>
                            <div class="checkbox-wrapper">
                                <input type="checkbox" id="featured" name="featured" value="1" 
                                       <?= (($form_data['featured'] ?? 0) == 1) ? 'checked' : '' ?>>
                                <label for="featured">Hiển thị trang chủ</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="short_description">Mô tả ngắn</label>
                        <textarea id="short_description" name="short_description" rows="2" 
                                  placeholder="Mô tả ngắn gọn về data"><?= htmlspecialchars($form_data['short_description'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="description" class="required">Mô tả chi tiết</label>
                        <textarea id="description" name="description" rows="6" 
                                  placeholder="Nhập mô tả chi tiết về data nguồn hàng..." required><?= htmlspecialchars($form_data['description'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Tab 2: Thông Tin Data -->
                <div class="tab-pane" id="tab-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="record_count">Số lượng Record</label>
                            <input type="number" id="record_count" name="record_count" 
                                   value="<?= htmlspecialchars($form_data['record_count'] ?? '100') ?>" 
                                   placeholder="100" min="0">
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
                            <label for="quota_per_usage">Số Quota Hao Phí Mỗi Lần Tải</label>
                            <input type="number" id="quota_per_usage" name="quota_per_usage" 
                                   value="<?= htmlspecialchars($form_data['quota_per_usage'] ?? '10') ?>" 
                                   placeholder="10" min="1">
                            <small>Số quota hao phí sau mỗi lần truy cập</small>
                        </div>
                    </div>
                </div>

                <!-- Tab 3: Nhà Cung Cấp -->
                <div class="tab-pane" id="tab-supplier">
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
                    </div>

                    <div class="form-group">
                        <label>Liên Hệ</label>
                        <p class="text-muted" style="font-size:13px;margin-bottom:12px;">Thêm các thông tin liên hệ như website, hotline, zalo...</p>
                        
                        <div id="supplier-social-container">
                        </div>
                        
                        <button type="button" class="btn btn-sm btn-success" onclick="addSupplierSocial()" style="margin-top:8px;">
                            <i class="fas fa-plus"></i> Thêm liên hệ
                        </button>
                        
                        <input type="hidden" id="supplier_social" name="supplier_social" value="<?= htmlspecialchars($form_data['supplier_social'] ?? '') ?>">
                    </div>
                </div>

                <!-- Tab 4: Lợi Ích -->
                <div class="tab-pane" id="tab-benefits">
                    <div class="form-group">
                        <label>Danh sách lợi ích</label>
                        <p class="text-muted" style="font-size:13px;margin-bottom:12px;">Thêm các lợi ích của sản phẩm. Click nút "+" để thêm dòng mới.</p>
                        
                        <div id="benefits-container">
                        </div>
                        
                        <button type="button" class="btn btn-sm btn-success" onclick="addBenefit()" style="margin-top:8px;">
                            <i class="fas fa-plus"></i> Thêm lợi ích
                        </button>
                        
                        <input type="hidden" id="benefits" name="benefits" value="<?= htmlspecialchars($benefits_json) ?>">
                    </div>
                    <div class="benefits-preview" id="benefitsPreview" style="margin-top:12px;padding:12px;background:#f8f9fa;border-radius:8px;min-height:60px;">
                        <p class="preview-empty" style="color:#999;margin:0;">Xem trước lợi ích sẽ hiển thị ở đây...</p>
                    </div>
                </div>

                <!-- Tab 5: Cấu Trúc Data -->
                <div class="tab-pane" id="tab-structure">
                    <div class="form-group">
                        <label>Cấu Trúc Data</label>
                        <p class="text-muted" style="font-size:13px;margin-bottom:12px;">Thêm các nhóm thông tin và trường dữ liệu. Mỗi nhóm có thể chứa nhiều trường.</p>
                        
                        <div id="structure-container">
                        </div>
                        
                        <button type="button" class="btn btn-sm btn-success" onclick="addStructureGroup()" style="margin-top:8px;">
                            <i class="fas fa-plus"></i> Thêm nhóm
                        </button>
                        
                        <input type="hidden" id="data_structure" name="data_structure" value="<?= htmlspecialchars($data_structure_json) ?>">
                    </div>
                    <div class="structure-preview" id="dataStructurePreview" style="margin-top:12px;padding:12px;background:#f8f9fa;border-radius:8px;min-height:60px;">
                        <p class="preview-empty" style="color:#999;margin:0;">Xem trước cấu trúc sẽ hiển thị ở đây...</p>
                    </div>
                </div>

                <!-- Tab 6: Hình Ảnh -->
                <div class="tab-pane" id="tab-image">
                    <?php $current_image = $form_data['image'] ?? ''; ?>
                    
                    <?php if (!empty($current_image)): ?>
                    <div class="form-group">
                        <label>Ảnh Hiện Tại</label>
                        <div style="margin-bottom:12px;">
                            <img src="<?= htmlspecialchars($current_image) ?>" alt="Ảnh hiện tại"
                                 style="max-width:300px;max-height:200px;border-radius:8px;border:1px solid #ddd;object-fit:cover;"
                                 onerror="this.style.display='none'">
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="image_file">Upload Ảnh Mới</label>
                        <div style="border:2px dashed #d1d5db;border-radius:8px;padding:24px;text-align:center;cursor:pointer;transition:border-color 0.3s;" 
                             onclick="document.getElementById('image_file').click()" id="uploadZone">
                            <i class="fas fa-cloud-upload-alt" style="font-size:2rem;color:#9ca3af;margin-bottom:8px;display:block;"></i>
                            <p style="margin:0;color:#6b7280;">Nhấp để chọn ảnh hoặc kéo thả vào đây</p>
                            <p style="margin:4px 0 0;font-size:12px;color:#9ca3af;">JPG, PNG, GIF, WebP — Tối đa 5MB</p>
                        </div>
                        <input type="file" id="image_file" name="image_file" accept="image/*" style="display:none;"
                               onchange="previewUploadedImage(this)">
                        <div id="imagePreview" style="margin-top:12px;display:none;">
                            <img id="previewImg" src="" alt="Preview" style="max-width:300px;max-height:200px;border-radius:8px;object-fit:cover;border:1px solid #ddd;">
                            <p style="margin:4px 0 0;font-size:12px;color:#10B981;"><i class="fas fa-check-circle"></i> Ảnh mới đã chọn — sẽ được upload khi lưu</p>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top:16px;">
                        <label for="image_url">Hoặc nhập URL ảnh</label>
                        <input type="url" id="image_url" name="image_url" 
                               value="<?= htmlspecialchars(filter_var($current_image, FILTER_VALIDATE_URL) ? $current_image : '') ?>" 
                               placeholder="https://example.com/image.jpg">
                        <small>Nếu upload ảnh thì URL này sẽ bị bỏ qua</small>
                    </div>
                </div>

                <!-- Tab 7: SEO -->
                <div class="tab-pane" id="tab-seo">
                    <div class="form-group">
                        <label for="meta_title">Tiêu Đề SEO</label>
                        <input type="text" id="meta_title" name="meta_title" 
                               value="<?= htmlspecialchars($form_data['meta_title'] ?? '') ?>" 
                               placeholder="Tiêu đề tối ưu cho SEO">
                        <small>Tối đa 60 ký tự</small>
                    </div>

                    <div class="form-group">
                        <label for="meta_description">Mô Tả SEO</label>
                        <textarea id="meta_description" name="meta_description" rows="3" 
                                  placeholder="Mô tả ngắn gọn cho SEO"><?= htmlspecialchars($form_data['meta_description'] ?? '') ?></textarea>
                        <small>Tối đa 160 ký tự</small>
                    </div>
                </div>

                <!-- Tab 8: Quản Lý Dữ Liệu -->
                <div class="tab-pane" id="tab-datamanagement">
                    <?php
                    // Initialize ProductDataModel for this tab
                    require_once __DIR__ . '/../../../models/ProductDataModel.php';
                    $productDataModel = new ProductDataModel();
                    
                    // Get data count
                    $dataCount = $productDataModel->countByProduct($product_id);
                    
                    // Handle data management actions
                    $dmMessage = '';
                    $dmMessageType = '';
                    
                    if (isset($_POST['data_action_type'])) {
                        // Upload Excel
                        if ($_POST['data_action_type'] === 'upload_excel' && !empty($_FILES['excel_file']['tmp_name'])) {
                            require_once __DIR__ . '/../../../services/ExcelParserService.php';
                            $parser = new ExcelParserService();
                            $result = $parser->parse($_FILES['excel_file']['tmp_name']);
                            
                            if ($result['success']) {
                                $productDataModel->deleteByProduct($product_id);
                                $inserted = $productDataModel->bulkInsert($result['data']);
                                $dmMessage = "Đã upload thành công {$inserted} dòng dữ liệu!";
                                $dmMessageType = 'success';
                                if (!empty($result['warnings'])) {
                                    $dmMessage .= " (" . count($result['warnings']) . " cảnh báo)";
                                }
                            } else {
                                $dmMessage = $result['error'];
                                $dmMessageType = 'error';
                            }
                        }
                        // Add Manual Entry
                        elseif ($_POST['data_action_type'] === 'add_manual') {
                            $data = [
                                'product_id' => $product_id,
                                'supplier_name' => trim($_POST['supplier_name'] ?? ''),
                                'address' => trim($_POST['address'] ?? ''),
                                'wechat_account' => trim($_POST['wechat_account'] ?? ''),
                                'phone' => trim($_POST['phone'] ?? ''),
                                'wechat_qr' => trim($_POST['wechat_qr'] ?? '')
                            ];
                            
                            // Allow empty entries - no validation required
                            $productDataModel->create($data);
                            $dmMessage = 'Đã thêm dữ liệu thành công!';
                            $dmMessageType = 'success';
                        }
                        // Delete single row
                        elseif ($_POST['data_action_type'] === 'delete_row' && !empty($_POST['data_id'])) {
                            $productDataModel->delete((int)$_POST['data_id']);
                            $dmMessage = 'Đã xóa dữ liệu!';
                            $dmMessageType = 'success';
                        }
                        // Update row
                        elseif ($_POST['data_action_type'] === 'update_row' && !empty($_POST['data_id'])) {
                            $dataId = (int)$_POST['data_id'];
                            $data = [
                                'supplier_name' => trim($_POST['supplier_name'] ?? ''),
                                'address' => trim($_POST['address'] ?? ''),
                                'wechat_account' => trim($_POST['wechat_account'] ?? ''),
                                'phone' => trim($_POST['phone'] ?? ''),
                                'wechat_qr' => trim($_POST['wechat_qr'] ?? '')
                            ];
                            
                            $productDataModel->update($dataId, $data);
                            $dmMessage = 'Đã cập nhật dữ liệu!';
                            $dmMessageType = 'success';
                        }
                        // Delete all data
                        elseif ($_POST['data_action_type'] === 'delete_all') {
                            $productDataModel->deleteByProduct($product_id);
                            $dmMessage = 'Đã xóa tất cả dữ liệu!';
                            $dmMessageType = 'success';
                        }
                        
                        // Refresh data count
                        $dataCount = $productDataModel->countByProduct($product_id);
                    }
                    
                    // Get pagination
                    $dmPage = max(1, (int)($_GET['dm_page'] ?? 1));
                    $dmPerPage = 10;
                    $dataPaginated = $productDataModel->getByProductPaginated($product_id, $dmPage, $dmPerPage);
                    $dataList = $dataPaginated['data'];
                    
                    // Get edit data if requested
                    $editDataItem = null;
                    if (isset($_GET['dm_action']) && $_GET['dm_action'] === 'edit' && !empty($_GET['dm_data_id'])) {
                        $editDataItem = $productDataModel->find((int)$_GET['dm_data_id']);
                    }
                    ?>
                    
                    <!-- Message -->
                    <?php if (!empty($dmMessage)): ?>
                    <div class="alert alert-<?= $dmMessageType === 'success' ? 'success' : 'danger' ?>">
                        <?= htmlspecialchars($dmMessage) ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Stats -->
                    <div class="data-stats" style="background:#f8f9fa;padding:15px;border-radius:8px;margin-bottom:20px;">
                        <h4 style="margin:0 0 10px 0;"><i class="fas fa-chart-bar"></i> Thống Kê Dữ Liệu</h4>
                        <p style="margin:0;">Tổng số dòng dữ liệu: <strong><?= (int)$dataCount ?></strong></p>
                    </div>
                    
                    <!-- Upload Excel Section -->
                    <div class="upload-section" style="background:#fff;padding:20px;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);margin-bottom:20px;">
                        <h5 style="margin:0 0 15px 0;"><i class="fas fa-file-excel"></i> Upload File Excel</h5>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <input type="file" name="excel_file" accept=".xlsx,.xls,.csv" 
                                   style="flex:1;padding:8px;border:1px solid #ddd;border-radius:4px;">
                            <button type="button" class="btn btn-primary" onclick="submitDataAction('upload_excel')">
                                <i class="fas fa-upload"></i> Upload
                            </button>
                        </div>
                        <small style="color:#666;">Chấp nhận file .xlsx, .xls, .csv</small>
                    </div>
                    
                    <!-- Manual Entry Section -->
                    <div class="manual-entry-section" style="background:#fff;padding:20px;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);margin-bottom:20px;">
                        <h5 style="margin:0 0 15px 0;"><i class="fas fa-plus-circle"></i> Thêm Thủ Công</h5>
                        <?php if ($editDataItem): ?>
                        <div style="margin-bottom:15px;">
                            <input type="hidden" name="data_action_type" value="update_row">
                            <input type="hidden" name="data_id" value="<?= (int)$editDataItem['id'] ?>">
                            <div class="form-row" style="display:flex;gap:10px;flex-wrap:wrap;">
                                <div class="form-group" style="flex:1;min-width:150px;">
                                    <label>Nhà Cung Cấp</label>
                                    <input type="text" name="supplier_name" value="<?= htmlspecialchars($editDataItem['supplier_name'] ?? '') ?>" 
                                           class="form-control">
                                </div>
                                <div class="form-group" style="flex:1;min-width:150px;">
                                    <label>Địa Chỉ</label>
                                    <input type="text" name="address" value="<?= htmlspecialchars($editDataItem['address'] ?? '') ?>" 
                                           class="form-control">
                                </div>
                                <div class="form-group" style="flex:1;min-width:150px;">
                                    <label>WeChat</label>
                                    <input type="text" name="wechat_account" value="<?= htmlspecialchars($editDataItem['wechat_account'] ?? '') ?>" 
                                           class="form-control">
                                </div>
                                <div class="form-group" style="flex:1;min-width:150px;">
                                    <label>Điện Thoại</label>
                                    <input type="text" name="phone" value="<?= htmlspecialchars($editDataItem['phone'] ?? '') ?>" 
                                           class="form-control">
                                </div>
                                <div class="form-group" style="flex:1;min-width:150px;">
                                    <label>QR WeChat</label>
                                    <input type="text" name="wechat_qr" value="<?= htmlspecialchars($editDataItem['wechat_qr'] ?? '') ?>" 
                                           class="form-control">
                                </div>
                                <div style="display:flex;gap:5px;align-items:flex-end;">
                                    <button type="button" class="btn btn-success btn-sm" onclick="submitDataAction('update_row')">
                                        <i class="fas fa-save"></i> Lưu
                                    </button>
                                    <a href="?page=admin&module=products&action=edit&id=<?= $product_id ?>" class="btn btn-outline btn-sm">
                                        Hủy
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <div>
                            <input type="hidden" name="data_action_type" value="add_manual">
                            <div class="form-row" style="display:flex;gap:10px;flex-wrap:wrap;">
                                <div class="form-group" style="flex:1;min-width:150px;">
                                    <label>Nhà Cung Cấp</label>
                                    <input type="text" name="supplier_name" placeholder="Tên nhà cung cấp" 
                                           class="form-control">
                                </div>
                                <div class="form-group" style="flex:1;min-width:150px;">
                                    <label>Địa Chỉ</label>
                                    <input type="text" name="address" placeholder="Địa chỉ" 
                                           class="form-control">
                                </div>
                                <div class="form-group" style="flex:1;min-width:150px;">
                                    <label>WeChat</label>
                                    <input type="text" name="wechat_account" placeholder="Tài khoản WeChat" 
                                           class="form-control">
                                </div>
                                <div class="form-group" style="flex:1;min-width:150px;">
                                    <label>Điện Thoại</label>
                                    <input type="text" name="phone" placeholder="Số điện thoại" 
                                           class="form-control">
                                </div>
                                <div class="form-group" style="flex:1;min-width:150px;">
                                    <label>QR WeChat</label>
                                    <input type="text" name="wechat_qr" placeholder="URL QR code" 
                                           class="form-control">
                                </div>
                                <button type="button" class="btn btn-success" onclick="submitDataAction('add_manual')">
                                    <i class="fas fa-plus"></i> Thêm
                                </button>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Data Table -->
                    <?php if (!empty($dataList)): ?>
                    <div class="table-container" style="background:#fff;padding:20px;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
                            <h5 style="margin:0;"><i class="fas fa-list"></i> Danh Sách Dữ Liệu</h5>
                            <div>
                                <input type="hidden" name="data_action_type" value="delete_all">
                                <button type="button" class="btn btn-danger btn-sm" onclick="if(confirm('Bạn có chắc muốn xóa tất cả dữ liệu?')){submitDataAction('delete_all')}">
                                    <i class="fas fa-trash"></i> Xóa Tất Cả
                                </button>
                            </div>
                        </div>
                        
                        <table class="data-table" style="width:100%;border-collapse:collapse;">
                            <thead>
                                <tr style="background:#f8f9fa;">
                                    <th style="padding:10px;text-align:left;border-bottom:2px solid #dee2e6;">#</th>
                                    <th style="padding:10px;text-align:left;border-bottom:2px solid #dee2e6;">Nhà Cung Cấp</th>
                                    <th style="padding:10px;text-align:left;border-bottom:2px solid #dee2e6;">Địa Chỉ</th>
                                    <th style="padding:10px;text-align:left;border-bottom:2px solid #dee2e6;">WeChat</th>
                                    <th style="padding:10px;text-align:left;border-bottom:2px solid #dee2e6;">Điện Thoại</th>
                                    <th style="padding:10px;text-align:left;border-bottom:2px solid #dee2e6;">QR WeChat</th>
                                    <th style="padding:10px;text-align:left;border-bottom:2px solid #dee2e6;">Thao Tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dataList as $index => $item): ?>
                                <tr style="border-bottom:1px solid #dee2e6;">
                                    <td style="padding:10px;"><?= ($dmPage - 1) * $dmPerPage + $index + 1 ?></td>
                                    <td style="padding:10px;"><?= htmlspecialchars($item['supplier_name'] ?? '') ?></td>
                                    <td style="padding:10px;"><?= htmlspecialchars($item['address'] ?? '') ?></td>
                                    <td style="padding:10px;"><?= htmlspecialchars($item['wechat_account'] ?? '') ?></td>
                                    <td style="padding:10px;"><?= htmlspecialchars($item['phone'] ?? '') ?></td>
                                    <td style="padding:10px;">
                                        <?php if (!empty($item['wechat_qr'])): ?>
                                        <a href="<?= htmlspecialchars($item['wechat_qr']) ?>" target="_blank">Xem</a>
                                        <?php else: ?>
                                        -
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding:10px;">
                                        <a href="?page=admin&module=products&action=edit&id=<?= $product_id ?>&dm_action=edit&dm_data_id=<?= (int)$item['id'] ?>" 
                                           class="btn btn-primary btn-sm" style="margin-right:5px;">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <input type="hidden" name="data_id_<?= (int)$item['id'] ?>" value="<?= (int)$item['id'] ?>">
                                        <button type="button" class="btn btn-danger btn-sm" onclick="if(confirm('Xóa dòng này?')){document.getElementById('data_action').value='delete_row';document.getElementById('delete_row_id').value='<?= (int)$item['id'] ?>';document.querySelector('.admin-form').submit()}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <!-- Pagination -->
                        <?php if ($dataPaginated['last_page'] > 1): ?>
                        <div class="pagination" style="display:flex;justify-content:center;gap:5px;margin-top:20px;">
                            <?php for ($i = 1; $i <= $dataPaginated['last_page']; $i++): ?>
                            <a href="?page=admin&module=products&action=edit&id=<?= $product_id ?>&dm_page=<?= $i ?>" 
                               class="<?= $i === $dmPage ? 'active' : '' ?>"
                               style="padding:8px 12px;border:1px solid #ddd;border-radius:4px;text-decoration:none;color:#333;<?= $i === $dmPage ? 'background:#007bff;color:#fff;' : '' ?>">
                                <?= $i ?>
                            </a>
                            <?php endfor; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Chưa có dữ liệu. Hãy upload file Excel hoặc thêm thủ công.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary" onclick="return checkRequiredFields()">
                <i class="fas fa-save"></i>
                Lưu Thay Đổi
            </button>
            <a href="?page=admin&module=products" class="btn btn-outline">
                <i class="fas fa-times"></i>
                Hủy
            </a>
        </div>
    </form>
</div>

<script>
function previewUploadedImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('imagePreview').style.display = 'block';
            // Clear URL input when file is selected
            document.getElementById('image_url').value = '';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Drag-and-drop support
var uploadZone = document.getElementById('uploadZone');
if (uploadZone) {
    uploadZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.style.borderColor = '#3B82F6';
        this.style.background = '#EFF6FF';
    });
    uploadZone.addEventListener('dragleave', function() {
        this.style.borderColor = '#d1d5db';
        this.style.background = '';
    });
    uploadZone.addEventListener('drop', function(e) {
        e.preventDefault();
        this.style.borderColor = '#d1d5db';
        this.style.background = '';
        var files = e.dataTransfer.files;
        if (files.length > 0) {
            document.getElementById('image_file').files = files;
            previewUploadedImage(document.getElementById('image_file'));
        }
    });
}

// ========== DYNAMIC FORM FOR BENEFITS ==========
var benefitCounter = 0;

function addBenefit(value = '') {
    benefitCounter++;
    var container = document.getElementById('benefits-container');
    var div = document.createElement('div');
    div.className = 'benefit-row';
    div.style.cssText = 'display:flex;align-items:center;gap:8px;margin-bottom:8px;';
    div.innerHTML = `
        <input type="text" class="benefit-input" value="${escapeHtml(value)}" 
               placeholder="Nhập lợi ích..." 
               style="flex:1;padding:8px;border:1px solid #d1d5db;border-radius:6px;"
               oninput="updateBenefits()">
        <button type="button" onclick="removeBenefit(this)" style="padding:8px 12px;background:#fee2e2;border:1px solid #fecaca;color:#dc2626;border-radius:6px;cursor:pointer;">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(div);
    updateBenefits();
}

function removeBenefit(btn) {
    btn.parentElement.remove();
    updateBenefits();
}

function updateBenefits() {
    var inputs = document.querySelectorAll('.benefit-input');
    var benefits = [];
    inputs.forEach(function(input) {
        if (input.value.trim()) {
            benefits.push(input.value.trim());
        }
    });
    document.getElementById('benefits').value = JSON.stringify(benefits);
    updateBenefitsPreview();
}

function updateBenefitsPreview() {
    var benefits = [];
    try {
        var val = document.getElementById('benefits').value;
        if (val) benefits = JSON.parse(val);
    } catch(e) {}
    
    var preview = document.getElementById('benefitsPreview');
    if (benefits.length > 0) {
        preview.innerHTML = '<ul style="margin:0;padding-left:20px;">' + 
            benefits.map(function(b) { return '<li>' + escapeHtml(b) + '</li>'; }).join('') + 
            '</ul>';
    } else {
        preview.innerHTML = '<p class="preview-empty" style="color:#999;margin:0;">Xem trước lợi ích sẽ hiển thị ở đây...</p>';
    }
}

// Initialize benefits
(function() {
    var existingBenefits = document.getElementById('benefits').value || '';
    if (existingBenefits) {
        try {
            var benefits = JSON.parse(existingBenefits);
            benefits.forEach(function(b) { addBenefit(b); });
        } catch(e) { addBenefit(''); }
    } else {
        addBenefit('');
    }
})();

// ========== DYNAMIC FORM FOR DATA STRUCTURE ==========
var structureCounter = 0;

function addStructureGroup(title = '', items = []) {
    structureCounter++;
    var container = document.getElementById('structure-container');
    var groupId = 'group-' + structureCounter;
    
    var div = document.createElement('div');
    div.className = 'structure-group';
    div.style.cssText = 'border:1px solid #e5e7eb;border-radius:8px;margin-bottom:16px;overflow:hidden;';
    
    var itemsHtml = '';
    if (items.length > 0) {
        items.forEach(function(item) {
            itemsHtml += createStructureItemHtml(item.title || '');
        });
    } else {
        itemsHtml = createStructureItemHtml('');
    }
    
    div.innerHTML = `
        <div style="background:#f9fafb;padding:12px;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;gap:8px;">
            <input type="text" class="group-title" value="${escapeHtml(title)}" 
                   placeholder="Tên nhóm..." 
                   style="flex:1;padding:8px;border:1px solid #d1d5db;border-radius:6px;"
                   oninput="updateDataStructure()">
            <button type="button" onclick="removeStructureGroup(this)" style="padding:8px 12px;background:#fee2e2;border:1px solid #fecaca;color:#dc2626;border-radius:6px;cursor:pointer;">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        <div class="group-items" style="padding:12px;">
            ${itemsHtml}
        </div>
        <div style="padding:8px 12px;border-top:1px solid #e5e7eb;">
            <button type="button" onclick="addStructureItem()" style="padding:6px 12px;background:#eff6ff;border:1px solid #bfdbfe;color:#2563eb;border-radius:6px;cursor:pointer;font-size:13px;">
                <i class="fas fa-plus"></i> Thêm trường
            </button>
        </div>
    `;
    container.appendChild(div);
    updateDataStructure();
}

function createStructureItemHtml(value = '') {
    return `
        <div class="item-row" style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
            <input type="text" value="${escapeHtml(value)}" 
                   placeholder="Tên trường..." 
                   style="flex:1;padding:8px;border:1px solid #d1d5db;border-radius:6px;"
                   oninput="updateDataStructure()">
            <button type="button" onclick="removeStructureItem(this)" style="padding:8px 12px;background:#fee2e2;border:1px solid #fecaca;color:#dc2626;border-radius:6px;cursor:pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
}

function addStructureItem() {
    var containers = document.querySelectorAll('.group-items');
    var lastContainer = containers[containers.length - 1];
    if (lastContainer) {
        var temp = document.createElement('div');
        temp.innerHTML = createStructureItemHtml('');
        lastContainer.appendChild(temp.firstElementChild);
    }
    updateDataStructure();
}

function removeStructureItem(btn) {
    var groupItems = btn.closest('.group-items');
    btn.parentElement.remove();
    if (groupItems.querySelectorAll('.item-row').length === 0) {
        groupItems.innerHTML = createStructureItemHtml('');
    }
    updateDataStructure();
}

function removeStructureGroup(btn) {
    btn.closest('.structure-group').remove();
    updateDataStructure();
}

function updateDataStructure() {
    var groups = [];
    var groupElements = document.querySelectorAll('.structure-group');
    groupElements.forEach(function(group) {
        var title = group.querySelector('.group-title').value.trim();
        if (title) {
            var items = [];
            group.querySelectorAll('.item-row input').forEach(function(input) {
                if (input.value.trim()) {
                    items.push({title: input.value.trim()});
                }
            });
            groups.push({title: title, items: items});
        }
    });
    document.getElementById('data_structure').value = JSON.stringify(groups);
    updateDataStructurePreview();
}

function updateDataStructurePreview() {
    var structure = [];
    try {
        var val = document.getElementById('data_structure').value;
        if (val) structure = JSON.parse(val);
    } catch(e) {}
    
    var preview = document.getElementById('dataStructurePreview');
    if (structure.length > 0) {
        var html = '';
        structure.forEach(function(group) {
            html += '<div style="margin-bottom:12px;">';
            html += '<strong style="color:#1f2937;">' + escapeHtml(group.title) + '</strong>';
            if (group.items && group.items.length > 0) {
                html += '<ul style="margin:4px 0 0 16px;color:#4b5563;">';
                group.items.forEach(function(item) {
                    html += '<li>' + escapeHtml(item.title) + '</li>';
                });
                html += '</ul>';
            }
            html += '</div>';
        });
        preview.innerHTML = html;
    } else {
        preview.innerHTML = '<p class="preview-empty" style="color:#999;margin:0;">Xem trước cấu trúc sẽ hiển thị ở đây...</p>';
    }
}

// Initialize data structure
(function() {
    var existingStructure = document.getElementById('data_structure').value || '';
    if (existingStructure) {
        try {
            var structure = JSON.parse(existingStructure);
            structure.forEach(function(group) { 
                addStructureGroup(group.title, group.items || []); 
            });
        } catch(e) { addStructureGroup('', []); }
    } else {
        addStructureGroup('', []);
    }
})();

// ========== DYNAMIC FORM FOR SUPPLIER SOCIAL ==========
var socialCounter = 0;

function addSupplierSocial(key = '', value = '') {
    socialCounter++;
    var container = document.getElementById('supplier-social-container');
    var div = document.createElement('div');
    div.className = 'social-row';
    div.style.cssText = 'display:flex;align-items:center;gap:8px;margin-bottom:8px;';
    div.innerHTML = `
        <select class="social-key" onchange="updateSupplierSocial()" style="padding:8px;border:1px solid #d1d5db;border-radius:6px;width:140px;">
            <option value="website" ${key === 'website' ? 'selected' : ''}>Website</option>
            <option value="hotline" ${key === 'hotline' ? 'selected' : ''}>Hotline</option>
            <option value="zalo" ${key === 'zalo' ? 'selected' : ''}>Zalo</option>
            <option value="email" ${key === 'email' ? 'selected' : ''}>Email</option>
            <option value="facebook" ${key === 'facebook' ? 'selected' : ''}>Facebook</option>
            <option value="address" ${key === 'address' ? 'selected' : ''}>Địa chỉ</option>
            <option value="other" ${key === 'other' ? 'selected' : ''}>Khác</option>
        </select>
        <input type="text" class="social-value" value="${escapeHtml(value)}" 
               placeholder="Giá trị..." 
               style="flex:1;padding:8px;border:1px solid #d1d5db;border-radius:6px;"
               oninput="updateSupplierSocial()">
        <button type="button" onclick="removeSupplierSocial(this)" style="padding:8px 12px;background:#fee2e2;border:1px solid #fecaca;color:#dc2626;border-radius:6px;cursor:pointer;">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(div);
    updateSupplierSocial();
}

function removeSupplierSocial(btn) {
    btn.parentElement.remove();
    updateSupplierSocial();
}

function updateSupplierSocial() {
    var social = {};
    var rows = document.querySelectorAll('.social-row');
    rows.forEach(function(row) {
        var key = row.querySelector('.social-key').value;
        var value = row.querySelector('.social-value').value.trim();
        if (value) {
            social[key] = value;
        }
    });
    document.getElementById('supplier_social').value = Object.keys(social).length > 0 ? JSON.stringify(social) : '';
}

// Initialize supplier social
(function() {
    var existingSocial = document.getElementById('supplier_social').value || '';
    if (existingSocial) {
        try {
            var social = JSON.parse(existingSocial);
            Object.keys(social).forEach(function(key) { 
                addSupplierSocial(key, social[key]); 
            });
        } catch(e) { addSupplierSocial('website', ''); }
    } else {
        addSupplierSocial('website', '');
    }
})();

// Helper to escape HTML
function escapeHtml(text) {
    if (!text) return '';
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Submit form with specific action
function submitDataAction(action) {
    // Get current active tab
    var activeTab = document.querySelector('.tab-btn.active');
    var tabParam = activeTab ? '&tab=' + activeTab.dataset.tab : '';
    
    document.getElementById('data_action').value = action;
    var form = document.querySelector('.admin-form');
    form.action = '?page=admin&module=products&action=edit&id=<?= $product_id ?>' + tabParam;
    form.submit();
}

// Submit form for saving product (main action)
function submitProductForm() {
    // Get current active tab
    var activeTab = document.querySelector('.tab-btn.active');
    var tabParam = activeTab ? '&tab=' + activeTab.dataset.tab : '';
    
    document.getElementById('data_action').value = '';
    var form = document.querySelector('.admin-form');
    form.action = '?page=admin&module=products&action=edit&id=<?= $product_id ?>' + tabParam;
    form.submit();
}

// Check required fields before submit
function checkRequiredFields() {
    var name = document.getElementById('name').value.trim();
    var category = document.getElementById('category_id').value;
    var price = document.getElementById('price').value;
    var description = document.getElementById('description').value;
    
    var errors = [];
    
    if (!name) {
        errors.push('Tên sản phẩm');
    }
    if (!category || category === '0') {
        errors.push('Danh mục');
    }
    if (!price || parseFloat(price) <= 0) {
        errors.push('Giá sản phẩm');
    }
    if (!description) {
        errors.push('Mô tả sản phẩm');
    }
    
    if (errors.length > 0) {
        alert('Vui lòng điền: ' + errors.join(', '));
        return false;
    }
    
    // Set data_action to empty for main form save
    document.getElementById('data_action').value = '';
    
    return true;
}

// Initialize tab from URL parameter on page load
document.addEventListener('DOMContentLoaded', function() {
    var urlParams = new URLSearchParams(window.location.search);
    var activeTab = urlParams.get('tab');
    
    if (activeTab) {
        // Update tab buttons
        document.querySelectorAll('.tab-btn').forEach(function(btn) {
            btn.classList.remove('active');
            if (btn.dataset.tab === activeTab) {
                btn.classList.add('active');
            }
        });
        
        // Update tab panes
        document.querySelectorAll('.tab-pane').forEach(function(pane) {
            pane.classList.remove('active');
            if (pane.id === activeTab) {
                pane.classList.add('active');
            }
        });
    }
});
</script>
