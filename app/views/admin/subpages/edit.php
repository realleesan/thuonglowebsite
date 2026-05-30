<?php
/**
 * Admin Sub Pages Edit
 * Edit dynamic static subpages or footer social configurations.
 */

require_once __DIR__ . '/../../../models/SubPageModel.php';
$subPageModel = new SubPageModel();

$key = $_GET['key'] ?? '';
if (empty($key)) {
    header('Location: ?page=admin&module=subpages&error=invalid_key');
    exit;
}

$page = $subPageModel->getByPageKey($key);
if (!$page) {
    header('Location: ?page=admin&module=subpages&error=not_found');
    exit;
}

$isSocial = ($key === 'footer_socials');
$error = $_GET['error'] ?? '';
?>

<div class="subpages-page" style="padding: 24px; background: #f9fafb; min-height: 100vh;">
    <!-- Page Header -->
    <div class="page-header" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e5e7eb; padding-bottom: 16px;">
        <div class="page-header-left">
            <h1 class="page-title" style="font-size: 24px; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 10px; margin: 0;">
                <i class="fas fa-edit" style="color: #f59e0b;"></i>
                Chỉnh Sửa: <?= htmlspecialchars($page['title']) ?>
            </h1>
            <p class="page-description" style="font-size: 14px; color: #6b7280; margin: 4px 0 0 0;">
                <?= $isSocial ? 'Quản lý hiển thị và đường dẫn liên kết của các biểu tượng mạng xã hội dưới chân trang' : 'Chỉnh sửa chi tiết nội dung, hình ảnh và tối ưu SEO cho trang phụ' ?>
            </p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=subpages" class="btn" style="display: inline-flex; align-items: center; gap: 6px; border: 1px solid #d1d5db; background: white; color: #374151; padding: 8px 16px; border-radius: 8px; font-weight: 600; text-decoration: none; font-size: 14px; transition: background 0.2s;" onmouseover="this.style.backgroundColor='#f3f4f6'" onmouseout="this.style.backgroundColor='white'">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger" style="background-color: #fef2f2; border: 1px solid #ef4444; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-exclamation-circle" style="font-size: 18px;"></i>
            <div>
                <strong>Lỗi:</strong> <?= htmlspecialchars(urldecode($error)) ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Form Container -->
    <div class="form-container" style="background: white; padding: 28px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); border: 1px solid #e5e7eb;">
        <form method="POST" action="?page=admin&module=subpages&action=edit&key=<?= htmlspecialchars($key) ?>" class="admin-form" enctype="multipart/form-data">
            <input type="hidden" name="page_key" value="<?= htmlspecialchars($key) ?>">
            
            <?php if ($isSocial): ?>
                <!-- CASE 1: EDIT SOCIAL MEDIA LINKS -->
                <div class="social-form-section">
                    <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin-top: 0; margin-bottom: 20px; border-bottom: 2px solid #3b82f6; padding-bottom: 8px; display: inline-block;">
                        <i class="fas fa-share-alt" style="color: #3b82f6; margin-right: 8px;"></i>Cấu hình liên kết mạng xã hội
                    </h3>
                    
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-bottom: 24px; color: #475569; font-size: 13.5px;">
                        <i class="fas fa-info-circle" style="color: #3b82f6; margin-right: 6px;"></i> 
                        Bạn có thể thay đổi đường dẫn (URL) liên kết mạng xã hội, sửa biểu tượng (FontAwesome icon) hoặc ẩn biểu tượng khỏi footer bằng cách bỏ tích chọn ở cột <strong>Hiển thị</strong>.
                    </div>

                    <div class="table-container" style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; margin-bottom: 20px;">
                        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
                            <thead>
                                <tr style="background: #f1f5f9; border-bottom: 1px solid #e2e8f0;">
                                    <th style="padding: 12px; font-weight: 600; color: #334155; width: 150px;">Mạng xã hội</th>
                                    <th style="padding: 12px; font-weight: 600; color: #334155; width: 180px;">Biểu tượng (FA Class)</th>
                                    <th style="padding: 12px; font-weight: 600; color: #334155;">Đường dẫn (URL)</th>
                                    <th style="padding: 12px; font-weight: 600; color: #334155; width: 100px; text-align: center;">Hiển thị</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $socialData = json_decode($page['content'], true);
                                if (!is_array($socialData)) {
                                    $socialData = [
                                        'facebook' => ['name' => 'Facebook', 'url' => 'https://facebook.com', 'visible' => true, 'icon' => 'fab fa-facebook'],
                                        'youtube' => ['name' => 'Youtube', 'url' => 'https://youtube.com', 'visible' => true, 'icon' => 'fab fa-youtube'],
                                        'instagram' => ['name' => 'Instagram', 'url' => 'https://instagram.com', 'visible' => true, 'icon' => 'fab fa-instagram'],
                                        'twitter' => ['name' => 'X (Twitter)', 'url' => 'https://twitter.com', 'visible' => true, 'icon' => 'fab fa-twitter'],
                                        'tiktok' => ['name' => 'Tiktok', 'url' => 'https://tiktok.com', 'visible' => true, 'icon' => 'fab fa-tiktok'],
                                        'linkedin' => ['name' => 'Linkedin', 'url' => 'https://linkedin.com', 'visible' => true, 'icon' => 'fab fa-linkedin']
                                    ];
                                }
                                
                                foreach ($socialData as $sKey => $sVal):
                                    $visible = isset($sVal['visible']) ? (bool)$sVal['visible'] : true;
                                    $iconClass = $sVal['icon'] ?? 'fab fa-link';
                                ?>
                                    <tr style="border-bottom: 1px solid #e2e8f0;">
                                        <td style="padding: 12px; font-weight: 600; color: #1e293b;">
                                            <input type="hidden" name="socials[<?= $sKey ?>][name]" value="<?= htmlspecialchars($sVal['name'] ?? $sKey) ?>">
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <div style="width: 28px; height: 28px; border-radius: 6px; background: #eff6ff; display: flex; align-items: center; justify-content: center; color: #1e40af;">
                                                    <i class="<?= htmlspecialchars($iconClass) ?>" id="preview-icon-<?= $sKey ?>"></i>
                                                </div>
                                                <?= htmlspecialchars($sVal['name'] ?? $sKey) ?>
                                            </div>
                                        </td>
                                        <td style="padding: 12px;">
                                            <input type="text" name="socials[<?= $sKey ?>][icon]" value="<?= htmlspecialchars($iconClass) ?>" 
                                                   oninput="document.getElementById('preview-icon-<?= $sKey ?>').className = this.value"
                                                   style="width: 100%; padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-family: monospace; font-size: 13px; color: #334155;">
                                        </td>
                                        <td style="padding: 12px;">
                                            <input type="url" name="socials[<?= $sKey ?>][url]" value="<?= htmlspecialchars($sVal['url'] ?? '') ?>" placeholder="Nhập liên kết https://..."
                                                   style="width: 100%; padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13.5px; color: #334155;">
                                        </td>
                                        <td style="padding: 12px; text-align: center;">
                                            <!-- Mẹo gửi checkbox visible: Sử dụng input hidden 0 ngay trước checkbox -->
                                            <input type="hidden" name="socials[<?= $sKey ?>][visible]" value="0">
                                            <input type="checkbox" name="socials[<?= $sKey ?>][visible]" value="1" <?= $visible ? 'checked' : '' ?> 
                                                   style="width: 18px; height: 18px; cursor: pointer; accent-color: #3b82f6;">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <!-- CASE 2: EDIT STATIC SUBPAGES WITH WYSIWYG -->
                <div class="form-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 28px;">
                    <!-- Left Column: Title & Content Editor -->
                    <div class="form-column">
                        <!-- Title -->
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="title" class="required" style="font-weight: 600; color: #374151; display: block; margin-bottom: 8px; font-size: 15px;">Tên trang / Tiêu đề chính:</label>
                            <input type="text" id="title" name="title" value="<?= htmlspecialchars($page['title']) ?>" 
                                   placeholder="Nhập tên trang nội dung..." required
                                   style="width: 100%; padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 15px; outline: none; transition: border-color 0.2s;"
                                   onfocus="this.style.borderColor='#4f46e5'" onblur="this.style.borderColor='#d1d5db'">
                            <small style="color: #6b7280; display: block; margin-top: 4px;">Tiêu đề sẽ hiển thị nổi bật ở phần header của trang nội dung.</small>
                        </div>

                        <!-- Subtitle -->
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="subtitle" style="font-weight: 600; color: #374151; display: block; margin-bottom: 8px; font-size: 15px;">Tiêu đề phụ (Subtitle):</label>
                            <input type="text" id="subtitle" name="subtitle" value="<?= htmlspecialchars($page['subtitle'] ?? '') ?>" 
                                   placeholder="Nhập tiêu đề phụ dưới tiêu đề chính..."
                                   style="width: 100%; padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 15px; outline: none; transition: border-color 0.2s;"
                                   onfocus="this.style.borderColor='#4f46e5'" onblur="this.style.borderColor='#d1d5db'">
                            <small style="color: #6b7280; display: block; margin-top: 4px;">Tiêu đề phụ sẽ hiển thị nhỏ hơn ở ngay bên dưới tiêu đề chính.</small>
                        </div>

                        <!-- WYSIWYG Editor -->
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label class="required" style="font-weight: 600; color: #374151; display: block; margin-bottom: 8px; font-size: 15px;">Nội dung trang (Văn bản & Hình ảnh xen kẽ):</label>
                            
                            <!-- Editor Toolbar -->
                            <div class="custom-editor-toolbar" style="background: #f9fafb; border: 1px solid #d1d5db; border-bottom: none; border-radius: 8px 8px 0 0; padding: 12px; display: flex; flex-wrap: wrap; gap: 12px; align-items: center;">
                                <div class="toolbar-group" style="display: flex; gap: 4px; border-right: 1px solid #e5e7eb; padding-right: 12px;">
                                    <button type="button" class="tb-btn" onclick="applyFormat('bold')" title="In đậm" style="background: white; border: 1px solid #d1d5db; border-radius: 6px; width: 34px; height: 34px; cursor: pointer; color: #4b5563; display: flex; align-items: center; justify-content: center; transition: all 0.2s;"><i class="fas fa-bold"></i></button>
                                    <button type="button" class="tb-btn" onclick="applyFormat('italic')" title="In nghiêng" style="background: white; border: 1px solid #d1d5db; border-radius: 6px; width: 34px; height: 34px; cursor: pointer; color: #4b5563; display: flex; align-items: center; justify-content: center; transition: all 0.2s;"><i class="fas fa-italic"></i></button>
                                    <button type="button" class="tb-btn" onclick="applyFormat('underline')" title="Gạch chân" style="background: white; border: 1px solid #d1d5db; border-radius: 6px; width: 34px; height: 34px; cursor: pointer; color: #4b5563; display: flex; align-items: center; justify-content: center; transition: all 0.2s;"><i class="fas fa-underline"></i></button>
                                </div>
                                
                                <div class="toolbar-group" style="display: flex; gap: 8px; border-right: 1px solid #e5e7eb; padding-right: 12px; align-items: center;">
                                    <select onchange="applyStyle('fontFamily', this.value)" style="padding: 6px 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px; color: #374151; background: white; cursor: pointer;">
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
                                    <button type="button" class="tb-btn" onclick="applyFormat('justifyLeft')" title="Căn trái" style="background: white; border: 1px solid #d1d5db; border-radius: 6px; width: 34px; height: 34px; cursor: pointer; color: #4b5563; display: flex; align-items: center; justify-content: center; transition: all 0.2s;"><i class="fas fa-align-left"></i></button>
                                    <button type="button" class="tb-btn" onclick="applyFormat('justifyCenter')" title="Căn giữa" style="background: white; border: 1px solid #d1d5db; border-radius: 6px; width: 34px; height: 34px; cursor: pointer; color: #4b5563; display: flex; align-items: center; justify-content: center; transition: all 0.2s;"><i class="fas fa-align-center"></i></button>
                                    <button type="button" class="tb-btn" onclick="applyFormat('justifyRight')" title="Căn phải" style="background: white; border: 1px solid #d1d5db; border-radius: 6px; width: 34px; height: 34px; cursor: pointer; color: #4b5563; display: flex; align-items: center; justify-content: center; transition: all 0.2s;"><i class="fas fa-align-right"></i></button>
                                </div>

                                <div class="toolbar-group" style="display: flex; gap: 4px; border-right: 1px solid #e5e7eb; padding-right: 12px;">
                                    <button type="button" class="tb-btn" onclick="applyFormat('insertUnorderedList')" title="Danh sách gạch đầu dòng" style="background: white; border: 1px solid #d1d5db; border-radius: 6px; width: 34px; height: 34px; cursor: pointer; color: #4b5563; display: flex; align-items: center; justify-content: center; transition: all 0.2s;"><i class="fas fa-list-ul"></i></button>
                                    <button type="button" class="tb-btn" onclick="applyFormat('insertOrderedList')" title="Danh sách số thứ tự" style="background: white; border: 1px solid #d1d5db; border-radius: 6px; width: 34px; height: 34px; cursor: pointer; color: #4b5563; display: flex; align-items: center; justify-content: center; transition: all 0.2s;"><i class="fas fa-list-ol"></i></button>
                                </div>

                                <!-- Image Insertion -->
                                <div class="toolbar-group" style="display: flex; gap: 8px;">
                                    <button type="button" class="tb-btn btn-success" onclick="triggerEditorImageUpload()" title="Tải lên & Chèn hình ảnh tại con trỏ" style="background: #10b981; border: 1px solid #059669; border-radius: 6px; padding: 0 12px; height: 34px; cursor: pointer; color: white; display: flex; align-items: center; gap: 6px; font-weight: 500; font-size: 13px; transition: background 0.2s;">
                                        <i class="fas fa-image"></i> Chèn Hình Ảnh
                                    </button>
                                    <input type="file" id="editor-image-file" style="display: none;" accept="image/*" onchange="uploadAndInsertImage(this)">
                                    <button type="button" class="tb-btn" onclick="applyFormat('removeFormat')" title="Xóa định dạng" style="background: white; border: 1px solid #d1d5db; border-radius: 6px; width: 34px; height: 34px; cursor: pointer; color: #ef4444; display: flex; align-items: center; justify-content: center; transition: all 0.2s;"><i class="fas fa-eraser"></i></button>
                                </div>
                            </div>

                            <!-- Editable Workspace -->
                            <div id="rich-editor" class="custom-editable-area" contenteditable="true" oninput="syncEditorContent()" 
                                 style="min-height: 480px; border: 1px solid #d1d5db; border-radius: 0 0 8px 8px; padding: 20px; outline: none; background: white; font-size: 16px; line-height: 1.7; overflow-y: auto; color: #1f2937;">
                                <?= $page['content'] ?>
                            </div>
                            
                            <!-- Hidden textarea for form submit -->
                            <textarea id="page-content-textarea" name="content" style="display: none;"><?= htmlspecialchars($page['content']) ?></textarea>
                            <small style="color: #6b7280; display: block; margin-top: 6px;">Bạn có thể viết văn bản và định dạng, sau đó click "Chèn Hình Ảnh" để upload ảnh từ máy tính. Ảnh sẽ chèn ngay tại vị trí con trỏ chuột.</small>
                        </div>
                    </div>

                    <!-- Right Column: Banner & SEO Meta -->
                    <div class="form-column">
                        <!-- Page Banner Upload -->
                        <div class="form-section" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; margin-bottom: 24px;">
                            <h3 style="font-size: 15px; font-weight: 600; color: #0f172a; margin-top: 0; margin-bottom: 16px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-image" style="color: #3b82f6;"></i>Ảnh Banner Trang Phụ
                            </h3>
                            <div class="form-group">
                                <label style="font-weight: 500; font-size: 13px; color: #475569; display: block; margin-bottom: 8px;">Ảnh banner tiêu đề:</label>
                                <div class="banner-preview" id="bannerPreviewContainer" style="background: #e2e8f0; border-radius: 8px; height: 150px; display: flex; align-items: center; justify-content: center; overflow: hidden; margin-bottom: 12px; cursor: pointer; border: 2px dashed #cbd5e1; transition: opacity 0.2s;" onclick="document.getElementById('image').click()" onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'">
                                    <?php if (!empty($page['image'])): ?>
                                        <img id="banner-preview-img" src="<?= htmlspecialchars($page['image']) ?>" alt="Banner Image" style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                        <div id="banner-placeholder" style="text-align: center; color: #64748b;">
                                            <i class="fas fa-cloud-upload-alt" style="font-size: 32px; margin-bottom: 6px; display: block; color: #94a3b8;"></i>
                                            <span style="font-size: 12px; font-weight: 500;">Click để chọn ảnh banner</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <input type="file" id="image" name="image" accept="image/*" style="display: none;" onchange="previewBannerImage(this)">
                                <input type="hidden" name="current_image" value="<?= htmlspecialchars($page['image'] ?? '') ?>">
                                <small style="color: #64748b; display: block; font-size: 11.5px; line-height: 1.4;">Kích thước đề xuất: 1200x350px. Ảnh sẽ xuất hiện làm hình nền tiêu đề ở đầu trang.</small>
                            </div>
                        </div>

                        <!-- SEO Search Engine Optimization -->
                        <div class="form-section" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px;">
                            <h3 style="font-size: 15px; font-weight: 600; color: #0f172a; margin-top: 0; margin-bottom: 16px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-search-plus" style="color: #10b981;"></i>Tối Ưu SEO Google
                            </h3>
                            
                            <div class="form-group" style="margin-bottom: 14px;">
                                <label for="meta_title" style="font-weight: 500; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Meta Title (Tiêu đề):</label>
                                <input type="text" id="meta_title" name="meta_title" value="<?= htmlspecialchars($page['meta_title'] ?? '') ?>" 
                                       placeholder="Nhập tiêu đề SEO..."
                                       style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13.5px; outline: none;"
                                       onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#cbd5e1'">
                            </div>

                            <div class="form-group" style="margin-bottom: 14px;">
                                <label for="meta_description" style="font-weight: 500; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Meta Description (Mô tả):</label>
                                <textarea id="meta_description" name="meta_description" rows="5" placeholder="Nhập mô tả ngắn SEO..."
                                          style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13.5px; resize: none; outline: none; line-height: 1.4;"
                                          onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#cbd5e1'"><?= htmlspecialchars($page['meta_description'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Form Actions -->
            <div class="form-actions" style="margin-top: 28px; border-top: 1px solid #e5e7eb; padding-top: 20px; display: flex; justify-content: flex-end; gap: 12px;">
                <a href="?page=admin&module=subpages" class="btn" style="border: 1px solid #d1d5db; background: white; color: #374151; padding: 10px 20px; border-radius: 8px; font-weight: 600; text-decoration: none; font-size: 14px; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.backgroundColor='#f3f4f6'" onmouseout="this.style.backgroundColor='white'">
                    Hủy bỏ
                </a>
                <button type="submit" class="btn" style="background: #4f46e5; border: 1px solid #4338ca; color: white; padding: 10px 24px; border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: background 0.2s;" onmouseover="this.style.backgroundColor='#4338ca'" onmouseout="this.style.backgroundColor='#4f46e5'">
                    <i class="fas fa-save"></i> Lưu Cấu Hình
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Format Text command
function applyFormat(command) {
    const editor = document.getElementById('rich-editor');
    if (!editor) return;
    editor.focus();
    document.execCommand(command, false, null);
    syncEditorContent();
}

