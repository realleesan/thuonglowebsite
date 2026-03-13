<?php
/**
 * Test full loading of add.php including view_init.php
 */

echo "<h1>Test Full Load of add.php</h1>";

echo "<h2>Step 1: Include config.php</h2>";
require_once __DIR__ . '/config.php';
echo "<p style='color:green'>✓ config.php loaded</p>";

echo "<h2>Step 2: Include core/database.php</h2>";
require_once __DIR__ . '/core/database.php';
echo "<p style='color:green'>✓ database.php loaded</p>";

echo "<h2>Step 3: Include core/view_init.php</h2>";
try {
    require_once __DIR__ . '/core/view_init.php';
    echo "<p style='color:green'>✓ view_init.php loaded</p>";
    
    // Check what variables are defined
    echo "<p>Defined variables after view_init:</p>";
    echo "<pre>" . print_r(get_defined_vars(), true) . "</pre>";
} catch (Throwable $e) {
    echo "<p style='color:red'>✗ Error loading view_init.php: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    exit;
}

echo "<h2>Step 4: Simulate GET params</h2>";
$_GET = [
    'page' => 'admin',
    'module' => 'products',
    'action' => 'add'
];
echo "<pre>" . print_r($_GET, true) . "</pre>";

echo "<h2>Step 5: Simulate POST data (form submission)</h2>";
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    'name'             => 'Full Test Product ' . date('Y-m-d H:i:s'),
    'category_id'      => 1,
    'price'            => 1500000,
    'description'      => 'Full test description',
    'status'           => 'active',
    'type'             => 'data_nguon_hang',
    'sale_price'       => 990000,
    'expiry_days'      => 30,
    'sku'              => 'FULL-TEST-' . time(),
    'short_description'=> 'Full test short',
    'record_count'     => 100,
    'data_size'        => '20 KB',
    'data_format'      => 'Excel',
    'data_source'      => 'Vietnam',
    'reliability'      => '95%',
    'quota'            => 50,
    'quota_per_usage'  => 5,
    'supplier_name'    => 'Full Test Supplier',
    'supplier_title'   => 'Supplier Title',
    'supplier_bio'     => 'Supplier Bio',
    'supplier_social'  => '{}',
    'benefits'         => '[]',
    'data_structure'   => '[]',
    'featured'         => '1'
];
$_FILES = [];

echo "<h2>Step 6: Check if \$service or \$adminService is available</h2>";
if (isset($service)) {
    echo "<p style='color:green'>✓ \$service is defined</p>";
} elseif (isset($adminService)) {
    echo "<p style='color:green'>✓ \$adminService is defined</p>";
    $service = $adminService;
} else {
    echo "<p style='color:orange'>⚠ Neither \$service nor \$adminService is defined - this might be the issue!</p>";
}

echo "<h2>Step 7: Try to get categories like in add.php</h2>";
try {
    if (isset($service)) {
        $categoriesData = $service->getActiveCategoriesForDropdown();
        $categories = $categoriesData['categories'] ?? [];
        echo "<p style='color:green'>✓ Got categories: " . count($categories) . " categories</p>";
    } else {
        echo "<p style='color:orange'>⚠ No service available, using default</p>";
        $categories = [];
    }
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Error getting categories: " . $e->getMessage() . "</p>";
    $categories = [];
}

echo "<h2>Step 8: Run the form processing logic from add.php</h2>";
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'active';
    
    if (empty($name)) {
        $errors[] = 'Tên data không được để trống';
    }
    
    if ($category_id <= 0) {
        $errors[] = 'Vui lòng chọn danh mục';
    }
    
    if ($price <= 0) {
        $errors[] = 'Giá data phải lớn hơn 0';
    }
    
    if (empty($description)) {
        $errors[] = 'Mô tả data không được để trống';
    }
    
    $image_path = '';
    // Skip file upload for testing
    
    if (empty($errors)) {
        echo "<p style='color:green'>✓ Validation passed</p>";
        
        require_once __DIR__ . '/app/models/ProductsModel.php';
        $productsModel = new ProductsModel();
        
        $record_count = isset($_POST['record_count']) && $_POST['record_count'] !== '' ? (int)$_POST['record_count'] : 0;
        
        function createSlugProduct($str) {
            $str = strtolower($str);
            $str = preg_replace('/[^a-z0-9\s-]/', '', $str);
            $str = preg_replace('/\s+/', '-', $str);
            return trim($str, '-');
        }
        
        $insertData = [
            'name'             => $name,
            'slug'             => createSlugProduct($name),
            'category_id'      => $category_id,
            'price'            => $price,
            'stock'            => $record_count,
            'description'      => $description,
            'status'           => $status,
            'type'             => $_POST['type'] ?? 'data_nguon_hang',
            'sale_price'       => isset($_POST['sale_price']) && $_POST['sale_price'] !== '' ? (float)$_POST['sale_price'] : null,
            'expiry_days'      => isset($_POST['expiry_days']) && $_POST['expiry_days'] !== '' ? (int)$_POST['expiry_days'] : 30,
            'sku'              => $_POST['sku'] ?? '',
            'short_description'=> $_POST['short_description'] ?? '',
            'image'            => $image_path,
            'record_count'     => $record_count,
            'data_size'        => $_POST['data_size'] ?? '',
            'data_format'      => $_POST['data_format'] ?? '',
            'data_source'      => $_POST['data_source'] ?? '',
            'reliability'      => $_POST['reliability'] ?? '',
            'quota'            => isset($_POST['quota']) && $_POST['quota'] !== '' ? (int)$_POST['quota'] : 100,
            'quota_per_usage'  => isset($_POST['quota_per_usage']) && $_POST['quota_per_usage'] !== '' ? (int)$_POST['quota_per_usage'] : 10,
            'supplier_name'    => $_POST['supplier_name'] ?? '',
            'supplier_title'   => $_POST['supplier_title'] ?? '',
            'supplier_bio'     => $_POST['supplier_bio'] ?? '',
            'supplier_avatar'  => $_POST['supplier_avatar'] ?? '',
            'supplier_social'  => $_POST['supplier_social'] ?? '',
            'benefits'         => $_POST['benefits'] ?? '',
            'data_structure'   => $_POST['data_structure'] ?? '',
            'digital'          => 1,
            'featured'         => isset($_POST['featured']) ? 1 : 0,
            'downloadable'     => isset($_POST['downloadable']) ? 1 : 0,
            'created_at'       => date('Y-m-d H:i:s')
        ];
        
        echo "<h2>Step 9: Call productsModel->create()</h2>";
        try {
            $id = $productsModel->create($insertData);
            if ($id) {
                echo "<p style='color:green'>✓ Product created with ID: " . $id . "</p>";
                
                // Test redirect header
                echo "<h2>Step 10: Test redirect</h2>";
                header('Location: ?page=admin&module=products');
                echo "<p>Header sent: Location: ?page=admin&module=products</p>";
            } else {
                echo "<p style='color:red'>✗ create() returned false</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color:red'>✗ Exception: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color:red'>Validation errors:</p>";
        echo "<ul>" . implode('', array_map(fn($e) => "<li>$e</li>", $errors)) . "</ul>";
    }
}

echo "<h2>Test Complete</h2>";
?>
