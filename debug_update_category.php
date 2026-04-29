<?php
/**
 * Debug script kiểm tra cập nhật danh mục
 */

require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/models/CategoriesModel.php';

echo "=== DEBUG UPDATE CATEGORY ===\n\n";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "POST Data:\n";
    print_r($_POST);
    
    echo "\nProcessing parent_id:\n";
    $parent_id = $_POST['parent_id'] ?? '';
    echo "Raw parent_id: " . var_export($parent_id, true) . "\n";
    
    $processed_parent_id = !empty($parent_id) ? (int)$parent_id : null;
    echo "Processed parent_id: " . var_export($processed_parent_id, true) . "\n";
    
    // Test update
    if (!empty($_POST['category_id'])) {
        $categoriesModel = new CategoriesModel();
        $category_id = (int)$_POST['category_id'];
        
        echo "\nAttempting to update category ID: $category_id\n";
        
        $updateData = [
            'name' => $_POST['name'] ?? 'Test',
            'parent_id' => $processed_parent_id
        ];
        
        echo "\nUpdate data:\n";
        print_r($updateData);
        
        $result = $categoriesModel->update($category_id, $updateData);
        echo "\nUpdate result: " . var_export($result, true) . "\n";
        
        // Check current value in DB
        $updated = $categoriesModel->find($category_id);
        echo "\nCurrent DB value - parent_id: " . var_export($updated['parent_id'] ?? 'NULL', true) . "\n";
    }
} else {
    // Show form
    $categoriesModel = new CategoriesModel();
    $categories = $categoriesModel->getActive();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Debug Update Category</title>
    </head>
    <body>
        <h2>Debug Update Category</h2>
        <form method="POST">
            <p>
                <label>Category ID:</label><br>
                <select name="category_id" required>
                    <option value="">-- Chọn danh mục --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?> (ID: <?= $cat['id'] ?>, parent: <?= $cat['parent_id'] ?? 'NULL' ?>)</option>
                    <?php endforeach; ?>
                </select>
            </p>
            <p>
                <label>New Name:</label><br>
                <input type="text" name="name" placeholder="Tên mới">
            </p>
            <p>
                <label>Parent ID:</label><br>
                <select name="parent_id">
                    <option value="">-- Không có (NULL) --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?> (ID: <?= $cat['id'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </p>
            <p>
                <button type="submit">Test Update</button>
            </p>
        </form>
    </body>
    </html>
    <?php
}
