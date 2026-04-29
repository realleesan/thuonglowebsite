<?php
/**
 * Debug script kiểm tra cập nhật parent_id
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== DEBUG PARENT_ID UPDATE ===\n\n";

try {
    require_once __DIR__ . '/app/config/config.php';
    require_once __DIR__ . '/app/models/CategoriesModel.php';
    
    $categoriesModel = new CategoriesModel();
} catch (Exception $e) {
    die("Error loading files: " . $e->getMessage());
}

// Lấy tất cả danh mục
$categories = $categoriesModel->getActive();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = (int)($_POST['category_id'] ?? 0);
    $parent_id_raw = $_POST['parent_id'] ?? '';
    
    echo "Category ID: $category_id\n";
    echo "Raw parent_id from form: " . var_export($parent_id_raw, true) . "\n";
    
    // Xử lý giống như trong edit.php
    $parent_id = !empty($parent_id_raw) ? (int)$parent_id_raw : null;
    echo "Processed parent_id: " . var_export($parent_id, true) . "\n";
    
    // Chuẩn bị update data
    $updateData = [
        'name' => $_POST['name'] ?? 'Test',
        'parent_id' => $parent_id
    ];
    
    echo "\nUpdate Data:\n";
    print_r($updateData);
    
    // Thực hiện update
    echo "\nExecuting update...\n";
    $result = $categoriesModel->update($category_id, $updateData);
    echo "Update result: " . var_export($result, true) . "\n";
    
    // Kiểm tra lại giá trị trong DB
    $updated = $categoriesModel->find($category_id);
    echo "\nValue in DB after update:\n";
    echo "parent_id: " . var_export($updated['parent_id'] ?? 'NOT FOUND', true) . "\n";
    
} else {
    // Hiển thị form
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Debug Parent Update</title>
    </head>
    <body>
        <h2>Debug Update parent_id</h2>
        <form method="POST">
            <p>
                <label>Chọn danh mục cần sửa:</label><br>
                <select name="category_id" required>
                    <option value="">-- Chọn --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>">
                            <?= htmlspecialchars($cat['name']) ?> 
                            (ID: <?= $cat['id'] ?>, 
                            parent_id: <?= $cat['parent_id'] ?? 'NULL' ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>
            <p>
                <label>Tên mới (optional):</label><br>
                <input type="text" name="name" placeholder="Để trống nếu không đổi">
            </p>
            <p>
                <label>Danh mục cha mới:</label><br>
                <select name="parent_id">
                    <option value="">-- Không có (NULL) --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>">
                            <?= htmlspecialchars($cat['name']) ?> (ID: <?= $cat['id'] ?>)
                        </option>
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
