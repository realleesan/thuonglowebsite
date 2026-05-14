<?php
/**
 * Edit Hero Section View - Enhanced Custom Editor
 */

// Get flash messages
$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);

// Use buttons from heroSection data
$heroButtons = $heroSection['buttons'] ?? [];
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">Chỉnh sửa Hero Section #<?php echo $heroSection['id']; ?></h1>
                    <p class="text-muted small mb-0">Thiết kế nội dung trang chủ chuyên nghiệp.</p>
                </div>
                <a href="?page=admin&module=hero-section" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Danh sách Hero Section
                </a>
            </div>

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
                                <?php echo $heroSection['title_main']; ?>
                            </div>
                            <textarea id="title_main" name="title_main" style="display:none;"><?php echo htmlspecialchars($heroSection['title_main']); ?></textarea>
                        </div>

                        <div class="form-group mb-4">
                            <label class="admin-label">Mô tả phụ</label>
                            
                            <!-- Custom Toolbar for Subtitle -->
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
                                <?php echo $heroSection['subtitle']; ?>
                            </div>
                            <textarea id="subtitle" name="subtitle" style="display:none;"><?php echo htmlspecialchars($heroSection['subtitle']); ?></textarea>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-8">
                                <label class="admin-label">Hình ảnh minh họa</label>
                                <div class="image-upload-wrapper">
                                    <div class="input-group">
                                        <input type="text" class="admin-input" id="image_url" name="image_url" value="<?php echo htmlspecialchars($heroSection['image_url'] ?? ''); ?>" placeholder="Đường dẫn ảnh hoặc tải lên">
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
                                    <input type="text" class="admin-input" id="background_color" name="background_color" value="<?php echo htmlspecialchars($heroSection['background_color'] ?? '#ffffff'); ?>">
                                    <input type="color" value="<?php echo htmlspecialchars($heroSection['background_color'] ?? '#ffffff'); ?>" onchange="document.getElementById('background_color').value = this.value">
                                </div>
                            </div>
                        </div>

                        <div class="form-check mb-4">
                            <input type="checkbox" id="is_active" name="is_active" value="1" <?php echo ($heroSection['is_active'] ? 'checked' : ''); ?>>
                            <label for="is_active" class="fw-bold">Hiển thị Hero Section này trên trang chủ</label>
                        </div>

                        <div class="form-actions mt-5">
                            <button type="submit" class="btn-save-large">Lưu tất cả thay đổi</button>
                        </div>
                    </form>
                </div>
                
                <!-- Buttons Card -->
                <div class="admin-card mt-4">
                    <div class="card-header-flex">
                        <h5 class="mb-0 fw-bold">Các nút bấm (Call to Action)</h5>
                        <button type="button" class="btn-add-pill" onclick="addNewButton()">+ Thêm nút mới</button>
                    </div>
                    <div id="buttons-container" class="mt-4">
                        <?php if (!empty($heroButtons)): ?>
                            <?php foreach ($heroButtons as $button): ?>
                                <div class="button-row-item" data-button-id="<?php echo $button['id']; ?>">
                                    <div class="row align-items-center">
                                        <div class="col-md-4">
                                            <label class="small text-muted mb-1">Tên hiển thị</label>
                                            <input type="text" class="admin-input-sm button-text" value="<?php echo htmlspecialchars($button['button_text']); ?>" placeholder="Vd: Xem ngay">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="small text-muted mb-1">Đường dẫn (URL)</label>
                                            <input type="text" class="admin-input-sm button-url" value="<?php echo htmlspecialchars($button['button_url']); ?>" placeholder="Vd: /san-pham">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="small text-muted mb-1">Kiểu dáng</label>
                                            <select class="admin-select-sm button-style">
                                                <option value="primary" <?php echo ($button['button_style'] === 'primary' ? 'selected' : ''); ?>>Nổi bật (Đậm)</option>
                                                <option value="outline" <?php echo ($button['button_style'] === 'outline' ? 'selected' : ''); ?>>Nhẹ nhàng (Viền)</option>
                                                <option value="secondary" <?php echo ($button['button_style'] === 'secondary' ? 'selected' : ''); ?>>Phụ</option>
                                            </select>
                                        </div>
                                        <div class="col-md-1 text-end pt-3">
                                            <button type="button" class="btn-remove" onclick="removeButton(this)"><i class="fas fa-trash-alt"></i></button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-4 text-muted border-dashed rounded" id="no-buttons-msg">
                                Chưa có nút bấm nào. Hãy nhấn "Thêm nút mới".
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex justify-content-center mt-4">
                        <button type="button" class="btn-sync" onclick="saveButtons()">
                            <i class="fas fa-sync-alt me-2"></i> Cập nhật danh sách nút bấm
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
/**
 * Core Editor Functions
 */
