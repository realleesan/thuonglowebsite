<?php
/**
 * Edit Featured Products Section View - Enhanced Custom Editor
 */

// Ensure section data is available
if (!isset($featuredProductsSection)) {
    $featuredProductsSection = [];
}

// Set default values
$featuredProductsSection = array_merge([
    'id' => 0,
    'title' => '<h2 class="section-title">Sản phẩm <span class="highlight">Nổi bật</span></h2>',
    'is_active' => 1
], $featuredProductsSection);

// Get flash messages
$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);
?>

<div class="hero-section-page hero-section-edit-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-star"></i>
                Chỉnh sửa Section Sản phẩm Nổi bật
            </h1>
            <p class="page-description">Tùy chỉnh tiêu đề và hiển thị section sản phẩm nổi bật trên trang chủ.</p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=homepage" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                Quản lý Trang chủ
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
            <form id="featuredProductsForm" method="POST">
                <input type="hidden" name="id" value="<?php echo $featuredProductsSection['id']; ?>">
                
                <div class="form-group mb-4">
                    <label class="admin-label">Tiêu đề Section <span class="text-danger">*</span></label>
                    
                    <!-- Custom Toolbar for Title -->
                    <div class="custom-editor-toolbar" data-for="title">
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
                    <div id="editor-title" class="custom-editable-area" contenteditable="true" oninput="syncEditor('title')">
                        <?php echo $featuredProductsSection['title']; ?>
                    </div>
                    <textarea id="title" name="title" style="display:none;"><?php echo htmlspecialchars($featuredProductsSection['title']); ?></textarea>
                </div>

                <div class="form-check mb-4">
                    <input type="checkbox" id="is_active" name="is_active" value="1" <?php echo ($featuredProductsSection['is_active'] ? 'checked' : ''); ?>>
                    <label for="is_active" class="fw-bold">Hiển thị Section này trên trang chủ</label>
                </div>

                <div class="form-actions mt-5">
                    <button type="submit" class="btn-save-large">Lưu tất cả thay đổi</button>
                </div>
            </form>
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
    window.editors.title = new RichTextEditor('title');
    
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

// Form Submit
document.getElementById('featuredProductsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Sync editor before submit
    if (window.editors.title) window.editors.title.syncEditor();
    
    const formData = new FormData(this);
    
    fetch('?page=admin&module=homepage&action=update-featured-products', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(r => r.json())
    .then(d => {
        console.log('Response:', d);
        if (d.success) {
            alert('Đã cập nhật Section Sản phẩm Nổi bật thành công!');
            window.location.href = '?page=admin&module=homepage';
        } else {
            alert('Lỗi: ' + d.message);
        }
    })
    .catch(err => {
        console.error('Submit error:', err);
        alert('Lỗi kết nối: ' + err.message);
    });
});
</script>

<style>
.hero-section-page {
    padding: 20px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #e5e7eb;
}

.page-title {
    font-size: 28px;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

.page-description {
    color: #6b7280;
    margin: 8px 0 0 0;
    font-size: 14px;
}

.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: #f3f4f6;
    color: #374151;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s;
}

.btn-back:hover {
    background: #e5e7eb;
    color: #1f2937;
}

.admin-card {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.admin-label {
    display: block;
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
    font-size: 14px;
}

.form-check {
    display: flex;
    align-items: center;
    gap: 10px;
}

.form-check input[type="checkbox"] {
    width: 20px;
    height: 20px;
    cursor: pointer;
}

.form-actions {
    display: flex;
    gap: 15px;
    padding-top: 20px;
    border-top: 1px solid #e5e7eb;
}

.btn-save-large {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 16px 32px;
    background: #356DF1;
    color: white;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-save-large:hover {
    background: #2557d6;
    transform: translateY(-1px);
}

.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert-danger {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

.text-danger {
    color: #dc2626;
}

.text-muted {
    color: #6b7280;
    font-size: 13px;
}

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

.toolbar-group button:active {
    transform: translateY(0);
    box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
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

.font-select:focus {
    outline: none;
    border-color: #356df1;
    box-shadow: 0 0 0 3px rgba(53, 109, 241, 0.1);
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

.custom-editable-area:empty:before {
    content: attr(placeholder);
    color: #9ca3af;
    font-style: italic;
}

.custom-editable-area strong {
    font-weight: 600;
}

.custom-editable-area em {
    font-style: italic;
}

.custom-editable-area u {
    text-decoration: underline;
}

/* Responsive Design */
@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .custom-editor-toolbar {
        flex-direction: column;
        align-items: stretch;
        gap: 8px;
    }
    
    .toolbar-group {
        padding: 8px 0;
        border-right: none;
        border-bottom: 1px solid #cbd5e1;
        justify-content: center;
    }
    
    .toolbar-group:last-child {
        border-bottom: none;
    }
    
    .custom-editable-area {
        min-height: 100px;
        padding: 12px;
    }
    
    .btn-save-large {
        padding: 14px 24px;
        font-size: 14px;
    }
}

@media (max-width: 480px) {
    .hero-section-page {
        padding: 10px;
    }
    
    .admin-card {
        padding: 20px;
    }
    
    .custom-editor-toolbar {
        padding: 8px;
    }
    
    .toolbar-group button {
        width: 32px;
        height: 32px;
        font-size: 12px;
    }
    
    .custom-editable-area {
        min-height: 80px;
        padding: 10px;
        font-size: 14px;
    }
    
    .btn-save-large {
        padding: 12px 20px;
        font-size: 13px;
    }
}
</style>
