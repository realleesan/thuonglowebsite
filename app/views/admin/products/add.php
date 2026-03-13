<?php
/**
 * Admin Products Add - Tái cấu trúc cho sản phẩm số (Data Nguồn Hàng)
 * Designed for digital products / data products
 * Using 2-layer tab layout (tabs container + tab-pane)
 */

// Khởi tạo View & ServiceManager
require_once __DIR__ . '/../../../../core/view_init.php';

// Chọn service admin (được inject từ index.php)
$service = isset($currentService) ? $currentService : ($adminService ?? null);

try {
    // Get categories for dropdown using AdminService
    $categoriesData = $service->getActiveCategoriesForDropdown();
    $categories = $categoriesData['categories'] ?? [];
    
} catch (Exception $e) {
    $errorHandler->logError('Admin Products Add View Error', $e);
    $categories = [];
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
    
    // Handle image upload
    $image_path = '';
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'assets/images/products/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];
        if (in_array($ext, $allowed)) {
            $filename = 'product_new_' . time() . '.' . $ext;
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
    
    // If no errors, save to database
    if (empty($errors)) {
        require_once __DIR__ . '/../../../models/ProductsModel.php';
        $productsModel = new ProductsModel();
        
        $record_count = isset($_POST['record_count']) && $_POST['record_count'] !== '' ? (int)$_POST['record_count'] : 0;
        
        $insertData = [
            'name'             => $name,
            'slug'             => createSlugProduct($name),
            'category_id'      => $category_id,
            'price'            => $price,
            'stock'            => $record_count,
            'description'      => $description,
            'status'           => $status,
            'type'             => $_POST['type'] ?? 'data_nguon_hang',
            'sale_price'       => isset($_POST['sale_price']) && $_POST['sale_price'] !== '' ? (float)$_POST['sale_price'] : null,
            // FIX: use isset so value 0 is still saved
            'expiry_days'      => isset($_POST['expiry_days']) && $_POST['expiry_days'] !== '' ? (int)$_POST['expiry_days'] : 30,
            'sku'              => !empty($_POST['sku']) ? $_POST['sku'] : null,
            'short_description'=> $_POST['short_description'] ?? '',
            'meta_title'       => $_POST['meta_title'] ?? '',
            'meta_description' => $_POST['meta_description'] ?? '',
            'image'            => $image_path,
            // Data fields
            'record_count'     => $record_count,
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
            'digital'          => 1,
            'featured'         => isset($_POST['featured']) ? 1 : 0,
            'downloadable'     => isset($_POST['downloadable']) ? 1 : 0,
            'created_at'       => date('Y-m-d H:i:s')
        ];
        
        try {
            $id = $productsModel->create($insertData);
            if ($id) {
                // If we used a temp filename, rename it with the real ID
                if (!empty($image_path) && strpos($image_path, 'product_new_') !== false) {
                    $ext = pathinfo($image_path, PATHINFO_EXTENSION);
                    $new_path = 'assets/images/products/product_' . $id . '_' . time() . '.' . $ext;
                    @rename($image_path, $new_path);
                    $productsModel->update($id, ['image' => $new_path]);
                }
                // Redirect properly using header
                header('Location: ?page=admin&module=products');
                exit;
            } else {
                $errors[] = 'Không thể lưu data';
            }
        } catch (Exception $e) {
            $errors[] = 'Lỗi lưu: ' . $e->getMessage();
        }
    }
}

// Helper function to create slug  
if (!function_exists('createSlugProduct')) {
    function createSlugProduct($str) {
        $str = strtolower($str);
        $str = preg_replace('/[^a-z0-9\s-]/', '', $str);
        $str = preg_replace('/\s+/', '-', $str);
        $str = trim($str, '-');
        return $str;
    }
}
?>

<div class="products-page products-add-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-plus-circle"></i>
                Thêm Data Mới
            </h1>
            <p class="page-description">Thêm data nguồn hàng mới vào hệ thống</p>
        </div>
        <div class="page-header-right">
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

    <!-- Form -->
    <form method="POST" action="?page=admin&module=products&action=add" enctype="multipart/form-data" class="admin-form" novalidate>
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
            </div>

            <div class="tabs-content">
                <!-- Tab 1: Thông Tin Cơ Bản -->
                <div class="tab-pane active" id="tab-basic">
                    <div class="form-row">
                        <div class="form-group form-group-8">
                            <label for="name" class="required">Tên Data</label>
                            <input type="text" id="name" name="name" 
                                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" 
                                   placeholder="Ví dụ: Gói 100 Data Ngành Quần Áo" required>
                        </div>
                        
                        <div class="form-group form-group-4">
                            <label for="type">Loại Data</label>
                            <select id="type" name="type">
                                <option value="data_nguon_hang" <?= (($_POST['type'] ?? 'data_nguon_hang') == 'data_nguon_hang') ? 'selected' : '' ?>>Data Nguồn Hàng</option>
                                <option value="khoa_hoc" <?= (($_POST['type'] ?? '') == 'khoa_hoc') ? 'selected' : '' ?>>Khóa Học</option>
                                <option value="tool" <?= (($_POST['type'] ?? '') == 'tool') ? 'selected' : '' ?>>Công Cụ</option>
                                <option value="dich_vu" <?= (($_POST['type'] ?? '') == 'dich_vu') ? 'selected' : '' ?>>Dịch Vụ</option>
                            </select>
                        </div>
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
                            <label for="sale_price">Giá khuyến mãi (VNĐ)</label>
                            <input type="number" id="sale_price" name="sale_price" 
                                   value="<?= htmlspecialchars($_POST['sale_price'] ?? '') ?>" 
                                   placeholder="0" min="0" step="1000">
                        </div>

                        <div class="form-group">
                            <label for="status">Trạng thái</label>
                            <select id="status" name="status">
                                <option value="active" <?= (($_POST['status'] ?? 'active') == 'active') ? 'selected' : '' ?>>Hoạt động</option>
                                <option value="inactive" <?= (($_POST['status'] ?? '') == 'inactive') ? 'selected' : '' ?>>Không hoạt động</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="sku">Mã SKU</label>
                            <input type="text" id="sku" name="sku" 
                                   value="<?= htmlspecialchars($_POST['sku'] ?? '') ?>" 
                                   placeholder="DATA-001">
                        </div>

                        <div class="form-group">
                            <label for="expiry_days">Số ngày hết hạn</label>
                            <input type="number" id="expiry_days" name="expiry_days" 
                                   value="<?= htmlspecialchars($_POST['expiry_days'] ?? '30') ?>" 
                                   placeholder="30" min="1">
                            <small>Số ngày sản phẩm có hiệu lực sau khi mua</small>
                        </div>

                        <div class="form-group">
                            <label for="featured">Nổi bật</label>
                            <div class="checkbox-wrapper">
                                <input type="checkbox" id="featured" name="featured" value="1" 
                                       <?= (($_POST['featured'] ?? '') == '1') ? 'checked' : '' ?>>
                                <label for="featured">Hiển thị trang chủ</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="short_description">Mô tả ngắn</label>
                        <textarea id="short_description" name="short_description" rows="2" 
                                  placeholder="Mô tả ngắn gọn về data (tối đa 200 ký tự)"><?= htmlspecialchars($_POST['short_description'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="description" class="required">Mô tả chi tiết</label>
                        <textarea id="description" name="description" rows="6" 
                                  placeholder="Nhập mô tả chi tiết về data nguồn hàng..." required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Tab 2: Thông Tin Data -->
                <div class="tab-pane" id="tab-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="record_count">Số lượng Record</label>
                            <input type="number" id="record_count" name="record_count" 
                                   value="<?= htmlspecialchars($_POST['record_count'] ?? '100') ?>" 
                                   placeholder="100" min="0">
                            <small>Số lượng thông tin trong data</small>
                        </div>

                        <div class="form-group">
                            <label for="data_size">Dung lượng Data</label>
                            <input type="text" id="data_size" name="data_size" 
                                   value="<?= htmlspecialchars($_POST['data_size'] ?? '') ?>" 
                                   placeholder="15 KB">
                            <small>Ví dụ: 15 KB, 2 MB</small>
                        </div>

                        <div class="form-group">
                            <label for="data_format">Định dạng File</label>
                            <input type="text" id="data_format" name="data_format" 
                                   value="<?= htmlspecialchars($_POST['data_format'] ?? '') ?>" 
                                   placeholder="Excel, CSV, JSON">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="data_source">Nguồn Gốc</label>
                            <input type="text" id="data_source" name="data_source" 
                                   value="<?= htmlspecialchars($_POST['data_source'] ?? '') ?>" 
                                   placeholder="Việt Nam, Trung Quốc...">
                        </div>

                        <div class="form-group">
                            <label for="reliability">Độ Tin Cậy</label>
                            <input type="text" id="reliability" name="reliability" 
                                   value="<?= htmlspecialchars($_POST['reliability'] ?? '') ?>" 
                                   placeholder="90%">
                            <small>Tỷ lệ chính xác của data</small>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="quota">Số Lần Tải (Quota)</label>
                            <input type="number" id="quota" name="quota" 
                                   value="<?= htmlspecialchars($_POST['quota'] ?? '100') ?>" 
                                   placeholder="100" min="1">
                            <small>Số lần khách được tải data</small>
                        </div>

                        <div class="form-group">
                            <label for="quota_per_usage">Số Record Mỗi Lần Tải</label>
                            <input type="number" id="quota_per_usage" name="quota_per_usage" 
                                   value="<?= htmlspecialchars($_POST['quota_per_usage'] ?? '10') ?>" 
                                   placeholder="10" min="1">
                            <small>Số record được tải mỗi lần</small>
                        </div>
                    </div>
                </div>

                <!-- Tab 3: Nhà Cung Cấp -->
                <div class="tab-pane" id="tab-supplier">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="supplier_name">Tên Nhà Cung Cấp</label>
                            <input type="text" id="supplier_name" name="supplier_name" 
                                   value="<?= htmlspecialchars($_POST['supplier_name'] ?? '') ?>" 
                                   placeholder="Công ty TNHH Data Logistics VN">
                        </div>

                        <div class="form-group">
                            <label for="supplier_title">Chức Danh</label>
                            <input type="text" id="supplier_title" name="supplier_title" 
                                   value="<?= htmlspecialchars($_POST['supplier_title'] ?? '') ?>" 
                                   placeholder="Đối tác chiến lược">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="supplier_bio">Giới Thiệu</label>
                        <textarea id="supplier_bio" name="supplier_bio" rows="3" 
                                  placeholder="Giới thiệu về nhà cung cấp"><?= htmlspecialchars($_POST['supplier_bio'] ?? '') ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="supplier_avatar">Avatar URL</label>
                            <input type="url" id="supplier_avatar" name="supplier_avatar" 
                                   value="<?= htmlspecialchars($_POST['supplier_avatar'] ?? '') ?>" 
                                   placeholder="https://...">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Liên Hệ</label>
                        <p class="text-muted" style="font-size:13px;margin-bottom:12px;">Thêm các thông tin liên hệ như website, hotline, zalo...</p>
                        
                        <div id="supplier-social-container">
                            <!-- Social contacts will be added here dynamically -->
                        </div>
                        
                        <button type="button" class="btn btn-sm btn-success" onclick="addSupplierSocial()" style="margin-top:8px;">
                            <i class="fas fa-plus"></i> Thêm liên hệ
                        </button>
                        
                        <!-- Hidden input for form submission -->
                        <input type="hidden" id="supplier_social" name="supplier_social" value="">
                    </div>
                </div>

                <!-- Tab 4: Lợi Ích -->
                <div class="tab-pane" id="tab-benefits">
                    <div class="form-group">
                        <label>Danh sách lợi ích</label>
                        <p class="text-muted" style="font-size:13px;margin-bottom:12px;">Thêm các lợi ích của sản phẩm. Click nút "+" để thêm dòng mới.</p>
                        
                        <div id="benefits-container">
                            <!-- Benefits will be added here dynamically -->
                        </div>
                        
                        <button type="button" class="btn btn-sm btn-success" onclick="addBenefit()" style="margin-top:8px;">
                            <i class="fas fa-plus"></i> Thêm lợi ích
                        </button>
                        
                        <!-- Hidden textarea for form submission -->
                        <input type="hidden" id="benefits" name="benefits" value="">
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
                            <!-- Data structure groups will be added here dynamically -->
                        </div>
                        
                        <button type="button" class="btn btn-sm btn-success" onclick="addStructureGroup()" style="margin-top:8px;">
                            <i class="fas fa-plus"></i> Thêm nhóm
                        </button>
                        
                        <!-- Hidden textarea for form submission -->
                        <input type="hidden" id="data_structure" name="data_structure" value="">
                    </div>
                    <div class="structure-preview" id="dataStructurePreview" style="margin-top:12px;padding:12px;background:#f8f9fa;border-radius:8px;min-height:60px;">
                        <p class="preview-empty" style="color:#999;margin:0;">Xem trước cấu trúc sẽ hiển thị ở đây...</p>
                    </div>
                </div>

                <!-- Tab 6: Hình Ảnh -->
                <div class="tab-pane" id="tab-image">
                    <div class="form-group">
                        <label for="image_file">Upload Ảnh</label>
                        <div style="border:2px dashed #d1d5db;border-radius:8px;padding:32px;text-align:center;cursor:pointer;transition:border-color 0.3s;" 
                             onclick="document.getElementById('image_file').click()" id="uploadZone">
                            <i class="fas fa-cloud-upload-alt" style="font-size:2.5rem;color:#9ca3af;margin-bottom:12px;display:block;"></i>
                            <p style="margin:0;color:#6b7280;font-weight:500;">Nhấp để chọn ảnh hoặc kéo thả vào đây</p>
                            <p style="margin:4px 0 0;font-size:12px;color:#9ca3af;">JPG, PNG, GIF, WebP — Tối đa 5MB</p>
                        </div>
                        <input type="file" id="image_file" name="image_file" accept="image/*" style="display:none;"
                               onchange="previewUploadedImage(this)">
                        <div id="imagePreview" style="margin-top:12px;display:none;">
                            <img id="previewImg" src="" alt="Preview" style="max-width:300px;max-height:200px;border-radius:8px;object-fit:cover;border:1px solid #ddd;">
                            <p style="margin:4px 0 0;font-size:12px;color:#10B981;"><i class="fas fa-check-circle"></i> Ảnh đã chọn — sẽ được upload khi lưu</p>
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

                <!-- Tab 7: SEO -->
                <div class="tab-pane" id="tab-seo">
                    <div class="form-group">
                        <label for="meta_title">Tiêu Đề SEO</label>
                        <input type="text" id="meta_title" name="meta_title" 
                               value="<?= htmlspecialchars($_POST['meta_title'] ?? '') ?>" 
                               placeholder="Tiêu đề tối ưu cho SEO">
                        <small>Tối đa 60 ký tự</small>
                    </div>

                    <div class="form-group">
                        <label for="meta_description">Mô Tả SEO</label>
                        <textarea id="meta_description" name="meta_description" rows="3" 
                                  placeholder="Mô tả ngắn gọn cho SEO"><?= htmlspecialchars($_POST['meta_description'] ?? '') ?></textarea>
                        <small>Tối đa 160 ký tự</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                Lưu Data
            </button>
            <button type="reset" class="btn btn-secondary">
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

// Initialize with existing benefits if any
(function() {
    var existingBenefits = '';
    try {
        existingBenefits = document.querySelector('input[name="benefits"]').value || '';
    } catch(e) {}
    
    if (existingBenefits) {
        try {
            var benefits = JSON.parse(existingBenefits);
            benefits.forEach(function(b) { addBenefit(b); });
        } catch(e) { addBenefit(''); }
    } else {
        addBenefit(''); // Add one empty row by default
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
            <button type="button" onclick="addStructureItem('${groupId}')" style="padding:6px 12px;background:#eff6ff;border:1px solid #bfdbfe;color:#2563eb;border-radius:6px;cursor:pointer;font-size:13px;">
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

function addStructureItem(groupId) {
    var group = document.querySelector('.structure-group');
    var containers = document.querySelectorAll('.group-items');
    // Find the last group items container
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
    // If no items left, add one empty
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

// Initialize with existing data structure if any
(function() {
    var existingStructure = '';
    try {
        existingStructure = document.querySelector('input[name="data_structure"]').value || '';
    } catch(e) {}
    
    if (existingStructure) {
        try {
            var structure = JSON.parse(existingStructure);
            structure.forEach(function(group) { 
                addStructureGroup(group.title, group.items || []); 
            });
        } catch(e) { addStructureGroup('', []); }
    } else {
        addStructureGroup('', []); // Add one empty group by default
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

// Initialize with existing supplier_social if any
(function() {
    var existingSocial = '';
    try {
        existingSocial = document.querySelector('input[name="supplier_social"]').value || '';
    } catch(e) {}
    
    if (existingSocial) {
        try {
            var social = JSON.parse(existingSocial);
            Object.keys(social).forEach(function(key) { 
                addSupplierSocial(key, social[key]); 
            });
        } catch(e) { addSupplierSocial('', ''); }
    } else {
        addSupplierSocial('website', ''); // Add one empty row by default
    }
})();
</script>
