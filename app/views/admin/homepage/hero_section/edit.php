<?php
/**
 * Edit Hero Section View - Enhanced Custom Editor
 */

// Ensure heroSection is available (passed from controller)
if (!isset($heroSection)) {
    $heroSection = [];
}

// Set default values for heroSection fields to prevent undefined variable errors
$heroSection = array_merge([
    'id' => 0,
    'title_main' => '',
    'subtitle' => '',
    'image_url' => '',
    'background_color' => '#ffffff',
    'is_active' => 0,
    'buttons' => []
], $heroSection);

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
                            
                            <!-- Custom Toolbar for Title -->
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
                            
                            <!-- Enhanced Custom Toolbar for Subtitle -->
                            <div class="custom-editor-toolbar" data-for="subtitle">
                                <div class="toolbar-group">
                                    <button type="button" onclick="applyFormat('bold', 'subtitle')" title="In đậm"><i class="fas fa-bold"></i></button>
                                    <button type="button" onclick="applyFormat('italic', 'subtitle')" title="In nghiêng"><i class="fas fa-italic"></i></button>
                                    <button type="button" onclick="applyFormat('underline', 'subtitle')" title="Gạch chân"><i class="fas fa-underline"></i></button>
                                </div>
                                <div class="toolbar-group">
                                    <select onchange="applyStyle('fontFamily', this.value, 'subtitle')" class="font-select">
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
                                        <input type="color" onchange="applyStyle('color', this.value, 'subtitle')" title="Màu chữ">
                                        <i class="fas fa-font"></i>
                                    </div>
                                    <button type="button" onclick="applyFormat('removeFormat', 'subtitle')" title="Xóa định dạng"><i class="fas fa-eraser"></i></button>
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
 * Enhanced Rich Text Editor - Modern Implementation
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
        // Initialize editor content
        if (this.textarea.value) {
            this.editor.innerHTML = this.textarea.value;
        }
        
        // Add event listeners
        this.editor.addEventListener('input', () => this.syncEditor());
        this.editor.addEventListener('paste', (e) => this.handlePaste(e));
        this.editor.addEventListener('keydown', (e) => this.handleKeydown(e));
        this.editor.addEventListener('mouseup', () => this.updateToolbarState());
        this.editor.addEventListener('keyup', () => this.updateToolbarState());
        
        // Initialize toolbar buttons
        this.initToolbar();
        
        // Auto-save to textarea
        setInterval(() => this.syncEditor(), 1000);
    }
    
    initToolbar() {
        if (!this.toolbar) return;
        
        // Bold, Italic, Underline
        this.toolbar.querySelectorAll('button').forEach(btn => {
            const onclick = btn.getAttribute('onclick');
            if (onclick && onclick.includes('applyFormat')) {
                const command = onclick.match(/'([^']+)'/)[1];
                btn.onclick = () => this.applyFormat(command);
            }
        });
        
        // Font family
        const fontSelect = this.toolbar.querySelector('select');
        if (fontSelect) {
            fontSelect.onchange = () => {
                if (fontSelect.value) {
                    this.applyStyle('fontFamily', fontSelect.value);
                }
            };
        }
        
                
        // Color
        const colorInput = this.toolbar.querySelector('input[type="color"]');
        if (colorInput) {
            colorInput.onchange = () => {
                this.applyStyle('color', colorInput.value);
            };
        }
        
        // Remove format
        const clearBtns = this.toolbar.querySelectorAll('button');
        clearBtns.forEach(btn => {
            if (btn.getAttribute('onclick') && btn.getAttribute('onclick').includes('removeFormat')) {
                btn.onclick = () => this.clearFormat();
            }
        });
    }
    
    applyFormat(command) {
        this.editor.focus();
        
        try {
            // Use built-in execCommand for reliable toggle behavior
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
    
    applyStyleToEditor(property, value) {
        const content = this.editor.innerHTML;
        
        const wrapper = document.createElement('div');
        wrapper.innerHTML = content;
        
        const span = document.createElement('span');
        span.style[property] = value;
        
        while (wrapper.firstChild) {
            span.appendChild(wrapper.firstChild);
        }
        
        this.editor.innerHTML = '';
        this.editor.appendChild(span);
    }
    
    applyStyleToSelection(property, value) {
        const selection = window.getSelection();
        if (!selection.rangeCount) return;
        
        const range = selection.getRangeAt(0);
        const contents = range.extractContents();
        
        const walker = document.createTreeWalker(
            contents,
            NodeFilter.SHOW_TEXT | NodeFilter.SHOW_ELEMENT,
            null,
            false
        );
        
        const nodes = [];
        let node;
        while (node = walker.nextNode()) {
            nodes.push(node);
        }
        
        nodes.forEach(node => {
            if (node.nodeType === Node.TEXT_NODE) {
                const span = document.createElement('span');
                span.style[property] = value;
                span.textContent = node.textContent;
                node.parentNode.replaceChild(span, node);
            }
        });
        
        range.insertNode(contents);
    }
    
    removeStyle(property) {
        this.editor.focus();
        
        try {
            const selection = window.getSelection();
            if (!selection.rangeCount) return;
            
            const range = selection.getRangeAt(0);
            
            if (range.collapsed) {
                this.removeStyleFromEditor(property);
            } else {
                this.removeStyleFromSelection(property);
            }
            
            this.syncEditor();
        } catch (e) {
            console.error('Remove style error:', e);
        }
    }
    
    removeStyleFromEditor(property) {
        const elements = this.editor.querySelectorAll('*');
        elements.forEach(element => {
            if (element.style[property]) {
                element.style.removeProperty(property);
            }
        });
        
        this.cleanUpEmptySpans();
    }
    
    removeStyleFromSelection(property) {
        const selection = window.getSelection();
        if (!selection.rangeCount) return;
        
        const range = selection.getRangeAt(0);
        const contents = range.extractContents();
        
        const walker = document.createTreeWalker(
            contents,
            NodeFilter.SHOW_ELEMENT,
            null,
            false
        );
        
        const nodes = [];
        let node;
        while (node = walker.nextNode()) {
            nodes.push(node);
        }
        
        nodes.forEach(node => {
            if (node.style[property]) {
                node.style.removeProperty(property);
            }
        });
        
        range.insertNode(contents);
    }
    
    cleanUpEmptySpans() {
        const emptySpans = this.editor.querySelectorAll('span');
        emptySpans.forEach(span => {
            if (!span.style.cssText && span.textContent.trim() === '') {
                span.remove();
            } else if (!span.style.cssText) {
                while (span.firstChild) {
                    span.parentNode.insertBefore(span.firstChild, span);
                }
                span.remove();
            }
        });
    }
    
    clearFormat() {
        this.editor.focus();
        
        try {
            const selection = window.getSelection();
            if (!selection.rangeCount) return;
            
            const range = selection.getRangeAt(0);
            
            if (range.collapsed) {
                this.clearFormatFromEditor();
            } else {
                this.clearFormatFromSelection();
            }
            
            this.syncEditor();
            this.updateToolbarState();
        } catch (e) {
            console.error('Clear format error:', e);
        }
    }
    
    clearFormatFromEditor() {
        const formattedElements = this.editor.querySelectorAll('strong, em, u, span');
        
        formattedElements.forEach(element => {
            while (element.firstChild) {
                element.parentNode.insertBefore(element.firstChild, element);
            }
            element.remove();
        });
        
        this.syncEditor();
    }
    
    clearFormatFromSelection() {
        const selection = window.getSelection();
        if (!selection.rangeCount) return;
        
        const range = selection.getRangeAt(0);
        const contents = range.extractContents();
        
        const formattedElements = contents.querySelectorAll('strong, em, u, span');
        
        formattedElements.forEach(element => {
            while (element.firstChild) {
                element.parentNode.insertBefore(element.firstChild, element);
            }
            element.remove();
        });
        
        range.insertNode(contents);
        this.syncEditor();
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
            
            // Update font family select
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
            
                        
            // Update color picker
            const colorInput = this.toolbar.querySelector('input[type="color"]');
            if (colorInput && selectedElement) {
                // Try to get color from style attribute first (for HEX)
                const styleColor = selectedElement.style.color;
                if (styleColor && styleColor.startsWith('#')) {
                    colorInput.value = styleColor;
                } else {
                    // Fallback to computed style and convert to HEX
                    const color = window.getComputedStyle(selectedElement).color;
                    const hexColor = this.rgbToHex(color);
                    if (hexColor && hexColor.startsWith('#')) {
                        colorInput.value = hexColor;
                    }
                }
            }
            
            this.updateButtonStates(selectedElement);
            
        } catch (e) {
            console.error('Toolbar state update error:', e);
        }
    }
    
    updateButtonStates(element) {
        if (!this.toolbar) return;
        
        const boldBtn = this.toolbar.querySelector('button[title*="đậm"], button[title*="Bold"]');
        if (boldBtn) {
            const isBold = document.queryCommandState('bold');
            boldBtn.style.backgroundColor = isBold ? '#e0e0e0' : '';
        }
        
        const italicBtn = this.toolbar.querySelector('button[title*="nghiêng"], button[title*="Italic"]');
        if (italicBtn) {
            const isItalic = document.queryCommandState('italic');
            italicBtn.style.backgroundColor = isItalic ? '#e0e0e0' : '';
        }
        
        const underlineBtn = this.toolbar.querySelector('button[title*="chân"], button[title*="Underline"]');
        if (underlineBtn) {
            const isUnderline = document.queryCommandState('underline');
            underlineBtn.style.backgroundColor = isUnderline ? '#e0e0e0' : '';
        }
    }
    
    rgbToHex(rgb) {
        // If already HEX, return as-is
        if (rgb && rgb.startsWith('#')) return rgb;
        
        // If not RGB format, return default black
        if (!rgb || rgb.indexOf('rgb') !== 0) return '#000000';
        
        const values = rgb.match(/\d+/g);
        if (!values || values.length < 3) return '#000000';
        
        const r = parseInt(values[0]);
        const g = parseInt(values[1]);
        const b = parseInt(values[2]);
        
        // Ensure valid RGB values
        if (isNaN(r) || isNaN(g) || isNaN(b)) return '#000000';
        
        const hex = "#" + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
        return hex;
    }
    
    syncEditor() {
        this.textarea.value = this.editor.innerHTML;
    }
}

