<?php
/**
 * Script chuyển đổi hàng loạt các file còn lại từ JSON sang SQL
 */

echo "=== CHUYỂN ĐỔI CÁC FILE VIEWS TỪ JSON SANG SQL ===\n\n";

// Danh sách các file cần chuyển đổi và pattern thay thế
$conversions = [
    // Admin Products
    'app/views/admin/products/view.php' => [
        'old' => '<?php
// Load fake data
$fake_data = json_decode(file_get_contents(__DIR__ . \'/../data/fake_data.json\'), true);
$products = $fake_data[\'products\'];
$categories = $fake_data[\'categories\'];',
        'new' => '<?php
// Load Models
require_once __DIR__ . \'/../../../models/ProductsModel.php\';
require_once __DIR__ . \'/../../../models/CategoriesModel.php\';

$productsModel = new ProductsModel();
$categoriesModel = new CategoriesModel();'
    ],
    
    // Admin Products Edit
    'app/views/admin/products/edit.php' => [
        'old' => '<?php
// Load fake data
$fake_data = json_decode(file_get_contents(__DIR__ . \'/../data/fake_data.json\'), true);
$products = $fake_data[\'products\'];
$categories = $fake_data[\'categories\'];',
        'new' => '<?php
// Load Models
require_once __DIR__ . \'/../../../models/ProductsModel.php\';
require_once __DIR__ . \'/../../../models/CategoriesModel.php\';

$productsModel = new ProductsModel();
$categoriesModel = new CategoriesModel();'
    ],
    
    // Admin Products Add
    'app/views/admin/products/add.php' => [
        'old' => '<?php
// Load fake data for categories
$fake_data = json_decode(file_get_contents(__DIR__ . \'/../data/fake_data.json\'), true);
$categories = $fake_data[\'categories\'];',
        'new' => '<?php
// Load Categories Model
require_once __DIR__ . \'/../../../models/CategoriesModel.php\';

$categoriesModel = new CategoriesModel();
$categories = $categoriesModel->getActive();'
    ],
    
    // Admin Categories View
    'app/views/admin/categories/view.php' => [
        'old' => '<?php
// Load fake data
$fake_data = json_decode(file_get_contents(__DIR__ . \'/../data/fake_data.json\'), true);
$categories = $fake_data[\'categories\'];
$products = $fake_data[\'products\'];',
        'new' => '<?php
// Load Models
require_once __DIR__ . \'/../../../models/CategoriesModel.php\';
require_once __DIR__ . \'/../../../models/ProductsModel.php\';

$categoriesModel = new CategoriesModel();
$productsModel = new ProductsModel();'
    ],
    
    // Admin Categories Edit
    'app/views/admin/categories/edit.php' => [
        'old' => '<?php
// Load fake data
$fake_data = json_decode(file_get_contents(__DIR__ . \'/../data/fake_data.json\'), true);
$categories = $fake_data[\'categories\'];',
        'new' => '<?php
// Load Categories Model
require_once __DIR__ . \'/../../../models/CategoriesModel.php\';

$categoriesModel = new CategoriesModel();'
    ]
];

// Thực hiện chuyển đổi
$converted = 0;
$failed = 0;

foreach ($conversions as $file => $conversion) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        if (strpos($content, $conversion['old']) !== false) {
            $newContent = str_replace($conversion['old'], $conversion['new'], $content);
            
            if (file_put_contents($file, $newContent)) {
                echo "✅ Đã chuyển đổi: $file\n";
                $converted++;
            } else {
                echo "❌ Lỗi khi ghi file: $file\n";
                $failed++;
            }
        } else {
            echo "⚠️  Không tìm thấy pattern trong file: $file\n";
        }
    } else {
        echo "❌ File không tồn tại: $file\n";
        $failed++;
    }
}

echo "\n=== KẾT QUẢ ===\n";
echo "Đã chuyển đổi: $converted file\n";
echo "Thất bại: $failed file\n";
echo "=== HOÀN THÀNH ===\n";