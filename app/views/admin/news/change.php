<?php
// News Management - Add/Edit Form
$page_title = "Thêm/Sửa Tin tức";
?>

<div class="admin-header">
    <h1><?php echo $page_title; ?></h1>
</div>

<div class="admin-card">
    <div class="admin-card-body">
        <form class="admin-form" method="POST">
            <div class="admin-form-group">
                <label class="admin-form-label">Tiêu đề *</label>
                <input type="text" class="admin-form-control" name="title" required>
            </div>
            <div class="admin-form-group">
                <label class="admin-form-label">Tóm tắt</label>
                <textarea class="admin-form-control" name="summary"></textarea>
            </div>
            <div class="admin-form-group">
                <label class="admin-form-label">Nội dung *</label>
                <textarea class="admin-form-control admin-textarea" name="content" required></textarea>
            </div>
            <div class="admin-form-group">
                <label class="admin-form-label">Trạng thái</label>
                <select class="admin-form-control" name="status">
                    <option value="draft">Nháp</option>
                    <option value="published">Đã xuất bản</option>
                </select>
            </div>
            <div class="admin-form-group">
                <button type="submit" class="admin-btn admin-btn-primary">Lưu</button>
                <a href="?page=admin&module=news" class="admin-btn admin-btn-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>