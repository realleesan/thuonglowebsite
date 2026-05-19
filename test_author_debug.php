<?php
/**
 * Test Author Debug
 * Kiểm tra dữ liệu tác giả trong database
 */

// Bật error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test Author Debug</h1>";

// Load necessary files
require_once __DIR__ . '/core/view_init.php';
require_once __DIR__ . '/app/models/NewsModel.php';
require_once __DIR__ . '/app/services/AdminService.php';

// Test 1: Kiểm tra cấu trúc bảng news
echo "<h2>Test 1: Cấu trúc bảng news</h2>";

$newsModel = new \NewsModel();
$structure = $newsModel->query("DESCRIBE news");

echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
foreach ($structure as $field) {
    echo "<tr>";
    echo "<td>{$field['Field']}</td>";
    echo "<td>{$field['Type']}</td>";
    echo "<td>{$field['Null']}</td>";
    echo "<td>{$field['Key']}</td>";
    echo "<td>{$field['Default']}</td>";
    echo "</tr>";
}
echo "</table>";

// Test 2: Kiểm tra dữ liệu author trong news
echo "<h2>Test 2: Dữ liệu tác giả trong news</h2>";

$newsData = $newsModel->query("SELECT id, title, author_name, author_id FROM news ORDER BY created_at DESC LIMIT 5");

echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>ID</th><th>Title</th><th>Author Name</th><th>Author ID</th></tr>";
foreach ($newsData as $news) {
    echo "<tr>";
    echo "<td>{$news['id']}</td>";
    echo "<td>" . htmlspecialchars(substr($news['title'], 0, 50)) . "</td>";
    echo "<td>{$news['author_name']}</td>";
    echo "<td>{$news['author_id']}</td>";
    echo "</tr>";
}
echo "</table>";

// Test 3: Kiểm tra dữ liệu users
echo "<h2>Test 3: Dữ liệu users table</h2>";

$usersData = $newsModel->query("SELECT id, name FROM users LIMIT 5");

echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>ID</th><th>Name</th></tr>";
foreach ($usersData as $user) {
    echo "<tr>";
    echo "<td>{$user['id']}</td>";
    echo "<td>{$user['name']}</td>";
    echo "</tr>";
}
echo "</table>";

// Test 4: Kiểm tra SQL query với JOIN
echo "<h2>Test 4: SQL query với JOIN</h2>";

$joinQuery = "
    SELECT n.id, n.title, n.author_name, n.author_id, u.name as user_name
    FROM news n
    LEFT JOIN users u ON n.author_id = u.id
    ORDER BY n.created_at DESC
    LIMIT 5
";

$joinData = $newsModel->query($joinQuery);

echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>ID</th><th>Title</th><th>News Author Name</th><th>News Author ID</th><th>User Name</th></tr>";
foreach ($joinData as $row) {
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>" . htmlspecialchars(substr($row['title'], 0, 50)) . "</td>";
    echo "<td>{$row['author_name']}</td>";
    echo "<td>{$row['author_id']}</td>";
    echo "<td>{$row['user_name']}</td>";
    echo "</tr>";
}
echo "</table>";

// Test 5: Kiểm tra AdminService getNewsData
echo "<h2>Test 5: AdminService getNewsData</h2>";

try {
    $adminService = new \AdminService(null, 'admin');
    $newsDataResult = $adminService->getNewsData(1, 5, []);
    
    echo "<h3>Kết quả từ AdminService:</h3>";
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>ID</th><th>Title</th><th>Author Name</th><th>Category Name</th></tr>";
    
    if (!empty($newsDataResult['news'])) {
        foreach ($newsDataResult['news'] as $article) {
            echo "<tr>";
            echo "<td>{$article['id']}</td>";
            echo "<td>" . htmlspecialchars(substr($article['title'], 0, 50)) . "</td>";
            echo "<td>{$article['author_name']}</td>";
            echo "<td>{$article['category_name']}</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='4'>Không có dữ liệu</td></tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Lỗi AdminService: " . $e->getMessage() . "</p>";
}

echo "<h2>Kết luận:</h2>";
echo "<ul>";
echo "<li>Nếu 'Author Name' trống nhưng 'Author ID' có giá trị → cần JOIN với users</li>";
echo "<li>Nếu cả hai đều trống → cần nhập dữ liệu tác giả</li>";
echo "<li>Nếu 'Author Name' có dữ liệu → vấn đề ở logic hiển thị</li>";
echo "</ul>";

echo "<h2>Test Complete</h2>";
?>
