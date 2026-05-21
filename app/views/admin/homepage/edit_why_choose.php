<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1 fw-bold text-dark">
                <i class="fas fa-question-circle text-primary me-2"></i>
                <?php echo $title ?? 'Chỉnh sửa Section Tại sao chọn ThuongLo?'; ?>
            </h2>
            <p class="text-muted mb-0">Tùy chỉnh tiêu đề chính và quản lý các khối thông tin lý do chọn ThuongLo</p>
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
            <form id="whyChooseForm" method="POST" action="?page=admin&module=homepage&action=update_why_choose">
                <input type="hidden" name="id" value="<?php echo $whyChooseSection['id'] ?? 0; ?>">
                
                <!-- Main Section Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="fas fa-cog me-2 text-secondary"></i>
                            Cấu hình chung Section
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-heading me-1 text-primary"></i>
                                Tiêu đề chính Section
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
                                            <option value="'Outfit', sans-serif">Outfit</option>
                                            <option value="'Roboto', sans-serif">Roboto</option>
                                            <option value="Georgia, serif">Georgia</option>
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
                                    <?php echo $whyChooseSection['title'] ?? ''; ?>
                                </div>
                                
                                <textarea name="title" id="titleInput" style="display: none;"><?php echo htmlspecialchars($whyChooseSection['title'] ?? ''); ?></textarea>
                            </div>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1 text-info"></i>
                                Hướng dẫn highlight: Bôi đen chữ (ví dụ: <b>ThuongLo?</b>) và chọn màu cam/highlight hoặc cấu hình HTML tùy ý.
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-eye me-1 text-success"></i>
                                        Trạng thái hiển thị
                                    </label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="isActive" 
                                               value="1" <?php echo ($whyChooseSection['is_active'] ?? 1) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="isActive">
                                            Hiển thị section này trên trang chủ
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Items Block Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="fas fa-list me-2 text-primary"></i>
                            Các khối thông tin lý do
                        </h5>
                        <button type="button" class="btn btn-sm btn-primary btn-add-block" onclick="addNewBlock()">
                            <i class="fas fa-plus me-1"></i> Thêm khối mới
                        </button>
                    </div>
                    <div class="card-body bg-light-soft p-3">
                        <div id="blocksContainer" class="row g-3">
                            <?php 
                            $items = $whyChooseSection['items'] ?? [];
                            foreach ($items as $index => $item): 
                            ?>
                                <div class="col-12 block-item-card" data-index="<?php echo $index; ?>">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-header bg-white border-bottom-0 d-flex justify-content-between align-items-center py-2 pe-2">
                                            <span class="badge bg-primary text-white block-order-badge">Khối #<?php echo $index + 1; ?></span>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-light btn-move-up" onclick="moveBlockUp(this)" title="Di chuyển lên">
                                                    <i class="fas fa-chevron-up"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-light btn-move-down" onclick="moveBlockDown(this)" title="Di chuyển xuống">
                                                    <i class="fas fa-chevron-down"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-light-danger" onclick="deleteBlock(this)" title="Xóa khối">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="card-body pt-1">
                                            <input type="hidden" name="items[<?php echo $index; ?>][id]" value="<?php echo $item['id']; ?>">
                                            <input type="hidden" name="items[<?php echo $index; ?>][sort_order]" class="block-sort-order" value="<?php echo $item['sort_order']; ?>">
                                            
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold text-secondary mb-1">Tiêu đề khối</label>
                                                <input type="text" name="items[<?php echo $index; ?>][title]" class="form-control form-control-sm border-2 fw-semibold" value="<?php echo htmlspecialchars($item['title']); ?>" required>
                                            </div>
                                            <div class="mb-0">
                                                <label class="form-label small fw-bold text-secondary mb-1">Nội dung</label>
                                                <textarea name="items[<?php echo $index; ?>][content]" class="form-control form-control-sm border-2" rows="3" required><?php echo htmlspecialchars($item['content']); ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div id="noBlocksWarning" class="text-center py-4 <?php echo !empty($items) ? 'd-none' : ''; ?>">
                            <i class="fas fa-info-circle text-muted fa-2x mb-2"></i>
                            <p class="text-muted mb-0">Chưa có khối thông tin nào. Vui lòng bấm "Thêm khối mới" để thêm.</p>
                        </div>
                    </div>
                </div>

                <!-- Action Button Card -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex justify-content-between align-items-center py-3">
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i>
                            Cập nhật lần cuối: <?php echo date('d/m/Y H:i', strtotime($whyChooseSection['updated_at'] ?? 'now')); ?>
                        </small>
                        <div>
                            <a href="?page=admin&module=homepage" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-times me-1"></i> Hủy
                            </a>
                            <button type="submit" class="btn btn-primary px-4" id="saveBtn">
                                <i class="fas fa-save me-1"></i> Lưu cấu hình
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Preview Column -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-eye me-2 text-info"></i>
                        Xem trước tiêu đề
                    </h5>
                </div>
                <div class="card-body">
                    <div class="preview-section border rounded p-3 bg-light text-center">
                        <div id="previewContent" class="fs-4 fw-bold">
                            <?php echo $whyChooseSection['title'] ?? ''; ?>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="fas fa-info-circle me-1"></i>
                        Đây là cách tiêu đề chính sẽ hiển thị ở trang chủ.
                    </small>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-keyboard me-2 text-warning"></i>
                        Phím tắt hỗ trợ
                    </h5>
                </div>
                <div class="card-body">
                    <div class="small text-secondary">
                        <div class="mb-2 d-flex justify-content-between"><span>In đậm:</span><kbd>Ctrl + B</kbd></div>
                        <div class="mb-2 d-flex justify-content-between"><span>In nghiêng:</span><kbd>Ctrl + I</kbd></div>
                        <div class="mb-2 d-flex justify-content-between"><span>Gạch chân:</span><kbd>Ctrl + U</kbd></div>
                        <hr class="my-2">
                        <div class="mb-0 text-muted small">
                            <i class="fas fa-lightbulb me-1 text-warning"></i>
                            Bôi đen đoạn tiêu đề cần định dạng rồi bấm tổ hợp phím hoặc sử dụng thanh công cụ soạn thảo.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Template for new blocks -->
