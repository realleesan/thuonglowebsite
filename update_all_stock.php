<?php
define('THUONGLO_INIT', true);

try {
    require_once __DIR__ . '/core/database.php';
    $db = Database::getInstance();

    // Thực hiện câu lệnh cập nhật kho cho toàn bộ sản phẩm
    $sql = "UPDATE products SET stock = 999999";
    $success = $db->query($sql);

    header('Content-Type: text/plain; charset=UTF-8');
    if ($success) {
        echo "CẬP NHẬT THÀNH CÔNG!\n";
        echo "--------------------\n";
        echo "Tất cả sản phẩm đã được cập nhật tồn kho thành 999999.\n";
    } else {
        echo "Cập nhật thất bại hoặc không có thay đổi nào được thực hiện.\n";
    }
} catch (Exception $e) {
    header('Content-Type: text/plain; charset=UTF-8', true, 500);
    echo "LỖI KHI CẬP NHẬT DATABASE:\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
