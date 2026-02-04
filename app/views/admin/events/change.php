<?php
// Events Management - Add/Edit Form
$page_title = "Thêm/Sửa Sự kiện";
?>

<div class="admin-header">
    <h1><?php echo $page_title; ?></h1>
</div>

<div class="admin-card">
    <div class="admin-card-body">
        <form class="admin-form" method="POST">
            <div class="admin-form-group">
                <label class="admin-form-label">Tên sự kiện *</label>
                <input type="text" class="admin-form-control" name="name" required>
            </div>
            <div class="admin-form-group">
                <label class="admin-form-label">Mô tả</label>
                <textarea class="admin-form-control admin-textarea" name="description"></textarea>
            </div>
            <div class="admin-form-group">
                <label class="admin-form-label">Ngày bắt đầu *</label>
                <input type="date" class="admin-form-control" name="start_date" required>
            </div>
            <div class="admin-form-group">
                <label class="admin-form-label">Ngày kết thúc *</label>
                <input type="date" class="admin-form-control" name="end_date" required>
            </div>
            <div class="admin-form-group">
                <label class="admin-form-label">Địa điểm</label>
                <input type="text" class="admin-form-control" name="location">
            </div>
            <div class="admin-form-group">
                <button type="submit" class="admin-btn admin-btn-primary">Lưu</button>
                <a href="?page=admin&module=events" class="admin-btn admin-btn-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>