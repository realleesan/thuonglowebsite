<?php
/**
 * Edit Budget Products Section View - Enhanced Custom Editor
 */

// Ensure section data is available
if (!isset($section)) {
    $section = [];
}

// Set default values
$section = array_merge([
    'id' => 0,
    'title' => '<h2 class="section-title">Sản phẩm <span class="highlight">Giá rẻ</span></h2>',
    'is_active' => 1,
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s')
], $section);

// Get flash messages
$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);
?>

<div class="hero-section-page hero-section-edit-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-dollar-sign"></i>
                Chỉnh sửa Section Sản phẩm Giá rẻ
            </h1>
            <p class="page-description">Tùy chỉnh tiêu đề và hiển thị section sản phẩm giá rẻ trên trang chủ.</p>
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
            <form id="budgetProductsForm" method="POST" action="?page=admin&module=homepage&action=update-budget-products">
                <input type="hidden" name="id" value="<?php echo $section['id']; ?>">
                
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
                        <?php echo $section['title']; ?>
                    </div>
                    <textarea id="title" name="title" style="display:none;"><?php echo htmlspecialchars($section['title']); ?></textarea>
                </div>

                <div class="form-check mb-4">
                    <input type="checkbox" id="is_active" name="is_active" value="1" <?php echo ($section['is_active'] ? 'checked' : ''); ?>>
                    <label for="is_active" class="fw-bold">Hiển thị Section này trên trang chủ</label>
                </div>

                <div class="form-actions mt-5">
                    <button type="submit" class="btn-save-large">Lưu tất cả thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

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
    border-color: #198754;
    box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.1);
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
    border-color: #198754;
    box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.1);
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
}

@media (max-width: 480px) {
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
}
</style>

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

document.getElementById('isActive').addEventListener('change', function() {
    const badge = this.nextElementSibling.querySelector('.badge');
    if (this.checked) {
        badge.className = 'badge bg-success ms-2';
        badge.textContent = 'Đang hiển thị';
    } else {
        badge.className = 'badge bg-secondary ms-2';
        badge.textContent = 'Đang ẩn';
    }
});

function previewTitle() {
    const title = document.getElementById('editor-title').innerHTML;
    const previewContent = document.getElementById('previewContent');
    
    if (title.trim()) {
        previewContent.innerHTML = title;
    } else {
        previewContent.innerHTML = '<span class="text-muted">Không có tiêu đề để xem trước</span>';
    }
    
    const modal = new bootstrap.Modal(document.getElementById('previewModal'));
    modal.show();
}

// Form submission with AJAX
document.getElementById('budgetProductsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang lưu...';
    
    // Sync editor before submit
    if (window.editors.title) window.editors.title.syncEditor();
    
    const formData = new FormData(this);
    
    fetch('?page=admin&module=homepage&action=update-budget-products', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showAlert('success', data.message || 'Đã cập nhật section thành công!');
            
            // Redirect after a short delay
            setTimeout(() => {
                window.location.href = '?page=admin&module=homepage';
            }, 1500);
        } else {
            showAlert('danger', data.message || 'Có lỗi xảy ra khi cập nhật section.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Có lỗi xảy ra khi kết nối đến server.');
    })
    .finally(() => {
        // Reset button state
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

function showAlert(type, message) {
    // Remove any existing alerts
    const existingAlert = document.querySelector('.alert');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    // Create new alert
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insert at the top of the card body
    const cardBody = document.querySelector('.card-body');
    cardBody.insertBefore(alert, cardBody.firstChild);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if (alert.parentNode) {
            alert.remove();
        }
    }, 5000);
}
</script>

