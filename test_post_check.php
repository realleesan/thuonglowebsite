<?php
/**
 * Simple test - check what value is being sent in POST
 * 
 * Chạy: http://test1.web3b.com/test_post_check.php?id=2
 */

echo "=== TEST SIMPLE POST ===\n\n";

echo "REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "POST data:\n";
    print_r($_POST);
    
    echo "\nimage_url value: '" . ($_POST['image_url'] ?? 'EMPTY') . "'\n";
    
    // Check if it's base64
    $url = $_POST['image_url'] ?? '';
    if (strpos($url, 'data:') === 0) {
        echo "\nWARNING: Value is BASE64 data, not a URL!\n";
    } elseif (strpos($url, 'http') === 0) {
        echo "\nOK: Value is a valid URL\n";
    } else {
        echo "\nWARNING: Value is not a valid URL format\n";
    }
} else {
    echo "This is a GET request. Add ?test=1 to test POST.\n";
}
