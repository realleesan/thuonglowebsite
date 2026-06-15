<?php
// Bật hiển thị lỗi tối đa để dễ dàng debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('THUONGLO_INIT', true);

try {
    require_once __DIR__ . '/core/database.php';
    $db = Database::getInstance();

    // Query an toàn sử dụng * để tránh lỗi nếu cột deleted_at không tồn tại
    $sql = "SELECT * FROM products WHERE id = 181";
    $products = $db->query($sql);

    header('Content-Type: text/plain; charset=UTF-8');
    if (empty($products)) {
        echo "Sản phẩm có ID 181 không tồn tại trong cơ sở dữ liệu.\n";
    } else {
        echo "THÔNG TIN SẢN PHẨM ID 181 TRONG DATABASE:\n";
        echo "----------------------------------------\n";
        foreach ($products[0] as $key => $value) {
            echo sprintf("%-25s: %s\n", $key, ($value === null ? 'NULL' : $value));
        }
    }
} catch (Exception $e) {
    header('Content-Type: text/plain; charset=UTF-8', true, 500);
    echo "LỖI KHI TRUY VẤN DATABASE:\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