function applyFormat(command, field) {
    document.getElementById('editor-' + field).focus();
    document.execCommand(command, false, null);
    syncEditor(field);
}

function applyStyle(property, value, field) {
    const editor = document.getElementById('editor-' + field);
    editor.focus();
    
    // Get selection
    const selection = window.getSelection();
    if (!selection.rangeCount) return;
    
    // For font size, color, font family - use spans for exact control
    if (property === 'fontSize' || property === 'color' || property === 'fontFamily') {
        const range = selection.getRangeAt(0);
        if (range.collapsed) return; // No selection
        
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

/**
 * Image Upload Function
 */
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
    
    fetch('?page=admin&module=hero-section&action=update&id=<?php echo $heroSection['id']; ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) alert('Đã cập nhật Hero Section thành công!');
        else alert('Lỗi: ' + d.message);
    });
});

// Button Management
let buttonIdCounter = 1000;
function addNewButton() {
    const container = document.getElementById('buttons-container');
    const msg = document.getElementById('no-buttons-msg');
    if (msg) msg.remove();

    const html = `
        <div class="button-row-item" data-button-id="new-${buttonIdCounter++}">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <label class="small text-muted mb-1">Tên hiển thị</label>
                    <input type="text" class="admin-input-sm button-text" placeholder="Vd: Xem ngay">
                </div>
                <div class="col-md-4">
                    <label class="small text-muted mb-1">Đường dẫn (URL)</label>
                    <input type="text" class="admin-input-sm button-url" placeholder="Vd: /san-pham">
                </div>
                <div class="col-md-3">
                    <label class="small text-muted mb-1">Kiểu dáng</label>
                    <select class="admin-select-sm button-style">
                        <option value="primary">Nổi bật (Đậm)</option>
                        <option value="outline">Nhẹ nhàng (Viền)</option>
                        <option value="secondary">Phụ</option>
                    </select>
                </div>
                <div class="col-md-1 text-end pt-3">
                    <button type="button" class="btn-remove" onclick="removeButton(this)"><i class="fas fa-trash-alt"></i></button>
                </div>
            </div>
        </div>`;
    container.insertAdjacentHTML('beforeend', html);
}

function removeButton(btn) {
    if (confirm('Xóa nút này khỏi Hero Section?')) {
        btn.closest('.button-row-item').remove();
    }
}

function saveButtons() {
    const buttons = [];
    document.querySelectorAll('[data-button-id]').forEach((card, index) => {
        buttons.push({
            id: card.getAttribute('data-button-id'),
            button_text: card.querySelector('.button-text').value,
            button_url: card.querySelector('.button-url').value,
            button_style: card.querySelector('.button-style').value,
            display_order: index + 1
        });
    });

    fetch('?page=admin&module=hero-section&action=update-buttons', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            hero_section_id: <?php echo $heroSection['id']; ?>,
            buttons: buttons
        })
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) alert('Đã cập nhật danh sách nút bấm thành công!');
        else alert('Lỗi: ' + d.message);
    });
}
</script>

