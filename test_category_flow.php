<?php
/**
 * Test trực tiếp luồng lấy categories cho sidebar filter
 * Chạy: http://localhost/thuonglowebsite/test_category_flow.php
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/app/services/DataTransformer.php';
require_once __DIR__ . '/app/models/CategoriesModel.php';

echo "<h1>Test Category Flow</h1>";

// Kết nối database
$db_host = 'localhost';
$db_name = 'test1_thuonglowebsite';
$db_user = 'test1_thuonglowebsite';
$db_pass = '21042005nhat';

$db = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

echo "<h2>Bước 1: Gọi SQL trực tiếp</h2>";
$sql = "
    SELECT c.*, COUNT(p.id) as product_count
    FROM categories c
    LEFT JOIN products p ON c.id = p.category_id
    WHERE c.status = 'active'
    GROUP BY c.id
    ORDER BY c.sort_order ASC
";
$result = $db->query($sql);
$rawCategories = [];
while ($row = $result->fetch_assoc()) {
    $rawCategories[] = $row;
}

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Name</th><th>product_count</th></tr>";
foreach ($rawCategories as $cat) {
    echo "<tr><td>{$cat['id']}</td><td>{$cat['name']}</td><td>{$cat['product_count']}</td></tr>";
}
echo "</table>";

echo "<h2>Bước 2: Test CategoriesModel->getWithProductCounts()</h2>";
// Simulate model
$categoriesModel = new CategoriesModel($db);
$modelResult = $categoriesModel->getWithProductCounts();

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Name</th><th>product_count</th></tr>";
foreach ($modelResult as $cat) {
    echo "<tr><td>{$cat['id']}</td><td>{$cat['name']}</td><td>{$cat['product_count']}</td></tr>";
}
echo "</table>";

echo "<h2>Bước 3: Test DataTransformer->transformCategories()</h2>";
// Simulate transformer
class MockSecurity {
    public function escapeHtml($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

$transformer = new DataTransformer(new MockSecurity());
$transformed = $transformer->transformCategories($modelResult);

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Name</th><th>product_count</th></tr>";
foreach ($transformed as $cat) {
    echo "<tr><td>{$cat['id']}</td><td>{$cat['name']}</td><td>{$cat['product_count']}</td></tr>";
}
echo "</table>";

$db->close();
echo "<p><strong>Done!</strong> So sánh kết quả 3 bước xem có giống nhau không.</p>";
