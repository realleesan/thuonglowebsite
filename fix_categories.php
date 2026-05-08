<?php
/**
 * Fix Categories Script - Simplified version
 */

require_once __DIR__ . '/core/database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getPdo();
    
    echo "<h1>Fix Categories</h1>";
    
    // Step 1: Get current categories
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY id");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Current Categories</h2>";
    foreach ($categories as $cat) {
        echo "<p>ID {$cat['id']}: " . htmlspecialchars($cat['name']) . " (Parent: {$cat['parent_id']})</p>";
    }
    
    // Step 2: Update images for existing categories
    $categoryImages = [
        'THỜI TRANG NỮ' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=800&h=600&fit=crop&crop=entropy',
        'THỜI TRANG NAM' => 'https://images.unsplash.com/photo-1551488831-005cb8bf5d7e?w=800&h=600&fit=crop&crop=entropy',
        'THỜI TRANG TRẺ EM' => 'https://images.unsplash.com/photo-1469334931182-2a2a8b1a2e5c?w=800&h=600&fit=crop&crop=entropy',
        'GIÀY DÉP NỮ' => 'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?w=800&h=600&fit=crop&crop=entropy',
        'TÚI VÍ NAM NỮ' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=800&h=600&fit=crop&crop=entropy',
        'PHỤ KIỆN VÀ TRANG SỨC' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=800&h=600&fit=crop&crop=entropy',
        'THỜI TRANG' => 'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?w=800&h=600&fit=crop&crop=entropy'
    ];
    
    echo "<h2>Updating Category Images</h2>";
    $updatedCount = 0;
    
    foreach ($categories as $cat) {
        if (isset($categoryImages[$cat['name']])) {
            $newImage = $categoryImages[$cat['name']];
            
            $stmt = $pdo->prepare("UPDATE categories SET image = ? WHERE id = ?");
            $result = $stmt->execute([$newImage, $cat['id']]);
            
            if ($result) {
                $updatedCount++;
                echo "<p style='color: green;'>✓ Updated image for: " . htmlspecialchars($cat['name']) . "</p>";
            }
        }
    }
    
    // Step 3: Add new categories to reach 20 total
    $newCategories = [
        [
            'name' => 'VÁY ĐẦM',
            'slug' => 'vay-dam',
            'description' => 'Các loại váy đầm nữ tính',
            'image' => 'https://images.unsplash.com/photo-1469334931182-2a2a8b1a2e5c?w=800&h=600&fit=crop&crop=entropy',
            'parent_id' => 14, // THỜI TRANG NỮ
            'sort_order' => 1
        ],
        [
            'name' => 'ÁO NỮ',
            'slug' => 'ao-nu',
            'description' => 'Các loại áo nữ đa dạng',
            'image' => 'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?w=800&h=600&fit=crop&crop=entropy',
            'parent_id' => 14, // THỜI TRANG NỮ
            'sort_order' => 2
        ],
        [
            'name' => 'QUẦN NỮ',
            'slug' => 'quan-nu',
            'description' => 'Các loại quần nữ thời trang',
            'image' => 'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?w=800&h=600&fit=crop&crop=entropy',
            'parent_id' => 14, // THỜI TRANG NỮ
            'sort_order' => 3
        ],
        [
            'name' => 'THƯƠNG HIỆU',
            'slug' => 'thuong-hieu',
            'description' => 'Các thương hiệu thời trang',
            'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800&h=600&fit=crop&crop=entropy',
            'parent_id' => null,
            'sort_order' => 10
        ],
        [
            'name' => 'PHỤ KIỆN NỮ',
            'slug' => 'phu-kien-nu',
            'description' => 'Phụ kiện thời trang nữ',
            'image' => 'https://images.unsplash.com/photo-1558769132-cb1aea458c5e?w=800&h=600&fit=crop&crop=entropy',
            'parent_id' => null,
            'sort_order' => 11
        ],
        [
            'name' => 'PHỤ KIỆN NAM',
            'slug' => 'phu-kien-nam',
            'description' => 'Phụ kiện thời trang nam',
            'image' => 'https://images.unsplash.com/photo-1578632292335-df3abbb0d586?w=800&h=600&fit=crop&crop=entropy',
            'parent_id' => null,
            'sort_order' => 12
        ],
        [
            'name' => 'VÁY CÔNG SỞ',
            'slug' => 'vay-cong-so',
            'description' => 'Váy thanh lịch công sở',
            'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800&h=600&fit=crop&crop=entropy',
            'parent_id' => null, // Will be updated later
            'sort_order' => 13
        ],
        [
            'name' => 'ÁO SƠ MI',
            'slug' => 'ao-so-mi',
            'description' => 'Áo sơ mi nữ thanh lịch',
            'image' => 'https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?w=800&h=600&fit=crop&crop=entropy',
            'parent_id' => null, // Will be updated later
            'sort_order' => 14
        ],
        [
            'name' => 'QUẦN JEANS',
            'slug' => 'quan-jeans',
            'description' => 'Quần jeans nữ phong cách',
            'image' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=800&h=600&fit=crop&crop=entropy',
            'parent_id' => null, // Will be updated later
            'sort_order' => 15
        ],
        [
            'name' => 'QUẦN SHORT',
            'slug' => 'quan-short',
            'description' => 'Quần short nữ năng động',
            'image' => 'https://images.unsplash.com/photo-1517831907240-f20f8942968e?w=800&h=600&fit=crop&crop=entropy',
            'parent_id' => null, // Will be updated later
            'sort_order' => 16
        ]
    ];
    
    echo "<h2>Adding New Categories</h2>";
    $insertedCount = 0;
    $newCategoryIds = [];
    
    foreach ($newCategories as $newCat) {
        $stmt = $pdo->prepare("
            INSERT INTO categories (name, slug, description, image, parent_id, sort_order, status)
            VALUES (?, ?, ?, ?, ?, ?, 'active')
        ");
        
        $result = $stmt->execute([
            $newCat['name'],
            $newCat['slug'],
            $newCat['description'],
            $newCat['image'],
            $newCat['parent_id'],
            $newCat['sort_order']
        ]);
        
        if ($result) {
            $newId = $pdo->lastInsertId();
            $newCategoryIds[$newCat['name']] = $newId;
            $insertedCount++;
            echo "<p style='color: blue;'>✓ Added: " . htmlspecialchars($newCat['name']) . " (ID: $newId)</p>";
        }
    }
    
    // Step 4: Update parent relationships for new categories
    echo "<h2>Updating Parent Relationships</h2>";
    
    $parentUpdates = [
        'VÁY CÔNG SỞ' => 14, // THỜI TRANG NỮ
        'ÁO SƠ MI' => 14,   // THỜI TRANG NỮ
        'QUẦN JEANS' => 14,  // THỜI TRANG NỮ
        'QUẦN SHORT' => 14   // THỜI TRANG NỮ
    ];
    
    foreach ($parentUpdates as $catName => $parentId) {
        if (isset($newCategoryIds[$catName])) {
            $catId = $newCategoryIds[$catName];
            
            $stmt = $pdo->prepare("UPDATE categories SET parent_id = ? WHERE id = ?");
            $result = $stmt->execute([$parentId, $catId]);
            
            if ($result) {
                echo "<p style='color: purple;'>→ Linked: $catName → Parent ID: $parentId</p>";
            }
        }
    }
    
    echo "<h2>Summary</h2>";
    echo "<p><strong>Images updated:</strong> $updatedCount</p>";
    echo "<p><strong>Categories added:</strong> $insertedCount</p>";
    echo "<p><strong>Total categories now:</strong> " . (count($categories) + $insertedCount) . "</p>";
    
    echo "<h2>Verify</h2>";
    echo "<a href='?page=products'>View Products</a>";
    
} catch (PDOException $e) {
    echo "<h2>Database Error</h2>";
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<p>SQL State: " . $e->getCode() . "</p>";
}
?>
