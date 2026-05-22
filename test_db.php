<?php
// Tệp kiểm tra lỗi kết nối CSDL và bảng sub_pages
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/app/models/SubPageModel.php';

echo "<h2>BẮT ĐẦU KIỂM TRA CƠ SỞ DỮ LIỆU</h2>";

try {
    // Sử dụng Database wrapper của hệ thống để kết nối chuẩn môi trường
    $dbInstance = Database::getInstance();
    $db = $dbInstance->getPdo();
    echo "<p style='color: green;'>✔ Kết nối PDO tới database thành công qua hệ thống!</p>";

    // Kiểm tra xem bảng sub_pages có tồn tại không
    $stmt = $db->query("SHOW TABLES LIKE 'sub_pages'");
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "<p style='color: green;'>✔ Bảng `sub_pages` tồn tại!</p>";
        
        // Mô tả cấu trúc bảng
        echo "<h3>Cấu trúc bảng `sub_pages`:</h3><pre>";
        $columns = $db->query("DESCRIBE sub_pages")->fetchAll(PDO::FETCH_ASSOC);
        print_r($columns);
        echo "</pre>";

        // Đếm số dòng
        $count = $db->query("SELECT COUNT(*) FROM sub_pages")->fetchColumn();
        echo "<p>Số lượng bản ghi hiện tại trong bảng `sub_pages`: <strong>$count</strong></p>";
        
        if ($count == 0) {
            echo "<p style='color: orange;'>⚠️ Bảng trống! Đang tiến hành chạy seedDefaultSubPages()...</p>";
            $model = new SubPageModel();
            $seedResult = $model->seedDefaultSubPages(true);
            if ($seedResult) {
                echo "<p style='color: green;'>✔ Chạy seedDefaultSubPages() THÀNH CÔNG!</p>";
                $newCount = $db->query("SELECT COUNT(*) FROM sub_pages")->fetchColumn();
                echo "<p>Số lượng bản ghi sau khi seed: <strong>$newCount</strong></p>";
            } else {
                echo "<p style='color: red;'>❌ Chạy seedDefaultSubPages() THẤT BẠI!</p>";
            }
        } else {
            echo "<h3>Dữ liệu hiện có:</h3><pre>";
            $data = $db->query("SELECT id, page_key, title, image FROM sub_pages")->fetchAll(PDO::FETCH_ASSOC);
            print_r($data);
            echo "</pre>";
        }

    } else {
        echo "<p style='color: red;'>❌ Bảng `sub_pages` KHÔNG TỒN TẠI trong cơ sở dữ liệu!</p>";
    }

} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Lỗi CSDL: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
