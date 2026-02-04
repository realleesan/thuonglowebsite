<?php
// Products Management - Delete Confirmation
// This file will be fully implemented in later tasks

$page_title = "Xóa Sản phẩm";
$breadcrumb = [
    ['text' => 'Dashboard', 'url' => '?page=admin&module=dashboard'],
    ['text' => 'Sản phẩm', 'url' => '?page=admin&module=products'],
    ['text' => 'Xóa', 'url' => null]
];

// Basic delete confirmation structure - will be implemented fully in later tasks
?>

<div class="admin-header">
    <h1><?php echo $page_title; ?></h1>
    <div class="admin-breadcrumb">
        <?php foreach ($breadcrumb as $index => $crumb): ?>
            <?php if ($crumb['url']): ?>
                <a href="<?php echo $crumb['url']; ?>"><?php echo $crumb['text']; ?></a>
            <?php else: ?>
                <span><?php echo $crumb['text']; ?></span>
            <?php endif; ?>
            <?php if ($index < count($breadcrumb) - 1): ?>
                <span> / </span>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <h3 class="admin-card-title">Xác nhận xóa sản phẩm</h3>
    </div>
    <div class="admin-card-body">
        <div class="alert alert-warning">
            <h4>⚠️ Cảnh báo</h4>
            <p>Bạn có chắc chắn muốn xóa sản phẩm này? Hành động này không thể hoàn tác.</p>
        </div>
        
        <form method="POST">
            <div class="admin-form-group">
                <button type="submit" name="confirm_delete" class="admin-btn admin-btn-danger">
                    Xác nhận xóa
                </button>
                <a href="?page=admin&module=products" class="admin-btn admin-btn-secondary">
                    Hủy bỏ
                </a>
            </div>
        </form>
    </div>
</div>