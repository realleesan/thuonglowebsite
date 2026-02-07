<?php
// Handle form submission (demo)
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation
    $key = trim($_POST['key'] ?? '');
    $value = trim($_POST['value'] ?? '');
    $type = $_POST['type'] ?? 'text';
    $description = trim($_POST['description'] ?? '');
    
    if (empty($key)) {
        $errors[] = 'Tên cài đặt không được để trống';
    } elseif (!preg_match('/^[a-z0-9_]+$/', $key)) {
        $errors[] = 'Tên cài đặt chỉ được chứa chữ thường, số và dấu gạch dưới';
    }
    
    if (empty($value)) {
        $errors[] = 'Giá trị không được để trống';
    }
    
    if (empty($description)) {
        $errors[] = 'Mô tả không được để trống';
    }
    
    // Validate based on type
    switch ($type) {
        case 'email':
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Giá trị phải là email hợp lệ';
            }
            break;
        case 'url':
            if (!filter_var($value, FILTER_VALIDATE_URL)) {
                $errors[] = 'Giá trị phải là URL hợp lệ';
            }
            break;
        case 'number':
            if (!is_numeric($value)) {
                $errors[] = 'Giá trị phải là số';
            }
            break;
        case 'boolean':
            if (!in_array($value, ['0', '1', 'true', 'false'])) {
                $errors[] = 'Giá trị boolean phải là 0, 1, true hoặc false';
            }
            break;
    }
    
    // If no errors, simulate save (demo)
    if (empty($errors)) {
        $success = true;
        // In real app: save to database
        // header('Location: ?page=admin&module=settings&success=added');
        // exit;
    }
}

// Setting types
$setting_types = [
    'text' => 'Văn bản',
    'textarea' => 'Văn bản dài',
    'email' => 'Email',
    'url' => 'URL',
    'number' => 'Số',
    'boolean' => 'Đúng/Sai',
    'select' => 'Lựa chọn',
    'file' => 'Tệp tin'
];
?>

