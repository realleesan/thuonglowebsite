<?php
// Events Management - Delete Confirmation
$page_title = "Xóa Sự kiện";
?>

<div class="admin-header">
    <h1><?php echo $page_title; ?></h1>
</div>

<div class="admin-card">
    <div class="admin-card-body">
        <div class="alert alert-warning">
            <p>Bạn có chắc chắn muốn xóa sự kiện này?</p>
        </div>
        <form method="POST">
            <button type="submit" name="confirm_delete" class="admin-btn admin-btn-danger">Xóa</button>
            <a href="?page=admin&module=events" class="admin-btn admin-btn-secondary">Hủy</a>
        </form>
    </div>
</div>