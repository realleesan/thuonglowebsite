<?php
/**
 * News Details Page - Trang chi tiết tin tức
 * Fixed version with proper error handling
 */

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start output buffering early
ob_start();

// 1. Khởi tạo View an toàn & ServiceManager
require_once __DIR__ . '/../../../core/view_init.php';

// Load required models with existence check
$modelsPath = __DIR__ . '/../../models/';
$newsModelPath = $modelsPath . 'NewsModel.php';
$categoriesModelPath = $modelsPath . 'CategoriesModel.php';

if (!file_exists($newsModelPath)) {
    die("Error: NewsModel.php not found at: " . $newsModelPath);
}
if (!file_exists($categoriesModelPath)) {
    die("Error: CategoriesModel.php not found at: " . $categoriesModelPath);
}

require_once $newsModelPath;
require_once $categoriesModelPath;

// Verify classes are loaded
if (!class_exists('NewsModel')) {
    die("Error: NewsModel class not loaded");
}
if (!class_exists('CategoriesModel')) {
    die("Error: CategoriesModel class not loaded");
}

error_log("News details page: Models loaded successfully");
?>
