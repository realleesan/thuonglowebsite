<?php
/**
 * Create or Edit Custom Category Section Form
 */
$section = $section ?? null;
$id = $section['id'] ?? 0;
$title_val = $section['title'] ?? '';
$cat_id_val = $section['category_id'] ?? 0;
$disp_type_val = $section['display_type'] ?? 'featured';
$sort_val = $section['sort_order'] ?? 0;
$is_active_val = isset($section['is_active']) ? (int)$section['is_active'] : 1;

// Function to build tree of categories
if (!function_exists('buildCategoryTree')) {
    function buildCategoryTree(array $elements, $parentId = null) {
        $branch = [];
        foreach ($elements as $element) {
            $pId = !empty($element['parent_id']) ? intval($element['parent_id']) : null;
            $targetPId = !empty($parentId) ? intval($parentId) : null;
            if ($pId === $targetPId) {
                $children = buildCategoryTree($elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
                } else {
                    $element['children'] = [];
                }
                $branch[] = $element;
            }
        }
        return $branch;
    }
}

// Function to render categories recursively as option tags
if (!function_exists('renderCategoryOptions')) {
    function renderCategoryOptions(array $tree, $level = 0, $selectedId = 0) {
        $options = '';
        foreach ($tree as $node) {
            $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
            $prefix = $level > 0 ? '↳ ' : '';
            $selected = ($selectedId == $node['id']) ? 'selected' : '';
            $options .= '<option value="' . $node['id'] . '" ' . $selected . '>';
            $options .= $indent . $prefix . htmlspecialchars($node['name']);
            $options .= '</option>';
            
            if (!empty($node['children'])) {
                $options .= renderCategoryOptions($node['children'], $level + 1, $selectedId);
            }
        }
        return $options;
    }
}
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <!-- Back Button and Title -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center">
                    <a href="?page=admin&module=homepage" class="btn btn-sm btn-light border-0 me-3 px-3 py-2 rounded-3 shadow-sm">
                        <i class="fas fa-arrow-left text-muted me-1"></i> Quay lại
                    </a>
                    <div>
                        <h1 class="h3 mb-1 fw-bold"><?php echo $id > 0 ? 'Chỉnh sửa Section Danh mục' : 'Thêm mới Section Danh mục'; ?></h1>
                        <p class="text-muted small mb-0">Cấu hình section sản phẩm theo danh mục tùy chọn</p>
                    </div>
                </div>
            </div>

            <!-- Main Form Card -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white border-bottom py-3.5">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-cog text-primary me-2"></i>
                        Thông tin cấu hình
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="?page=admin&module=homepage&action=save-custom-category" method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="id" value="<?php echo $id; ?>">

                        <!-- Section Title (Rich Text Editor) -->
                        <div class="form-group mb-4">
                            <label class="form-label fw-semibold text-dark mb-2">Tiêu đề Section <span class="text-danger">*</span></label>
                            
                            <!-- Custom Toolbar for Title -->
                            <div class="custom-editor-toolbar mb-2" data-for="title">
                                <div class="toolbar-group">
                                    <button type="button" onclick="applyFormat('bold', 'title')" title="In đậm"><i class="fas fa-bold"></i></button>
                                    <button type="button" onclick="applyFormat('italic', 'title')" title="In nghiêng"><i class="fas fa-italic"></i></button>
                                    <button type="button" onclick="applyFormat('underline', 'title')" title="Gạch chân"><i class="fas fa-underline"></i></button>
                                </div>
                                <div class="toolbar-group">
                                    <select onchange="applyStyle('fontFamily', this.value, 'title')" class="font-select">
                                        <option value="">Font chữ</option>
                                        <option value="Arial, sans-serif">Arial</option>
                                        <option value="'Inter', sans-serif">Inter</option>
                                        <option value="'Roboto', sans-serif">Roboto</option>
                                        <option value="serif">Serif</option>
                                        <option value="monospace">Monospace</option>
                                    </select>
                                </div>
                                <div class="toolbar-group">
                                    <div class="color-picker-wrapper">
                                        <input type="color" onchange="applyStyle('color', this.value, 'title')" title="Màu chữ">
                                        <i class="fas fa-font"></i>
                                    </div>
                                    <button type="button" onclick="applyFormat('removeFormat', 'title')" title="Xóa định dạng"><i class="fas fa-eraser"></i></button>
                                </div>
                            </div>
                            
                            <!-- Editable Area -->
                            <div id="editor-title" class="custom-editable-area form-control rounded-3 border-light-subtle shadow-sm mb-2" contenteditable="true" oninput="syncEditor('title')" style="min-height: 100px; padding: 15px; background: white;">
                                <?php echo $title_val; ?>
                            </div>
                            <textarea id="title" name="title" style="display:none;"><?php echo htmlspecialchars($title_val); ?></textarea>
                            <div class="invalid-feedback">Vui lòng nhập hoặc thiết kế tiêu đề hiển thị.</div>
                            <small class="text-muted mt-1 d-block">Tùy biến định dạng tiêu đề in đậm, nghiêng, chọn font chữ và màu sắc.</small>
                        </div>

                        <!-- Category Select -->
                        <div class="mb-4">
                            <label for="category_id" class="form-label fw-semibold text-dark">Danh mục sản phẩm <span class="text-danger">*</span></label>
                            <select class="form-select form-select-lg rounded-3 border-light-subtle shadow-sm" 
                                    id="category_id" name="category_id" required>
                                <option value="" disabled <?php echo !$cat_id_val ? 'selected' : ''; ?>>-- Chọn danh mục --</option>
                                <?php if (!empty($categories)): ?>
                                    <?php 
                                    $categoryTree = buildCategoryTree($categories);
                                    echo renderCategoryOptions($categoryTree, 0, $cat_id_val);
                                    ?>
                                <?php endif; ?>
                            </select>
                            <div class="invalid-feedback">Vui lòng chọn một danh mục sản phẩm.</div>
                            <input type="hidden" name="sort_order" value="<?php echo $sort_val; ?>">
                        </div>

                        <!-- Display Type Choices -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-dark mb-3">Loại sản phẩm hiển thị</label>
                            <div class="display-type-row">
                                <!-- Option Featured -->
                                <div class="display-type-col">
                                    <label class="display-type-card text-center rounded-3 p-3 border w-100 d-block cursor-pointer shadow-sm position-relative <?php echo $disp_type_val === 'featured' ? 'active' : ''; ?>">
                                        <input type="radio" name="display_type" value="featured" style="position: absolute !important; opacity: 0 !important; width: 0 !important; height: 0 !important; pointer-events: none !important; z-index: -1 !important;" <?php echo $disp_type_val === 'featured' ? 'checked' : ''; ?>>
                                        <div class="icon-wrapper mb-2 rounded-circle d-flex align-items-center justify-content-center bg-soft-danger text-danger mx-auto" style="width: 48px; height: 48px;">
                                            <i class="fas fa-star fa-lg"></i>
                                        </div>
                                        <div class="fw-bold fs-6">Nổi bật</div>
                                        <div class="text-muted small mt-1">Sản phẩm đánh dấu nổi bật</div>
                                    </label>
                                </div>

                                <!-- Option Budget -->
                                <div class="display-type-col">
                                    <label class="display-type-card text-center rounded-3 p-3 border w-100 d-block cursor-pointer shadow-sm position-relative <?php echo $disp_type_val === 'budget' ? 'active' : ''; ?>">
                                        <input type="radio" name="display_type" value="budget" style="position: absolute !important; opacity: 0 !important; width: 0 !important; height: 0 !important; pointer-events: none !important; z-index: -1 !important;" <?php echo $disp_type_val === 'budget' ? 'checked' : ''; ?>>
                                        <div class="icon-wrapper mb-2 rounded-circle d-flex align-items-center justify-content-center bg-soft-success text-success mx-auto" style="width: 48px; height: 48px;">
                                            <i class="fas fa-tags fa-lg"></i>
                                        </div>
                                        <div class="fw-bold fs-6">Giá rẻ</div>
                                        <div class="text-muted small mt-1">Sản phẩm có giá từ thấp đến cao</div>
                                    </label>
                                </div>

                                <!-- Option Sale -->
                                <div class="display-type-col">
                                    <label class="display-type-card text-center rounded-3 p-3 border w-100 d-block cursor-pointer shadow-sm position-relative <?php echo $disp_type_val === 'sale' ? 'active' : ''; ?>">
                                        <input type="radio" name="display_type" value="sale" style="position: absolute !important; opacity: 0 !important; width: 0 !important; height: 0 !important; pointer-events: none !important; z-index: -1 !important;" <?php echo $disp_type_val === 'sale' ? 'checked' : ''; ?>>
                                        <div class="icon-wrapper mb-2 rounded-circle d-flex align-items-center justify-content-center bg-soft-warning text-warning mx-auto" style="width: 48px; height: 48px;">
                                            <i class="fas fa-percentage fa-lg"></i>
                                        </div>
                                        <div class="fw-bold fs-6">Giảm giá</div>
                                        <div class="text-muted small mt-1">Sản phẩm có giá khuyến mại</div>
                                    </label>
                                </div>

                                <!-- Option Latest -->
                                <div class="display-type-col">
                                    <label class="display-type-card text-center rounded-3 p-3 border w-100 d-block cursor-pointer shadow-sm position-relative <?php echo $disp_type_val === 'latest' ? 'active' : ''; ?>">
                                        <input type="radio" name="display_type" value="latest" style="position: absolute !important; opacity: 0 !important; width: 0 !important; height: 0 !important; pointer-events: none !important; z-index: -1 !important;" <?php echo $disp_type_val === 'latest' ? 'checked' : ''; ?>>
                                        <div class="icon-wrapper mb-2 rounded-circle d-flex align-items-center justify-content-center bg-soft-info text-info mx-auto" style="width: 48px; height: 48px;">
                                            <i class="fas fa-clock fa-lg"></i>
                                        </div>
                                        <div class="fw-bold fs-6">Mới nhất</div>
                                        <div class="text-muted small mt-1">Sản phẩm mới thêm gần đây</div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Active Status Switch -->
                        <div class="mb-4 py-2 px-3 bg-light rounded-3 d-flex align-items-center justify-content-between border border-light-subtle shadow-sm">
                            <div>
                                <div class="fw-bold text-dark mb-0.5">Trạng thái hoạt động</div>
                                <div class="text-muted small">Nếu tắt, section này sẽ tạm ẩn khỏi trang chủ.</div>
                            </div>
                            <div class="form-check form-switch form-switch-lg fs-4 mb-0">
                                <input class="form-check-input cursor-pointer" type="checkbox" role="switch" 
                                       id="is_active" name="is_active" value="1" <?php echo $is_active_val ? 'checked' : ''; ?>>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex align-items-center justify-content-end gap-3 pt-3 border-top mt-4">
                            <a href="?page=admin&module=homepage" class="btn btn-lg btn-light rounded-3 px-4 fw-semibold border">Hủy bỏ</a>
                            <button type="submit" class="btn btn-lg btn-primary rounded-3 px-5 fw-semibold shadow-sm">
                                <i class="fas fa-save me-1.5"></i> Lưu cấu hình
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
/**
 * Rich Text Editor Implementation
 */
