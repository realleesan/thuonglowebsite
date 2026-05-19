<?php
/**
 * Debug file for news add/edit pages
 */

// Bật error reporting để thấy lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debug News Pages</h1>";

// Test 1: Kiểm tra basic PHP
echo "<h2>Test 1: Basic PHP</h2>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "Current working directory: " . __DIR__ . "<br>";

// Test 2: Kiểm tra require view_init.php
echo "<h2>Test 2: Loading view_init.php</h2>";
try {
    require_once __DIR__ . '/core/view_init.php';
    echo "✅ view_init.php loaded successfully<br>";
} catch (Exception $e) {
    echo "❌ Error loading view_init.php: " . $e->getMessage() . "<br>";
}

// Test 3: Kiểm tra CategoriesModel
echo "<h2>Test 3: CategoriesModel</h2>";
try {
    // Load CategoriesModel first
    require_once __DIR__ . '/app/models/CategoriesModel.php';
    echo "✅ CategoriesModel loaded successfully<br>";
    
    $categoriesModel = new \CategoriesModel();
    echo "✅ CategoriesModel created successfully<br>";
    
    // Test query
    $news_categories = $categoriesModel->query("SELECT * FROM categories WHERE type = 'news' AND status = 'active' ORDER BY name ASC LIMIT 5") ?? [];
    echo "✅ Query executed successfully. Found " . count($news_categories) . " categories<br>";
    
    if (!empty($news_categories)) {
        echo "<pre>" . print_r($news_categories[0], true) . "</pre>";
    }
} catch (Exception $e) {
    echo "❌ Error with CategoriesModel: " . $e->getMessage() . "<br>";
}

// Test 4: Kiểm tra AdminService
echo "<h2>Test 4: AdminService</h2>";
try {
    if (class_exists('\AdminService')) {
        $adminService = new \AdminService(null, 'admin');
        echo "✅ AdminService created successfully<br>";
    } else {
        echo "❌ AdminService class not found<br>";
    }
} catch (Exception $e) {
    echo "❌ Error with AdminService: " . $e->getMessage() . "<br>";
}

// Test 5: Kiểm tra session
echo "<h2>Test 5: Session</h2>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
echo "Session status: " . session_status() . "<br>";
echo "Session ID: " . session_id() . "<br>";

// Test 6: Kiểm tra file add.php
echo "<h2>Test 6: Loading add.php step by step</h2>";

// Bước 1: Include file và kiểm tra cú pháp
echo "<h3>Bước 1: Syntax check</h3>";
$add_php_content = file_get_contents(__DIR__ . '/app/views/admin/news/add.php');
echo "File size: " . strlen($add_php_content) . " bytes<br>";

// Kiểm tra cú pháp PHP
$temp_file = tempnam(sys_get_temp_dir(), 'php_syntax_check');
file_put_contents($temp_file, $add_php_content);

$output = [];
$return_var = 0;
exec("php -l " . escapeshellarg($temp_file) . " 2>&1", $output, $return_var);

if ($return_var === 0) {
    echo "✅ PHP syntax is valid<br>";
} else {
    echo "❌ PHP syntax error:<br>";
    echo "<pre>" . implode("\n", $output) . "</pre>";
}

unlink($temp_file);

// Bước 2: Test từng phần của add.php
echo "<h3>Bước 2: Test individual parts</h3>";

try {
    // Test require view_init.php
    require_once __DIR__ . '/core/view_init.php';
    echo "✅ view_init.php loaded<br>";
    
    // Test service availability
    global $adminService, $currentService;
    $service = isset($currentService) ? $currentService : ($adminService ?? null);
    echo "Service available: " . ($service ? "✅ Yes" : "❌ No") . "<br>";
    
    // Test CategoriesModel
    $news_categories = [];
    try {
        $categoriesModel = new \CategoriesModel();
        $news_categories = $categoriesModel->query("SELECT * FROM categories WHERE type = 'news' AND status = 'active' ORDER BY name ASC") ?? [];
        echo "✅ Categories loaded: " . count($news_categories) . " items<br>";
    } catch (Exception $e) {
        echo "⚠️ Categories error: " . $e->getMessage() . "<br>";
    }
    
    // Test form data
    $form_data = [
        'title' => '',
        'slug' => '',
        'content' => '',
        'excerpt' => '',
        'image' => '',
        'status' => 'draft',
        'author' => 'Admin ThuongLo'
    ];
    echo "✅ Form data initialized<br>";
    
} catch (Exception $e) {
    echo "❌ Error in step-by-step test: " . $e->getMessage() . "<br>";
    echo "Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h2>Test Complete</h2>";
echo "<p>If you see this message, the basic PHP functionality is working.</p>";
echo "<p>Check for any ❌ errors above to identify the issue.</p>";
?>
