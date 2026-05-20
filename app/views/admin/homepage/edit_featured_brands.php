<?php
/**
 * Edit Featured Brands Section View - Enhanced Custom Editor
 */

// Ensure section data is available
if (!isset($featuredBrandsSection)) {
    $featuredBrandsSection = [];
}

// Set default values
$featuredBrandsSection = array_merge([
    'id' => 0,
    'title' => '<h2 class="section-title">Thương hiệu <span class="highlight">Nổi bật</span></h2>',
    'is_active' => 1
], $featuredBrandsSection);

// Get flash messages
$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);
?>

<div class="hero-section-page hero-section-edit-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-tag"></i>
                Chỉnh sửa Section Thương hiệu Nổi bật
            </h1>
            <p class="page-description">Tùy chỉnh tiêu đề và hiển thị section thương hiệu nổi bật trên trang chủ.</p>
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
            <form id="featuredBrandsForm" method="POST">
                <input type="hidden" name="id" value="<?php echo $featuredBrandsSection['id']; ?>">
                
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
                        <?php echo $featuredBrandsSection['title']; ?>
                    </div>
                    <textarea id="title" name="title" style="display:none;"><?php echo htmlspecialchars($featuredBrandsSection['title']); ?></textarea>
                </div>

                <div class="form-check mb-4">
                    <input type="checkbox" id="is_active" name="is_active" value="1" <?php echo ($featuredBrandsSection['is_active'] ? 'checked' : ''); ?>>
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
            document.execCommand('removeFormat', false, null);
            this.syncEditor();
        } catch (e) {
            console.error('Clear format error:', e);
        }
    }
    
    syncEditor() {
        this.textarea.value = this.editor.innerHTML;
    }
    
    handlePaste(e) {
        e.preventDefault();
        const text = e.clipboardData.getData('text/html') || e.clipboardData.getData('text/plain');
        document.execCommand('insertHTML', false, text);
        this.syncEditor();
    }
    
    handleKeydown(e) {
        // Handle tab key
        if (e.key === 'Tab') {
            e.preventDefault();
            document.execCommand('insertText', false, '\t');
        }
        
        // Handle common shortcuts
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
        // Update toolbar button states based on current selection
        try {
            const isBold = document.queryCommandState('bold');
            const isItalic = document.queryCommandState('italic');
            const isUnderline = document.queryCommandState('underline');
            
            // Update button states
            if (this.toolbar) {
                const boldBtn = this.toolbar.querySelector('button[onclick*="bold"]');
                const italicBtn = this.toolbar.querySelector('button[onclick*="italic"]');
                const underlineBtn = this.toolbar.querySelector('button[onclick*="underline"]');
                
                if (boldBtn) boldBtn.classList.toggle('active', isBold);
                if (italicBtn) italicBtn.classList.toggle('active', isItalic);
                if (underlineBtn) underlineBtn.classList.toggle('active', isUnderline);
            }
        } catch (e) {
            // Ignore errors
        }
    }
}

// Global functions for onclick handlers
function applyFormat(format, fieldId) {
    const editor = window[`editor_${fieldId}`];
    if (editor) {
        editor.applyFormat(format);
    }
}

function applyStyle(property, value, fieldId) {
    const editor = window[`editor_${fieldId}`];
    if (editor) {
        editor.applyStyle(property, value);
    }
}

function syncEditor(fieldId) {
    const editor = window[`editor_${fieldId}`];
    if (editor) {
        editor.syncEditor();
    }
}

// Initialize editors when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize title editor
    window.editor_title = new RichTextEditor('title');
    
    // Form submission
    const form = document.getElementById('featuredBrandsForm');
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        // Show loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang lưu...';
        
        fetch('?page=admin&module=homepage&action=update_featured_brands', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('success', data.message);
                setTimeout(() => {
                    window.location.href = '?page=admin&module=homepage';
                }, 1500);
            } else {
                showNotification('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'Có lỗi xảy ra khi kết nối đến server');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });
    
    // Helper function to show notifications
    function showNotification(type, message) {
        // Remove existing notifications
        const existingAlert = document.querySelector('.alert-notification');
        if (existingAlert) {
            existingAlert.remove();
        }

        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show alert-notification`;
        alertDiv.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.parentNode.removeChild(alertDiv);
            }
        }, 5000);
    }
});
</script>

