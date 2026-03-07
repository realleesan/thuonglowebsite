<?php
/**
 * Test script để debug vấn đề category product count
 * Chạy file này bằng cách truy cập: http://localhost/thuonglowebsite/test_category_debug.php
 */

$db_host = 'localhost';
$db_name = 'test1_thuonglowebsite';
$db_user = 'test1_thuonglowebsite';
$db_pass = '21042005nhat';

$db = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

echo "<h1>Test Category Debug</h1>";

echo "<h2>1. Danh sách Categories</h2>";
$result = $db->query("SELECT * FROM categories ORDER BY id");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Name</th><th>Status</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr><td>{$row['id']}</td><td>{$row['name']}</td><td>{$row['status']}</td></tr>";
}
echo "</table>";

echo "<h2>2. Danh sách Products</h2>";
$result = $db->query("SELECT id, name, category_id, status FROM products ORDER BY id LIMIT 20");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Name</th><th>Category ID</th><th>Status</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr><td>{$row['id']}</td><td>{$row['name']}</td><td>{$row['category_id']}</td><td>{$row['status']}</td></tr>";
}
echo "</table>";

echo "<h2>3. Test SQL đếm sản phẩm trong danh mục (không có điều kiện status)</h2>";
$sql = "
    SELECT c.id, c.name, COUNT(p.id) as product_count
    FROM categories c
    LEFT JOIN products p ON c.id = p.category_id
    WHERE c.status = 'active'
    GROUP BY c.id
    ORDER BY c.sort_order ASC
";
$result = $db->query($sql);
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Name</th><th>Product Count</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr><td>{$row['id']}</td><td>{$row['name']}</td><td>{$row['product_count']}</td></tr>";
}
echo "</table>";

echo "<h2>4. Test SQL với điều kiện status = 'active'</h2>";
$sql2 = "
    SELECT c.id, c.name, COUNT(p.id) as product_count
    FROM categories c
    LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active'
    WHERE c.status = 'active'
    GROUP BY c.id
    ORDER BY c.sort_order ASC
";
$result2 = $db->query($sql2);
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Name</th><th>Product Count (status='active')</th></tr>";
while ($row = $result2->fetch_assoc()) {
    echo "<tr><td>{$row['id']}</td><td>{$row['name']}</td><td>{$row['product_count']}</td></tr>";
}
echo "</table>";

echo "<h2>5. Kiểm tra xem products có category_id hợp lệ không</h2>";
$sql3 = "
    SELECT p.category_id, c.id as cat_id, c.name as cat_name, COUNT(*) as count
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    GROUP BY p.category_id
";
$result3 = $db->query($sql3);
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Product Category ID</th><th>Category ID (from join)</th><th>Category Name</th><th>Count</th></tr>";
while ($row = $result3->fetch_assoc()) {
    echo "<tr><td>{$row['category_id']}</td><td>{$row['cat_id']}</td><td>{$row['cat_name']}</td><td>{$row['count']}</td></tr>";
}
echo "</table>";

$db->close();
echo "<p><strong>Done!</strong> Vui lòng copy kết quả và gửi cho admin.</p>";
