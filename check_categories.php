<?php
require_once 'config.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['database']['host']};dbname={$config['database']['name']};charset=utf8mb4",
        $config['database']['username'],
        $config['database']['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "<h2>Danh mục hiện có trong database:</h2>";
    
    $stmt = $pdo->query("SELECT id, name, slug, status, show_in_filter FROM categories ORDER BY id ASC");
    $categories = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Tên</th><th>Slug</th><th>Status</th><th>Show in Filter</th></tr>";
    
    foreach ($categories as $cat) {
        echo "<tr>";
        echo "<td>{$cat['id']}</td>";
        echo "<td><strong>{$cat['name']}</strong></td>";
        echo "<td>{$cat['slug']}</td>";
        echo "<td>{$cat['status']}</td>";
        echo "<td>" . ($cat['show_in_filter'] ? '✅' : '❌') . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<h2>URL cho danh mục:</h2>";
    echo "<ul>";
    foreach ($categories as $cat) {
        if ($cat['status'] === 'active') {
            echo "<li><a href='?page=products&category={$cat['id']}'>?page=products&category={$cat['id']} - {$cat['name']}</a></li>";
        }
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<h2>Lỗi:</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