class RichTextEditor {
    constructor(fieldId) {
        this.fieldId = fieldId;
        this.editor = document.getElementById('editor-' + fieldId);
        this.textarea = document.getElementById(fieldId);
        this.toolbar = document.querySelector(`[data-for="${fieldId}"]`);
        
        this.init();
    }
    
    init() {
        if (this.textarea.value) {
            this.editor.innerHTML = this.textarea.value;
        }
        
        this.editor.addEventListener('input', () => this.syncEditor());
        this.editor.addEventListener('paste', (e) => this.handlePaste(e));
        this.editor.addEventListener('keydown', (e) => this.handleKeydown(e));
        this.editor.addEventListener('mouseup', () => this.updateToolbarState());
        this.editor.addEventListener('keyup', () => this.updateToolbarState());
        
        this.initToolbar();
        setInterval(() => this.syncEditor(), 1000);
    }
    
    initToolbar() {
        if (!this.toolbar) return;
        
        this.toolbar.querySelectorAll('button').forEach(btn => {
            const onclick = btn.getAttribute('onclick');
            if (onclick && onclick.includes('applyFormat')) {
                const command = onclick.match(/'([^']+)'/)[1];
                btn.onclick = (e) => {
                    e.preventDefault();
                    this.applyFormat(command);
                };
            }
        });
        
