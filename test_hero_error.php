<?php
/**
 * Test Hero Section Error & Database Connection for Hosting - Deep Integration Test
 */
define('THUONGLO_INIT', true);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<div style='font-family: Arial, sans-serif; padding: 20px; line-height: 1.6;'>";
echo "<h2 style='color: #1e3a8a; border-bottom: 2px solid #3b82f6; padding-bottom: 8px;'>🔍 KIỂM TRA SÂU LỖI CẬP NHẬT HERO SECTION</h2>";

try {
    if (!file_exists(__DIR__ . '/config.php')) {
        throw new Exception("Không tìm thấy tệp config.php ở thư mục gốc!");
    }
    
    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/core/database.php';
    
    $db = Database::getInstance();
    echo "<p style='color: green; font-weight: bold;'>✔ Kết nối database trên Hosting thành công!</p>";
    
    // Kiểm tra bản ghi đầu tiên
    $firstHero = $db->table('hero_sections')->first();
    if ($firstHero) {
        $id = $firstHero['id'];
        echo "<p style='color: green;'>✔ Tìm thấy bản ghi Hero Section đang hoạt động (ID = $id)</p>";
        
        // CẬP NHẬT THỬ NGHIỆM VỚI DỮ LIỆU ĐẦY ĐỦ NHƯ AJAX GỬI LÊN
        echo "<h3 style='color: #2563eb;'>3. Thử nghiệm cập nhật giả lập 100% dữ liệu AJAX gửi lên:</h3>";
        
        require_once __DIR__ . '/app/models/HeroSectionModel.php';
        $model = new HeroSectionModel();
        
        // Mô phỏng chính xác dữ liệu từ frontend gửi lên
        $testData = [
            'title_main' => $firstHero['title_main'],
            'subtitle' => $firstHero['subtitle'],
            'image_url' => $firstHero['image_url'] ?? '',
            'background_color' => $firstHero['background_color'] ?? '#ffffff',
            'is_active' => (int)$firstHero['is_active'],
            'title_highlight' => '', // Dữ liệu AJAX gửi
            'text_color' => '#333333', // Dữ liệu AJAX gửi
            'highlight_color' => '#356DF1', // Dữ liệu AJAX gửi
            'font_family' => 'Arial, sans-serif' // Dữ liệu AJAX gửi
        ];
        
        echo "<p>Bộ dữ liệu kiểm tra chuyên sâu:</p>";
        echo "<pre style='background: #f8fafc; padding: 10px; border-radius: 4px; border: 1px solid #e2e8f0;'>" . htmlspecialchars(print_r($testData, true)) . "</pre>";
        
        // Chạy cập nhật qua model
        $result = $model->updateHeroSection($id, $testData);
        if ($result) {
            echo "<p style='color: green; font-weight: bold;'>✔ THỬ NGHIỆM CẬP NHẬT TRÊN MODEL THÀNH CÔNG! Không phát sinh lỗi SQL.</p>";
            echo "<p>Dữ liệu sau khi cập nhật trong Database:</p>";
            echo "<pre style='background: #f0fdf4; padding: 10px; border-radius: 4px; border: 1px solid #bbf7d0;'>" . htmlspecialchars(print_r($result, true)) . "</pre>";
        } else {
            echo "<p style='color: orange; font-weight: bold;'>⚠️ Hàm update() của Model trả về false (Không thay đổi dữ liệu hoặc thất bại).</p>";
        }
        
    } else {
        echo "<p style='color: red; font-weight: bold;'>❌ Bảng `hero_sections` hiện không có bản ghi nào để cập nhật thử nghiệm!</p>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #fef2f2; border-left: 4px solid #ef4444; padding: 15px; margin-top: 20px;'>";
    echo "<h3 style='color: #991b1b; margin-top: 0;'>❌ PHÁT HIỆN LỖI PHÁT SINH KHI CẬP NHẬT:</h3>";
    echo "<p style='color: #b91c1c; font-weight: bold;'>Thông điệp lỗi: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Tệp lỗi: {$e->getFile()} (Dòng {$e->getLine()})</p>";
    echo "<h4>Stack Trace:</h4>";
    echo "<pre style='background: #fff; padding: 10px; border: 1px solid #fee2e2; font-size: 12px; overflow-x: auto;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}

echo "</div>";