// Apply Specific Style
function applyStyle(property, value) {
    const editor = document.getElementById('rich-editor');
    if (!editor) return;
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
    if (editor && textarea) {
        textarea.value = editor.innerHTML;
    }
}

// Trigger upload file dialog
function triggerEditorImageUpload() {
    const fileInput = document.getElementById('editor-image-file');
    if (fileInput) fileInput.click();
}

// AJAX Upload and Insert Image to cursor position
function uploadAndInsertImage(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const btn = document.querySelector('[onclick="triggerEditorImageUpload()"]');
        const originalBtnText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang tải lên...';
        btn.disabled = true;

        const formData = new FormData();
        formData.append('upload_file', file);

        // Fetch API to upload image asynchronously to subpages upload endpoint
        fetch('?page=admin&module=subpages&action=upload_editor_image', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            btn.innerHTML = originalBtnText;
            btn.disabled = false;
            
            if (data.success) {
                const editor = document.getElementById('rich-editor');
                editor.focus();
                
                // Construct responsive image html
                const imgHtml = `<img src="${data.url}" alt="Hình ảnh nội dung" style="max-width: 100%; height: auto; display: block; margin: 16px auto; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">`;
                
                document.execCommand('insertHTML', false, imgHtml);
                syncEditorContent();
            } else {
                alert('Tải hình ảnh thất bại: ' + (data.message || 'Lỗi không xác định'));
            }
        })
        .catch(err => {
            btn.innerHTML = originalBtnText;
            btn.disabled = false;
            console.error(err);
            alert('Đã xảy ra lỗi kết nối mạng khi tải lên ảnh.');
        });
        
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
const form = document.querySelector('form');
if (form) {
    form.addEventListener('submit', function() {
        syncEditorContent();
    });
}
</script>

<style>
.custom-editor-toolbar button:hover {
    background: #e2e8f0 !important;
    color: #0f172a !important;
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
</style>