        const fontSelect = this.toolbar.querySelector('select');
        if (fontSelect) {
            fontSelect.onchange = (e) => {
                e.preventDefault();
                if (fontSelect.value) {
                    this.applyStyle('fontFamily', fontSelect.value);
                }
            };
        }
                
        const colorInput = this.toolbar.querySelector('input[type="color"]');
        if (colorInput) {
            colorInput.onchange = (e) => {
                e.preventDefault();
                this.applyStyle('color', colorInput.value);
            };
        }
    }
    
    applyFormat(command) {
        this.editor.focus();
        try {
            const cmd = command === 'bold' ? 'bold' : 
                       command === 'italic' ? 'italic' : 'underline';
            document.execCommand(cmd, false, null);
            this.syncEditor();
            this.updateToolbarState();
        } catch (e) {
            console.error('Format error:', e);
        }
    }
    
    applyStyle(property, value) {
        this.editor.focus();
        try {
            if (property === 'fontFamily') {
                document.execCommand('fontName', false, value);
            } else if (property === 'color') {
                document.execCommand('foreColor', false, value);
            }
            this.syncEditor();
        } catch (e) {
            console.error('Style error:', e);
        }
    }
    
    clearFormat() {
        this.editor.focus();
        try {
            document.execCommand('removeFormat', false, null);
            this.syncEditor();
            this.updateToolbarState();
        } catch (e) {
            console.error('Clear format error:', e);
        }
    }
    
    handlePaste(e) {
        e.preventDefault();
        const text = e.clipboardData.getData('text/plain') || '';
        const selection = window.getSelection();
        if (selection.rangeCount) {
            const range = selection.getRangeAt(0);
            range.deleteContents();
            range.insertNode(document.createTextNode(text));
        }
        this.syncEditor();
    }
    
    handleKeydown(e) {
        if (e.ctrlKey || e.metaKey) {
            switch(e.key) {
                case 'b':
                    e.preventDefault();
                    this.applyFormat('bold');
                    break;
                case 'i':
                    e.preventDefault();
                    this.applyFormat('italic');
                    break;
                case 'u':
                    e.preventDefault();
                    this.applyFormat('underline');
                    break;
            }
        }
    }
    
    updateToolbarState() {
        if (!this.toolbar) return;
        try {
            const selection = window.getSelection();
            if (!selection.rangeCount) return;
            
            const range = selection.getRangeAt(0);
            if (range.collapsed) return;
            
            let selectedElement = range.commonAncestorContainer;
            if (selectedElement.nodeType === Node.TEXT_NODE) {
                selectedElement = selectedElement.parentElement;
            }
            
            const fontSelect = this.toolbar.querySelector('select');
            if (fontSelect && selectedElement) {
                const fontFamily = window.getComputedStyle(selectedElement).fontFamily;
                fontSelect.value = '';
                Array.from(fontSelect.options).forEach(option => {
                    if (option.value && fontFamily.includes(option.value.replace(/['"]/g, ''))) {
                        fontSelect.value = option.value;
                    }
                });
            }
            
            this.updateButtonStates();
        } catch (e) {
            console.error('Toolbar state update error:', e);
        }
    }
    
    updateButtonStates() {
        if (!this.toolbar) return;
        const boldBtn = this.toolbar.querySelector('button[title*="đậm"], button[title*="Bold"]');
        if (boldBtn) {
            boldBtn.style.backgroundColor = document.queryCommandState('bold') ? '#e0e0e0' : '';
        }
        const italicBtn = this.toolbar.querySelector('button[title*="nghiêng"], button[title*="Italic"]');
        if (italicBtn) {
            italicBtn.style.backgroundColor = document.queryCommandState('italic') ? '#e0e0e0' : '';
        }
        const underlineBtn = this.toolbar.querySelector('button[title*="chân"], button[title*="Underline"]');
        if (underlineBtn) {
            underlineBtn.style.backgroundColor = document.queryCommandState('underline') ? '#e0e0e0' : '';
        }
    }
    
    syncEditor() {
        this.textarea.value = this.editor.innerHTML;
    }
}

// Global functions for backward compatibility with onclick handlers
window.applyFormat = function(command, field) {
    if (window.editors && window.editors[field]) {
        window.editors[field].applyFormat(command);
    }
};

window.applyStyle = function(property, value, field) {
    if (window.editors && window.editors[field]) {
        window.editors[field].applyStyle(property, value);
    }
};

window.syncEditor = function(field) {
    if (window.editors && window.editors[field]) {
        window.editors[field].syncEditor();
    }
};

document.addEventListener('DOMContentLoaded', function () {
    window.editors = {};
    window.editors.title = new RichTextEditor('title');

    // Style card choices dynamically based on click
    const cards = document.querySelectorAll('.display-type-card');
    cards.forEach(card => {
        card.addEventListener('click', function () {
            cards.forEach(c => c.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (window.editors && window.editors.title) {
                window.editors.title.syncEditor();
            }
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
});
</script>

<style>
/* Rich Text Editor Styles */
.custom-editor-toolbar {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px 8px 0 0;
    border-bottom: 2px solid #e2e8f0;
    flex-wrap: wrap;
}

.toolbar-group {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 0 8px;
    border-right: 1px solid #cbd5e1;
}

.toolbar-group:last-child {
    border-right: none;
}

.toolbar-group button {
    width: 36px;
    height: 36px;
    border: 1px solid #cbd5e1;
    background: white;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #475569;
    font-size: 14px;
    transition: all 0.2s;
}

.toolbar-group button:hover {
    background: #f1f5f9;
    border-color: #94a3b8;
    color: #1e293b;
    transform: translateY(-1px);
}

.font-select {
    padding: 8px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    background: white;
    color: #475569;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s;
}

.color-picker-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.color-picker-wrapper input[type="color"] {
    width: 36px;
    height: 36px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    cursor: pointer;
    background: white;
    padding: 0;
}

.color-picker-wrapper i {
    position: absolute;
    pointer-events: none;
    color: #475569;
    font-size: 14px;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    opacity: 0.5;
}

.custom-editable-area {
    min-height: 120px;
    padding: 16px;
    background: #ffffff;
    border: 1px solid #d1d5db;
    border-radius: 0 0 8px 8px;
    font-size: 16px;
    line-height: 1.6;
    color: #374151;
    transition: border-color 0.3s ease;
    outline: none;
}

.custom-editable-area:focus {
    border-color: #356df1;
    box-shadow: 0 0 0 3px rgba(53, 109, 241, 0.1);
}

/* Custom robust flex layout for choice cards */
.display-type-row {
    display: flex !important;
    flex-wrap: wrap !important;
    gap: 16px !important;
    margin-bottom: 24px !important;
}

.display-type-col {
    flex: 1 1 calc(25% - 12px) !important;
    min-width: 150px !important;
    max-width: 100% !important;
}

@media (max-width: 991px) {
    .display-type-col {
        flex: 1 1 calc(50% - 8px) !important;
    }
}

@media (max-width: 576px) {
    .display-type-col {
        flex: 1 1 100% !important;
    }
}

.display-type-card {
    border: 2px solid #e5e7eb !important;
    border-radius: 12px !important;
    padding: 20px 16px !important;
    background-color: #ffffff !important;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
    cursor: pointer !important;
    height: 100% !important;
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    justify-content: center !important;
    position: relative !important;
    text-align: center !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.02) !important;
}

.display-type-card:hover:not(.active) {
    border-color: #356df1 !important;
    background-color: #f8fafc !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05) !important;
}

.display-type-card.active {
    border-color: #356df1 !important;
    background-color: #eff6ff !important;
    box-shadow: 0 8px 20px rgba(53, 109, 241, 0.15) !important;
}

.cursor-pointer {
    cursor: pointer !important;
}
.form-switch-lg .form-check-input {
    width: 2.25em !important;
    height: 1.25em !important;
}
.bg-soft-danger { background-color: #fef2f2; }
.bg-soft-success { background-color: #f0fdf4; }
.bg-soft-warning { background-color: #fffbeb; }
.bg-soft-info { background-color: #f0f9ff; }
.text-danger { color: #dc2626 !important; }
.text-success { color: #16a34a !important; }
.text-warning { color: #ca8a04 !important; }
.text-info { color: #0284c7 !important; }
</style>
