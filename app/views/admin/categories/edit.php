<?php
/**
 * Admin Categories Edit
 * Sử dụng AdminService thông qua ServiceManager
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Khởi tạo View & ServiceManager
require_once __DIR__ . '/../../../../core/view_init.php';

// Chọn service admin (được inject từ index.php)
$service = isset($currentService) ? $currentService : ($adminService ?? null);

try {
    // Get category ID from URL
    $category_id = (int)($_GET['id'] ?? 0);

    if (!$category_id) {
        header('Location: ?page=admin&module=categories&error=invalid_id');
        exit;
    }

    // Get category data using AdminService
    $categoryData = $service->getCategoryDetailsData($category_id);
    $category = $categoryData['category'];

    // Redirect if category not found
    if (!$category) {
        header('Location: ?page=admin&module=categories&error=not_found');
        exit;
    }

    // Lấy tất cả danh mục active để tính vị trí hiện tại
    require_once __DIR__ . '/../../../models/CategoriesModel.php';
    $categoriesModel = new CategoriesModel();
    $allCategories = $categoriesModel->getActive();
    $totalCategories = count($allCategories);

    // Tính vị trí hiện tại của danh mục (sắp xếp theo sort_order)
    usort($allCategories, function($a, $b) {
        return ($a['sort_order'] ?? 0) - ($b['sort_order'] ?? 0);
    });

    $currentPosition = 1;
    foreach ($allCategories as $index => $cat) {
        if ($cat['id'] == $category_id) {
            $currentPosition = $index + 1;
            break;
        }
    }

} catch (Exception $e) {
    $errorHandler->logError('Admin Categories Edit View Error', $e);
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
    
    // If no errors, update database
    if (empty($errors)) {
        // Prepare update data - preserve existing image if not uploading new one
        // DEBUG: Log POST data
        error_log("DEBUG EDIT CATEGORY - POST parent_id: " . var_export($_POST['parent_id'] ?? 'NOT SET', true));
        
        $parent_id_processed = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
        error_log("DEBUG EDIT CATEGORY - Processed parent_id: " . var_export($parent_id_processed, true));
        
        $updateData = [
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'status' => $status,
            'parent_id' => $parent_id_processed,
            'icon' => $_POST['icon'] ?? '',
            'meta_title' => $_POST['meta_title'] ?? '',
            'meta_description' => $_POST['meta_description'] ?? '',
            'keywords' => $_POST['keywords'] ?? '',
            'sort_order' => (int)($_POST['sort_order'] ?? 1) - 1, // Convert 1-indexed position to 0-indexed sort_order
            'featured' => isset($_POST['featured']) ? 1 : 0,
            'show_in_filter' => isset($_POST['show_in_filter']) ? 1 : 0
        ];

        // Handle custom SVG icon upload
        if (isset($_FILES['icon_svg']) && $_FILES['icon_svg']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['icon_svg'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if ($ext === 'svg') {
                $uploadDir = dirname(__DIR__, 4) . '/assets/uploads/categories/icons/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $fileName = 'icon_' . time() . '_' . uniqid() . '.svg';
                $targetPath = $uploadDir . $fileName;
                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    $updateData['icon'] = '/assets/uploads/categories/icons/' . $fileName;
                }
            } else {
                $errors[] = 'Chỉ chấp nhận định dạng file .svg cho icon tùy chỉnh';
            }
        } elseif (trim($_POST['icon'] ?? '') === '[Tải lên SVG]') {
            $updateData['icon'] = $category['icon'] ?? '';
        }
        
        // Only update image if a new file was uploaded
        if (!empty($_FILES['image']['name'])) {
            // Handle image upload
            $uploadDir = dirname(__DIR__, 4) . '/assets/uploads/categories/';
            $uploadUrl = '/assets/uploads/categories/';
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
                $updateData['image'] = '/assets/uploads/categories/' . $fileName;
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
        
        $updated = $service->updateCategory($category_id, $updateData);
        
        if ($updated) {
            // Use PRG pattern - redirect after successful POST
            if (!headers_sent($filename, $linenum)) {
                header('Location: ?page=admin&module=categories&action=view&id=' . $category_id . '&updated=1');
                exit;
            } else {
                // Fallback: if headers sent, use JavaScript redirect
                ?>
                <script>
                window.location.href = "?page=admin&module=categories&action=view&id=<?= $category_id ?>&updated=1";
                </script>
                <div style="padding:20px;text-align:center;">
                    <p>Đang chuyển hướng...</p>
                    <a href="?page=admin&module=categories&action=view&id=<?= $category_id ?>&updated=1">Nhấn vào đây nếu không tự chuyển</a>
                </div>
                <?php
                exit;
            }
        } else {
            $errors[] = 'Không thể cập nhật danh mục';
        }
    }
} else {
    // Pre-fill form with existing data
    $_POST = [
        'name' => $category['name'],
        'slug' => $category['slug'],
        'description' => $category['description'],
        'status' => $category['status'],
        'parent_id' => $category['parent_id'] ?? null,
        'icon' => $category['icon'] ?? '',
        'meta_title' => $category['meta_title'] ?? '',
        'meta_description' => $category['meta_description'] ?? '',
        'keywords' => $category['keywords'] ?? '',
        'sort_order' => ($category['sort_order'] ?? 0) + 1, // Convert 0-indexed to 1-indexed for display
        'featured' => $category['featured'] ?? 0,
        'show_in_filter' => $category['show_in_filter'] ?? 1
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
            Cập nhật danh mục thành công!
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

                        <div class="form-row">
                            <div class="form-group col-6">
                                <label for="parent_id">Danh mục cha</label>
                                <select id="parent_id" name="parent_id">
                                    <option value="">-- Không có (Danh mục gốc) --</option>
                                    <?php
                                    // Lấy danh sách danh mục cho dropdown
                                    $categoriesModel = new CategoriesModel();
                                    $allCategoriesForLevels = $categoriesModel->getActive();

                                    // Lấy tất cả ID con cháu của danh mục hiện tại (để loại trừ khỏi lựa chọn cha)
                                    $descendantIds = [];
                                    foreach ($allCategoriesForLevels as $cat) {
                                        if ($cat['parent_id'] == $category['id']) {
                                            // Đệ quy lấy tất cả con cháu
                                            $descendantIds[] = $cat['id'];
                                            $stack = [$cat['id']];
                                            while (!empty($stack)) {
                                                $currentId = array_pop($stack);
                                                foreach ($allCategoriesForLevels as $child) {
                                                    if ($child['parent_id'] == $currentId) {
                                                        $descendantIds[] = $child['id'];
                                                        $stack[] = $child['id'];
                                                    }
                                                }
                                            }
                                        }
                                    }

                                    // Lọc: chỉ lấy danh mục active, loại trừ chính nó và tất cả con cháu của nó
                                    $parentCategories = [];
                                    foreach ($allCategoriesForLevels as $p) {
                                        if ($p['id'] != $category['id'] && !in_array($p['id'], $descendantIds)) {
                                            $parentCategories[] = $p;
                                        }
                                    }

                                    // Tính cấp độ cho từng danh mục
                                    $categoryLevels = [];
                                    $maxLevelDepth = 10;

                                    foreach ($parentCategories as $p) {
                                        $level = 1;
                                        $currentParentId = $p['parent_id'] ?? null;
                                        $visitedIds = [$p['id']];

                                        while ($currentParentId && $level < $maxLevelDepth) {
                                            if (in_array($currentParentId, $visitedIds)) {
                                                error_log("Circular reference detected in category hierarchy at category ID: {$p['id']}");
                                                break;
                                            }
                                            $visitedIds[] = $currentParentId;
                                            $level++;
                                            $found = false;
                                            foreach ($allCategoriesForLevels as $check) {
                                                if ($check['id'] == $currentParentId) {
                                                    $currentParentId = $check['parent_id'] ?? null;
                                                    $found = true;
                                                    break;
                                                }
                                            }
                                            if (!$found) break;
                                        }
                                        $categoryLevels[$p['id']] = $level;
                                    }

                                    $selectedParent = $_POST['parent_id'] ?? '';
                                    foreach ($parentCategories as $parent):
                                        $level = $categoryLevels[$parent['id']] ?? 1;
                                    ?>
                                        <option value="<?= $parent['id'] ?>" <?= ($selectedParent == $parent['id']) ? 'selected' : '' ?>>
                                            <?= str_repeat('— ', $level - 1) ?><?= htmlspecialchars($parent['name']) ?> <small style="color:#999">(cấp <?= $level ?>)</small>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small>Chọn danh mục cha. Không hiển thị danh mục con của danh mục này để tránh vòng lặp.</small>
                            </div>

                            <div class="form-group col-6">
                                <label for="icon">Icon danh mục (FontAwesome hoặc Tải lên SVG)</label>
                                <div class="icon-picker-container">
                                    <div class="icon-picker-input-group" style="display: flex; gap: 8px;">
                                        <div class="icon-preview-box" id="icon-preview-box" style="display: flex; align-items: center; justify-content: center; width: 45px; height: 45px; border: 1px solid #d1d5db; border-radius: 6px; background: #f9fafb; color: #356df1; font-size: 1.25rem; flex-shrink: 0;">
                                            <?php
                                            $currentIcon = $_POST['icon'] ?? $category['icon'] ?? 'fas fa-folder';
                                            if (strpos($currentIcon, '.svg') !== false || strpos($currentIcon, '/') !== false): ?>
                                                <img src="<?= htmlspecialchars($currentIcon) ?>" id="svg-preview-img" style="width: 24px; height: 24px; object-fit: contain;">
                                            <?php else: ?>
                                                <i class="<?= htmlspecialchars($currentIcon ?: 'fas fa-folder') ?>"></i>
                                            <?php endif; ?>
                                        </div>
                                        <input type="text" id="icon" name="icon" value="<?= htmlspecialchars($_POST['icon'] ?? $category['icon'] ?? 'fas fa-folder') ?>" placeholder="Ví dụ: fas fa-laptop" style="flex: 1; border: 1px solid #d1d5db; border-radius: 6px; padding: 0 12px;">
                                    </div>

                                    <!-- Custom SVG Upload Input & Buttons -->
                                    <div class="svg-upload-section" style="margin-top: 10px; display: flex; align-items: center; gap: 10px;">
                                        <input type="file" id="icon_svg" name="icon_svg" accept=".svg" style="display: none;">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('icon_svg').click();" style="display: flex; align-items: center; gap: 6px; padding: 6px 12px; font-size: 0.85rem; border: 1px solid #d1d5db; border-radius: 4px; background: #fff; cursor: pointer; transition: all 0.2s;">
                                            <i class="fas fa-upload" style="font-size: 0.85rem; color: #4b5563;"></i> Tải lên icon SVG
                                        </button>
                                        <span id="svg-file-name" style="font-size: 0.85rem; color: #6b7280; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            <?php
                                            if (strpos($currentIcon, '.svg') !== false) {
                                                echo basename($currentIcon);
                                            } else {
                                                echo 'Chưa có file nào được chọn';
                                            }
                                            ?>
                                        </span>
                                        <button type="button" id="remove-svg-btn" class="btn btn-sm btn-outline-danger" style="<?= strpos($currentIcon, '.svg') !== false ? 'display: inline-block;' : 'display: none;' ?> padding: 2px 8px; font-size: 0.75rem; border: 1px solid #ef4444; border-radius: 4px; background: #fff; color: #ef4444; cursor: pointer;">Xóa SVG</button>
                                    </div>
                                    <div class="icon-grid-selector" style="margin-top: 10px; padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px; background: #fff; max-height: 150px; overflow-y: auto;">
                                        <small style="display: block; margin-bottom: 8px; color: #6b7280; font-weight: 500;">Chọn nhanh từ bộ icon mẫu (FontAwesome):</small>
                                        <div class="icon-grid" style="display: grid; grid-template-columns: repeat(10, 1fr); gap: 8px;">
                                            <?php
                                            $sampleIcons = [
                                                 // Folders & Documents
                                                 'fas fa-folder', 'fas fa-folder-open', 'fas fa-file', 'fas fa-book', 'fas fa-bookmark', 'fas fa-box', 'fas fa-boxes', 'fas fa-archive',
                                                 // Tech & Electronics
                                                 'fas fa-laptop', 'fas fa-desktop', 'fas fa-mobile-alt', 'fas fa-tablet-alt', 'fas fa-headphones', 'fas fa-camera', 'fas fa-gamepad', 'fas fa-tv', 'fas fa-plug', 'fas fa-mouse',
                                                 // Shopping & Business
                                                 'fas fa-shopping-cart', 'fas fa-shopping-bag', 'fas fa-store', 'fas fa-gift', 'fas fa-tags', 'fas fa-wallet', 'fas fa-credit-card', 'fas fa-briefcase', 'fas fa-chart-pie', 'fas fa-chart-line', 'fas fa-percent', 'fas fa-calculator', 'fas fa-coins',
                                                 // Clothes & Fashion
                                                 'fas fa-tshirt', 'fas fa-socks', 'fas fa-glasses', 'fas fa-gem', 'fas fa-cut', 'fas fa-ribbon', 'fas fa-crown', 'fas fa-graduation-cap',
                                                 // Tools & Home
                                                 'fas fa-wrench', 'fas fa-key', 'fas fa-lock', 'fas fa-home', 'fas fa-building', 'fas fa-bed', 'fas fa-bath', 'fas fa-couch', 'fas fa-lightbulb', 'fas fa-chair', 'fas fa-tools', 'fas fa-hammer',
                                                 // Transportation & Travel
                                                 'fas fa-truck', 'fas fa-plane', 'fas fa-car', 'fas fa-bicycle', 'fas fa-globe', 'fas fa-map-marker-alt', 'fas fa-ship', 'fas fa-train', 'fas fa-hotel', 'fas fa-compass',
                                                 // Food & Drinks
                                                 'fas fa-utensils', 'fas fa-mug-hot', 'fas fa-glass-martini-alt', 'fas fa-hamburger', 'fas fa-ice-cream', 'fas fa-pizza-slice', 'fas fa-apple-alt', 'fas fa-coffee', 'fas fa-beer',
                                                 // Misc & Dynamic
                                                 'fas fa-bell', 'fas fa-calendar-alt', 'fas fa-clock', 'fas fa-image', 'fas fa-video', 'fas fa-music', 'fas fa-heart', 'fas fa-star', 'fas fa-smile', 'fas fa-thumbs-up', 'fas fa-user', 'fas fa-users', 'fas fa-phone', 'fas fa-envelope', 'fas fa-search', 'fas fa-check', 'fas fa-cog'
                                            ];
                                            $selectedIcon = $_POST['icon'] ?? $category['icon'] ?? 'fas fa-folder';
                                            foreach ($sampleIcons as $ico):
                                            ?>
                                                <button type="button" class="icon-select-btn <?= $selectedIcon === $ico ? 'active' : '' ?>" data-icon="<?= $ico ?>" title="<?= $ico ?>" style="display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; border: 1px solid #e5e7eb; border-radius: 4px; background: #fff; cursor: pointer; transition: all 0.2s; font-size: 1rem; color: #4b5563;">
                                                    <i class="<?= $ico ?>"></i>
                                                </button>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <style>
                        .icon-select-btn:hover {
                            border-color: #356df1 !important;
                            background-color: #eef4ff !important;
                            color: #356df1 !important;
                        }
                        .icon-select-btn.active {
                            border-color: #356df1 !important;
                            background-color: #356df1 !important;
                            color: #fff !important;
                        }
                        </style>

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
                                <?php if (!empty($category['image'])): ?>
                                    <img src="<?= htmlspecialchars($category['image']) ?>" alt="Current image" id="currentImage">
                                    <div class="image-overlay">
                                        <span><i class="fas fa-camera"></i> Click để thay đổi ảnh</span>
                                    </div>
                                <?php else: ?>
                                    <div class="image-placeholder">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <span>Click để tải ảnh lên</span>
                                        <small>Hoặc kéo thả ảnh vào đây</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <input type="file" id="image" name="image" accept="image/*" style="display:none;" onchange="previewUploadedImage(this)">

                            <div class="image-input-group">
                                <label>Hoặc nhập URL ảnh</label>
                                <input type="text" id="image_url" name="image_url"
                                       value="<?= htmlspecialchars($category['image'] ?? '') ?>"
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
                            // Xác định giá trị checkbox: nếu đang POST (có lỗi) thì dùng isset, nếu load lần đầu thì dùng từ database
                            $isPost = ($_SERVER['REQUEST_METHOD'] === 'POST');
                            $showInFilter = $isPost ? (isset($_POST['show_in_filter']) ? 1 : 0) : ($_POST['show_in_filter'] ?? 1);
                            $featured = $isPost ? (isset($_POST['featured']) ? 1 : 0) : ($_POST['featured'] ?? 0);
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

function previewUploadedImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var imagePreview = document.getElementById('imagePreview');
            imagePreview.innerHTML = '<img src="' + e.target.result + '" alt="New image">' +
                '<div class="image-overlay"><span><i class="fas fa-camera"></i> Click để thay đổi ảnh</span></div>';
            document.getElementById('image_url').value = '';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeImage() {
    if (confirm('Bạn có chắc chắn muốn xóa ảnh này?')) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '?page=admin&module=categories&action=edit&id=<?= $category_id ?>', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                window.location.reload();
            }
        };
        xhr.send('remove_image=1');
    }
}

// Icon picker logic
document.querySelectorAll('.icon-select-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.icon-select-btn').forEach(function(b) {
            b.classList.remove('active');
        });
        
        this.classList.add('active');
        
        var iconClass = this.getAttribute('data-icon');
        
        // Reset SVG upload
        document.getElementById('icon_svg').value = '';
        document.getElementById('svg-file-name').textContent = 'Chưa có file nào được chọn';
        document.getElementById('remove-svg-btn').style.display = 'none';
        
        var iconInput = document.getElementById('icon');
        iconInput.value = iconClass;
        iconInput.removeAttribute('readonly');
        
        var previewBox = document.getElementById('icon-preview-box');
        previewBox.innerHTML = '<i class="' + iconClass + '"></i>';
    });
});

document.getElementById('icon').addEventListener('input', function() {
    var val = this.value.trim() || 'fas fa-folder';
    
    // If not a path (doesn't contain slash or .svg), preview it as class
    if (val.indexOf('/') === -1 && val.indexOf('.svg') === -1) {
        var previewBox = document.getElementById('icon-preview-box');
        previewBox.innerHTML = '<i class="' + val + '"></i>';
    }
    
    document.querySelectorAll('.icon-select-btn').forEach(function(b) {
        if (b.getAttribute('data-icon') === val) {
            b.classList.add('active');
        } else {
            b.classList.remove('active');
        }
    });
});

// Custom SVG Icon Upload logic
document.getElementById('icon_svg').addEventListener('change', function(e) {
    var file = e.target.files[0];
    if (file) {
        if (file.name.slice(-4).toLowerCase() !== '.svg') {
            alert('Chỉ chấp nhận định dạng file .svg!');
            this.value = '';
            return;
        }
        
        document.getElementById('svg-file-name').textContent = file.name;
        document.getElementById('remove-svg-btn').style.display = 'inline-block';
        
        var reader = new FileReader();
        reader.onload = function(e) {
            var previewBox = document.getElementById('icon-preview-box');
            previewBox.innerHTML = '<img src="' + e.target.result + '" id="svg-preview-img" style="width: 24px; height: 24px; object-fit: contain;">';
        };
        reader.readAsDataURL(file);
        
        // Clear active buttons
        document.querySelectorAll('.icon-select-btn').forEach(function(b) {
            b.classList.remove('active');
        });
        
        var iconInput = document.getElementById('icon');
        iconInput.value = '[Tải lên SVG]';
        iconInput.setAttribute('readonly', 'readonly');
    }
});

document.getElementById('remove-svg-btn').addEventListener('click', function() {
    document.getElementById('icon_svg').value = '';
    document.getElementById('svg-file-name').textContent = 'Chưa có file nào được chọn';
    this.style.display = 'none';
    
    var iconInput = document.getElementById('icon');
    iconInput.value = 'fas fa-folder';
    iconInput.removeAttribute('readonly');
    
    var previewBox = document.getElementById('icon-preview-box');
    previewBox.innerHTML = '<i class="fas fa-folder"></i>';
    
    // Highlight the folder button
    document.querySelectorAll('.icon-select-btn').forEach(function(b) {
        if (b.getAttribute('data-icon') === 'fas fa-folder') {
            b.classList.add('active');
        } else {
            b.classList.remove('active');
        }
    });
});
</script>

