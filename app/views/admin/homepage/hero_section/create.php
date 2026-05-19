<?php
/**
 * Create Hero Section View - Enhanced Custom Editor
 */

// Get flash messages
$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);
?>

<div class="hero-section-page hero-section-create-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-plus"></i>
                Tạo Hero Section mới
            </h1>
            <p class="page-description">Thiết kế nội dung thu hút khách hàng ngay từ cái nhìn đầu tiên.</p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=hero-section" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                Quay lại
            </a>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

            <div class="admin-form-full">
                <div class="admin-card">
                    <form id="heroSectionForm" method="POST">
                        <div class="form-group mb-4">
                            <label class="admin-label">Tiêu đề Hero Section <span class="text-danger">*</span></label>
                            
                            <!-- Enhanced Custom Rich Text Toolbar -->
                            <div class="custom-editor-toolbar" data-for="title_main">
                                <div class="toolbar-group">
                                    <button type="button" onclick="applyFormat('bold', 'title_main')" title="In đậm"><i class="fas fa-bold"></i></button>
                                    <button type="button" onclick="applyFormat('italic', 'title_main')" title="In nghiêng"><i class="fas fa-italic"></i></button>
                                    <button type="button" onclick="applyFormat('underline', 'title_main')" title="Gạch chân"><i class="fas fa-underline"></i></button>
                                </div>
                                <div class="toolbar-group">
                                    <select onchange="applyStyle('fontFamily', this.value, 'title_main')" class="font-select">
                                        <option value="">Font chữ</option>
                                        <option value="Arial, sans-serif">Arial</option>
                                        <option value="'Inter', sans-serif">Inter</option>
                                        <option value="'Roboto', sans-serif">Roboto</option>
                                        <option value="serif">Serif</option>
                                        <option value="monospace">Monospace</option>
                                    </select>
                                    <div class="size-input-wrapper">
                                        <input type="number" value="48" min="10" max="100" onchange="applyStyle('fontSize', this.value + 'px', 'title_main')" class="size-input">
                                        <span>px</span>
                                    </div>
                                </div>
                                <div class="toolbar-group">
                                    <div class="color-picker-wrapper">
                                        <input type="color" onchange="applyStyle('color', this.value, 'title_main')" title="Màu chữ">
                                        <i class="fas fa-font"></i>
                                    </div>
                                    <button type="button" onclick="applyFormat('removeFormat', 'title_main')" title="Xóa định dạng"><i class="fas fa-eraser"></i></button>
                                </div>
                            </div>
                            
                            <!-- Editable Area -->
                            <div id="editor-title_main" class="custom-editable-area" contenteditable="true" oninput="syncEditor('title_main')">
                                Nền tảng data nguồn hàng và dịch vụ <strong>Thương mại xuyên biên giới</strong>
                            </div>
                            <textarea id="title_main" name="title_main" style="display:none;"></textarea>
                        </div>

                        <div class="form-group mb-4">
                            <label class="admin-label">Mô tả phụ</label>
                            
                            <div class="custom-editor-toolbar" data-for="subtitle">
                                <div class="toolbar-group">
                                    <button type="button" onclick="applyFormat('bold', 'subtitle')"><i class="fas fa-bold"></i></button>
                                    <button type="button" onclick="applyFormat('italic', 'subtitle')"><i class="fas fa-italic"></i></button>
                                    <div class="size-input-wrapper">
                                        <input type="number" value="18" min="10" max="100" onchange="applyStyle('fontSize', this.value + 'px', 'subtitle')" class="size-input">
                                        <span>px</span>
                                    </div>
                                </div>
                                <div class="toolbar-group">
                                    <div class="color-picker-wrapper">
                                        <input type="color" onchange="applyStyle('color', this.value, 'subtitle')">
                                        <i class="fas fa-font"></i>
                                    </div>
                                    <button type="button" onclick="applyFormat('removeFormat', 'subtitle')"><i class="fas fa-eraser"></i></button>
                                </div>
                            </div>
                            
                            <div id="editor-subtitle" class="custom-editable-area" contenteditable="true" oninput="syncEditor('subtitle')">
                                ThuongLo cung cấp giải pháp toàn diện cho doanh nghiệp.
                            </div>
                            <textarea id="subtitle" name="subtitle" style="display:none;"></textarea>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-8">
                                <label class="admin-label">Hình ảnh minh họa</label>
                                <div class="image-upload-wrapper">
                                    <div class="input-group">
                                        <input type="text" class="admin-input" id="image_url" name="image_url" value="home/home-banner-final.png" placeholder="Đường dẫn ảnh hoặc tải lên">
                                        <button type="button" class="btn-upload" onclick="document.getElementById('image_file').click()">
                                            <i class="fas fa-upload me-1"></i> Tải lên từ máy
                                        </button>
                                    </div>
                                    <input type="file" id="image_file" style="display:none" accept="image/*" onchange="uploadImage(this)">
                                    <div id="upload-status" class="small mt-1 text-muted"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="admin-label">Màu nền Section</label>
                                <div class="input-with-color">
                                    <input type="text" class="admin-input" id="background_color" name="background_color" value="#ffffff">
                                    <input type="color" value="#ffffff" onchange="document.getElementById('background_color').value = this.value">
                                </div>
                            </div>
                        </div>

                        <div class="form-check mb-4">
                            <input type="checkbox" id="is_active" name="is_active" value="1" checked>
                            <label for="is_active" class="fw-bold">Kích hoạt ngay sau khi tạo</label>
                        </div>

                        <div class="form-actions mt-5">
                            <button type="submit" class="btn-save-large">Lưu Hero Section</button>
                        </div>
                    </form>
                </div>
            </div>
            </div>
        </div>
    </div>
