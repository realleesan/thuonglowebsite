<?php
// Products Management - Add/Edit Form
// This file will be fully implemented in later tasks

$page_title = "Thêm/Sửa Sản phẩm";
$breadcrumb = [
    ['text' => 'Dashboard', 'url' => '?page=admin&module=dashboard'],
    ['text' => 'Sản phẩm', 'url' => '?page=admin&module=products'],
    ['text' => 'Thêm/Sửa', 'url' => null]
];

// Basic form structure - will be implemented fully in later tasks
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
        <h3 class="admin-card-title">Thông tin Sản phẩm</h3>
    </div>
    <div class="admin-card-body">
        <form class="admin-form" method="POST">
            <div class="admin-form-group">
                <label class="admin-form-label" for="name">Tên sản phẩm *</label>
                <input type="text" class="admin-form-control" id="name" name="name" required>
            </div>
            
            <div class="admin-form-group">
                <label class="admin-form-label" for="description">Mô tả</label>
                <textarea class="admin-form-control admin-textarea" id="description" name="description"></textarea>
            </div>
            
            <div class="admin-form-group">
                <label class="admin-form-label" for="price">Giá *</label>
                <input type="number" class="admin-form-control" id="price" name="price" required>
            </div>
            
            <div class="admin-form-group">
                <label class="admin-form-label" for="category_id">Danh mục *</label>
                <select class="admin-form-control admin-form-select" id="category_id" name="category_id" required>
                    <option value="">Chọn danh mục</option>
                    <!-- Categories will be loaded dynamically -->
                </select>
            </div>
            
            <div class="admin-form-group">
                <label class="admin-form-label" for="status">Trạng thái</label>
                <select class="admin-form-control admin-form-select" id="status" name="status">
                    <option value="active">Hoạt động</option>
                    <option value="inactive">Không hoạt động</option>
                </select>
            </div>
            
            <div class="admin-form-group">
                <button type="submit" class="admin-btn admin-btn-primary">Lưu sản phẩm</button>
                <a href="?page=admin&module=products" class="admin-btn admin-btn-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>