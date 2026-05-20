<?php
/**
 * Edit Featured Products Section View
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
            <form id="featuredProductsForm" method="POST" action="?page=admin&module=homepage&action=update-featured-products">
                <input type="hidden" name="id" value="<?php echo $featuredProductsSection['id']; ?>">
                
                <div class="form-group mb-4">
                    <label class="admin-label">Tiêu đề Section <span class="text-danger">*</span></label>
                    <textarea id="title" name="title" class="form-control ckeditor-textarea" rows="4"><?php echo htmlspecialchars($featuredProductsSection['title']); ?></textarea>
                    <small class="text-muted">Sử dụng HTML để tùy chỉnh tiêu đề. Ví dụ: &lt;h2&gt;Sản phẩm &lt;span class="highlight"&gt;Nổi bật&lt;/span&gt;&lt;/h2&gt;</small>
                </div>

                <div class="form-check mb-4">
                    <input type="checkbox" id="is_active" name="is_active" value="1" <?php echo ($featuredProductsSection['is_active'] ? 'checked' : ''); ?>>
                    <label for="is_active" class="fw-bold">Hiển thị Section này trên trang chủ</label>
                </div>

                <div class="form-actions mt-5">
                    <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i>
                        Lưu thay đổi
                    </button>
                    <a href="?page=admin&module=homepage" class="btn-cancel">
                        <i class="fas fa-times"></i>
                        Hủy
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- CKEditor 5 -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize CKEditor for title
    ClassicEditor
        .create(document.querySelector('#title'), {
            toolbar: ['heading', '|', 'bold', 'italic', 'underline', '|', 'fontSize', 'fontColor', 'fontBackgroundColor', '|', 'link', '|', 'undo', 'redo'],
            fontSize: {
                options: [12, 14, 16, 18, 20, 24, 28, 32, 36, 48]
            }
        })
        .then(editor => {
            window.titleEditor = editor;
        })
        .catch(error => {
            console.error('CKEditor initialization error:', error);
        });

    // Form submit handler
    document.getElementById('featuredProductsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData();
        formData.append('id', document.querySelector('input[name="id"]').value);
        formData.append('title', window.titleEditor.getData());
        formData.append('is_active', document.getElementById('is_active').checked ? '1' : '0');

        fetch(this.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '?page=admin&module=homepage';
            } else {
                alert(data.message || 'Có lỗi xảy ra');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi lưu dữ liệu');
        });
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

.form-control {
    width: 100%;
    padding: 12px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: #356DF1;
    box-shadow: 0 0 0 3px rgba(53, 109, 241, 0.1);
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

.btn-save {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: #356DF1;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-save:hover {
    background: #2557d6;
}

.btn-cancel {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: #f3f4f6;
    color: #374151;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-cancel:hover {
    background: #e5e7eb;
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

.ck-editor__editable {
    min-height: 150px;
}
</style>
