<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1 fw-bold">
                <i class="fas fa-newspaper text-info me-2"></i>
                <?php echo $title ?? 'Chỉnh sửa Section Tin tức Mới nhất'; ?>
            </h2>
            <p class="text-muted mb-0">Tùy chỉnh tiêu đề và cài đặt hiển thị cho section tin tức mới nhất</p>
        </div>
        <a href="?page=admin&module=homepage" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Quay lại
        </a>
    </div>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['flash_message_type'] ?? 'info'; ?> alert-dismissible fade show" role="alert">
            <?php 
            echo htmlspecialchars($_SESSION['flash_message']);
            unset($_SESSION['flash_message']);
            unset($_SESSION['flash_message_type']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i>
                        Chỉnh sửa Tiêu đề Section
                    </h5>
                </div>
                <div class="card-body">
                    <form id="latestNewsForm" method="POST" action="?page=admin&module=homepage&action=update_latest_news">
                        <input type="hidden" name="id" value="<?php echo $latestNewsSection['id'] ?? 0; ?>">
                        
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-heading me-1"></i>
                                Tiêu đề Section
                            </label>
                            <div class="custom-editor-container">
                                <div class="custom-editor-toolbar">
                                    <div class="toolbar-group">
                                        <button type="button" class="toolbar-btn" onclick="formatText('bold')" title="In đậm">
                                            <i class="fas fa-bold"></i>
                                        </button>
                                        <button type="button" class="toolbar-btn" onclick="formatText('italic')" title="In nghiêng">
                                            <i class="fas fa-italic"></i>
                                        </button>
                                        <button type="button" class="toolbar-btn" onclick="formatText('underline')" title="Gạch chân">
                                            <i class="fas fa-underline"></i>
                                        </button>
                                    </div>
                                    
                                    <div class="toolbar-divider"></div>
                                    
                                    <div class="toolbar-group">
                                        <select class="toolbar-select" onchange="changeFontFamily(this.value)">
                                            <option value="Arial, sans-serif">Arial</option>
                                            <option value="'Inter', sans-serif">Inter</option>
                                            <option value="'Roboto', sans-serif">Roboto</option>
                                            <option value="Georgia, serif">Georgia</option>
                                            <option value="'Courier New', monospace">Courier</option>
                                        </select>
                                        
                                        <input type="color" class="toolbar-color" onchange="changeColor(this.value)" title="Màu chữ">
                                        
                                        <button type="button" class="toolbar-btn" onclick="removeFormat()" title="Xóa định dạng">
                                            <i class="fas fa-eraser"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="custom-editable-area" 
                                     contenteditable="true" 
                                     id="titleEditor"
                                     onkeyup="syncContent()"
                                     onpaste="handlePaste(event)">
                                    <?php echo $latestNewsSection['title'] ?? ''; ?>
                                </div>
                                
                                <textarea name="title" id="titleInput" style="display: none;"><?php echo htmlspecialchars($latestNewsSection['title'] ?? ''); ?></textarea>
                            </div>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Sử dụng thanh công cụ để định dạng tiêu đề. Hỗ trợ HTML.
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-eye me-1"></i>
                                        Trạng thái hiển thị
                                    </label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="isActive" 
                                               value="1" <?php echo ($latestNewsSection['is_active'] ?? 1) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="isActive">
                                            Hiển thị section này trên trang chủ
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <div>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    Cập nhật lần cuối: <?php echo date('d/m/Y H:i', strtotime($latestNewsSection['updated_at'] ?? 'now')); ?>
                                </small>
                            </div>
                            <div>
                                <a href="?page=admin&module=homepage" class="btn btn-outline-secondary me-2">
                                    <i class="fas fa-times me-1"></i> Hủy
                                </a>
                                <button type="submit" class="btn btn-primary" id="saveBtn">
                                    <i class="fas fa-save me-1"></i> Lưu thay đổi
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0">
                        <i class="fas fa-eye me-2"></i>
                        Xem trước
                    </h6>
                </div>
                <div class="card-body">
                    <div class="preview-section border rounded p-3 bg-light">
                        <div id="previewContent">
                            <?php echo $latestNewsSection['title'] ?? ''; ?>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="fas fa-info-circle me-1"></i>
                        Đây là cách tiêu đề sẽ hiển thị trên trang chủ
                    </small>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0">
                        <i class="fas fa-keyboard me-2"></i>
                        Phím tắt
                    </h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <div class="mb-2"><kbd>Ctrl</kbd> + <kbd>B</kbd> = In đậm</div>
                        <div class="mb-2"><kbd>Ctrl</kbd> + <kbd>I</kbd> = In nghiêng</div>
                        <div class="mb-2"><kbd>Ctrl</kbd> + <kbd>U</kbd> = Gạch chân</div>
                        <div class="mb-0 text-muted">
                            <i class="fas fa-lightbulb me-1"></i>
                            Chọn văn bản rồi nhấn tổ hợp phím
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
class RichTextEditor {
    constructor() {
        this.editor = document.getElementById('titleEditor');
        this.input = document.getElementById('titleInput');
        this.preview = document.getElementById('previewContent');
        this.saveBtn = document.getElementById('saveBtn');
        this.form = document.getElementById('latestNewsForm');
        
        this.init();
    }

    init() {
        // Sync content every second
        setInterval(() => this.syncContent(), 1000);
        
        // Handle form submission
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        
        // Handle keyboard shortcuts
        this.editor.addEventListener('keydown', (e) => this.handleKeydown(e));
        
        // Initial sync
        this.syncContent();
    }

    syncContent() {
        const content = this.editor.innerHTML;
        this.input.value = content;
        this.preview.innerHTML = content;
    }

    handleSubmit(e) {
        e.preventDefault();
        
        const formData = new FormData(this.form);
        const data = Object.fromEntries(formData.entries());
        
        // Show loading state
        this.saveBtn.disabled = true;
        this.saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Đang lưu...';
        
        fetch(this.form.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-success alert-dismissible fade show';
                alertDiv.innerHTML = `
                    <i class="fas fa-check-circle me-1"></i>
                    ${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                
                this.form.parentElement.insertBefore(alertDiv, this.form);
                
                // Auto remove alert after 3 seconds
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.parentNode.removeChild(alertDiv);
                    }
                }, 3000);
                
                // Scroll to top
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                // Show error message
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                alertDiv.innerHTML = `
                    <i class="fas fa-exclamation-circle me-1"></i>
                    ${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                
                this.form.parentElement.insertBefore(alertDiv, this.form);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger alert-dismissible fade show';
            alertDiv.innerHTML = `
                <i class="fas fa-exclamation-circle me-1"></i>
                Có lỗi xảy ra. Vui lòng thử lại.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            this.form.parentElement.insertBefore(alertDiv, this.form);
        })
        .finally(() => {
            // Reset button state
            this.saveBtn.disabled = false;
            this.saveBtn.innerHTML = '<i class="fas fa-save me-1"></i> Lưu thay đổi';
        });
    }

    handleKeydown(e) {
        if (e.ctrlKey || e.metaKey) {
            switch(e.key) {
                case 'b':
                case 'B':
                    e.preventDefault();
                    this.formatText('bold');
                    break;
                case 'i':
                case 'I':
                    e.preventDefault();
                    this.formatText('italic');
                    break;
                case 'u':
                case 'U':
                    e.preventDefault();
                    this.formatText('underline');
                    break;
            }
        }
    }

    formatText(command) {
        document.execCommand(command, false, null);
        this.editor.focus();
        this.syncContent();
    }

    changeFontFamily(font) {
        document.execCommand('fontName', false, font);
        this.editor.focus();
        this.syncContent();
    }

    changeColor(color) {
        document.execCommand('foreColor', false, color);
        this.editor.focus();
        this.syncContent();
    }

    removeFormat() {
        document.execCommand('removeFormat', false, null);
        this.editor.focus();
        this.syncContent();
    }

    handlePaste(e) {
        e.preventDefault();
        const text = e.clipboardData.getData('text/plain');
        document.execCommand('insertText', false, text);
        this.syncContent();
    }
}

// Global functions for onclick handlers
function formatText(command) {
    window.editor.formatText(command);
}

function changeFontFamily(font) {
    window.editor.changeFontFamily(font);
}

function changeColor(color) {
    window.editor.changeColor(color);
}

function removeFormat() {
    window.editor.removeFormat();
}

function syncContent() {
    window.editor.syncContent();
}

function handlePaste(e) {
    window.editor.handlePaste(e);
}

// Initialize editor when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.editor = new RichTextEditor();
});
</script>

<style>
.custom-editor-container {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    overflow: hidden;
    background: white;
}

.custom-editor-toolbar {
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    padding: 8px 12px;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.toolbar-group {
    display: flex;
    align-items: center;
    gap: 4px;
}

.toolbar-btn {
    width: 32px;
    height: 32px;
    border: 1px solid #dee2e6;
    background: white;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    font-size: 14px;
    color: #495057;
}

.toolbar-btn:hover {
    background: #e9ecef;
    border-color: #adb5bd;
    color: #212529;
}

.toolbar-btn:active {
    background: #dee2e6;
    transform: translateY(1px);
}

.toolbar-divider {
    width: 1px;
    height: 24px;
    background: #dee2e6;
    margin: 0 4px;
}

.toolbar-select {
    height: 32px;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 0 8px;
    font-size: 14px;
    background: white;
    cursor: pointer;
}

.toolbar-color {
    width: 32px;
    height: 32px;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    cursor: pointer;
    padding: 0;
}

.custom-editable-area {
    min-height: 120px;
    padding: 16px;
    font-size: 16px;
    line-height: 1.5;
    outline: none;
    overflow-y: auto;
    max-height: 300px;
}

.custom-editable-area:focus {
    background: #f8f9fa;
}

.preview-section {
    min-height: 60px;
    font-size: 18px;
    font-weight: 600;
    line-height: 1.4;
}

kbd {
    background: #f1f3f4;
    border: 1px solid #dadce0;
    border-radius: 4px;
    padding: 2px 6px;
    font-size: 12px;
    font-family: monospace;
}

.card {
    transition: box-shadow 0.2s;
}

.card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
}

.btn {
    transition: all 0.2s;
}

.btn:hover {
    transform: translateY(-1px);
}

.alert {
    border: none;
    border-radius: 8px;
}

.alert-success {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    color: #155724;
}

.alert-danger {
    background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
    color: #721c24;
}
</style>
