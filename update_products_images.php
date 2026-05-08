<?php
/**
 * Script to update product images and names
 * This script will:
 * 1. Show all current products
 * 2. Update product names (excluding specified ones)
 * 3. Add appropriate product images
 */

require_once __DIR__ . '/core/database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getPdo();
    
    echo "<h1>Product Management - Update Images & Names</h1>";
    
    // Get all products
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Current Products (" . count($products) . " items)</h2>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'>
            <th>ID</th>
            <th>Name</th>
            <th>Current Image</th>
            <th>Price</th>
            <th>Category</th>
            <th>Status</th>
          </tr>";
    
    foreach ($products as $product) {
        echo "<tr>
                <td>{$product['id']}</td>
                <td>" . htmlspecialchars($product['name']) . "</td>
                <td>" . ($product['image'] ? "<img src='{$product['image']}' width='50' height='50'>" : "No image") . "</td>
                <td>" . number_format($product['price'], 0, ',', '.') . "đ</td>
                <td>{$product['category_id']}</td>
                <td>{$product['status']}</td>
              </tr>";
    }
    echo "</table>";
    
    // Products to exclude from renaming
    $excludeNames = [
        'Lemon Tree',
        'Nieve',
        'I AM',
        'GS',
        'See You',
        'Nile',
        'Hers',
        'Y.JIA',
        'NHÀ WGWE'
    ];
    
    echo "<h2>Updating Products...</h2>";
    
    // Sample product names for fashion items
    $fashionNames = [
        'Váy hoa nhún eo nữ tính',
        'Áo sơ mi trắng công sở',
        'Quần jeans ống rộng thời trang',
        'Áo thun oversize graphic',
        'Chân váy chữ A nữ',
        'Áo croptop mùa hè',
        'Quần short kaki casual',
        'Váy maxi hoa nhí',
        'Áo blazer thanh lịch',
        'Quần culottes công sở',
        'Áo polo nữ thể thao',
        'Váy bodycon tiệc tùng',
        'Áo len mỏng mùa thu',
        'Quần jogger phong cách',
        'Váy yếm denim',
        'Áo tank top basic',
        'Quần tây nữ công sở',
        'Váy suông công sở',
        'Áo khoác denim',
        'Quần leggings thể thao',
        'Váy hai dây nữ',
        'Áo sơ mi hoa nhí',
        'Quần ống loe retro',
        'Váy cocktail sang trọng',
        'Áo thun tay lỡ',
        'Quần short jeans',
        'Váy wrap nữ tính',
        'Áo khoác cardigan',
        'Quần cargo phong cách',
        'Váy tua rua vintage',
        'Áo polo stripe',
        'Quần skinny jeans',
        'Váy off shoulder',
        'Áo tank top croptop',
        'Quần palazzo công sở',
        'Váy sequin lấp lánh',
        'Áo blazer cropped',
        'Quần flare jeans',
        'Váy satin sang trọng',
        'Áo thun nữ basic',
        'Quần culottes hoa',
        'Váy tweed công sở',
        'Áo sơ mi lụa',
        'Quần wide leg',
        'Váy kimono',
        'Áo polo nữ vintage',
        'Quần paperbag',
        'Váy plaid công sở',
        'Áo thun graphic',
        'Quần jogger nữ',
        'Váy organza',
        'Áo blazer double',
        'Quần cigarette',
        'Váy velvet sang trọng',
        'Áo croptop hoa',
        'Quần capri retro',
        'Váy lace nữ tính',
        'Áo tank top stripe',
        'Quần high waist',
        'Váy chiffon mùa hè',
        'Áo sơ mi kẻ',
        'Quần mom jeans',
        'Váy tulle tiệc',
        'Áo polo nữ pastel',
        'Quần short denim',
        'Váy brocade sang trọng',
        'Áo thun nữ oversized',
        'Quần jogger phong cách',
        'Váy guipure nữ tính',
        'Áo blazer nữ công sở',
        'Quần culottes hoa nhí',
        'Váy jacquard sang trọng',
        'Áo croptop basic',
        'Quần ống rộng công sở',
        'Váy mesh nữ tính',
        'Áo polo nữ thể thao',
        'Quần short kaki',
        'Váy sequin tiệc tùng',
        'Áo thun nữ graphic',
        'Quần jeans nữ skinny',
        'Váy organza công sở',
        'Áo sơ mi nữ công sở',
        'Quần tây nữ thanh lịch',
        'Váy satin tiệc',
        'Áo blazer nữ thanh lịch',
        'Quần palazzo nữ',
        'Váy lace công sở',
        'Áo croptop nữ',
        'Quần short nữ',
        'Váy maxi nữ',
        'Áo thun nữ basic',
        'Quần jeans nữ',
        'Váy bodycon nữ',
        'Áo sơ mi nữ',
        'Quần nữ công sở',
        'Váy nữ công sở',
        'Áo nữ thời trang',
        'Quần nữ thời trang',
        'Váy nữ thời trang'
    ];
    
    // Product image URLs from Unsplash (stable and high quality)
    $fashionImages = [
        'https://images.unsplash.com/photo-1539008835657-9e8e9680c956?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1515372039744-b8f2a3214755?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1558769132-cb1aea458c5e?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1469334931182-2a2a8b1a2e5c?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1578632292335-df3abbb0d586?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1517831907240-f20f8942968e?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1551488831-005cb8bf5d7e?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1539109136881-3be0616acf4b?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1509631179647-0177331693ae?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1578632292335-df3abbb0d586?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1422393465423-8ca2f1b6c6e0?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1578632292335-df3abbb0d586?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1517831907240-f20f8942968e?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1551488831-005cb8bf5d7e?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1539109136881-3be0616acf4b?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1509631179647-0177331693ae?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1422393465423-8ca2f1b6c6e0?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1578632292335-df3abbb0d586?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1517831907240-f20f8942968e?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1551488831-005cb8bf5d7e?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1539109136881-3be0616acf4b?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1509631179647-0177331693ae?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1422393465423-8ca2f1b6c6e0?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1578632292335-df3abbb0d586?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1517831907240-f20f8942968e?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1551488831-005cb8bf5d7e?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1539109136881-3be0616acf4b?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1509631179647-0177331693ae?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1422393465423-8ca2f1b6c6e0?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1578632292335-df3abbb0d586?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1517831907240-f20f8942968e?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1551488831-005cb8bf5d7e?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1539109136881-3be0616acf4b?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1509631179647-0177331693ae?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1422393465423-8ca2f1b6c6e0?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1578632292335-df3abbb0d586?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1517831907240-f20f8942968e?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1551488831-005cb8bf5d7e?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1539109136881-3be0616acf4b?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1509631179647-0177331693ae?w=800&h=1000&fit=crop&crop=face',
        'https://images.unsplash.com/photo-1422393465423-8ca2f1b6c6e0?w=800&h=1000&fit=crop&crop=face'
    ];
    
    $updatedCount = 0;
    $nameIndex = 0;
    $imageIndex = 0;
    
    foreach ($products as $product) {
        $shouldUpdate = !in_array($product['name'], $excludeNames);
        
        if ($shouldUpdate) {
            // Get new name
            $newName = $fashionNames[$nameIndex % count($fashionNames)];
            $nameIndex++;
            
            // Get new image
            $newImage = $fashionImages[$imageIndex % count($fashionImages)];
            $imageIndex++;
            
            // Generate slug from name
            $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $newName));
            $slug = trim($slug, '-');
            
            // Update product
            $stmt = $pdo->prepare("
                UPDATE products 
                SET name = ?, slug = ?, image = ?, short_description = ? 
                WHERE id = ?
            ");
            
            $shortDesc = "Thời trang nữ phong cách hiện đại, chất liệu cao cấp, thiết kế thanh lịch phù hợp mọi hoàn cảnh.";
            
            $result = $stmt->execute([$newName, $slug, $newImage, $shortDesc, $product['id']]);
            
            if ($result) {
                $updatedCount++;
                echo "<p style='color: green;'>✓ Updated product ID {$product['id']}: " . htmlspecialchars($newName) . "</p>";
            } else {
                echo "<p style='color: red;'>✗ Failed to update product ID {$product['id']}</p>";
            }
        } else {
            echo "<p style='color: blue;'>→ Skipped product ID {$product['id']}: " . htmlspecialchars($product['name']) . " (excluded)</p>";
        }
    }
    
    echo "<h2>Update Summary</h2>";
    echo "<p><strong>Total products:</strong> " . count($products) . "</p>";
    echo "<p><strong>Excluded products:</strong> " . count($excludeNames) . "</p>";
    echo "<p><strong>Updated products:</strong> " . $updatedCount . "</p>";
    
    echo "<h2>Verify Updates</h2>";
    echo "<a href='?page=products'>View Products Page</a>";
    
} catch (PDOException $e) {
    echo "<h2>Database Error</h2>";
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
