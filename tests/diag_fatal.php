<?php
/**
 * TOÀN DIỆN DIAGNOSTIC SCRIPT v2
 * Kiểm tra chuyên sâu lỗi trắng trang trên hosting Linux
 */

// 1. Ép hiển thị lỗi tối đa
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h1>ThuongLo - Deep Diagnostic v2</h1>";

$baseDir = dirname(__DIR__);

// Helper kiểm tra nạp file kèm bắt lỗi
function tryRequire($name, $path) {
    echo "<li>Đang thử nạp <b>$name</b> (<code>$path</code>)... ";
    if (!file_exists($path)) {
        echo "<span style='color:red;'>KHÔNG TÌM THẤY FILE!</span></li>";
        return false;
    }
    try {
        require_once $path;
        echo "<span style='color:green;'>THÀNH CÔNG</span></li>";
        return true;
    } catch (Throwable $e) {
        echo "<span style='color:red;'>THẤT BẠI!</span><br>";
        echo "<div style='background:#fff0f0; border:1px solid red; padding:5px; margin:5px;'>";
        echo "<b>Lỗi:</b> " . $e->getMessage() . "<br>";
        echo "<b>Tại:</b> " . $e->getFile() . " line " . $e->getLine();
        echo "</div></li>";
        return false;
    }
}

echo "<h2>1. Loading Core Files (Kiểm tra lỗi Fatal)</h2>";
echo "<ul>";
tryRequire('Config', $baseDir . '/config.php');
tryRequire('Functions', $baseDir . '/core/functions.php');
tryRequire('Database', $baseDir . '/core/database.php');
tryRequire('ViewInit', $baseDir . '/core/view_init.php');
tryRequire('ViewDataService', $baseDir . '/app/services/ViewDataService.php');
echo "</ul>";

echo "<h2>2. Kiểm tra hàm Helper sau khi nạp</h2>";
echo "<ul>";
$helpers = ['img_url', 'asset_url', 'getProductImage', 'detect_environment', 'init_url_builder'];
foreach ($helpers as $h) {
    $status = function_exists($h) ? "<span style='color:green;'>OK</span>" : "<span style='color:red;'>MISSING</span>";
    echo "<li>Hàm <code>$h()</code>: $status</li>";
}
echo "</ul>";

echo "<h2>3. Kiểm tra DB Connection</h2>";
try {
    if (class_exists('Database')) {
        $db = Database::getInstance();
        echo "<p style='color:green;'>Kết nối DB: OK</p>";
    }
} catch (Throwable $e) {
    echo "<p style='color:red;'>Lỗi DB: " . $e->getMessage() . "</p>";
}

echo "<hr><p>Vui lòng copy kết quả này đưa cho tôi.</p>";
?>
