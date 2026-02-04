<?php
// Categories Management - Add/Edit Form
$page_title = "Thêm/Sửa Danh mục";
?>

<div class="admin-header">
    <h1><?php echo $page_title; ?></h1>
</div>

<div class="admin-card">
    <div class="admin-card-body">
        <form class="admin-form" method="POST">
            <div class="admin-form-group">
                <label class="admin-form-label">Tên danh mục *</label>
                <input type="text" class="admin-form-control" name="name" required>
            </div>
            <div class="admin-form-group">
                <label class="admin-form-label">Mô tả</label>
                <textarea class="admin-form-control admin-textarea" name="description"></textarea>
            </div>
            <div class="admin-form-group">
                <button type="submit" class="admin-btn admin-btn-primary">Lưu</button>
                <a href="?page=admin&module=categories" class="admin-btn admin-btn-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>