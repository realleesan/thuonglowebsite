<?php
require_once __DIR__ . '/../../services/AdminService.php';
require_once __DIR__ . '/../../services/ErrorHandler.php';

try {
} catch (Exception $e) {
    $errorHandler->logError('Admin Events Add Error', $e);
    header('Location: ?page=admin&module=events&error=1');
    exit;
}

// Initialize form data
$form_data = [
    'title' => '',
    'slug' => '',
    'description' => '',
    'start_date' => '',
    'end_date' => '',
    'location' => '',
    'price' => 0,
    'max_participants' => 100,
    'status' => 'upcoming',
    'image' => ''
];

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data = array_merge($form_data, $_POST);
    
    // Validation
    if (empty($form_data['title'])) {
        $errors[] = 'Tên sự kiện không được để trống';
    }
    
    if (empty($form_data['slug'])) {
        $errors[] = 'Slug không được để trống';
    }
    
    if (empty($form_data['description'])) {
        $errors[] = 'Mô tả không được để trống';
    }
    
    if (empty($form_data['start_date'])) {
        $errors[] = 'Thời gian bắt đầu không được để trống';
    }
    
    if (empty($form_data['end_date'])) {
        $errors[] = 'Thời gian kết thúc không được để trống';
    }
    
    if (empty($form_data['location'])) {
        $errors[] = 'Địa điểm không được để trống';
    }
    
    if (!is_numeric($form_data['price']) || $form_data['price'] < 0) {
        $errors[] = 'Giá vé phải là số và không được âm';
    }
    
    if (!is_numeric($form_data['max_participants']) || $form_data['max_participants'] <= 0) {
        $errors[] = 'Số lượng tham gia tối đa phải là số dương';
    }
    
    // Check slug format
    if (!empty($form_data['slug']) && !preg_match('/^[a-z0-9-]+$/', $form_data['slug'])) {
        $errors[] = 'Slug chỉ được chứa chữ thường, số và dấu gạch ngang';
    }
    
    // Check date logic
    if (!empty($form_data['start_date']) && !empty($form_data['end_date'])) {
        $start = strtotime($form_data['start_date']);
        $end = strtotime($form_data['end_date']);
        if ($start >= $end) {
            $errors[] = 'Thời gian kết thúc phải sau thời gian bắt đầu';
        }
    }
    
    if (empty($errors)) {
        $success = true;
        // Trong thực tế sẽ lưu vào database
    }
}

// Generate slug from title
function generateSlug($title) {
    $slug = strtolower($title);
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug;
}
?>