// Initialize editors
document.addEventListener('DOMContentLoaded', function() {
    // Store editor instances globally for debugging
    window.editors = {};
    
    // Initialize title editor
    window.editors.title_main = new RichTextEditor('title_main');
    
    // Initialize subtitle editor
    window.editors.subtitle = new RichTextEditor('subtitle');
    
    console.log('Rich text editors initialized:', Object.keys(window.editors));
});

// Legacy functions for backward compatibility
function applyFormat(command, field) {
    const editor = window.editors?.[field];
    if (editor) {
        editor.applyFormat(command);
    }
}

function applyStyle(property, value, field) {
    const editor = window.editors?.[field];
    if (editor) {
        editor.applyStyle(property, value);
    }
}

function syncEditor(field) {
    const editor = window.editors?.[field];
    if (editor) {
        editor.syncEditor();
    }
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
    
    // Sync editors before submit
    if (window.editors.title_main) window.editors.title_main.syncEditor();
    if (window.editors.subtitle) window.editors.subtitle.syncEditor();
    
    const data = {
        title_main: document.getElementById('editor-title_main').innerHTML,
        subtitle: document.getElementById('editor-subtitle').innerHTML,
        image_url: document.getElementById('image_url').value,
        background_color: document.getElementById('background_color').value,
        is_active: document.getElementById('is_active').checked ? 1 : 0,
        title_highlight: '',
        text_color: '#333333',
        highlight_color: '#356DF1',
        font_family: 'Arial, sans-serif'
    };
    
    console.log('Submitting data:', data);
    
    fetch('?page=admin&module=hero-section&action=update&id=<?php echo $heroSection['id']; ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(d => {
        console.log('Response:', d);
        if (d.success) {
            alert('Đã cập nhật Hero Section thành công!');
        } else {
            alert('Lỗi: ' + d.message);
        }
    })
    .catch(err => {
        console.error('Submit error:', err);
        alert('Lỗi kết nối: ' + err.message);
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
.size-input { border: none; width: 60px; padding: 6px 4px; font-size: 14px; text-align: center; outline: none; font-weight: 600; }
.size-input-wrapper span { font-size: 12px; color: #888; font-weight: 500; }

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