<template id="blockTemplate">
    <div class="col-12 block-item-card" data-index="{index}">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom-0 d-flex justify-content-between align-items-center py-2 pe-2">
                <span class="badge bg-primary text-white block-order-badge">Khối mới</span>
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-light btn-move-up" onclick="moveBlockUp(this)" title="Di chuyển lên">
                        <i class="fas fa-chevron-up"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-light btn-move-down" onclick="moveBlockDown(this)" title="Di chuyển xuống">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-light-danger" onclick="deleteBlock(this)" title="Xóa khối">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>
            <div class="card-body pt-1">
                <input type="hidden" name="items[{index}][id]" value="0">
                <input type="hidden" name="items[{index}][sort_order]" class="block-sort-order" value="{sort_order}">
                
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary mb-1">Tiêu đề khối</label>
                    <input type="text" name="items[{index}][title]" class="form-control form-control-sm border-2 fw-semibold" placeholder="Nhập tiêu đề khối..." required>
                </div>
                <div class="mb-0">
                    <label class="form-label small fw-bold text-secondary mb-1">Nội dung</label>
                    <textarea name="items[{index}][content]" class="form-control form-control-sm border-2" rows="3" placeholder="Nhập nội dung khối thông tin..." required></textarea>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
class WhyChooseEditor {
    constructor() {
        this.editor = document.getElementById('titleEditor');
        this.input = document.getElementById('titleInput');
        this.preview = document.getElementById('previewContent');
        this.saveBtn = document.getElementById('saveBtn');
        this.form = document.getElementById('whyChooseForm');
        
        this.init();
    }

    init() {
        setInterval(() => this.syncContent(), 1000);
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        this.editor.addEventListener('keydown', (e) => this.handleKeydown(e));
        this.syncContent();
    }

    syncContent() {
        const content = this.editor.innerHTML;
        this.input.value = content;
        this.preview.innerHTML = content;
    }

    handleSubmit(e) {
        e.preventDefault();
        
        // Show loading state
        this.saveBtn.disabled = true;
        this.saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Đang lưu...';
        
        // Use FormData directly to send nested array structure
        const formData = new FormData(this.form);
        
        fetch(this.form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Remove any existing alerts
            document.querySelectorAll('.alert-auto-dismiss').forEach(el => el.remove());
            
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${data.success ? 'success' : 'danger'} alert-dismissible fade show alert-auto-dismiss mb-4`;
            alertDiv.innerHTML = `
                <i class="fas fa-${data.success ? 'check-circle' : 'exclamation-circle'} me-1"></i>
                ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            this.form.prepend(alertDiv);
            
            if (data.success) {
                // Auto reload after 1.5 seconds so that the page is refreshed with saved content and new IDs
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            }
            
            window.scrollTo({ top: 0, behavior: 'smooth' });
        })
        .catch(error => {
            console.error('Error:', error);
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger alert-dismissible fade show alert-auto-dismiss mb-4';
            alertDiv.innerHTML = `
                <i class="fas fa-exclamation-circle me-1"></i>
                Có lỗi xảy ra. Vui lòng thử lại.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            this.form.prepend(alertDiv);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        })
        .finally(() => {
            this.saveBtn.disabled = false;
            this.saveBtn.innerHTML = '<i class="fas fa-save me-1"></i> Lưu cấu hình';
        });
    }

    handleKeydown(e) {
        if (e.ctrlKey || e.metaKey) {
            switch(e.key) {
                case 'b':
                case 'B':
                    e.preventDefault();
                    formatText('bold');
                    break;
                case 'i':
                case 'I':
                    e.preventDefault();
                    formatText('italic');
                    break;
                case 'u':
                case 'U':
                    e.preventDefault();
                    formatText('underline');
                    break;
            }
        }
    }
}

// Global text formatting helper functions
function formatText(command, value = null) {
    document.execCommand(command, false, value);
    document.getElementById('titleEditor').focus();
}

function changeFontFamily(font) {
    formatText('fontName', font);
}

function changeColor(color) {
    formatText('foreColor', color);
}

function removeFormat() {
    formatText('removeFormat');
}

function handlePaste(e) {
    e.preventDefault();
    const text = (e.originalEvent || e).clipboardData.getData('text/plain');
    document.execCommand('insertText', false, text);
}

// Block Item Management Logic
function updateOrderBadges() {
    const container = document.getElementById('blocksContainer');
    const cards = container.querySelectorAll('.block-item-card');
    
    if (cards.length === 0) {
        document.getElementById('noBlocksWarning').classList.remove('d-none');
    } else {
        document.getElementById('noBlocksWarning').classList.add('d-none');
    }
    
    cards.forEach((card, index) => {
        // Update title badge
        const badge = card.querySelector('.block-order-badge');
        if (badge) {
            badge.textContent = `Khối #${index + 1}`;
        }
        
        // Update sort order value
        const sortInput = card.querySelector('.block-sort-order');
        if (sortInput) {
            sortInput.value = index + 1;
        }
    });
}