<div class="events-add-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-plus"></i>
                Thêm Sự Kiện Mới
            </h1>
            <p class="page-description">Tạo sự kiện, workshop hoặc hội thảo mới</p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=events" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i>
                Quay lại danh sách
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <div>
                <strong>Thành công!</strong> Sự kiện đã được thêm thành công.
                <br><a href="?page=admin&module=events">Quay lại danh sách sự kiện</a>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                <strong>Có lỗi xảy ra:</strong>
                <ul class="error-list">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <!-- Form Container -->
    <div class="form-container">
        <form method="POST" class="admin-form" enctype="multipart/form-data">
            <div class="form-grid">
                <!-- Left Column -->
                <div class="form-column">
                    <!-- Basic Information -->
                    <div class="form-section">
                        <h3 class="section-title">Thông Tin Cơ Bản</h3>
                        
                        <div class="form-group">
                            <label for="title" class="required">Tên sự kiện:</label>
                            <input type="text" id="title" name="title" 
                                   value="<?= htmlspecialchars($form_data['title']) ?>" 
                                   placeholder="Nhập tên sự kiện..."
                                   onkeyup="generateSlugFromTitle()" required>
                            <small>Tên sự kiện sẽ hiển thị trên trang chủ và trang chi tiết</small>
                        </div>

                        <div class="form-group">
                            <label for="slug" class="required">Slug (URL thân thiện):</label>
                            <input type="text" id="slug" name="slug" 
                                   value="<?= htmlspecialchars($form_data['slug']) ?>" 
                                   placeholder="vi-du-slug-su-kien" required>
                            <small>Chỉ sử dụng chữ thường, số và dấu gạch ngang. VD: workshop-dropshipping-2024</small>
                        </div>

                        <div class="form-group">
                            <label for="description" class="required">Mô tả sự kiện:</label>
                            <textarea id="description" name="description" rows="6" 
                                      placeholder="Nhập mô tả chi tiết về sự kiện..." required><?= htmlspecialchars($form_data['description']) ?></textarea>
                            <small>Mô tả đầy đủ về nội dung, chương trình và lợi ích của sự kiện</small>
                        </div>
                    </div>

                    <!-- Event Details -->
                    <div class="form-section">
                        <h3 class="section-title">Chi Tiết Sự Kiện</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="start_date" class="required">Thời gian bắt đầu:</label>
                                <input type="datetime-local" id="start_date" name="start_date" 
                                       value="<?= htmlspecialchars($form_data['start_date']) ?>" required>
                                <small>Ngày và giờ bắt đầu sự kiện</small>
                            </div>

                            <div class="form-group">
                                <label for="end_date" class="required">Thời gian kết thúc:</label>
                                <input type="datetime-local" id="end_date" name="end_date" 
                                       value="<?= htmlspecialchars($form_data['end_date']) ?>" required>
                                <small>Ngày và giờ kết thúc sự kiện</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="location" class="required">Địa điểm:</label>
                            <input type="text" id="location" name="location" 
                                   value="<?= htmlspecialchars($form_data['location']) ?>" 
                                   placeholder="Nhập địa điểm tổ chức..." required>
                            <small>Địa chỉ cụ thể hoặc tên địa điểm tổ chức sự kiện</small>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="price">Giá vé (VNĐ):</label>
                                <input type="number" id="price" name="price" 
                                       value="<?= htmlspecialchars($form_data['price']) ?>" 
                                       placeholder="0" min="0" step="1000">
                                <small>Giá vé tham gia sự kiện (để 0 nếu miễn phí)</small>
                            </div>

                            <div class="form-group">
                                <label for="max_participants" class="required">Số lượng tối đa:</label>
                                <input type="number" id="max_participants" name="max_participants" 
                                       value="<?= htmlspecialchars($form_data['max_participants']) ?>" 
                                       placeholder="100" min="1" required>
                                <small>Số lượng người tham gia tối đa</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="form-column">
                    <!-- Event Status -->
                    <div class="form-section">
                        <h3 class="section-title">Trạng Thái Sự Kiện</h3>
                        
                        <div class="form-group">
                            <label for="status">Trạng thái:</label>
                            <select id="status" name="status">
                                <option value="upcoming" <?= $form_data['status'] == 'upcoming' ? 'selected' : '' ?>>Sắp diễn ra</option>
                                <option value="ongoing" <?= $form_data['status'] == 'ongoing' ? 'selected' : '' ?>>Đang diễn ra</option>
                                <option value="completed" <?= $form_data['status'] == 'completed' ? 'selected' : '' ?>>Đã kết thúc</option>
                                <option value="cancelled" <?= $form_data['status'] == 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                            </select>
                            <small>Trạng thái hiện tại của sự kiện</small>
                        </div>

                        <div class="form-group">
                            <label>Ngày tạo:</label>
                            <input type="text" value="<?= date('d/m/Y H:i') ?>" class="readonly" readonly>
                            <small>Thời gian tạo sự kiện</small>
                        </div>

                        <div class="form-group">
                            <label>Số người đăng ký:</label>
                            <input type="text" value="0" class="readonly" readonly>
                            <small>Số lượng người đã đăng ký tham gia</small>
                        </div>
                    </div>

                    <!-- Event Image -->
                    <div class="form-section">
                        <h3 class="section-title">Hình Ảnh Sự Kiện</h3>
                        
                        <div class="form-group">
                            <label for="image">Chọn hình ảnh:</label>
                            <div class="image-upload-container">
                                <div class="image-preview" onclick="document.getElementById('image').click()">
                                    <img id="preview-img" src="" alt="Preview" style="display: none;">
                                    <div id="preview-placeholder">
                                        <i class="fas fa-image"></i>
                                        <p>Click để chọn hình ảnh</p>
                                    </div>
                                </div>
                                <input type="file" id="image" name="image" class="image-input" 
                                       accept="image/*" onchange="previewImage(this)">
                                <div class="image-upload-info">
                                    <small>Định dạng: JPG, PNG, GIF. Kích thước tối đa: 2MB</small>
                                    <small>Kích thước khuyến nghị: 1200x800px</small>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($form_data['image'])): ?>
                            <div class="current-image-info">
                                <strong>Hình ảnh hiện tại:</strong><br>
                                <?= basename($form_data['image']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Additional Info -->
                    <div class="form-section">
                        <h3 class="section-title">Thông Tin Bổ Sung</h3>
                        
                        <div class="form-group">
                            <label for="organizer">Đơn vị tổ chức:</label>
                            <input type="text" id="organizer" name="organizer" 
                                   value="<?= htmlspecialchars($form_data['organizer'] ?? 'ThuongLo') ?>" 
                                   placeholder="Tên đơn vị tổ chức">
                            <small>Tên tổ chức hoặc công ty tổ chức sự kiện</small>
                        </div>

                        <div class="form-group">
                            <label for="contact_info">Thông tin liên hệ:</label>
                            <textarea id="contact_info" name="contact_info" rows="3" 
                                      placeholder="Email, số điện thoại, website..."><?= htmlspecialchars($form_data['contact_info'] ?? '') ?></textarea>
                            <small>Thông tin liên hệ để đăng ký hoặc hỏi đáp</small>
                        </div>

                        <div class="form-group">
                            <label for="requirements">Yêu cầu tham gia:</label>
                            <textarea id="requirements" name="requirements" rows="3" 
                                      placeholder="Các yêu cầu hoặc điều kiện tham gia..."><?= htmlspecialchars($form_data['requirements'] ?? '') ?></textarea>
                            <small>Điều kiện, yêu cầu hoặc chuẩn bị cần thiết</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Lưu Sự Kiện
                </button>
                <button type="button" class="btn btn-secondary" onclick="saveDraft()">
                    <i class="fas fa-clock"></i>
                    Lưu Nháp
                </button>
                <a href="?page=admin&module=events" class="btn btn-outline">
                    <i class="fas fa-times"></i>
                    Hủy
                </a>
            </div>
        </form>
    </div>
</div>

