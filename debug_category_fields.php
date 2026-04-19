<?php
/**
 * Debug script - Kiểm tra category fields trong database
 */

require_once __DIR__ . '/core/database.php';

// Kết nối database
try {
    $db = Database::getInstance();
    echo "<h2>Kiểm tra cấu trúc bảng categories</h2>";

    // Lấy thông tin cột
    $columns = $db->query("DESCRIBE categories");

    echo "<h3>Các cột trong bảng categories:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Default</th></tr>";

    $has_show_in_menu = false;
    $has_featured = false;
    $has_show_in_filter = false;

    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "</tr>";

        if ($col['Field'] == 'show_in_menu') $has_show_in_menu = true;
        if ($col['Field'] == 'featured') $has_featured = true;
        if ($col['Field'] == 'show_in_filter') $has_show_in_filter = true;
    }
    echo "</table>";

    echo "<h3>Kiểm tra các trường mới:</h3>";
    echo "<ul>";
    echo "<li>show_in_menu: " . ($has_show_in_menu ? "<span style='color:green'>✓ CÓ</span>" : "<span style='color:red'>✗ KHÔNG CÓ</span>") . "</li>";
    echo "<li>featured: " . ($has_featured ? "<span style='color:green'>✓ CÓ</span>" : "<span style='color:red'>✗ KHÔNG CÓ</span>") . "</li>";
    echo "<li>show_in_filter: " . ($has_show_in_filter ? "<span style='color:green'>✓ CÓ</span>" : "<span style='color:red'>✗ KHÔNG CÓ</span>") . "</li>";
    echo "</ul>";

    // Lấy dữ liệu một category để kiểm tra
    echo "<h3>Dữ liệu category ID=1 (nếu có):</h3>";
    $category = $db->query("SELECT * FROM categories WHERE id = 1 LIMIT 1");
    if (!empty($category)) {
        echo "<pre>";
        print_r($category[0]);
        echo "</pre>";
    } else {
        echo "Không tìm thấy category ID=1";
    }

    // Kiểm tra tất cả categories
    echo "<h3>Tất cả categories:</h3>";
    $all = $db->query("SELECT id, name, show_in_menu, featured, show_in_filter FROM categories LIMIT 10");
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Name</th><th>show_in_menu</th><th>featured</th><th>show_in_filter</th></tr>";
    foreach ($all as $cat) {
        echo "<tr>";
        echo "<td>{$cat['id']}</td>";
        echo "<td>{$cat['name']}</td>";
        echo "<td>" . (isset($cat['show_in_menu']) ? $cat['show_in_menu'] : 'N/A') . "</td>";
        echo "<td>" . (isset($cat['featured']) ? $cat['featured'] : 'N/A') . "</td>";
        echo "<td>" . (isset($cat['show_in_filter']) ? $cat['show_in_filter'] : 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</table>";

} catch (Exception $e) {
    echo "Lỗi: " . $e->getMessage();
}