</div>

<script>
function applyFormat(command, field) {
    document.getElementById('editor-' + field).focus();
    document.execCommand(command, false, null);
    syncEditor(field);
}

function applyStyle(property, value, field) {
    const editor = document.getElementById('editor-' + field);
    editor.focus();
    
    const selection = window.getSelection();
    if (!selection.rangeCount) return;
    
    if (property === 'fontSize' || property === 'color' || property === 'fontFamily') {
        const range = selection.getRangeAt(0);
        if (range.collapsed) return;
        
        const span = document.createElement('span');
        span.style[property] = value;
        range.surroundContents(span);
    } else {
        document.execCommand(property, false, value);
    }
    
    syncEditor(field);
}

function syncEditor(field) {
    const editor = document.getElementById('editor-' + field);
    const textarea = document.getElementById(field);
    textarea.value = editor.innerHTML;
}

function uploadImage(input) {
    if (!input.files || !input.files[0]) return;
    
    const status = document.getElementById('upload-status');
    status.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Đang tải lên...';
    
    const formData = new FormData();
    formData.append('image', input.files[0]);
    
    fetch('?page=admin&module=hero-section&action=upload-image', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('image_url').value = data.url;
            status.innerHTML = '<span class="text-success"><i class="fas fa-check me-1"></i> Tải lên thành công!</span>';
        } else {
            status.innerHTML = '<span class="text-danger"><i class="fas fa-times me-1"></i> ' + data.message + '</span>';
        }
    })
    .catch(err => {
        status.innerHTML = '<span class="text-danger"><i class="fas fa-times me-1"></i> Lỗi kết nối</span>';
    });
}

// Form Submit
document.getElementById('heroSectionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const data = {
        title_main: document.getElementById('editor-title_main').innerHTML,
        subtitle: document.getElementById('editor-subtitle').innerHTML,
        image_url: document.getElementById('image_url').value,
        background_color: document.getElementById('background_color').value,
        is_active: document.getElementById('is_active').checked ? 1 : 0,
        title_highlight: '',
        text_color: '#333333',
        highlight_color: '#356DF1',
        font_family: 'Arial, sans-serif',
        title_font_size: '48px',
        subtitle_font_size: '18px'
    };
    
    fetch('/api.php?path=admin/hero-section&sub=create', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) window.location.href = '?page=admin&module=hero-section';
        else alert('Lỗi: ' + d.message);
    });
});
</script>

