<?php
// Professional Categories Management
$page_title = "Quản lý Danh mục";
$breadcrumb = [
    ['text' => 'Dashboard', 'url' => '?page=admin&module=dashboard'],
    ['text' => 'Danh mục', 'url' => null]
];

// Load fake data
$fake_data_file = __DIR__ . '/../data/fake_data.json';
$categories = [];

if (file_exists($fake_data_file)) {
    $json_data = json_decode(file_get_contents($fake_data_file), true);
    $categories = $json_data['categories'] ?? [];
}

// Count products per category
$productCounts = [];
if (isset($json_data['products'])) {
    foreach ($json_data['products'] as $product) {
        $catId = $product['category_id'];
        $productCounts[$catId] = ($productCounts[$catId] ?? 0) + 1;
    }
}
?>

<div class="admin-page-header">
    <div class="page-header-left">
        <h1><?php echo $page_title; ?></h1>
        <div class="admin-breadcrumb">
            <?php foreach ($breadcrumb as $index => $crumb): ?>
                <?php if ($crumb['url']): ?>
                    <a href="<?php echo $crumb['url']; ?>"><?php echo $crumb['text']; ?></a>
                <?php else: ?>
                    <span class="current"><?php echo $crumb['text']; ?></span>
                <?php endif; ?>
                <?php if ($index < count($breadcrumb) - 1): ?>
                    <span class="delimiter">/</span>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="page-header-right">
        <a href="?page=admin&module=categories&action=change" class="admin-btn admin-btn-primary">
            <i class="fas fa-plus"></i> Thêm danh mục mới
        </a>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-body">
        <?php if (empty($categories)): ?>
            <div class="admin-empty-state">
                <i class="fas fa-folder-open" style="font-size: 48px; color: #9CA3AF; margin-bottom: 16px;"></i>
                <h3>Chưa có danh mục nào</h3>
                <p>Bắt đầu bằng cách thêm danh mục đầu tiên</p>
                <a href="?page=admin&module=categories&action=change" class="admin-btn admin-btn-primary">
                    <i class="fas fa-plus"></i> Thêm danh mục
                </a>
            </div>
        <?php else: ?>
            <div class="categories-grid">
                <?php foreach ($categories as $category): ?>
                <div class="category-card">
                    <div class="category-header">
                        <div class="category-icon">
                            <i class="fas fa-folder"></i>
                        </div>
                        <div class="category-info">
                            <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                            <p><?php echo htmlspecialchars($category['description']); ?></p>
                        </div>
                    </div>
                    <div class="category-stats">
                        <div class="stat">
                            <i class="fas fa-box"></i>
                            <span><?php echo $productCounts[$category['id']] ?? 0; ?> sản phẩm</span>
                        </div>
                        <?php if ($category['parent_id']): ?>
                        <div class="stat">
                            <i class="fas fa-sitemap"></i>
                            <span>Danh mục con</span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="category-actions">
                        <a href="?page=admin&module=products&category=<?php echo $category['id']; ?>" 
                           class="action-link">
                            <i class="fas fa-eye"></i> Xem sản phẩm
                        </a>
                        <a href="?page=admin&module=categories&action=edit&id=<?php echo $category['id']; ?>" 
                           class="action-link">
                            <i class="fas fa-edit"></i> Sửa
                        </a>
                        <a href="?page=admin&module=categories&action=delete&id=<?php echo $category['id']; ?>" 
                           class="action-link text-danger"
                           onclick="return confirm('Bạn có chắc chắn muốn xóa?')">
                            <i class="fas fa-trash"></i> Xóa
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px;
}

.category-card {
    background: white;
    border: 1px solid #E5E7EB;
    border-radius: 8px;
    padding: 20px;
    transition: all 0.2s ease;
}

.category-card:hover {
    border-color: #356DF1;
    box-shadow: 0 4px 12px rgba(53, 109, 241, 0.1);
    transform: translateY(-2px);
}

.category-header {
    display: flex;
    gap: 16px;
    margin-bottom: 16px;
}

.category-icon {
    width: 48px;
    height: 48px;
    background: #E1E9FD;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #356DF1;
    font-size: 20px;
    flex-shrink: 0;
}

.category-info {
    flex: 1;
}

.category-info h3 {
    margin: 0 0 4px 0;
    font-size: 16px;
    font-weight: 600;
    color: #1F2937;
}

.category-info p {
    margin: 0;
    font-size: 13px;
    color: #6B7280;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.category-stats {
    display: flex;
    gap: 16px;
    padding: 12px 0;
    border-top: 1px solid #F3F4F6;
    border-bottom: 1px solid #F3F4F6;
    margin-bottom: 16px;
}

.category-stats .stat {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: #6B7280;
}

.category-stats .stat i {
    color: #9CA3AF;
}

.category-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.action-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: #356DF1;
    text-decoration: none;
    padding: 6px 12px;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.action-link:hover {
    background: #F3F4F6;
}

.action-link.text-danger {
    color: #EF4444;
}

@media (max-width: 768px) {
    .categories-grid {
        grid-template-columns: 1fr;
    }
}
</style>