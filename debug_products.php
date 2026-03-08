<?php
/**
 * Debug file để xem cấu trúc bảng products
 */

require_once __DIR__ . '/core/database.php';

echo "<h1>Debug: Cấu trúc bảng products</h1>";

$db = Database::getInstance();
$pdo = $db->getPdo();

// Xem cấu trúc bảng
echo "<h2>1. Cấu trúc bảng products:</h2>";
$sql = "DESCRIBE products";
$stmt = $pdo->query($sql);
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "<td>" . $row['Default'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Xem 5 sản phẩm đầu tiên
echo "<h2>2. Dữ liệu mẫu (5 sản phẩm đầu tiên):</h2>";
$sql = "SELECT id, name, image, price, sale_price FROM products LIMIT 5";
$stmt = $pdo->query($sql);
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><th>ID</th><th>Name</th><th>Image</th><th>Price</th><th>Sale Price</th></tr>";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['image'] ?? 'NULL') . "</td>";
    echo "<td>" . $row['price'] . "</td>";
    echo "<td>" . ($row['sale_price'] ?? 'NULL') . "</td>";
    echo "</tr>";
}
echo "</table>";

// Xem cart items
echo "<h2>3. Dữ liệu bảng carts:</h2>";
$sql = "SELECT * FROM carts LIMIT 5";
$stmt = $pdo->query($sql);
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
$first = true;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if ($first) {
        echo "<tr>";
        foreach ($row as $key => $value) {
            echo "<th>" . $key . "</th>";
        }
        echo "</tr>";
        $first = false;
    }
    echo "<tr>";
    foreach ($row as $key => $value) {
        echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
    }
    echo "</tr>";
}
echo "</table>";

// Test getCartData output
echo "<h2>4. Test UserService getCartData:</h2>";
session_start();
require_once __DIR__ . '/app/services/UserService.php';

$userService = new UserService();
$userId = $_SESSION['user_id'] ?? 1; // Thử user_id = 1

echo "User ID: " . $userId . "<br>";

$cartData = $userService->getCartData($userId);
echo "<h3>Cart Items:</h3>";
echo "<pre>";
print_r($cartData);
echo "</pre>";
