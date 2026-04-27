<?php
/**
 * Script kích hoạt tất cả danh mục thành "Danh mục nổi bật"
 * Chạy: http://localhost/thuonglowebsite/activate_featured_categories.php
 */

require_once __DIR__ . '/core/database.php';

echo "<h2>Kích hoạt tất cả danh mục thành Danh mục nổi bật</h2>";

try {
    $db = Database::getInstance();
    
    // Đếm số danh mục hiện tại chưa là nổi bật
    $countResult = $db->query("SELECT COUNT(*) as total FROM categories WHERE featured = 0 OR featured IS NULL");
    $totalToUpdate = $countResult[0]['total'] ?? 0;
    
    echo "<p>Số danh mục cần kích hoạt: <strong>{$totalToUpdate}</strong></p>";
    
    if ($totalToUpdate > 0) {
        // Cập nhật tất cả danh mục thành featured = 1
        $result = $db->query("UPDATE categories SET featured = 1 WHERE featured = 0 OR featured IS NULL");
        
        // Kiểm tra số dòng bị ảnh hưởng
        $affectedRows = $db->query("SELECT ROW_COUNT() as affected");
        $updated = $affectedRows[0]['affected'] ?? 0;
        
        echo "<p style='color: green;'>✓ Đã kích hoạt thành công <strong>{$updated}</strong> danh mục!</p>";
    } else {
        echo "<p style='color: blue;'>Tất cả danh mục đã được kích hoạt sẵn.</p>";
    }
    
    // Hiển thị danh sách danh mục sau khi cập nhật
    echo "<h3>Danh sách danh mục hiện tại:</h3>";
    $categories = $db->query("SELECT id, name, featured, show_in_filter FROM categories ORDER BY id ASC");
    
    echo "<table border='1' cellpadding='8'>";
    echo "<tr><th>ID</th><th>Tên danh mục</th><th>Danh mục nổi bật</th><th>Hiển thị ở bộ lọc</th></tr>";
    
    foreach ($categories as $cat) {
        $featuredBadge = $cat['featured'] 
            ? "<span style='color: green;'>✓ Có</span>" 
            : "<span style='color: red;'>✗ Không</span>";
        $filterBadge = $cat['show_in_filter'] 
            ? "<span style='color: green;'>✓ Có</span>" 
            : "<span style='color: red;'>✗ Không</span>";
        
        echo "<tr>";
        echo "<td>{$cat['id']}</td>";
        echo "<td>" . htmlspecialchars($cat['name']) . "</td>";
        echo "<td>{$featuredBadge}</td>";
        echo "<td>{$filterBadge}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Lỗi: " . $e->getMessage() . "</p>";
}

echo "<p><a href='?page=admin&module=categories'>← Quay lại trang quản lý danh mục</a></p>";
