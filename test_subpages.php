<?php
/**
 * Test SubPages Data Diagnostics
 */
define('THUONGLO_INIT', true);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<div style='font-family: Arial, sans-serif; padding: 20px; line-height: 1.6;'>";
echo "<h2 style='color: #1e3a8a; border-bottom: 2px solid #3b82f6; padding-bottom: 8px;'>🔍 CHẨN ĐOÁN DỮ LIỆU BẢNG SUB_PAGES</h2>";

try {
    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/core/database.php';
    
    $db = Database::getInstance();
    echo "<p style='color: green; font-weight: bold;'>✔ Kết nối database thành công!</p>";
    
    // Lấy toàn bộ bản ghi sub_pages
    $pages = $db->table('sub_pages')->select()->get();
    
    echo "<p>Tìm thấy <b>" . count($pages) . "</b> bản ghi trong bảng `sub_pages`:</p>";
    
    echo "<table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f1f5f9;'>
            <th>ID</th>
            <th>Page Key</th>
            <th>Title</th>
            <th>Content Length</th>
            <th>Sample Content (first 200 chars)</th>
          </tr>";
          
    foreach ($pages as $p) {
        $len = strlen($p['content'] ?? '');
        $sample = htmlspecialchars(mb_substr($p['content'] ?? '', 0, 200) . ($len > 200 ? '...' : ''));
        echo "<tr>";
        echo "<td>{$p['id']}</td>";
        echo "<td style='font-weight: bold; color: #2563eb;'>{$p['page_key']}</td>";
        echo "<td>" . htmlspecialchars($p['title']) . "</td>";
        echo "<td>{$len} bytes</td>";
        echo "<td><pre style='margin: 0; white-space: pre-wrap; font-size: 12px;'>{$sample}</pre></td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Thử chạy hàm seedDefaultSubPages nếu bảng bị thiếu hoặc rỗng
    echo "<h3 style='color: #2563eb; margin-top: 30px;'>Kích hoạt khởi tạo dữ liệu mặc định (Seeding):</h3>";
    require_once __DIR__ . '/app/models/SubPageModel.php';
    $subPageModel = new SubPageModel();
    
    // Seed nếu muốn khôi phục dữ liệu gốc
    echo "<p>Để khôi phục dữ liệu gốc của toàn bộ sub_pages, bạn có thể gọi hàm seedDefaultSubPages(true).</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Lỗi: " . $e->getMessage() . "</p>";
}

echo "</div>";
