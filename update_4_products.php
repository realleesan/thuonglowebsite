<?php
/**
 * Update images for 4 specific products: Lemon Tree, I AM, Maxi, See You
 * Using similar images to neighboring products
 */

require_once __DIR__ . '/core/database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getPdo();
    
    echo "<h1>Update 4 Products Images</h1>";
    
    // Target products to update
    $targetProducts = [
        'Lemon Tree',
        'I AM', 
        'Nieve',
        'Váy maxi hoa nhí',
        'See You'
    ];
    
    // Fashion images similar to existing products (professional, elegant style)
    $fashionImages = [
        'https://images.unsplash.com/photo-1558769132-cb1aea458c5e?w=800&h=1000&fit=crop&crop=face', // Similar to existing style
        'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800&h=1000&fit=crop&crop=face', // Professional style
        'https://images.unsplash.com/photo-1469334931182-2a2a8b1a2e5c?w=800&h=1000&fit=crop&crop=face', // Elegant dress
        'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?w=800&h=1000&fit=crop&crop=face', // Fashion style
        'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=800&h=1000&fit=crop&crop=face', // Additional style
    ];
    
    echo "<h2>Target Products:</h2>";
    echo "<ul>";
    foreach ($targetProducts as $productName) {
        echo "<li>" . htmlspecialchars($productName) . "</li>";
    }
    echo "</ul>";
    
    // Get products to update
    $placeholders = str_repeat('?,', count($targetProducts) - 1) . '?';
    $stmt = $pdo->prepare("SELECT * FROM products WHERE name IN ($placeholders) ORDER BY id");
    $stmt->execute($targetProducts);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Found Products (" . count($products) . " items)</h2>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'>
            <th>ID</th>
            <th>Name</th>
            <th>Current Image</th>
            <th>Price</th>
            <th>Status</th>
          </tr>";
    
    foreach ($products as $product) {
        echo "<tr>
                <td>{$product['id']}</td>
                <td>" . htmlspecialchars($product['name']) . "</td>
                <td>" . ($product['image'] ? "<img src='{$product['image']}' width='50' height='50'>" : "No image") . "</td>
                <td>" . number_format($product['price'], 0, ',', '.') . "đ</td>
                <td>{$product['status']}</td>
              </tr>";
    }
    echo "</table>";
    
    echo "<h2>Updating Products...</h2>";
    
    $updatedCount = 0;
    $imageIndex = 0;
    
    foreach ($products as $product) {
        // Get new image
        $newImage = $fashionImages[$imageIndex % count($fashionImages)];
        $imageIndex++;
        
        // Update product image
        $stmt = $pdo->prepare("UPDATE products SET image = ? WHERE id = ?");
        $result = $stmt->execute([$newImage, $product['id']]);
        
        if ($result) {
            $updatedCount++;
            echo "<p style='color: green;'>✓ Updated product ID {$product['id']}: " . htmlspecialchars($product['name']) . "</p>";
            echo "<p style='color: blue; margin-left: 20px;'>New image: <img src='$newImage' width='100' height='120'></p>";
        } else {
            echo "<p style='color: red;'>✗ Failed to update product ID {$product['id']}</p>";
        }
    }
    
    echo "<h2>Update Summary</h2>";
    echo "<p><strong>Target products:</strong> " . count($targetProducts) . "</p>";
    echo "<p><strong>Found products:</strong> " . count($products) . "</p>";
    echo "<p><strong>Updated products:</strong> " . $updatedCount . "</p>";
    
    echo "<h2>Verify Updates</h2>";
    echo "<a href='?page=products'>View Products Page</a>";
    
} catch (PDOException $e) {
    echo "<h2>Database Error</h2>";
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
