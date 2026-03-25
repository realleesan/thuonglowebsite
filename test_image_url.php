<?php
/**
 * Test file for debugging image URL issue
 * 
 * Chạy: http://test1.web3b.com/test_image_url.php?id=2
 * 
 * Submit form để xem dữ liệu được gửi
 */

require_once __DIR__ . '/core/view_init.php';
require_once __DIR__ . '/app/models/NewsModel.php';

$service = isset($currentService) ? $currentService : ($adminService ?? null);

echo "=== TEST IMAGE URL SUBMISSION ===\n\n";

$news_id = (int)($_GET['id'] ?? 0);
echo "News ID: " . $news_id . "\n\n";

// Show current image
$newsModel = new NewsModel();
$currentNews = $newsModel->find($news_id);
echo "Current image in DB: " . ($currentNews['image'] ?? 'NULL') . "\n\n";

echo "--- POST DATA ---\n";
echo "REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "_POST:\n";
print_r($_POST);
echo "\n_FILES:\n";
print_r($_FILES);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "\n--- ANALYSIS ---\n";
    echo "empty(\$_FILES['image']['name']): " . (empty($_FILES['image']['name']) ? 'TRUE' : 'FALSE') . "\n";
    echo "empty(\$_POST['image_url']): " . (empty($_POST['image_url']) ? 'TRUE' : 'FALSE') . "\n";
    echo "isset(\$_POST['image_url']): " . (isset($_POST['image_url']) ? 'TRUE' : 'FALSE') . "\n";
    
    if (!empty($_POST['image_url'])) {
        echo "\n=> Will update image to: " . $_POST['image_url'] . "\n";
        
        // Test update
        $testUpdate = ['image' => trim($_POST['image_url'])];
        echo "\nTest update data:\n";
        print_r($testUpdate);
    } else {
        echo "\n=> Will keep existing image\n";
    }
}

echo "\n=== END TEST ===\n";
