<?php
/**
 * Script to update images for specific products only
 * Target products: Chân váy chữ A nữ, Váy maxi hoa nhí, và các sản phẩm excluded
 */

require_once __DIR__ . '/core/database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getPdo();
    
    echo "<h1>Update Specific Products Images</h1>";
    
    // Target products to update
    $targetProducts = [
        'Chân váy chữ A nữ',
        'Váy maxi hoa nhí',
        'NHÀ WGWE',
        'Y.JIA',
        'Hers',
        'Nile',
        'See You',
        'GS',
        'I AM',
        'Nieve',
        'Lemon Tree'
    ];
    
    // Fashion images from Unsplash (different variety)
    $fashionImages = [
        'https://images.unsplash.com/photo-1539109136881-3be0616acf4b?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1469334931182-2a2a8b1a2e5c?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1578632292335-df3abbb0d586?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1517831907240-f20f8942968e?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1551488831-005cb8bf5d7e?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1509631179647-0177331693ae?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1422393465423-8ca2f1b6c6e0?w=800&h=1000&fit=crop&crop=face'
    ];
    
    echo "<h2>Target Products to Update:</h2>";
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
