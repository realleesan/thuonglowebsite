<?php
/**
 * Admin Affiliate Content Edit
 * Edit dynamic agent page content with a premium text editor allowing mixed text and images.
 */

// Choose admin service
$service = isset($currentService) ? $currentService : ($adminService ?? null);

if ($service === null) {
    die('Error: AdminService not available.');
}

require_once __DIR__ . '/../../../models/AgentContentModel.php';
$agentContentModel = new AgentContentModel();

$key = $_GET['key'] ?? '';
if (empty($key)) {
    header('Location: ?page=admin&module=affiliates&action=content&error=invalid_key');
    exit;
}

$page = $agentContentModel->getByPageKey($key);
if (!$page) {
    header('Location: ?page=admin&module=affiliates&action=content&error=not_found');
    exit;
}

$error = $_GET['error'] ?? '';
?>

<div class="affiliates-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-edit"></i>
                Chỉnh Sửa Trang: <?= htmlspecialchars($page['title']) ?>
            </h1>
            <p class="page-description">Cấu hình chi tiết nội dung, hình ảnh và SEO cho trang đại lý</p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=affiliates&action=content" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                <strong>Lỗi:</strong> <?= htmlspecialchars(urldecode($error)) ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Form Container -->
    <div class="form-container" style="background: white; padding: 28px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <form method="POST" action="?page=admin&module=affiliates&action=content_edit&key=<?= htmlspecialchars($key) ?>" class="admin-form" enctype="multipart/form-data">
            <input type="hidden" name="page_key" value="<?= htmlspecialchars($key) ?>">
            
            <div class="form-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 28px;">
                <!-- Left Column: Title & Content -->
                <div class="form-column">
                    <!-- Title -->
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="title" class="required" style="font-weight: 600; color: #374151; display: block; margin-bottom: 8px;">Tên trang / Tiêu đề chính:</label>
                        <input type="text" id="title" name="title" value="<?= htmlspecialchars($page['title']) ?>" 
                               placeholder="Nhập tên trang nội dung..." required
                               style="width: 100%; padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 15px;">
                        <small style="color: #6b7280; display: block; margin-top: 4px;">Tiêu đề chính sẽ hiển thị nổi bật ở đầu trang nội dung phía người dùng.</small>
                    </div>

                    <!-- Subtitle -->
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="subtitle" style="font-weight: 600; color: #374151; display: block; margin-bottom: 8px;">Tiêu đề phụ (Subtitle):</label>
                        <input type="text" id="subtitle" name="subtitle" value="<?= htmlspecialchars($page['subtitle'] ?? '') ?>" 
                               placeholder="Nhập tiêu đề phụ dưới tiêu đề chính..."
                               style="width: 100%; padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 15px;">
                        <small style="color: #6b7280; display: block; margin-top: 4px;">Tiêu đề phụ sẽ hiển thị nhỏ hơn ở ngay bên dưới tiêu đề chính.</small>
                    </div>

                    <!-- Mixed Text and Image Editor -->
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="required" style="font-weight: 600; color: #374151; display: block; margin-bottom: 8px;">Nội dung trang (Hỗ trợ xen kẽ Văn bản và Hình ảnh):</label>
                        
                        <!-- Rich Text Editor Toolbar -->
                        <div class="custom-editor-toolbar" style="background: #f9fafb; border: 1px solid #d1d5db; border-bottom: none; border-radius: 8px 8px 0 0; padding: 12px; display: flex; flex-wrap: wrap; gap: 12px; align-items: center;">
                            <div class="toolbar-group" style="display: flex; gap: 4px; border-right: 1px solid #e5e7eb; padding-right: 12px;">
                                <button type="button" class="tb-btn" onclick="applyFormat('bold')" title="In đậm" style="background: white; border: 1px solid #d1d5db; border-radius: 6px; width: 34px; height: 34px; cursor: pointer; color: #4b5563;"><i class="fas fa-bold"></i></button>
                                <button type="button" class="tb-btn" onclick="applyFormat('italic')" title="In nghiêng" style="background: white; border: 1px solid #d1d5db; border-radius: 6px; width: 34px; height: 34px; cursor: pointer; color: #4b5563;"><i class="fas fa-italic"></i></button>
                                <button type="button" class="tb-btn" onclick="applyFormat('underline')" title="Gạch chân" style="background: white; border: 1px solid #d1d5db; border-radius: 6px; width: 34px; height: 34px; cursor: pointer; color: #4b5563;"><i class="fas fa-underline"></i></button>
                            </div>
                            
                            <div class="toolbar-group" style="display: flex; gap: 8px; border-right: 1px solid #e5e7eb; padding-right: 12px; align-items: center;">
                                <select onchange="applyStyle('fontFamily', this.value)" style="padding: 6px 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px; color: #374151;">
                                    <option value="">Font chữ</option>
                                    <option value="Arial, sans-serif">Arial</option>
                                    <option value="'Inter', sans-serif">Inter</option>
                                    <option value="'Roboto', sans-serif">Roboto</option>
                                    <option value="Helvetica, sans-serif">Helvetica</option>
                                    <option value="Georgia, serif">Georgia</option>
                                    <option value="monospace">Monospace</option>
                                </select>
                                <div style="display: flex; align-items: center; gap: 4px; background: white; border: 1px solid #d1d5db; border-radius: 6px; padding: 0 8px; height: 34px;">
                                    <input type="number" value="16" min="12" max="72" onchange="applyStyle('fontSize', this.value + 'px')" style="border: none; width: 40px; outline: none; font-size: 13px; font-weight: 500; text-align: center;">
                                    <span style="font-size: 11px; color: #9ca3af;">px</span>
                                </div>
                            </div>
                            
                            <div class="toolbar-group" style="display: flex; gap: 4px; border-right: 1px solid #e5e7eb; padding-right: 12px;">
                                <button type="button" class="tb-btn" onclick="applyFormat('justifyLeft')" title="Căn trái" style="background: white; border: 1px solid #d1d5db; border-radius: 6px; width: 34px; height: 34px; cursor: pointer; color: #4b5563;"><i class="fas fa-align-left"></i></button>
                                <button type="button" class="tb-btn" onclick="applyFormat('justifyCenter')" title="Căn giữa" style="background: white; border: 1px solid #d1d5db; border-radius: 6px; width: 34px; height: 34px; cursor: pointer; color: #4b5563;"><i class="fas fa-align-center"></i></button>
                                <button type="button" class="tb-btn" onclick="applyFormat('justifyRight')" title="Căn phải" style="background: white; border: 1px solid #d1d5db; border-radius: 6px; width: 34px; height: 34px; cursor: pointer; color: #4b5563;"><i class="fas fa-align-right"></i></button>
                            </div>

                            <div class="toolbar-group" style="display: flex; gap: 4px; border-right: 1px solid #e5e7eb; padding-right: 12px;">
                                <button type="button" class="tb-btn" onclick="applyFormat('insertUnorderedList')" title="Danh sách gạch đầu dòng" style="background: white; border: 1px solid #d1d5db; border-radius: 6px; width: 34px; height: 34px; cursor: pointer; color: #4b5563;"><i class="fas fa-list-ul"></i></button>
                                <button type="button" class="tb-btn" onclick="applyFormat('insertOrderedList')" title="Danh sách số thứ tự" style="background: white; border: 1px solid #d1d5db; border-radius: 6px; width: 34px; height: 34px; cursor: pointer; color: #4b5563;"><i class="fas fa-list-ol"></i></button>
                            </div>

                            <!-- Premium Image Insertion Feature -->
                            <div class="toolbar-group" style="display: flex; gap: 8px;">
                                <button type="button" class="tb-btn btn-success" onclick="triggerEditorImageUpload()" title="Tải & Chèn hình ảnh xen kẽ văn bản" style="background: #10b981; border: 1px solid #059669; border-radius: 6px; padding: 0 12px; height: 34px; cursor: pointer; color: white; display: flex; align-items: center; gap: 6px; font-weight: 500; font-size: 13px;">
                                    <i class="fas fa-image"></i> Chèn Hình Ảnh
                                </button>
                                <input type="file" id="editor-image-file" style="display: none;" accept="image/*" onchange="uploadAndInsertImage(this)">
                                <button type="button" class="tb-btn" onclick="applyFormat('removeFormat')" title="Xóa định dạng" style="background: white; border: 1px solid #d1d5db; border-radius: 6px; width: 34px; height: 34px; cursor: pointer; color: #ef4444;"><i class="fas fa-eraser"></i></button>
                            </div>
                        </div>

                        <!-- Editable Workspace -->
                        <div id="rich-editor" class="custom-editable-area" contenteditable="true" oninput="syncEditorContent()" 
                             style="min-height: 450px; border: 1px solid #d1d5db; border-radius: 0 0 8px 8px; padding: 20px; outline: none; background: white; font-size: 16px; line-height: 1.6; overflow-y: auto;">
                            <?= $page['content'] ?>
                        </div>
                        
                        <!-- Real hidden textarea for form submit -->
                        <textarea id="page-content-textarea" name="content" style="display: none;"><?= htmlspecialchars($page['content']) ?></textarea>
                        <small style="color: #6b7280; display: block; margin-top: 6px;">Bạn có thể viết văn bản, sau đó nhấn "Chèn Hình Ảnh" để chọn ảnh tải lên, hình ảnh sẽ xuất hiện ngay tại vị trí con trỏ chuột của bạn.</small>
                    </div>
                </div>

                <!-- Right Column: Banner Image & SEO Settings -->
                <div class="form-column">
                    <!-- Featured Page Banner -->
                    <div class="form-section" style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 24px;">
                        <h3 style="font-size: 16px; font-weight: 600; color: #111827; margin-top: 0; margin-bottom: 16px; border-bottom: 1px solid #e5e7eb; padding-bottom: 10px;">
                            <i class="fas fa-image" style="color: #3b82f6; margin-right: 8px;"></i>Ảnh Banner Trang
                        </h3>
                        <div class="form-group">
                            <label style="font-weight: 500; font-size: 14px; color: #4b5563; display: block; margin-bottom: 8px;">Tải lên ảnh mới:</label>
                            <div class="banner-preview" id="bannerPreviewContainer" style="background: #e5e7eb; border-radius: 8px; height: 160px; display: flex; align-items: center; justify-content: center; overflow: hidden; margin-bottom: 12px; cursor: pointer;" onclick="document.getElementById('image').click()">
                                <?php if (!empty($page['image'])): ?>
                                    <img id="banner-preview-img" src="<?= htmlspecialchars($page['image']) ?>" alt="Banner Image" style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <div id="banner-placeholder" style="text-align: center; color: #6b7280;">
                                        <i class="fas fa-upload" style="font-size: 28px; margin-bottom: 8px; display: block;"></i>
                                        <span style="font-size: 13px;">Nhấp để tải lên ảnh banner</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <input type="file" id="image" name="image" accept="image/*" style="display: none;" onchange="previewBannerImage(this)">
                            <input type="hidden" name="current_image" value="<?= htmlspecialchars($page['image'] ?? '') ?>">
                            <small style="color: #6b7280; display: block;">Kích thước tối ưu: 1200x400px. Định dạng JPG, PNG, WEBP.</small>
                        </div>
                    </div>

                    <!-- SEO Section -->
                    <div class="form-section" style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px;">
                        <h3 style="font-size: 16px; font-weight: 600; color: #111827; margin-top: 0; margin-bottom: 16px; border-bottom: 1px solid #e5e7eb; padding-bottom: 10px;">
                            <i class="fas fa-search-plus" style="color: #10b981; margin-right: 8px;"></i>Tối Ưu Hóa SEO
                        </h3>
                        
                        <div class="form-group" style="margin-bottom: 14px;">
                            <label for="meta_title" style="font-weight: 500; font-size: 13px; color: #4b5563; display: block; margin-bottom: 6px;">Meta Title:</label>
                            <input type="text" id="meta_title" name="meta_title" value="<?= htmlspecialchars($page['meta_title'] ?? '') ?>" 
                                   placeholder="Nhập tiêu đề SEO..."
                                   style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
                        </div>

                        <div class="form-group" style="margin-bottom: 14px;">
                            <label for="meta_description" style="font-weight: 500; font-size: 13px; color: #4b5563; display: block; margin-bottom: 6px;">Meta Description:</label>
                            <textarea id="meta_description" name="meta_description" rows="4" placeholder="Nhập mô tả ngắn cho kết quả tìm kiếm..."
                                      style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; resize: none;"><?= htmlspecialchars($page['meta_description'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions" style="margin-top: 28px; border-top: 1px solid #e5e7eb; padding-top: 20px; display: flex; justify-content: flex-end; gap: 12px;">
                <a href="?page=admin&module=affiliates&action=content" class="btn btn-secondary" style="border: 1px solid #d1d5db; background: white; color: #374151; padding: 10px 20px; border-radius: 8px; font-weight: 500; text-decoration: none; cursor: pointer;">
                    Hủy bỏ
                </a>
                <button type="submit" class="btn btn-primary" style="background: #007bff; border: 1px solid #0056b3; color: white; padding: 10px 24px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-save"></i> Lưu Thay Đổi
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Format Text command
function applyFormat(command) {
    const editor = document.getElementById('rich-editor');
    editor.focus();
    document.execCommand(command, false, null);
    syncEditorContent();
}

