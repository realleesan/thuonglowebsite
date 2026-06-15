<?php
define('THUONGLO_INIT', true);
$config = require __DIR__ . '/config.php';
require_once __DIR__ . '/core/functions.php';

echo "=== IMAGE HELPER VERIFICATION ===\n\n";

// Test cases for resolve_image_path
$testCases = [
    // External urls
    'https://images.unsplash.com/photo-1441986300917-64674bd600d8' => 'Fallback expected (unsplash)',
    'https://eduma.thimpress.com/demo-marketplace/wp-content/uploads/sites/99/2022/11/create-an-lms-website-with-learnpress-4-675x450.png' => 'Fallback expected (thimpress)',
    'https://example.com/some-other-valid-image.jpg' => 'Pass-through expected (valid external)',
    
    // Relative local paths
    '/assets/uploads/categories/1778577526_B866-1.jpg' => 'Fallback expected (non-existent)',
    'about/about_tt&tt_1.jpg' => 'Should resolve to assets/images/about/about_tt&tt_1.jpg',
    'about/about_tt&tt_2.jpg' => 'Should resolve to assets/images/about/about_tt&tt_2.jpg',
    
    // Empty / null
    '' => 'Fallback expected (empty)',
    null => 'Fallback expected (null)'
];

echo "Testing resolve_image_path directly:\n";
foreach ($testCases as $input => $expected) {
    $resolved = resolve_image_path($input, 'home/no-image.png');
    echo "Input: " . var_export($input, true) . "\n";
    echo "Expected behavior: $expected\n";
    echo "Resolved URL: $resolved\n\n";
}

echo "Testing module-specific helpers:\n";
echo "getNewsImage('about/about_tt&tt_1.jpg') -> " . getNewsImage('about/about_tt&tt_1.jpg') . "\n";
echo "getNewsImage(['image' => 'about/about_tt&tt_1.jpg']) -> " . getNewsImage(['image' => 'about/about_tt&tt_1.jpg']) . "\n";
echo "getNewsImage('/assets/uploads/news/non-existent.jpg') -> " . getNewsImage('/assets/uploads/news/non-existent.jpg') . "\n";
echo "getCategoryImage('/assets/uploads/categories/non-existent.jpg') -> " . getCategoryImage('/assets/uploads/categories/non-existent.jpg') . "\n";
echo "getProductImage('https://images.unsplash.com/photo-1441986300917-64674bd600d8') -> " . getProductImage('https://images.unsplash.com/photo-1441986300917-64674bd600d8') . "\n";

echo "\n=== END VERIFICATION ===\n";