<style>
.admin-form-full { max-width: 1000px; margin: 0 auto; }
.admin-card { background: white; border-radius: 12px; padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin-bottom: 25px; }
.admin-label { display: block; font-weight: 700; margin-bottom: 10px; color: #333; font-size: 0.95rem; }
.admin-input { width: 100%; padding: 12px 15px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: all 0.3s; }
.admin-input:focus { border-color: #356DF1; box-shadow: 0 0 0 3px rgba(53, 109, 241, 0.1); outline: none; }

/* Custom Editor */
.custom-editor-toolbar { background: #fdfdfd; border: 1px solid #e0e0e0; border-bottom: none; border-radius: 8px 8px 0 0; padding: 10px; display: flex; flex-wrap: wrap; gap: 15px; align-items: center; }
.toolbar-group { display: flex; gap: 5px; align-items: center; border-right: 1px solid #eee; padding-right: 15px; }
.toolbar-group:last-child { border-right: none; }

.custom-editor-toolbar button { background: white; border: 1px solid #ddd; border-radius: 6px; width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #555; }
.custom-editor-toolbar button:hover { background: #f0f4ff; color: #356DF1; border-color: #356DF1; }

.font-select { padding: 6px 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 13px; }
.size-input-wrapper { display: flex; align-items: center; gap: 5px; background: #fff; border: 1px solid #ddd; border-radius: 6px; padding: 0 8px; }
.size-input { border: none; width: 45px; padding: 6px 0; font-size: 13px; text-align: center; outline: none; }
.size-input-wrapper span { font-size: 12px; color: #888; }

.color-picker-wrapper { position: relative; width: 34px; height: 34px; border: 1px solid #ddd; border-radius: 6px; overflow: hidden; }
.color-picker-wrapper input[type="color"] { position: absolute; top: -5px; left: -5px; width: 50px; height: 50px; cursor: pointer; opacity: 0; z-index: 2; }
.color-picker-wrapper i { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 1; color: #555; }

.custom-editable-area { min-height: 150px; border: 1px solid #e0e0e0; border-radius: 0 0 8px 8px; padding: 20px; background: white; outline: none; font-size: 1rem; line-height: 1.6; }
.custom-editable-area:focus { border-color: #356DF1; }

/* Image Upload */
.image-upload-wrapper .input-group { display: flex; gap: 10px; }
.btn-upload { background: #f8f9fa; border: 1px solid #ddd; padding: 0 20px; border-radius: 8px; cursor: pointer; font-weight: 600; white-space: nowrap; transition: 0.2s; }
.btn-upload:hover { background: #e9ecef; }

/* Buttons */
.button-row-item { background: #f9fafb; padding: 20px; border: 1px solid #edf2f7; border-radius: 12px; margin-bottom: 15px; transition: 0.2s; }
.button-row-item:hover { border-color: #356DF1; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
.btn-add-pill { background: #356DF1; color: white; border: none; padding: 8px 20px; border-radius: 50px; font-weight: 600; cursor: pointer; transition: 0.3s; }
.btn-add-pill:hover { background: #2851c3; transform: translateY(-1px); }
.btn-remove { background: none; border: none; color: #e53e3e; cursor: pointer; font-size: 1.1rem; padding: 8px; border-radius: 50%; transition: 0.2s; }
.btn-remove:hover { background: #fff5f5; color: #c53030; }
.btn-sync { background: #4a5568; color: white; border: none; padding: 12px 35px; border-radius: 8px; font-weight: 600; cursor: pointer; }
.btn-sync:hover { background: #2d3748; }

.btn-save-large { background: #356DF1; color: white; border: none; padding: 16px 50px; border-radius: 10px; font-size: 1.1rem; font-weight: 700; cursor: pointer; width: 100%; box-shadow: 0 4px 14px rgba(53, 109, 241, 0.4); transition: 0.3s; }
.btn-save-large:hover { background: #2851c3; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(53, 109, 241, 0.5); }

.card-header-flex { display: flex; justify-content: space-between; align-items: center; }
.border-dashed { border: 2px dashed #e2e8f0; }
.btn-back { text-decoration: none; color: #718096; font-size: 0.9rem; font-weight: 500; }
.btn-back:hover { color: #356DF1; }
.admin-input-sm { width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.9rem; }
.admin-select-sm { width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.9rem; background: white; }

.row { display: flex; flex-wrap: wrap; margin-left: -15px; margin-right: -15px; }
.col-md-8 { width: 66.66%; padding: 0 15px; }
.col-md-4 { width: 33.33%; padding: 0 15px; }
.col-md-3 { width: 25%; padding: 0 15px; }
.col-md-1 { width: 8.33%; padding: 0 15px; }
</style>