<div class="settings-page settings-add-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-plus"></i>
                Thêm Cài Đặt Mới
            </h1>
            <p class="page-description">Thêm cài đặt mới vào hệ thống</p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=settings" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Quay lại danh sách
            </a>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            Thêm cài đặt thành công! (Demo - dữ liệu không được lưu thật)
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

    <!-- Add Setting Form -->
    <div class="form-container">
        <form method="POST" class="admin-form">
            <div class="form-grid">
                <!-- Left Column -->
                <div class="form-column">
                    <div class="form-section">
                        <h3 class="section-title">Thông Tin Cài Đặt</h3>
                        
                        <div class="form-group">
                            <label for="key" class="required">Tên cài đặt (Key)</label>
                            <input type="text" id="key" name="key" 
                                   value="<?= htmlspecialchars($_POST['key'] ?? '') ?>" 
                                   placeholder="site_name, contact_email..." required>
                            <small>Chỉ sử dụng chữ thường, số và dấu gạch dưới (_)</small>
                        </div>

                        <div class="form-group">
                            <label for="description" class="required">Mô tả</label>
                            <input type="text" id="description" name="description" 
                                   value="<?= htmlspecialchars($_POST['description'] ?? '') ?>" 
                                   placeholder="Mô tả ngắn gọn về cài đặt này" required>
                        </div>

                        <div class="form-group">
                            <label for="type" class="required">Loại dữ liệu</label>
                            <select id="type" name="type" required onchange="updateValueField()">
                                <?php foreach ($setting_types as $type_key => $type_label): ?>
                                    <option value="<?= $type_key ?>" 
                                            <?= (($_POST['type'] ?? 'text') == $type_key) ? 'selected' : '' ?>>
                                        <?= $type_label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="form-column">
                    <div class="form-section">
                        <h3 class="section-title">Giá Trị</h3>
                        
                        <div class="form-group" id="value-group">
                            <label for="value" class="required">Giá trị</label>
                            
                            <!-- Text input (default) -->
                            <input type="text" id="value" name="value" 
                                   value="<?= htmlspecialchars($_POST['value'] ?? '') ?>" 
                                   placeholder="Nhập giá trị..." required>
                            
                            <!-- Textarea (hidden by default) -->
                            <textarea id="value-textarea" name="value" rows="5" 
                                      placeholder="Nhập giá trị..." style="display: none;" required><?= htmlspecialchars($_POST['value'] ?? '') ?></textarea>
                            
                            <!-- Boolean select (hidden by default) -->
                            <select id="value-boolean" name="value" style="display: none;" required>
                                <option value="1" <?= (($_POST['value'] ?? '') == '1') ? 'selected' : '' ?>>Có (True)</option>
                                <option value="0" <?= (($_POST['value'] ?? '') == '0') ? 'selected' : '' ?>>Không (False)</option>
                            </select>
                            
                            <div id="value-help" class="form-help">
                                <small id="value-help-text">Nhập giá trị cho cài đặt</small>
                            </div>
                        </div>

                        <!-- Examples based on type -->
                        <div class="form-group">
                            <div class="setting-examples">
                                <h4>Ví dụ theo loại:</h4>
                                <div class="example-list">
                                    <div class="example-item" data-type="text">
                                        <strong>Văn bản:</strong> ThuongLo Website
                                    </div>
                                    <div class="example-item" data-type="textarea">
                                        <strong>Văn bản dài:</strong> Mô tả chi tiết về website...
                                    </div>
                                    <div class="example-item" data-type="email">
                                        <strong>Email:</strong> admin@thuonglo.com
                                    </div>
                                    <div class="example-item" data-type="url">
                                        <strong>URL:</strong> https://thuonglo.com
                                    </div>
                                    <div class="example-item" data-type="number">
                                        <strong>Số:</strong> 100, 15.5, -20
                                    </div>
                                    <div class="example-item" data-type="boolean">
                                        <strong>Đúng/Sai:</strong> Có hoặc Không
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Lưu Cài Đặt
                </button>
                <button type="button" class="btn btn-secondary" onclick="resetForm()">
                    <i class="fas fa-undo"></i>
                    Đặt lại
                </button>
                <a href="?page=admin&module=settings" class="btn btn-outline">
                    <i class="fas fa-times"></i>
                    Hủy
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function updateValueField() {
    const type = document.getElementById('type').value;
    const valueInput = document.getElementById('value');
    const valueTextarea = document.getElementById('value-textarea');
    const valueBoolean = document.getElementById('value-boolean');
    const helpText = document.getElementById('value-help-text');
    
    // Hide all inputs first
    valueInput.style.display = 'none';
    valueTextarea.style.display = 'none';
    valueBoolean.style.display = 'none';
    
    // Remove required from all
    valueInput.removeAttribute('required');
    valueTextarea.removeAttribute('required');
    valueBoolean.removeAttribute('required');
    
    // Show appropriate input and set required
    switch (type) {
        case 'textarea':
            valueTextarea.style.display = 'block';
            valueTextarea.setAttribute('required', 'required');
            helpText.textContent = 'Nhập văn bản dài, có thể xuống dòng';
            break;
        case 'boolean':
            valueBoolean.style.display = 'block';
            valueBoolean.setAttribute('required', 'required');
            helpText.textContent = 'Chọn Có (True) hoặc Không (False)';
            break;
        case 'email':
            valueInput.style.display = 'block';
            valueInput.setAttribute('required', 'required');
            valueInput.type = 'email';
            valueInput.placeholder = 'example@domain.com';
            helpText.textContent = 'Nhập địa chỉ email hợp lệ';
            break;
        case 'url':
            valueInput.style.display = 'block';
            valueInput.setAttribute('required', 'required');
            valueInput.type = 'url';
            valueInput.placeholder = 'https://example.com';
            helpText.textContent = 'Nhập URL đầy đủ (bao gồm http:// hoặc https://)';
            break;
        case 'number':
            valueInput.style.display = 'block';
            valueInput.setAttribute('required', 'required');
            valueInput.type = 'number';
            valueInput.placeholder = '0';
            helpText.textContent = 'Nhập số (có thể là số thập phân)';
            break;
        default:
            valueInput.style.display = 'block';
            valueInput.setAttribute('required', 'required');
            valueInput.type = 'text';
            valueInput.placeholder = 'Nhập giá trị...';
            helpText.textContent = 'Nhập giá trị văn bản';
            break;
    }
    
    // Show/hide examples
    const examples = document.querySelectorAll('.example-item');
    examples.forEach(example => {
        example.style.display = example.dataset.type === type ? 'block' : 'none';
    });
}

function resetForm() {
    if (confirm('Bạn có chắc chắn muốn đặt lại form?')) {
        document.querySelector('.admin-form').reset();
        updateValueField();
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateValueField();
});
</script>