// Apply Specific Style
function applyStyle(property, value) {
    const editor = document.getElementById('rich-editor');
    editor.focus();
    
    const selection = window.getSelection();
    if (!selection.rangeCount) return;
    
    if (property === 'fontSize' || property === 'fontFamily') {
        const range = selection.getRangeAt(0);
        if (range.collapsed) return;
        
        const span = document.createElement('span');
        span.style[property] = value;
        range.surroundContents(span);
    } else {
        document.execCommand(property, false, value);
    }
    syncEditorContent();
}

// Sync Content to Textarea
function syncEditorContent() {
    const editor = document.getElementById('rich-editor');
    const textarea = document.getElementById('page-content-textarea');
    textarea.value = editor.innerHTML;
}

// Trigger upload file dialog
function triggerEditorImageUpload() {
    document.getElementById('editor-image-file').click();
}

// AJAX Upload and Insert Image to cursor position
function uploadAndInsertImage(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Show loading notification
        const originalBtnText = document.querySelector('[onclick="triggerEditorImageUpload()"]').innerHTML;
        document.querySelector('[onclick="triggerEditorImageUpload()"]').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang tải lên...';
        document.querySelector('[onclick="triggerEditorImageUpload()"]').disabled = true;

        const formData = new FormData();
        formData.append('upload_file', file);

        // Fetch API to upload image asynchronously
        fetch('?page=admin&module=affiliates&action=upload_editor_image', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Restore button
            document.querySelector('[onclick="triggerEditorImageUpload()"]').innerHTML = originalBtnText;
            document.querySelector('[onclick="triggerEditorImageUpload()"]').disabled = false;
            
            if (data.success) {
                // Focus editor
                const editor = document.getElementById('rich-editor');
                editor.focus();
                
                // Construct clean premium responsive image
                const imgHtml = `<img src="${data.url}" alt="Hình ảnh nội dung" style="max-width: 100%; height: auto; display: block; margin: 16px auto; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">`;
                
                // Insert at cursor
                document.execCommand('insertHTML', false, imgHtml);
                syncEditorContent();
            } else {
                alert('Tải hình ảnh thất bại: ' + (data.message || 'Lỗi không xác định'));
            }
        })
        .catch(err => {
            document.querySelector('[onclick="triggerEditorImageUpload()"]').innerHTML = originalBtnText;
            document.querySelector('[onclick="triggerEditorImageUpload()"]').disabled = false;
            console.error(err);
            alert('Đã xảy ra lỗi mạng trong quá trình upload ảnh');
        });
        
        // Reset file input
        input.value = '';
    }
}

// Preview Uploaded Page Banner
function previewBannerImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var container = document.getElementById('bannerPreviewContainer');
            container.innerHTML = '<img id="banner-preview-img" src="' + e.target.result + '" alt="Banner Preview" style="width: 100%; height: 100%; object-fit: cover;">';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Ensure form syncs before sending
document.querySelector('form').addEventListener('submit', function() {
    syncEditorContent();
});
</script>

<style>
.custom-editor-toolbar button:hover {
    background: #e5e7eb !important;
    color: #111827 !important;
}
.custom-editor-toolbar button.btn-success:hover {
    background: #059669 !important;
    color: white !important;
}
.custom-editable-area img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin: 16px auto;
    display: block;
}
.banner-preview:hover {
    opacity: 0.85;
}
</style>
