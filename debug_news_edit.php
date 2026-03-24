<?php
/**
 * Debug Test File - Kiểm tra vấn đề form submission và beforeunload warning
 * SIMPLE VERSION - Sử dụng cách tiếp cận khác để tránh lỗi protected method
 * 
 * Chạy file này tại: http://test1.web3b.com/debug_news_edit.php?id=2
 */

// Khởi tạo môi trường
require_once __DIR__ . '/core/view_init.php';

// Include model
require_once __DIR__ . '/app/models/NewsModel.php';

$service = isset($currentService) ? $currentService : ($adminService ?? null);

if (!$service) {
    die("ERROR: Service not available. Please access through admin panel.");
}

echo "=== DEBUG: NEWS EDIT FORM SUBMISSION ===\n\n";

// Lấy news ID
$news_id = (int)($_GET['id'] ?? 0);
echo "News ID: " . $news_id . "\n\n";

// Lấy dữ liệu ban đầu từ DB - sử dụng NewsModel trực tiếp
echo "--- 1. LẤY DỮ LIỆU TỪ DATABASE ---\n";
try {
    $newsModel = new NewsModel();
    $newsRaw = $newsModel->find($news_id);
    if ($newsRaw) {
        echo "Raw data from DB:\n";
        echo "  - title: " . (isset($newsRaw['title']) ? substr($newsRaw['title'], 0, 50) : 'NULL') . "\n";
        echo "  - content: " . (isset($newsRaw['content']) ? substr($newsRaw['content'], 0, 100) . '...' : 'NULL') . "\n";
        echo "  - excerpt: " . (isset($newsRaw['excerpt']) ? substr($newsRaw['excerpt'], 0, 50) : 'NULL') . "\n";
    } else {
        echo "Không tìm thấy tin tức!\n";
    }
} catch (Exception $e) {
    echo "Error getting raw data: " . $e->getMessage() . "\n";
}
echo "\n";

// Transform dữ liệu
echo "--- 2. SAU KHI TRANSFORM ---\n";
try {
    $newsTransformed = $service->getNewsDetailsData($news_id);
    if ($newsTransformed['news']) {
        echo "Transformed data:\n";
        echo "  - title: " . (isset($newsTransformed['news']['title']) ? substr($newsTransformed['news']['title'], 0, 50) : 'NULL') . "\n";
        echo "  - content: " . (isset($newsTransformed['news']['content']) ? substr($newsTransformed['news']['content'], 0, 100) . '...' : 'NULL') . "\n";
        echo "  - excerpt: " . (isset($newsTransformed['news']['excerpt']) ? substr($newsTransformed['news']['excerpt'], 0, 50) : 'NULL') . "\n";
    } else {
        echo "Transformed data is NULL!\n";
    }
} catch (Exception $e) {
    echo "Error transforming data: " . $e->getMessage() . "\n";
}
echo "\n";

// Kiểm tra POST data
echo "--- 3. KIỂM TRA POST DATA ---\n";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "POST method detected!\n";
    echo "POST data:\n";
    print_r($_POST);
    
    // Check validation
    echo "\n--- 4. VALIDATION ---\n";
    $form_data = array_merge([
        'title' => '',
        'slug' => '',
        'content' => '',
        'excerpt' => '',
        'author' => ''
    ], $_POST);
    
    echo "Merged form_data:\n";
    echo "  - title: '" . $form_data['title'] . "'\n";
    echo "  - slug: '" . $form_data['slug'] . "'\n";
    echo "  - content: '" . substr($form_data['content'] ?? '', 0, 100) . "'\n";
    echo "  - excerpt: '" . $form_data['excerpt'] . "'\n";
    echo "  - author: '" . $form_data['author'] . "'\n";
    
    // Check empty
    echo "\nValidation check:\n";
    echo "  - empty(title): " . (empty($form_data['title']) ? 'YES' : 'NO') . "\n";
    echo "  - empty(content): " . (empty($form_data['content']) ? 'YES' : 'NO') . "\n";
    echo "  - empty(excerpt): " . (empty($form_data['excerpt']) ? 'YES' : 'NO') . "\n";
    echo "  - empty(author): " . (empty($form_data['author']) ? 'YES' : 'NO') . "\n";
    
    // Transform data before update
    echo "\n--- 5. DATA TRƯỚC KHI UPDATE ---\n";
    $update_data = $form_data;
    if (isset($update_data['author'])) {
        $update_data['author_name'] = $update_data['author'];
        unset($update_data['author']);
    }
    
    echo "Update data keys: " . implode(', ', array_keys($update_data)) . "\n";
    echo "content in update_data: " . (isset($update_data['content']) ? 'YES' : 'NO') . "\n";
    
    // Check fillable - sử dụng Reflection
    echo "\n--- 6. KIỂM TRA FILLABLE FILTER ---\n";
    try {
        $newsModel = new NewsModel();
        $reflection = new ReflectionClass($newsModel);
        $property = $reflection->getProperty('fillable');
        $property->setAccessible(true);
        $fillable = $property->getValue($newsModel);
        
        echo "NewsModel fillable: " . implode(', ', $fillable) . "\n";
        echo "content in fillable: " . (in_array('content', $fillable) ? 'YES' : 'NO') . "\n";
        
        // Filter fillable - sử dụng Reflection
        $method = $reflection->getMethod('filterFillable');
        $method->setAccessible(true);
        $filteredData = $method->invoke($newsModel, $update_data);
        
        echo "\nAfter filterFillable:\n";
        echo "  Keys: " . implode(', ', array_keys($filteredData)) . "\n";
        echo "  content: " . (isset($filteredData['content']) ? 'YES - value: ' . substr($filteredData['content'], 0, 50) : 'NO') . "\n";
    } catch (Exception $e) {
        echo "Error accessing protected: " . $e->getMessage() . "\n";
    }
    
    // Perform update
    echo "\n--- 7. THỰC HIỆN UPDATE ---\n";
    try {
        $result = $service->updateNews($news_id, $update_data);
        echo "Update result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
    } catch (Exception $e) {
        echo "Error updating: " . $e->getMessage() . "\n";
    }
    
    // Check updated data
    echo "\n--- 8. SAU KHI UPDATE - KIỂM TRA LẠI DB ---\n";
    try {
        $newsAfterUpdate = $newsModel->find($news_id);
        if ($newsAfterUpdate) {
            echo "Data in DB after update:\n";
            echo "  - title: " . $newsAfterUpdate['title'] . "\n";
            echo "  - content: " . (isset($newsAfterUpdate['content']) ? substr($newsAfterUpdate['content'], 0, 100) . '...' : 'NULL/EMPTY') . "\n";
            echo "  - content length: " . strlen($newsAfterUpdate['content'] ?? '') . "\n";
        }
    } catch (Exception $e) {
        echo "Error getting updated data: " . $e->getMessage() . "\n";
    }
    
} else {
    echo "Chưa có POST request. Thêm ?id=2 vào URL và submit form để test.\n";
}

echo "\n=== END DEBUG ===\n";
