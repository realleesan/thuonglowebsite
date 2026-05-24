<?php
/**
 * Edit CTA Section View - Enhanced Custom Editor
 */

// Ensure section data is available
if (!isset($section)) {
    $section = [];
}

// Set default values
$section = array_merge([
    'id' => 0,
    'title' => 'Trở thành một trong <span class="highlight">500+</span>',
    'subtitle' => 'Đại Lý Affiliate ThuongLo',
    'content' => 'Tham gia cùng chúng tôi và kiếm thu nhập thụ động từ việc giới thiệu dịch vụ thương mại xuyên biên giới hàng đầu Việt Nam',
    'button_text' => 'Đăng ký ngay',
    'button_url' => '?page=agent',
    'background_color' => '#ECEDEF',
    'image_url' => 'home/cta-final-1.png',
    'is_active' => 1
], $section);

// Get flash messages
$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);
$success = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_success']);
?>

<div class="hero-section-page hero-section-edit-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-bullhorn text-warning me-2"></i>
                Chỉnh sửa Section CTA (Call to Action)
            </h1>
            <p class="page-description">Tùy chỉnh tiêu đề, nội dung, hình ảnh, nút bấm và màu nền cho Section CTA ở cuối trang.</p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=homepage" class="btn-back">
                <i class="fas fa-arrow-left me-1"></i>
                Quản lý Trang chủ
            </a>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius: 8px;">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Thất bại!</strong> <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius: 8px;">
            <i class="fas fa-check-circle me-2"></i>
            <strong>Thành công!</strong> <?php echo htmlspecialchars($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="admin-form-full">
        <div class="admin-card card border-0 shadow-sm p-4">
            <form id="ctaSectionForm" method="POST" action="?page=admin&module=homepage&action=update-cta" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $section['id']; ?>">
                <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($section['image_url'] ?? ''); ?>">
                
                <div class="row">
                    <div class="col-md-8">
                        <!-- Title Field (Rich Text) -->
                        <div class="form-group mb-4">
                            <label class="admin-label fw-bold mb-2">Tiêu đề chính <span class="text-danger">*</span></label>
                            <p class="text-muted small mb-2">Ví dụ: <code>Trở thành một trong &lt;span class="highlight"&gt;500+&lt;/span&gt;</code></p>
                            
                            <!-- Custom Toolbar for Title -->
                            <div class="custom-editor-toolbar mb-2" data-for="title">
                                <div class="toolbar-group">
                                    <button type="button" class="btn-tool" onclick="applyFormat('bold', 'title')" title="In đậm"><i class="fas fa-bold"></i></button>
                                    <button type="button" class="btn-tool" onclick="applyFormat('italic', 'title')" title="In nghiêng"><i class="fas fa-italic"></i></button>
                                    <button type="button" class="btn-tool" onclick="applyFormat('underline', 'title')" title="Gạch chân"><i class="fas fa-underline"></i></button>
                                </div>
                                <div class="toolbar-group">
                                    <select onchange="applyStyle('fontFamily', this.value, 'title')" class="font-select form-select-sm border">
                                        <option value="">Font chữ</option>
                                        <option value="Arial, sans-serif">Arial</option>
                                        <option value="'Inter', sans-serif">Inter</option>
                                        <option value="'Roboto', sans-serif">Roboto</option>
                                        <option value="serif">Serif</option>
                                        <option value="monospace">Monospace</option>
                                    </select>
                                </div>
                                <div class="toolbar-group d-flex align-items-center">
                                    <div class="color-picker-wrapper me-2">
                                        <input type="color" onchange="applyStyle('color', this.value, 'title')" title="Màu chữ">
                                        <i class="fas fa-font"></i>
                                    </div>
                                    <button type="button" class="btn-tool" onclick="applyFormat('removeFormat', 'title')" title="Xóa định dạng"><i class="fas fa-eraser"></i></button>
                                </div>
                            </div>
                            
                            <!-- Editable Area for Title -->
                            <div id="editor-title" class="custom-editable-area form-control border p-3 bg-white" style="min-height: 100px; border-radius: 8px;" contenteditable="true" oninput="syncEditor('title')">
                                <?php echo $section['title']; ?>
                            </div>
                            <textarea id="title" name="title" style="display:none;"><?php echo htmlspecialchars($section['title']); ?></textarea>
                        </div>

                        <!-- Subtitle Field -->
                        <div class="form-group mb-4">
                            <label class="admin-label fw-bold mb-2" for="subtitle">Tiêu đề phụ</label>
                            <input type="text" id="subtitle" name="subtitle" class="form-control" value="<?php echo htmlspecialchars($section['subtitle'] ?? ''); ?>" placeholder="Nhập tiêu đề phụ (ví dụ: Đại Lý Affiliate ThuongLo)">
                        </div>

                        <!-- Content Field (Rich Text) -->
                        <div class="form-group mb-4">
                            <label class="admin-label fw-bold mb-2">Nội dung mô tả <span class="text-danger">*</span></label>
                            
                            <!-- Custom Toolbar for Content -->
                            <div class="custom-editor-toolbar mb-2" data-for="content">
                                <div class="toolbar-group">
                                    <button type="button" class="btn-tool" onclick="applyFormat('bold', 'content')" title="In đậm"><i class="fas fa-bold"></i></button>
                                    <button type="button" class="btn-tool" onclick="applyFormat('italic', 'content')" title="In nghiêng"><i class="fas fa-italic"></i></button>
                                    <button type="button" class="btn-tool" onclick="applyFormat('underline', 'content')" title="Gạch chân"><i class="fas fa-underline"></i></button>
                                </div>
                                <div class="toolbar-group d-flex align-items-center">
                                    <div class="color-picker-wrapper me-2">
                                        <input type="color" onchange="applyStyle('color', this.value, 'content')" title="Màu chữ">
                                        <i class="fas fa-font"></i>
                                    </div>
                                    <button type="button" class="btn-tool" onclick="applyFormat('removeFormat', 'content')" title="Xóa định dạng"><i class="fas fa-eraser"></i></button>
                                </div>
                            </div>
                            
                            <!-- Editable Area for Content -->
                            <div id="editor-content" class="custom-editable-area form-control border p-3 bg-white" style="min-height: 150px; border-radius: 8px;" contenteditable="true" oninput="syncEditor('content')">
                                <?php echo $section['content']; ?>
                            </div>
                            <textarea id="content" name="content" style="display:none;"><?php echo htmlspecialchars($section['content']); ?></textarea>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <!-- Image Upload Field -->
                        <div class="form-group mb-4">
                            <label class="admin-label fw-bold mb-2">Hình ảnh Section</label>
                            <div class="cta-image-preview-container mb-3 text-center border p-3 rounded bg-light">
                                <?php
                                $imgUrl = $section['image_url'] ?? '';
                                if ($imgUrl) {
                                    if (strpos($imgUrl, 'http') === 0) {
                                        $finalImg = $imgUrl;
                                    } elseif (strpos($imgUrl, 'uploads/') === 0 || strpos($imgUrl, 'assets/') === 0) {
                                        $finalImg = base_url($imgUrl);
                                    } else {
                                        $finalImg = img_url($imgUrl);
                                    }
                                    echo '<img id="image-preview" src="'.$finalImg.'" class="img-fluid rounded shadow-sm" style="max-height: 200px; object-fit: contain;">';
                                } else {
                                    echo '<img id="image-preview" src="https://via.placeholder.com/250x150?text=No+Image" class="img-fluid rounded shadow-sm" style="max-height: 200px; object-fit: contain;">';
                                }
                                ?>
                            </div>
                            <input type="file" id="image" name="image" class="form-control" accept="image/*" onchange="previewUploadImage(this)">
                            <p class="text-muted small mt-1">Nên chọn ảnh trong suốt (PNG) có kích thước 500x500px.</p>
                        </div>

                        <!-- Background Color Picker -->
                        <div class="form-group mb-4">
                            <label class="admin-label fw-bold mb-2" for="background_color">Màu nền Section</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0 bg-light">
                                    <input type="color" id="bg-color-picker" value="<?php echo htmlspecialchars($section['background_color'] ?? '#ECEDEF'); ?>" class="form-control-color border-0 p-0" style="width: 28px; height: 28px; cursor: pointer;" oninput="updateBgColorInput(this.value)">
                                </span>
                                <input type="text" id="background_color" name="background_color" class="form-control border-start-0 ps-1" value="<?php echo htmlspecialchars($section['background_color'] ?? '#ECEDEF'); ?>" placeholder="#ECEDEF" oninput="updateBgColorPicker(this.value)">
                            </div>
                        </div>

                        <!-- Button Text -->
                        <div class="form-group mb-4">
                            <label class="admin-label fw-bold mb-2" for="button_text">Tiêu đề nút bấm</label>
                            <input type="text" id="button_text" name="button_text" class="form-control" value="<?php echo htmlspecialchars($section['button_text'] ?? 'Đăng ký ngay'); ?>" placeholder="Nhập chữ hiển thị trên nút">
                        </div>

                        <!-- Button URL -->
                        <div class="form-group mb-4">
                            <label class="admin-label fw-bold mb-2" for="button_url">Đường dẫn nút bấm (URL)</label>
                            <input type="text" id="button_url" name="button_url" class="form-control" value="<?php echo htmlspecialchars($section['button_url'] ?? '?page=agent'); ?>" placeholder="Nhập đường dẫn liên kết">
                        </div>

                        <!-- Status Toggle -->
                        <div class="form-check mb-4 form-switch">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" style="width: 40px; height: 20px; cursor: pointer;" <?php echo ($section['is_active'] ? 'checked' : ''); ?>>
                            <label for="is_active" class="form-check-label fw-bold ms-2" style="cursor: pointer;">Hiển thị Section này trên trang chủ</label>
                        </div>
                    </div>
                </div>

                <div class="form-actions mt-4 pt-3 border-top d-flex gap-3">
                    <button type="submit" class="btn btn-primary px-4 py-2" style="border-radius: 8px; font-weight: 600;">
                        <i class="fas fa-save me-1"></i> Lưu thay đổi
                    </button>
                    <a href="?page=admin&module=homepage" class="btn btn-secondary px-4 py-2" style="border-radius: 8px; font-weight: 600;">
                        Hủy
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
/**
 * Synchronize contenteditable editor area with form textarea
 */
function syncEditor(fieldId) {
    const editor = document.getElementById('editor-' + fieldId);
    const textarea = document.getElementById(fieldId);
    if (editor && textarea) {
        textarea.value = editor.innerHTML;
    }
}

/**
 * Enhanced Rich Text Formatting commands
 */
function applyFormat(command, fieldId) {
    const editor = document.getElementById('editor-' + fieldId);
    if (!editor) return;
    
    editor.focus();
    try {
        document.execCommand(command, false, null);
        syncEditor(fieldId);
    } catch (e) {
        console.error('Format command failed:', e);
    }
}

function applyStyle(property, value, fieldId) {
    const editor = document.getElementById('editor-' + fieldId);
    if (!editor || !value) return;
    
    editor.focus();
    try {
        if (property === 'fontFamily') {
            document.execCommand('fontName', false, value);
        } else if (property === 'color') {
            document.execCommand('foreColor', false, value);
        }
        syncEditor(fieldId);
    } catch (e) {
        console.error('Style apply failed:', e);
    }
}

/**
 * Background Color syncing
 */
function updateBgColorInput(value) {
    document.getElementById('background_color').value = value.toUpperCase();
}

function updateBgColorPicker(value) {
    if (/^#[0-9A-F]{6}$/i.test(value)) {
        document.getElementById('bg-color-picker').value = value;
    }
}

/**
 * Image Upload Preview
 */
function previewUploadImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('image-preview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Auto-sync editors on load and periodically
document.addEventListener('DOMContentLoaded', function() {
    syncEditor('title');
    syncEditor('content');
    
    setInterval(function() {
        syncEditor('title');
        syncEditor('content');
    }, 1000);
});
</script>

<style>
/* Custom Style adjustments for edit page to ensure premium feel */
.custom-editor-toolbar {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-bottom: none;
    border-radius: 8px 8px 0 0;
    padding: 8px;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
}

.custom-editable-area {
    border-radius: 0 0 8px 8px !important;
    outline: none;
    overflow-y: auto;
}

.custom-editable-area:focus {
    border-color: #86b7fe !important;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
}

.btn-tool {
    background: white;
    border: 1px solid #ced4da;
    border-radius: 4px;
    width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #495057;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-tool:hover {
    background-color: #e9ecef;
    color: #0d6efd;
    border-color: #b0d4ff;
}

.color-picker-wrapper {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: white;
    border: 1px solid #ced4da;
    border-radius: 4px;
    width: 32px;
    height: 32px;
    cursor: pointer;
}

.color-picker-wrapper input[type="color"] {
    position: absolute;
    opacity: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
}

.color-picker-wrapper i {
    color: #495057;
    font-size: 14px;
    pointer-events: none;
}

.hero-section-edit-page {
    padding: 20px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.page-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 5px 0;
}

.page-description {
    color: #6B7280;
    margin: 0;
}

.btn-back {
    display: inline-flex;
    align-items: center;
    background: #f3f4f6;
    color: #4b5563;
    padding: 8px 16px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.btn-back:hover {
    background: #e5e7eb;
    color: #1f2937;
}

/* Custom Highlight style within contenteditable editor to preview actual highlight class */
.custom-editable-area .highlight {
    color: #356DF1;
    font-weight: 700;
}
</style>