function addNewBlock() {
    const container = document.getElementById('blocksContainer');
    const template = document.getElementById('blockTemplate').innerHTML;
    
    const count = container.querySelectorAll('.block-item-card').length;
    const uniqueIndex = 'new_' + Date.now();
    
    let html = template.replace(/\{index\}/g, uniqueIndex);
    html = html.replace('{sort_order}', count + 1);
    
    // Convert html string to elements
    const div = document.createElement('div');
    div.innerHTML = html.trim();
    const newCard = div.firstChild;
    
    container.appendChild(newCard);
    updateOrderBadges();
    
    // Scroll to the new block and focus its title input
    newCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    setTimeout(() => {
        newCard.querySelector('input[type="text"]').focus();
    }, 300);
}

function deleteBlock(button) {
    if (confirm('Bạn có chắc chắn muốn xóa khối thông tin này?')) {
        const card = button.closest('.block-item-card');
        card.remove();
        updateOrderBadges();
    }
}

function moveBlockUp(button) {
    const card = button.closest('.block-item-card');
    const prev = card.previousElementSibling;
    if (prev && prev.classList.contains('block-item-card')) {
        card.parentNode.insertBefore(card, prev);
        updateOrderBadges();
    }
}

function moveBlockDown(button) {
    const card = button.closest('.block-item-card');
    const next = card.nextElementSibling;
    if (next && next.classList.contains('block-item-card')) {
        card.parentNode.insertBefore(next, card);
        updateOrderBadges();
    }
}

// Instantiate editor on DOM content loaded
document.addEventListener('DOMContentLoaded', () => {
    new WhyChooseEditor();
    updateOrderBadges();
});
</script>

<style>
/* Modern styling matching the admin portal design */
.custom-editor-container {
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    background: #fff;
    overflow: hidden;
    transition: all 0.2s ease;
}
.custom-editor-container:focus-within {
    border-color: #356DF1;
    box-shadow: 0 0 0 3px rgba(53, 109, 241, 0.15);
}
.custom-editor-toolbar {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: #f8fafc;
    border-bottom: 2px solid #e2e8f0;
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
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    background: transparent;
    color: #475569;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.15s;
}
.toolbar-btn:hover {
    background: #e2e8f0;
    color: #0f172a;
}
.toolbar-divider {
    width: 1px;
    height: 20px;
    background: #cbd5e1;
    margin: 0 4px;
}
.toolbar-select {
    padding: 4px 8px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 13px;
    color: #334155;
    background: #fff;
    cursor: pointer;
}
.toolbar-color {
    width: 32px;
    height: 32px;
    padding: 0;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    cursor: pointer;
    background: transparent;
}
.custom-editable-area {
    min-height: 120px;
    padding: 16px;
    font-size: 15px;
    line-height: 1.6;
    outline: none;
    color: #1e293b;
    overflow-y: auto;
}
.bg-light-soft {
    background-color: #f8fafc !important;
    border-radius: 12px;
    border: 2px dashed #cbd5e1;
}
.block-item-card {
    transition: all 0.2s ease;
}
.block-item-card:hover {
    transform: translateY(-2px);
}
.block-item-card .card {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
}
.block-item-card .card:focus-within {
    border-color: #356DF1;
    box-shadow: 0 4px 12px rgba(53, 109, 241, 0.08);
}
.btn-light-danger {
    color: #dc2626;
    background-color: #fef2f2;
    border: none;
}
.btn-light-danger:hover {
    background-color: #dc2626;
    color: white;
}
</style>
